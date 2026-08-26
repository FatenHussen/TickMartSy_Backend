# Quick Actions API

Quick actions let admins configure shortcuts that appear for authenticated users. Each action stores a translatable title, target page, icon image, order, and active flag. The same routes are documented from both the admin and user perspectives so the frontend teams can align expectations.

## Admin API (requires `auth:admin`)

| Method | Endpoint | Description |
| --- | --- | --- |
| `POST` | `/api/admin/quick-actions` | Create a quick action. |
| `GET` | `/api/admin/quick-actions` | List quick actions with pagination, search (`search`), sort (`sort_field`, `sort_order`), and per-page controls. |
| `GET` | `/api/admin/quick-actions/{id}` | Get one quick action. |
| `PUT` | `/api/admin/quick-actions/{id}` | Update. |
| `DELETE` | `/api/admin/quick-actions/{id}` | Delete. |

### Request payload (create / update)

```http
POST /api/admin/quick-actions
Content-Type: multipart/form-data

title[en]=Quick Buy
title[ar]=شراء سريع
page_id=3
icon=@/path/to/icon.png
order=1
is_active=true
```

- `title.en` `string` required, max 255  
- `title.ar` `string` required, max 255  
- `page_id` `exists:pages,id`  
- `icon` image `jpeg|jpg|png|gif|webp` ≤ 2MB (required on create, nullable on update)  
- `order` optional integer ≥ 0  
- `is_active` boolean

### Success response (OneResource)

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "id": 1,
    "title": {
      "en": "Quick Buy",
      "ar": "شراء سريع"
    },
    "page_id": 3,
    "page": {
      "id": 3,
      "title": "Products",
      "slug": "products"
    },
    "icon": "http://.../storage/icons/uuid.png",
    "order": 1,
    "is_active": true,
    "created_at": "2026-04-01T08:00:00.000000Z",
    "updated_at": "2026-04-01T08:00:00.000000Z"
  }
}
```

The list endpoint returns `AllResource` items (single-language-friendly `title` and `page` info plus `id`, `icon`, `order`, `is_active`, `created_at`) inside the standard `sendResponse` envelope.

## User API (requires `auth:user`)

| Method | Endpoint | Description |
| --- | --- | --- |
| `GET` | `/api/user/quick-actions` | Fetch active quick actions ordered by the `order` column. |

### Success response

```json
{
  "status": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "title": "Quick Buy",
      "page_id": 3,
      "page_slug": "products",
      "page_title": "Products",
      "icon": "http://.../storage/icons/uuid.png",
      "order": 1
    },
    ...
  ]
}
```

The controller uses `QuickActionService::getQuickActionsForUser()`, which only returns records where `is_active` is `true` and eager-loads the related `page`.

## Notes

- All responses are wrapped using `App\Http\Controllers\Controller::sendResponse`.  
- Icons upload to the `public` disk; the `icon_url` accessor builds the full `asset('storage/...')` path.  
- Updates keep translations intact by passing arrays such as `"title": {"en": "...", "ar": "..."}`.
