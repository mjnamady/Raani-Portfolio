<?php

namespace App\Providers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Models\BannerSection;
use App\Models\Client;
use App\Models\Comment;
use App\Models\LatestService;
use App\Models\Message;
use App\Models\Post;
use App\Models\Project;
use App\Models\ResumeEntry;
use App\Models\Section;
use App\Models\Service;
use App\Models\Skill;
use App\Models\Stat;
use App\Models\Testimonial;
use App\Support\ContactDetails;
use App\Support\FlashableInput;
use App\Support\SiteSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Each reset route gets its own buckets: per IP, and per target email across IPs.
        RateLimiter::for('password-reset', fn (Request $request) => [
            Limit::perMinute(5)->by($request->path().'|ip|'.$request->ip()),
            Limit::perHour(10)->by($request->path().'|email|'.Str::lower(trim((string) $request->input('email')))),
        ]);

        // Routes that check the current password or change account credentials: 6 per minute per user and route.
        RateLimiter::for('account-security', fn (Request $request) => Limit::perMinute(6)->by(
            $request->route()?->getName().'|'.($request->user()?->getAuthIdentifier() ?? $request->ip())
        ));

        // Blog comments (CON-4): 3 per minute and 20 per day per visitor, and 100 per hour for the whole
        // site so a botnet can not flood the moderation queue. Over the limit, the visitor is sent back
        // to the form with a message and their text, instead of a bare "429" page.
        RateLimiter::for('comments', function (Request $request) {
            $visitor = static::rateLimitAddress((string) $request->ip());
            $tooFast = fn () => redirect()->to(url()->previous().'#comment-form')
                ->withInput(FlashableInput::from($request->except(StoreCommentRequest::HONEYPOT)))
                ->withErrors(['body' => 'You are sending comments too quickly. Please wait a little and try again.'], 'comment');

            return [
                Limit::perMinute(3)->by('comments|minute|'.$visitor)->response($tooFast),
                Limit::perDay(20)->by('comments|day|'.$visitor)->response($tooFast),
                Limit::perHour(100)->by('comments|site')->response($tooFast),
            ];
        });

        // Visitor messages (CON-4, V-4): 5 per minute and 30 per day per visitor, 100 per hour for the
        // whole site. JavaScript forms get a JSON 429; plain forms go back with a message and their text.
        RateLimiter::for('messages', function (Request $request) {
            $visitor = static::rateLimitAddress((string) $request->ip());
            $tooFast = function () use ($request) {
                $text = 'You are sending messages too quickly. Please wait a little and try again.';

                if ($request->expectsJson()) {
                    return response()->json(['message' => $text], 429);
                }

                $source = $request->input('source');
                $bag = 'message_'.(is_string($source) && array_key_exists($source, Message::SOURCES) ? $source : Message::SOURCE_CONTACT);

                return redirect()->to(StoreMessageRequest::backToForm($source))
                    ->withInput(FlashableInput::from($request->except(StoreMessageRequest::HONEYPOT)))
                    ->withErrors(['body' => $text], $bag);
            };

            return [
                Limit::perMinute(5)->by('messages|minute|'.$visitor)->response($tooFast),
                Limit::perDay(30)->by('messages|day|'.$visitor)->response($tooFast),
                Limit::perHour(100)->by('messages|site')->response($tooFast),
            ];
        });

        // Replies from the inbox send real emails: at most 30 an hour per admin.
        RateLimiter::for('message-replies', fn (Request $request) => Limit::perHour(30)
            ->by('message-replies|'.($request->user()?->getAuthIdentifier() ?? $request->ip()))
            ->response(fn () => redirect()->route('admin.messages.show', $request->route('message'))
                ->withInput(FlashableInput::from($request->only(['subject', 'body'])))
                ->withErrors(['body' => 'You have sent a lot of replies in the last hour. Please wait a little before sending more.'], 'reply')));

        // Unread messages badge in the admin sidebar (A-6).
        View::composer('admin.layout.components.sidebar', function ($view) {
            $view->with('unreadMessagesCount', Message::query()->unread()->count());
        });

        // Admin changes: 60 per minute per admin, a guard against a runaway script or a stolen session.
        RateLimiter::for('admin-writes', fn (Request $request) => $request->isMethodSafe()
            ? Limit::none()
            : Limit::perMinute(60)->by('admin-writes|'.($request->user()?->getAuthIdentifier() ?? $request->ip())));

        // Pending comments badge in the admin sidebar (A-7).
        View::composer('admin.layout.components.sidebar', function ($view) {
            $view->with('pendingCommentsCount', Comment::query()->pending()->count());
        });

        // Home page banner content (cached; cleared whenever the banner or its stats are saved).
        View::composer('frontend.partials.banner', function ($view) {
            $view->with(BannerSection::forHomePage() + ['contact' => ContactDetails::primary()]);
        });

        // Header, menu panel, mobile menu and footer (M3: GEN-1, GEN-2, GEN-3, GEN-5, GEN-6), and the
        // site name, description and favicon in both public layouts. All read from the cached settings.
        View::composer([
            'frontend.partials.header',
            'frontend.partials.footer',
            'layouts.frontend',
            'frontend.pages.layout.pages_temp',
        ], function ($view) {
            $site = SiteSettings::group('site');

            $view->with([
                'siteName' => $site['name'],
                'siteTagline' => $site['tagline'],
                'siteLogo' => SiteSettings::imageUrl('logo'),
                'siteLogoLight' => SiteSettings::imageUrl('logo_light'),
                'siteFavicon' => SiteSettings::imageUrl('favicon'),
                'socialLinks' => SiteSettings::socialLinks(),
                // Computed, checked values first, so they always win over stored keys.
                'offcanvas' => ['image_url' => SiteSettings::imageUrl('offcanvas_image')] + SiteSettings::group('offcanvas'),
                'footer' => ['quick_links' => SiteSettings::footerLinks(), 'copyright' => SiteSettings::copyright()] + SiteSettings::group('footer'),
                'contact' => ContactDetails::primary(),
            ]);
        });

        // Home page service cards (HOME-2): active, featured services in admin order.
        View::composer('frontend.partials.service', function ($view) {
            $view->with('services', Service::forHomePage());
        });

        // Home page Counter Area (HOME-5): highlight + stat cards, hidden when switched off.
        View::composer('frontend.partials.counter', function ($view) {
            $view->with(Stat::forCounterArea() + [
                'counterAreaVisible' => Section::isVisible(Section::HOME_COUNTER),
            ]);
        });

        // Home page Latest Service section (HOME-3): heading, side image and numbered items.
        View::composer('frontend.partials.latest_services', function ($view) {
            $view->with([
                'latestSection' => Section::cached(Section::HOME_LATEST_SERVICES),
                'latestServices' => LatestService::forHomePage(),
            ]);
        });

        // Education and Experience (ABT-2, ABT-3): headings, side image and active entries; reused on the About page.
        View::composer('frontend.partials.education_experience', function ($view) {
            $entries = ResumeEntry::forHomePage();

            $view->with([
                'educationSection' => Section::cached(Section::HOME_EDUCATION),
                'experienceSection' => Section::cached(Section::HOME_EXPERIENCE),
                'educationEntries' => $entries['education'],
                'experienceEntries' => $entries['experience'],
            ]);
        });

        // Latest Portfolio (HOME-6): up to four featured published projects, hidden when switched off.
        View::composer('frontend.partials.latest_portfolio', function ($view) {
            $view->with([
                'portfolioProjects' => Project::forHomePage(),
                'portfolioVisible' => Section::isVisible(Section::HOME_PORTFOLIO),
            ]);
        });

        // My Skill (HOME-4): heading and visible skills, hidden when switched off.
        View::composer('frontend.partials.my_skill', function ($view) {
            $view->with([
                'skills' => Skill::forHomePage(),
                'skillsSection' => Section::cached(Section::HOME_SKILLS),
                'skillsVisible' => Section::isVisible(Section::HOME_SKILLS),
            ]);
        });

        // Supported Companies (HOME-8): active logos, hidden when switched off.
        View::composer('frontend.partials.supported_company', function ($view) {
            $view->with([
                'clients' => Client::forHomePage(),
                'clientsVisible' => Section::isVisible(Section::HOME_CLIENTS),
            ]);
        });

        // Blog and News (HOME-10): heading and the latest published posts, hidden when switched off.
        View::composer('frontend.partials.blog_news', function ($view) {
            $view->with([
                'homePosts' => Post::forHomePage(),
                'blogSection' => Section::cached(Section::HOME_BLOG),
                'blogVisible' => Section::isVisible(Section::HOME_BLOG),
            ]);
        });

        // "Get in touch" heading, text and contact form (HOME-11), shared by the About and Contact pages.
        View::composer('frontend.partials.get_in_touch', function ($view) {
            $section = Section::cached(Section::CONTACT_GET_IN_TOUCH);
            $defaults = ContactDetails::GET_IN_TOUCH_DEFAULTS;

            $view->with([
                'touchEyebrow' => $section ? $section->eyebrow : $defaults['eyebrow'],
                'touchTitle' => $section?->title ?: $defaults['title'],
                'touchText' => $section ? $section->subtitle : $defaults['subtitle'],
            ]);
        });

        // Testimonials (HOME-9): big title and visible slides, hidden when switched off.
        View::composer('frontend.partials.testimonial', function ($view) {
            $view->with([
                'testimonials' => Testimonial::forHomePage(),
                'testimonialsSection' => Section::cached(Section::HOME_TESTIMONIALS),
                'testimonialsVisible' => Section::isVisible(Section::HOME_TESTIMONIALS),
            ]);
        });
    }

    /**
     * The address a visitor is rate limited by: IPv4 as is, IPv6 by its /64 network, since one
     * IPv6 connection usually controls a whole /64 and could otherwise rotate through it.
     */
    public static function rateLimitAddress(string $ip): string
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $ip;
        }

        return inet_ntop(substr(inet_pton($ip), 0, 8).str_repeat("\0", 8)).'/64';
    }
}
