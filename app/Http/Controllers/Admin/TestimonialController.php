<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\MovesWithinList;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MoveTestimonialRequest;
use App\Http\Requests\Admin\StoreTestimonialRequest;
use App\Http\Requests\Admin\UpdateTestimonialRequest;
use App\Http\Requests\Admin\UpdateTestimonialSectionRequest;
use App\Models\Section;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * The home Testimonial slider (HOME-9, TST-1, ADM-4).
 */
class TestimonialController extends Controller
{
    use MovesWithinList;

    public function index(): View
    {
        Gate::authorize('viewAny', Testimonial::class);

        return view('admin.testimonials.index', [
            'testimonials' => Testimonial::query()->ordered()->get(),
            // Not saved until the settings form is submitted.
            'section' => Section::query()->firstOrNew(['key' => Section::HOME_TESTIMONIALS], [
                'title' => 'Testimonial',
                'is_active' => true,
            ]),
        ]);
    } // End method

    public function updateSettings(UpdateTestimonialSectionRequest $request): RedirectResponse
    {
        Section::query()->updateOrCreate(['key' => Section::HOME_TESTIMONIALS], $request->validated());

        return redirect()->route('admin.testimonials.index')
            ->with('status', 'The Testimonial section has been updated.');
    } // End method

    public function create(): View
    {
        Gate::authorize('create', Testimonial::class);

        return view('admin.testimonials.form', ['testimonial' => new Testimonial(['is_active' => true])]);
    } // End method

    public function store(StoreTestimonialRequest $request): RedirectResponse
    {
        $testimonial = new Testimonial($request->validated());
        $testimonial->sort_order = (int) Testimonial::query()->max('sort_order') + 1;
        $testimonial->save();

        return redirect()->route('admin.testimonials.index')
            ->with('status', "The testimonial from {$testimonial->name} has been added.");
    } // End method

    public function edit(Testimonial $testimonial): View
    {
        Gate::authorize('update', $testimonial);

        return view('admin.testimonials.form', ['testimonial' => $testimonial]);
    } // End method

    public function update(UpdateTestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update($request->validated());

        return redirect()->route('admin.testimonials.index')
            ->with('status', 'The testimonial has been updated.');
    } // End method

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        Gate::authorize('delete', $testimonial);

        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')
            ->with('status', 'The testimonial has been deleted.');
    } // End method

    public function toggle(Testimonial $testimonial): RedirectResponse
    {
        Gate::authorize('update', $testimonial);

        $testimonial->is_active = ! $testimonial->is_active;
        $testimonial->save();

        return redirect()->route('admin.testimonials.index')
            ->with('status', $testimonial->is_active ? 'The testimonial is now shown on the site.' : 'The testimonial is now hidden from the site.');
    } // End method

    public function move(MoveTestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $this->moveWithinList(Testimonial::query()->ordered(), $testimonial, $request->validated('direction') === 'up');

        return redirect()->route('admin.testimonials.index')
            ->with('status', 'The order has been updated.');
    } // End method
}
