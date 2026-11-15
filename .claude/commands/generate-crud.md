---
description: Generate an admin-managed content type and wire it to the Reeni theme
argument-hint: <Model> [fields] [public section/page], e.g. Testimonial quote:text name:string role:string photo:image section:home.testimonials
---

Generate an admin CRUD for: $ARGUMENTS

Read `docs/PRD.md` §5–6, `CLAUDE.md` and existing code first, and match the project's conventions. Ask only if the model, fields, or where it appears publicly are unclear. **Ask before installing any package.**

Create:
1. **Migration** with correct types, foreign keys (`constrained()`), indexes, and the standard content columns as relevant: `slug` (unique), `sort_order`, `is_active` or `status` + `published_at`, SEO fields for items with detail pages, and image path columns with an `*_alt` text column.
2. **Model** with `$fillable` (never `$guarded = []`), casts, relationships, `published()` / `active()` / `ordered()` scopes, and a slug generated from the title when empty.
3. **Factory** with states (`published`, `draft`, `inactive`, `featured`) and a **seeder** recreating the theme's demo content for this type.
4. **Form Requests** (`Store…Request`, `Update…Request`) with full validation: lengths, `url` with `http(s)` only for links, `image|mimes:jpg,jpeg,png,webp|max:…` for images, unique slugs ignoring the current record.
5. **Admin controller** in `app/Http/Controllers/Admin/` using `$request->validated()`, the shared image upload service (resize, random name, remove old file), the HTML sanitizer for rich-text fields, transactions for multi-table writes, pagination (20) and eager loading. Add `reorder` and `toggle` actions if the type is ordered or toggleable.
6. **Routes** inside the `/admin` group (`auth` + `EnsureAdmin`) with `Route::resource()` limited to needed actions; reorder/toggle as POST/PATCH. No state changes on GET.
7. **Admin views** on `layouts/admin.blade.php` with Bootstrap: index table (thumbnail, title, status badge, order, actions), create/edit form with `@csrf`, `@method`, old input, inline errors, image preview, rich-text editor where needed, and a delete confirmation. Add the sidebar link.
8. **Public wiring:** update the matching Blade section component or page (`resources/views/components/sections/…`) to loop over `Model::active()->ordered()` (or `published()`) data, keeping the theme's exact markup and classes. Escape all plain text with `{{ }}`; only sanitized rich text may use `{!! !!}`. Add detail routes (`/…/{slug}`) using published-only route model binding if the type has detail pages, and add it to the sitemap.
9. **Cache:** clear the related cache keys on save, delete, reorder and toggle (model events or an observer).

Never edit theme files in `public/assets`; put any styling changes in `custom.css`.

Then hand over to **/write-test** for the new type and run `php artisan test`. Finish with the list of files created or changed and any decisions for the owner.
