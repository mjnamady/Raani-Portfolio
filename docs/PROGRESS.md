# Progress Tracker: Reeni Portfolio MVP

Current milestone: **M2 Admin foundation** (M0 still open: Git, Mailpit, theme console check)

Each item counts as done only when it is built, tested, and security-reviewed.

## M0 Setup
- [ ] Laravel 13 project running on XAMPP (PHP 8.3+)
- [ ] MySQL database + separate test database
- [ ] Git repository initialised
- [ ] Mailpit running for local email
- [ ] Theme assets copied to `public/assets`, loading without console errors

## M1 Theme to Blade (static)
- [ ] `layouts/app.blade.php`
- [ ] Partials: header, off-canvas menu, page banner, footer, chat popup
- [ ] Section components (banner, service cards, skills, counter, portfolio, testimonials, blog, contact, education, experience)
- [ ] Pages: Home, About, Services, Service details, Projects, Project details, Blog, Blog details, Contact, 404
- [ ] `custom.css` / `custom.js` for overrides
- [ ] Exit check: all pages match screenshots on desktop and mobile; sliders, counters and menus work

## M2 Admin foundation
- [x] Admin login, registration disabled
- [x] `layouts/admin.blade.php` (Bootstrap admin template) — built as `admin/layout/admin_dashboard_temp.blade.php` (Cork)
- [x] `EnsureAdmin` middleware + `/admin` route group
- [x] Change password + password reset
- [x] Login rate limiting
- [x] Exit check: only the owner reaches `/admin`; access tests pass

## M3 Settings, profile and sections
- [ ] `settings` table, seeder, cached Settings service, View Composer
- [ ] `sections` table and seeder (eyebrow, title, subtitle, on/off)
- [ ] Admin forms: Site Settings, Profile & Hero, Sections
- [ ] Header, off-canvas, banner, contact page and footer read from settings
- [ ] Exit check: changing contact details in admin updates every public location

## M4 Simple collections
- [ ] Stats
- [ ] Skills
- [ ] Testimonials
- [ ] Resume entries (education + experience)
- [ ] Services + service detail page
- [ ] Exit check: all home and about sections except portfolio and blog use live data

## M5 Projects and blog
- [ ] Image upload service (validate, resize, random name, remove old file)
- [ ] Rich-text editor + HTML sanitizer
- [ ] Project categories + projects + gallery + list and detail pages
- [ ] Post categories + tags + posts + list and detail pages
- [ ] Draft/published with `published()` scope
- [ ] Exit check: a published project and post appear on home, list and detail pages; drafts never appear

## M6 Forms and inbox
- [ ] `messages` table
- [ ] Contact form + chat popup (AJAX + no-JS fallback)
- [ ] Honeypot + throttling
- [ ] Queued owner email notification
- [ ] Admin inbox (read/unread, replied, delete) + dashboard counts
- [ ] Exit check: both forms reach the inbox and email; spam protection tests pass

## M7 SEO, polish and launch prep
- [ ] Meta + Open Graph tags on every page
- [ ] `sitemap.xml` + `robots.txt`
- [ ] Legal pages (terms, privacy)
- [ ] Themed 404/500
- [ ] Lazy-loading images, caching review, Lighthouse pass
- [ ] Real content entered; no theme placeholders left
- [ ] README
- [ ] Exit check: Definition of Done (`docs/MVP.md` §7) met

## Decisions
<!-- Record answers to open questions here, e.g. "2026-10-21: Admin panel = custom Bootstrap (no Filament)" -->

## Log
<!-- One dated line per completed step, e.g. "2026-10-22: M2 admin login done (ADM-1, ADM-8); tests pass; security review clean" -->
- 2026-10-20: M2 admin login by email or username, registration removed, config-driven AdminDataSeeder, login + password-reset throttling, neutral reset response, current password required for email change (ADM-1, ADM-8); tests 40/40 pass; security re-review clean (0 Critical/High)
- 2026-10-21: M2 `is_admin` flag + `EnsureAdmin` middleware + `/admin` group (dashboard, profile), admin-only login, per-account login throttle, login timebox, password-reset limiter, generic reset errors, last-admin delete guard, hardened AdminDataSeeder (ADM-1, A-1); tests 61/61 pass; security review clean (0 Critical/High, 4 Low open)
- 2026-10-22: M2 admin profile settings (DB-driven profile, `profile_image` column, GD image upload service with live preview, change password with strong rules + throttle), TestCase guard against cached config wiping the dev DB (ADM-5, ADM-8); tests 101/101 pass; security review clean (0 Critical/High, 3 Medium + 5 Low open)
- 2026-10-22: Incident: a test run with cached config wiped the dev MySQL DB; admin re-seeded from .env; guard added in tests/TestCase.php
- 2026-10-22: M2 security fixes: removed Breeze `PUT /password`, `account-security` rate limit on profile/password routes, `auth.session` signs out other sessions on password change, GD errors → validation error, image deleted only after save, 4000px cap + memory check, TestCase guard checks the resolved DB connection + `force="true"` in phpunit.xml (ADM-1, ADM-5, ADM-8); tests 112/112 pass. M2 exit check met.
