<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\MovesWithinList;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MoveSkillBarRequest;
use App\Http\Requests\Admin\StoreSkillBarRequest;
use App\Http\Requests\Admin\UpdateAboutPageRequest;
use App\Http\Requests\Admin\UpdateSkillBarRequest;
use App\Models\Section;
use App\Models\SkillBar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * The About page (ABT-4, ABT-5): skill bar columns. The reused sections (services, counter,
 * education/experience, testimonials, "Get in touch") are edited on their own pages.
 */
class AboutPageController extends Controller
{
    use MovesWithinList;

    public function index(): View
    {
        Gate::authorize('viewAny', SkillBar::class);

        $bars = SkillBar::query()->ordered()->get()->groupBy('group');

        return view('admin.about.index', [
            'columns' => collect(SkillBar::GROUPS)->map(fn (array $group, string $key) => [
                'key' => $key,
                // Not saved until the settings form is submitted.
                'section' => Section::query()->firstOrNew(['key' => $group['section']], ['title' => $group['title'], 'is_active' => true]),
                'bars' => $bars->get($key, collect()),
            ]),
        ]);
    } // End method

    public function updateSettings(UpdateAboutPageRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            foreach (SkillBar::GROUPS as $key => $group) {
                Section::query()->updateOrCreate(['key' => $group['section']], [
                    'title' => $request->validated("{$key}_title"),
                    'is_active' => $request->validated("{$key}_active"),
                ]);
            }
        });

        return redirect()->route('admin.about.index')
            ->with('status', 'The About page has been updated.');
    } // End method

    public function storeBar(StoreSkillBarRequest $request): RedirectResponse
    {
        $bar = new SkillBar($request->safe()->only(['name', 'percent']));
        $bar->group = $request->validated('group');
        $bar->sort_order = (int) SkillBar::query()->where('group', $bar->group)->max('sort_order') + 1;
        $bar->save();

        return redirect()->route('admin.about.index')
            ->with('status', "The skill \"{$bar->name}\" has been added.");
    } // End method

    public function updateBar(UpdateSkillBarRequest $request, SkillBar $skillBar): RedirectResponse
    {
        $skillBar->update($request->safe()->only(['name', 'percent']));

        return redirect()->route('admin.about.index')
            ->with('status', 'The skill has been updated.');
    } // End method

    public function toggleBar(SkillBar $skillBar): RedirectResponse
    {
        Gate::authorize('update', $skillBar);

        $skillBar->is_active = ! $skillBar->is_active;
        $skillBar->save();

        return redirect()->route('admin.about.index')
            ->with('status', $skillBar->is_active ? 'The skill is now shown on the About page.' : 'The skill is now hidden from the About page.');
    } // End method

    public function moveBar(MoveSkillBarRequest $request, SkillBar $skillBar): RedirectResponse
    {
        $this->moveWithinList(
            SkillBar::query()->where('group', $skillBar->group)->ordered(),
            $skillBar,
            $request->validated('direction') === 'up',
        );

        return redirect()->route('admin.about.index')
            ->with('status', 'The order has been updated.');
    } // End method

    public function destroyBar(SkillBar $skillBar): RedirectResponse
    {
        Gate::authorize('delete', $skillBar);

        $skillBar->delete();

        return redirect()->route('admin.about.index')
            ->with('status', 'The skill has been deleted.');
    } // End method
}
