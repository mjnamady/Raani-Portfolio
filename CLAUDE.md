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

- Theme assets: `public/assets/` (read-only, see Rules)
- Public layout: `resources/views/layouts/app.blade.php`; partials in `resources/views/partials/`
- Public sections: Blade components in `resources/views/components/sections/`
- Admin layout: `resources/views/layouts/admin.blade.php`; admin views in `resources/views/admin/`
- Admin controllers: `app/Http/Controllers/Admin/`

## Rules

- Never edit files in `public/assets` (theme). Overrides go in `custom.css` / `custom.js` only.
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

For every feature: build → `/write-test` → security-reviewer agent → `/refactor` if needed → commit.
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
