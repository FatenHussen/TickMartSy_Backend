## Delivery Distance Ranges Admin API

توثيق CRUD الخاص بإدارة نطاقات المسافة للتوصيل من الكنترولر:
`app/Http/Controllers/Admin/DeliveryDistanceRange/DeliveryDistanceRangeCrudController.php`

## Base URL

`/api/admin`

## Authentication

- جميع الـ endpoints هنا داخل `auth:admin`.
- يجب إرسال توكن الأدمن في الهيدر (Bearer token).

## Resource Endpoints

- `GET /api/admin/delivery-distance-ranges`
- `GET /api/admin/delivery-distance-ranges/{id}`
- `POST /api/admin/delivery-distance-ranges`
- `PUT /api/admin/delivery-distance-ranges/{id}`
- `PATCH /api/admin/delivery-distance-ranges/{id}`
- `DELETE /api/admin/delivery-distance-ranges/{id}`

## Data Model

- `min_distance` (number, required)
- `max_distance` (number|null)
- `multiplier` (number, required)

### Range Rule

كل Range يتم تفسيره بهذه القاعدة:

`min_distance <= distance < max_distance`

- `min_distance` مشمول (Included)
- `max_distance` غير مشمول (Excluded)
- إذا `max_distance = null` فهذا يعني `∞`

أمثلة:

- `0 -> 5` يعني `0 <= d < 5`
- `5 -> 8` يعني `5 <= d < 8`
- `8 -> null` يعني `d >= 8`

## Validation Rules

عند `Create` و `Update`:

- `min_distance` يجب أن يكون رقمًا و `>= 0`
- إذا `max_distance` ليست `null`:
  - يجب أن تكون رقمًا
  - ويجب أن تكون `> min_distance`
- `multiplier` يجب أن يكون رقمًا و `> 0`

### Overlap Prevention (Strict)

يمنع منعًا باتًا أي تداخل بين النطاقات.

يتم اعتبار وجود تعارض إذا تحقق الشرطان:

`(new.min < existing.max OR existing.max IS NULL)`
`AND`
`(new.max > existing.min OR new.max IS NULL)`

عند التعارض ترجع Validation Error:

`يوجد تعارض في المسافات مع Range آخر`

> الملاحظة المهمة: التلامس على الحدود مسموح (مثل 0-5 و 5-8) لأنه ليس تداخلًا.

## Create

### Endpoint

`POST /api/admin/delivery-distance-ranges`

### Request Body

```json
{
  "min_distance": 5,
  "max_distance": 8,
  "multiplier": 1.5
}
```

### Success

Status: `200 OK`

```json
{
  "data": {
    "id": 2,
    "min_distance": 5,
    "max_distance": 8,
    "multiplier": 1.5,
    "created_at": "2026-04-20 13:10"
  }
}
```

### Validation Error (Overlap)

Status: `422 Unprocessable Entity`

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "range": [
      "يوجد تعارض في المسافات مع Range آخر"
    ]
  }
}
```

## Update

### Endpoint

`PUT /api/admin/delivery-distance-ranges/{id}`
أو
`PATCH /api/admin/delivery-distance-ranges/{id}`

### Request Body (مثال)

```json
{
  "min_distance": 8,
  "max_distance": null,
  "multiplier": 2
}
```

### Notes

- `PUT/PATCH` يدعم التعديل الجزئي (يمكن إرسال حقل واحد أو أكثر).
- عند إرسال `max_distance = null` يصبح النطاق مفتوحًا حتى ما لا نهاية.

## List

### Endpoint

`GET /api/admin/delivery-distance-ranges`

### Response

Status: `200 OK`

```json
{
  "data": {
    "items": [
      {
        "id": 1,
        "min_distance": 0,
        "max_distance": 5,
        "multiplier": 1,
        "created_at": "2026-04-20 13:00"
      },
      {
        "id": 2,
        "min_distance": 5,
        "max_distance": 8,
        "multiplier": 1.5,
        "created_at": "2026-04-20 13:01"
      },
      {
        "id": 3,
        "min_distance": 8,
        "max_distance": null,
        "multiplier": 2,
        "created_at": "2026-04-20 13:02"
      }
    ],
    "pagination": null
  }
}
```

## Show

### Endpoint

`GET /api/admin/delivery-distance-ranges/{id}`

### Response

Status: `200 OK`

```json
{
  "data": {
    "id": 3,
    "min_distance": 8,
    "max_distance": null,
    "multiplier": 2,
    "created_at": "2026-04-20 13:02"
  }
}
```

## Delete

### Endpoint

`DELETE /api/admin/delivery-distance-ranges/{id}`

### Response

Status: `200 OK`

```json
{
  "data": true
}
```

## Ready Example (Valid Ranges)

```json
[
  { "min_distance": 0, "max_distance": 5, "multiplier": 1.0 },
  { "min_distance": 5, "max_distance": 8, "multiplier": 1.5 },
  { "min_distance": 8, "max_distance": null, "multiplier": 2.0 }
]
```
