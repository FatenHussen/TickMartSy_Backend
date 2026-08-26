# Admin Contact Methods API

This document explains the admin CRUD API for `ContactMethod`.

## Base Endpoint

- `/api/admin/contact-methods`

## Authentication

- Requires `auth:admin` token.

## Endpoints

### 1) List Contact Methods

- `GET /api/admin/contact-methods`

Supported query params (inherited from `BaseCRUDController`):

- `search` (optional)
- `sort_field` (optional, allowed: `id`, `type`, `created_at`)
- `sort_order` (optional: `asc`, `desc`)
- `page` (optional)
- `per_page` (optional)

Example:

- `GET /api/admin/contact-methods?search=+963&page=1&per_page=10`

---

### 2) Show One Contact Method

- `GET /api/admin/contact-methods/{id}`

---

### 3) Create Contact Method

- `POST /api/admin/contact-methods`
- `Content-Type: multipart/form-data`

Fields:

- `type` (required): `number | email | url | whts`
- `value` (required): string, max 255
- `icon` (optional): image (`jpeg,png,jpg,webp,svg`), max 2MB

Example payload (form-data):

- `type = whts`
- `value = +963988888888`
- `icon = (file)`

---

### 4) Update Contact Method

- `POST /api/admin/contact-methods/{id}` with `_method=PUT`
  - or `PUT /api/admin/contact-methods/{id}`
- `Content-Type: multipart/form-data`

Fields (all optional):

- `type`: `number | email | url | whts`
- `value`: string, max 255
- `icon`: image (`jpeg,png,jpg,webp,svg`), max 2MB

---

### 5) Delete Contact Method

- `DELETE /api/admin/contact-methods/{id}`

## Success Response Shape

### List

```json
{
  "status": true,
  "message": "success",
  "data": {
    "items": [
      {
        "id": 1,
        "type": "email",
        "value": "support@example.com",
        "icon": "https://example.com/storage/contact/icons/mail.png",
        "created_at": "2026-04-30 14:10"
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

### Show / Store / Update

```json
{
  "status": true,
  "message": "success",
  "data": {
    "id": 1,
    "type": "email",
    "value": "support@example.com",
    "icon": "https://example.com/storage/contact/icons/mail.png"
  }
}
```

### Delete

```json
{
  "status": true,
  "message": "success",
  "data": true
}
```

## Validation Rules Summary

- `type`: in `number,email,url,whts`
- `value`: string, max 255
- `icon`: nullable image, max 2048 KB
