<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\MovesWithinList;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MoveProjectCategoryRequest;
use App\Http\Requests\Admin\StoreProjectCategoryRequest;
use App\Http\Requests\Admin\UpdateProjectCategoryRequest;
use App\Models\ProjectCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Project categories: the filter tabs on the projects page (PRJ-2).
 */
class ProjectCategoryController extends Controller
{
    use MovesWithinList;

    public function index(): View
    {
        Gate::authorize('viewAny', ProjectCategory::class);

        return view('admin.projects.categories', [
            'categories' => ProjectCategory::query()->withCount('projects')->ordered()->get(),
        ]);
    } // End method

    public function store(StoreProjectCategoryRequest $request): RedirectResponse
    {
        $category = new ProjectCategory($request->validated());
        $category->sort_order = (int) ProjectCategory::query()->max('sort_order') + 1;
        $category->save();

        return redirect()->route('admin.project-categories.index')
            ->with('status', "The category \"{$category->name}\" has been added.");
    } // End method

    public function update(UpdateProjectCategoryRequest $request, ProjectCategory $category): RedirectResponse
    {
        // An empty slug keeps the current one, so existing filter links keep working.
        $category->fill($request->safe()->except(blank($request->validated('slug')) ? ['slug'] : []))->save();

        return redirect()->route('admin.project-categories.index')
            ->with('status', 'The category has been updated.');
    } // End method

    public function destroy(ProjectCategory $category): RedirectResponse
    {
        Gate::authorize('delete', $category);

        // Its projects stay, uncategorised (the foreign key nulls category_id).
        $category->delete();

        return redirect()->route('admin.project-categories.index')
            ->with('status', 'The category has been deleted. Its projects are now uncategorised.');
    } // End method

    public function move(MoveProjectCategoryRequest $request, ProjectCategory $category): RedirectResponse
    {
        $this->moveWithinList(ProjectCategory::query()->ordered(), $category, $request->validated('direction') === 'up');

        return redirect()->route('admin.project-categories.index')
            ->with('status', 'The order has been updated.');
    } // End method
}
