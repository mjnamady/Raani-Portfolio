<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminPasswordRequest;
use App\Http\Requests\Admin\UpdateProfileSettingsRequest;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
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
}
