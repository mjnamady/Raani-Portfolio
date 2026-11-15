<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateContactPageRequest;
use App\Models\Section;
use App\Models\Setting;
use App\Support\ContactDetails;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * The Contact page (GEN-2): address, email and phone cards stored as "contact" settings, and the
 * "Get in touch" text shared with the About page.
 */
class ContactPageController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Setting::class);

        return view('admin.contact.index', [
            'contact' => ContactDetails::all(),
            // Not saved until the form is submitted.
            'getInTouch' => Section::query()->firstOrNew(['key' => Section::CONTACT_GET_IN_TOUCH], ContactDetails::GET_IN_TOUCH_DEFAULTS + ['is_active' => true]),
        ]);
    } // End method

    public function update(UpdateContactPageRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            foreach (array_keys(ContactDetails::DEFAULTS) as $key) {
                Setting::put("contact.{$key}", $request->validated($key, []));
            }

            Section::query()->updateOrCreate(['key' => Section::CONTACT_GET_IN_TOUCH], [
                'eyebrow' => $request->validated('touch_eyebrow'),
                'title' => $request->validated('touch_title'),
                'subtitle' => $request->validated('touch_text'),
            ]);
        });

        return redirect()->route('admin.contact.index')
            ->with('status', 'The Contact page has been updated.');
    } // End method
}
