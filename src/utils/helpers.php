<?php

if (!function_exists('get_file_name_without_extension')) {
    function get_file_name_without_extension($file, $name = null)
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = $name ? $name : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        if (substr_compare($fileName, $extension, -strlen($extension)) === 0) {
            $fileName = str_replace($extension, '', $fileName);
            if (substr($fileName, -1) === '.') {
                $fileName = substr($fileName, 0, -1);
            }
        }
        return $fileName;
    }
}

if (!function_exists('get_file_extension')) {
    function get_file_extension($file, $extension = null)
    {
        $extension = $extension ? $extension : ($file->getClientOriginalExtension() ? $file->getClientOriginalExtension() : "jpg");
        return strtolower($extension);
    }
}

if (!function_exists('generate_resized_versions')) {
    function generate_resized_versions($file, $mimeType)
    {
        try {
            // Check if format is supported by GD
            $extension = strtolower(pathinfo($file->getRealPath(), PATHINFO_EXTENSION) ?: $file->getClientOriginalExtension());
            $unsupportedFormats = ['avif']; // AVIF is not supported by GD

            if (in_array($extension, $unsupportedFormats)) {
                logger("File resize not supported.");
                // Skip resizing for unsupported formats
                return [];
            }

            // Skip very large images to prevent memory issues
            // $fileSize = filesize($file->getRealPath());
            // if ($fileSize > 5 * 1024 * 1024) { // 5MB limit
            //     return [];
            // }

            $image = \Intervention\Image\Laravel\Facades\Image::read($file->getRealPath());
            $disk = config('filesystems.default');

            $variants = [];
            $sizes = [
                'sm' => 150,
                'md' => 300,
                'lg' => 600,
            ];

            foreach ($sizes as $label => $width) {
                $resized = $image->scale(width: $width);

                $filename = 'uploads/' . $mimeType . '/' . \Illuminate\Support\Str::uuid() . "_{$label}." . $file->getClientOriginalExtension();

                \Illuminate\Support\Facades\Storage::disk($disk)->put($filename, (string) $resized->encode());

                $variants[$label] = $filename;

                // Free memory after each resize
                unset($resized);
            }

            // Free memory
            unset($image);

            return $variants;
        } catch (\Exception $e) {
            // If resizing fails (e.g., unsupported format or memory issue), return empty array
            // The file will still be uploaded, just without resized versions
            return [];
        }
    }
}
