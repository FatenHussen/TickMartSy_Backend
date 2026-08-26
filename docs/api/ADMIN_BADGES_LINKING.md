# Admin Badges Linking (Shop / Product / ...)

## Base
- Base URL: `/api/admin`
- Auth: `Authorization: Bearer <admin_token>`

## Badge Payload (shared)
Send badges as an array:
```json
{
  "badges": [
    {"id": 1, "position": "top"},
    {"id": 2, "position": "bottom"}
  ]
}
```
Rules:
- `id` must exist in `badges` table.
- `position` is `top` or `bottom`.

## Where Badges Are Wired In Admin

### Shops
Endpoints:
- `POST /shops`
- `PUT /shops/{id}` or `PATCH /shops/{id}`

Request:
- `badges` is accepted and saved.

Response:
- `GET /shops/{id}` returns `badges`:
```json
"badges": [
  {"id": 1, "name": "Top Seller", "color": "#FFAA00", "postion": "top"}
]
```
Note: the response key is `postion` (typo from backend resource).

---

### Products
Endpoints:
- `POST /products`
- `PUT /products/{id}` or `PATCH /products/{id}`

Request:
- `badges` is accepted by validation.

Important note:
- Current backend handling for products does **not** properly sync `badges` to the pivot (custom `ProductService::handleRelations`). If you send `badges`, it will not attach correctly. This needs a backend fix if you want product badges from admin.

---

### Baskets (non-scheduled)
Endpoints:
- `POST /baskets`
- `PUT /baskets/{id}` or `PATCH /baskets/{id}`

Request:
- `badges` is accepted by validation.

Important note:
- `BasketService` does not sync `badges` (it only syncs `items`). So badges are not persisted currently. Needs backend fix if required.

Response:
- `GET /baskets/{id}` includes `badges` if they exist in DB:
```json
"badges": [
  {"id": 1, "name": "Hot", "color": "#FF0000", "postion": "top"}
]
```

---

### Scheduled Baskets
Endpoints:
- `POST /scheduled-baskets`
- `PUT /scheduled-baskets/{id}` or `PATCH /scheduled-baskets/{id}`

Request:
- `badges` is accepted by validation.

Important note:
- `ScheduledBasketService` does not sync `badges`, so they are not persisted currently.

---

### Recipes
Endpoints:
- `POST /recipes`
- `PUT /recipes/{id}` or `PATCH /recipes/{id}`

Request:
- `badges` is accepted and saved.

Response:
- `GET /recipes/{id}` includes `badges`:
```json
"badges": [
  {"id": 3, "name": "Chef Pick", "color": "#00AAFF", "postion": "bottom"}
]
```

---

## Quick Summary
Working now:
- Shops
- Recipes

Accepted but not persisted (needs backend fix):
- Products
- Baskets
- Scheduled Baskets
