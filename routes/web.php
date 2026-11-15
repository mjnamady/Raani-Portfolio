<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});

// auth.session ends every other session once the password changes.
Route::middleware(['auth', 'auth.session', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // My routes for the admin dashboard
    Route::controller(AdminController::class)->group(function () {

        Route::get('/dashboard', 'dashboard')->name('dashboard');
        Route::get('/profile/settings', 'profileSettings')->name('profile.settings');
        Route::patch('/profile/settings', 'updateProfileSettings')->middleware('throttle:account-security')->name('profile.settings.update');
        Route::put('/profile/password', 'updatePassword')->middleware('throttle:account-security')->name('profile.password.update');

    });

    // Default Laravel Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->middleware('throttle:account-security')->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->middleware('throttle:account-security')->name('profile.destroy');
});

require __DIR__.'/auth.php';
