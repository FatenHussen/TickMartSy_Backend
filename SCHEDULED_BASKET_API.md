# Scheduled Basket CRUD API - Admin

## Overview
السلال المجدولة (Scheduled Baskets) هي سلال يتم توصيلها بشكل دوري حسب جدولة محددة.

## الفروقات عن السلال العادية

| الخاصية | السلال العادية | السلال المجدولة |
|---------|----------------|-----------------|
| `is_schedule` | 0 | 1 |
| `is_required` | دائماً 1 | 0 أو 1 |
| `is_extra` | دائماً 0 | 0 أو 1 |
| `shop_product_variant_id` | ID واحد | null |
| `shop_product_variant_ids` | null | مصفوفة من IDs |
| السعر | من variant واحد | مجموع أسعار كل الـ variants |
| الجد
ولة | لا يوجد | جدولة افتراضية |

---

## Endpoints

### 1. List Scheduled Baskets
**GET** `/api/admin/scheduled-baskets`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**
- `page` (optional): رقم الصفحة
- `per_page` (optional): عدد العناصر في الصفحة
- `search` (optional): البحث في الاسم
- `category_id` (optional): تصفية حسب الفئة

**Response:**
```json
{
    "status": true,
    "message": "تمت العملية بنجاح.",
    "data": {
        "items": [...],
        "p               "quantity": 2,
                "is_required": true,
                "is_extra": false,
                "min_quantity": 1,
                "max_quantity": 10,
                "unit_price": 150.00,
                "subtotal": 300.00
            }
        ],
        "schedules": [
            {
                "id": 1,
                "number_of_days": 7,
                "is_active": true
            }
        ]
    }
}
```

---

### 3. Create Scheduled Basket
**POST** `/api/admin/scheduled-baskets`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "category_id": 1,
    "name": {
        "en": "Weekly Basket",
        "ar": "سلة أسبوعية"
    },
    "discount_type": "percentage",
    "discount": 10,
    "delivery_price": 20,
    "offer_ends_at": "01-01-2030",
    "schedule": {
        "number_of_days": 7,
        "is_active": true
    },
    "items": [
        {
            "shop_product_variant_ids": [2, 3, 5],
            "quantity": 2,
            "is_required": true,
            "is_extra": false,
            "min_quantity": 1,
            "max_quantity": 10
        },
        {
            "shop_product_variant_ids": [7, 8],
            "quantity": 1,
            "is_required": false,
            "is_extra": true,
            "min_quantity": 0,
            "max_quantity": 5
        }
    ]
}
```

**ملاحظات:**
- `shop_product_variant_ids`: مصفوفة من IDs للمنتجات في المتجر
- السعر يُحسب تلقائياً من مجموع أسعار كل الـ variants
- `is_required`: إذا كان true، المنتج إجباري في السلة
- `is_extra`: إذا كان true، المنتج إضافي (اختياري)
- `schedule.number_of_days`: عدد الأيام بين كل توصيل (مثلاً 7 = أسبوعياً)

---

### 4. Update Scheduled Basket
**PUT** `/api/admin/scheduled-baskets/{id}`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
    "category_id": 1,
    "name": {
        "en": "Weekly Basket Updated",
        "ar": "سلة أسبوعية محدثة"
    },
    "discount_type": "fixed",
    "discount": 50,
    "schedule": {
        "number_of_days": 14,
        "is_active": true
    },
    "items": [
        {
            "shop_product_variant_ids": [2, 3],
            "quantity": 3,
            "is_required": true,
            "is_extra": false
        }
    ]
}
```

---

### 5. Delete Scheduled Basket
**DELETE** `/api/admin/scheduled-baskets/{id}`

**Response:**
```json
{
    "status": true,
    "message": "تمت العملية بنجاح.",
    "data": true
}
```

---

## مثال كامل - Create Scheduled Basket

### Request
```bash
POST /api/admin/scheduled-baskets
Content-Type: application/json
Authorization: Bearer your_token_here
```

```json
{
    "category_id": 1,
    "name": {
        "en": "Fresh Vegetables Weekly",
        "ar": "خضروات طازجة أسبوعية"
    },
    "discount_type": "percentage",
    "discount": 15,
    "delivery_price": 25,
    "offer_ends_at": "31-12-2026",
    "schedule": {
        "number_of_days": 7,
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

### Response
```json
{
    "status": true,
    "message": "تمت العملية بنجاح.",
    "data": {
        "id": 10,
        "category": {
            "id": 1,
            "name": "خضروات"
        },
        "name": "خضروات طازجة أسبوعية",
        "image": null,
        "num_varieties": 1,
        "offer_ends_at": "2026-12-31",
        "original_price": 450.00,
        "discount": "15.00",
        "discount_type": "percentage",
        "discount_amount": 67.50,
        "final_price": 382.50,
        "rating": 0,
        "average_rating": 0,
        "num_sold": 0,
        "is_on_offer": true,
        "delivery_price": 25,
        "is_schedule": true,
        "items": [
            {
                "id": 20,
                "product_id": 5,
                "variant_id": 8,
                "shop_product_variant_ids": [10, 11, 12],
                "quantity": 1,
                "unit_price": 300.00,
                "subtotal": 300.00,
                "is_required": true,
                "is_extra": false
            },
            {
                "id": 21,
                "product_id": 7,
                "variant_id": 9,
                "shop_product_variant_ids": [15, 16],
                "quantity": 1,
                "unit_price": 150.00,
                "subtotal": 150.00,
                "is_required": false,
                "is_extra": true
            }
        ],
        "schedules": [
            {
                "id": 5,
                "number_of_days": 7,
                "is_active": true
            }
        ],
        "created_at": "2026-02-11 18:00:00",
        "updated_at": "2026-02-11 18:00:00"
    }
}
```

---

## ملاحظات مهمة

1. **حساب السعر**: السعر الإجمالي للـ item يُحسب من مجموع أسعار كل الـ shop_product_variants في المصفوفة
2. **الجدولة**: `number_of_days` يحدد كل كم يوم يتم التوصيل (7 = أسبوعياً، 14 = كل أسبوعين، 30 = شهرياً)
3. **next_delivery_date**: يُحسب تلقائياً بناءً على الجدولة الافتراضية
4. **is_required و is_extra**: يمكن أن يكونا true أو false حسب نوع المنتج في السلة
5. **استخدم JSON**: لأن المصفوفات المتداخلة أسهل في JSON من form-data

---

## Validation Rules

### Create/Update Items
- `shop_product_variant_ids`: مطلوب، مصفوفة، على الأقل عنصر واحد
- `quantity`: مطلوب، رقم صحيح، على الأقل 1
- `is_required`: مطلوب، boolean
- `is_extra`: مطلوب، boolean
- `min_quantity`: اختياري، رقم صحيح، على الأقل 1
- `max_quantity`: اختياري، رقم صحيح، على الأقل 1

### Schedule
- `number_of_days`: مطلوب، رقم صحيح، على الأقل 1
- `is_active`: اختياري، boolean (افتراضي: true)
