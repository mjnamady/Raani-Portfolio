<?php

namespace App\Models;

use Database\Factories\ProjectCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug'])]
class ProjectCategory extends Model
{
    /** @use HasFactory<ProjectCategoryFactory> */
    use HasFactory;

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
     * The model's boot hooks: give new categories a unique slug, and clear the cached project lists on change.
     */
    protected static function booted(): void
    {
        static::saving(function (ProjectCategory $category) {
            $category->slug = static::uniqueSlug($category->slug ?: $category->name, $category->id);
        });

        static::saved(fn () => Project::forgetCache());
        static::deleted(fn () => Project::forgetCache());
    }

    /**
     * A slug based on $text that no other category uses.
     */
    public static function uniqueSlug(string $text, ?int $ignoreId = null): string
    {
        // Room for a "-2" style suffix within the 80-character column.
        $base = Str::limit(Str::slug($text), 70, '') ?: 'category';
        $slug = $base;
        $suffix = 2;

        while (static::query()->where('slug', $slug)->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'category_id');
    }

    /**
     * @param  Builder<ProjectCategory>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }
}
