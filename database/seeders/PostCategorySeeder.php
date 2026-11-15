<?php

namespace Database\Seeders;

use App\Models\PostCategory;
use Illuminate\Database\Seeder;

class PostCategorySeeder extends Seeder
{
    /**
     * The demo blog categories. Existing categories are never overwritten.
     */
    public function run(): void
    {
        if (PostCategory::query()->exists()) {
            return;
        }

        foreach (['Web Design', 'UX Design', 'Branding'] as $position => $name) {
            // DatabaseSeeder disables model events, so set the slug here instead of the saving hook.
            $category = new PostCategory(['name' => $name, 'slug' => PostCategory::uniqueSlug($name)]);
            $category->sort_order = $position + 1;
            $category->save();
        }
    }
}
