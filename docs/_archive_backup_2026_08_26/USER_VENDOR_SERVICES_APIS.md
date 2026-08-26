# User Vendor Services APIs

هذا الملف مخصص للفرونت (User App) لشرح APIs المتعلقة بـ:
- أنواع الخدمات + خدمات البائع
- الشوبات (مع فلتر مزود خدمة)
- خدمات الشوب

## Base Info

- Base URL: `/api/user`
- أغلب endpoints هنا Public
- غلاف الاستجابة القياسي:

```json
{
  "status": true,
  "message": "Success",
  "data": {}
}
```

## 1) Get Service Types + Vendor Services

### Endpoint

- `GET /api/user/vendor-services`

### Behavior

- يرجع فقط الأنواع الفعالة `vendor_service_types.is_active = true`
- وكل نوع يحتوي فقط خدمات فعالة `vendor_services.is_active = true`

### Response Example

```json
{
  "status": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "name": {
        "ar": "خدمات منزلية",
        "en": "Home Services"
      },
      "services": [
        {
          "id": 10,
          "name": {
            "ar": "تنظيف",
            "en": "Cleaning"
          },
          "description": {
            "ar": "تنظيف شامل",
            "en": "Full cleaning"
          }
        }
      ]
    }
  ]
}
```

## 2) Get Shops List

### Endpoint

- `GET /api/user/shops`

### Supported Filters

- `area_id`
- `city_id`
- `governorate_id`
- `category_id`
- `brand_id`
- `search`
- `type` (`nearby`, `offers`, `top_rated`, `active`)
- `lat` + `lng` (مطلوبين إذا `type=nearby`)
- `max_distance` (افتراضي 100 كم في service)
- `is_service_provider` (`1` أو `0`)

### Important Example (Service Providers)

- مزودي الخدمة فقط:
  - `GET /api/user/shops?is_service_provider=1`
- غير مزودي الخدمة:
  - `GET /api/user/shops?is_service_provider=0`

### Response Shape

`data` في هذا endpoint عبارة عن:

```json
{
  "items": [
    {
      "id": 15,
      "name": {},
      "description": {},
      "email": "shop@example.com",
      "mobile": "0999999999",
      "logo_url": "https://...",
      "is_active": true,
      "is_open_now": true,
      "is_service_provider": true,
      "created_at": "2026-04-07 13:20",
      "is_favorite": false,
      "average_rating": 4.5,
      "categories": [],
      "vendor": {}
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 1
  }
}
```

## 3) Get Single Shop

### Endpoint

- `GET /api/user/shops/{id}`

### Response Highlights

يرجع تفاصيل أوسع من list، مثل:
- `address`
- `phone`
- `working_hours`
- `cover_images_urls`
- `ratings_count`
- `is_service_provider`
- `categories`

## 4) Get Shop Services

### Endpoint

- `GET /api/user/shops/{id}/services`

### Behavior

- يرجع فقط الخدمات الفعالة داخل الشوب (`shop_vendor_services.is_active = true`)
- ويضمن إرجاع نوع الخدمة عبر:
  - `vendorService.type`

### Response Example

```json
{
  "status": true,
  "message": "Success",
  "data": [
    {
      "id": 5,
      "service": {
        "id": 10,
        "name": {
          "ar": "تنظيف",
          "en": "Cleaning"
        },
        "description": {
          "ar": "تنظيف شامل",
          "en": "Full cleaning"
        },
        "type": {
          "id": 1,
          "name": {
            "ar": "خدمات منزلية",
            "en": "Home Services"
          }
        }
      },
      "price": "25.00",
      "price_unit": "per hour",
      "duration_minutes": 60,
      "extra_details": {
        "note": "يشمل المواد"
      },
      "schedule": {
        "monday": {
          "open": "09:00",
          "close": "18:00",
          "closed": false
        }
      },
      "is_open_now": true
    }
  ]
}
```

## Suggested Frontend Flow

1. صفحة الاستكشاف حسب النوع:
   - `GET /api/user/vendor-services`
2. صفحة قائمة مزودي الخدمة:
   - `GET /api/user/shops?is_service_provider=1`
3. صفحة تفاصيل شوب خدمات:
   - `GET /api/user/shops/{id}`
   - `GET /api/user/shops/{id}/services`
