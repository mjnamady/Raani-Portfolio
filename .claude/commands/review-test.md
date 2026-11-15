---
description: Review existing portfolio tests for coverage gaps and quality issues
argument-hint: <test file, folder, or area, e.g. tests/Feature/Admin/Projects>
---

Review the tests in: $ARGUMENTS

Read the tests, the code they cover and the matching requirements in `docs/PRD.md` / `docs/MVP.md`, then run them with `php artisan test --filter=<target>`. Check:

- **Coverage:** every in-scope requirement and MVP story for this area has a test; guest access to every `/admin` route is tested; drafts and inactive items are proven hidden on every public surface (sections, lists, detail pages, previous/next, sitemap).
- **Portfolio risks:** uploads (accepted and rejected types, old files removed), rich-text sanitizing, escaped plain-text output, form throttling and honeypot, settings changes reaching every page, cache clearing.
- **Assertions:** tests check database state and rendered content, not only status codes; validation tests name the failing fields and confirm nothing was saved.
- **Isolation:** `RefreshDatabase`, test database only, factories, frozen time for `published_at` logic, `Storage::fake()`, `Mail::fake()`.
- **Reliability:** no order dependence, real dates, sleeps or network calls.
- **Smells:** skipped or weakened assertions, duplicated setup that belongs in helpers or `beforeEach`, multi-behaviour tests, unclear names.

Do not edit files. Report:

```
## Test Review: <target>
Result: <passed/failed/skipped>
### Missing tests (by requirement ID)
### Weak or incorrect tests (file:line, problem, fix)
### Suggested additions for test-writer
```
