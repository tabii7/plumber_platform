<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ImageUploadService
{
    /**
     * Download and store WhatsApp image
     */
    public function downloadWhatsAppImage(string $imageUrl, string $requestId): ?array
    {
        try {
            // Download image from WhatsApp
            $response = Http::timeout(30)->get($imageUrl);
            
            if (!$response->successful()) {
                Log::error('Failed to download WhatsApp image', [
                    'url' => $imageUrl,
                    'status' => $response->status(),
                    'request_id' => $requestId
                ]);
                return null;
            }

            // Get image content and determine file extension
            $imageContent = $response->body();
            $contentType = $response->header('Content-Type');
            $extension = $this->getExtensionFromContentType($contentType);
            
            if (!$extension) {
                $extension = 'jpg'; // Default to jpg if content type is unknown
            }

            // Generate unique filename
            $filename = 'wa_request_' . $requestId . '_' . Str::random(10) . '.' . $extension;
            $path = 'whatsapp-images/' . $filename;

            // Store image locally
            if (Storage::disk('public')->put($path, $imageContent)) {
                $fullUrl = Storage::disk('public')->url($path);
                
                return [
                    'filename' => $filename,
                    'path' => $path,
                    'url' => $fullUrl,
                    'size' => strlen($imageContent),
                    'content_type' => $contentType,
                    'uploaded_at' => now()->toISOString()
                ];
            }

            Log::error('Failed to store WhatsApp image', [
                'request_id' => $requestId,
                'path' => $path
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('Error downloading WhatsApp image', [
                'url' => $imageUrl,
                'request_id' => $requestId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get file extension from content type
     */
    protected function getExtensionFromContentType(string $contentType): ?string
    {
        $mimeMap = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/bmp' => 'bmp'
        ];

        return $mimeMap[$contentType] ?? null;
    }

    /**
     * Delete image files when request is deleted
     */
    public function deleteRequestImages(array $images): bool
    {
        try {
            foreach ($images as $image) {
                if (isset($image['path'])) {
                    Storage::disk('public')->delete($image['path']);
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Error deleting request images', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get image preview URL for WhatsApp display
     */
    public function getImagePreviewUrl(array $image): string
    {
        return $image['url'] ?? '';
    }

    /**
     * Validate image size and type
     */
    public function validateImage(array $image): bool
    {
        $maxSize = 10 * 1024 * 1024; // 10MB max
        
        if (isset($image['size']) && $image['size'] > $maxSize) {
            return false;
        }

        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        
        if (isset($image['content_type']) && !in_array($image['content_type'], $allowedTypes)) {
            return false;
        }

        return true;
    }
}



