<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdateBlogSectionRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\BannerSection;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Section;
use App\Models\Tag;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

/**
 * Blog posts (BLG-1, BLG-3, HOME-10, ADM-4).
 */
class PostController extends Controller
{
    public const PER_PAGE = 20;

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Post::class);

        $status = in_array($request->query('status'), [Post::STATUS_DRAFT, Post::STATUS_PUBLISHED], true)
            ? $request->query('status')
            : null;

        return view('admin.posts.index', [
            'posts' => Post::query()
                ->with(['category', 'tags'])
                ->withCount([
                    'comments',
                    'comments as pending_comments_count' => fn ($query) => $query->pending(),
                ])
                ->when($status, fn ($query) => $query->where('status', $status))
                ->newestFirst()
                ->paginate(self::PER_PAGE)
                ->withQueryString(),
            'status' => $status,
            'counts' => [
                'all' => Post::query()->count(),
                'published' => Post::query()->where('status', Post::STATUS_PUBLISHED)->count(),
                'draft' => Post::query()->where('status', Post::STATUS_DRAFT)->count(),
            ],
            // Not saved until the settings form is submitted.
            'section' => Section::query()->firstOrNew(['key' => Section::HOME_BLOG], [
                'eyebrow' => 'Blog and news',
                'title' => "Elevating Personal Branding the\nthrough Powerful Portfolios",
                'is_active' => true,
            ]),
        ]);
    } // End method

    public function updateSettings(UpdateBlogSectionRequest $request): RedirectResponse
    {
        Section::query()->updateOrCreate(['key' => Section::HOME_BLOG], $request->validated());

        return redirect()->route('admin.posts.index')
            ->with('status', 'The Blog and News section has been updated.');
    } // End method

    public function create(): View
    {
        Gate::authorize('create', Post::class);

        return view('admin.posts.form', $this->formData(new Post([
            'status' => Post::STATUS_DRAFT,
            'author_name' => BannerSection::query()->value('name'),
        ])));
    } // End method

    public function store(StorePostRequest $request, ImageUploadService $images): RedirectResponse
    {
        $post = new Post($request->safe()->except(['tags', 'featured_image']));

        $this->saveWithImage($post, $request, $images);

        return redirect()->route('admin.posts.edit', $post)
            ->with('status', $post->isPublished() ? 'The post has been published.' : 'The post has been saved as a draft.');
    } // End method

    public function edit(Post $post): View
    {
        Gate::authorize('update', $post);

        return view('admin.posts.form', $this->formData($post->load('tags')));
    } // End method

    public function update(UpdatePostRequest $request, Post $post, ImageUploadService $images): RedirectResponse
    {
        // An empty slug keeps the current URL; only new posts get one made from the title.
        $post->fill($request->safe()->except(array_filter([
            'tags', 'featured_image', blank($request->validated('slug')) ? 'slug' : null,
        ])));

        $this->saveWithImage($post, $request, $images);

        return redirect()->route('admin.posts.edit', $post)
            ->with('status', 'The post has been updated.');
    } // End method

    public function destroy(Post $post, ImageUploadService $images): RedirectResponse
    {
        Gate::authorize('delete', $post);

        $files = [$post->featured_image, $post->card_image];

        // Tag links go with the post (cascade).
        $post->delete();

        foreach ($files as $path) {
            $this->deleteUpload($path, $images);
        }

        return redirect()->route('admin.posts.index')
            ->with('status', 'The post has been deleted.');
    } // End method

    public function togglePublish(Post $post): RedirectResponse
    {
        Gate::authorize('update', $post);

        // The quick toggle skips the form, so check what the form would require before publishing.
        if (! $post->isPublished() && (blank($post->body) || blank($post->featured_image))) {
            return back()->with('error', 'Add the post text and a featured image before publishing it.');
        }

        $post->status = $post->isPublished() ? Post::STATUS_DRAFT : Post::STATUS_PUBLISHED;
        $post->save();

        return back()->with('status', $post->isPublished() ? 'The post is now published.' : 'The post is now a draft and hidden from the site.');
    } // End method

    /**
     * @return array{post: Post, categories: Collection<int, PostCategory>, tagSuggestions: Collection<int, string>}
     */
    protected function formData(Post $post): array
    {
        return [
            'post' => $post,
            'categories' => PostCategory::query()->ordered()->get(),
            'tagSuggestions' => Tag::query()->orderBy('name')->pluck('name'),
        ];
    } // End method

    /**
     * Store the new featured image in both sizes, save the post and its tags in one
     * transaction, and only then delete replaced files. New uploads are removed again if saving fails.
     */
    protected function saveWithImage(Post $post, StorePostRequest $request, ImageUploadService $images): void
    {
        $replaced = [];
        $uploaded = [];

        try {
            if ($request->hasFile('featured_image')) {
                $file = $request->file('featured_image');

                $uploaded[] = $featured = $this->storeImage($file, Post::FEATURED_WIDTH, Post::FEATURED_HEIGHT, $images);
                $uploaded[] = $card = $this->storeImage($file, Post::CARD_WIDTH, Post::CARD_HEIGHT, $images);

                $replaced = [$post->featured_image, $post->card_image];
                $post->featured_image = $featured;
                $post->card_image = $card;
            }

            DB::transaction(function () use ($post, $request) {
                $post->save();

                $tagIds = Tag::idsForNames($request->validated('tags', []));
                $post->tags()->sync(collect($tagIds)->mapWithKeys(fn (int $id, int $position) => [$id => ['sort_order' => $position]])->all());
            });
        } catch (Throwable $e) {
            foreach ($uploaded as $path) {
                $images->delete($path);
            }

            throw $e;
        }

        foreach ($replaced as $path) {
            $this->deleteUpload($path, $images);
        }

        // Tag changes do not touch the posts row, so clear the cache explicitly.
        Post::forgetCache();
    } // End method

    protected function storeImage(UploadedFile $file, int $width, int $height, ImageUploadService $images): string
    {
        try {
            return $images->storeCropped($file, 'posts', $width, $height);
        } catch (RuntimeException $e) {
            report($e);

            throw ValidationException::withMessages([
                'featured_image' => 'The image could not be processed. Please try a different image.',
            ]);
        }
    } // End method

    /**
     * Delete an uploaded file; theme demo images are read-only and never deleted.
     */
    protected function deleteUpload(?string $path, ImageUploadService $images): void
    {
        if ($path && ! Post::isThemePath($path)) {
            $images->delete($path);
        }
    } // End method
}
