---
description: Refactor portfolio code safely without changing behaviour or design
argument-hint: <file, class, area, or Blade component, plus optional goal>
---

Refactor: $ARGUMENTS

Rules: **behaviour and visual output must not change.** No new features and no changes to routes, URLs, slugs, request fields, validation rules, access control, database schema or the rendered theme markup unless explicitly asked. Follow `CLAUDE.md` boundaries and permission rules (ask before installing or removing packages).

1. **Baseline.** Run `php artisan test`. If the target has no tests, use the **test-writer** agent to cover its current behaviour first and get them green. For Blade changes, note the current rendered HTML of affected pages to compare afterwards.
2. **Diagnose.** List the problems before changing anything, for example:
   - fat admin controllers (move upload, sanitizing and slug logic to shared services or traits; validation to Form Requests);
   - repeated CRUD code across content types that belongs in a base controller, trait or shared Blade partial (form fields, image input, status badge, delete modal);
   - duplicated theme markup across pages that should be a Blade component (section heading, page banner, project card, post card, contact form);
   - settings or section lookups scattered in views instead of the `Settings` service / View Composer;
   - N+1 queries and missing eager loading; repeated query conditions that belong in scopes (`published()`, `active()`, `ordered()`);
   - cache keys duplicated as strings (centralise them);
   - magic strings for statuses and message sources (use enums or constants);
   - unclear names and long methods.
3. **Change in small steps.** One kind of change at a time, running the related tests after each step.
4. **Keep security and design intact.** Never remove middleware, `@csrf`, throttling, honeypots, sanitizing, escaping or the `published()` scope; never switch `{{ }}` to `{!! !!}`; never edit files in `public/assets`. Keep the theme's HTML structure and CSS classes identical.
5. **Verify.** Run the full suite and `./vendor/bin/pint`. For Blade refactors, compare the rendered HTML of affected pages with the baseline.

Report: problems found, what changed and why (file by file), test results before and after, any rendered-output differences, and what is left for later. Suggest running the **security-reviewer** if admin access, uploads, forms, rich text or caching code was touched.
