# Admin Quick Actions API

Document the admin quick action endpoints handled by `QuickActionController` (inside `routes/api/admin.php`). This covers the resource created by `QuickActionService` using `QuickAction` model, `StoreRequest`, `UpdateRequest`, and its JSON resources. The base controller returns an envelope with `status`, `message`, and `data`.

## Authentication & Headers

- All quick action routes live under `/api/admin/quick-actions` and require `auth:admin` middleware (see `routes/api/admin.php`).
- Send `Authorization: Bearer <token>` and `Accept: application/json` for JSON endpoints.
- Creation/updating also needs `Content-Type: multipart/form-data` because `icon` is an uploaded image (a single-image column handled via `QuickActionService::$singleImages`).

## Common response envelope

Every controller method uses `Controller::sendResponse`, so the response looks like:

```json
{
  "status": true,
  "message": "Success",
  "data": { ... }
}
```

Errors use `status: "error"` and include `errors` when validation fails.

## List quick actions

- **GET** `/api/admin/quick-actions`
- Query params supported by `BaseCRUDController::index` and `BaseService::queryBuilder`:
  - `search` (matches `id` and `page_id`)
  - `sort_field` (allowed: `id`, `order`, `created_at`), default `id`
  - `sort_order` (`asc`/`desc`, default `desc`)
  - `page`, `per_page` (pagination defaults to page 1, 10 items)
- Response uses `AllResource` and includes:
  - `id`
  - `title` localized to `app()->getLocale()` (e.g., `"title": "سرعة"`)
  - `page` summary (`id`, `title`, `slug`)
  - `icon` (public URL via `QuickAction::icon_url`)
  - `order`, `is_active`, `created_at`
  - Pagination metadata under `data.data.pagination`.

## Retrieve a single quick action

- **GET** `/api/admin/quick-actions/{id}`
- Response uses `OneResource`:
  - `title` contains all translations (`title.en`, `title.ar`)
  - `page_id` and `page` object as above
  - `icon`, `order`, `is_active`, `created_at`, `updated_at`

## Create a quick action

- **POST** `/api/admin/quick-actions`
- Payload validated by `StoreRequest`:
  - `title` (object) with both `title.en` and `title.ar`, each `string|max:255`
  - `page_id` must exist in `pages` table
  - `icon` required image (jpeg/jpg/png/gif/webp, max 2 MB)
  - `order` optional `integer|min:0`
  - `is_active` optional boolean
- `icon` is stored via `SingleImageService`, and `QuickActionService` refreshes the model before returning `OneResource`.
- Response returns the created resource inside the standard envelope.

## Update a quick action

- **PUT/PATCH** `/api/admin/quick-actions/{id}`
- Payload validated by `UpdateRequest` (all fields optional, except the nested translation keys must still be strings when sent).
- You can replace the `icon` by uploading a new file (same validation as `StoreRequest`).
- Returns the updated `OneResource`.

## Delete

- **DELETE** `/api/admin/quick-actions/{id}`
- Removes the record and its stored icon file (via `BaseService::deleteSingleImages`).
- Response simply returns `"data": true` inside the envelope.

## Notes

- Titles are translatable (`QuickAction::$translatable = ['title']`). When creating/updating the translation array, the service handles `setTranslations` automatically before saving.
- `QuickActionService` eager-loads `page` so `page` metadata is always available and returns paginated collections by default.
- Search and sort are handled entirely on the server; no extra query builder hooks are needed.
