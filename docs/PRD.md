# Product Requirements Document: Dynamic Personal Portfolio (Reeni Theme)

| Field | Value |
|---|---|
| Product | Dynamic personal portfolio website |
| Document type | Product Requirements Document |
| Version | 1.0 (Draft) |
| Status | For review |
| Frontend theme | Reeni (dark Bootstrap portfolio theme) |
| Stack | Laravel 13, MySQL, Blade, Bootstrap, JavaScript, XAMPP (local) |

---

## 1. Overview

A personal portfolio website built on the Reeni Bootstrap theme, powered by Laravel 13 so that every piece of content (profile, services, skills, projects, blog posts, testimonials, pricing, resume and contact details) can be managed from a private admin panel without touching code.

Visitors use the public site to learn who the owner is, see their work and services, read their blog, and get in touch. The owner uses the admin panel to keep everything current and to read the messages and newsletter sign-ups the site collects.

### 1.1 Problem statement

The Reeni theme is static HTML: every name, number, image and paragraph is hard-coded, and updating anything means editing HTML files and re-uploading them. Placeholder content (demo names, lorem ipsum, demo logos) is scattered across many pages. Forms do not send anywhere. There is no blog engine, no project management, and no way to see or respond to enquiries.

### 1.2 Goals

1. Keep the Reeni design faithful while making all content database-driven.
2. Let the owner manage every section and page from a secure Bootstrap admin panel.
3. Turn the contact form, chat popup and footer newsletter into a working inbox with email notifications.
4. Provide a full blog and project showcase with clean URLs and detail pages.
5. Make the site fast, secure, mobile-friendly and well indexed by search engines.

### 1.3 Non-goals (this release)

Multiple admin users and role management, real-time live chat, online payments for pricing plans, a client portal, multi-language content, sending newsletter campaigns from the site (sign-ups are collected and exportable only), and a drag-and-drop page builder.

---

## 2. Users

| User | Description | Needs |
|---|---|---|
| **Visitor** | Potential client, employer, collaborator or reader | Understand who the owner is, see proof of work, read posts, get in touch easily |
| **Owner (Admin)** | The portfolio owner; the only person who logs in | Update content quickly, hide or reorder sections, read and reply to messages, keep the site secure |

---

## 3. Technology stack

| Technology | Purpose |
|---|---|
| Laravel 13 (PHP) | Routing, controllers, Eloquent ORM, validation, auth, mail, queues, caching |
| MySQL | Storage for content, settings, messages and subscribers |
| Blade | Templates and components built from the Reeni HTML |
| HTML / CSS | Reeni theme markup and styles, kept as close to the original as possible |
| Bootstrap | Theme grid and components (public site) and the admin panel layout |
| JavaScript | Theme plugins (sliders, counters, animations, menus), AJAX form submission, admin interactions |
| XAMPP | Local development environment (Apache, MySQL, PHP) |

Notes: Laravel 13 needs PHP 8.3 or newer, so the XAMPP build must match. The admin panel uses a separate Bootstrap admin template so it never interferes with the public theme's styles.

---

## 4. Site map

### 4.1 Public site

| Page | URL | Content |
|---|---|---|
| Home | `/` | Banner, service cards, latest services, skills, counters, portfolio, pricing, clients, testimonials, blog, contact CTA |
| About | `/about` | Profile intro, education, experiences, counters, testimonials, contact form |
| Services | `/services` | Numbered service cards |
| Service details | `/services/{slug}` | Featured image, rich content, service category sidebar |
| Projects | `/projects` | Project grid, filterable by category |
| Project details | `/projects/{slug}` | Hero image, rich content, feature list, gallery, info sidebar (name, client/author, date, tags), previous/next, contact form |
| Blog | `/blog` | Post cards, pagination, category and tag filters, search |
| Blog details | `/blog/{slug}` | Featured image, content, author, date, category, tags, sidebar (categories, recent posts), previous/next, approved comments and comment form |
| Contact | `/contact` | Address, email and phone cards, contact form |
| Legal | `/terms`, `/privacy` | Admin-editable text pages |

### 4.2 Global components (every public page)

Header (logo, menu with Services, Blog and Project dropdowns, social icons, hamburger off-canvas menu), inner-page banner with breadcrumb, footer (logo, tagline, newsletter field, quick links, contact details, social icons, copyright, legal links), floating "chat with me" email popup, and back-to-top button.

### 4.3 Admin panel

Private area at `/admin` (path configurable) with sidebar navigation: Dashboard, Site Settings, Profile & Hero, Home Sections, Services, Skills, Projects, Blog (Posts, Categories, Comments), Pricing, Testimonials, Clients, Resume (Education & Experience), Stats, Pages, Messages, Subscribers, Account.

---

## 5. Functional requirements

Priority key: **P0** = required for launch, **P1** = should have, **P2** = nice to have.

### 5.1 Global and settings

| ID | Requirement | Priority |
|---|---|---|
| GEN-1 | Site name, logo, favicon, tagline and copyright text editable in Site Settings | P0 |
| GEN-2 | Contact details (address, emails, phones) edited once and reused in header, hero, contact page and footer | P0 |
| GEN-3 | Social links (Instagram, LinkedIn, X, Facebook, plus optional others) editable; empty links are hidden | P0 |
| GEN-4 | Menu items fixed in code; dropdowns list live services, latest posts and project categories | P1 |
| GEN-5 | Footer quick links editable (label + URL, ordered) | P1 |
| GEN-6 | Off-canvas menu shows short bio, contact details and social links from settings | P1 |
| GEN-7 | Every public page has an editable meta title, meta description and share image, with sensible defaults | P0 |
| GEN-8 | Custom 404 and 500 pages styled in the theme | P1 |
| GEN-9 | Optional maintenance mode with a themed "coming soon" page | P2 |

### 5.2 Home page sections

Every section has an admin-editable **eyebrow** (small red text), **title**, optional **subtitle**, an **on/off** switch and a **display order**.

| ID | Section | Requirement | Priority |
|---|---|---|---|
| HOME-1 | Banner | Greeting, name, role, portrait image, biography, contact block and up to 3 headline stats | P0 |
| HOME-2 | Service cards | Icon, title and project count for 4 featured services | P0 |
| HOME-3 | Latest service | Up to 3 numbered services with description, plus a side image | P1 |
| HOME-4 | My skill | Skill rows with icon, title, "N done" count, description and link | P0 |
| HOME-5 | Counter area | Highlight number + label + text (e.g. "25 Years of Experience") and 4 stat cards, animated on scroll | P0 |
| HOME-6 | Latest portfolio | Featured or latest projects (image, title, category) linking to detail pages | P0 |
| HOME-7 | Price plan | Plans with name, price, period, feature list, highlighted plan and button | P1 |
| HOME-8 | Supported companies | Client logo grid with optional links | P1 |
| HOME-9 | Testimonials | Slider of quote, name, role and optional photo | P0 |
| HOME-10 | Blog and news | Latest 3 published posts | P0 |
| HOME-11 | Get in touch | Heading, text and contact form | P0 |
| HOME-12 | Owner can toggle and reorder all home sections | P1 |

### 5.3 About page

| ID | Requirement | Priority |
|---|---|---|
| ABT-1 | Intro block from the profile (photo, title, bio) | P0 |
| ABT-2 | Education entries: title, institution (optional), years, description; shown as two-column cards | P0 |
| ABT-3 | Experience entries: label, organisation or name, role, description; with a side image | P0 |
| ABT-4 | Reuses the service cards, counter, education/experience, testimonial and contact form sections | P0 |
| ABT-5 | Two columns of skill progress bars ("Design Skill" / "Development Skill"): editable column titles and on/off; bars with name and percentage (0-100), order and active flag | P1 |

### 5.4 Services

| ID | Requirement | Priority |
|---|---|---|
| SRV-1 | Admin CRUD for services: title, slug, icon, short description, rich-text content, featured image, project count, category, featured flag, order, active flag | P0 |
| SRV-2 | Service categories (CRUD) shown in the Service Details sidebar | P1 |
| SRV-3 | Services page lists active services as numbered cards (01, 02 …) in admin-defined order | P0 |
| SRV-4 | Service details page with image, content and category sidebar; inactive services return 404 | P0 |

### 5.5 Skills

| ID | Requirement | Priority |
|---|---|---|
| SKL-1 | Admin CRUD for skills: icon, title, done count, description, optional link (e.g. to a service), order, active | P0 |
| SKL-2 | The row marked "highlighted" uses the theme's red style | P2 |

### 5.6 Projects (portfolio)

| ID | Requirement | Priority |
|---|---|---|
| PRJ-1 | Admin CRUD for projects: title, slug, card tagline, category, client/author name, project date, tags, cover image, card image, rich-text content, key feature list, gallery images, external link, featured flag, status (draft/published), order | P0 |
| PRJ-2 | Project categories CRUD; projects page filterable by category | P1 |
| PRJ-3 | Projects page shows published projects with pagination | P0 |
| PRJ-4 | Project details page with info sidebar, gallery, previous/next navigation and contact form | P0 |
| PRJ-5 | Gallery images can be added, removed and reordered | P1 |

### 5.7 Blog

| ID | Requirement | Priority |
|---|---|---|
| BLG-1 | Admin CRUD for posts: title, slug, excerpt, rich-text body, featured image, category, tags, author name, status (draft / published / scheduled), publish date | P0 |
| BLG-2 | Categories and tags CRUD | P0 |
| BLG-3 | Blog list with author and date badge, title and "Read more"; 9 per page | P0 |
| BLG-4 | Filter by category and tag; keyword search across title and body | P1 |
| BLG-5 | Blog details page with sidebar (search, categories, recent posts, tags) and previous/next | P0 |
| BLG-6 | Scheduled posts go live automatically at their publish date | P1 |
| BLG-7 | Estimated reading time and social share buttons | P2 |
| BLG-8 | RSS feed | P2 |
| BLG-9 | Visitors can comment on published posts (name, email, message; one level of replies). Comments are held until the owner approves them; the owner can reply, unapprove and delete, and can close comments per post. CON-4 spam protection applies; emails are never shown publicly | P1 |

### 5.8 Pricing, testimonials, clients, stats

| ID | Requirement | Priority |
|---|---|---|
| PRC-1 | Admin CRUD for pricing plans: name, price, currency, period, feature list, highlighted flag, button label and link, order, active | P1 |
| PRC-2 | Plan button opens the contact form with the plan name pre-filled in the subject | P1 |
| TST-1 | Admin CRUD for testimonials: quote, name, role/company, optional photo, optional rating, order, active | P0 |
| CLT-1 | Admin CRUD for client logos: name, logo, optional URL, order, active | P1 |
| STAT-1 | Admin CRUD for stats: value, suffix (k, +), label, placement (banner / counter area / highlight), order | P0 |

### 5.9 Contact, chat popup and newsletter

| ID | Requirement | Priority |
|---|---|---|
| CON-1 | Contact form (name, phone, email, subject, message) saves a message and emails the owner | P0 |
| CON-2 | Chat popup form (name, email, message) saves to the same inbox, tagged "chat" | P0 |
| CON-3 | Forms submit via AJAX with inline errors and a success message, and still work without JavaScript | P1 |
| CON-4 | Spam protection on all public forms: CSRF, rate limiting, honeypot field; optional Cloudflare Turnstile | P0 |
| CON-5 | Visitor receives an automatic acknowledgement email | P2 |
| NEWS-1 | Footer newsletter field saves unique subscriber emails and shows a confirmation | P1 |
| NEWS-2 | Double opt-in confirmation email and unsubscribe link | P2 |
| NEWS-3 | Owner can view, delete and export subscribers to CSV | P1 |

### 5.10 Admin panel

| ID | Requirement | Priority |
|---|---|---|
| ADM-1 | Secure login for the owner only; public registration disabled | P0 |
| ADM-2 | Dashboard: counts of projects, posts, services, unread messages and subscribers; latest 5 messages; quick links | P0 |
| ADM-3 | Messages inbox: list, read/unread, filter by source (contact / chat / project / pricing), view, mark as replied, delete, "reply by email" link; reply from the inbox: the reply is emailed to the visitor (Reply-To the owner, original quoted) and kept in a thread with its delivery status (sending / sent / failed + retry) | P0 |
| ADM-4 | All collections support create, edit, delete (with confirmation), active toggle and reordering | P0 |
| ADM-5 | Image uploads with preview, type and size validation, and automatic resizing to theme ratios | P0 |
| ADM-6 | Rich-text editor for long content, with sanitized output | P0 |
| ADM-7 | Slugs auto-generated from titles, editable, unique | P0 |
| ADM-8 | Change password; password reset by email | P0 |
| ADM-9 | Two-factor authentication for the admin login | P2 |
| ADM-10 | "View on site" links from edit screens; preview for draft posts and projects | P1 |
| ADM-11 | Saving content clears the relevant cache automatically | P0 |

### 5.11 SEO

| ID | Requirement | Priority |
|---|---|---|
| SEO-1 | Clean slug URLs for services, projects and posts | P0 |
| SEO-2 | Meta title, description, canonical URL and Open Graph / X card tags on every page | P0 |
| SEO-3 | Auto-generated `sitemap.xml` and `robots.txt` | P1 |
| SEO-4 | Structured data (JSON-LD): Person on home, BlogPosting on posts | P2 |
| SEO-5 | Alt text field on every uploaded image | P1 |

---

## 6. Data model (summary)

| Table | Key fields |
|---|---|
| `users` | id, name, email, password, two-factor fields, timestamps |
| `settings` | key, value (JSON), group: site identity, contact, social, footer, SEO defaults, profile/hero |
| `sections` | key (e.g. `home.skills`), eyebrow, title, subtitle, is_active, sort_order |
| `stats` | value, suffix, label, description, placement, sort_order, is_active |
| `service_categories` | name, slug, sort_order |
| `services` | category_id, title, slug, icon, excerpt, content, image, project_count, is_featured, sort_order, is_active, SEO fields |
| `skills` | icon, title, done_count, description, link, is_highlighted, sort_order, is_active |
| `skill_bars` | group (design / development), name, percent, sort_order, is_active |
| `project_categories` | name, slug, sort_order |
| `projects` | category_id, title, slug, tagline, client_name, project_date, cover_image, card_image, content, features (JSON), external_url, is_featured, status, published_at, sort_order, SEO fields |
| `project_images` | project_id, path, alt, sort_order |
| `post_categories` | name, slug |
| `tags`, `post_tag`, `project_tag` | name, slug; pivot tables |
| `posts` | category_id, title, slug, excerpt, body, featured_image, author_name, status, published_at, allow_comments, SEO fields |
| `comments` | post_id, parent_id, author_name, author_email, body, is_owner, status (pending / approved), approved_at, ip_address, user_agent |
| `pricing_plans` | name, price, currency, period, features (JSON), is_highlighted, button_label, button_url, sort_order, is_active |
| `testimonials` | quote, name, role, photo, rating, sort_order, is_active |
| `clients` | name, logo, url, sort_order, is_active |
| `resume_entries` | type (education / experience), title, organisation, role, period, description, sort_order, is_active |
| `pages` | slug (terms, privacy), title, body, SEO fields |
| `messages` | source (contact / chat / pricing), name, email, phone, subject, body, ip, user_agent, read_at, replied_at |
| `subscribers` | email, token, confirmed_at, unsubscribed_at |

Images are stored on Laravel's `public` disk; tables store file paths, not files.

---

## 7. Non-functional requirements

**Design fidelity.** Public pages match the Reeni screenshots on desktop, tablet and mobile. Theme CSS and JS stay untouched; changes go into separate `custom.css` and `custom.js` files.

**Performance.** Home page loads in under 2 seconds on a typical 4G connection; mobile Lighthouse performance ≥ 85. Settings, sections and home-page collections are cached and cleared on save. Images are resized, served in modern formats where possible and lazy-loaded below the fold. Eager loading prevents N+1 queries.

**Security.** Single admin with hashed password and login throttling; CSRF on every form; server-side validation everywhere; `$fillable` on all models; rich text sanitized with an allow-list before output; uploads limited to images with size limits, renamed on save and stored where they cannot execute; rate-limited public forms with a honeypot; security headers; `APP_DEBUG=false` and HTTPS in production; the admin URL is never linked from the public site.

**Accessibility.** Semantic headings, alt text on images, visible focus states, labelled form fields, enough contrast on the dark theme, and sliders that can be paused.

**Maintainability.** One Blade component per section, migrations and seeders for every table, seed data that recreates the theme's demo content, and feature tests for forms, admin access and publishing rules.

**Compatibility.** Latest Chrome, Edge, Firefox and Safari; responsive from 360px wide.

---

## 8. UI reference

The 21 provided screenshots define the public design: Banner, Services Area, Latest Service, My Skill, Counter, Latest Portfolio, My Price Plan, Supported Companies, Testimonials, Education, Experience, Blog and News, Footer, Chat popup, Contact page, Services page, Service Details, Project Details, Blog Details and the About page sections. Key traits: near-black background, red/pink accent, rounded dark cards, large bold headings with small red uppercase eyebrows, and floating round action buttons at the bottom right.

The admin panel uses a clean Bootstrap admin layout (sidebar + top bar), kept separate from the public theme.

---

## 9. Success metrics

| Metric | Target (first 3 months) |
|---|---|
| Content updates made without touching code | 100% |
| Time to publish a new project or post | < 5 minutes |
| Mobile Lighthouse performance / SEO / accessibility | ≥ 85 / ≥ 95 / ≥ 90 |
| Spam reaching the inbox | < 5% of messages |
| Published pages indexed by search engines | 100% |

---

## 10. Risks and open questions

1. **Admin panel approach.** This PRD assumes a custom Bootstrap admin panel rather than Filament. Confirm.
2. **"Appointment Now" button.** Decided 2026-11-02: renamed to "Send Message"; no appointment booking.
3. **Pricing plans.** Do plan buttons lead to an enquiry (assumed) or to payment?
4. **Chat popup.** Treated as an email form, not live chat; the "Online" label will be removed or made editable.
5. **Skills vs Services.** The two sections are similar. Keep separate tables (assumed) or merge?
6. **Blog comments.** Decided 2026-10-28: in scope as moderated comments with owner replies (BLG-9).
7. **Theme licence.** Confirm the Reeni licence allows this use, and replace demo images and company logos with owned or licensed assets.
8. **Newsletter.** Sign-ups are collected only; sending campaigns would need a service like Mailchimp later.
9. **Email delivery.** Needs SMTP in production; use Mailpit locally on XAMPP.
10. **Hosting.** XAMPP is for development only; production needs a properly configured server with HTTPS.
