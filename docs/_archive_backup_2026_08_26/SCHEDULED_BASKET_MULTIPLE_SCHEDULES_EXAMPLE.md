# مثال إنشاء سلة مجدولة مع جداول متعددة

## نظرة عامة
يمكنك إنشاء سلة مجدولة مع جداول توصيل متعددة (مثلاً: توصيل كل 3 أيام، كل 7 أيام، كل 14 يوم).

## Endpoint
```
POST /api/admin/scheduled-baskets
```

## Request Body

### البيانات الأساسية للسلة
```json
{
  "category_id": 19,
  "name": {
    "ar": "سلة الخضروات الأسبوعية",
    "en": "Weekly Vegetables Basket"
  },
  "discount": 10,
  "discount_type": "fixed",
  "delivery_price": 5,
  "image": "(binary file)"
}
```

### الجداول المتعددة (schedules)
```json
{
  "schedules": [
    {
      "title": {
        "ar": "توصيل كل 3 أيام",
        "en": "Every 3 Days Delivery"
      },
      "number_of_days": 3,
      "discount_type": "percentage",
      "discount_value": 5,
      "is_active": true,
      "is_default": true
    },
    {
      "title": {
        "ar": "توصيل أسبوعي",
        "en": "Weekly Delivery"
      },
      "number_of_days": 7,
      "discount_type": "percentage",
      "discount_value": 10,
      "is_active": true,
      "is_default": false
    },
    {
      "title": {
        "ar": "توصيل كل أسبوعين",
        "en": "Bi-Weekly Delivery"
      },
      "number_of_days": 14,
      "discount_type": "fixed",
      "discount_value": 15,
      "is_active": true,
      "is_default": false
    }
  ]
}
```

### المنتجات (items)
```json
{
  "items": [
    {
      "shop_product_variant_id": 1,
      "shop_product_variant_ids": [77, 88],
      "quantity": 2,
      "is_required": true,
      "is_extra": false,
      "min_quantity": 1,
      "max_quantity": 5
    },
    {
      "shop_product_variant_id": 4,
      "quantity": 1,
      "is_required": false,
      "is_extra": true,
      "min_quantity": 1,
      "max_quantity": 3
    }
  ]
}
```

### الشارات (badges)
```json
{
  "badges": [
    {
      "id": 2,
      "position": "top"
    },
    {
      "id": 3,
      "position": "bottom"
    }
  ]
}
```

## مثال كامل بصيغة Form-Data

```
category_id: 19
name[ar]: سلة الخضروات الأسبوعية
name[en]: Weekly Vegetables Basket
discount: 10
discount_type: fixed
delivery_price: 5
image: (binary)

schedules[0][title]
es[2][title][en]: Bi-Weekly Delivery
schedules[2][number_of_days]: 14
schedules[2][discount_type]: fixed
schedules[2][discount_value]: 15
schedules[2][is_active]: 1
schedules[2][is_default]: 0

items[0][shop_product_variant_id]: 1
items[0][shop_product_variant_ids][]: 77
items[0][shop_product_variant_ids][]: 88
items[0][quantity]: 2
items[0][is_required]: 1
items[0][is_extra]: 0
items[0][min_quantity]: 1
items[0][max_quantity]: 5

items[1][shop_product_variant_id]: 4
items[1][quantity]: 1
items[1][is_required]: 0
items[1][is_extra]: 1
items[1][min_quantity]: 1
items[1][max_quantity]: 3

badges[0][id]: 2
badges[0][position]: top

badges[1][id]: 3
badges[1][position]: bottom
```

## Response

```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": {
    "id": 5,
    "category": {
      "id": 19,
      "name": "خضروات"
    },
    "name": {
      "ar": "سلة الخضروات الأسبوعية",
      "en": "Weekly Vegetables Basket"
    },
    "image": "https://example.com/storage/baskets/image.png",
    "num_varieties": 1,
    "offer_ends_at": null,
    "original_price": 321,
    "discount": "10.00",
    "discount_type": "fixed",
    "discount_amount": 10,
    "final_price": 311,
    "rating": 0,
    "average_rating": 0,
    "num_sold": 0,
    "is_on_offer": false,
    "delivery_price": 5,
    "is_schedule": true,
    "items": [
      {
        "id": 19,
        "product_id": 1,
        "variant_id": 1,
        "shop_product_variant_id": 1,
        "product": {
          "id": 1,
          "name": "أرز بسمتي فاخر",
          "image": "https://example.com/storage/product/image8.jpg",
          "is_instant_delivery": 1
        },
        "variant": ["#fc0303", "وسط"],
        "alternatives": [
          {
            "product_id": 7,
            "shop_product_variant_id": 77,
            "name": "أرز بني طويل الحبة أديداس",
            "image_url": "https://example.com/storage/product/image10.jpg",
            "price": 52
          }
        ],
        "quantity": 2,
        "unit_price": 193,
        "subtotal": 386,
        "is_required": true,
        "is_extra": false,
        "min_quantity": 1,
        "max_quantity": 5
      }
    ],
    "extras": [
      {
        "id": 20,
        "product_id": 1,
        "variant_id": 2,
        "shop_product_variant_id": 4,
        "product": {
          "id": 1,
          "name": "أرز بسمتي فاخر",
          "image": "https://example.com/storage/product/image8.jpg",
          "is_instant_delivery": 1
        },
        "variant": ["#fc0303", "كبير"],
        "quantity": 1,
        "unit_price": 128,
        "subtotal": 128,
        "is_required": false,
        "is_extra": true,
        "min_quantity": 1,
        "max_quantity": 3
      }
    ],
    "schedules": [
      {
        "id": 11,
        "title": {
          "ar": "توصيل كل 3 أيام",
          "en": "Every 3 Days Delivery"
        },
        "number_of_days": 3,
        "discount_type": "percentage",
        "discount_value": 5,
        "is_active": true,
        "is_default": true,
        "created_at": "2026-03-26 14:00:10",
        "updated_at": "2026-03-26 14:00:10"
      },
      {
        "id": 12,
        "title": {
          "ar": "توصيل أسبوعي",
          "en": "Weekly Delivery"
        },
        "number_of_days": 7,
        "discount_type": "percentage",
        "discount_value": 10,
        "is_active": true,
        "is_default": false,
        "created_at": "2026-03-26 14:00:10",
        "updated_at": "2026-03-26 14:00:10"
      },
      {
        "id": 13,
        "title": {
          "ar": "توصيل كل أسبوعين",
          "en": "Bi-Weekly Delivery"
        },
        "number_of_days": 14,
        "discount_type": "fixed",
        "discount_value": 15,
        "is_active": true,
        "is_default": false,
        "created_at": "2026-03-26 14:00:10",
        "updated_at": "2026-03-26 14:00:10"
      }
    ],
    "badges": [
      {
        "id": 2,
        "name": "جديد",
        "color": "#ff0000",
        "position": "top"
      },
      {
        "id": 3,
        "name": "عرض خاص",
        "color": "#00ff00",
        "position": "bottom"
      }
    ],
    "created_at": "2026-03-26 13:56:12",
    "updated_at": "2026-03-26 14:00:09"
  }
}
```

## ملاحظات مهمة

1. **schedules مطلوب**: يجب إرسال جدولة واحدة على الأقل
2. **is_default**: يجب أن تكون جدولة واحدة فقط هي الافتراضية (is_default: true)
3. **discount_type**: يمكن أن يكون `fixed` أو `percentage` أو `null`
4. **items**: يجب إضافة منتج واحد على الأقل
5. **badges**: اختياري، يمكن إضافة شارات للسلة
6. **shop_product_variant_ids**: اختياري، لإضافة بدائل للمنتج الأساسي

## التحديث (Update)

نفس الصيغة تُستخدم للتحديث:

```
PUT /api/admin/scheduled-baskets/{id}
```

عند التحديث، سيتم حذف الجداول القديمة وإنشاء الجداول الجديدة المرسلة.
