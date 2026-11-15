<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MoveResumeEntryRequest;
use App\Http\Requests\Admin\ResumeEntryRequest;
use App\Http\Requests\Admin\UpdateResumeSectionRequest;
use App\Models\ResumeEntry;
use App\Models\Section;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

/**
 * Shared CRUD for education and experience entries (ABT-2, ABT-3, ADM-4).
 * Subclasses only say which type they manage.
 */
abstract class ResumeEntryController extends Controller
{
    /**
     * Settings for the managed type.
     *
     * @return array{type: string, route: string, section: string, label: string, singular: string, view: string, default_title: string}
     */
    abstract protected function config(): array;

    public function index(): View
    {
        Gate::authorize('viewAny', ResumeEntry::class);

        $config = $this->config();

        return view($config['view'], [
            'config' => $config,
            'entries' => ResumeEntry::query()->ofType($config['type'])->ordered()->get(),
            // Not saved until the settings form is submitted.
            'section' => Section::query()->firstOrNew(
                ['key' => $config['section']],
                ['title' => $config['default_title'], 'is_active' => true],
            ),
        ]);
    } // End method

    public function updateSettings(UpdateResumeSectionRequest $request, ImageUploadService $images): RedirectResponse
    {
        $config = $this->config();

        $section = Section::query()->firstOrNew(['key' => $config['section']]);
        $section->fill($request->safe()->only(['title', 'is_active', 'image_alt']));

        $oldImage = $section->image;
        $newImage = null;

        if ($request->isExperience() && $request->hasFile('image')) {
            try {
                $newImage = $images->storeCropped($request->file('image'), 'experience', ResumeEntry::IMAGE_WIDTH, ResumeEntry::IMAGE_HEIGHT);
            } catch (RuntimeException $e) {
                report($e);

                throw ValidationException::withMessages([
                    'image' => 'The image could not be processed. Please try a different image.',
                ]);
            }

            $section->image = $newImage;
        }

        // Only remove the old image once the new path is saved, and never leave an orphaned upload.
        try {
            $section->save();
        } catch (Throwable $e) {
            $images->delete($newImage);

            throw $e;
        }

        if ($newImage) {
            $images->delete($oldImage);
        }

        return redirect()->route($config['route'].'.index')
            ->with('status', 'The '.strtolower($config['label']).' section has been updated.');
    } // End method

    public function create(): View
    {
        Gate::authorize('create', ResumeEntry::class);

        return view('admin.resume.form', [
            'config' => $this->config(),
            'entry' => new ResumeEntry(['is_active' => true]),
        ]);
    } // End method

    public function store(ResumeEntryRequest $request): RedirectResponse
    {
        $config = $this->config();

        $entry = new ResumeEntry($request->validated());
        $entry->type = $config['type'];
        $entry->sort_order = (int) ResumeEntry::query()->ofType($config['type'])->max('sort_order') + 1;
        $entry->save();

        return redirect()->route($config['route'].'.index')
            ->with('status', 'The '.$config['singular'].' has been added.');
    } // End method

    public function edit(ResumeEntry $entry): View
    {
        $this->ensureType($entry);
        Gate::authorize('update', $entry);

        return view('admin.resume.form', [
            'config' => $this->config(),
            'entry' => $entry,
        ]);
    } // End method

    public function update(ResumeEntryRequest $request, ResumeEntry $entry): RedirectResponse
    {
        $entry->fill($request->validated())->save();

        return redirect()->route($this->config()['route'].'.index')
            ->with('status', 'The '.$this->config()['singular'].' has been updated.');
    } // End method

    public function destroy(ResumeEntry $entry): RedirectResponse
    {
        $this->ensureType($entry);
        Gate::authorize('delete', $entry);

        $entry->delete();

        return redirect()->route($this->config()['route'].'.index')
            ->with('status', 'The '.$this->config()['singular'].' has been deleted.');
    } // End method

    public function toggle(ResumeEntry $entry): RedirectResponse
    {
        $this->ensureType($entry);
        Gate::authorize('update', $entry);

        $entry->is_active = ! $entry->is_active;
        $entry->save();

        return redirect()->route($this->config()['route'].'.index')
            ->with('status', $entry->is_active
                ? 'The '.$this->config()['singular'].' is now shown on the site.'
                : 'The '.$this->config()['singular'].' is now hidden from the site.');
    } // End method

    public function move(MoveResumeEntryRequest $request, ResumeEntry $entry): RedirectResponse
    {
        $config = $this->config();

        DB::transaction(function () use ($request, $entry, $config) {
            $entries = ResumeEntry::query()->ofType($config['type'])->ordered()->lockForUpdate()->get()->values();
            $index = $entries->search(fn (ResumeEntry $item) => $item->is($entry));
            $target = $request->validated('direction') === 'up' ? $index - 1 : $index + 1;

            if ($index === false || ! $entries->has($target)) {
                return;
            }

            $order = $entries->all();
            [$order[$index], $order[$target]] = [$order[$target], $order[$index]];

            // Renumber the whole list so gaps or equal sort orders never cause surprises.
            foreach (array_values($order) as $position => $item) {
                $item->sort_order = $position + 1;
                $item->save();
            }
        });

        return redirect()->route($config['route'].'.index')
            ->with('status', 'The order has been updated.');
    } // End method

    /**
     * An experience entry is not reachable through education URLs, and the other way round.
     */
    protected function ensureType(ResumeEntry $entry): void
    {
        abort_unless($entry->type === $this->config()['type'], 404);
    }
}
