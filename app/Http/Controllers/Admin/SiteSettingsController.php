<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSiteSettingsRequest;
use App\Models\Setting;
use App\Services\ImageUploadService;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

/**
 * Site Settings (M3): identity, social links, menu panel and footer. Contact details are edited
 * on the Contact Page admin (ContactPageController).
 */
class SiteSettingsController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Setting::class);

        return view('admin.settings.index', [
            'site' => SiteSettings::group('site'),
            'social' => SiteSettings::group('social'),
            'offcanvas' => SiteSettings::group('offcanvas'),
            'footer' => SiteSettings::group('footer'),
            'images' => collect(SiteSettings::IMAGES)->map(fn (array $image, string $key) => $image + [
                'url' => SiteSettings::imageUrl($key),
                'uploaded' => SiteSettings::hasUploadedImage($key),
            ]),
        ]);
    } // End method

    public function update(UpdateSiteSettingsRequest $request, ImageUploadService $images): RedirectResponse
    {
        $values = [];

        foreach (UpdateSiteSettingsRequest::TEXT_FIELDS as $field => $key) {
            $values[$key] = (string) $request->validated($field, '');
        }

        foreach (array_keys(SiteSettings::SOCIAL_NETWORKS) as $network) {
            $values["social.{$network}"] = (string) ($request->validated("social.{$network}") ?? '');
        }

        $values['footer.links'] = array_map(
            fn (array $link) => ['label' => $link['label'], 'url' => $link['url']],
            $request->validated('footer_links', []),
        );

        // Store new uploads first; old files are removed only once the new paths are saved.
        $newFiles = [];
        $oldFiles = [];

        try {
            foreach (SiteSettings::IMAGES as $image => $config) {
                $current = Setting::get($config['key']);

                if ($request->hasFile($image)) {
                    $newFiles[$image] = $config['mode'] === 'crop'
                        ? $images->storeCropped($request->file($image), 'settings', $config['width'], $config['height'])
                        : $images->storeFitted($request->file($image), 'settings', $config['width'], $config['height']);
                    $values[$config['key']] = $newFiles[$image];
                } elseif ($request->validated("reset_{$image}")) {
                    $values[$config['key']] = null;
                } else {
                    continue;
                }

                if (SiteSettings::isOwnedImagePath($current)) {
                    $oldFiles[] = $current;
                }
            }
        } catch (RuntimeException $e) {
            report($e);
            array_map(fn (string $path) => $images->delete($path), $newFiles);

            throw ValidationException::withMessages([
                $image => 'The '.strtolower(SiteSettings::IMAGES[$image]['label']).' could not be processed. Please try a different image.',
            ]);
        }

        try {
            DB::transaction(function () use ($values) {
                foreach ($values as $key => $value) {
                    Setting::put($key, $value);
                }
            });
        } catch (Throwable $e) {
            array_map(fn (string $path) => $images->delete($path), $newFiles);

            throw $e;
        }

        // Model events clear the cache inside the transaction; clear again after commit so no stale copy survives.
        Setting::forgetCache();

        // The save has succeeded: a file that can not be removed is logged, never turned into an error page.
        foreach ($oldFiles as $path) {
            try {
                $images->delete($path);
            } catch (Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('admin.settings.index')
            ->with('status', 'The site settings have been updated.');
    } // End method
}
