# Basket System - Complete Summary

## نظرة عامة
تم إنشاء نظام كامل لإدارة السلال (Baskets) من لوحة تحكم الأدمن، مع دعم نوعين من السلال:

1. **السلال العادية** (Regular Baskets) - `is_schedule = 0`
2. **السلال المجدولة** (Scheduled Baskets) - `is_schedule = 1`

---

## 1. السلال العادية (Regular Baskets)

### الخصائص
- `is_schedule`: 0
- `is_required`: دائماً true
- `is_extra`: دائماً false
- `shop_product_variant_id`: ID واحد لكل item
- السعر: يُجلب من shop_product_variant واحد

### الملفات المنشأة
```
app/Services/Admin/BasketService.php
app/Http/Controllers/Admin/Basket/BasketController.php
app/Http/Requests/Admin/Basket/StoreRequest.php
app/Http/Requests/Admin/Basket/UpdateRequest.php
app/Http/Requests/Admin/Basket/FilterRequest.php
app/Http/Resources/Admin/Basket/OneResource.php
app/Http/Resources/Admin/Basket/AllResource.php
app/Http/Resources/Admin/Basket/BasketItemResource.php
```

### API Endpoints
```
GET    /api/admin/baskets          - List all regular baskets
GET    /api/admin/baskets/{id}     - Show single basket
POST   /api/admin/baskets          - Create new basket
PUT    /api/admin/baskets/{id}     - Update basket
DELETE /api/admin/baskets/{id}     - Delete basket
```

### مثال Create Request (JSON)
```json
{
    "category_id": 1,
    "name": {
        "en": "Breakfast Basket",
        "ar": "سلة الإفطار"
    },
    "discount_type": "percentage",
    "discount": 10,
    "items": [
        {
            "shop_product_variant_id": 2,
            "quantity": 2
        },
        {
            "shop_product_variant_id": 3,
            "quantity": 1
        }
    ],
    "offer_ends_at": "01-01-2030"
}
```

---

## 2. السلال المجدولة (Scheduled Baskets)

### الخصائص
- `is_schedule`: 1
- `is_required`: يمكن أن يكون true أو false
- `is_extra`: يمكن أن يكون true أو false
- `shop_product_variant_ids`: مصفوفة من IDs لكل item
- السعر: مجموع أسعار كل shop_product_variants في المصفوفة
- الجدولة: جدولة افتراضية تحدد تكرار التوصيل

### الملفات المنشأة
```
app/Services/Admin/ScheduledBasketService.php
app/Http/Controllers/Admin/Basket/ScheduledBasketController.php
app/Http/Requests/Admin/Basket/ScheduledBasket/StoreRequest.php
app/Http/Requests/Admin/Basket/ScheduledBasket/UpdateRequest.php
app/Http/Requests/Admin/Basket/ScheduledBasket/FilterRequest.php
```

### API Endpoints
```
GET    /api/admin/scheduled-baskets          - List all scheduled baskets
GET    /api/admin/scheduled-baskets/{id}     - Show single scheduled basket
POST   /api/admin/scheduled-baskets          - Create new scheduled basket
PUT    /api/admin/scheduled-baskets/{id}     - Update scheduled basket
DELETE /api/admin/scheduled-baskets/{id}     - Delete scheduled basket
```

### مثال Create Request (JSON)
```json
{
    "category_id": 1,
    "name": {
        "en": "Weekly Vegetables",
        "ar": "خضروات أسبوعية"
    },
    "discount_type": "percentage",
    "discount": 15,
    "delivery_price": 25,
    "offer_ends_at": "31-12-2026",
    "schedule": {
        "title": {
            "en": "Weekly Delivery",
            "ar": "توصيل أسبوعي"
        },
        "number_of_days": 7,
        "discount_type": "percentage",
        "discount_value": 5,
        "is_active": true
    },
    "items": [
        {
            "shop_product_variant_ids": [10, 11, 12],
            "quantity": 1,
            "is_required": true,
            "is_extra": false,
            "min_quantity": 1,
            "max_quantity": 5
        },
        {
            "shop_product_variant_ids": [15, 16],
            "quantity": 1,
            "is_required": false,
            "is_extra": true,
            "min_quantity": 0,
            "max_quantity": 3
        }
    ]
}
```

---

## المقارنة بين النوعين

| الخاصية | السلال العادية | السلال المجدولة |
|---------|----------------|-----------------|
| **is_schedule** | 0 | 1 |
| **is_required** | دائماً true | true أو false |
| **is_extra** | دائماً false | true أو false |
| **shop_product_variant_id** | ID واحد | null |
| **shop_product_variant_ids** | null | مصفوفة من IDs |
| **السعر** | من variant واحد | مجموع أسعار الـ variants |
| **الجدولة** | لا يوجد | جدولة افتراضية |
| **next_delivery_date** | لا يوجد | يُحسب من الجدولة |
| **min/max_quantity** | افتراضي (1/10) | قابل للتخصيص |

---

## الملفات المشتركة

### Models
```
app/Models/Basket.php
app/Models/BasketItem.php
app/Models/BasketSchedule.php
```

### Resources (مشتركة)
```
app/Http/Resources/Admin/Basket/OneResource.php
app/Http/Resources/Admin/Basket/AllResource.php
app/Http/Resources/Admin/Basket/BasketItemResource.php
```

---

## ملاحظات مهمة

### 1. استخدام JSON بدلاً من form-data
لأن Laravel لا يدعم parsing المصفوفات المتداخلة في form-data مع PUT/PATCH، يُفضل استخدام JSON:

```
Content-Type: application/json
```

### 2. رفع الصور مع form-data
إذا كنت بحاجة لرفع صورة، استخدم POST مع `_method=PUT`:

```
POST /api/admin/baskets/5
_method: PUT
image: [file]
... other fields
```

### 3. حساب الأسعار تلقائياً
- السعر الإجمالي (`price`) يُحسب تلقائياً من مجموع أسعار الـ items
- `num_varieties` يُحسب تلقائياً من عدد الـ items غير الإضافية (`is_extra = false`)
- `discount_amount` و `final_price` يُحسبان تلقائياً

### 4. الجدولة (Scheduled Baskets فقط)
- `number_of_days`: عدد الأيام بين كل توصيل
  - 7 = أسبوعياً
  - 14 = كل أسبوعين
  - 30 = شهرياً
- `next_delivery_date` يُحسب تلقائياً: `today + number_of_days`

### 5. Logging
تم إضافة logging مفصل في:
- `BasketService::update()`
- `ScheduledBasketService::update()`
- `syncBasketItems()`
- `syncScheduledBasketItems()`

لمراجعة الـ logs:
```bash
Get-Content storage/logs/laravel.log -Tail 100
```

---

## التوثيق الكامل

- **السلال العادية**: `BASKET_API_README.md`
- **السلال المجدولة**: `SCHEDULED_BASKET_API.md`
- **حل مشكلة form-data**: `BASKET_UPDATE_FIX.md`
- **أمثلة Postman**: `POSTMAN_BASKET_EXAMPLE.md`

---

## الخطوات التالية

1. ✅ تم إنشاء CRUD للسلال العادية
2. ✅ تم إنشاء CRUD للسلال المجدولة
3. ⏳ اختبار الـ APIs في Postman
4. ⏳ إنشاء Seeder للبيانات التجريبية (إذا لزم الأمر)

---

## Routes Summary

```php
// Regular Baskets
Route::apiResource('baskets', BasketController::class);

// Scheduled Baskets
Route::apiResource('scheduled-baskets', ScheduledBasketController::class);
```

كل الـ routes محمية بـ `auth:admin` middleware.
