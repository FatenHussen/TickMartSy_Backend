# User Contact Methods API

This document explains the user-facing API for `ContactMethod`.

## Base Endpoint

- `GET /api/user/contact-methods`

## Authentication

- Public endpoint (no token required).

## Query Parameters

- `type` (optional): filter by method type.
  - Allowed values: `number`, `email`, `url`, `whts`

## Behavior

- If `type` is not sent, the API returns all contact methods ordered by `id` ascending.
- If `type` is sent, the API returns only matching records.
- If `type` has an invalid value, validation error is returned.

## Success Response Example

```json
{
  "status": true,
  "message": "success",
  "data": [
    {
      "id": 1,
      "type": "number",
      "value": "+963999999999",
      "icon": "https://example.com/storage/contact/icons/phone.png",
      "created_at": "2026-04-30 14:10"
    },
    {
      "id": 2,
      "type": "whts",
      "value": "+963988888888",
      "icon": "https://example.com/storage/contact/icons/whatsapp.png",
      "created_at": "2026-04-30 14:12"
    }
  ]
}
```

## Validation Error Example

Request:

- `GET /api/user/contact-methods?type=facebook`

Response:

```json
{
  "status": false,
  "message": "The given data was invalid.",
  "errors": {
    "type": [
      "The selected type is invalid."
    ]
  }
}
```
