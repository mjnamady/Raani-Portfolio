---
description: Write feature tests for a portfolio feature, page or admin area
argument-hint: <feature, file, or requirement ID, e.g. "projects" or PRJ-4>
---

Use the **test-writer** agent to write tests for: $ARGUMENTS

1. Read the matching requirements in `docs/PRD.md` / `docs/MVP.md` and the code for the feature (routes, controller, Form Request, policy, model and scopes, Blade component).
2. List the test cases first, covering: admin happy path, public output (active/published shown, inactive/draft hidden everywhere), validation, guest access to `/admin`, mass assignment, and side effects (cache cleared, files stored/removed, emails queued).
3. Add the portfolio-specific cases that apply: uploads, rich-text sanitizing, slugs, ordering, settings propagation, form spam protection, SEO tags.
4. Add or update factories and states as needed.
5. Write the tests in `tests/Feature/<Area>/` using the project's framework, `RefreshDatabase`, the test database, `Storage::fake()` and `Mail::fake()`. Reference requirement IDs in test names.
6. Run `php artisan test --filter=<Area>`, then the full suite.
7. Do not change app code to make tests pass, and ask before installing any package. Report using the test-writer report format.
