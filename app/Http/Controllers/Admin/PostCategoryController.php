<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\MovesWithinList;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MovePostCategoryRequest;
use App\Http\Requests\Admin\StorePostCategoryRequest;
use App\Http\Requests\Admin\UpdatePostCategoryRequest;
use App\Models\PostCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Blog categories: the filter links and sidebar list on the blog (BLG-2).
 */
class PostCategoryController extends Controller
{
    use MovesWithinList;

    public function index(): View
    {
        Gate::authorize('viewAny', PostCategory::class);

        return view('admin.posts.categories', [
            'categories' => PostCategory::query()->withCount('posts')->ordered()->get(),
        ]);
    } // End method

    public function store(StorePostCategoryRequest $request): RedirectResponse
    {
        $category = new PostCategory($request->validated());
        $category->sort_order = (int) PostCategory::query()->max('sort_order') + 1;
        $category->save();

        return redirect()->route('admin.post-categories.index')
            ->with('status', "The category \"{$category->name}\" has been added.");
    } // End method

    public function update(UpdatePostCategoryRequest $request, PostCategory $category): RedirectResponse
    {
        // An empty slug keeps the current one, so existing filter links keep working.
        $category->fill($request->safe()->except(blank($request->validated('slug')) ? ['slug'] : []))->save();

        return redirect()->route('admin.post-categories.index')
            ->with('status', 'The category has been updated.');
    } // End method

    public function destroy(PostCategory $category): RedirectResponse
    {
        Gate::authorize('delete', $category);

        // Its posts stay, uncategorised (the foreign key nulls category_id).
        $category->delete();

        return redirect()->route('admin.post-categories.index')
            ->with('status', 'The category has been deleted. Its posts are now uncategorised.');
    } // End method

    public function move(MovePostCategoryRequest $request, PostCategory $category): RedirectResponse
    {
        $this->moveWithinList(PostCategory::query()->ordered(), $category, $request->validated('direction') === 'up');

        return redirect()->route('admin.post-categories.index')
            ->with('status', 'The order has been updated.');
    } // End method
}
