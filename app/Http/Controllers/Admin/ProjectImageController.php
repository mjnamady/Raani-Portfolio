<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\MovesWithinList;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MoveProjectImageRequest;
use App\Http\Requests\Admin\StoreProjectImagesRequest;
use App\Http\Requests\Admin\UpdateProjectImageRequest;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

/**
 * A project's gallery images: add several at once, describe, reorder and remove (PRJ-5).
 */
class ProjectImageController extends Controller
{
    use MovesWithinList;

    public function store(StoreProjectImagesRequest $request, Project $project, ImageUploadService $images): RedirectResponse
    {
        $stored = [];

        try {
            foreach ($request->file('images') as $file) {
                $stored[] = $images->storeCropped($file, 'projects/gallery', ProjectImage::WIDTH, ProjectImage::HEIGHT);
            }
        } catch (RuntimeException $e) {
            report($e);
            array_map(fn (string $path) => $images->delete($path), $stored);

            throw ValidationException::withMessages([
                'images' => 'One of the images could not be processed. Please try different images.',
            ]);
        }

        try {
            $next = (int) $project->images()->max('sort_order');

            foreach ($stored as $path) {
                $image = new ProjectImage(['alt' => $project->title]);
                $image->path = $path;
                $image->sort_order = ++$next;
                $project->images()->save($image);
            }
        } catch (Throwable $e) {
            array_map(fn (string $path) => $images->delete($path), $stored);

            throw $e;
        }

        $count = count($stored);

        return redirect()->to(route('admin.projects.edit', $project).'#gallery')
            ->with('status', $count === 1 ? 'The image has been added to the gallery.' : "{$count} images have been added to the gallery.");
    } // End method

    public function update(UpdateProjectImageRequest $request, Project $project, ProjectImage $image): RedirectResponse
    {
        $image->fill($request->validated())->save();

        return redirect()->to(route('admin.projects.edit', $project).'#gallery')
            ->with('status', 'The image description has been saved.');
    } // End method

    public function move(MoveProjectImageRequest $request, Project $project, ProjectImage $image): RedirectResponse
    {
        $this->moveWithinList($project->images()->getQuery(), $image, $request->validated('direction') === 'left');

        return redirect()->to(route('admin.projects.edit', $project).'#gallery')
            ->with('status', 'The gallery order has been updated.');
    } // End method

    public function destroy(Project $project, ProjectImage $image, ImageUploadService $images): RedirectResponse
    {
        Gate::authorize('update', $project);

        $path = $image->path;
        $image->delete();

        if (! Project::isThemePath($path)) {
            $images->delete($path);
        }

        return redirect()->to(route('admin.projects.edit', $project).'#gallery')
            ->with('status', 'The image has been removed from the gallery.');
    } // End method
}
