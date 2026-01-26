<?php

namespace Lyre\File\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Lyre\File\Models\File as FileModel;

trait UploadsFilesFromUrl
{
    /**
     * Download file from URL and upload using FileRepository
     *
     * @param string $url The URL to download from
     * @param string|null $name Optional custom name for the file
     * @param string|null $description Optional description
     * @param array|null $metadata Optional metadata (will be JSON encoded)
     * @param int $maxSizeBytes Maximum file size in bytes (default: 2MB for memory efficiency)
     * @return FileModel|null The uploaded file model or null on failure
     */
    protected function uploadFileFromUrl(
        string $url,
        ?string $name = null,
        ?string $description = null,
        ?array $metadata = null,
        int $maxSizeBytes = 2097152 // 2MB default (safe for image resizing in 128MB memory)
    ): ?FileModel {
        $tempPath = null;

        try {
            Log::debug('📥 Downloading file from URL', [
                'url' => $url,
                'name' => $name,
                'max_size' => number_format($maxSizeBytes) . ' bytes',
            ]);

            // Use stream context to get headers first (check size before downloading)
            $context = stream_context_create([
                'http' => [
                    'method' => 'HEAD',
                    'timeout' => 10,
                ]
            ]);

            $headers = @get_headers($url, true, $context);
            $contentLength = null;

            if ($headers) {
                // Handle both single value and array of values for Content-Length
                $contentLength = is_array($headers['Content-Length'] ?? null)
                    ? end($headers['Content-Length'])
                    : ($headers['Content-Length'] ?? null);
            }

            if ($contentLength && $contentLength > $maxSizeBytes) {
                Log::warning('⚠️ File too large, skipping', [
                    'url' => $url,
                    'size' => number_format($contentLength) . ' bytes',
                    'max_size' => number_format($maxSizeBytes) . ' bytes',
                ]);
                return null;
            }

            // Download the file content with memory limit check
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 30,
                ]
            ]);

            $contents = @file_get_contents($url, false, $context);

            if ($contents === false) {
                Log::error('Failed to download file from URL', ['url' => $url]);
                return null;
            }

            $fileSize = strlen($contents);

            // Double-check size after download
            if ($fileSize > $maxSizeBytes) {
                Log::warning('⚠️ Downloaded file too large, skipping', [
                    'url' => $url,
                    'size' => number_format($fileSize) . ' bytes',
                    'max_size' => number_format($maxSizeBytes) . ' bytes',
                ]);
                return null;
            }

            // Create a temporary file
            $tempPath = sys_get_temp_dir() . '/' . uniqid('upload_') . '_' . ($name ?? 'file');
            file_put_contents($tempPath, $contents);

            // Free memory immediately
            unset($contents);

            // Get mime type
            $mimeType = mime_content_type($tempPath);

            // Create UploadedFile instance
            $uploadedFile = new UploadedFile(
                $tempPath,
                basename($tempPath),
                $mimeType,
                null,
                true // Set test mode to true to avoid is_uploaded_file() validation
            );

            Log::debug('⬆️ Uploading to FileRepository', [
                'size' => number_format($fileSize) . ' bytes',
                'mime_type' => $mimeType,
            ]);

            // Upload using FileRepository
            $file = fileRepository()->uploadFile(
                file: $uploadedFile,
                name: $name,
                description: $description,
                originalName: $name,
                metadata: $metadata ? json_encode($metadata) : null
            );

            // Clean up temporary file
            @unlink($tempPath);

            Log::debug('✅ File uploaded successfully', [
                'file_id' => $file->id,
                'path' => $file->path,
                'size' => number_format($file->size) . ' bytes',
            ]);

            return $file;
        } catch (\Throwable $e) {
            Log::error('❌ Failed to upload file from URL', [
                'url' => $url,
                'error' => $e->getMessage(),
                'type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Clean up temp file if it exists
            if ($tempPath && file_exists($tempPath)) {
                @unlink($tempPath);
            }

            return null;
        }
    }

    /**
     * Associate a file with the current tenant
     *
     * @param FileModel $file The file to associate
     * @param int|null $tenantId Optional tenant ID (uses current tenant if not provided)
     * @return void
     */
    protected function associateFileWithTenant(FileModel $file, ?int $tenantId = null): void
    {
        $tenantId = $tenantId ?? tenant()?->id;

        if ($tenantId) {
            $file->associateWithTenant($tenantId);

            Log::debug('🏢 File associated with tenant', [
                'file_id' => $file->id,
                'tenant_id' => $tenantId,
            ]);
        }
    }
}
