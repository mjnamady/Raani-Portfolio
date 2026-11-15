<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\MovesWithinList;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MoveSkillRequest;
use App\Http\Requests\Admin\StoreSkillRequest;
use App\Http\Requests\Admin\UpdateSkillRequest;
use App\Http\Requests\Admin\UpdateSkillSectionRequest;
use App\Models\Section;
use App\Models\Skill;
use App\Support\FontAwesomeIcons;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * The home "My Skill" section (HOME-4, SKL-1, SKL-2, ADM-4).
 */
class SkillController extends Controller
{
    use MovesWithinList;

    public function index(): View
    {
        Gate::authorize('viewAny', Skill::class);

        return view('admin.skills.index', [
            'skills' => Skill::query()->ordered()->get(),
            // Not saved until the settings form is submitted.
            'section' => Section::query()->firstOrNew(['key' => Section::HOME_SKILLS], [
                'eyebrow' => 'My Skill',
                'title' => "Elevated Designs Personalized\nthe best Experiences",
                'is_active' => true,
            ]),
        ]);
    } // End method

    public function updateSettings(UpdateSkillSectionRequest $request): RedirectResponse
    {
        Section::query()->updateOrCreate(['key' => Section::HOME_SKILLS], $request->validated());

        return redirect()->route('admin.skills.index')
            ->with('status', 'The My Skill section has been updated.');
    } // End method

    public function create(): View
    {
        Gate::authorize('create', Skill::class);

        return view('admin.skills.form', $this->formData(new Skill([
            'icon' => 'fa-light fa-pen-nib',
            'done_label' => 'Done',
            'is_active' => true,
        ])));
    } // End method

    public function store(StoreSkillRequest $request): RedirectResponse
    {
        $skill = new Skill($request->safe()->except('is_highlighted'));
        $skill->sort_order = (int) Skill::query()->max('sort_order') + 1;
        $skill->save();

        if ($request->validated('is_highlighted')) {
            $skill->highlight();
        }

        Skill::ensureOneHighlighted();

        return redirect()->route('admin.skills.index')
            ->with('status', "The skill \"{$skill->title}\" has been added.");
    } // End method

    public function edit(Skill $skill): View
    {
        Gate::authorize('update', $skill);

        return view('admin.skills.form', $this->formData($skill));
    } // End method

    public function update(UpdateSkillRequest $request, Skill $skill): RedirectResponse
    {
        $skill->fill($request->safe()->except('is_highlighted'));

        if (! $request->validated('is_highlighted')) {
            $skill->is_highlighted = false;
        }

        $skill->save();

        if ($request->validated('is_highlighted')) {
            $skill->highlight();
        }

        Skill::ensureOneHighlighted();

        return redirect()->route('admin.skills.index')
            ->with('status', 'The skill has been updated.');
    } // End method

    public function destroy(Skill $skill): RedirectResponse
    {
        Gate::authorize('delete', $skill);

        $skill->delete();
        Skill::ensureOneHighlighted();

        return redirect()->route('admin.skills.index')
            ->with('status', 'The skill has been deleted.');
    } // End method

    public function toggle(Skill $skill): RedirectResponse
    {
        Gate::authorize('update', $skill);

        $skill->is_active = ! $skill->is_active;
        $skill->save();

        // A hidden skill passes the highlight on to the first visible one.
        Skill::ensureOneHighlighted();

        return redirect()->route('admin.skills.index')
            ->with('status', $skill->is_active ? 'The skill is now shown on the site.' : 'The skill is now hidden from the site.');
    } // End method

    public function highlight(Skill $skill): RedirectResponse
    {
        Gate::authorize('update', $skill);

        if (! $skill->is_active) {
            return redirect()->route('admin.skills.index')
                ->with('error', 'Show the skill on the site before highlighting it.');
        }

        $skill->highlight();

        return redirect()->route('admin.skills.index')
            ->with('status', "\"{$skill->title}\" is now the highlighted skill.");
    } // End method

    public function move(MoveSkillRequest $request, Skill $skill): RedirectResponse
    {
        $this->moveWithinList(Skill::query()->ordered(), $skill, $request->validated('direction') === 'up');

        return redirect()->route('admin.skills.index')
            ->with('status', 'The order has been updated.');
    } // End method

    /**
     * @return array{skill: Skill, iconSuggestions: list<string>, iconStyles: list<string>}
     */
    protected function formData(Skill $skill): array
    {
        return [
            'skill' => $skill,
            'iconSuggestions' => FontAwesomeIcons::SUGGESTED,
            'iconStyles' => FontAwesomeIcons::STYLES,
        ];
    } // End method
}
