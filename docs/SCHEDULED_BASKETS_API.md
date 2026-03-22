# Scheduled Baskets API - Admin Panel

## نظرة عامة

السلال المجدولة (Scheduled Baskets) هي سلال يتم توصيلها بشكل دوري حسب جدول زمني محدد (مثل: كل 3 أيام، أسبوعياً، شهرياً).

### الفرق بين السلال العادية والمجدولة:

| الميزة | السلة العادية | السلة المجدولة |
|--------|---------------|----------------|
| `is_schedule` | false | true |
| التوصيل | مرة واحدة | دوري حسب الجدول |
| الجدولة | لا يوجد | يوجد جدول توصيل |
| البدائل | لا يوجد | يمكن إضافة منتجات بديلة |
| الخصم | على السلة | على السلة + على الجدول |

---

## Base URL
```
/api/admin/scheduled-baskets
```

## Authentication
```
Authorization: Bearer {admin_token}
```

---

## 1. List Scheduled Baskets
**GET** `/api/admin/scheduled-baskets`

### Query Parameters
- `page` (int): Page number
- `per_page` (int): Items per page (default: 10)
- `search` (string): Search in basket name
- `category_id` (int): Filter by category
- `sort_field` (string): id, name, price, rating, num_sold, created_at
- `sort_order` (string): asc, desc

### Response
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "category": {
          "id": 5,
          "name": "خضروات"
        },
        "name": "سلة الخضروات الأسبوعية",
        "image": "https://example.com/storage/baskets/image.jpg",
        "num_varieties": 5,
        "original_price": 150.00,
        "discount": 10,
        "discount_type": "percentage",
        "discount_amount": 15.00,
        "final_price": 135.00,
        "rating": 4.5,
        "average_rating": 4.5,
        "num_sold": 120,
        "delivery_price": 5.00,
        "is_schedule": true,
        "has_schedule": true,
        "schedule_count": 2,
        "created_at": "2024-01-01 12:00:00",
        "updated_at": "2024-01-15 14:30:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 10,
      "total": 50
    }
  }
}
```

---

## 2. Get Single Scheduled Basket
**GET** `/api/admin/scheduled-baskets/{id}`

### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "c

    
    "delivery_price": 5.00,
    "is_schedule": true,
    
    "items": [
      {
        "id": 1,
        "product_id": 10,
        "variant_id": 15,
        "shop_product_variant_id": 20,
        "product": {
          "id": 10,
          "name": "طماطم",
          "image": "https://...",
          "brand": "برند محلي"
        },
        "variant": {
          "id": 15,
          "attributes": [
            {
              "name": "الحجم",
              "value": "كبير"
            }
          ]
        },
        "alternatives": [
          {
            "product_id": 11,
            "shop_product_variant_id": 21,
            "name": "طماطم عضوية",
            "image_url": "https://...",
            "price": 12.00
          }
        ],
        "quantity": 2,
        "unit_price": 10.00,
        "subtotal": 20.00,
        "is_required": true,
        "is_extra": false,
        "min_quantity": 1,
        "max_quantity": 5,
        "created_at": "2024-01-01 12:00:00",
        "updated_at": "2024-01-01 12:00:00"
      }
    ],
    "extras": [
      {
        "id": 2,
        "product_id": 12,
        "quantity": 1,
        "unit_price": 5.00,
        "subtotal": 5.00,
        "is_required": false,
        "is_extra": true,
        "min_quantity": 0,
        "max_quantity": 3
      }
    ],
    "schedules": [
      {
        "id": 1,
        "title": {
          "en": "Weekly Delivery",
          "ar": "توصيل أسبوعي"
        },
        "number_of_days": 7,
        "discount_type": "percentage",
        "discount_value": 5.00,
        "is_active": true,
        "is_default": true,
        "created_at": "2024-01-01 12:00:00"
      }
    ],
    "badges": [
      {
        "id": 1,
        "name": "جديد",
        "icon": "https://...",
        "position": "top"
      }
    ],
    "created_at": "2024-01-01 12:00:00",
    "updated_at": "2024-01-15 14:30:00"
  }
}
```

---

## 3. Create Scheduled Basket
**POST** `/api/admin/scheduled-baskets`

### Request Body (multipart/form-data)

#### Basic Fields
```json
{
  "category_id": 5,
  "name[en]": "Weekly Vegetables Basket",
  "name[ar]": "سلة الخضروات الأسبوعية",
  "discount": 10,
  "discount_type": "percentage",
  "delivery_price": 5,
  "image": file
}
```

#### Schedule (Default Delivery Schedule)
```json
{
  "schedule[title][en]": "Weekly Delivery",
  "schedule[title][ar]": "توصيل أسبوعي",
  "schedule[number_of_days]": 7,
  "schedule[discount_type]": "percentage",
  "schedule[discount_value]": 5,
  "schedule[is_active]": true
}
```

#### Items (Products in Basket)
```json
{
  "items[0][shop_product_variant_id]": 20,
  "items[0][shop_product_variant_ids][]": [21, 22],
  "items[0][quantity]": 2,
  "items[0][is_required]": true,
  "items[0][is_extra]": false,
  "items[0][min_quantity]": 1,
  "items[0][max_quantity]": 5,
  
  "items[1][shop_product_variant_id]": 25,
  "items[1][shop_product_variant_ids][]": [],
  "items[1][quantity]": 1,
  "items[1][is_required]": false,
  "items[1][is_extra]": true,
  "items[1][min_quantity]": 0,
  "items[1][max_quantity]": 3
}
```

#### Badges
```json
{
  "badges[0][id]": 1,
  "badges[0][position]": "top"
}
```

### Validation Rules

#### Basic Fields
- `category_id`: required|integer|exists:categories,id
- `name`: required|array
- `name.*`: required|string|max:255
- `discount`: nullable|numeric|min:0
- `discount_type`: required|in:fixed,percentage
- `image`: nullable|image|mimes:jpeg,png,jpg,gif|max:2048
- `delivery_price`: nullable|numeric|min:0

#### Schedule
- `schedule`: required|array
- `schedule.title`: nullable|array
- `schedule.title.*`: nullable|string|max:255
- `schedule.number_of_days`: required|integer|min:1
- `schedule.discount_type`: nullable|in:fixed,percentage
- `schedule.discount_value`: nullable|numeric|min:0
- `schedule.is_active`: nullable|boolean
- `schedule.is_default`: nullable|boolean


#### Items
- `items`: required|array|min:1
- `items.*.shop_product_variant_id`: required|integer|exists:shop_product_variants,id
- `items.*.shop_product_variant_ids`: nullable|array
- `items.*.shop_product_variant_ids.*`: required|integer|exists:shop_product_variants,id
- `items.*.quantity`: required|integer|min:1
- `items.*.is_required`: required|boolean
- `items.*.is_extra`: required|boolean
- `items.*.min_quantity`: nullable|integer|min:1
- `items.*.max_quantity`: nullable|integer|min:1

### Response
Same as Get Single Scheduled Basket response.

---

## 4. Update Scheduled Basket
**PUT/PATCH** `/api/admin/scheduled-baskets/{id}`

Same request body as Create, but all fields are optional.

### Response
Same as Get Single Scheduled Basket response.

---

## 5. Delete Scheduled Basket
**DELETE** `/api/admin/scheduled-baskets/{id}`

### Response
```json
{
  "success": true,
  "message": "تم حذف السلة المجدولة بنجاح"
}
```

---

## Field Descriptions

### Basic Fields

#### category_id
معرف الفئة التي تنتمي إليها السلة (مثل: خضروات، فواكه، منتجات ألبان)

#### name
اسم السلة بجميع اللغات المدعومة

#### discount & discount_type
خصم على السلة نفسها:
- `discount_type`: نوع الخصم (percentage أو fixed)
- `discount`: قيمة الخصم

#### delivery_price
سعر التوصيل للسلة

#### image
صورة السلة الرئيسية

---

### Schedule Fields

#### schedule.title
عنوان الجدول الزمني (مثل: "توصيل أسبوعي")

#### schedule.number_of_days
عدد الأيام بين كل توصيل:
- `7` = أسبوعياً
- `3` = كل 3 أيام
- `14` = كل أسبوعين
- `30` = شهرياً

#### schedule.discount_type & discount_value
خصم إضافي على الاشتراك في الجدول:
- `discount_type`: percentage أو fixed
- `discount_value`: قيمة الخصم

**ملاحظة:** هذا الخصم يضاف على خصم السلة الأساسي

#### schedule.is_active
حالة الجدول (نشط/غير نشط)

---

### Item Fields

#### shop_product_variant_id
معرف المنتج الأساسي (Primary Variant) - هذا هو المنتج الافتراضي في السلة

#### shop_product_variant_ids
قائمة معرفات المنتجات البديلة (Alternative Variants) - يمكن للمستخدم اختيار أحدها بدلاً من المنتج الأساسي

**مثال:**
```json
{
  "shop_product_variant_id": 20,  // طماطم عادية (المنتج الأساسي)
  "shop_product_variant_ids": [21, 22]  // [طماطم عضوية، طماطم كرزية] (البدائل)
}
```

#### quantity
الكمية الافتراضية للمنتج في السلة

#### is_required
هل المنتج إلزامي في السلة؟
- `true`: لا يمكن للمستخدم حذفه
- `false`: يمكن للمستخدم حذفه

#### is_extra
هل المنتج إضافي (اختياري)؟
- `true`: منتج إضافي يمكن للمستخدم إضافته
- `false`: منتج أساسي في السلة

#### min_quantity & max_quantity
الحد الأدنى والأقصى للكمية التي يمكن للمستخدم طلبها

---

## Examples

### Example 1: Weekly Vegetables Basket

```json
{
  "category_id": 5,
  "name": {
    "en": "Weekly Vegetables Basket",
    "ar": "سلة الخضروات الأسبوعية"
  },
  "discount": 10,
  "discount_type": "percentage",
  "delivery_price": 5,
  
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
      "shop_product_variant_id": 20,
      "shop_product_variant_ids": [21, 22],
      "quantity": 2,
      "is_required": true,
      "is_extra": false,
      "min_quantity": 1,
      "max_quantity": 5
    },
    {
      "shop_product_variant_id": 25,
      "shop_product_variant_ids": [],
      "quantity": 1,
      "is_required": false,
      "is_extra": true,
      "min_quantity": 0,
      "max_quantity": 3
    }
  ]
}
```

### Example 2: Every 3 Days Fruits Basket

```json
{
  "category_id": 6,
  "name": {
    "en": "Fresh Fruits Every 3 Days",
    "ar": "فواكه طازجة كل 3 أيام"
  },
  "discount": 15,
  "discount_type": "fixed",
  "delivery_price": 3,
  
  "schedule": {
    "title": {
      "en": "Every 3 Days",
      "ar": "كل 3 أيام"
    },
    "number_of_days": 3,
    "discount_type": "fixed",
    "discount_value": 10,
    "is_active": true
  },
  
  "items": [
    {
      "shop_product_variant_id": 30,
      "shop_product_variant_ids": [31],
      "quantity": 1,
      "is_required": true,
      "is_extra": false,
      "min_quantity": 1,
      "max_quantity": 3
    }
  ]
}
```

---

## Workflow

### كيفية إنشاء سلة مجدولة:

1. **اختر الفئة** (category_id)
2. **أدخل اسم السلة** بجميع اللغات
3. **حدد خصم السلة** (اختياري)
4. **أضف صورة السلة** (اختياري)
5. **حدد سعر التوصيل** (اختياري)

6. **أنشئ الجدول الزمني:**
   - عنوان الجدول
   - عدد الأيام بين كل توصيل
   - خصم إضافي على الاشتراك (اختياري)

7. **أضف المنتجات:**
   - اختر المنتج الأساسي (shop_product_variant_id)
   - أضف منتجات بديلة (shop_product_variant_ids) - اختياري
   - حدد الكمية الافتراضية
   - حدد إذا كان المنتج إلزامي أو اختياري
   - حدد إذا كان المنتج أساسي أو إضافي
   - حدد الحد الأدنى والأقصى للكمية

8. **أضف شارات (Badges)** - اختياري

---

## Price Calculation

### حساب السعر النهائي:

```
1. السعر الأساسي = مجموع (سعر المنتج × الكمية) لجميع المنتجات
2. خصم السلة = السعر الأساسي × (discount / 100) إذا كان discount_type = percentage
              أو discount إذا كان discount_type = fixed
3. السعر بعد خصم السلة = السعر الأساسي - خصم السلة
4. خصم الجدول = السعر بعد خصم السلة × (schedule.discount_value / 100) إذا كان schedule.discount_type = percentage
                أو schedule.discount_value إذا كان schedule.discount_type = fixed
5. السعر النهائي = السعر بعد خصم السلة - خصم الجدول
```

### مثال:
```
السعر الأساسي = 150 دينار
خصم السلة (10%) = 15 دينار
السعر بعد خصم السلة = 135 دينار
خصم الجدول (5%) = 6.75 دينار
السعر النهائي = 128.25 دينار
```

---

## Error Responses

### 404 Not Found
```json
{
  "success": false,
  "message": "Scheduled basket not found"
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "category_id": ["الفئة مطلوبة"],
    "items": ["يجب إضافة منتج واحد على الأقل للسلة"],
    "schedule.number_of_days": ["عدد الأيام للتوصيل مطلوب"]
  }
}
```

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated."
}
```
