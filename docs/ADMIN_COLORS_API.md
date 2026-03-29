# Admin Colors API

## Base
- Base URL: `/api/admin/colors`
- Auth: `Authorization: Bearer <admin_token>` (requires `auth:admin`)
- Content-Type:
  - `application/json` for list/show/delete
  - `multipart/form-data` or `application/json` for create/update

## Endpoints
- `GET /api/admin/colors`
- `POST /api/admin/colors`
- `GET /api/admin/colors/{id}`
- `PUT /api/admin/colors/{id}` or `PATCH /api/admin/colors/{id}`
- `DELETE /api/admin/colors/{id}`

## List Query Params
- Common params from `BaseCRUDController`:
  - `search` (search in `name` and `hex`)
  - `sort_field` (`id`, `hex`, `is_active`, `created_at`)
  - `sort_order` (`asc` or `desc`)
  - `page`, `per_page`
- Extra filters:
  - `hex` (string, max 7, example `#FF0000`)
  - `is_active` (boolean)

## Request (Create)
```json
{
  "name": {
    "en": "Red",
    "ar": "أحمر"
  },
  "hex": "#FF0000",
  "is_active": true
}
```

Validation:
- `name.{locale}`: required for every active language
- `hex`: required, format `#RRGGBB`, unique in `colors` table
- `is_active`: optional boolean

## Request (Update)
```json
{
  "name": {
    "en": "Dark Red",
    "ar": "أحمر غامق"
  },
  "hex": "#CC0000",
  "is_active": false
}
```

Validation:
- all fields optional
- `hex` keeps same rule and uniqueness (ignores current record id)

## Response Envelope
```json
{
  "status": true,
  "message": "Success",
  "data": {}
}
```

## Response: List (`GET /colors`)
```json
{
  "status": true,
  "message": "Success",
  "data": {
    "items": [
      {
        "id": 1,
        "name": "Red",
        "hex": "#FF0000",
        "is_active": true,
        "created_at": "2026-03-29T18:00:00.000000Z",
        "updated_at": "2026-03-29T18:00:00.000000Z"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 10,
      "total": 1
    }
  }
}
```

## Response: Single (`GET /colors/{id}`, `POST`, `PUT/PATCH`)
```json
{
  "status": true,
  "message": "Success",
  "data": {
    "id": 1,
    "name": {
      "en": "Red",
      "ar": "أحمر"
    },
    "hex": "#FF0000",
    "is_active": true,
    "created_at": "2026-03-29T18:00:00.000000Z",
    "updated_at": "2026-03-29T18:00:00.000000Z"
  }
}
```

## Response: Delete (`DELETE /colors/{id}`)
```json
{
  "status": true,
  "message": "Success",
  "data": true
}
```

## Validation Error Example
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "hex": [
      "The hex format is invalid."
    ]
  }
}
```
