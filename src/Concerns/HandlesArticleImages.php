<?php
namespace Lyre\Content\Concerns;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Lyre\Content\Models\Article;
use Lyre\File\Models\File as FileModel;

trait HandlesArticleImages
{
    /**
     * Generate image using DALL-E
     */
    protected function generateImage(string $prompt): ?string
    {
        try {
            Log::info('🎨 Calling DALL-E API', [
                'prompt_preview' => substr($prompt, 0, 100),
            ]);

            $response = openai()->request('post', 'images/generations', [
                'model'   => 'dall-e-3',
                'prompt'  => $prompt,
                'n'       => 1,
                'size'    => '1024x1024',
                'quality' => 'standard',
            ]);

            $imageUrl = $response['data'][0]['url'] ?? null;

            Log::info('✅ DALL-E image generated', ['has_url' => ! empty($imageUrl)]);

            return $imageUrl;
        } catch (\Exception $e) {
            Log::error('❌ Failed to generate image', [
                'prompt' => substr($prompt, 0, 100),
                'error'  => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Upload image from URL to storage
     */
    protected function uploadImageFromUrl(string $url, string $name): FileModel
    {
        Log::debug('📥 Downloading image from URL', ['name' => $name]);

        $contents  = file_get_contents($url);
        $extension = 'png';
        $filename  = Str::slug($name) . '-' . time();
        $path      = 'articles/images/' . date('Y/m/') . $filename . '.' . $extension;

        Storage::disk('public')->put($path, $contents);

        $fileModel = FileModel::create([
            'name'          => $filename,
            'original_name' => $name . '.' . $extension,
            'path'          => $path,
            'extension'     => $extension,
            'mimetype'      => 'image/png',
            'size'          => strlen($contents),
            'storage'       => 'public',
        ]);

        $config = $this->getConfig();
        if ($config['tenant_id'] ?? null) {
            $fileModel->associateWithTenant($config['tenant_id']);
        }

        Log::debug('✅ Image uploaded to storage', [
            'file_id' => $fileModel->id,
            'path'    => $path,
            'size'    => number_format(strlen($contents)) . ' bytes',
        ]);

        return $fileModel;
    }

    /**
     * Add images to article content
     *
     * Note: Image positions should be at natural break points (between paragraphs,
     * sections, headings, or complete sentences). Images should never interrupt
     * a sentence midway. The AI is instructed to provide appropriate positions.
     */
    protected function addImagesToContent(array $articleData): array
    {
        $content        = $articleData['content'];
        $imagePrompts   = $articleData['image_prompts'] ?? [];
        $imagePositions = $articleData['image_positions'] ?? [];

        // Generate and insert inline images (if enabled in config)
        // Images are placed at positions suggested by AI, which should be at natural break points
        if ($this->shouldAddInlineImages()) {
            $offset = 0;
            foreach ($imagePrompts as $index => $imagePrompt) {
                Log::info("🎨 Generating inline image " . ($index + 1) . "/" . count($imagePrompts), [
                    'prompt_preview' => substr($imagePrompt, 0, 50),
                ]);

                $imageUrl = $this->generateImage($imagePrompt);

                if ($imageUrl) {
                    // Upload image to storage
                    $fileModel = $this->uploadImageFromUrl($imageUrl, "inline-image-{$index}");

                    // Create HTML for image
                    $imageUrl  = Storage::disk('public')->url($fileModel->path);
                    $imageHtml = sprintf(
                        '<img src="%s" alt="%s" style="max-width: 100%%; height: auto;" />',
                        $imageUrl,
                        htmlspecialchars($imagePrompt)
                    );

                    // Insert at position
                    $position = $imagePositions[$index] ?? null;
                    if ($position !== null) {
                        $position += $offset;
                        $content   = substr_replace($content, $imageHtml, $position, 0);
                        $offset   += strlen($imageHtml);
                    } else {
                        // Append if no position specified
                        $content .= "\n" . $imageHtml;
                    }

                    Log::info('✅ Inline image added', [
                        'index'   => $index + 1,
                        'file_id' => $fileModel->id,
                    ]);
                }
            }
        }

        $articleData['content'] = $content;

        // Generate featured image (if enabled in config)
        if ($this->shouldAddFeaturedImage() && ! empty($articleData['featured_image_prompt'])) {
            Log::info('🎨 Generating featured image', [
                'prompt_preview' => substr($articleData['featured_image_prompt'], 0, 50),
            ]);

            $imageUrl = $this->generateImage($articleData['featured_image_prompt']);

            if ($imageUrl) {
                $fileModel                         = $this->uploadImageFromUrl($imageUrl, 'featured-image');
                $articleData['featured_image']     = $fileModel;
                $articleData['featured_image_url'] = $imageUrl;

                Log::info('✅ Featured image generated', [
                    'file_id' => $fileModel->id,
                ]);
            }
        }

        return $articleData;
    }

    /**
     * Attach featured image to article from URL
     */
    protected function attachFeaturedImageFromUrl(Article $article, string $imageUrl): void
    {
        Log::info('🖼️ Attaching featured image from URL', [
            'article_id' => $article->id,
        ]);

        try {
            $imageContents = file_get_contents($imageUrl);
            if ($imageContents === false) {
                throw new \Exception('Failed to download image');
            }

            $filename = 'featured-' . $article->slug . '-' . time() . '.png';
            $path     = 'articles/' . date('Y/m') . '/' . $filename;

            Storage::disk('public')->put($path, $imageContents);

            $file = FileModel::create([
                'name'      => $filename,
                'path'      => $path,
                'disk'      => 'public',
                'mime_type' => 'image/png',
                'size'      => strlen($imageContents),
                'status'    => 'published',
            ]);

            $config = $this->getConfig();
            if ($config['tenant_id'] ?? null) {
                $file->associateWithTenant($config['tenant_id']);
            }

            // Attach to article (single file, so we detach existing first)
            $article->files()->detach();
            $article->files()->attach($file->id);

            Log::info('✅ Featured image attached', [
                'article_id' => $article->id,
                'file_id'    => $file->id,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Failed to attach featured image', [
                'article_id' => $article->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if inline images should be added based on config
     */
    protected function shouldAddInlineImages(): bool
    {
        $config = $this->getConfig();
        return ($config['add_images'] ?? false) && ($config['add_inline_images'] ?? true);
    }

    /**
     * Check if featured image should be added based on config
     */
    protected function shouldAddFeaturedImage(): bool
    {
        $config = $this->getConfig();
        return ($config['add_images'] ?? false) && ($config['add_featured_image'] ?? true);
    }

    /**
     * Get configuration - must be implemented by using class
     */
    abstract protected function getConfig(): array;
}
