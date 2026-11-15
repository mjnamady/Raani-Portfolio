# Reeni Portfolio (Laravel 13)

Dynamic personal portfolio built on the Reeni Bootstrap theme. Every piece of public content is managed from a private custom Bootstrap admin panel.

- Requirements: @docs/PRD.md
- Build plan and current scope: @docs/MVP.md

## Stack

Laravel 13 (PHP 8.3+), MySQL, Blade, Bootstrap, JavaScript (theme jQuery plugins), XAMPP for local development, Mailpit for local email.

## Commands

- Run tests: `php artisan test` (single area: `php artisan test --filter=<Name>`)
- After pulling: `composer install && php artisan migrate --seed && php artisan storage:link`
- Queue worker (emails): `php artisan queue:work`
- Code style: `./vendor/bin/pint`
- Local mail inbox: Mailpit at http://localhost:8025

## Project structure

- Theme assets: `public/frontend/assets/` (read-only, see Rules)
- Admin theme (Cork): `public/backend/` (read-only); admin overrides in `public/backend/custom/`
- Public layout: `resources/views/layouts/frontend.blade.php`; partials in `resources/views/frontend/partials/` (`layouts/app.blade.php` is Breeze's layout, not the public one)
- Public sections: Blade components in `resources/views/components/sections/`
- Admin layout: `resources/views/layouts/admin.blade.php`; admin views in `resources/views/admin/`
- Admin controllers: `app/Http/Controllers/Admin/`

## Rules

- Never edit files in `public/frontend/assets` or `public/backend/src` (themes). Overrides go in `custom.css` / `custom.js` (public) or `public/backend/custom/` (admin) only.
- All admin routes live in the `/admin` group with `auth` + `EnsureAdmin` middleware. There is no public registration.
- Validate with Form Requests and use `$request->validated()`; authorize with Policies.
- Models use `$fillable`; never `$guarded = []`.
- Rich text must pass through the HTML sanitizer before display. `{!! !!}` is allowed only for sanitized rich-text fields; use `{{ }}` everywhere else.
- Public queries for projects and posts must use the `published()` scope.
- Images are validated (type and size), resized, renamed and stored on the `public` disk.
- Saving content must clear the related cache (settings, sections, home collections).
- Tests use the separate test database only, never the development or production database.

## 🚫 Context Boundaries: files Claude must NEVER touch

The following files are completely off-limits. Never read, modify, or delete them:

- `.env` and `.env.*` files (contain secrets)
- `vendor/` folder (managed by Composer)
- `node_modules/` folder (managed by NPM)
- `storage/app/` (real user uploads)
- `storage/logs/` (production logs)
- `bootstrap/cache/` (auto-generated)
- Any file matching `*.key` or `*.pem` (cryptographic keys)
- Any migration with a timestamp older than [INSERT PRODUCTION LAUNCH DATE]. To change the schema after launch, create a new migration instead.

## 🛑 Destructive Operations: always confirm first

NEVER execute these without explicit, in-the-moment permission from me:

- `php artisan migrate:fresh` (deletes all data)
- `php artisan migrate:rollback` past more than 1 step
- `DB::table()->truncate()` or `delete()` on more than 100 records
- `rm -rf` or any recursive deletion
- `composer remove` any package
- `npm uninstall` any package
- Any direct database modification on production
- Force-pushing to git (`git push --force`)
- Modifying webhook URLs in production Stripe (if Stripe is added later)

## ✅ Operations that need permission

Always ask before:

- Installing a new Composer or NPM package
- Adding a new database column to a table that already has production data
- Changing the contract of any route or endpoint already deployed (URLs, request fields, responses)
- Modifying authentication logic
- Modifying payment logic (if payments are added later)
- Changing role or permission definitions

## Workflow

For every feature: plan → build → `/write-test` → security-reviewer agent → `/refactor` if needed → commit.

**Plan first, always.** Before writing any code for a feature, present an implementation plan and wait for my approval. The plan covers: data model and migrations, routes, controllers/requests/policies, admin UI (layout and look), public output, caching, seed data, tests, and open questions with a recommended answer for each. Only start building after I approve (or adjust) the plan.
Use `/generate-crud` for new admin-managed content types. Each feature is done only when tests pass and the security review has no Critical or High findings.

## 🧭 Development guidance: follow the MVP and suggest the next step

The build order is defined in `docs/MVP.md` §5 (milestones M0 → M7). Progress is tracked in `docs/PROGRESS.md`.

### At the start of a session
- Read `docs/PROGRESS.md` to see the current milestone, what is done, and what is still open. If the file does not exist, create it from the MVP milestones with every item unchecked.
- If my request belongs to a later milestone while an earlier one is unfinished, point it out in one line, say what is still missing, and ask whether to continue anyway.
- Never build features that `docs/MVP.md` §2.2 lists as deferred unless I ask for them explicitly.

### After completing a module or functionality
End the reply with a short **Status & Next Step** block, in this format:

```
## ✅ Status
- Done: <what was completed> (requirement IDs)
- Checks: tests <pass/fail>, security review <done/not yet>, refactor <needed/not needed>
- Milestone: <M#> — <n of n> items complete

## 👉 Next best step
<one concrete recommended action, e.g. "Run /write-test projects" or "Start M5: posts with categories and tags">
Why: <one sentence, tied to the MVP order or a dependency>
Then: <the step after that>
```

Choose the next step using this priority order:
1. **Finish the quality loop first.** If the feature has no tests yet, the next step is `/write-test`. If tests pass but no security review was done, the next step is the security-reviewer. If either found problems, the next step is fixing them.
2. **Close the current milestone.** Suggest the next unchecked item in the current milestone in `docs/MVP.md` §5, respecting dependencies (e.g. settings before the components that read them, the image upload service before projects and posts, the inbox before notifications).
3. **Move to the next milestone** only when every item and exit check of the current one is met. Summarise the milestone's exit check first.
4. **After M7**, verify the Definition of Done (`docs/MVP.md` §7) item by item; only then suggest post-MVP features from `docs/MVP.md` §2.2, starting with the ones I have answered open questions for.

Keep the block short. Do not start the suggested step yourself; wait for my go-ahead.

### Keeping progress up to date
- After a step is completed and its tests pass, tick it in `docs/PROGRESS.md` and add a one-line dated note (what was done, requirement IDs).
- Record decisions on the open questions (`docs/PRD.md` §10, `docs/MVP.md` §9) in `docs/PROGRESS.md` under "Decisions" when I make them.
- If a change affects the plan (new requirement, changed scope, dropped feature), tell me and propose the edit to `docs/PRD.md` or `docs/MVP.md` instead of silently diverging from them.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.3. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

</laravel-boost-guidelines>
