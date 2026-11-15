# MVP Plan: Dynamic Personal Portfolio (Reeni Theme)

| Field | Value |
|---|---|
| Related document | PRD.md |
| Version | 1.0 (Draft) |
| Stack | Laravel 13, MySQL, Blade, Bootstrap, JavaScript, XAMPP/Laragon |
| Target | A live portfolio where every visible piece of content is editable from the admin panel and every form reaches the owner |

---

## 1. MVP goal

Ship the smallest version of the site that looks exactly like the Reeni theme, contains **real content instead of demo content**, and can be **kept up to date without touching code**.

**The MVP is done when** the owner can log in to the admin panel and:
1. change their name, photo, bio, contact details and social links, and see them update across the site;
2. add a project with images and see it on the home page, the projects page and its own detail page;
3. publish a blog post and see it in the home "Blog and News" section and on the blog;
4. edit services, skills, stats, testimonials and resume entries;
5. receive a contact form or chat popup message in the admin inbox and by email.

All of this must work on mobile, pass the security checks, and leave no theme placeholder text on any public page.

---

## 2. Scope

### 2.1 In scope

**Public site**
- Theme converted to Blade: shared layout, header, off-canvas menu, page banner, footer, chat popup, back-to-top
- Pages: Home, About, Services, Service details, Projects, Project details, Blog, Blog details, Contact, Terms, Privacy, 404
- Home sections: Banner, Service cards, My Skill, Counter, Latest Portfolio, Testimonials, Blog and News, Get in Touch
- About sections: intro, Education, Experiences, Counter, Testimonials, contact form
- Contact form and chat popup saving to the inbox and emailing the owner
- Basic SEO: slugs, meta title/description, Open Graph tags, sitemap

**Admin panel (custom Bootstrap)**
- Login, logout, change password, password reset; registration disabled
- Dashboard with counts and latest messages
- Site Settings: identity, contact details, social links, footer text, SEO defaults
- Profile & Hero
- Section headings (eyebrow, title, subtitle, on/off) for the in-scope sections
- CRUD: Services, Skills, Stats, Projects (+ categories, gallery), Posts (+ categories, tags), Testimonials, Resume entries, Legal pages
- Messages inbox
- Image upload with validation and resizing; rich-text editor with sanitizing; ordering and active toggles

### 2.2 Deferred to post-MVP

| Feature | Reason for deferring |
|---|---|
| Price Plan section | Pricing intent still undecided (PRD open question 3); section hidden until then |
| Supported Companies logos | Needs real client logos with permission; hidden until available |
| Latest Service section (numbered list) | Duplicates service cards; can be added once services content exists |
| Newsletter sign-up and CSV export | Footer field hidden for MVP; inbox covers enquiries |
| Service categories sidebar | Can show a plain "Other services" list in MVP |
| Reordering home sections | Fixed order in MVP; on/off toggle is enough |
| Scheduled posts | Draft/published covers launch |
| Blog search, tag pages, RSS, share buttons, reading time | Useful once there are many posts |
| Acknowledgement email to visitors, double opt-in | Not required for launch |
| Two-factor login, draft preview links | Hardening/convenience for later |
| JSON-LD structured data | SEO extra |
| Maintenance mode page | Not needed at launch |

---

## 3. User stories and acceptance criteria

### Visitor

**V-1 First impression.** As a visitor, I see who the owner is the moment the home page loads.
- Banner shows the owner's real name, role, photo, bio, contact info and stats from the admin panel.
- No theme placeholder text or demo names appear anywhere on public pages.
- Page is usable on a 360px-wide phone and loads in under 2 seconds on 4G.

**V-2 Browse work.** As a visitor, I can explore projects and open one to read the details.
- Projects page lists published projects, newest or admin-ordered first, paginated.
- Category filter shows only projects in that category.
- Detail page shows cover image, content, feature list, gallery, info sidebar and previous/next links.
- Draft projects and unknown slugs return the themed 404 page.

**V-3 Read the blog.** As a visitor, I can read posts and browse by category.
- Blog list shows author, date, image and title for published posts only; 9 per page.
- Post page shows content, category, tags, recent posts and previous/next links.

**V-4 Get in touch.** As a visitor, I can send a message from the contact page, any contact section, or the chat popup.
- Required fields are validated with clear inline messages; nothing is saved when invalid.
- A valid message shows a success confirmation without leaving the page (and works without JavaScript).
- More than 5 submissions per minute from one IP are blocked; honeypot submissions are silently discarded.

### Owner (Admin)

**A-1 Secure access.** As the owner, only I can access the admin panel.
- `/admin` redirects guests to the login page; there is no registration route.
- Failed logins are throttled; password reset works by email; changing the password requires the current one.

**A-2 Site identity and profile.** As the owner, I edit my details once and they update everywhere.
- Changing email, phone, address or social links updates the header, off-canvas menu, banner, contact page and footer.
- Empty social links hide their icons.
- Updated values appear immediately (cache cleared on save).

**A-3 Manage collections.** As the owner, I can create, edit, hide, reorder and delete services, skills, stats, testimonials, resume entries, projects and posts.
- Every form validates on the server and shows errors next to fields.
- Delete asks for confirmation.
- Hidden (inactive) items disappear from the public site but stay in the admin panel.
- Images are checked for type and size and resized to the theme's ratios.
- Slugs are generated from titles, stay unique, and can be edited.

**A-4 Rich content safely.** As the owner, I can format long content, and nothing I paste can run scripts on the public site.
- The editor supports headings, bold/italic, lists, links, images and quotes.
- `<script>`, inline event handlers and `javascript:` links are stripped before display.

**A-5 Sections.** As the owner, I can edit each in-scope section's eyebrow, title and subtitle and switch the section on or off.

**A-6 Inbox.** As the owner, I see every message in one place and get an email for each.
- Messages show source (contact / chat), sender details, subject, message and date.
- I can mark messages read/unread or replied, delete them, and click to reply by email.
- The dashboard shows the unread count and the latest 5 messages.

---

## 4. MVP database tables

`users`, `settings`, `sections`, `stats`, `services`, `skills`, `project_categories`, `projects`, `project_images`, `post_categories`, `tags`, `post_tag`, `posts`, `testimonials`, `resume_entries`, `pages`, `messages`, plus Laravel's `password_reset_tokens`, `sessions`, `cache` and `jobs`. Field details are in PRD §6. `pricing_plans`, `clients`, `service_categories` and `subscribers` are created post-MVP.

---

## 5. Build plan

| Milestone | Deliverables | Exit check |
|---|---|---|
| **M0 Setup** | Laravel 13 on XAMPP, MySQL database, `.env`, Git repo, Mailpit for local email, theme assets copied to `public/assets` | App loads at localhost; theme CSS/JS load without console errors |
| **M1 Theme to Blade (static)** | `layouts/app.blade.php`; partials for header, off-canvas, banner, footer, chat popup; one component per section; all in-scope pages rendering with demo content; `custom.css`/`custom.js` for overrides | Every page matches the screenshots on desktop and mobile; sliders, counters and menus still work |
| **M2 Admin foundation** | Admin login (Breeze or hand-built), registration disabled, Bootstrap admin layout, dashboard shell, change password/reset, `EnsureAdmin` middleware, rate limiting | Only the owner reaches `/admin`; tests for access pass |
| **M3 Settings, profile and sections** | `settings` and `sections` tables with seeders, settings service with caching, admin forms, header/footer/banner/contact page wired to settings | Changing contact details in admin updates every public location |
| **M4 Simple collections** | Stats, Skills, Testimonials, Resume entries, Services (with detail page): migrations, models, factories, admin CRUD (`/generate-crud`), public sections wired up | All home and about sections except portfolio and blog are live data |
| **M5 Projects and blog** | Projects with categories and gallery; posts with categories and tags; rich-text editor + HTML sanitizer; image upload service; list and detail pages; slugs; draft/published | Owner publishes a project and a post and sees them on home, list and detail pages |
| **M6 Forms and inbox** | Contact and chat forms (AJAX + fallback), honeypot, throttling, `messages` table, owner email notification (queued), admin inbox | Both forms reach the inbox and email; spam protection tests pass |
| **M7 SEO, polish and launch prep** | Meta and OG tags, sitemap and robots.txt, legal pages, themed 404/500, image lazy-loading, caching review, Lighthouse pass, real content entered, README | Definition of done (§7) met; test-writer and security-reviewer passes clean |

---

## 6. Key technical decisions

- **Theme integration.** Copy the theme's `assets/` folder to `public/assets` and load it with `asset()`; keep the theme's own jQuery plugins as they are. Overrides go in `custom.css`/`custom.js` only, so the theme can be updated without merge pain.
- **Blade structure.** `layouts/app.blade.php` + `partials/` (header, footer, offcanvas, page-banner, chat-popup) + `components/sections/` (banner, skills, counter, portfolio, testimonials, blog, contact, education, experience) + small UI components (section-heading, project-card, post-card).
- **Admin.** Separate `layouts/admin.blade.php` using a free Bootstrap admin template; routes grouped under `/admin` with `auth` + `EnsureAdmin` middleware; resource controllers, Form Requests and Policies, generated with `/generate-crud`.
- **Settings.** `settings` table (key + JSON value) behind a `Settings` service with `Cache::rememberForever()`; cache cleared by model events on save. Shared to all views with a View Composer.
- **Sections.** `sections` table keyed like `home.skills`; each section component reads its heading and visibility from it.
- **Images.** Laravel `public` disk with `php artisan storage:link`; uploads validated (`image`, `mimes:jpg,jpeg,png,webp`, max size) and resized with an image library (e.g. Intervention Image) to fixed ratios per use (card, cover, gallery, avatar); random filenames.
- **Rich text.** A free WYSIWYG editor (e.g. TinyMCE self-hosted, Quill or Trix); output cleaned with an HTML sanitizer such as `mews/purifier` on save and rendered with `{!! !!}` only for that sanitized field.
- **Forms.** Single `MessageController@store` used by the contact and chat forms with a `source` field; `throttle` middleware; honeypot field; `Mail` notification queued (database queue driver).
- **Publishing.** `status` + `published_at` with a `published()` query scope used by every public query.
- **SEO.** A small `seo` Blade component reading per-model SEO fields with fallbacks to settings; sitemap generated from published models (e.g. `spatie/laravel-sitemap`).
- **Testing and review.** Reuse the `test-writer` and `security-reviewer` agents and the `/write-test`, `/review-test`, `/generate-crud` and `/refactor` commands, pointing them at this project's PRD and MVP.

---

## 7. Definition of done

1. Every in-scope page matches the Reeni screenshots on desktop, tablet and a 360px phone, with no theme placeholder text left.
2. Every visible text, image, number and link in the in-scope sections is editable from the admin panel.
3. Contact and chat forms save to the inbox and send an email; spam protections are in place.
4. Only the owner can access `/admin`; no registration route exists.
5. Rich text is sanitized, uploads are restricted to images, and `APP_DEBUG=false` in production.
6. Feature tests cover: admin access, settings propagation, each CRUD's validation, publish rules (drafts hidden), form validation and throttling, and rich-text sanitizing.
7. Mobile Lighthouse scores: performance ≥ 85, SEO ≥ 95, accessibility ≥ 90.
8. Security-reviewer reports no Critical or High findings.
9. README covers XAMPP setup, `.env`, `storage:link`, migrations and seeding, queue worker, and deployment notes.

---

## 8. Demo script

1. Log in at `/admin`. Open Site Settings, change the phone number and Instagram link, and save.
2. Visit the home page: the new phone number shows in the banner, off-canvas menu, contact page and footer.
3. In Profile & Hero, upload a new portrait and edit the bio; refresh the home page to see it.
4. Add a skill and a stat; hide one testimonial; check the home and about pages.
5. Create a project with a category, cover image, three gallery images and a feature list; publish it. Open it from the home page portfolio section.
6. Write a blog post with a heading, a list and a link; try pasting a `<script>` tag. Publish and confirm the script was removed.
7. Send a message through the contact page and another through the chat popup.
8. Back in the admin panel, both messages appear in the inbox (and in Mailpit); mark one as replied.
9. Visit `/sitemap.xml` and an unknown URL to see the themed 404.

---

## 9. Open questions to settle before M3

1. Confirm the custom Bootstrap admin panel (no Filament).
2. Rename "Appointment Now" to "Send Message"?
3. Keep Skills and Services as separate content, or merge?
4. Which sections should launch hidden: Price Plan and Supported Companies (assumed), anything else?
5. Is the chat popup's "Online" label kept, removed or made editable?
