<?php

namespace Lyre\Content\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Filament\Notifications\Notification;
use Lyre\Content\Concerns\HandlesArticleImages;
use Lyre\Content\Concerns\InteractsWithOpenAI;
use Lyre\Content\Concerns\ManagesArticleData;
use Lyre\Content\Services\ArticleUploadService;
use Lyre\Content\Models\Article;
use App\Models\User;

class ProcessArticleFormatting implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use InteractsWithOpenAI, HandlesArticleImages, ManagesArticleData;

    public $timeout = 3600; // 1 hour
    public $tries = 1;

    protected array $articleIds;
    protected array $config;
    protected ?int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $articleIds, array $config, ?int $userId = null)
    {
        $this->articleIds = $articleIds;
        $this->config = $config;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('🚀 Article Formatting Job Started', [
            'articles_count' => count($this->articleIds),
            'user_id' => $this->userId,
            'config' => $this->config,
        ]);

        $stats = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        $service = new ArticleUploadService();

        foreach ($this->articleIds as $index => $articleId) {
            $articleNum = $index + 1;
            Log::info("📝 Processing article " . $articleNum . "/" . count($this->articleIds), [
                'article_id' => $articleId,
            ]);

            try {
                $article = Article::find($articleId);

                if (!$article) {
                    Log::warning('⚠️ Article not found', ['article_id' => $articleId]);
                    $stats['failed']++;
                    $stats['errors'][] = "Article ID {$articleId} not found";
                    continue;
                }

                if ($article->is_ai_formatted && !($this->config['force_reformat'] ?? false)) {
                    Log::info('⏭️ Skipping already formatted article', [
                        'article_id' => $articleId,
                        'title' => $article->title,
                    ]);
                    $stats['processed']++;
                    continue;
                }

                // Format the article using AI
                $formattedData = $this->formatArticleWithAI($article, $service);

                // Update the article
                $this->updateArticle($article, $formattedData);

                $stats['successful']++;
                Log::info('✅ Article formatted successfully', [
                    'article_id' => $articleId,
                    'title' => $article->title,
                ]);
            } catch (\Exception $e) {
                $stats['failed']++;
                $stats['errors'][] = "Article ID {$articleId}: {$e->getMessage()}";
                Log::error('❌ Failed to format article', [
                    'article_id' => $articleId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            $stats['processed']++;
        }

        Log::info('✅ Article Formatting Job Completed', $stats);

        // Send notification to user
        if ($this->userId) {
            $user = User::find($this->userId);
            if ($user) {
                NotificationFacade::send($user,
                    Notification::make()
                        ->success()
                        ->title('AI Formatting Complete')
                        ->body("Processed {$stats['processed']} articles. {$stats['successful']} successful, {$stats['failed']} failed.")
                        ->toDatabase()
                );
            }
        }
    }

    /**
     * Format article content using AI
     */
    protected function formatArticleWithAI(Article $article, ArticleUploadService $service): array
    {
        Log::info('🤖 Formatting article with AI', [
            'article_id' => $article->id,
            'title' => $article->title,
            'content_length' => strlen($article->content),
        ]);

        // Build the formatting prompt
        $prompt = $this->buildFormattingPrompt($article);

        // Create request data
        $requestData = [
            'model' => config('services.openai.default_model', 'gpt-4'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert content editor and formatter. You help format articles for publication. Always respond with valid JSON.',
                ],
                [
                    'role' => 'user',
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

        Log::info('🤖 Calling OpenAI API for formatting', [
            'model' => $model,
            'content_length' => strlen($article->content),
        ]);

        $response = openai()->chat()->create($requestData);
        $responseContent = $response['choices'][0]['message']['content'];

        Log::info('✅ OpenAI API response received', [
            'response_length' => strlen($responseContent),
            'tokens_used' => $response['usage'] ?? null,
        ]);

        // Extract JSON from response
        $aiResponse = $this->extractJsonFromResponse($responseContent);

        if (!$aiResponse) {
            throw new \Exception('Failed to parse AI response as JSON');
        }

        // Generate images if requested
        if ($this->config['add_images'] ?? false) {
            Log::info('🎨 Processing images for article', [
                'article_id' => $article->id,
                'add_featured' => $this->config['add_featured_image'] ?? true,
                'add_inline' => $this->config['add_inline_images'] ?? true,
            ]);

            $aiResponse = $this->addImagesToContent($aiResponse);
        }

        return $aiResponse;
    }

    /**
     * Build the formatting prompt based on config
     */
    protected function buildFormattingPrompt(Article $article): string
    {
        $style = $this->config['formatting_style'] ?? 'conversational';
        $customPrompt = $this->config['custom_formatting_prompt'] ?? '';

        $styleInstructions = match ($style) {
            'chicago' => 'Format the article according to the Chicago Manual of Style. Use proper citations, formatting, and structure.',
            'apa' => 'Format the article according to APA Style guidelines. Use appropriate headings, citations, and structure.',
            'mla' => 'Format the article according to MLA Style guidelines. Use proper formatting and citations.',
            'ap' => 'Format the article according to AP Style guidelines. Use AP-style formatting, abbreviations, and structure.',
            'conversational' => 'Format the article in a conversational, engaging blog style. Make it readable and engaging while maintaining professionalism.',
            'custom' => $customPrompt,
            default => 'Format the article to improve readability and engagement.',
        };

        $prompt = <<<PROMPT
You are tasked with formatting an existing article. Here is the current content:

Title: {$article->title}
Subtitle: {$article->subtitle}

Content:
{$article->content}

Formatting Instructions:
{$styleInstructions}

Please format this article and return a JSON response with the following structure:
{
    "title": "formatted title (or keep original if regenerate_title is false)",
    "subtitle": "formatted subtitle (or keep original if regenerate_subtitle is false)",
    "content": "the formatted HTML content",
    "categories": ["category1", "category2"],
    "image_prompts": ["prompt for inline image 1", "prompt for inline image 2"],
    "featured_image_prompt": "prompt for the featured image"
}

Additional requirements:
PROMPT;

        if (!($this->config['regenerate_title'] ?? false)) {
            $prompt .= "\n- Keep the original title: \"{$article->title}\"";
        } else {
            $prompt .= "\n- Create a compelling, SEO-friendly title";
        }

        if (!($this->config['regenerate_subtitle'] ?? false)) {
            $prompt .= "\n- Keep the original subtitle" . ($article->subtitle ? ": \"{$article->subtitle}\"" : '');
        } else {
            $prompt .= "\n- Create an engaging subtitle";
        }

        if ($this->config['update_categories'] ?? true) {
            $prompt .= "\n- Suggest 2-4 relevant categories based on the content";
        } else {
            $prompt .= "\n- Keep existing categories or leave empty if none exist";
        }

        if ($this->config['add_images'] ?? false) {
            if ($this->config['add_inline_images'] ?? true) {
                $prompt .= "\n- Provide 2-4 prompts for inline images that would enhance the article";
                $prompt .= "\n- CRITICAL: Inline images must ONLY be placed at natural break points (between paragraphs, sections, headings, or complete sentences)";
                $prompt .= "\n- Images must NEVER interrupt a sentence midway or appear in the middle of a sentence";
            }
            if ($this->config['add_featured_image'] ?? true) {
                $prompt .= "\n- Provide a prompt for a featured image that represents the article";
            }
        } else {
            $prompt .= "\n- Do not include image prompts (leave arrays empty)";
        }

        return $prompt;
    }

    /**
     * Update the article with formatted data
     */
    protected function updateArticle(Article $article, array $data): void
    {
        Log::info('💾 Updating article with formatted data', [
            'article_id' => $article->id,
            'has_new_title' => isset($data['title']),
            'has_new_content' => isset($data['content']),
        ]);

        $updateData = [];

        if ($this->config['regenerate_title'] ?? false) {
            $updateData['title'] = $data['title'] ?? $article->title;
        }

        if ($this->config['regenerate_subtitle'] ?? false) {
            $updateData['subtitle'] = $data['subtitle'] ?? $article->subtitle;
        }

        $updateData['content'] = $data['content'] ?? $article->content;
        $updateData['is_ai_formatted'] = true;

        $article->update($updateData);

        // Update categories if requested
        if (($this->config['update_categories'] ?? true) && !empty($data['categories'])) {
            $this->updateCategories($article, $data['categories']);
        }

        // Update featured image if present
        if (!empty($data['featured_image_url'])) {
            $this->attachFeaturedImageFromUrl($article, $data['featured_image_url']);
        }

        Log::info('✅ Article updated successfully', [
            'article_id' => $article->id,
        ]);
    }

    /**
     * Get config array (required by traits)
     */
    protected function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('💥 Article Formatting Job Failed Permanently', [
            'articles_count' => count($this->articleIds),
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        if ($this->userId) {
            $user = User::find($this->userId);
            if ($user) {
                NotificationFacade::send($user,
                    Notification::make()
                        ->danger()
                        ->title('AI Formatting Failed')
                        ->body('The article formatting job failed: ' . $exception->getMessage())
                        ->toDatabase()
                );
            }
        }
    }
}
