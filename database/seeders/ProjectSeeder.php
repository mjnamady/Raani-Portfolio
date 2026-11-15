<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectImage;
use App\Models\Section;
use App\Models\Tag;
use App\Support\RichText;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Recreate the theme's demo projects (images served from the theme folder).
     * Existing projects and categories are never overwritten.
     */
    public function run(): void
    {
        Section::query()->firstOrCreate(['key' => Section::HOME_PORTFOLIO], ['is_active' => true]);

        if (! ProjectCategory::query()->exists()) {
            foreach (['Branding', 'Design', 'Content writing', 'Marketing'] as $position => $name) {
                // DatabaseSeeder disables model events, so set the slug here instead of the saving hook.
                $category = new ProjectCategory(['name' => $name, 'slug' => ProjectCategory::uniqueSlug($name)]);
                $category->sort_order = $position + 1;
                $category->save();
            }
        }

        if (! Project::query()->exists()) {
            $categories = ProjectCategory::query()->pluck('id', 'name');
            $theme = Project::THEME_IMAGE_PREFIX;

            foreach ($this->demoProjects() as $position => $demo) {
                $project = new Project([
                    'category_id' => $categories[$demo['category']] ?? null,
                    'title' => $demo['title'],
                    // Model events are off under DatabaseSeeder: slug and sanitizing are done here.
                    'slug' => Project::uniqueSlug($demo['title']),
                    'tagline' => 'Development Coaches',
                    'client_name' => 'Nadimul Islam',
                    'project_date' => $demo['date'],
                    'card_image_alt' => $demo['title'],
                    'cover_image_alt' => $demo['title'],
                    'content' => RichText::clean($this->demoContent($demo['title'])),
                    'features' => ['Ui/visual Design', 'App Development', 'Software Developer'],
                    'is_featured' => true,
                    'status' => Project::STATUS_PUBLISHED,
                    'published_at' => now()->subDays(10 - $position),
                ]);
                $project->card_image = $theme.'latest-portfolio/'.$demo['image'];
                $project->cover_image = $theme.'projects-details/thumnail-img.png';
                $project->sort_order = $position + 1;
                $project->save();

                $project->tags()->sync(
                    collect(Tag::idsForNames($demo['tags']))->mapWithKeys(fn (int $id, int $order) => [$id => ['sort_order' => $order]])->all()
                );

                foreach (['project-detials-swiper-img-1.jpg', 'project-detials-swiper-img-2.png', 'project-detials-swiper-img-1.jpg'] as $order => $file) {
                    $image = new ProjectImage(['alt' => $demo['title']]);
                    $image->path = $theme.'projects-details/'.$file;
                    $image->sort_order = $order + 1;
                    $project->images()->save($image);
                }
            }
        }

        // DatabaseSeeder disables model events, so clear the home page caches explicitly.
        Section::forgetCache();
        Project::forgetCache();
    }

    /**
     * @return list<array{title: string, category: string, image: string, date: string, tags: list<string>}>
     */
    protected function demoProjects(): array
    {
        return [
            ['title' => 'SAAS Website Design', 'category' => 'Branding', 'image' => 'portfoli-img-1.jpg', 'date' => '2024-01-23', 'tags' => ['Figma']],
            ['title' => 'Workout App Design', 'category' => 'Design', 'image' => 'portfoli-img-3.jpg', 'date' => '2024-03-12', 'tags' => ['Adobe', 'Webflow', 'Ai']],
            ['title' => 'Fitness Tracker Design', 'category' => 'Content writing', 'image' => 'portfoli-img-4.jpg', 'date' => '2024-05-08', 'tags' => ['Figma', 'Framer', 'WordPress']],
            ['title' => 'Dashboard Design', 'category' => 'Marketing', 'image' => 'portfoli-img-2.jpg', 'date' => '2024-07-19', 'tags' => ['Adobe', 'Webflow']],
        ];
    }

    protected function demoContent(string $title): string
    {
        return '<p>'.e($title).' started with a clear brief: make a complex product feel simple, fast and trustworthy. '
            .'We began with interviews and a review of the existing experience, then mapped the journeys that mattered most.</p>'
            .'<p>Every screen was designed mobile-first, tested with real users and refined until the key tasks took seconds.</p>'
            .'<h3>Elevate your business with thoughtful design</h3>'
            .'<p>The result is a consistent design system, a faster build and a product the team is proud to ship.</p>';
    }
}
