# Frontend Service Cards API

هذا الملف يشرح للفرونت كيف يبني كروت:

1. كروت أنواع الخدمات (`Service Types`)
2. كروت خدمات البائع (`Vendor Services`)
3. كروت خدمات الشوب (`Shop Services`)

جميع أمثلة المسارات هنا مبنية على `routes/api/user.php`.

## شكل الاستجابة العام

كل API يرجّع نفس الغلاف من `Controller::sendResponse`:

```json
{
  "status": true,
  "message": "Success",
  "data": {}
}
```

## 1) كروت أنواع الخدمات + خدمات البائع

### Endpoint

- `GET /api/user/vendor-services`

### ماذا يرجّع؟

يرجّع أنواع الخدمات الفعالة، وكل نوع يحتوي خدماته الفعالة:

```json
{
  "status": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "name": "خدمات منزلية",
      "services": [
        {
          "id": 10,
          "name": "تنظيف",
          "description": "تنظيف شامل"
        }
      ]
    }
  ]
}
```

### كيف نبني الكروت بالفرونت؟

- كرت نوع الخدمة (`Service Type Card`)
  - `id`
  - `name`
  - `services_count = services.length`

- كرت خدمة البائع (`Vendor Service Card`) داخل كل نوع
  - `id`
  - `name`
  - `description`

ملاحظة: القيم `name` و`description` تأتي مترجمة حسب `app locale`.

## 2) كروت الشوب (مع فلتر مزود خدمة)

### Endpoint

- `GET /api/user/shops`

### فلتر مزود الخدمة

- مزودي الخدمة فقط:
  - `GET /api/user/shops?is_service_provider=1`
- غير مزودي الخدمة:
  - `GET /api/user/shops?is_service_provider=0`

### أهم حقول الكرت من قائمة الشوب

من `Shop AllResource`:

- `id`
- `name`
- `description`
- `logo_url`
- `is_open_now`
- `is_service_provider`
- `average_rating`
- `is_favorite`
- `vendor`
- `categories`

## 3) كروت خدمات الشوب

### Endpoint

- `GET /api/user/shops/{shopId}/services`

### ماذا يرجّع؟

يرجّع خدمات الشوب من جدول `shop_vendor_services` مع تفاصيل الخدمة ونوعها:

```json
{
  "status": true,
  "message": "Success",
  "data": [
    {
      "id": 5,
      "service": {
        "id": 10,
        "name": "تنظيف",
        "description": "تنظيف شامل",
        "type": {
          "id": 1,
          "name": "خدمات منزلية"
        }
      },
      "price": "25.00",
      "price_unit": "per hour",
      "duration_minutes": 60,
      "extra_details": {
        "note": "مواد التنظيف مشمولة"
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

### كيف نبني كرت خدمة الشوب؟

- `title = service.name`
- `subtitle = service.type.name`
- `description = service.description`
- `price_text = price + " " + price_unit` (إذا موجود)
- `duration_text = duration_minutes` بالدقائق (إذا موجود)
- `is_open_now` لبادج "متاح الآن"
- `extra_details` لعرض تفاصيل إضافية

## تسلسل استخدام مقترح للواجهة

1. جلب الشوبات مع الفلتر المطلوب:
   - مثال: `GET /api/user/shops?is_service_provider=1`
2. عند فتح صفحة الشوب:
   - جلب خدماته: `GET /api/user/shops/{shopId}/services`
3. لصفحة الاستكشاف حسب النوع:
   - جلب الأنواع والخدمات: `GET /api/user/vendor-services`

## ملاحظات تنفيذية سريعة

- إذا كنت تعرض خدمات فقط، اعتمد على `GET /api/user/shops/{shopId}/services` لأنه يرجّع السعر والمدة والجدول.
- إذا كنت تحتاج تبويب حسب النوع (Category Tabs)، اعتمد على `GET /api/user/vendor-services`.
- فلتر `is_service_provider` موجود على قائمة الشوب ومفيد لفصل تجربة "مزودي الخدمة" عن "المتاجر العادية".
