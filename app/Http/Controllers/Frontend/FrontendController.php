<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BannerSection;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Section;
use App\Models\SkillBar;
use App\Models\Tag;
use App\Support\ContactDetails;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FrontendController extends Controller
{
    /**
     * Projects shown per page on the projects page (PRJ-3).
     */
    public const PROJECTS_PER_PAGE = 8;

    /**
     * Posts shown per page on the blog (BLG-3).
     */
    public const POSTS_PER_PAGE = 9;

    /**
     * Posts in the blog sidebar's "Recent Post" box.
     */
    public const RECENT_POSTS = 3;

    /**
     * Published projects, optionally filtered by category (?category=slug), paginated.
     */
    public function allProjects(Request $request): View
    {
        // Only categories with at least one published project get a tab.
        $categories = ProjectCategory::query()
            ->whereHas('projects', fn ($query) => $query->published())
            ->ordered()
            ->get();

        $category = $request->query('category');
        $activeCategory = is_string($category) ? $categories->firstWhere('slug', $category) : null;

        $projects = Project::query()
            ->published()
            ->with('tags')
            ->when($activeCategory, fn ($query) => $query->where('category_id', $activeCategory->id))
            ->ordered()
            ->paginate(self::PROJECTS_PER_PAGE)
            ->withQueryString();

        return view('frontend.pages.all_projects', [
            'projects' => $projects,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
        ]);
    } // End method

    /**
     * One published project; drafts, future publish dates and unknown slugs are 404 (V-2).
     */
    public function projectDetails(string $slug): View
    {
        $project = Project::query()
            ->published()
            ->with(['category', 'images', 'tags'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Previous / next in the same order as the projects page (PRJ-4).
        $orderedIds = Project::query()->published()->ordered()->pluck('id');
        $position = $orderedIds->search($project->id);

        $neighbour = fn (int $offset) => ($id = $orderedIds->get($position + $offset))
            ? Project::query()->select(['id', 'title', 'slug'])->find($id)
            : null;

        return view('frontend.pages.project_details', [
            'project' => $project,
            'previous' => $position > 0 ? $neighbour(-1) : null,
            'next' => $neighbour(1),
        ]);
    } // End method

    /**
     * Published posts, newest first, optionally filtered by category (?category=slug) (BLG-3, V-3).
     */
    public function blogIndex(Request $request): View
    {
        // Only categories with at least one published post get a filter link.
        $categories = PostCategory::query()
            ->whereHas('posts', fn ($query) => $query->published())
            ->ordered()
            ->get();

        $category = $request->query('category');
        $activeCategory = is_string($category) ? $categories->firstWhere('slug', $category) : null;

        $posts = Post::query()
            ->published()
            ->when($activeCategory, fn ($query) => $query->where('category_id', $activeCategory->id))
            ->newestFirst()
            ->paginate(self::POSTS_PER_PAGE)
            ->withQueryString();

        return view('frontend.pages.blog', [
            'posts' => $posts,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
        ]);
    } // End method

    /**
     * One published post with the sidebar; drafts, future publish dates and unknown slugs are 404 (BLG-5).
     */
    public function blogDetails(Request $request, string $slug): View
    {
        $post = Post::query()
            ->published()
            ->with(['category', 'tags', 'approvedComments'])
            ->withCount(['comments as approved_comments_count' => fn ($query) => $query->approved()])
            ->where('slug', $slug)
            ->firstOrFail();

        // "Reply" links add ?reply={id}; only an approved top-level comment on this post can be replied to (BLG-9).
        $reply = $request->query('reply', old('parent_id'));
        $replyTo = is_scalar($reply) && ctype_digit((string) $reply)
            ? $post->approvedComments->firstWhere('id', (int) $reply)
            : null;

        // Previous = the next older post, next = the next newer one.
        $orderedIds = Post::query()->published()->newestFirst()->pluck('id');
        $position = $orderedIds->search($post->id);

        $neighbour = fn (int $offset) => ($id = $orderedIds->get($position + $offset))
            ? Post::query()->select(['id', 'title', 'slug'])->find($id)
            : null;

        return view('frontend.pages.blog_details', [
            'post' => $post,
            'replyTo' => $replyTo,
            'previous' => $neighbour(1),
            'next' => $position > 0 ? $neighbour(-1) : null,
            'sidebarCategories' => PostCategory::query()
                ->withCount(['posts' => fn ($query) => $query->published()])
                ->whereHas('posts', fn ($query) => $query->published())
                ->ordered()
                ->get(),
            'recentPosts' => Post::query()
                ->published()
                ->with('category')
                ->whereKeyNot($post->id)
                ->newestFirst()
                ->limit(self::RECENT_POSTS)
                ->get(),
            'sidebarTags' => Tag::query()
                ->whereHas('posts', fn ($query) => $query->published())
                ->orderBy('name')
                ->get(),
            'author' => BannerSection::query()->first(),
        ]);
    } // End method

    /**
     * The About page (ABT-4, ABT-5): skill bar columns and the "Get in touch" text. Services, counter,
     * education/experience and testimonials come from their own partials' view composers.
     */
    public function about(): View
    {
        $bars = SkillBar::forAboutPage();

        return view('frontend.pages.about', [
            'skillColumns' => collect(SkillBar::GROUPS)
                ->map(fn (array $group, string $key) => [
                    'title' => Section::cached($group['section'])?->title ?: $group['title'],
                    'bars' => $bars[$key],
                ])
                ->filter(fn (array $column, string $key) => Section::isVisible(SkillBar::GROUPS[$key]['section']) && $column['bars']->isNotEmpty()),
        ]);
    } // End method

    /**
     * The Contact page (GEN-2): address, email and phone cards from the contact settings. The
     * "Get in touch" section comes from its partial's view composer.
     */
    public function contact(): View
    {
        return view('frontend.pages.contact', [
            'contactCards' => ContactDetails::cards(),
        ]);
    } // End method
}
