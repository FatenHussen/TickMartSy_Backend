# Admin Vendor Services CRUDs

هذا الملف مخصص للفرونت/الداشبورد لشرح CRUDs الخاصة بـ:
- أنواع الخدمات `vendor-service-types`
- الخدمات العامة للبائعين `vendor-services`
- خدمات الشوب `shop-vendor-services`

## Base Info

- Base URL: `/api/admin`
- Auth: مطلوب `auth:admin` (Bearer Token)
- غلاف الاستجابة القياسي:

```json
{
  "status": true,
  "message": "Success",
  "data": {}
}
```

## Common List Query Params

تعمل على `index` (GET list):

- `page` (default: `1`)
- `per_page` (default: `10`)
- `sort_field` (default: `id`)
- `sort_order` (`asc` أو `desc`, default: `desc`)
- `search` (بحث نصي عام)

## 1) Vendor Service Types

### Endpoints

| Method | Endpoint | الوصف |
|---|---|---|
| GET | `/api/admin/vendor-service-types` | قائمة أنواع الخدمات |
| POST | `/api/admin/vendor-service-types` | إنشاء نوع خدمة |
| GET | `/api/admin/vendor-service-types/{id}` | تفاصيل نوع خدمة |
| PUT/PATCH | `/api/admin/vendor-service-types/{id}` | تعديل نوع خدمة |
| DELETE | `/api/admin/vendor-service-types/{id}` | حذف نوع خدمة |

### Create Payload

```json
{
  "name": {
    "ar": "خدمات منزلية",
    "en": "Home Services"
  },
  "is_active": true
}
```

### Update Payload

كل الحقول اختيارية:

```json
{
  "name": {
    "ar": "خدمات منزلية محدثة",
    "en": "Updated Home Services"
  },
  "is_active": false
}
```

## 2) Vendor Services

### Endpoints

| Method | Endpoint | الوصف |
|---|---|---|
| GET | `/api/admin/vendor-services` | قائمة خدمات البائعين |
| POST | `/api/admin/vendor-services` | إنشاء خدمة بائع |
| GET | `/api/admin/vendor-services/{id}` | تفاصيل خدمة بائع |
| PUT/PATCH | `/api/admin/vendor-services/{id}` | تعديل خدمة بائع |
| DELETE | `/api/admin/vendor-services/{id}` | حذف خدمة بائع |

### Create Payload

```json
{
  "vendor_service_type_id": 1,
  "name": {
    "ar": "تنظيف",
    "en": "Cleaning"
  },
  "description": {
    "ar": "تنظيف شامل",
    "en": "Full cleaning"
  },
  "is_active": true
}
```

### Update Payload

كل الحقول اختيارية:

```json
{
  "vendor_service_type_id": 2,
  "name": {
    "ar": "تنظيف متقدم"
  },
  "description": {
    "ar": "تنظيف متقدم مع تعقيم"
  },
  "is_active": true
}
```

## 3) Shop Vendor Services

هذا المورد يربط الشوب مع خدمة بائع معيّنة، ويضيف سعر/مدة/جدول لهذه الخدمة داخل الشوب.

### Endpoints

| Method | Endpoint | الوصف |
|---|---|---|
| GET | `/api/admin/shop-vendor-services` | قائمة خدمات الشوب |
| POST | `/api/admin/shop-vendor-services` | إضافة خدمة لشوب |
| GET | `/api/admin/shop-vendor-services/{id}` | تفاصيل خدمة شوب |
| PUT/PATCH | `/api/admin/shop-vendor-services/{id}` | تعديل خدمة شوب |
| DELETE | `/api/admin/shop-vendor-services/{id}` | حذف خدمة شوب |

### Create Payload

```json
{
  "shop_id": 15,
  "vendor_service_id": 3,
  "extra_details": {
    "note": "يشمل المواد"
  },
  "price": 25,
  "price_unit": "per hour",
  "duration_minutes": 60,
  "schedule": {
    "monday": { "open": "09:00", "close": "18:00", "closed": false },
    "tuesday": { "open": "09:00", "close": "18:00", "closed": false }
  },
  "is_active": true
}
```

### Update Payload

لا يمكن تغيير `shop_id` و`vendor_service_id` عبر update الحالي، فقط:

```json
{
  "price": 30,
  "price_unit": "per visit",
  "duration_minutes": 90,
  "is_active": true
}
```

### Important DB Rule

- يوجد unique constraint على:
  - `shop_id + vendor_service_id`
- يعني نفس الخدمة لا تتكرر مرتين على نفس الشوب.

## Response Pattern for CRUD

### List (index)

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "items": [],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 10,
      "total": 0
    }
  }
}
```

### Show / Store / Update

```json
{
  "status": true,
  "message": "Success",
  "data": {}
}
```

### Delete

```json
{
  "status": true,
  "message": "Success",
  "data": true
}
```

## Validation Errors

عند خطأ validation (مثل نقص حقل required) يرجع Laravel غالباً `422` مع تفاصيل الأخطاء حسب الحقول.
