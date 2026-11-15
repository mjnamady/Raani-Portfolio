<?php

use App\Services\ImageUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->service = new ImageUploadService;
});

test('stores a cropped webp under a random name and leaves old files to the caller', function () {
    Storage::disk('public')->put('profile-images/old.webp', 'old');

    $path = $this->service->storeCropped(UploadedFile::fake()->image('photo.png', 800, 300), 'profile-images', 400, 400);

    expect($path)->toMatch('#^profile-images/[0-9a-f-]{36}\.webp$#');
    Storage::disk('public')->assertExists($path);
    Storage::disk('public')->assertExists('profile-images/old.webp');
    expect(getimagesizefromstring(Storage::disk('public')->get($path))[2])->toBe(IMAGETYPE_WEBP);
});

test('throws a runtime exception for files GD cannot read', function () {
    $file = UploadedFile::fake()->createWithContent('broken.jpg', "\xFF\xD8\xFF\xE0".str_repeat("\x00", 64));

    expect(fn () => $this->service->storeCropped($file, 'profile-images', 400, 400))
        ->toThrow(RuntimeException::class);

    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('refuses to decode an image that would exceed the memory limit', function () {
    $service = new class extends ImageUploadService
    {
        protected function memoryLimitInBytes(): ?int
        {
            return memory_get_usage() + 1024;
        }
    };

    expect(fn () => $service->storeCropped(UploadedFile::fake()->image('big.png', 1000, 1000), 'profile-images', 400, 400))
        ->toThrow(RuntimeException::class, 'too large to process');

    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('delete ignores an empty path and removes an existing file', function () {
    Storage::disk('public')->put('profile-images/a.webp', 'a');

    $this->service->delete(null);
    $this->service->delete('profile-images/a.webp');

    Storage::disk('public')->assertMissing('profile-images/a.webp');
});
