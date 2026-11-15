<?php

use App\Http\Controllers\Frontend\FrontendController;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectImage;
use App\Models\Section;
use App\Models\Skill;
use App\Models\Tag;
use Database\Seeders\ProjectSeeder;

function publishedProject(array $attributes = []): Project
{
    return Project::factory()->published()->create($attributes);
}

// ---------- Home: Latest Portfolio ----------

test('the home page shows the featured published projects linking to their pages', function () {
    $featured = publishedProject(['title' => 'Featured Work', 'tagline' => 'Brand strategy', 'is_featured' => true]);
    Project::factory()->featured()->create(['title' => 'Secret Draft']);

    $this->get('/')
        ->assertOk()
        ->assertSee('latest-portfolio-area', false)
        ->assertSee('Featured Work')
        ->assertSee('Brand strategy')
        ->assertSee('href="'.route('projects.show', $featured->slug).'"', false)
        ->assertDontSee('Secret Draft');
});

test('the home section can be switched off, but My Skill stays', function () {
    publishedProject(['title' => 'Featured Work', 'is_featured' => true]);
    Skill::factory()->create();
    Section::query()->create(['key' => Section::HOME_PORTFOLIO, 'is_active' => false]);

    $this->get('/')
        ->assertOk()
        ->assertDontSee('latest-portfolio-area', false)
        ->assertDontSee('Featured Work')
        ->assertSee('my-skill', false);
});

test('the home section is hidden when nothing is published', function () {
    Project::factory()->featured()->create();
    Skill::factory()->create();

    $this->get('/')->assertOk()->assertDontSee('latest-portfolio-area', false)->assertSee('my-skill', false);
});

test('changes in the admin show up on the home page right away', function () {
    $project = publishedProject(['title' => 'Before', 'is_featured' => true]);
    $this->get('/')->assertSee('Before');

    $project->update(['title' => 'After']);

    $this->get('/')->assertSee('After')->assertDontSee('>Before<', false);
});

// ---------- Projects page ----------

test('the projects page lists published projects with their tags', function () {
    $project = publishedProject(['title' => 'Live Project']);
    $project->tags()->sync([Tag::factory()->create(['name' => 'Figma'])->id => ['sort_order' => 0]]);
    Project::factory()->create(['title' => 'Draft Project']);
    Project::factory()->create(['title' => 'Scheduled Project', 'status' => 'published', 'published_at' => now()->addWeek()]);

    $this->get(route('projects.index'))
        ->assertOk()
        ->assertSee('Live Project')
        ->assertSee('<a class="tag-item">Figma</a>', false)
        ->assertSee('href="'.route('projects.show', $project->slug).'"', false)
        ->assertDontSee('Draft Project')
        ->assertDontSee('Scheduled Project');
});

test('the old projects URL redirects permanently', function () {
    $this->get('/all/projects')->assertStatus(301)->assertRedirect('/projects');
});

test('category tabs only list categories with published projects and filter the list', function () {
    $branding = ProjectCategory::factory()->create(['name' => 'Branding']);
    $design = ProjectCategory::factory()->create(['name' => 'Design']);
    $empty = ProjectCategory::factory()->create(['name' => 'Empty Category']);

    publishedProject(['title' => 'Brand Book', 'category_id' => $branding->id]);
    publishedProject(['title' => 'App Screens', 'category_id' => $design->id]);
    Project::factory()->create(['title' => 'Hidden Draft', 'category_id' => $empty->id]);

    $this->get(route('projects.index'))
        ->assertSee('Branding')->assertSee('Design')
        ->assertDontSee('Empty Category');

    $this->get(route('projects.index', ['category' => $branding->slug]))
        ->assertOk()
        ->assertSee('Brand Book')
        ->assertDontSee('App Screens')
        ->assertSee('class="nav-link active" href="'.route('projects.index', ['category' => $branding->slug]).'"', false);
});

test('an unknown category shows all projects', function () {
    publishedProject(['title' => 'Any Project']);

    $this->get(route('projects.index', ['category' => 'does-not-exist']))->assertOk()->assertSee('Any Project');
});

test('a malformed category shows all projects instead of an error page', function () {
    Project::factory()->published()->create(['title' => 'Still Listed']);

    $this->get('/projects?category[]=x')->assertOk()->assertSee('Still Listed');
});

test('the projects page is paginated and keeps the category in page links', function () {
    $category = ProjectCategory::factory()->create(['name' => 'Branding']);
    $perPage = FrontendController::PROJECTS_PER_PAGE;

    foreach (range(1, $perPage + 2) as $i) {
        $project = publishedProject(['title' => "Project {$i}", 'category_id' => $category->id]);
        $project->forceFill(['sort_order' => $i])->save();
    }

    $this->get(route('projects.index', ['category' => $category->slug]))
        ->assertSee('Project 1')
        ->assertDontSee('Project '.($perPage + 1))
        ->assertSee('tmp-pagination-button', false)
        ->assertSee('category='.$category->slug.'&amp;page=2', false);

    $this->get(route('projects.index', ['category' => $category->slug, 'page' => 2]))
        ->assertSee('Project '.($perPage + 1))
        ->assertDontSee('>Project 1<', false);
});

test('the projects page has a friendly message when nothing is published', function () {
    $this->get(route('projects.index'))->assertOk()->assertSee('No projects to show yet.');
});

// ---------- Project details ----------

test('the details page shows everything about a published project', function () {
    $category = ProjectCategory::factory()->create(['name' => 'Branding']);
    $project = publishedProject([
        'title' => 'Fintech App',
        'category_id' => $category->id,
        'client_name' => 'Acme Bank',
        'project_date' => '2024-01-23',
        'content' => '<h3>The brief</h3><p>Make banking simple.</p>',
        'features' => ['UI design', 'Prototyping'],
        'external_url' => 'https://acme.example',
    ]);
    $project->tags()->sync([Tag::factory()->create(['name' => 'Figma'])->id => ['sort_order' => 0]]);
    $image = new ProjectImage(['alt' => 'Login screen']);
    $image->path = 'frontend/assets/images/projects-details/project-detials-swiper-img-1.jpg';
    $image->sort_order = 1;
    $project->images()->save($image);

    $this->get(route('projects.show', $project->slug))
        ->assertOk()
        ->assertSee('<h2 class="title">Fintech App</h2>', false)
        ->assertSee('<h3>The brief</h3><p>Make banking simple.</p>', false)
        ->assertSeeInOrder(['UI design', 'Prototyping'])
        ->assertSee('project-details-swiper', false)
        ->assertSee('alt="Login screen"', false)
        ->assertSee('Acme Bank')
        ->assertSee('23 January, 2024')
        ->assertSee('Figma')
        ->assertSee('href="'.route('projects.index', ['category' => 'branding']).'"', false)
        ->assertSee('href="https://acme.example" target="_blank" rel="noopener nofollow"', false);
});

test('drafts, scheduled projects and unknown slugs are 404', function () {
    $draft = Project::factory()->create();
    $scheduled = Project::factory()->create(['status' => 'published', 'published_at' => now()->addDay()]);

    $this->get(route('projects.show', $draft->slug))->assertNotFound();
    $this->get(route('projects.show', $scheduled->slug))->assertNotFound();
    $this->get(route('projects.show', 'no-such-project'))->assertNotFound();
});

test('previous and next links follow the projects page order and skip drafts', function () {
    $first = publishedProject(['title' => 'First Project']);
    $first->forceFill(['sort_order' => 1])->save();
    $draft = Project::factory()->create(['title' => 'Draft In Between']);
    $draft->forceFill(['sort_order' => 2])->save();
    $second = publishedProject(['title' => 'Second Project']);
    $second->forceFill(['sort_order' => 3])->save();

    $this->get(route('projects.show', $first->slug))
        ->assertSee('href="'.route('projects.show', $second->slug).'"', false)
        ->assertDontSee('project-pager-prev', false)
        ->assertDontSee('Draft In Between');

    $this->get(route('projects.show', $second->slug))
        ->assertSee('href="'.route('projects.show', $first->slug).'"', false)
        ->assertDontSee('project-pager-next', false);
});

test('a project without optional parts still renders cleanly', function () {
    $project = publishedProject(['content' => null, 'features' => [], 'client_name' => null, 'project_date' => null]);

    $this->get(route('projects.show', $project->slug))
        ->assertOk()
        ->assertDontSee('check-box-wrap', false)
        ->assertDontSee('project-details-swiper', false)
        ->assertDontSee('Author:');
});

test('text is escaped and rich content can not run scripts on the public pages', function () {
    $project = Project::factory()->published()->featured()->create([
        'title' => '<script>alert(1)</script>',
        'tagline' => '<img src=x onerror=alert(2)>',
        'content' => '<p>Safe</p><script>alert(3)</script><a href="javascript:alert(4)">x</a>',
        'features' => ['<b onmouseover=alert(5)>f</b>'],
    ]);

    foreach (['/', route('projects.index'), route('projects.show', $project->slug)] as $url) {
        $this->get($url)
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<img src=x onerror=alert(2)>', false)
            ->assertDontSee('<script>alert(3)</script>', false)
            ->assertDontSee('javascript:alert(4)', false)
            ->assertDontSee('<b onmouseover=alert(5)>', false);
    }
});

test('the project pages load the theme scripts once', function () {
    $project = publishedProject();

    $html = $this->get(route('projects.show', $project->slug))->getContent();

    expect(substr_count($html, 'frontend/assets/js/main.js'))->toBe(1)
        ->and($html)->toContain('frontend/custom/custom.css');
});

test('the seeded demo projects render on all three pages', function () {
    $this->seed(ProjectSeeder::class);
    $project = Project::query()->ordered()->first();

    $this->get('/')->assertOk()->assertSee($project->title);
    $this->get(route('projects.index'))->assertOk()->assertSee($project->title)->assertSee('Branding');
    $this->get(route('projects.show', $project->slug))->assertOk()->assertSee($project->title)->assertSee('Elevate your business');
});
