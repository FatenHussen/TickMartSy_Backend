# Scheduled Basket - Form Data Format for Postman

## ⚠️ ملاحظة مهمة
استخدم **POST** مع `_method=PUT` للتحديث مع form-data (لأن PUT لا يدعم form-data بشكل صحيح)

---

## 📝 Create Scheduled Basket (POST)

### Method: POST
### URL: `http://127.0.0.1:8000/api/admin/scheduled-baskets`
### Body Type: form-data

| Key | Value | Type |
|-----|-------|------|
| `category_id` | `1` | Text |
| `name[en]` | `Weekly Vegetables` | Text |
| `name[ar]` | `خضروات أسبوعية` | Text |
| `discount_type` | `percentage` | Text |
| `discount` | `15` | Text |
| `delivery_price` | `25` | Text |
| `offer_ends_at` | `31-12-2026` | Text |
| `image` | [اختر ملف] | File |
| | | |
| **Schedule** | | |
| `schedule[title][en]` | `Weekly Delivery` | Text |
| `schedule[title][ar]` | `توصيل أسبوعي` | Text |
| `schedule[number_of_days]` | `7` | Text |
| `schedule[discount_type]` | `percentage` | Text |
| `schedule[discount_value]` | `5` | Text |
| `schedule[is_active]` | `1` | Text |
| | | |
| **Item 1** | | |
| `items[0][shop_product_variant_ids][0]` | `10` | Text |
| `items[0][shop_product_variant_ids][1]` | `11` | Text |
| `items[0][shop_product_variant_ids][2]` | `12` | Text |
| `items[0][quantity]` | `1` | Text |
| `items[0][is_required]` | `1` | Text |
| `items[0][is_extra]` | `0` | Text |
| `items[0][min_quantity]` | `1` | Text |
| `items[0][max_quantity]` | `5` | Text |
| | | |
| **Item 2** | | |
| `items[1][shop_product_variant_ids][0]` | `15` | Text |
| `items[1][shop_product_variant_ids][1]` | `16` | Text |
| `items[1][quantity]` | `1` | Text |
| `items[1][is_required]` | `0` | Text |
| `items[1][is_extra]` | `1` | Text |
| `items[1][min_quantity]` | `0` | Text |
| `items[1][max_quantity]` | `3` | Text |

---

## 🔄 Update Scheduled Basket (POST with _method)

### Method: POST
### URL: `http://127.0.0.1:8000/api/admin/scheduled-baskets/1`
### Body Type: form-data

| Key | Value | Type |
|-----|-------|------|
| `_method` | `PUT` | Text |
| `category_id` | `1` | Text |
| `name[en]` | `Weekly Vegetables Updated` | Text |
| `name[ar]` | `خضروات أسبوعية محدثة` | Text |
| `discount_type` | `fixed` | Text |
| `discount` | `20` | Text |
| `image` | [اختر ملف] | File |
| | | |
| **Schedule** | | |
| `schedule[number_of_days]` | `14` | Text |
| `schedule[is_active]` | `1` | Text |
| | | |
| **Item 1** | | |
| `items[0][shop_product_variant_ids][0]` | `10` | Text |
| `items[0][shop_product_variant_ids][1]` | `11` | Text |
| `items[0][quantity]` | `2` | Text |
| `items[0][is_required]` | `1` | Text |
| `items[0][is_extra]` | `0` | Text |

---

## 📸 خطوات إضافة الصورة في Postman

1. اختر **Body** → **form-data**
2. أضف key جديد: `image`
3. غير النوع من **Text** إلى **File**
4. اضغط **Select Files** واختر الصورة

---

## ⚙️ إعدادات Postman

### Headers (تلقائي):
```
Accept: application/json
Authorization: Bearer YOUR_ADMIN_TOKEN
Content-Type: multipart/form-data (تلقائي)
```

---

## 🎯 نصائح مهمة

### 1. المصفوفات في form-data:
```
items[0][shop_product_variant_ids][0] = 10
items[0][shop_product_variant_ids][1] = 11
items[0][shop_product_variant_ids][2] = 12
```

### 2. Boolean Values:
- `true` = `1`
- `false` = `0`

### 3. التواريخ:
استخدم صيغة `d-m-Y`:
```
offer_ends_at = 31-12-2026
```

### 4. الترجمات:
```
name[en] = English Name
name[ar] = الاسم بالعربي
```

---

## 📋 مثال كامل - Copy & Paste

### Create Request (form-data):

```
category_id: 1
name[en]: Weekly Vegetables
name[ar]: خضروات أسبوعية
discount_type: percentage
discount: 15
delivery_price: 25
offer_ends_at: 31-12-2026
image: [file]

schedule[title][en]: Weekly Delivery
schedule[title][ar]: توصيل أسبوعي
schedule[number_of_days]: 7
schedule[discount_type]: percentage
schedule[discount_value]: 5
schedule[is_active]: 1

items[0][shop_product_variant_ids][0]: 10
items[0][shop_product_variant_ids][1]: 11
items[0][shop_product_variant_ids][2]: 12
items[0][quantity]: 1
items[0][is_required]: 1
items[0][is_extra]: 0
items[0][min_quantity]: 1
items[0][max_quantity]: 5

items[1][shop_product_variant_ids][0]: 15
items[1][shop_product_variant_ids][1]: 16
items[1][quantity]: 1
items[1][is_required]: 0
items[1][is_extra]: 1
items[1][min_quantity]: 0
items[1][max_quantity]: 3
```

---

## 🚨 استكشاف الأخطاء

### المشكلة: البيانات فارغة
**الحل:** استخدم JSON بدلاً من form-data (إذا لم تكن بحاجة لرفع صورة)

### المشكلة: الصورة لا تُرفع
**الحل:** تأكد من:
1. النوع = **File** (مش Text)
2. الحجم أقل من 2MB
3. الصيغة: jpeg, png, jpg, gif

### المشكلة: المصفوفات لا تُرسل
**الحل:** استخدم الصيغة الصحيحة:
```
items[0][shop_product_variant_ids][0]
items[0][shop_product_variant_ids][1]
```

---

## 💡 التوصية

### للطلبات بدون صورة:
استخدم **JSON** (أسهل وأسرع):
```json
{
    "category_id": 1,
    "name": {
        "en": "Weekly Vegetables",
        "ar": "خضروات أسبوعية"
    },
    "items": [
        {
            "shop_product_variant_ids": [10, 11, 12],
            "quantity": 1,
            "is_required": true,
            "is_extra": false
        }
    ]
}
```

### للطلبات مع صورة:
استخدم **form-data** كما في الأمثلة أعلاه

---

## ✅ جاهز للاستخدام!

الآن يمكنك:
- ✅ إنشاء سلة مجدولة مع صورة
- ✅ تحديث سلة مجدولة مع صورة جديدة
- ✅ إضافة items متعددة مع shop_product_variant_ids
- ✅ تحديد is_required و is_extra لكل item
