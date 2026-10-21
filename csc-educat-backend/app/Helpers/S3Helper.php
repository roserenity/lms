<?php

namespace App\Helpers;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Storage;

class S3Helper
{
    /**
     * Generate a downloadable presigned S3 URL.
     *
     * @param string $fileUrlOrPath
     * @param int $expiresInSeconds
     * @return string|null
     */
    public static function generatePresignedDownloadUrl(string $fileUrlOrPath, int $expiresInSeconds = 60): ?string
    {
        
        $path = ltrim(parse_url($fileUrlOrPath, PHP_URL_PATH), '/');
        $parts = explode('/', $path);
        $file_path = implode('/', array_slice($parts, 1));

        $expires = 60;
        // Create temporary URL
        $temporary_url = Storage::disk('s3')->temporaryUrl(
            $file_path,
            now()->addMinutes($expires),
            [
                'ResponseContentType' => 'application/octet-stream',
                'ResponseContentDisposition' => 'attachment; filename="' . basename($file_path) . '"',
            ]
        );

        return response()->json([
            'result' => 'success',
            'downloadable_url' => $temporary_url,
        ]);

    }
}
