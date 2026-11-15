<?php

use App\Models\ResumeEntry;
use App\Models\Section;
use App\Models\Service;
use App\Models\SkillBar;
use App\Models\Testimonial;
use Database\Seeders\AboutPageSeeder;
use Database\Seeders\CounterAreaSeeder;
use Database\Seeders\ResumeEntrySeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\TestimonialSeeder;

/**
 * Only the skill bar area of the About page.
 */
function skillAreaHtml(string $html): string
{
    $start = strpos($html, '<div class="tmp-skill-area');

    return $start === false ? '' : substr($html, $start, strpos($html, '<!-- tmp skill area end -->', $start) - $start);
}

test('the About page shows the bars from the database, in order, without hidden ones', function () {
    SkillBar::factory()->create(['name' => 'Second Tool', 'percent' => 60, 'sort_order' => 2]);
    SkillBar::factory()->create(['name' => 'First Tool', 'percent' => 95, 'sort_order' => 1]);
    SkillBar::factory()->inactive()->create(['name' => 'Hidden Tool']);
    SkillBar::factory()->development()->create(['name' => 'Laravel', 'percent' => 80]);

    $area = skillAreaHtml($this->get(route('about.index'))->assertOk()->getContent());

    expect($area)
        ->toContain('Design Skill')
        ->toContain('Development Skill')
        ->toContain('style="width: 95%;')
        ->toContain('aria-valuenow="95"')
        ->toContain('<span class="percent-label">80%</span>')
        ->not->toContain('Hidden Tool')
        ->not->toContain('PHOTOSHOT')
        ->not->toContain('src="assets/')
        ->and(strpos($area, 'First Tool'))->toBeLessThan(strpos($area, 'Second Tool'));
});

test('saved column titles are used and a switched-off or empty column is hidden', function () {
    SkillBar::factory()->create(['name' => 'Figma']);
    SkillBar::factory()->development()->create(['name' => 'Laravel']);
    Section::query()->create(['key' => Section::ABOUT_SKILLS_DESIGN, 'title' => 'My Design Tools', 'is_active' => true]);
    Section::query()->create(['key' => Section::ABOUT_SKILLS_DEVELOPMENT, 'title' => 'Code', 'is_active' => false]);

    $area = skillAreaHtml($this->get(route('about.index'))->getContent());

    expect($area)->toContain('My Design Tools')->toContain('Figma')->not->toContain('Laravel');

    SkillBar::query()->get()->each->delete();
    $this->get(route('about.index'))->assertOk()->assertDontSee('tmp-skill-area', false);
});

test('the reused sections come from the database', function () {
    $this->seed([ServiceSeeder::class, CounterAreaSeeder::class, ResumeEntrySeeder::class, TestimonialSeeder::class, AboutPageSeeder::class]);

    $html = $this->get(route('about.index'))->assertOk()->getContent();
    $service = Service::query()->where('is_active', true)->first();
    $education = ResumeEntry::query()->where('type', ResumeEntry::TYPE_EDUCATION)->first();

    expect($html)
        ->toContain('service-card-v1')
        ->toContain(e($service->title))
        ->toContain('counter-area tmp-section-gapBottom')
        ->toContain('education-experience tmp-section-gapBottom')
        ->toContain(e($education->period))
        ->toContain('testimonial-area tmp-section-gapBottom')
        ->toContain(e(Testimonial::query()->first()->name))
        ->toContain('Photoshop');
});

test('the get in touch text comes from the admin and the form never posts to the theme vendor', function () {
    Section::query()->create([
        'key' => Section::CONTACT_GET_IN_TOUCH,
        'eyebrow' => 'Say hello',
        'title' => 'Let us work together',
        'subtitle' => 'I reply within a day.',
    ]);

    $this->get(route('about.index'))
        ->assertSee('Say hello')
        ->assertSee('Let us work together')
        ->assertSee('I reply within a day.')
        ->assertSee('Send Message')
        ->assertDontSee('Appointment Now')
        ->assertDontSee('inversweb.com')
        ->assertDontSee('mailer.php');
});

test('theme defaults show until the get in touch text is saved, and an emptied small heading stays hidden', function () {
    $this->get(route('about.index'))->assertSee('Elevate your brand with Me')->assertDontSee('ished fact');

    Section::query()->create(['key' => Section::CONTACT_GET_IN_TOUCH, 'eyebrow' => '', 'title' => 'Hello', 'subtitle' => '']);

    $html = $this->get(route('about.index'))->getContent();
    $start = strpos($html, '<section class="get-in-touch-area');
    $touch = substr($html, $start, strpos($html, '</section>', $start) - $start);

    expect($touch)->toContain('Hello')->not->toContain('section-sub-title')->not->toContain('class="description');
});

test('the price plan is not shown', function () {
    $this->get(route('about.index'))->assertOk()->assertDontSee('our-price-plan-area', false)->assertDontSee('Per Month');
});

test('the home page spacing of the shared sections is unchanged', function () {
    $this->seed([CounterAreaSeeder::class, ResumeEntrySeeder::class, TestimonialSeeder::class]);

    $this->get('/')
        ->assertSee('<section class="counter-area ">', false)
        ->assertSee('education-experience tmp-section-gapTop', false)
        ->assertSee('testimonial-area tmp-section-gapTop', false);
});

test('the header links to the About page in both menus', function () {
    // The menus only: the footer's quick links are wired up with the settings in M3.
    $html = $this->get('/')->getContent();
    $menus = substr($html, 0, strpos($html, '<footer'));

    expect(substr_count($menus, 'href="'.route('about.index').'"'))->toBeGreaterThanOrEqual(2)
        ->and($menus)->not->toContain('href="about.html"');
});

test('text is escaped on the About page', function () {
    SkillBar::factory()->create(['name' => '<script>alert(1)</script>']);
    Section::query()->create(['key' => Section::ABOUT_SKILLS_DESIGN, 'title' => '<b>x</b>', 'is_active' => true]);
    Section::query()->create(['key' => Section::CONTACT_GET_IN_TOUCH, 'title' => '<img src=x onerror=alert(2)>']);

    $this->get(route('about.index'))
        ->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('<b>x</b>', false)
        ->assertDontSee('<img src=x onerror=alert(2)>', false);
});

test('the seeder fills both columns once', function () {
    $this->seed(AboutPageSeeder::class);
    $this->seed(AboutPageSeeder::class);

    expect(SkillBar::query()->where('group', 'design')->count())->toBe(4)
        ->and(SkillBar::query()->where('group', 'development')->count())->toBe(4);

    $area = skillAreaHtml($this->get(route('about.index'))->getContent());
    expect($area)->toContain('Photoshop')->toContain('PHP / Laravel');
});
