<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['alt'])]
class ProjectImage extends Model
{
    /**
     * Size gallery images are cropped to (twice the theme's 410x295 slider images).
     */
    public const WIDTH = 820;

    public const HEIGHT = 590;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * The model's boot hooks: gallery changes clear the cached project lists.
     */
    protected static function booted(): void
    {
        static::saved(fn () => Project::forgetCache());
        static::deleted(fn () => Project::forgetCache());
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Public URL of the image (a theme demo file or an upload).
     */
    protected function url(): Attribute
    {
        return Attribute::get(fn () => Project::publicUrl($this->path));
    }
}
