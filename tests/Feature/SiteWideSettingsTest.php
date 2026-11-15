<?php

use App\Models\BannerSection;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\BannerSectionSeeder;
use Illuminate\Support\Facades\Storage;

/**
 * The part of the page between two markers.
 */
function htmlBetween(string $html, string $start, string $end): string
{
    $from = strpos($html, $start);

    if ($from === false) {
        return '';
    }

    $to = strpos($html, $end, $from + strlen($start));

    return substr($html, $from, ($to === false ? strlen($html) : $to) - $from);
}

function headerHtml(string $html): string
{
    return htmlBetween($html, '<header class="tmp-header-area-start', '<!-- tpm-header-area end -->');
}

function panelHtml(string $html): string
{
    return htmlBetween($html, '<div class="tmp-sidebar-area', '<div class="tmp-popup-mobile-menu');
}

function mobileMenuHtml(string $html): string
{
    return htmlBetween($html, '<div class="tmp-popup-mobile-menu', '</header>') ?: htmlBetween($html, '<div class="tmp-popup-mobile-menu', '<footer');
}

function footerHtml(string $html): string
{
    return htmlBetween($html, '<footer class="footer-area', '</body>');
}

beforeEach(function () {
    Storage::fake('public');
    $this->seed(BannerSectionSeeder::class);
});

// ---------- Theme content is gone ----------

test('no theme placeholder content is left in the header, menus, footer or page head', function (string $url) {
    $html = $this->get($url)->assertOk()->getContent();

    expect($html)
        ->not->toContain('Personal Portfolio HTML Template')
        ->not->toContain('Webflow')
        ->not->toContain('+92 (8800)')
        ->not->toContain('example@info.com')
        ->not->toContain('66 Broklyant')
        ->not->toContain('nafiz125@gmail.com')
        ->not->toContain('Ranchview')
        ->not->toContain('01245789321')
        ->not->toContain('InversWeb')
        ->not->toContain('document.write')
        ->not->toContain('href="index.html"')
        ->not->toContain('href="contact.html"')
        ->not->toContain('service.html')
        ->not->toContain('team.html')
        ->not->toContain('Trams & Condition')
        ->not->toContain('href="#"><i class="fa-brands');
})->with(['/', '/about', '/contact', '/blog', '/projects']);

// ---------- Identity ----------

test('the site name, description and favicon come from the settings, after the page title', function () {
    Setting::put('site.name', 'Ada Lovelace');
    Setting::put('site.tagline', 'Engineer & writer.');

    $this->get('/')->assertSee('<title>Ada Lovelace</title>', false)->assertSee('content="Engineer &amp; writer."', false);
    $this->get('/about')->assertSee('<title>About | Ada Lovelace</title>', false);
    $this->get('/contact')->assertSee('<title>Contact | Ada Lovelace</title>', false)
        ->assertSee('<link rel="icon" href="'.asset('frontend/assets/images/favicon.svg').'">', false);

    Setting::put('site.favicon', 'settings/0b9c2f4e-2a8d-4c55-9b51-3f2d1c7a8e10.webp');
    $this->get('/contact')->assertSee('<link rel="icon" href="'.Storage::disk('public')->url('settings/0b9c2f4e-2a8d-4c55-9b51-3f2d1c7a8e10.webp').'">', false);
});

test('uploaded logos replace the theme logos in the header, panel, mobile menu and footer', function () {
    Setting::put('site.name', 'Ada Lovelace');
    Setting::put('site.logo', 'settings/6f1e8a52-7c3b-4d9e-a1f0-2b6c9d4e5f70.webp');

    $html = $this->get('/')->getContent();
    $logo = 'src="'.Storage::disk('public')->url('settings/6f1e8a52-7c3b-4d9e-a1f0-2b6c9d4e5f70.webp').'" alt="Ada Lovelace"';

    expect(headerHtml($html))->toContain($logo)
        ->and(panelHtml($html))->toContain($logo)
        ->and(mobileMenuHtml($html))->toContain($logo)
        ->and(footerHtml($html))->toContain($logo)
        ->and(headerHtml($html))->toContain('src="'.asset('frontend/assets/images/logo/logo-white.png').'"');
});

test('the copyright line fills in the year and site name on the server', function () {
    Setting::put('site.name', 'Ada Lovelace');
    Setting::put('site.copyright', '© {year} {site}. Made in London.');

    $this->get('/')->assertSee('© '.now()->year.' Ada Lovelace. Made in London.');
});

// ---------- Social links ----------

test('only the social links that are set are shown, opening in a new tab', function () {
    Setting::put('social.github', 'https://github.com/ada');
    Setting::put('social.instagram', '');

    $html = $this->get('/')->getContent();
    $link = '<a href="https://github.com/ada" target="_blank" rel="noopener" aria-label="GitHub"><i class="fa-brands fa-github"';

    expect(headerHtml($html))->toContain($link)->not->toContain('fa-instagram')
        ->and(panelHtml($html))->toContain($link)
        ->and(mobileMenuHtml($html))->toContain($link)
        ->and(footerHtml($html))->toContain($link);
});

test('with no social links the icon rows and their labels are hidden', function () {
    Setting::put('offcanvas.social_label', 'Find with me');

    $html = $this->get('/')->getContent();

    expect($html)->not->toContain('fa-brands')
        ->and(panelHtml($html))->not->toContain('Find with me');
});

test('stored social links that are not https are never printed', function () {
    Setting::put('social.x', 'javascript:alert(1)');
    Setting::put('social.facebook', 'http://facebook.com/ada');

    $this->get('/')->assertDontSee('javascript:alert(1)', false)->assertDontSee('facebook.com/ada', false);
});

// ---------- Menu panel and mobile menu ----------

test('the menu panel shows its text and the contact details as links', function () {
    Setting::put('offcanvas.heading', 'Hello from Ada');
    Setting::put('offcanvas.bio', 'I build analytical engines.');
    Setting::put('offcanvas.phone_label', 'Ring me');
    Setting::put('contact.phones', ['+44 20 7946 0000']);
    Setting::put('contact.emails', ['ada@example.test']);
    Setting::put('contact.address_lines', ['12 Analytical Street', 'London']);

    expect(panelHtml($this->get('/')->getContent()))
        ->toContain('Hello from Ada')
        ->toContain('I build analytical engines.')
        ->toContain('Ring me')
        ->toContain('<a href="tel:+442079460000" class="number">+44 20 7946 0000</a>')
        ->toContain('<a href="mailto:ada@example.test" class="number">ada@example.test</a>')
        ->toContain('12 Analytical Street, London')
        ->toContain('src="'.asset('frontend/assets/images/logo/man.png').'"');
});

test('the mobile menu links to the real pages and has no services dropdown yet', function () {
    expect(mobileMenuHtml($this->get('/')->getContent()))
        ->toContain('href="'.url('/').'"')
        ->toContain('href="'.route('contact.index').'"')
        ->toContain('href="'.route('projects.index').'"')
        ->not->toContain('has-dropdown');
});

// ---------- Footer ----------

test('the footer shows the tagline, quick links and contact details', function () {
    Setting::put('footer.tagline_highlight', 'Let us');
    Setting::put('footer.tagline', 'build together');
    Setting::put('footer.links_title', 'Explore');
    Setting::put('footer.links', [['label' => 'Writing', 'url' => '/blog'], ['label' => 'Dribbble', 'url' => 'https://dribbble.com/ada']]);
    Setting::put('footer.contact_title', 'Reach me');
    Setting::put('contact.emails', ['ada@example.test']);

    expect(footerHtml($this->get('/')->getContent()))
        ->toContain('<span>Let us</span> build together')
        ->toContain('Explore')
        ->toContain('<a href="'.url('/blog').'" >Writing</a>')
        ->toContain('<a href="https://dribbble.com/ada"  target="_blank" rel="noopener" >Dribbble</a>')
        ->toContain('Reach me')
        ->toContain('<a href="mailto:ada@example.test">ada@example.test</a>')
        ->toContain('<a href="'.route('contact.index').'">Contact Us</a>')
        ->not->toContain('Privacy Policy')
        ->not->toContain('newsletter-form-1');
});

test('stored quick links that point elsewhere are never printed', function () {
    Setting::put('footer.links', [['label' => 'Bad', 'url' => 'javascript:alert(1)'], ['label' => 'Off site', 'url' => '//evil.example'], ['label' => 'Fine', 'url' => '/about']]);

    expect(footerHtml($this->get('/')->getContent()))
        ->not->toContain('javascript:')
        ->not->toContain('evil.example')
        ->toContain('Fine');
});

test('the contact column and quick links hide when empty', function () {
    Setting::put('footer.links', []);

    expect(footerHtml($this->get('/')->getContent()))
        ->not->toContain('quick-link-wrap')
        ->not->toContain('ft-title">Contact');
});

// ---------- M3 exit check ----------

test('changing the contact details in the admin updates every public place', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->put(route('admin.contact.update'), [
        'address_title' => 'Address', 'address_lines' => ['12 Analytical Street', 'London'],
        'email_title' => 'E-mail', 'emails' => ['ada@example.test'],
        'phone_title' => 'Call Me', 'phones' => ['+44 20 7946 0000'],
        'touch_title' => 'Get in touch',
    ])->assertSessionHasNoErrors();
    auth()->logout();

    $home = $this->get('/')->getContent();
    $banner = htmlBetween($home, '<div class="rpp-banner-five-area', '<div class="banner-shape-img-bg');

    // Banner, menu panel, footer and Contact page.
    foreach ([$banner, panelHtml($home), footerHtml($home), $this->get('/contact')->getContent()] as $html) {
        expect($html)->toContain('ada@example.test')->toContain('+44 20 7946 0000');
    }

    expect(BannerSection::query()->first()->getAttributes())->not->toHaveKey('email');
});

// ---------- Robustness (M3 review L1, L2, I2) ----------

test('settings stored with the wrong type fall back to the defaults instead of breaking pages', function (mixed $value) {
    foreach (['site.name', 'site.copyright', 'site.tagline', 'offcanvas.heading', 'offcanvas.phone_label', 'footer.links_title', 'footer.links', 'social.github'] as $key) {
        Setting::put($key, $value);
    }

    $this->get('/')->assertOk()->assertSee('<title>'.e(config('app.name')).'</title>', false);
    $this->get('/blog')->assertOk();
    $this->actingAs(User::factory()->admin()->create())->get(route('admin.settings.index'))->assertOk();
})->with([[['x']], [42], [null]]);

test('stored keys can not override the checked footer links, copyright or panel image', function () {
    Setting::put('footer.quick_links', [['label' => 'Evil', 'url' => 'javascript:alert(1)', 'external' => false]]);
    Setting::put('footer.copyright', '<b>raw</b>');
    Setting::put('offcanvas.image_url', 'javascript:x');

    $html = $this->get('/')->getContent();

    // (The theme's own menu overlay uses href="javascript:void(0);", so only the injected values are checked.)
    expect($html)->not->toContain('javascript:alert')->not->toContain('javascript:x')->not->toContain('Evil')->not->toContain('&lt;b&gt;raw');
});

test('a quick link only opens in a new tab when it leaves this site', function () {
    $host = parse_url(url('/'), PHP_URL_HOST);
    Setting::put('footer.links', [
        ['label' => 'Lookalike', 'url' => 'https://'.$host.'.evil.example/x'],
        ['label' => 'Same host', 'url' => 'https://'.$host.'/about'],
    ]);

    $footer = footerHtml($this->get('/')->getContent());

    expect($footer)
        ->toContain('<a href="https://'.$host.'.evil.example/x"  target="_blank" rel="noopener" >Lookalike</a>')
        ->toContain('<a href="https://'.$host.'/about" >Same host</a>');
});

test('settings text is escaped everywhere', function () {
    Setting::put('site.name', '<script>alert(1)</script>');
    Setting::put('offcanvas.heading', '<img src=x onerror=alert(2)>');
    Setting::put('footer.links', [['label' => '<b>bold</b>', 'url' => '/about']]);
    Setting::put('site.copyright', '<i>{site}</i>');

    $this->get('/')
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('<img src=x onerror=alert(2)>', false)
        ->assertDontSee('<b>bold</b>', false)
        ->assertDontSee('<i>', false);
});
