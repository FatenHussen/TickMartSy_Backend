# User promotions API — frontend guide

This document describes the public endpoint implemented in `app/Http/Controllers/User/PromotionController.php` for building web or mobile clients.

## Endpoint

| Method | URL | Auth |
|--------|-----|------|
| `GET` | `/api/user/promotions` | None (public) |

The `/api` prefix comes from Laravel’s API route registration. The `user` segment is defined in `routes/api/user.php`.

---

## Query parameters

| Parameter | Required | Rules | Purpose |
|-----------|----------|--------|---------|
| `page_slug` | No | Optional string, max 255 characters. If present, must match an existing `pages.slug` in the database. | Filter which promotions apply to the current screen. |

### Behaviour

- **Without `page_slug`:** Returns every promotion that is currently **active** (see below). No page-based filter.
- **With `page_slug`:** Returns active promotions that are either:
  - linked to that page (via the `page_promotion` pivot), **or**
  - not linked to any page (“global” promotions that should appear everywhere).

Invalid `page_slug` (not in `pages`) results in **422 Unprocessable Entity** with Laravel validation errors on `page_slug`.

---

## Success response

HTTP **200**. Body shape from `Controller::sendResponse`:

```json
{
  "status": true,
  "message": "<translated success message>",
  "data": [ /* array of promotion objects */ ]
}
```

- **`data`** is a **plain array** of promotions, not a paginated object (no `current_page`, `last_page`, etc.). The backend loads all matching rows with `get()`.
- Default **`message`** uses the app translation key for success unless the controller overrides it (currently it does not pass a custom message).

---

## Promotion object (`AllResource`)

Each element of `data` has:

| Field | Type | Notes |
|-------|------|--------|
| `id` | number | Primary key. |
| `name` | object | Localized strings (e.g. `ar`, `en`) from Spatie Translatable. |
| `description` | object | Localized strings. |
| `type` | string | Business type, e.g. `simple_discount`, `spend_x_discount`, `spend_x_get_gift`, `spend_x_get_points`, `free_shipping`, `spend_x_get_free_shipping`. |
| `is_active` | boolean | Always `true` for items returned (inactive rows are excluded by `scopeActive`). |
| `position` | string | UI placement hint, e.g. `top` or `bottom` (see admin validation / DB column). |
| `created_at` | string \| null | Format `Y-m-d H:i` when present. |
| `page_slugs` | string[] | Slugs of linked pages. Empty array when the promotion is global (no page links). Populated because the controller eager-loads `pages`. |

### Example

```json
{
  "id": 1,
  "name": { "en": "10% off", "ar": "خصم 10٪" },
  "description": { "en": "…", "ar": "…" },
  "type": "simple_discount",
  "is_active": true,
  "position": "top",
  "created_at": "2026-05-01 12:00",
  "page_slugs": ["cart"]
}
```

---

## Ordering

Promotions are sorted by **`id` ascending** (`orderBy('id')`).

---

## What “active” means (`Promotion::scopeActive`)

A promotion is included only if:

1. `is_active` is `true`, and  
2. `starts_at` is `null` or in the past, and  
3. `ends_at` is `null` or in the future.

---

## Integration tips

1. **Localization:** Read `name` and `description` using the keys that match your app locale (e.g. `name[locale]` with a fallback).
2. **Per-screen promos:** Pass the CMS page `slug` for the current route as `page_slug` so global + page-specific promos are returned together.
3. **Layout:** Use `position` (and optionally `type`) to decide where and how to render each promo.
4. **Volume:** There is no server-side pagination; if the list grows large, coordinate with backend for pagination or limits.

---

## Source files

| File | Role |
|------|------|
| `app/Http/Controllers/User/PromotionController.php` | Validates input, applies scopes, returns collection. |
| `app/Http/Resources/Promotion/AllResource.php` | JSON shape for each promotion. |
| `app/Models/Promotion.php` | `scopeActive`, `scopeForPageSlug`, `pages()` relation. |
| `routes/api/user.php` | `Route::prefix('promotions')` → `GET /`. |

For Arabic-oriented notes and cross-links to other docs, see `docs/promotion-user.md` and `docs/USER_PROMOTIONS_API.md`.
