<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\MovesWithinList;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MoveProjectRequest;
use App\Http\Requests\Admin\StoreProjectRequest;
use App\Http\Requests\Admin\UpdateProjectRequest;
use App\Http\Requests\Admin\UpdateProjectSectionRequest;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Section;
use App\Models\Tag;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

/**
 * Portfolio projects (PRJ-1, PRJ-3, HOME-6, ADM-4).
 */
class ProjectController extends Controller
{
    use MovesWithinList;

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Project::class);

        $status = in_array($request->query('status'), [Project::STATUS_DRAFT, Project::STATUS_PUBLISHED], true)
            ? $request->query('status')
            : null;
        $category = $request->query('category');
        $categoryId = is_string($category) && ctype_digit($category) ? (int) $category : null;

        return view('admin.projects.index', [
            'projects' => Project::query()
                ->with('category')
                ->withCount('images')
                ->when($status, fn ($query) => $query->where('status', $status))
                ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
                ->ordered()
                ->get(),
            'categories' => ProjectCategory::query()->ordered()->get(),
            'filters' => ['status' => $status, 'category' => $categoryId],
            'counts' => [
                'all' => Project::query()->count(),
                'published' => Project::query()->where('status', Project::STATUS_PUBLISHED)->count(),
                'draft' => Project::query()->where('status', Project::STATUS_DRAFT)->count(),
            ],
            // Not saved until the visibility switch is submitted.
            'section' => Section::query()->firstOrNew(['key' => Section::HOME_PORTFOLIO], ['is_active' => true]),
        ]);
    } // End method

    public function updateSettings(UpdateProjectSectionRequest $request): RedirectResponse
    {
        Section::query()->updateOrCreate(
            ['key' => Section::HOME_PORTFOLIO],
            ['is_active' => $request->validated('is_active')],
        );

        return redirect()->route('admin.projects.index')
            ->with('status', 'The latest portfolio section has been updated.');
    } // End method

    public function create(): View
    {
        Gate::authorize('create', Project::class);

        return view('admin.projects.form', [
            'project' => new Project(['status' => Project::STATUS_DRAFT, 'features' => []]),
            'categories' => ProjectCategory::query()->ordered()->get(),
            'tagSuggestions' => Tag::query()->orderBy('name')->pluck('name'),
        ]);
    } // End method

    public function store(StoreProjectRequest $request, ImageUploadService $images): RedirectResponse
    {
        $project = new Project($request->safe()->except(['tags', 'card_image', 'cover_image']));
        $project->sort_order = (int) Project::query()->max('sort_order') + 1;

        $this->saveWithImages($project, $request, $images);

        return redirect()->route('admin.projects.edit', $project)
            ->with('status', $project->isPublished() ? 'The project has been published.' : 'The project has been saved as a draft.');
    } // End method

    public function edit(Project $project): View
    {
        Gate::authorize('update', $project);

        return view('admin.projects.form', [
            'project' => $project->load(['images', 'tags']),
            'categories' => ProjectCategory::query()->ordered()->get(),
            'tagSuggestions' => Tag::query()->orderBy('name')->pluck('name'),
        ]);
    } // End method

    public function update(UpdateProjectRequest $request, Project $project, ImageUploadService $images): RedirectResponse
    {
        // An empty slug keeps the current URL; only new projects get one made from the title.
        $project->fill($request->safe()->except(array_filter([
            'tags', 'card_image', 'cover_image', blank($request->validated('slug')) ? 'slug' : null,
        ])));

        $this->saveWithImages($project, $request, $images);

        return redirect()->route('admin.projects.edit', $project)
            ->with('status', 'The project has been updated.');
    } // End method

    public function destroy(Project $project, ImageUploadService $images): RedirectResponse
    {
        Gate::authorize('delete', $project);

        $files = $project->images()->pluck('path')->push($project->card_image, $project->cover_image);

        $project->delete();

        // Gallery rows go with the project (cascade); remove their uploaded files too.
        $files->each(fn (?string $path) => $this->deleteUpload($path, $images));

        return redirect()->route('admin.projects.index')
            ->with('status', 'The project has been deleted.');
    } // End method

    public function togglePublish(Project $project): RedirectResponse
    {
        Gate::authorize('update', $project);

        $project->status = $project->isPublished() ? Project::STATUS_DRAFT : Project::STATUS_PUBLISHED;
        $project->save();

        return back()->with('status', $project->isPublished() ? 'The project is now published.' : 'The project is now a draft and hidden from the site.');
    } // End method

    public function toggleFeatured(Project $project): RedirectResponse
    {
        Gate::authorize('update', $project);

        $project->is_featured = ! $project->is_featured;
        $project->save();

        return back()->with('status', $project->is_featured ? 'The project is now featured on the home page.' : 'The project is no longer featured.');
    } // End method

    public function move(MoveProjectRequest $request, Project $project): RedirectResponse
    {
        $this->moveWithinList(Project::query()->ordered(), $project, $request->validated('direction') === 'up');

        return back()->with('status', 'The order has been updated.');
    } // End method

    /**
     * Store new card/cover images, save the project and its tags in one transaction, and
     * only then delete replaced files. New uploads are removed again if saving fails.
     */
    protected function saveWithImages(Project $project, StoreProjectRequest $request, ImageUploadService $images): void
    {
        $replaced = [];
        $uploaded = [];

        try {
            foreach ([
                'card_image' => [Project::CARD_WIDTH, Project::CARD_HEIGHT],
                'cover_image' => [Project::COVER_WIDTH, Project::COVER_HEIGHT],
            ] as $field => [$width, $height]) {
                if ($request->hasFile($field)) {
                    $uploaded[] = $path = $this->storeImage($request->file($field), $field, $width, $height, $images);
                    $replaced[] = $project->{$field};
                    $project->{$field} = $path;
                }
            }

            DB::transaction(function () use ($project, $request) {
                $project->save();

                $tagIds = Tag::idsForNames($request->validated('tags', []));
                $project->tags()->sync(collect($tagIds)->mapWithKeys(fn (int $id, int $position) => [$id => ['sort_order' => $position]])->all());
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

        // Tag changes do not touch the projects row, so clear the cache explicitly.
        Project::forgetCache();
    } // End method

    protected function storeImage(UploadedFile $file, string $field, int $width, int $height, ImageUploadService $images): string
    {
        try {
            return $images->storeCropped($file, 'projects', $width, $height);
        } catch (RuntimeException $e) {
            report($e);

            throw ValidationException::withMessages([
                $field => 'The image could not be processed. Please try a different image.',
            ]);
        }
    } // End method

    /**
     * Delete an uploaded file; theme demo images are read-only and never deleted.
     */
    protected function deleteUpload(?string $path, ImageUploadService $images): void
    {
        if ($path && ! Project::isThemePath($path)) {
            $images->delete($path);
        }
    } // End method
}
