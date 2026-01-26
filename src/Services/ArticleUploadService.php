<?php
namespace Lyre\Content\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Lyre\Content\Concerns\HandlesArticleImages;
use Lyre\Content\Concerns\InteractsWithOpenAI;
use Lyre\Content\Concerns\ManagesArticleData;
use Lyre\Content\Models\Article;
use Lyre\File\Models\File as FileModel;

class ArticleUploadService
{
    use InteractsWithOpenAI, HandlesArticleImages, ManagesArticleData;

    protected array $config;
    protected array $stats = [
        'processed'  => 0,
        'successful' => 0,
        'failed'     => 0,
        'errors'     => [],
    ];

    /**
     * Process uploaded files from Filament
     */
    public function processUploadedFiles(array $uploadedFiles, array $config): array
    {
        Log::info('📥 Starting Article Upload Service', [
            'files_count' => count($uploadedFiles),
            'config'      => [
                'use_ai'     => $config['use_ai'] ?? false,
                'file_types' => $config['file_types'] ?? [],
                'add_images' => $config['add_images'] ?? false,
            ],
        ]);

        $this->config = $config;
        $this->stats  = ['processed' => 0, 'successful' => 0, 'failed' => 0, 'errors' => []];

        $filesToProcess  = [];
        $tempDirectories = [];

        try {
            Log::info('📦 Processing uploaded files', ['count' => count($uploadedFiles)]);

            // Process each uploaded file
            foreach ($uploadedFiles as $index => $uploadedFile) {
                $storagePath = Storage::disk('local')->path($uploadedFile);
                $fileName    = basename($uploadedFile);

                Log::debug("📄 Examining file " . ($index + 1) . "/" . count($uploadedFiles), [
                    'file' => $fileName,
                    'size' => filesize($storagePath),
                ]);

                // Check if it's a zip file
                if (strtolower(pathinfo($storagePath, PATHINFO_EXTENSION)) === 'zip') {
                    Log::info('📦 Extracting ZIP archive', ['file' => $fileName]);

                    $result            = $this->extractZipFile($storagePath);
                    $filesToProcess    = array_merge($filesToProcess, $result['files']);
                    $tempDirectories[] = $result['extractPath'];

                    Log::info('✅ ZIP extracted successfully', [
                        'file'            => $fileName,
                        'extracted_files' => count($result['files']),
                        'temp_dir'        => basename($result['extractPath']),
                    ]);
                } else {
                    $filesToProcess[] = $storagePath;
                }
            }

            // Filter files based on allowed types
            $beforeFilter   = count($filesToProcess);
            $filesToProcess = $this->filterFilesByType($filesToProcess);
            $afterFilter    = count($filesToProcess);

            Log::info('🔍 Filtered files by type', [
                'before'        => $beforeFilter,
                'after'         => $afterFilter,
                'skipped'       => $beforeFilter - $afterFilter,
                'allowed_types' => $config['file_types'] ?? [],
            ]);

            if (empty($filesToProcess)) {
                Log::warning('⚠️ No files to process after filtering');
                return $this->stats;
            }

            // Process each file
            Log::info('🎯 Starting to process articles', ['count' => count($filesToProcess)]);

            foreach ($filesToProcess as $index => $file) {
                $fileName = basename($file);

                Log::info("📝 Processing article " . ($index + 1) . "/" . count($filesToProcess), [
                    'file'     => $fileName,
                    'progress' => round((($index + 1) / count($filesToProcess)) * 100) . '%',
                ]);

                try {
                    $this->stats['processed']++;
                    $article = $this->processFile($file);
                    $this->stats['successful']++;

                    Log::info("✅ Article created successfully", [
                        'file'       => $fileName,
                        'article_id' => $article->id,
                        'title'      => $article->title,
                        'slug'       => $article->slug ?? null,
                        'progress'   => "{$this->stats['successful']}/{$this->stats['processed']}",
                    ]);
                } catch (\Exception $e) {
                    $this->stats['failed']++;
                    $this->stats['errors'][] = [
                        'file'  => $fileName,
                        'error' => $e->getMessage(),
                    ];

                    Log::error("❌ Failed to process article", [
                        'file'  => $fileName,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            Log::info('🎉 All articles processed', [
                'total'        => $this->stats['processed'],
                'successful'   => $this->stats['successful'],
                'failed'       => $this->stats['failed'],
                'success_rate' => $this->stats['processed'] > 0
                    ? round(($this->stats['successful'] / $this->stats['processed']) * 100) . '%'
                    : '0%',
            ]);
        } finally {
            // Clean up temporary directories
            if (! empty($tempDirectories)) {
                Log::info('🧹 Cleaning up temporary directories', ['count' => count($tempDirectories)]);

                foreach ($tempDirectories as $dir) {
                    $this->deleteDirectory($dir);
                    Log::debug('🗑️ Deleted temp directory', ['dir' => basename($dir)]);
                }

                Log::info('✨ Cleanup completed');
            }
        }

        return $this->stats;
    }

    /**
     * Process articles from a folder (legacy method for server-side folders)
     */
    public function processFolder(string $folderPath, array $config): array
    {
        Log::info('📁 Processing folder', ['path' => $folderPath]);

        $this->config = $config;
        $this->stats  = ['processed' => 0, 'successful' => 0, 'failed' => 0, 'errors' => []];

        $files = $this->getFilesFromFolder($folderPath);

        Log::info('📋 Files found in folder', ['count' => count($files)]);

        foreach ($files as $file) {
            try {
                $this->stats['processed']++;
                $this->processFile($file);
                $this->stats['successful']++;
            } catch (\Exception $e) {
                $this->stats['failed']++;
                $this->stats['errors'][] = [
                    'file'  => $file,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $this->stats;
    }

    /**
     * Extract files from a zip archive
     */
    protected function extractZipFile(string $zipPath): array
    {
        if (! class_exists('ZipArchive')) {
            throw new \Exception('ZipArchive extension not installed');
        }

        $zip            = new \ZipArchive();
        $extractedFiles = [];

        if ($zip->open($zipPath) === true) {
            $extractPath = storage_path('app/temp-extracted-' . uniqid() . '-' . time());

            Log::debug('📦 Creating extraction directory', ['path' => basename($extractPath)]);

            // Create extraction directory
            if (! is_dir($extractPath)) {
                mkdir($extractPath, 0755, true);
            }

            // Extract all files
            $zip->extractTo($extractPath);
            $numFiles = $zip->numFiles;
            $zip->close();

            Log::debug('📦 ZIP archive extracted', [
                'zip_entries'  => $numFiles,
                'extract_path' => basename($extractPath),
            ]);

            // Get all extracted files recursively
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($extractPath, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $extractedFiles[] = $file->getPathname();
                }
            }

            Log::debug('📋 Extracted files cataloged', ['count' => count($extractedFiles)]);

            return [
                'files'       => $extractedFiles,
                'extractPath' => $extractPath,
            ];
        } else {
            throw new \Exception('Failed to open zip file');
        }
    }

    /**
     * Recursively delete a directory
     */
    protected function deleteDirectory(string $dir): bool
    {
        if (! is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        return rmdir($dir);
    }

    /**
     * Filter files based on allowed types
     */
    protected function filterFilesByType(array $files): array
    {
        $allowedExtensions = $this->config['file_types'] ?? ['txt', 'docx', 'pdf'];

        return array_filter($files, function ($file) use ($allowedExtensions) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            return in_array($extension, $allowedExtensions);
        });
    }

    /**
     * Get files from folder based on selected file types
     */
    protected function getFilesFromFolder(string $folderPath): array
    {
        $allowedExtensions = $this->config['file_types'] ?? ['txt', 'docx', 'pdf'];
        $files             = [];

        if (! is_dir($folderPath)) {
            throw new \Exception("Folder not found: {$folderPath}");
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folderPath)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $extension = strtolower($file->getExtension());
                if (in_array($extension, $allowedExtensions)) {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    /**
     * Process a single file
     */
    protected function processFile(string $filePath): Article
    {
        $fileName  = basename($filePath);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        Log::debug('🔧 Processing file', [
            'file'      => $fileName,
            'extension' => $extension,
        ]);

        // Extract content from file
        Log::debug('📖 Extracting content', ['file' => $fileName]);
        $rawContent    = $this->extractContent($filePath);
        $contentLength = strlen($rawContent);
        $wordCount     = str_word_count($rawContent);

        Log::info('✅ Content extracted', [
            'file'   => $fileName,
            'length' => number_format($contentLength) . ' chars',
            'words'  => number_format($wordCount),
        ]);

        // Get filename for potential title
        $filename = pathinfo($filePath, PATHINFO_FILENAME);

        // Process with AI if requested
        if ($this->config['use_ai'] ?? false) {
            Log::info('🤖 Processing with AI', [
                'file'           => $fileName,
                'content_length' => number_format($contentLength),
            ]);

            try {
                $articleData = $this->processWithAI($rawContent, $filename);

                Log::info('✅ AI processing complete', [
                    'file'           => $fileName,
                    'has_title'      => ! empty($articleData['title']),
                    'title'          => $articleData['title'] ?? null,
                    'has_subtitle'   => ! empty($articleData['subtitle']),
                    'has_categories' => ! empty($articleData['categories']),
                    'categories'     => $articleData['categories'] ?? [],
                    'has_images'     => ! empty($articleData['featured_image']),
                ]);
            } catch (\Exception $e) {
                Log::error('❌ Failed to parse AI response', [
                    $e->getMessage(),
                ]);

                Log::debug('📝 Processing without AI', ['file' => $fileName]);

                $articleData = [
                    'title'      => $this->cleanTitle($filename),
                    'subtitle'   => null,
                    'content'    => $this->convertToHtml($rawContent),
                    'categories' => [],
                ];
            }
        } else {
            Log::debug('📝 Processing without AI', ['file' => $fileName]);

            $articleData = [
                'title'      => $this->cleanTitle($filename),
                'subtitle'   => null,
                'content'    => $this->convertToHtml($rawContent),
                'categories' => [],
            ];
        }

        // Create the article
        Log::info('💾 Creating article in database', [
            'file'  => $fileName,
            'title' => $articleData['title'],
        ]);

        $article = $this->createArticle($articleData);

        Log::info('✨ Article created successfully', [
            'file'       => $fileName,
            'article_id' => $article->id,
            'slug'       => $article->slug ?? null,
            'title'      => $article->title,
        ]);

        return $article;
    }

    /**
     * Extract content from file based on type
     */
    protected function extractContent(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return match ($extension) {
            'txt'   => file_get_contents($filePath),
            'pdf'   => $this->extractFromPdf($filePath),
            'docx'  => $this->extractFromDocx($filePath),
            default => throw new \Exception("Unsupported file type: {$extension}"),
        };
    }

    /**
     * Extract content from PDF
     */
    protected function extractFromPdf(string $filePath): string
    {
        Log::debug('📄 Extracting from PDF', ['file' => basename($filePath)]);

        if (! class_exists('Smalot\PdfParser\Parser')) {
            throw new \Exception('PDF parser not installed. Run: composer require smalot/pdfparser');
        }

        $parser = new \Smalot\PdfParser\Parser();
        $pdf    = $parser->parseFile($filePath);
        $text   = $pdf->getText();

        Log::debug('✅ PDF extracted', [
            'file'   => basename($filePath),
            'length' => strlen($text),
        ]);

        return $text;
    }

    /**
     * Extract content from DOCX
     */
    protected function extractFromDocx(string $filePath): string
    {
        Log::debug('📄 Extracting from DOCX', ['file' => basename($filePath)]);

        if (! class_exists('PhpOffice\PhpWord\IOFactory')) {
            throw new \Exception('PhpWord not installed. Run: composer require phpoffice/phpword');
        }

        $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
        $content = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $content .= $element->getText() . "\n";
                } elseif (method_exists($element, 'getElements')) {
                    foreach ($element->getElements() as $childElement) {
                        if (method_exists($childElement, 'getText')) {
                            $content .= $childElement->getText() . "\n";
                        }
                    }
                }
            }
        }

        Log::debug('✅ DOCX extracted', [
            'file'   => basename($filePath),
            'length' => strlen($content),
        ]);

        return $content;
    }

    /**
     * Process content with AI
     */
    protected function processWithAI(string $content, string $filename): array
    {
        $prompt = $this->buildAIPrompt($content, $filename);

        $requestData = [
            'model'       => config('services.openai.default_model', 'gpt-4'),
            'messages'    => [
                [
                    'role'    => 'system',
                    'content' => 'You are an expert content editor and formatter. You help format articles for publication. Always respond with valid JSON.',
                ],
                [
                    'role'    => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => 0.7,
        ];

        // Only add response_format for models that support it
        $model = config('services.openai.default_model', 'gpt-4');
        if ($this->supportsJsonMode($model)) {
            $requestData['response_format'] = ['type' => 'json_object'];
        }

        Log::info('🤖 Calling OpenAI API', [
            'model'              => $model,
            'content_length'     => strlen($content),
            'prompt_length'      => strlen($prompt),
            'supports_json_mode' => $this->supportsJsonMode($model),
        ]);

        $response = openai()->chat()->create($requestData);

        $responseContent = $response['choices'][0]['message']['content'];

        Log::info('✅ OpenAI API response received', [
            'model'           => $model,
            'response_length' => strlen($responseContent),
            'tokens_used'     => $response['usage'] ?? null,
        ]);

        // Extract JSON from response (handles both pure JSON and markdown-wrapped JSON)
        $aiResponse = $this->extractJsonFromResponse($responseContent);

        if (! $aiResponse) {
            Log::error('❌ Failed to parse AI response', [
                'response_preview' => substr($responseContent, 0, 200),
            ]);
            throw new \Exception('Failed to parse AI response as JSON. Response: ' . substr($responseContent, 0, 200));
        }

        Log::debug('✅ AI response parsed successfully', [
            'has_title'      => isset($aiResponse['title']),
            'has_content'    => isset($aiResponse['content']),
            'has_categories' => isset($aiResponse['categories']),
        ]);

        // Generate images if requested
        if ($this->config['add_images'] ?? false) {
            Log::info('🎨 Processing images', [
                'inline_images' => count($aiResponse['image_prompts'] ?? []),
                'has_featured'  => ! empty($aiResponse['featured_image_prompt']),
            ]);

            $aiResponse = $this->addImagesToContent($aiResponse, $this->config['tenant_id'] ?? null);
        }

        return $aiResponse;
    }

    /**
     * Get configuration for trait methods
     */
    protected function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Build AI prompt based on configuration
     */
    protected function buildAIPrompt(string $content, string $filename): string
    {
        $prompt = "I have the following article content:\n\n{$content}\n\n";
        $prompt .= "Please format this article and return ONLY a valid JSON object (no other text) with the following structure:\n";
        $prompt .= "{\n";

        if ($this->config['generate_title'] ?? true) {
            $prompt .= '  "title": "An engaging title for the article",' . "\n";
        } else {
            $prompt .= '  "title": "' . $this->cleanTitle($filename) . '",' . "\n";
        }

        if ($this->config['generate_subtitle'] ?? false) {
            $prompt .= '  "subtitle": "A compelling subtitle",' . "\n";
        }

        $prompt .= '  "content": "The formatted content in HTML",' . "\n";

        if ($this->config['generate_categories'] ?? false) {
            $prompt .= '  "categories": ["category1", "category2"],' . "\n";
        }

        if ($this->config['add_images'] ?? false) {
            $imageSource = $this->config['image_source'] ?? 'dalle';

            if ($imageSource === 'openverse') {
                $prompt .= '  "image_queries": ["search query 1", "search query 2"],' . "\n";
                $prompt .= '  "featured_image_query": "search query for featured image",' . "\n";
            } else {
                $prompt .= '  "image_prompts": ["Description for image 1", "Description for image 2"],' . "\n";
                $prompt .= '  "featured_image_prompt": "Description for the featured image",' . "\n";
            }

            $prompt .= '  "image_positions": [150, 450] // Approximate character positions where images should be inserted' . "\n";
        }

        $prompt .= "}\n\n";

        // Add formatting instructions
        if (! empty($this->config['custom_prompt'])) {
            $prompt .= "Additional instructions: " . $this->config['custom_prompt'] . "\n\n";
        } elseif (! empty($this->config['format_style'])) {
            $prompt .= "Format the article according to {$this->config['format_style']} style guide.\n\n";
        }

        if ($this->config['add_images'] ?? false) {
            $imageSource = $this->config['image_source'] ?? 'dalle';

            if ($imageSource === 'openverse') {
                $prompt .= "For images: Suggest specific, concise search queries to find relevant images on OpenVerse (a database of openly licensed images). ";
                $prompt .= "Queries should be 2-4 words describing the visual content needed (e.g., 'mountain landscape sunset', 'business team meeting'). ";
            } else {
                $prompt .= "For images: Suggest specific, descriptive prompts for DALL-E to generate relevant images. ";
            }

            $prompt .= "IMPORTANT: Images must ONLY be placed at natural break points in the content. ";
            $prompt .= "Valid positions are: between paragraphs, between sections, between a heading and body text, or between two complete sentences. ";
            $prompt .= "Images must NEVER interrupt a sentence midway or appear in the middle of a sentence. ";
            $prompt .= "Suggest character positions that correspond to these natural breaks.\n";
        }

        return $prompt;
    }

    /**
     * Create article with all data
     */
    protected function createArticle(array $data): Article
    {
        $publishedAt = $this->config['published_at'] ?? now();

        Log::info('💾 Creating article record', [
            'title'        => $data['title'],
            'author_id'    => $this->config['author_id'] ?? auth()->id(),
            'published_at' => $publishedAt,
        ]);

        $article = Article::create([
            'title'        => $data['title'],
            'subtitle'     => $data['subtitle'] ?? null,
            'content'      => $data['content'],
            'author_id'    => $this->config['author_id'] ?? auth()->id(),
            'published_at' => $publishedAt,
            'unpublished'  => false,
        ]);

        if (isset($this->config['tenant_id'])) {
            $article->associateWithTenant($this->config['tenant_id']);
        }

        Log::info('✅ Article record created', [
            'article_id' => $article->id,
            'slug'       => $article->slug ?? null,
        ]);

        // Attach featured image if exists
        if (! empty($data['featured_image']) && $data['featured_image'] instanceof FileModel) {
            Log::debug('🖼️ Attaching featured image', [
                'article_id' => $article->id,
                'file_id'    => $data['featured_image']->id,
            ]);

            $article->attachFile($data['featured_image']->id);

            Log::info('✅ Featured image attached', [
                'article_id' => $article->id,
                'file_id'    => $data['featured_image']->id,
            ]);
        }

        // Create and attach categories
        if (! empty($data['categories'])) {
            Log::info('🏷️ Attaching categories', [
                'article_id' => $article->id,
                'categories' => $data['categories'],
            ]);

            $this->attachCategories($article, $data['categories'], $this->config['tenant_id'] ?? null);

            Log::info('✅ Categories attached', [
                'article_id' => $article->id,
                'count'      => count($data['categories']),
            ]);
        }

        return $article;
    }

    /**
     * Convert plain text to basic HTML
     */
    protected function convertToHtml(string $text): string
    {
        $paragraphs = explode("\n\n", $text);
        $html       = '';

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (! empty($paragraph)) {
                $html .= '<p>' . nl2br(htmlspecialchars($paragraph)) . '</p>';
            }
        }

        return $html;
    }

    /**
     * Get processing statistics
     */
    public function getStats(): array
    {
        return $this->stats;
    }
}
