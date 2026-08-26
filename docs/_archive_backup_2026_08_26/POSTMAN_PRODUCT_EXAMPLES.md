# أمثلة Postman لإضافة وتعديل المنتجات

## 1. إضافة منتج جديد (Create Product)

### Endpoint
```
POST {{base_url}}/api/admin/products
```

### Headers
```
Authorization: Bearer YOUR_ADMIN_TOKEN_HERE
Accept: application/json
Accept-Language: ar
```

### Body (form-data)

#### الحقول الأساسية (مطلوبة)
```
category_id: 1
price: 15000
```

#### الحقول متعددة اللغات
```
name[ar]: هاتف سامسونج جالاكسي S23
name[en]: Samsung Galaxy S23

description[ar]: هاتف ذكي بمواصفات عالية وكاميرا احترافية
description[en]: High-end smartphone with professional camera

full_description[ar]: هاتف سامسونج جالاكسي S23 يأتي بشاشة AMOLED بحجم 6.1 بوصة ومعالج Snapdragon 8 Gen 2
full_description[en]: Samsung Galaxy S23 comes with 6.1 inch AMOLED display and Snapdragon 8 Gen 2 processor

country[ar]: كوريا الجنوبية
country[en]: South Korea
```

#### الحقول الاختيارية
```
brand_id: 2
sku: SAM-S23-BLK-256
model: SM-S911B
cost_price: 12000
discount: 10
discount_type: percentage
quantity: 50
unit: قطعة
warranty_period: 24
barcode: 8801643796709
time_prepare: 00:30
is_instant_delivery: false
is_visible: true
```

#### المنتجات المقترحة
```
bought_with[0]: 5
bought_with[1]: 12
bought_with[2]: 18
```

#### الصور (اختياري)
```
media[0]: [اختر ملف صورة - product-image-1.jpg]
media[1]: [اختر ملف صورة - product-image-2.jpg]
media[2]: [اختر ملف صورة - product-image-3.jpg]
```

#### الصورة المصغرة وصورة SEO
```
thumbnail: [اختر ملف صورة - thumbnail.jpg]
seo_image: [اختر ملف صورة - seo-image.jpg]
```

#### حقول SEO
```
seo_title[ar]: شراء هاتف سامسونج جالاكسي S23 - أفضل سعر في السعودية
seo_title[en]: Buy Samsung Galaxy S23 - Best Price in Saudi Arabia

seo_description[ar]: احصل على هاتف سامسونج جالاكسي S23 بأفضل سعر مع ضمان سنتين وشحن مجاني
seo_description[en]: Get Samsung Galaxy S23 at the best price with 2 years warranty and free shipping

seo_keywords[ar][0]: سامسونج
seo_keywords[ar][1]: جالاكسي
seo_keywords[ar][2]: هاتف ذكي
seo_keywords[en][0]: samsung
seo_keywords[en][1]: galaxy
seo_keywords[en][2]: smartphone
```

#### التنويعات (Variants)
```
variants[0][attributes_values_ids][0]: 10
variants[0][attributes_values_ids][1]: 25
variants[0][images][0]: [اختر ملف صورة - variant-black-256gb-1.jpg]
variants[0][images][1]: [اختر ملف صورة - variant-black-256gb-2.jpg]

variants[1][attributes_values_ids][0]: 10
variants[1][attributes_values_ids][1]: 26
variants[1][images][0]: [اختر ملف صورة - variant-black-512gb.jpg]

variants[2][attributes_values_ids][0]: 11
variants[2][attributes_values_ids][1]: 25
variants[2][images][0]: [اختر ملف صورة - variant-white-256gb.jpg]
```

**ملاحظة**: 
- `attributes_values_ids[0]` = معرف اللون (مثلاً: 10 = أسود، 11 = أبيض)
- `attributes_values_ids[1]` = معرف السعة (مثلاً: 25 = 256GB، 26 = 512GB)

#### ربط التنويعات بالمتاجر (Shop Variants)
```
shop_variants[0][shop_id]: 1
shop_variants[0][variant_index]: 0
shop_variants[0][price]: 14500
shop_variants[0][quantity]: 20

shop_variants[1][shop_id]: 2
shop_variants[1][variant_index]: 0
shop_variants[1][price]: 14800
shop_variants[1][quantity]: 15

shop_variants[2][shop_id]: 1
shop_variants[2][variant_index]: 1
shop_variants[2][price]: 16500
shop_variants[2][quantity]: 10

shop_variants[3][shop_id]: 1
shop_variants[3][variant_index]: 2
shop_variants[3][price]: 14700
shop_variants[3][quantity]: 12
```

**ملاحظة**:
- `shop_id` = معرف المتجر (1 = فرع الرياض، 2 = فرع جدة)
- `variant_index` = رقم التنويع في مصفوفة variants (يبدأ من 0)
- `price` = السعر في هذا المتجر
- `quantity` = الكمية المتوفرة في هذا المتجر

#### تفاصيل الفئة (Category Details)
```
category_details[0][category_detail_id]: 5
category_details[0][detail_value][ar]: 6.1 بوصة
category_details[0][detail_value][en]: 6.1 inch

category_details[1][category_detail_id]: 6
category_details[1][detail_value][ar]: Snapdragon 8 Gen 2
category_details[1][detail_value][en]: Snapdragon 8 Gen 2

category_details[2][category_detail_id]: 7
category_details[2][detail_value][ar]: 3900 مللي أمبير
category_details[2][detail_value][en]: 3900 mAh

category_details[3][category_detail_id]: 8
category_details[3][detail_value][ar]: 8 جيجا رام
category_details[3][detail_value][en]: 8GB RAM
```

#### التفاصيل الإضافية (Extra Details)
```
extra_details[0][detail_key][ar]: تغليف هدايا
extra_details[0][detail_key][en]: Gift Wrapping
extra_details[0][detail_value][ar]: تغليف فاخر مع بطاقة تهنئة
extra_details[0][detail_value][en]: Premium wrapping with greeting card
extra_details[0][price]: 50

extra_details[1][detail_key][ar]: ضمان إضافي
extra_details[1][detail_key][en]: Extended Warranty
extra_details[1][detail_value][ar]: سنة إضافية
extra_details[1][detail_value][en]: One extra year
extra_details[1][price]: 500

extra_details[2][detail_key][ar]: خدمة التركيب
extra_details[2][detail_key][en]: Installation Service
extra_details[2][detail_value][ar]: تركيب وتفعيل الجهاز
extra_details[2][detail_value][en]: Device setup and activation
extra_details[2][price]: 100
```

#### الشارات (Badges)
```
badges[0][id]: 1
badges[0][position]: top

badges[1][id]: 3
badges[1][position]: bottom
```

#### الأيقونات (Icons)
```
icon_ids[0]: 2
icon_ids[1]: 5
icon_ids[2]: 8
```

---

## 2. تعديل منتج موجود (Update Product)

### Endpoint
```
POST {{base_url}}/api/admin/products/123
```

**ملاحظة**: استخدم POST مع إضافة `_method=PUT` في الـ body، أو استخدم PUT مباشرة إذا كان Postman يدعمه.

### Headers
```
Authorization: Bearer YOUR_ADMIN_TOKEN_HERE
Accept: application/json
Accept-Language: ar
```

### Body (form-data)

#### مثال 1: تعديل السعر والكمية فقط
```
price: 14000
quantity: 75
discount: 15
```

#### مثال 2: تعديل الاسم والوصف
```
name[ar]: هاتف سامسونج جالاكسي S23 - محدث
name[en]: Samsung Galaxy S23 - Updated

description[ar]: وصف محدث للمنتج
description[en]: Updated product description
```

#### مثال 3: تحديث الصورة المصغرة فقط
```
thumbnail: [اختر ملف صورة جديدة - new-thumbnail.jpg]
```
**ملاحظة**: صورة SEO ستبقى كما هي لأننا لم نرسلها

#### مثال 4: تحديث صورة SEO فقط
```
seo_image: [اختر ملف صورة جديدة - new-seo-image.jpg]
```
**ملاحظة**: الصورة المصغرة ستبقى كما هي

#### مثال 5: تحديث الصور الرئيسية
```
existing_media_ids[0]: 301
existing_media_ids[1]: 302
media[0]: [اختر ملف صورة جديدة - new-product-image.jpg]
```
**ملاحظة**: 
- سيتم الاحتفاظ بالصور ذات IDs: 301, 302
- سيتم حذف أي صورة أخرى
- سيتم إضافة الصورة الجديدة

#### مثال 6: تحديث التنويعات
```
variants[0][attributes_values_ids][0]: 10
variants[0][attributes_values_ids][1]: 25
variants[0][existing_images_ids][0]: 789
variants[0][images][0]: [اختر ملف صورة جديدة]

variants[1][attributes_values_ids][0]: 11
variants[1][attributes_values_ids][1]: 25
```
**ملاحظة**:
- التنويع الأول: سيتم الاحتفاظ بالصورة 789 وإضافة صورة جديدة
- التنويع الثاني: تنويع جديد سيتم إنشاؤه
- أي تنويع غير موجود في الـ request سيتم حذفه (Soft Delete)

#### مثال 7: تحديث ربط المتاجر
```
shop_variants[0][shop_id]: 1
shop_variants[0][variant_index]: 0
shop_variants[0][price]: 13500
shop_variants[0][quantity]: 25
```

#### مثال 8: تحديث تفاصيل الفئة
```
category_details[0][category_detail_id]: 5
category_details[0][detail_value][ar]: 6.2 بوصة
category_details[0][detail_value][en]: 6.2 inch
```

---

## 3. مثال كامل للتعديل (Update All)

### Body (form-data)
```
# الحقول الأساسية
price: 14000
quantity: 80
discount: 12

# الاسم والوصف
name[ar]: هاتف سامسونج جالاكسي S23 - إصدار محدث
name[en]: Samsung Galaxy S23 - Updated Edition

description[ar]: وصف محدث مع مميزات جديدة
description[en]: Updated description with new features

# تحديث الصورة المصغرة فقط
thumbnail: [اختر ملف صورة جديدة]
# لا نرسل seo_image، ستبقى كما هي

# تحديث الصور الرئيسية
existing_media_ids[0]: 301
existing_media_ids[1]: 302
media[0]: [اختر ملف صورة جديدة]

# تحديث التنويعات
variants[0][attributes_values_ids][0]: 10
variants[0][attributes_values_ids][1]: 25
variants[0][existing_images_ids][0]: 789
variants[0][images][0]: [اختر ملف صورة جديدة]

# تحديث ربط المتاجر
shop_variants[0][shop_id]: 1
shop_variants[0][variant_index]: 0
shop_variants[0][price]: 13500
shop_variants[0][quantity]: 30

# تحديث تفاصيل الفئة
category_details[0][category_detail_id]: 5
category_details[0][detail_value][ar]: 6.1 بوصة
category_details[0][detail_value][en]: 6.1 inch

# تحديث التفاصيل الإضافية
extra_details[0][detail_key][ar]: تغليف هدايا
extra_details[0][detail_key][en]: Gift Wrapping
extra_details[0][detail_value][ar]: تغليف فاخر
extra_details[0][detail_value][en]: Premium wrapping
extra_details[0][price]: 60

# تحديث الشارات
badges[0][id]: 1
badges[0][position]: top

# تحديث الأيقونات
icon_ids[0]: 2
icon_ids[1]: 5
```

---

## 4. مثال بسيط للإضافة (Minimal Create)

### Body (form-data)
```
# الحقول المطلوبة فقط
category_id: 1
price: 5000

name[ar]: منتج تجريبي
name[en]: Test Product

description[ar]: وصف تجريبي
description[en]: Test description

media[0]: [اختر ملف صورة]
```

---

## 5. مثال بسيط للتعديل (Minimal Update)

### Body (form-data)
```
# تحديث السعر فقط
price: 4500
```
**ملاحظة**: جميع البيانات الأخرى ستبقى كما هي

---


## 6. كيفية إعداد Postman

### الخطوة 1: إنشاء Request جديد
1. افتح Postman
2. اضغط على "New" → "HTTP Request"
3. اختر Method: `POST`
4. أدخل URL: `http://your-domain.com/api/admin/products`

### الخطوة 2: إضافة Headers
1. اذهب إلى تبويب "Headers"
2. أضف:
   - Key: `Authorization` | Value: `Bearer YOUR_TOKEN_HERE`
   - Key: `Accept` | Value: `application/json`
   - Key: `Accept-Language` | Value: `ar`

### الخطوة 3: إضافة Body
1. اذهب إلى تبويب "Body"
2. اختر `form-data`
3. أضف الحقول واحداً تلو الآخر:
   - للنصوص: اكتب Key والـ Value مباشرة
   - للصور: اختر "File" من القائمة المنسدلة بجانب Key

### الخطوة 4: إرسال الطلب
1. اضغط على "Send"
2. انتظر الـ Response

---

## 7. نصائح مهمة لـ Postman

### للحقول المتعددة (Arrays)
```
# طريقة صحيحة
media[0]: file1.jpg
media[1]: file2.jpg
media[2]: file3.jpg

# طريقة خاطئة ❌
media: file1.jpg
media: file2.jpg
```

### للحقول متعددة اللغات (Nested Arrays)
```
# طريقة صحيحة
name[ar]: النص بالعربية
name[en]: Text in English

# طريقة خاطئة ❌
name: النص بالعربية
```

### للتنويعات (Nested Arrays with Files)
```
# طريقة صحيحة
variants[0][attributes_values_ids][0]: 10
variants[0][attributes_values_ids][1]: 25
variants[0][images][0]: [File]

# طريقة خاطئة ❌
variants[0][attributes_values_ids]: [10, 25]
```

### للصور
- اختر "File" من القائمة المنسدلة بجانب Key
- اضغط "Select Files" واختر الصورة
- تأكد أن الصورة بصيغة: jpg, jpeg, png, gif, webp
- الحد الأقصى: 5MB لكل صورة

---

## 8. أمثلة Response

### Response ناجح (201 Created)
```json
{
  "success": true,
  "message": "تم إنشاء المنتج بنجاح",
  "data": {
    "id": 123,
    "name": "هاتف سامسونج جالاكسي S23",
    "price": 15000,
    "price_after_discount": 13500,
    "thumbnail": "http://your-domain.com/storage/product/uuid-123.jpg",
    "seo_image": "http://your-domain.com/storage/product/uuid-456.jpg",
    "images": [
      {
        "id": 301,
        "url": "http://your-domain.com/storage/products/product-123-1.jpg"
      }
    ],
    "variants": [...],
    "category_details": [...],
    "extra_details": [...]
  }
}
```

### Response خطأ (422 Validation Error)
```json
{
  "success": false,
  "message": "خطأ في البيانات المدخلة",
  "errors": {
    "category_id": [
      "حقل الفئة مطلوب"
    ],
    "price": [
      "حقل السعر مطلوب"
    ],
    "media": [
      "يجب رفع صورة واحدة على الأقل"
    ]
  }
}
```

### Response خطأ (401 Unauthorized)
```json
{
  "message": "Unauthenticated."
}
```
**الحل**: تأكد من صحة الـ Token في Header

---

## 9. اختبار سريع (Quick Test)

### اختبار 1: إضافة منتج بسيط
```
POST /api/admin/products

category_id: 1
price: 1000
name[ar]: منتج تجريبي
name[en]: Test Product
description[ar]: وصف
description[en]: Description
media[0]: [صورة]
```

### اختبار 2: تعديل السعر
```
POST /api/admin/products/123

price: 900
```

### اختبار 3: تحديث الصورة المصغرة
```
POST /api/admin/products/123

thumbnail: [صورة جديدة]
```

### اختبار 4: عرض المنتج
```
GET /api/admin/products/123
```

### اختبار 5: عرض قائمة المنتجات
```
GET /api/admin/products?page=1&per_page=10
```

---

## 10. استكشاف الأخطاء

### المشكلة: "category_id is required"
**الحل**: تأكد من إضافة `category_id` في الـ Body

### المشكلة: "media is required"
**الحل**: لم يعد `media` مطلوباً. إن ظهر الخطأ، تأكد أن السيرفر محدّث. إن رفعت صوراً، استخدم `media[0]`، `media[1]`، ...

### المشكلة: "The thumbnail must be an image"
**الحل**: تأكد من اختيار "File" من القائمة المنسدلة، وليس "Text"

### المشكلة: "Unauthenticated"
**الحل**: تأكد من إضافة `Authorization: Bearer TOKEN` في Headers

### المشكلة: الصور لا تُرفع
**الحل**: 
1. تأكد من اختيار "File" وليس "Text"
2. تأكد من حجم الصورة أقل من 5MB
3. تأكد من صيغة الصورة (jpg, png, jpeg, gif, webp)

### المشكلة: التنويعات لا تُحفظ
**الحل**: تأكد من الصيغة الصحيحة:
```
variants[0][attributes_values_ids][0]: 10
variants[0][attributes_values_ids][1]: 25
```

---

## 11. Postman Collection (JSON)

يمكنك استيراد هذا الـ Collection في Postman:

```json
{
  "info": {
    "name": "Admin Products API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Create Product",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{admin_token}}"
          },
          {
            "key": "Accept",
            "value": "application/json"
          },
          {
            "key": "Accept-Language",
            "value": "ar"
          }
        ],
        "body": {
          "mode": "formdata",
          "formdata": [
            {
              "key": "category_id",
              "value": "1",
              "type": "text"
            },
            {
              "key": "price",
              "value": "15000",
              "type": "text"
            },
            {
              "key": "name[ar]",
              "value": "منتج تجريبي",
              "type": "text"
            },
            {
              "key": "name[en]",
              "value": "Test Product",
              "type": "text"
            },
            {
              "key": "description[ar]",
              "value": "وصف المنتج",
              "type": "text"
            },
            {
              "key": "description[en]",
              "value": "Product description",
              "type": "text"
            },
            {
              "key": "media[0]",
              "type": "file",
              "src": ""
            },
            {
              "key": "thumbnail",
              "type": "file",
              "src": ""
            },
            {
              "key": "seo_image",
              "type": "file",
              "src": ""
            }
          ]
        },
        "url": {
          "raw": "{{base_url}}/api/admin/products",
          "host": ["{{base_url}}"],
          "path": ["api", "admin", "products"]
        }
      }
    },
    {
      "name": "Update Product",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{admin_token}}"
          },
          {
            "key": "Accept",
            "value": "application/json"
          }
        ],
        "body": {
          "mode": "formdata",
          "formdata": [
            {
              "key": "price",
              "value": "14000",
              "type": "text"
            }
          ]
        },
        "url": {
          "raw": "{{base_url}}/api/admin/products/123",
          "host": ["{{base_url}}"],
          "path": ["api", "admin", "products", "123"]
        }
      }
    },
    {
      "name": "Get Product",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{admin_token}}"
          },
          {
            "key": "Accept",
            "value": "application/json"
          }
        ],
        "url": {
          "raw": "{{base_url}}/api/admin/products/123",
          "host": ["{{base_url}}"],
          "path": ["api", "admin", "products", "123"]
        }
      }
    },
    {
      "name": "List Products",
      "request": {
        "method": "GET",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{admin_token}}"
          },
          {
            "key": "Accept",
            "value": "application/json"
          }
        ],
        "url": {
          "raw": "{{base_url}}/api/admin/products?page=1&per_page=10",
          "host": ["{{base_url}}"],
          "path": ["api", "admin", "products"],
          "query": [
            {
              "key": "page",
              "value": "1"
            },
            {
              "key": "per_page",
              "value": "10"
            }
          ]
        }
      }
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "http://your-domain.com"
    },
    {
      "key": "admin_token",
      "value": "YOUR_TOKEN_HERE"
    }
  ]
}
```

---

## 12. ملاحظات نهائية

✅ **للإضافة (Create)**:
- `category_id` مطلوب؛ `media[]` و `country_id` اختياريان
- باقي الحقول اختيارية

✅ **للتعديل (Update)**:
- جميع الحقول اختيارية
- فقط الحقول المرسلة سيتم تحديثها
- الحقول غير المرسلة تبقى كما هي

✅ **للصور**:
- `thumbnail` و `seo_image` تُحفظ في جدول `products`
- `media[]` تُحفظ في جدول `product_media`
- عند التعديل بدون إرسال صورة، تبقى الصورة القديمة

✅ **للتنويعات**:
- يمكن إضافة تنويعات متعددة
- كل تنويع يمكن أن يكون له صور خاصة
- يمكن ربط كل تنويع بمتاجر مختلفة بأسعار مختلفة

---

**تاريخ الإنشاء**: 27 مارس 2026
