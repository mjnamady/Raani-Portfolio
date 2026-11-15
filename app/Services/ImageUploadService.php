<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ImageUploadService
{
    /**
     * Rough bytes GD needs per pixel for a decoded truecolor image, plus headroom.
     */
    protected const BYTES_PER_PIXEL = 5;

    /**
     * Crop the uploaded image to fill the given size and store it as WebP under a
     * random name on the public disk.
     *
     * Re-encoding through GD also strips metadata and anything embedded in the original file.
     * The caller deletes the previous file once the new path has been saved, see delete().
     *
     * @return string The stored path, relative to the public disk.
     *
     * @throws RuntimeException When the image cannot be decoded, processed or stored.
     */
    public function storeCropped(UploadedFile $file, string $directory, int $width, int $height): string
    {
        $contents = $this->encode($file, $width, $height);

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';

        if (! Storage::disk('public')->put($path, $contents)) {
            throw new RuntimeException('The processed image could not be stored.');
        }

        return $path;
    }

    /**
     * Delete a previously stored image, if there is one.
     */
    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Decode, crop and re-encode the image as WebP.
     */
    protected function encode(UploadedFile $file, int $width, int $height): string
    {
        $size = @getimagesize($file->getRealPath());

        if ($size === false) {
            throw new RuntimeException('The uploaded file is not a readable image.');
        }

        $this->ensureEnoughMemory($size[0] * $size[1]);

        try {
            $source = imagecreatefromstring($file->get());

            if ($source === false) {
                throw new RuntimeException('The uploaded file is not a readable image.');
            }

            $canvas = $this->cropToFill($source, $width, $height);

            ob_start();
            $encoded = imagewebp($canvas, null, 85);
            $contents = (string) ob_get_clean();
        } catch (RuntimeException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new RuntimeException('The uploaded image could not be processed.', previous: $e);
        } finally {
            // GdImage objects are freed when released; drop them before the encoded string is returned.
            unset($source, $canvas);
        }

        if (! $encoded || $contents === '') {
            throw new RuntimeException('The uploaded image could not be processed.');
        }

        return $contents;
    }

    /**
     * Refuse to decode images that would exceed PHP's memory limit (a fatal error that cannot be caught).
     */
    protected function ensureEnoughMemory(int $pixels): void
    {
        $limit = $this->memoryLimitInBytes();

        if ($limit === null) {
            return;
        }

        $needed = memory_get_usage() + ($pixels * self::BYTES_PER_PIXEL);

        if ($needed > $limit) {
            throw new RuntimeException('The uploaded image is too large to process.');
        }
    }

    /**
     * PHP's memory_limit in bytes, or null when unlimited.
     */
    protected function memoryLimitInBytes(): ?int
    {
        $limit = trim((string) ini_get('memory_limit'));

        if ($limit === '' || $limit === '-1') {
            return null;
        }

        $value = (int) $limit;

        return match (strtolower(substr($limit, -1))) {
            'g' => $value * 1024 ** 3,
            'm' => $value * 1024 ** 2,
            'k' => $value * 1024,
            default => $value,
        };
    }

    /**
     * Scale the image to cover the target size and crop the centre.
     */
    protected function cropToFill(GdImage $source, int $width, int $height): GdImage
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        $scale = max($width / $sourceWidth, $height / $sourceHeight);
        $cropWidth = (int) round($width / $scale);
        $cropHeight = (int) round($height / $scale);
        $cropX = (int) round(($sourceWidth - $cropWidth) / 2);
        $cropY = (int) round(($sourceHeight - $cropHeight) / 2);

        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));

        imagecopyresampled($canvas, $source, 0, 0, $cropX, $cropY, $width, $height, $cropWidth, $cropHeight);

        return $canvas;
    }
}
