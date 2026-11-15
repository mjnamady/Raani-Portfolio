<?php

use App\Http\Controllers\Admin\AboutPageController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\ContactPageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EducationController;
use App\Http\Controllers\Admin\ExperienceController;
use App\Http\Controllers\Admin\MessageController as AdminMessageController;
use App\Http\Controllers\Admin\MessageReplyController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\ProjectCategoryController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProjectImageController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Frontend\CommentController;
use App\Http\Controllers\Frontend\FrontendController;
use App\Http\Controllers\Frontend\MessageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Frontend routes start
Route::get('/', function () {
    return view('frontend.index');
});

Route::controller(FrontendController::class)->group(function () {
    // Projects (PRJ-2, PRJ-3, PRJ-4, SEO-1): list with category filter, and details by slug.
    Route::get('/projects', 'allProjects')->name('projects.index');
    Route::get('/projects/{slug}', 'projectDetails')->name('projects.show');

    // Blog (BLG-3, BLG-5, SEO-1): list with category filter, and posts by slug.
    Route::get('/blog', 'blogIndex')->name('blog.index');
    Route::get('/blog/{slug}', 'blogDetails')->name('blog.show');
});

// Blog comments (BLG-9): rate limited; the request 404s for posts that are not live.
Route::post('/blog/{post:slug}/comments', [CommentController::class, 'store'])
    ->middleware('throttle:comments')
    ->name('blog.comments.store');

// Visitor messages (CON-1, CON-2): contact forms, project pages and the chat popup; rate limited.
Route::post('/messages', [MessageController::class, 'store'])
    ->middleware('throttle:messages')
    ->name('messages.store');

// The old projects URL keeps working.
Route::permanentRedirect('/all/projects', '/projects')->name('all.projects');

Route::get('/about', [FrontendController::class, 'about'])->name('about.index');

Route::get('/contact', [FrontendController::class, 'contact'])->name('contact.index');

// Frontend routes end

// auth.session ends every other session once the password changes; admin-writes limits changes to 60 a minute.
Route::middleware(['auth', 'auth.session', 'admin', 'throttle:admin-writes'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard (ADM-2): where the admin lands after signing in
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // My routes for the admin dashboard
    Route::controller(AdminController::class)->group(function () {

        // Admin's profile routes
        Route::get('/profile/settings', 'profileSettings')->name('profile.settings');
        Route::patch('/profile/settings', 'updateProfileSettings')->middleware('throttle:account-security')->name('profile.settings.update');
        Route::put('/profile/password', 'updatePassword')->middleware('throttle:account-security')->name('profile.password.update');

        // Banner section's routes
        Route::get('/banner', 'bannerSection')->name('banner');
        Route::patch('/banner', 'updateBannerSection')->name('banner.update');

        // Services Area routes
        Route::get('/services', 'servicesArea')->name('services');
        Route::put('/services', 'updateServicesArea')->name('services.update');

        // Counter Area routes
        Route::get('/counter', 'counterArea')->name('counter');
        Route::put('/counter', 'updateCounterArea')->name('counter.update');

        // Latest Service Area routes
        Route::get('/latest-services', 'latestServicesArea')->name('latest-services');
        Route::patch('/latest-services', 'updateLatestServicesArea')->name('latest-services.update');

    });

    // Education and Experience entries (same actions, one controller per type)
    foreach (['education' => EducationController::class, 'experience' => ExperienceController::class] as $type => $controller) {
        Route::controller($controller)->prefix($type)->name("{$type}.")->group(function () {
            Route::get('/', 'index')->name('index');
            Route::put('/settings', 'updateSettings')->name('settings');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{entry}/edit', 'edit')->name('edit');
            Route::put('/{entry}', 'update')->name('update');
            Route::delete('/{entry}', 'destroy')->name('destroy');
            Route::patch('/{entry}/toggle', 'toggle')->name('toggle');
            Route::patch('/{entry}/move', 'move')->name('move');
        });
    }

    // Supported Companies logos
    Route::controller(ClientController::class)->prefix('clients')->name('clients.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/settings', 'updateSettings')->name('settings');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{client}/edit', 'edit')->name('edit');
        Route::put('/{client}', 'update')->name('update');
        Route::delete('/{client}', 'destroy')->name('destroy');
        Route::patch('/{client}/toggle', 'toggle')->name('toggle');
        Route::patch('/{client}/move', 'move')->name('move');
    });

    // Portfolio: projects, their gallery images and categories
    Route::controller(ProjectController::class)->prefix('projects')->name('projects.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/settings', 'updateSettings')->name('settings');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{project}/edit', 'edit')->name('edit');
        Route::put('/{project}', 'update')->name('update');
        Route::delete('/{project}', 'destroy')->name('destroy');
        Route::patch('/{project}/publish', 'togglePublish')->name('publish');
        Route::patch('/{project}/feature', 'toggleFeatured')->name('feature');
        Route::patch('/{project}/move', 'move')->name('move');
    });

    // Gallery images are only reachable through the project they belong to.
    Route::controller(ProjectImageController::class)->prefix('projects/{project}/images')->name('projects.images.')->scopeBindings()->group(function () {
        Route::post('/', 'store')->name('store');
        Route::put('/{image}', 'update')->name('update');
        Route::patch('/{image}/move', 'move')->name('move');
        Route::delete('/{image}', 'destroy')->name('destroy');
    });

    Route::controller(ProjectCategoryController::class)->prefix('project-categories')->name('project-categories.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{category}', 'update')->name('update');
        Route::delete('/{category}', 'destroy')->name('destroy');
        Route::patch('/{category}/move', 'move')->name('move');
    });

    // My Skill rows
    Route::controller(SkillController::class)->prefix('skills')->name('skills.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/settings', 'updateSettings')->name('settings');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{skill}/edit', 'edit')->name('edit');
        Route::put('/{skill}', 'update')->name('update');
        Route::delete('/{skill}', 'destroy')->name('destroy');
        Route::patch('/{skill}/toggle', 'toggle')->name('toggle');
        Route::patch('/{skill}/highlight', 'highlight')->name('highlight');
        Route::patch('/{skill}/move', 'move')->name('move');
    });

    // Testimonial slides
    Route::controller(TestimonialController::class)->prefix('testimonials')->name('testimonials.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/settings', 'updateSettings')->name('settings');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{testimonial}/edit', 'edit')->name('edit');
        Route::put('/{testimonial}', 'update')->name('update');
        Route::delete('/{testimonial}', 'destroy')->name('destroy');
        Route::patch('/{testimonial}/toggle', 'toggle')->name('toggle');
        Route::patch('/{testimonial}/move', 'move')->name('move');
    });

    // Blog: posts and their categories
    Route::controller(PostController::class)->prefix('posts')->name('posts.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/settings', 'updateSettings')->name('settings');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{post}/edit', 'edit')->name('edit');
        Route::put('/{post}', 'update')->name('update');
        Route::delete('/{post}', 'destroy')->name('destroy');
        Route::patch('/{post}/publish', 'togglePublish')->name('publish');
    });

    Route::controller(PostCategoryController::class)->prefix('post-categories')->name('post-categories.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{category}', 'update')->name('update');
        Route::delete('/{category}', 'destroy')->name('destroy');
        Route::patch('/{category}/move', 'move')->name('move');
    });

    // Blog comment moderation
    Route::controller(AdminCommentController::class)->prefix('comments')->name('comments.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::patch('/{comment}/approve', 'approve')->name('approve');
        Route::patch('/{comment}/unapprove', 'unapprove')->name('unapprove');
        Route::post('/{comment}/reply', 'reply')->name('reply');
        Route::delete('/{comment}', 'destroy')->name('destroy');
    });

    // About page: skill bar columns
    Route::controller(AboutPageController::class)->prefix('about')->name('about.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/settings', 'updateSettings')->name('settings');
        Route::post('/skill-bars', 'storeBar')->name('bars.store');
        Route::put('/skill-bars/{skillBar}', 'updateBar')->name('bars.update');
        Route::patch('/skill-bars/{skillBar}/toggle', 'toggleBar')->name('bars.toggle');
        Route::patch('/skill-bars/{skillBar}/move', 'moveBar')->name('bars.move');
        Route::delete('/skill-bars/{skillBar}', 'destroyBar')->name('bars.destroy');
    });

    // Messages inbox (ADM-3, A-6)
    Route::controller(AdminMessageController::class)->prefix('messages')->name('messages.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{message}', 'show')->name('show');
        Route::patch('/{message}/read', 'markRead')->name('read');
        Route::patch('/{message}/unread', 'markUnread')->name('unread');
        Route::patch('/{message}/replied', 'markReplied')->name('replied');
        Route::patch('/{message}/unreplied', 'markUnreplied')->name('unreplied');
        Route::delete('/{message}', 'destroy')->name('destroy');
    });

    // Replying to messages by email from the inbox (ADM-3); at most 30 replies an hour
    Route::controller(MessageReplyController::class)->prefix('messages/{message}/replies')->name('messages.replies.')->scopeBindings()->group(function () {
        Route::post('/', 'store')->middleware('throttle:message-replies')->name('store');
        Route::patch('/{reply}/retry', 'retry')->name('retry');
    });

    // Site Settings (M3): identity, social links, menu panel and footer
    Route::controller(SiteSettingsController::class)->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/', 'update')->name('update');
    });

    // Contact page: contact details cards and the shared "Get in touch" text
    Route::controller(ContactPageController::class)->prefix('contact')->name('contact.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/', 'update')->name('update');
    });

    // Default Laravel Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->middleware('throttle:account-security')->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->middleware('throttle:account-security')->name('profile.destroy');
});

require __DIR__.'/auth.php';
