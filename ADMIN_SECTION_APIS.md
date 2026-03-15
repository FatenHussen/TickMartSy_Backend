# Admin APIs (Settings, Badges, Activity Logs, Affiliate Withdraw Requests, Icons, Promotions)

## Base
- Base URL: `/api/admin`
- Auth: `Authorization: Bearer <admin_token>` (all endpoints below use `auth:admin`)
- Content-Type:
`application/json` for JSON bodies.
`multipart/form-data` when uploading files.

## Common Response Envelope
Most endpoints return:
```json
{
  "status": true,
  "message": "Success",
  "data": {}
}
```
Errors use:
```json
{
  "status": "error",
  "message": "Error",
  "errors": {}
}
```

## Common List Query Params
Available on `GET` list endpoints handled by `BaseCRUDController`:
- `search`: string
- `sort_field`: string (allowed values depend on resource)
- `sort_order`: `asc` or `desc`
- `page`: number
- `per_page`: number

Response data shape for list endpoints:
```json
{
  "items": [/* resource items */],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 10,
    "total": 50
  }
}
```

---

# Settings
## Endpoints
- `GET /settings`
- `GET /settings/{key}`
- `PUT /settings/{key}`

## Request (Update)
Field rules depend on the setting `type` stored in DB.

| Field | Type | Required | Notes |
| --- | --- | --- | --- |
| `value` | boolean/number/string/object/file | yes | Rules by type: `boolean` -> boolean, `number` -> numeric, `json` -> array/object, `file` -> file or string path, `string` -> string |

If `type` is `file` and you are uploading a new file, use `multipart/form-data` with `value` as file.

## Response (Setting Object)
Returned in `data` for `GET` and `PUT`:
```json
{
  "id": 1,
  "key": "support_phone",
  "type": "string",
  "value": "+963-...",
  "created_at": "2026-03-15 12:00:00",
  "updated_at": "2026-03-15 12:10:00"
}
```
Notes:
- `value` is cast based on `type`.
- For `type = file`, `value` is a full URL or `null`.

---

# Badges
## Endpoints
- `GET /badges`
- `POST /badges`
- `GET /badges/{id}`
- `PUT /badges/{id}` or `PATCH /badges/{id}`
- `DELETE /badges/{id}`

## List Query Params (extra)
| Param | Type | Notes |
| --- | --- | --- |
| `type` | string | Allowed: `orders`, `delivery`, `payments`, `account`, `stores&drivers`, `other` |

## Request (Create/Update)
| Field | Type | Required | Notes |
| --- | --- | --- | --- |
| `name.en` | string | create: yes, update: optional | Translated name (EN) |
| `name.ar` | string | create: yes, update: optional | Translated name (AR) |
| `color` | string | create: yes, update: optional | e.g. `#FFAA00` |

## Response
`GET /badges` returns items with localized `name` (string in current locale):
```json
{
  "id": 5,
  "name": "Top Seller",
  "color": "#FFAA00",
  "postion": null
}
```
`GET /badges/{id}`, `POST`, `PUT` return `name` as translations map:
```json
{
  "id": 5,
  "name": {"en": "Top Seller", "ar": "«·√›÷·"},
  "color": "#FFAA00",
  "postion": null
}
```
Note: the field key is `postion` (typo in resource).

---

# Activity Logs
## Endpoint
- `GET /activity-logs`

## Query Params
| Param | Type | Notes |
| --- | --- | --- |
| `action` | string | Filter by action (e.g. `created`, `updated`, `deleted`) |
| `model` | string | Must match `model_type` stored in DB (full class name, e.g. `App\\Models\\Badge`) |
| `per_page` | number | Default 10 |
| `page` | number | Pagination page |

## Response (NOT wrapped with `status/message`)
```json
{
  "items": [
    {
      "id": 1,
      "user": "Admin Name",
      "user_type": "Admin",
      "action": "Created",
      "model": "Badge",
      "model_id": 5,
      "changes": {"color": {"old": "#000", "new": "#FFF"}},
      "date": "2026-03-15 14:20",
      "message": "Admin Name created Badge #5"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 10,
    "total": 23
  }
}
```

---

# Affiliate Withdraw Requests
## Endpoints
- `GET /affiliate-withdraw-requests`
- `GET /affiliate-withdraw-requests/{id}`
- `PUT /affiliate-withdraw-requests/{id}` or `PATCH /affiliate-withdraw-requests/{id}`

## List Query Params (extra)
| Param | Type | Notes |
| --- | --- | --- |
| `status` | string | `pending`, `approved`, `rejected` |
| `affiliate_id` | string | Affiliate id |
| `from` | date | `YYYY-MM-DD` |
| `to` | date | `YYYY-MM-DD` |
| `min_amount` | number | Minimum amount |
| `max_amount` | number | Maximum amount |
| `sort_field` | string | Allowed: `id`, `created_at`, `amount`, `status` |

## Request (Update)
| Field | Type | Required | Notes |
| --- | --- | --- | --- |
| `status` | string | yes | `approved` or `rejected` |
| `note` | string | required if rejected | Max 1000 |

Rules from service:
- Only `pending` requests can be updated.
- If `status = approved`, the affiliate must have enough available balance.

## Response
List item (`GET /affiliate-withdraw-requests`):
```json
{
  "id": 1,
  "affiliate_id": "AFF-123",
  "affiliate": {
    "id": 10,
    "name": "John",
    "email": "john@example.com",
    "phone": "+963..."
  },
  "amount": 100.0,
  "status": "pending",
  "note": null,
  "created_at": "2026-03-15 10:00:00"
}
```
Single item (`GET /affiliate-withdraw-requests/{id}`) adds `updated_at`:
```json
{
  "id": 1,
  "affiliate_id": "AFF-123",
  "affiliate": {"id": 10, "name": "John", "email": "john@example.com", "phone": "+963..."},
  "amount": 100.0,
  "status": "approved",
  "note": "Paid",
  "created_at": "2026-03-15 10:00:00",
  "updated_at": "2026-03-15 12:00:00"
}
```

---

# Icons
## Endpoints
- `GET /icons`
- `POST /icons`
- `GET /icons/{id}`
- `PUT /icons/{id}` or `PATCH /icons/{id}`
- `DELETE /icons/{id}`

## List Query Params (extra)
| Param | Type | Notes |
| --- | --- | --- |
| `sort_field` | string | Allowed: `id`, `name`, `is_active`, `created_at` |

## Request (Create/Update)
Use `multipart/form-data` when sending `image`.

| Field | Type | Required | Notes |
| --- | --- | --- | --- |
| `name` | object | create: yes, update: optional | `{ "ar": "...", "en": "..." }` |
| `image` | file | create: yes, update: optional | `jpeg/png/jpg/gif/svg/webp`, max 2MB |
| `description` | object | optional | `{ "ar": "...", "en": "..." }` |
| `is_active` | boolean | optional | |

## Response (Icon)
```json
{
  "id": 3,
  "name": "New",
  "image": "https://.../storage/icons/xxx.png",
  "description": "...",
  "is_active": true,
  "created_at": "2026-03-15 12:00:00",
  "updated_at": "2026-03-15 12:30:00"
}
```

---

# Promotions
## Endpoints
- `GET /promotions`
- `POST /promotions`
- `GET /promotions/{id}`
- `PUT /promotions/{id}` or `PATCH /promotions/{id}`
- `DELETE /promotions/{id}`

## Request (Create)
| Field | Type | Required | Notes |
| --- | --- | --- | --- |
| `name` | object | yes | `{ "en": "...", "ar": "..." }` |
| `description` | object | yes | `{ "en": "...", "ar": "..." }` |
| `type` | string | yes | `simple_discount`, `spend_x_discount`, `buy_x_get_y` |
| `is_active` | boolean | optional | |
| `starts_at` | date | optional | ISO date string |
| `ends_at` | date | optional | Must be `>= starts_at` |
| `min_spend` | number | optional | Required for `spend_x_discount` by business logic |
| `buy_quantity` | number | optional | Required for `buy_x_get_y` by business logic |
| `get_quantity` | number | optional | Required for `buy_x_get_y` by business logic |
| `discount_value` | number | optional | Used with `simple_discount` or `spend_x_discount` |
| `discount_type` | string | optional | `percentage` or `fixed` |

Note: `gift_product_ids` is not accepted on create (not in validation rules).

## Request (Update)
Same fields as create, all optional. Update allows:
- `gift_product_ids`: array of `shop_product_variants` ids.

## Response
List item (`GET /promotions`):
```json
{
  "id": 7,
  "name": "10% Off",
  "description": "Discount",
  "type": "simple_discount",
  "is_active": true,
  "created_at": "2026-03-15 09:00"
}
```
Single item (`GET /promotions/{id}`) uses translations map:
```json
{
  "id": 7,
  "name": {"en": "10% Off", "ar": "Œ’„ 10%"},
  "description": {"en": "Discount", "ar": "Œ’„"},
  "type": "simple_discount",
  "is_active": true,
  "starts_at": "2026-03-15T00:00:00.000000Z",
  "ends_at": null,
  "min_spend": null,
  "buy_quantity": null,
  "get_quantity": null,
  "discount_value": 10.0,
  "discount_type": "percentage",
  "gift_product_ids": []
}
```

---

## Notes for Frontend
- `name` and `description` are translatable fields. List endpoints may return a localized string, while single endpoints return a translations map.
- `postion` is a misspelled key from backend resources and should be used as-is.
- If you need a new endpoint (e.g. fields-by-type for promotions), the controller has a method but no route is defined yet.
