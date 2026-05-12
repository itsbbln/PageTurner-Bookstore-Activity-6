<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Storage;

class ImageService
{
    const COVER_WIDTH = 300;
    const COVER_HEIGHT = 450;
    const THUMBNAIL_WIDTH = 150;
    const THUMBNAIL_HEIGHT = 200;

    /**
     * Store and process a book cover image
     */
    public static function storeBookCover(UploadedFile $file): string
    {
        // Store the original image
        $path = $file->store('covers', 'public');

        // Try to resize the image if GD is available
        if (extension_loaded('gd')) {
            try {
                self::resizeImage($path);
            } catch (\Exception $e) {
                \Log::warning('Image resizing failed, storing original: ' . $e->getMessage());
            }
        }

        return $path;
    }

    /**
     * Resize an image to fit cover dimensions
     */
    private static function resizeImage(string $path): void
    {
        $fullPath = storage_path('app/public/' . $path);

        if (!file_exists($fullPath)) {
            return;
        }

        // Get image dimensions
        $imageInfo = @getimagesize($fullPath);
        if ($imageInfo === false) {
            return; // Not a valid image
        }

        list($width, $height) = $imageInfo;

        // Create a new image resource
        if ($imageInfo[2] == IMAGETYPE_JPEG) {
            $source = imagecreatefromjpeg($fullPath);
        } elseif ($imageInfo[2] == IMAGETYPE_PNG) {
            $source = imagecreatefrompng($fullPath);
        } elseif ($imageInfo[2] == IMAGETYPE_WEBP) {
            $source = imagecreatefromwebp($fullPath);
        } else {
            return; // Unsupported format
        }

        if (!$source) {
            return;
        }

        // Calculate scaling to fit within bounds while maintaining aspect ratio
        $ratioWidth = self::COVER_WIDTH / $width;
        $ratioHeight = self::COVER_HEIGHT / $height;
        $ratio = min($ratioWidth, $ratioHeight);

        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        // Create resized image
        $resized = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG
        if ($imageInfo[2] == IMAGETYPE_PNG) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }

        imagecopyresampled(
            $resized,
            $source,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $width,
            $height
        );

        // Save the resized image
        if ($imageInfo[2] == IMAGETYPE_JPEG) {
            imagejpeg($resized, $fullPath, 85);
        } elseif ($imageInfo[2] == IMAGETYPE_PNG) {
            imagepng($resized, $fullPath, 8);
        } elseif ($imageInfo[2] == IMAGETYPE_WEBP) {
            imagewebp($resized, $fullPath, 85);
        }

        imagedestroy($source);
        imagedestroy($resized);
    }

    /**
     * Get placeholder image URL
     */
    public static function getPlaceholder(): string
    {
        return asset('images/placeholder-book.png');
    }

    /**
     * Delete an image file
     */
    public static function deleteImage(string $path): bool
    {
        return Storage::disk('public')->delete($path);
    }
}
