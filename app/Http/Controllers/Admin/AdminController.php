<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminPasswordRequest;
use App\Http\Requests\Admin\UpdateBannerSectionRequest;
use App\Http\Requests\Admin\UpdateCounterAreaRequest;
use App\Http\Requests\Admin\UpdateLatestServicesAreaRequest;
use App\Http\Requests\Admin\UpdateProfileSettingsRequest;
use App\Http\Requests\Admin\UpdateServicesAreaRequest;
use App\Models\BannerSection;
use App\Models\LatestService;
use App\Models\Section;
use App\Models\Service;
use App\Models\Stat;
use App\Services\ImageUploadService;
use App\Support\ContactDetails;
use App\Support\FontAwesomeIcons;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class AdminController extends Controller
{
    public function bannerSection(): View
    {
        $banner = BannerSection::current();

        $stats = Stat::query()->forPlacement(Stat::PLACEMENT_BANNER)->get();

        return view('admin.pages.banner', [
            'banner' => $banner,
            // Always show three rows; empty rows are ignored on save.
            'statRows' => $stats->pad(UpdateBannerSectionRequest::MAX_STATS, null)->take(UpdateBannerSectionRequest::MAX_STATS)->values(),
            // Shown read-only: the contact details are edited on the Contact Page.
            'contact' => ContactDetails::primary(),
        ]);
    } // End method

    public function updateBannerSection(UpdateBannerSectionRequest $request, ImageUploadService $images): RedirectResponse
    {
        $banner = BannerSection::current();

        $banner->fill($request->safe()->except(['image', 'stats']));

        $oldImage = $banner->image;
        $newImage = null;

        if ($request->hasFile('image')) {
            try {
                $newImage = $images->storeCropped(
                    $request->file('image'),
                    'banner',
                    BannerSection::IMAGE_WIDTH,
                    BannerSection::IMAGE_HEIGHT,
                );
            } catch (RuntimeException $e) {
                report($e);

                throw ValidationException::withMessages([
                    'image' => 'The banner image could not be processed. Please try a different image.',
                ]);
            }

            $banner->image = $newImage;
        }

        // Only remove the old image once the new path is saved, and never leave an orphaned upload.
        try {
            DB::transaction(function () use ($banner, $request) {
                $banner->save();

                Stat::query()->where('placement', Stat::PLACEMENT_BANNER)->delete();

                foreach (array_values($request->validated('stats', [])) as $index => $stat) {
                    Stat::create([
                        'value' => $stat['value'],
                        'suffix' => $stat['suffix'] ?? null,
                        'label' => $stat['label'],
                        'placement' => Stat::PLACEMENT_BANNER,
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ]);
                }
            });
        } catch (Throwable $e) {
            $images->delete($newImage);

            throw $e;
        }

        // Model events clear the cache inside the transaction; clear again after commit so no stale copy survives.
        BannerSection::forgetCache();

        if ($newImage) {
            $images->delete($oldImage);
        }

        return redirect()->route('admin.banner')
            ->with('status', 'The banner section has been updated.');
    } // End method

    public function profileSettings(Request $request): View
    {
        return view('admin.profile.profile_settings', [
            'user' => $request->user(),
        ]);
    } // End method

    public function updateProfileSettings(UpdateProfileSettingsRequest $request, ImageUploadService $images): RedirectResponse
    {
        $user = $request->user();

        $user->fill($request->safe()->only(['name', 'username', 'email']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $oldImage = $user->profile_image;
        $newImage = null;

        if ($request->hasFile('profile_image')) {
            try {
                $newImage = $images->storeCropped($request->file('profile_image'), 'profile-images', 400, 400);
            } catch (RuntimeException $e) {
                report($e);

                throw ValidationException::withMessages([
                    'profile_image' => 'The profile image could not be processed. Please try a different image.',
                ]);
            }

            $user->profile_image = $newImage;
        }

        // Only remove the old image once the new path is saved, and never leave an orphaned upload.
        try {
            $user->save();
        } catch (Throwable $e) {
            $images->delete($newImage);

            throw $e;
        }

        if ($newImage) {
            $images->delete($oldImage);
        }

        return redirect()->route('admin.profile.settings')
            ->with('status', 'Your profile has been updated.');
    } // End method

    public function updatePassword(UpdateAdminPasswordRequest $request): RedirectResponse
    {
        // With auth.session on the admin routes, every other session is logged out on its next request.
        $request->user()->forceFill([
            'password' => $request->validated('password'),
            'remember_token' => Str::random(60),
        ])->save();

        $request->session()->regenerate();

        return redirect()->route('admin.profile.settings')
            ->with('status', 'Your password has been changed. Other sessions have been signed out.')
            ->with('active_tab', 'password');
    } // End method

    public function servicesArea(): View
    {
        return view('admin.pages.services_area', [
            'services' => Service::query()->featured()->ordered()->get(),
            'maxServices' => UpdateServicesAreaRequest::MAX_SERVICES,
            'iconStyles' => FontAwesomeIcons::STYLES,
            'iconSuggestions' => FontAwesomeIcons::SUGGESTED,
        ]);
    } // End method

    public function updateServicesArea(UpdateServicesAreaRequest $request): RedirectResponse
    {
        $rows = array_values($request->validated('services', []));

        DB::transaction(function () use ($rows) {
            $keptIds = collect($rows)->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();

            // Cards removed in the editor only leave the home page: the service itself (and any
            // future content or detail page) is kept. The cache is cleared after the commit below.
            Service::query()->featured()->whereNotIn('id', $keptIds)->update(['is_featured' => false]);

            foreach ($rows as $position => $row) {
                $service = filled($row['id'] ?? null)
                    ? Service::query()->featured()->findOrFail($row['id'])
                    : new Service;

                $service->fill([
                    'title' => $row['title'],
                    'icon' => $row['icon'],
                    'project_count' => $row['project_count'],
                    'project_count_label' => $row['project_count_label'],
                    'is_active' => $row['is_active'] ?? false,
                    'is_featured' => true,
                    'sort_order' => $position + 1,
                ])->save();
            }
        });

        // Model events clear the cache inside the transaction; clear again after commit so no stale copy survives.
        Service::forgetCache();

        return redirect()->route('admin.services')
            ->with('status', 'The services area has been updated.');
    } // End method

    public function counterArea(): View
    {
        $counters = Stat::query()->forPlacement(Stat::PLACEMENT_COUNTER)->get();

        return view('admin.pages.counter_area', [
            'section' => Section::forKey(Section::HOME_COUNTER),
            'highlight' => Stat::query()->forPlacement(Stat::PLACEMENT_HIGHLIGHT)->first(),
            // Always show four rows; empty rows are ignored on save.
            'counterRows' => $counters->pad(UpdateCounterAreaRequest::MAX_COUNTERS, null)->take(UpdateCounterAreaRequest::MAX_COUNTERS)->values(),
        ]);
    } // End method

    public function updateCounterArea(UpdateCounterAreaRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            Section::forKey(Section::HOME_COUNTER)->update(['is_active' => $request->validated('is_active')]);

            Stat::query()->whereIn('placement', [Stat::PLACEMENT_HIGHLIGHT, Stat::PLACEMENT_COUNTER])->delete();

            $highlight = $request->validated('highlight');

            Stat::create([
                'value' => $highlight['value'],
                'suffix' => $highlight['suffix'] ?? null,
                'label' => $highlight['label'],
                'description' => $highlight['description'] ?? null,
                'placement' => Stat::PLACEMENT_HIGHLIGHT,
                'sort_order' => 1,
                'is_active' => true,
            ]);

            foreach (array_values($request->validated('counters', [])) as $index => $counter) {
                Stat::create([
                    'value' => $counter['value'],
                    'suffix' => $counter['suffix'] ?? null,
                    'label' => $counter['label'],
                    'placement' => Stat::PLACEMENT_COUNTER,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
            }
        });

        // Model events clear the caches inside the transaction; clear again after commit so no stale copy survives.
        Section::forgetCache();
        Stat::forgetCounterCache();

        return redirect()->route('admin.counter')
            ->with('status', 'The counter area has been updated.');
    } // End method

    public function latestServicesArea(): View
    {
        $items = LatestService::query()->ordered()->get();

        return view('admin.pages.latest_services_area', [
            'section' => Section::forKey(Section::HOME_LATEST_SERVICES),
            // Always show three rows; empty rows are ignored on save.
            'itemRows' => $items->pad(UpdateLatestServicesAreaRequest::MAX_ITEMS, null)->take(UpdateLatestServicesAreaRequest::MAX_ITEMS)->values(),
        ]);
    } // End method

    public function updateLatestServicesArea(UpdateLatestServicesAreaRequest $request, ImageUploadService $images): RedirectResponse
    {
        $section = Section::forKey(Section::HOME_LATEST_SERVICES);

        $section->fill($request->safe()->only(['eyebrow', 'title', 'subtitle', 'image_alt', 'is_active']));

        $oldImage = $section->image;
        $newImage = null;

        if ($request->hasFile('image')) {
            try {
                $newImage = $images->storeCropped(
                    $request->file('image'),
                    'latest-services',
                    LatestService::IMAGE_WIDTH,
                    LatestService::IMAGE_HEIGHT,
                );
            } catch (RuntimeException $e) {
                report($e);

                throw ValidationException::withMessages([
                    'image' => 'The side image could not be processed. Please try a different image.',
                ]);
            }

            $section->image = $newImage;
        }

        // Only remove the old image once the new path is saved, and never leave an orphaned upload.
        try {
            DB::transaction(function () use ($section, $request) {
                $section->save();

                LatestService::query()->delete();

                foreach (array_values($request->validated('items', [])) as $index => $item) {
                    LatestService::create([
                        'title' => $item['title'],
                        'description' => $item['description'],
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ]);
                }
            });
        } catch (Throwable $e) {
            $images->delete($newImage);

            throw $e;
        }

        // Model events clear the caches inside the transaction; clear again after commit so no stale copy survives.
        Section::forgetCache();
        LatestService::forgetCache();

        if ($newImage) {
            $images->delete($oldImage);
        }

        return redirect()->route('admin.latest-services')
            ->with('status', 'The latest service area has been updated.');
    } // End method
}
