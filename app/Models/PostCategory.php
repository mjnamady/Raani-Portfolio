<?php

namespace App\Models;

use Database\Factories\PostCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug'])]
class PostCategory extends Model
{
    /** @use HasFactory<PostCategoryFactory> */
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
     * The model's boot hooks: give new categories a unique slug, and clear the cached home cards on change.
     */
    protected static function booted(): void
    {
        static::saving(function (PostCategory $category) {
            $category->slug = static::uniqueSlug($category->slug ?: $category->name, $category->id);
        });

        static::saved(fn () => Post::forgetCache());
        static::deleted(fn () => Post::forgetCache());
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
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'category_id');
    }

    /**
     * @param  Builder<PostCategory>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }
}
