<?php

namespace App\Services;

use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use RuntimeException;
use Throwable;

class ImageUploadService
{
    /**
     * Rough bytes GD needs per pixel for a decoded truecolor image, plus headroom.
     */
    protected const BYTES_PER_PIXEL = 5;

    protected const WEBP_QUALITY = 85;

    /**
     * Largest width or height accepted from an upload. Bigger photos are scaled down by
     * cover(); the cap and ensureEnoughMemory() guard against decompression bombs.
     */
    public const MAX_SOURCE_DIMENSION = 12000;

    /**
     * Memory allowed while decoding an upload, so full-size phone photos (12-50 MP) fit.
     * Raised for the current request only; php.ini is left alone.
     */
    protected const PROCESSING_MEMORY_LIMIT = '512M';

    /**
     * The "dimensions" validation rule for an image at least $minWidth x $minHeight pixels
     * ($minHeight defaults to $minWidth).
     */
    public static function dimensionsRule(int $minWidth, ?int $minHeight = null): string
    {
        $minHeight ??= $minWidth;
        $max = static::MAX_SOURCE_DIMENSION;

        return "dimensions:min_width={$minWidth},min_height={$minHeight},max_width={$max},max_height={$max}";
    }

    /**
     * The validation message that goes with dimensionsRule().
     */
    public static function dimensionsMessage(string $subject, int $minWidth, ?int $minHeight = null): string
    {
        $minHeight ??= $minWidth;
        $max = number_format(static::MAX_SOURCE_DIMENSION);

        return "{$subject} must be at least {$minWidth}×{$minHeight} pixels and at most {$max}×{$max}. Larger images are resized automatically.";
    }

    /**
     * Crop the uploaded image to fill the given size and store it as WebP under a
     * random name on the public disk.
     *
     * Re-encoding through Intervention Image (GD) applies EXIF rotation, keeps only the
     * first frame, and strips metadata and anything embedded in the original file.
     * The caller deletes the previous file once the new path has been saved, see delete().
     *
     * @return string The stored path, relative to the public disk.
     *
     * @throws RuntimeException When the image cannot be decoded, processed or stored.
     */
    public function storeCropped(UploadedFile $file, string $directory, int $width, int $height): string
    {
        return $this->store($file, $directory, fn (ImageInterface $image) => $image->cover($width, $height));
    }

    /**
     * Shrink the uploaded image to fit inside the given box, keeping its own proportions
     * (nothing is cropped and small images are never enlarged), and store it as WebP.
     * Meant for logos; transparency is kept. Same safety steps as storeCropped().
     *
     * @return string The stored path, relative to the public disk.
     *
     * @throws RuntimeException When the image cannot be decoded, processed or stored.
     */
    public function storeFitted(UploadedFile $file, string $directory, int $maxWidth, int $maxHeight): string
    {
        return $this->store($file, $directory, fn (ImageInterface $image) => $image->scaleDown($maxWidth, $maxHeight));
    }

    /**
     * Run the upload through $resize, re-encode it and store it under a random name.
     *
     * @param  Closure(ImageInterface): ImageInterface  $resize
     */
    protected function store(UploadedFile $file, string $directory, Closure $resize): string
    {
        $contents = $this->encode($file, $resize);

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
     * Decode, resize with $resize and re-encode the image as WebP.
     *
     * @param  Closure(ImageInterface): ImageInterface  $resize
     */
    protected function encode(UploadedFile $file, Closure $resize): string
    {
        $size = @getimagesize($file->getRealPath());

        if ($size === false) {
            throw new RuntimeException('The uploaded file is not a readable image.');
        }

        $this->allowProcessingMemory();
        $this->ensureEnoughMemory($size[0] * $size[1]);

        try {
            $contents = $resize($this->manager()->decodePath($file->getRealPath()))
                ->encode(new WebpEncoder(quality: self::WEBP_QUALITY, strip: true))
                ->toString();
        } catch (Throwable $e) {
            throw new RuntimeException('The uploaded image could not be processed.', previous: $e);
        }

        if ($contents === '') {
            throw new RuntimeException('The uploaded image could not be processed.');
        }

        return $contents;
    }

    /**
     * Image manager using GD, which this server has with WebP support.
     */
    protected function manager(): ImageManager
    {
        return ImageManager::usingDriver(
            GdDriver::class,
            autoOrientation: true,
            decodeAnimation: false,
            strip: true,
        );
    }

    /**
     * Raise PHP's memory limit to PROCESSING_MEMORY_LIMIT for this request, never lowering it.
     * If the host forbids ini_set(), the current limit stays and ensureEnoughMemory() decides.
     */
    protected function allowProcessingMemory(): void
    {
        $current = $this->memoryLimitInBytes();

        if ($current !== null && $current < $this->toBytes(self::PROCESSING_MEMORY_LIMIT)) {
            @ini_set('memory_limit', self::PROCESSING_MEMORY_LIMIT);
        }
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

        return $this->toBytes($limit);
    }

    /**
     * A php.ini size such as "128M" in bytes.
     */
    protected function toBytes(string $size): int
    {
        $value = (int) $size;

        return match (strtolower(substr($size, -1))) {
            'g' => $value * 1024 ** 3,
            'm' => $value * 1024 ** 2,
            'k' => $value * 1024,
            default => $value,
        };
    }
}
