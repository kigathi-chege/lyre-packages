<?php

namespace Lyre\Content\Jobs;

use Lyre\Content\Services\ArticleUploadService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class ProcessArticleUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour timeout
    public $tries = 1; // Don't retry automatically

    protected array $uploadedFiles;
    protected array $config;
    protected ?int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $uploadedFiles, array $config, ?int $userId = null)
    {
        $this->uploadedFiles = $uploadedFiles;
        $this->config = $config;
        $this->userId = $userId;

        Log::info('📦 Article Upload Job Created', [
            'files_count' => count($uploadedFiles),
            'files' => array_map(fn($f) => basename($f), $uploadedFiles),
            'user_id' => $userId,
            'config' => [
                'use_ai' => $config['use_ai'] ?? false,
                'add_images' => $config['add_images'] ?? false,
                'file_types' => $config['file_types'] ?? [],
            ],
        ]);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('🚀 Article Upload Job Started', [
            'job_id' => $this->job->getJobId(),
            'files_count' => count($this->uploadedFiles),
            'user_id' => $this->userId,
        ]);

        $stats = null;

        try {
            $service = new ArticleUploadService();

            Log::info('🔄 Processing uploaded files', [
                'files' => array_map(fn($f) => basename($f), $this->uploadedFiles),
            ]);

            $stats = $service->processUploadedFiles($this->uploadedFiles, $this->config);

            Log::info('✅ Article Upload Job Completed Successfully', [
                'job_id' => $this->job->getJobId(),
                'stats' => $stats,
            ]);

            // Send success notification to user
            $this->sendNotification(
                'success',
                'Articles Processed Successfully',
                "Processed: {$stats['processed']}, Successful: {$stats['successful']}, Failed: {$stats['failed']}"
            );

            // Send individual error notifications
            if (!empty($stats['errors'])) {
                foreach ($stats['errors'] as $error) {
                    $this->sendNotification(
                        'warning',
                        'Failed to process file',
                        "{$error['file']}: {$error['error']}"
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('❌ Article Upload Job Failed', [
                'job_id' => $this->job->getJobId(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'stats' => $stats,
            ]);

            $this->sendNotification(
                'danger',
                'Article Upload Failed',
                $e->getMessage()
            );

            throw $e;
        } finally {
            // Clean up temporary files
            Log::info('🧹 Cleaning up temporary files', [
                'files_count' => count($this->uploadedFiles),
            ]);

            foreach ($this->uploadedFiles as $file) {
                if (Storage::disk('local')->exists($file)) {
                    Storage::disk('local')->delete($file);
                    Log::debug('🗑️ Deleted temporary file', ['file' => basename($file)]);
                }
            }

            Log::info('✨ Cleanup completed');
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('💥 Article Upload Job Failed Permanently', [
            'job_id' => $this->job?->getJobId(),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'user_id' => $this->userId,
        ]);

        $this->sendNotification(
            'danger',
            'Article Upload Job Failed',
            'The article processing job failed. Check logs for details: ' . $exception->getMessage()
        );
    }

    /**
     * Send notification to user
     */
    protected function sendNotification(string $type, string $title, string $body): void
    {
        if (!$this->userId) {
            Log::warning('⚠️ Cannot send notification: No user ID', [
                'title' => $title,
                'body' => $body,
            ]);
            return;
        }

        try {
            Notification::make()
                ->{$type}()
                ->title($title)
                ->body($body)
                ->sendToDatabase(\App\Models\User::find($this->userId));

            Log::debug('📬 Notification sent', [
                'type' => $type,
                'title' => $title,
                'user_id' => $this->userId,
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Failed to send notification', [
                'error' => $e->getMessage(),
                'type' => $type,
                'title' => $title,
            ]);
        }
    }
}
