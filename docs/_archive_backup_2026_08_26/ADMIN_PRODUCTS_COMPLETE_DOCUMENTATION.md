# دليل شامل لإدارة المنتجات (Products) - لوحة الإدارة (Admin Panel)

## جدول المحتويات
1. [نظرة عامة](#نظرة-عامة)
2. [بنية قاعدة البيانات](#بنية-قاعدة-البيانات)
3. [API Endpoints](#api-endpoints)
4. [إضافة منتج جديد (Create)](#إضافة-منتج-جديد-create)
5. [عرض قائمة المنتجات (Index)](#عرض-قائمة-المنتجات-index)
6. [عرض تفاصيل منتج (Show)](#عرض-تفاصيل-منتج-show)
7. [تعديل منتج (Update)](#تعديل-منتج-update)
8. [حذف منتج (Delete)](#حذف-منتج-delete)
9. [قبول/رفض المنتج (Approval)](#قبولرفض-المنتج-approval)
10. [العلاقات والجداول المرتبطة](#العلاقات-والجداول-المرتبطة)
11. [الحقول حسب الفئة (Category)](#الحقول-حسب-الفئة-category)

---

## نظرة عامة

نظام إدارة المنتجات يسمح للإدارة بإدارة كاملة للمنتجات في النظام، بما في ذلك:
- إضافة وتعديل وحذف المنتجات
- إدارة التنويعات (Variants) والأسعار حسب المتاجر
- إدارة التفاصيل الإضافية والصور
- قبول أو رفض المنتجات المضافة من قبل البائعين
- ربط المنتجات بالفئات والعلامات التجارية والأيقونات


## بنية قاعدة البيانات

### جدول `products`

| الحقل | النوع | الوصف | مصدر القيمة |
|------|------|-------|------------|
| `id` | bigint | المعرف الفريد | Auto Increment |
| `category_id` | bigint | معرف الفئة | من جدول `categories` |
| `vendor_id` | bigint | معرف البائع | من جدول `vendors` (افتراضي: 1) |
| `brand_id` | bigint | معرف العلامة التجارية | من جدول `brands` (اختياري) |
| `name` | json | اسم المنتج (متعدد اللغات) | يدخله المستخدم |
| `description` | json | وصف قصير (متعدد اللغات) | يدخله المستخدم |
| `full_description` | json | وصف كامل (متعدد اللغات) | يدخله المستخدم (اختياري) |
| `sku` | string | رمز المنتج | يدخله المستخدم (فريد، اختياري) |
| `country` | json | بلد المنشأ (متعدد اللغات) | يدخله المستخدم (اختياري) |
| `model` | string | رقم الموديل | يدخله المستخدم (فريد، اختياري) |
| `price` | integer | السعر الأساسي | يدخله المستخدم (مطلوب) |
| `cost_price` | decimal | سعر التكلفة | يدخله المستخدم (اختياري) |
| `discount` | integer | قيمة الخصم | يدخله المستخدم (0-100) |
| `discount_type` | enum | نوع الخصم | `none`, `percentage`, `fixed` |
| `quantity` | integer | الكمية المتوفرة | يدخله المستخدم |
| `unit` | string | وحدة القياس | يدخله المستخدم (مثل: كجم، قطعة) |
| `warranty_period` | integer | فترة الضمان (بالأشهر) | يدخله المستخدم (اختياري) |
| `barcode` | string | الباركود | يدخله المستخدم (اختياري) |
| `time_prepare` | time | وقت التحضير | يدخله المستخدم (اختياري) |
| `bought_with` | json | منتجات مقترحة للشراء معه | مصفوفة من IDs من جدول `products` |
| `is_instant_delivery` | boolean | توصيل فوري | يدخله المستخدم (افتراضي: false) |
| `is_visible` | boolean | ظاهر للعملاء | يدخله المستخدم (افتراضي: true) |
| `approval_status` | enum | حالة الموافقة | `pending`, `approved`, `rejected` |
| `rejection_reason` | text | سبب الرفض | يدخله الإدارة عند الرفض |
| `thumbnail` | string | صورة مصغرة | رفع ملف |
| `seo_title` | json | عنوان SEO (متعدد اللغات) | يدخله المستخدم (اختياري) |
| `seo_description` | json | وصف SEO (متعدد اللغات) | يدخله المستخدم (اختياري) |
| `seo_keywords` | json | كلمات مفتاحية SEO (متعدد اللغات) | يدخله المستخدم (اختياري) |
| `seo_image` | string | صورة SEO | رفع ملف (اختياري) |
| `deleted_at` | timestamp | تاريخ الحذف (Soft Delete) | تلقائي |
| `created_at` | timestamp | تاريخ الإنشاء | تلقائي |
| `updated_at` | timestamp | تاريخ آخر تحديث | تلقائي |


### جدول `product_variants` (تنويعات المنتج)

| الحقل | النوع | الوصف | مصدر القيمة |
|------|------|-------|------------|
| `id` | bigint | المعرف الفريد | Auto Increment |
| `product_id` | bigint | معرف المنتج | من جدول `products` |
| `attributes_values_ids` | json | مصفوفة معرفات قيم الصفات | من جدول `attribute_values` |
| `is_trend` | boolean | منتج رائج | يدخله المستخدم |
| `deleted_at` | timestamp | تاريخ الحذف | تلقائي |
| `created_at` | timestamp | تاريخ الإنشاء | تلقائي |
| `updated_at` | timestamp | تاريخ آخر تحديث | تلقائي |

**ملاحظة**: التنويعات تُستخدم لتحديد خيارات المنتج مثل (اللون، الحجم، النكهة، إلخ)

### جدول `shop_product_variants` (أسعار وكميات التنويعات حسب المتجر)

| الحقل | النوع | الوصف | مصدر القيمة |
|------|------|-------|------------|
| `id` | bigint | المعرف الفريد | Auto Increment |
| `product_variant_id` | bigint | معرف التنويع | من جدول `product_variants` |
| `shop_id` | bigint | معرف المتجر | من جدول `shops` |
| `quantity` | integer | الكمية المتوفرة في المتجر | يدخله المستخدم |
| `price` | integer | السعر في المتجر | يدخله المستخدم |
| `deleted_at` | timestamp | تاريخ الحذف | تلقائي |
| `created_at` | timestamp | تاريخ الإنشاء | تلقائي |
| `updated_at` | timestamp | تاريخ آخر تحديث | تلقائي |

### جدول `product_category_details` (تفاصيل المنتج حسب الفئة)

| الحقل | النوع | الوصف | مصدر القيمة |
|------|------|-------|------------|
| `id` | bigint | المعرف الفريد | Auto Increment |
| `product_id` | bigint | معرف المنتج | من جدول `products` |
| `category_detail_id` | bigint | معرف التفصيل | من جدول `category_details` |
| `detail_value` | json | قيمة التفصيل (متعدد اللغات) | يدخله المستخدم |
| `deleted_at` | timestamp | تاريخ الحذف | تلقائي |
| `created_at` | timestamp | تاريخ الإنشاء | تلقائي |
| `updated_at` | timestamp | تاريخ آخر تحديث | تلقائي |

**مثال**: إذا كانت الفئة "هواتف"، قد تكون التفاصيل: (حجم الشاشة، نوع المعالج، الذاكرة، إلخ)

### جدول `product_extra_details` (تفاصيل إضافية مخصصة)

| الحقل | النوع | الوصف | مصدر القيمة |
|------|------|-------|------------|
| `id` | bigint | المعرف الفريد | Auto Increment |
| `product_id` | bigint | معرف المنتج | من جدول `products` |
| `detail_key` | json | مفتاح التفصيل (متعدد اللغات) | يدخله المستخدم |
| `detail_value` | json | قيمة التفصيل (متعدد اللغات) | يدخله المستخدم |
| `price` | decimal | سعر إضافي | يدخله المستخدم (اختياري) |
| `deleted_at` | timestamp | تاريخ الحذف | تلقائي |
| `created_at` | timestamp | تاريخ الإنشاء | تلقائي |
| `updated_at` | timestamp | تاريخ آخر تحديث | تلقائي |

**مثال**: إضافات اختيارية مثل (تغليف هدايا، بطاقة تهنئة، خدمة تركيب، إلخ)



### جدول `product_media` (صور ووسائط المنتج)

| الحقل | النوع | الوصف | مصدر القيمة |
|------|------|-------|------------|
| `id` | bigint | المعرف الفريد | Auto Increment |
| `mediable_id` | bigint | معرف الكيان (منتج أو تنويع) | من `products` أو `product_variants` |
| `mediable_type` | string | نوع الكيان | `Product` أو `ProductVariant` |
| `collection` | string | نوع المجموعة | `product`, `variant_images`, `seo`, `thumbnail` |
| `file_name` | string | اسم الملف | تلقائي عند الرفع |
| `path` | string | مسار الملف | تلقائي عند الرفع |
| `url` | string | رابط الملف | تلقائي عند الرفع |
| `order` | integer | ترتيب الصورة | يدخله المستخدم |
| `created_at` | timestamp | تاريخ الإنشاء | تلقائي |
| `updated_at` | timestamp | تاريخ آخر تحديث | تلقائي |

### جدول `icon_product` (ربط الأيقونات بالمنتجات)

| الحقل | النوع | الوصف | مصدر القيمة |
|------|------|-------|------------|
| `product_id` | bigint | معرف المنتج | من جدول `products` |
| `icon_id` | bigint | معرف الأيقونة | من جدول `icons` |

**مثال**: أيقونات مثل (جديد، عرض خاص، الأكثر مبيعاً، إلخ)

### جدول `badgeable` (ربط الشارات بالمنتجات)

| الحقل | النوع | الوصف | مصدر القيمة |
|------|------|-------|------------|
| `badge_id` | bigint | معرف الشارة | من جدول `badges` |
| `badgeable_id` | bigint | معرف الكيان | من جدول `products` |
| `badgeable_type` | string | نوع الكيان | `Product` |
| `position` | enum | موضع الشارة | `top`, `bottom` |

**مثال**: شارات مثل (خصم 50%, توصيل مجاني, منتج عضوي، إلخ)

---

## API Endpoints

### Base URL
```
/api/admin/products
```

### Authentication
جميع الـ endpoints تتطلب Authentication عبر Bearer Token للإدارة (`auth:admin`)

### قائمة الـ Endpoints

| Method | Endpoint | الوصف |
|--------|----------|-------|
| GET | `/api/admin/products` | عرض قائمة المنتجات مع الفلترة والبحث |
| GET | `/api/admin/products/{id}` | عرض تفاصيل منتج محدد |
| POST | `/api/admin/products` | إضافة منتج جديد |
| PUT/PATCH | `/api/admin/products/{id}` | تعديل منتج موجود |
| DELETE | `/api/admin/products/{id}` | حذف منتج (Soft Delete) |
| POST | `/api/admin/products/{id}/approve` | قبول منتج |
| POST | `/api/admin/products/{id}/reject` | رفض منتج |


---

## إضافة منتج جديد (Create)

### Endpoint
```
POST /api/admin/products
```

### Headers
```
Authorization: Bearer {admin_token}
Content-Type: multipart/form-data
Accept: application/json
Accept-Language: ar (أو en حسب اللغة المطلوبة)
```

### Request Body (Form Data)

#### الحقول الأساسية (مطلوبة)

```json
{
  "category_id": 5,
  "price": 15000,
  "media": [
    "file1.jpg",
    "file2.jpg"
  ]
}
```

#### الحقول متعددة اللغات

```json
{
  "name": {
    "ar": "هاتف سامسونج جالاكسي S23",
    "en": "Samsung Galaxy S23"
  },
  "description": {
    "ar": "هاتف ذكي بمواصفات عالية",
    "en": "High-end smartphone"
  },
  "full_description": {
    "ar": "وصف تفصيلي كامل للمنتج...",
    "en": "Full detailed description..."
  },
  "country": {
    "ar": "كوريا الجنوبية",
    "en": "South Korea"
  }
}
```

#### الحقول الاختيارية

```json
{
  "brand_id": 3,
  "sku": "SAM-S23-BLK-256",
  "model": "SM-S911B",
  "cost_price": 12000,
  "discount": 10,
  "discount_type": "percentage",
  "quantity": 50,
  "unit": "قطعة",
  "warranty_period": 24,
  "barcode": "8801643796709",
  "time_prepare": "00:30",
  "bought_with": [12, 45, 67],
  "is_instant_delivery": false,
  "is_visible": true,
  "thumbnail": "thumbnail.jpg"
}
```

#### حقول SEO (اختيارية)

```json
{
  "seo_title": {
    "ar": "شراء هاتف سامسونج جالاكسي S23 - أفضل سعر",
    "en": "Buy Samsung Galaxy S23 - Best Price"
  },
  "seo_description": {
    "ar": "احصل على هاتف سامسونج جالاكسي S23 بأفضل سعر...",
    "en": "Get Samsung Galaxy S23 at the best price..."
  },
  "seo_keywords": {
    "ar": ["سامسونج", "جالاكسي", "هاتف ذكي"],
    "en": ["samsung", "galaxy", "smartphone"]
  },
  "seo_image": "seo-image.jpg"
}
```


#### التنويعات (Variants)

```json
{
  "variants": [
    {
      "attributes_values_ids": [10, 25],
      "images": [
        "variant1-img1.jpg",
        "variant1-img2.jpg"
      ]
    },
    {
      "attributes_values_ids": [10, 26],
      "images": [
        "variant2-img1.jpg"
      ]
    }
  ]
}
```

**شرح**:
- `attributes_values_ids`: مصفوفة من IDs قيم الصفات من جدول `attribute_values`
- مثال: [10 = "أسود", 25 = "256 جيجا"]
- يتم جلب قيم الصفات من API الفئات: `/api/admin/category-attributes`

#### ربط التنويعات بالمتاجر (Shop Variants)

```json
{
  "shop_variants": [
    {
      "shop_id": 1,
      "variant_index": 0,
      "price": 14500,
      "quantity": 20
    },
    {
      "shop_id": 2,
      "variant_index": 0,
      "price": 14800,
      "quantity": 15
    },
    {
      "shop_id": 1,
      "variant_index": 1,
      "price": 15500,
      "quantity": 10
    }
  ]
}
```

**شرح**:
- `shop_id`: معرف المتجر من جدول `shops`
- `variant_index`: رقم التنويع في مصفوفة `variants` (يبدأ من 0)
- `price`: السعر في هذا المتجر لهذا التنويع
- `quantity`: الكمية المتوفرة في هذا المتجر

#### تفاصيل الفئة (Category Details)

```json
{
  "category_details": [
    {
      "category_detail_id": 5,
      "detail_value": {
        "ar": "6.1 بوصة",
        "en": "6.1 inch"
      }
    },
    {
      "category_detail_id": 6,
      "detail_value": {
        "ar": "Snapdragon 8 Gen 2",
        "en": "Snapdragon 8 Gen 2"
      }
    }
  ]
}
```

**شرح**:
- `category_detail_id`: معرف التفصيل من جدول `category_details`
- يتم جلب تفاصيل الفئة من: `/api/admin/category-details?category_id={category_id}`
- كل فئة لها تفاصيل خاصة بها (مثل: حجم الشاشة للهواتف، الوزن للمواد الغذائية، إلخ)

#### التفاصيل الإضافية (Extra Details)

```json
{
  "extra_details": [
    {
      "detail_key": {
        "ar": "تغليف هدايا",
        "en": "Gift Wrapping"
      },
      "detail_value": {
        "ar": "تغليف فاخر مع بطاقة",
        "en": "Premium wrapping with card"
      },
      "price": 50
    },
    {
      "detail_key": {
        "ar": "ضمان إضافي",
        "en": "Extended Warranty"
      },
      "detail_value": {
        "ar": "سنة إضافية",
        "en": "One extra year"
      },
      "price": 500
    }
  ]
}
```

**شرح**:
- تفاصيل مخصصة يمكن إضافتها لأي منتج
- يمكن أن يكون لها سعر إضافي
- تظهر للعميل كخيارات إضافية عند الشراء


#### الشارات (Badges)

```json
{
  "badges": [
    {
      "id": 1,
      "position": "top"
    },
    {
      "id": 3,
      "position": "bottom"
    }
  ]
}
```

**شرح**:
- `id`: معرف الشارة من جدول `badges`
- `position`: موضع الشارة على صورة المنتج (`top` أو `bottom`)
- يتم جلب الشارات من: `/api/admin/badges`

#### الأيقونات (Icons)

```json
{
  "icon_ids": [2, 5, 8]
}
```

**شرح**:
- مصفوفة من معرفات الأيقونات من جدول `icons`
- يتم جلب الأيقونات من: `/api/admin/icons`
- مثال: أيقونة "جديد"، "الأكثر مبيعاً"، "عرض خاص"

### مثال كامل للـ Request

```javascript
const formData = new FormData();

// الحقول الأساسية
formData.append('category_id', 5);
formData.append('price', 15000);
formData.append('brand_id', 3);

// الحقول متعددة اللغات
formData.append('name[ar]', 'هاتف سامسونج جالاكسي S23');
formData.append('name[en]', 'Samsung Galaxy S23');
formData.append('description[ar]', 'هاتف ذكي بمواصفات عالية');
formData.append('description[en]', 'High-end smartphone');

// الصور الرئيسية (مطلوبة)
formData.append('media[]', file1); // File object
formData.append('media[]', file2);

// التنويعات
formData.append('variants[0][attributes_values_ids][]', 10);
formData.append('variants[0][attributes_values_ids][]', 25);
formData.append('variants[0][images][]', variantFile1);

formData.append('variants[1][attributes_values_ids][]', 10);
formData.append('variants[1][attributes_values_ids][]', 26);

// ربط التنويعات بالمتاجر
formData.append('shop_variants[0][shop_id]', 1);
formData.append('shop_variants[0][variant_index]', 0);
formData.append('shop_variants[0][price]', 14500);
formData.append('shop_variants[0][quantity]', 20);

// تفاصيل الفئة
formData.append('category_details[0][category_detail_id]', 5);
formData.append('category_details[0][detail_value][ar]', '6.1 بوصة');
formData.append('category_details[0][detail_value][en]', '6.1 inch');

// التفاصيل الإضافية
formData.append('extra_details[0][detail_key][ar]', 'تغليف هدايا');
formData.append('extra_details[0][detail_key][en]', 'Gift Wrapping');
formData.append('extra_details[0][detail_value][ar]', 'تغليف فاخر');
formData.append('extra_details[0][detail_value][en]', 'Premium wrapping');
formData.append('extra_details[0][price]', 50);

// الشارات
formData.append('badges[0][id]', 1);
formData.append('badges[0][position]', 'top');

// الأيقونات
formData.append('icon_ids[]', 2);
formData.append('icon_ids[]', 5);

// SEO
formData.append('seo_title[ar]', 'شراء هاتف سامسونج جالاكسي S23');
formData.append('seo_title[en]', 'Buy Samsung Galaxy S23');
formData.append('seo_image', seoImageFile);

// إرسال الطلب
fetch('/api/admin/products', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json',
    'Accept-Language': 'ar'
  },
  body: formData
});
```


### Response (Success - 201 Created)

```json
{
  "success": true,
  "message": "تم إنشاء المنتج بنجاح",
  "data": {
    "id": 123,
    "name": {
      "ar": "هاتف سامسونج جالاكسي S23",
      "en": "Samsung Galaxy S23"
    },
    "description": {
      "ar": "هاتف ذكي بمواصفات عالية",
      "en": "High-end smartphone"
    },
    "full_description": {
      "ar": "وصف تفصيلي...",
      "en": "Full description..."
    },
    "country": {
      "ar": "كوريا الجنوبية",
      "en": "South Korea"
    },
    "price": 15000,
    "cost_price": 12000,
    "price_after_discount": 13500,
    "discount": 10,
    "discount_type": "percentage",
    "quantity": 50,
    "unit": "قطعة",
    "warranty_period": 24,
    "is_visible": true,
    "sku": "SAM-S23-BLK-256",
    "model": "SM-S911B",
    "barcode": "8801643796709",
    "time_prepare": "00:30",
    "bought_with": [
      {
        "id": 12,
        "name": "سماعات لاسلكية"
      },
      {
        "id": 45,
        "name": "حافظة واقية"
      }
    ],
    "is_instant_delivery": false,
    "thumbnail": "https://example.com/storage/thumbnails/product-123.jpg",
    "category": {
      "id": 5,
      "name": "هواتف ذكية"
    },
    "brand": {
      "id": 3,
      "name": "سامسونج"
    },
    "vendor": {
      "id": 1,
      "name": "متجر الإلكترونيات"
    },
    "approval_status": "pending",
    "approval_status_label": "قيد الانتظار",
    "rejection_reason": null,
    "variants": [
      {
        "id": 456,
        "attributes": [
          {
            "attribute": "اللون",
            "value": "أسود",
            "type": "color"
          },
          {
            "attribute": "السعة",
            "value": "256 جيجا",
            "type": "text"
          }
        ],
        "shops": [
          {
            "shop_id": 1,
            "shop_name": "فرع الرياض",
            "price": 14500,
            "quantity": 20
          },
          {
            "shop_id": 2,
            "shop_name": "فرع جدة",
            "price": 14800,
            "quantity": 15
          }
        ],
        "images": [
          {
            "id": 789,
            "url": "https://example.com/storage/variants/variant-456-1.jpg"
          },
          {
            "id": 790,
            "url": "https://example.com/storage/variants/variant-456-2.jpg"
          }
        ]
      }
    ],
    "category_details": [
      {
        "id": 101,
        "name": "حجم الشاشة",
        "value": {
          "ar": "6.1 بوصة",
          "en": "6.1 inch"
        }
      },
      {
        "id": 102,
        "name": "المعالج",
        "value": {
          "ar": "Snapdragon 8 Gen 2",
          "en": "Snapdragon 8 Gen 2"
        }
      }
    ],
    "extra_details": [
      {
        "id": 201,
        "key": {
          "ar": "تغليف هدايا",
          "en": "Gift Wrapping"
        },
        "value": {
          "ar": "تغليف فاخر مع بطاقة",
          "en": "Premium wrapping with card"
        },
        "price": 50
      }
    ],
    "images": [
      {
        "id": 301,
        "url": "https://example.com/storage/products/product-123-1.jpg"
      },
      {
        "id": 302,
        "url": "https://example.com/storage/products/product-123-2.jpg"
      }
    ],
    "seo_title": {
      "ar": "شراء هاتف سامسونج جالاكسي S23 - أفضل سعر",
      "en": "Buy Samsung Galaxy S23 - Best Price"
    },
    "seo_description": {
      "ar": "احصل على هاتف سامسونج جالاكسي S23...",
      "en": "Get Samsung Galaxy S23..."
    },
    "seo_keywords": {
      "ar": ["سامسونج", "جالاكسي", "هاتف ذكي"],
      "en": ["samsung", "galaxy", "smartphone"]
    },
    "seo_image": "https://example.com/storage/seo/product-123-seo.jpg",
    "badges": [
      {
        "id": 1,
        "name": "جديد",
        "icon": "https://example.com/storage/badges/new.png",
        "position": "top"
      }
    ],
    "icons": [
      {
        "id": 2,
        "name": "الأكثر مبيعاً",
        "icon": "https://example.com/storage/icons/bestseller.png"
      }
    ],
    "rating": 0,
    "rating_count": 0,
    "created_at": "2026-03-27T10:30:00.000000Z",
    "updated_at": "2026-03-27T10:30:00.000000Z"
  }
}
```


### Response (Error - 422 Validation Error)

```json
{
  "success": false,
  "message": "خطأ في البيانات المدخلة",
  "errors": {
    "category_id": [
      "حقل الفئة مطلوب"
    ],
    "price": [
      "حقل السعر مطلوب",
      "يجب أن يكون السعر رقماً صحيحاً"
    ],
    "media": [
      "يجب رفع صورة واحدة على الأقل"
    ],
    "variants.0.attributes_values_ids.0": [
      "قيمة الصفة غير موجودة"
    ]
  }
}
```

---

## عرض قائمة المنتجات (Index)

### Endpoint
```
GET /api/admin/products
```

### Headers
```
Authorization: Bearer {admin_token}
Accept: application/json
Accept-Language: ar
```

### Query Parameters

| Parameter | Type | الوصف | مثال |
|-----------|------|-------|------|
| `page` | integer | رقم الصفحة | `?page=2` |
| `per_page` | integer | عدد العناصر في الصفحة | `?per_page=20` |
| `shop_id` | integer | فلترة حسب المتجر | `?shop_id=5` |
| `category_id` | integer | فلترة حسب الفئة | `?category_id=3` |
| `brand_id` | integer | فلترة حسب العلامة التجارية | `?brand_id=7` |
| `vendor_id` | integer | فلترة حسب البائع | `?vendor_id=2` |
| `approval_status` | string | فلترة حسب حالة الموافقة | `?approval_status=pending` |
| `is_visible` | boolean | فلترة حسب الظهور | `?is_visible=1` |
| `search` | string | البحث في الحقول | `?search=سامسونج` |
| `sortField` | string | حقل الترتيب | `?sortField=price` |
| `sortOrder` | string | اتجاه الترتيب | `?sortOrder=desc` |

### مثال Request

```bash
GET /api/admin/products?page=1&per_page=20&category_id=5&approval_status=approved&search=سامسونج&sortField=price&sortOrder=asc
```

### Response (Success - 200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "category_id": "هواتف ذكية",
      "brand_id": "سامسونج",
      "name": "هاتف سامسونج جالاكسي S23",
      "description": "هاتف ذكي بمواصفات عالية",
      "full_description": "وصف تفصيلي...",
      "country": "كوريا الجنوبية",
      "sku": "SAM-S23-BLK-256",
      "model": "SM-S911B",
      "price": 15000,
      "cost_price": 12000,
      "price_after_discount": 13500,
      "discount": 10,
      "discount_type": "percentage",
      "quantity": 50,
      "unit": "قطعة",
      "warranty_period": 24,
      "is_visible": true,
      "barcode": "8801643796709",
      "time_prepare": "00:30:00",
      "bought_with": [
        {
          "id": 12,
          "name": "سماعات لاسلكية"
        }
      ],
      "is_instant_delivery": false,
      "thumbnail": "https://example.com/storage/thumbnails/product-123.jpg",
      "vendor": {
        "id": 1,
        "name": "متجر الإلكترونيات"
      },
      "approval_status": "approved",
      "approval_status_label": "مقبول",
      "image": "https://example.com/storage/products/product-123-1.jpg",
      "images": [
        "https://example.com/storage/products/product-123-1.jpg",
        "https://example.com/storage/products/product-123-2.jpg"
      ],
      "created_at": "2026-03-27T10:30:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 20,
    "to": 20,
    "total": 95
  },
  "links": {
    "first": "https://example.com/api/admin/products?page=1",
    "last": "https://example.com/api/admin/products?page=5",
    "prev": null,
    "next": "https://example.com/api/admin/products?page=2"
  }
}
```


---

## عرض تفاصيل منتج (Show)

### Endpoint
```
GET /api/admin/products/{id}
```

### Headers
```
Authorization: Bearer {admin_token}
Accept: application/json
Accept-Language: ar
```

### مثال Request

```bash
GET /api/admin/products/123
```

### Response (Success - 200 OK)

الـ Response هو نفسه الموضح في قسم "إضافة منتج جديد" أعلاه، يحتوي على جميع التفاصيل الكاملة للمنتج.

### Response (Error - 404 Not Found)

```json
{
  "success": false,
  "message": "المنتج غير موجود"
}
```

---

## تعديل منتج (Update)

### Endpoint
```
PUT /api/admin/products/{id}
أو
PATCH /api/admin/products/{id}
```

### Headers
```
Authorization: Bearer {admin_token}
Content-Type: multipart/form-data
Accept: application/json
Accept-Language: ar
```

### Request Body

جميع الحقول اختيارية في التعديل، يمكنك إرسال فقط الحقول التي تريد تعديلها.

#### الفرق الرئيسي عن الإضافة:

1. **الصور الموجودة (existing_media_ids)**:
```json
{
  "existing_media_ids": [301, 302],
  "media": ["new-image.jpg"]
}
```
- `existing_media_ids`: مصفوفة من IDs الصور التي تريد الاحتفاظ بها
- أي صورة غير موجودة في `existing_media_ids` سيتم حذفها
- `media`: صور جديدة تريد إضافتها

2. **التنويعات الموجودة**:
```json
{
  "variants": [
    {
      "id": 456,
      "attributes_values_ids": [10, 25],
      "existing_images_ids": [789],
      "images": ["new-variant-image.jpg"]
    }
  ]
}
```
- إذا كان التنويع موجوداً (نفس `attributes_values_ids`)، سيتم تحديثه
- إذا كان التنويع جديداً، سيتم إنشاؤه
- أي تنويع غير موجود في الـ request سيتم حذفه (Soft Delete)

3. **تفاصيل الفئة والتفاصيل الإضافية**:
```json
{
  "category_details": [
    {
      "id": 101,
      "category_detail_id": 5,
      "detail_value": {
        "ar": "6.1 بوصة",
        "en": "6.1 inch"
      }
    }
  ],
  "extra_details": [
    {
      "id": 201,
      "detail_key": {
        "ar": "تغليف هدايا",
        "en": "Gift Wrapping"
      },
      "detail_value": {
        "ar": "تغليف فاخر",
        "en": "Premium wrapping"
      },
      "price": 50
    }
  ]
}
```
- إذا كان `id` موجوداً، سيتم التحديث
- إذا لم يكن `id` موجوداً، سيتم الإنشاء
- أي عنصر غير موجود في الـ request سيتم حذفه



### مثال كامل للتعديل

```javascript
const formData = new FormData();

// تعديل الحقول الأساسية
formData.append('price', 14000);
formData.append('discount', 15);
formData.append('quantity', 60);

// تعديل الحقول متعددة اللغات
formData.append('name[ar]', 'هاتف سامسونج جالاكسي S23 - محدث');
formData.append('description[ar]', 'وصف محدث');

// الاحتفاظ بالصور الموجودة وإضافة صورة جديدة
formData.append('existing_media_ids[]', 301);
formData.append('existing_media_ids[]', 302);
formData.append('media[]', newImageFile);

// تعديل التنويعات
formData.append('variants[0][attributes_values_ids][]', 10);
formData.append('variants[0][attributes_values_ids][]', 25);
formData.append('variants[0][existing_images_ids][]', 789);
formData.append('variants[0][images][]', newVariantImage);

// تعديل ربط المتاجر
formData.append('shop_variants[0][shop_id]', 1);
formData.append('shop_variants[0][variant_index]', 0);
formData.append('shop_variants[0][price]', 13500);
formData.append('shop_variants[0][quantity]', 25);

// إرسال الطلب
fetch('/api/admin/products/123', {
  method: 'POST', // استخدم POST مع _method=PUT
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json',
    'Accept-Language': 'ar'
  },
  body: formData
});

// أو استخدم PUT مباشرة إذا كان الـ client يدعمه
```

### Response (Success - 200 OK)

نفس الـ Response الخاص بالإضافة، مع البيانات المحدثة.

### Response (Error - 404 Not Found)

```json
{
  "success": false,
  "message": "المنتج غير موجود"
}
```

### Response (Error - 422 Validation Error)

```json
{
  "success": false,
  "message": "خطأ في البيانات المدخلة",
  "errors": {
    "price": [
      "يجب أن يكون السعر رقماً صحيحاً"
    ]
  }
}
```

---

## حذف منتج (Delete)

### Endpoint
```
DELETE /api/admin/products/{id}
```

### Headers
```
Authorization: Bearer {admin_token}
Accept: application/json
Accept-Language: ar
```

### مثال Request

```bash
DELETE /api/admin/products/123
```

### Response (Success - 200 OK)

```json
{
  "success": true,
  "message": "تم حذف المنتج بنجاح"
}
```

### Response (Error - 404 Not Found)

```json
{
  "success": false,
  "message": "المنتج غير موجود"
}
```

### ملاحظات مهمة:
- الحذف هو Soft Delete (لا يتم حذف البيانات فعلياً)
- يتم تعيين `deleted_at` في قاعدة البيانات
- عند حذف المنتج، يتم حذف التنويعات المرتبطة به تلقائياً (Cascade Soft Delete)
- يتم حذف `shop_product_variants` المرتبطة بالتنويعات أيضاً


---

## قبول/رفض المنتج (Approval)

### نظرة عامة
عندما يضيف البائع (Vendor) منتجاً جديداً، يكون في حالة `pending` (قيد الانتظار). الإدارة يمكنها قبول أو رفض المنتج.

### حالات الموافقة (Approval Status)
- `pending`: قيد الانتظار (الحالة الافتراضية)
- `approved`: مقبول (يظهر للعملاء)
- `rejected`: مرفوض (لا يظهر للعملاء)

---

### قبول المنتج (Approve)

#### Endpoint
```
POST /api/admin/products/{id}/approve
```

#### Headers
```
Authorization: Bearer {admin_token}
Accept: application/json
Accept-Language: ar
```

#### مثال Request

```bash
POST /api/admin/products/123/approve
```

#### Response (Success - 200 OK)

```json
{
  "success": true,
  "message": "تم قبول المنتج بنجاح",
  "data": {
    "id": 123,
    "name": "هاتف سامسونج جالاكسي S23",
    "approval_status": "approved",
    "approval_status_label": "مقبول",
    "rejection_reason": null,
    ...
  }
}
```

#### Response (Error - 400 Bad Request)

```json
{
  "success": false,
  "message": "يمكن قبول المنتجات في حالة الانتظار فقط"
}
```

#### ملاحظات:
- يتم إرسال إشعار FCM للبائع عند قبول المنتج
- يتم تسجيل الإشعار في جدول `vendor_notifications`
- المنتج يصبح ظاهراً للعملاء بعد القبول (إذا كان `is_visible = true`)

---

### رفض المنتج (Reject)

#### Endpoint
```
POST /api/admin/products/{id}/reject
```

#### Headers
```
Authorization: Bearer {admin_token}
Content-Type: application/json
Accept: application/json
Accept-Language: ar
```

#### Request Body

```json
{
  "rejection_reason": "المنتج لا يتوافق مع سياسات المتجر. يرجى التأكد من جودة الصور ودقة المواصفات."
}
```

#### مثال Request

```bash
POST /api/admin/products/123/reject
Content-Type: application/json

{
  "rejection_reason": "المنتج لا يتوافق مع سياسات المتجر"
}
```

#### Response (Success - 200 OK)

```json
{
  "success": true,
  "message": "تم رفض المنتج",
  "data": {
    "id": 123,
    "name": "هاتف سامسونج جالاكسي S23",
    "approval_status": "rejected",
    "approval_status_label": "مرفوض",
    "rejection_reason": "المنتج لا يتوافق مع سياسات المتجر",
    ...
  }
}
```

#### Response (Error - 422 Validation Error)

```json
{
  "success": false,
  "message": "خطأ في البيانات المدخلة",
  "errors": {
    "rejection_reason": [
      "يجب إدخال سبب الرفض"
    ]
  }
}
```

#### Response (Error - 400 Bad Request)

```json
{
  "success": false,
  "message": "يمكن رفض المنتجات في حالة الانتظار فقط"
}
```

#### ملاحظات:
- سبب الرفض مطلوب (required)
- يتم إرسال إشعار FCM للبائع مع سبب الرفض
- المنتج لا يظهر للعملاء بعد الرفض
- البائع يمكنه تعديل المنتج وإعادة تقديمه للمراجعة


---

## العلاقات والجداول المرتبطة

### 1. الفئات (Categories)

#### جلب الفئات
```
GET /api/admin/categories
```

#### Response
```json
{
  "data": [
    {
      "id": 5,
      "name": "هواتف ذكية",
      "icon": "https://example.com/storage/categories/phones.png",
      "parent_id": 1,
      "order": 1,
      "is_active": true
    }
  ]
}
```

#### استخدام في المنتج
```json
{
  "category_id": 5
}
```

---

### 2. العلامات التجارية (Brands)

#### جلب العلامات التجارية
```
GET /api/admin/brands
```

#### Response
```json
{
  "data": [
    {
      "id": 3,
      "name": "سامسونج",
      "logo": "https://example.com/storage/brands/samsung.png"
    }
  ]
}
```

#### استخدام في المنتج
```json
{
  "brand_id": 3
}
```

---

### 3. صفات الفئة (Category Attributes)

#### جلب صفات فئة معينة
```
GET /api/admin/category-attributes?category_id=5
```

#### Response
```json
{
  "data": [
    {
      "id": 10,
      "category_id": 5,
      "name": "اللون",
      "type": "color",
      "values": [
        {
          "id": 25,
          "name": "أسود",
          "value": "#000000"
        },
        {
          "id": 26,
          "name": "أبيض",
          "value": "#FFFFFF"
        }
      ]
    },
    {
      "id": 11,
      "category_id": 5,
      "name": "السعة",
      "type": "text",
      "values": [
        {
          "id": 30,
          "name": "128 جيجا"
        },
        {
          "id": 31,
          "name": "256 جيجا"
        }
      ]
    }
  ]
}
```

#### استخدام في التنويعات
```json
{
  "variants": [
    {
      "attributes_values_ids": [25, 30]
    }
  ]
}
```

**شرح**: التنويع الأول = أسود + 128 جيجا

---

### 4. تفاصيل الفئة (Category Details)

#### جلب تفاصيل فئة معينة
```
GET /api/admin/category-details?category_id=5
```

#### Response
```json
{
  "data": [
    {
      "id": 5,
      "category_id": 5,
      "name": "حجم الشاشة",
      "type": "text",
      "is_required": true
    },
    {
      "id": 6,
      "category_id": 5,
      "name": "نوع المعالج",
      "type": "text",
      "is_required": true
    },
    {
      "id": 7,
      "category_id": 5,
      "name": "حجم البطارية",
      "type": "number",
      "is_required": false
    }
  ]
}
```

#### استخدام في المنتج
```json
{
  "category_details": [
    {
      "category_detail_id": 5,
      "detail_value": {
        "ar": "6.1 بوصة",
        "en": "6.1 inch"
      }
    },
    {
      "category_detail_id": 6,
      "detail_value": {
        "ar": "Snapdragon 8 Gen 2",
        "en": "Snapdragon 8 Gen 2"
      }
    }
  ]
}
```


---

### 5. المتاجر (Shops)

#### جلب المتاجر
```
GET /api/admin/shops
```

#### Response
```json
{
  "data": [
    {
      "id": 1,
      "name": "فرع الرياض",
      "address": "شارع الملك فهد، الرياض",
      "is_active": true
    },
    {
      "id": 2,
      "name": "فرع جدة",
      "address": "شارع التحلية، جدة",
      "is_active": true
    }
  ]
}
```

#### استخدام في ربط التنويعات بالمتاجر
```json
{
  "shop_variants": [
    {
      "shop_id": 1,
      "variant_index": 0,
      "price": 14500,
      "quantity": 20
    }
  ]
}
```

---

### 6. الشارات (Badges)

#### جلب الشارات
```
GET /api/admin/badges
```

#### Response
```json
{
  "data": [
    {
      "id": 1,
      "name": "جديد",
      "icon": "https://example.com/storage/badges/new.png",
      "color": "#FF5722"
    },
    {
      "id": 2,
      "name": "خصم 50%",
      "icon": "https://example.com/storage/badges/discount.png",
      "color": "#4CAF50"
    }
  ]
}
```

#### استخدام في المنتج
```json
{
  "badges": [
    {
      "id": 1,
      "position": "top"
    },
    {
      "id": 2,
      "position": "bottom"
    }
  ]
}
```

---

### 7. الأيقونات (Icons)

#### جلب الأيقونات
```
GET /api/admin/icons
```

#### Response
```json
{
  "data": [
    {
      "id": 2,
      "name": "الأكثر مبيعاً",
      "icon": "https://example.com/storage/icons/bestseller.png"
    },
    {
      "id": 5,
      "name": "عرض خاص",
      "icon": "https://example.com/storage/icons/special.png"
    }
  ]
}
```

#### استخدام في المنتج
```json
{
  "icon_ids": [2, 5, 8]
}
```

---

### 8. المنتجات المقترحة (Bought With)

#### جلب المنتجات للاقتراح
```
GET /api/admin/products?category_id=5
```

#### استخدام في المنتج
```json
{
  "bought_with": [12, 45, 67]
}
```

**شرح**: عند عرض المنتج للعميل، سيتم اقتراح المنتجات ذات IDs: 12, 45, 67 للشراء معه.

---

### 9. البائعون (Vendors)

#### جلب البائعين
```
GET /api/admin/vendors
```

#### Response
```json
{
  "data": [
    {
      "id": 1,
      "name": "متجر الإلكترونيات",
      "email": "electronics@example.com",
      "phone": "+966501234567"
    }
  ]
}
```

#### ملاحظة
- `vendor_id` يتم تعيينه تلقائياً من المستخدم المسجل دخوله
- في حالة الإدارة، يتم تعيينه افتراضياً إلى 1


---

## الحقول حسب الفئة (Category)

### كيفية تحديد الحقول المطلوبة حسب الفئة

عند اختيار فئة معينة، يجب جلب:

1. **صفات الفئة (Category Attributes)** - للتنويعات
2. **تفاصيل الفئة (Category Details)** - للمواصفات

### مثال عملي: إضافة منتج في فئة "هواتف ذكية"

#### الخطوة 1: اختيار الفئة
```json
{
  "category_id": 5
}
```

#### الخطوة 2: جلب صفات الفئة
```
GET /api/admin/category-attributes?category_id=5
```

**Response**:
```json
{
  "data": [
    {
      "id": 10,
      "name": "اللون",
      "type": "color",
      "values": [
        {"id": 25, "name": "أسود", "value": "#000000"},
        {"id": 26, "name": "أبيض", "value": "#FFFFFF"},
        {"id": 27, "name": "أزرق", "value": "#0000FF"}
      ]
    },
    {
      "id": 11,
      "name": "السعة",
      "type": "text",
      "values": [
        {"id": 30, "name": "128 جيجا"},
        {"id": 31, "name": "256 جيجا"},
        {"id": 32, "name": "512 جيجا"}
      ]
    }
  ]
}
```

#### الخطوة 3: إنشاء التنويعات
```json
{
  "variants": [
    {
      "attributes_values_ids": [25, 30]
    },
    {
      "attributes_values_ids": [25, 31]
    },
    {
      "attributes_values_ids": [26, 30]
    },
    {
      "attributes_values_ids": [26, 31]
    }
  ]
}
```

**النتيجة**:
- تنويع 1: أسود + 128 جيجا
- تنويع 2: أسود + 256 جيجا
- تنويع 3: أبيض + 128 جيجا
- تنويع 4: أبيض + 256 جيجا

#### الخطوة 4: جلب تفاصيل الفئة
```
GET /api/admin/category-details?category_id=5
```

**Response**:
```json
{
  "data": [
    {
      "id": 5,
      "name": "حجم الشاشة",
      "type": "text",
      "is_required": true
    },
    {
      "id": 6,
      "name": "نوع المعالج",
      "type": "text",
      "is_required": true
    },
    {
      "id": 7,
      "name": "حجم البطارية",
      "type": "number",
      "is_required": false
    },
    {
      "id": 8,
      "name": "نوع الشاشة",
      "type": "text",
      "is_required": false
    }
  ]
}
```

#### الخطوة 5: ملء تفاصيل الفئة
```json
{
  "category_details": [
    {
      "category_detail_id": 5,
      "detail_value": {
        "ar": "6.1 بوصة",
        "en": "6.1 inch"
      }
    },
    {
      "category_detail_id": 6,
      "detail_value": {
        "ar": "Snapdragon 8 Gen 2",
        "en": "Snapdragon 8 Gen 2"
      }
    },
    {
      "category_detail_id": 7,
      "detail_value": {
        "ar": "3900 مللي أمبير",
        "en": "3900 mAh"
      }
    },
    {
      "category_detail_id": 8,
      "detail_value": {
        "ar": "AMOLED",
        "en": "AMOLED"
      }
    }
  ]
}
```


---

### مثال آخر: إضافة منتج في فئة "مواد غذائية"

#### الخطوة 1: اختيار الفئة
```json
{
  "category_id": 8
}
```

#### الخطوة 2: جلب صفات الفئة
```
GET /api/admin/category-attributes?category_id=8
```

**Response**:
```json
{
  "data": [
    {
      "id": 20,
      "name": "الحجم",
      "type": "text",
      "values": [
        {"id": 50, "name": "صغير"},
        {"id": 51, "name": "متوسط"},
        {"id": 52, "name": "كبير"}
      ]
    },
    {
      "id": 21,
      "name": "النكهة",
      "type": "text",
      "values": [
        {"id": 60, "name": "فانيليا"},
        {"id": 61, "name": "شوكولاتة"},
        {"id": 62, "name": "فراولة"}
      ]
    }
  ]
}
```

#### الخطوة 3: إنشاء التنويعات
```json
{
  "variants": [
    {
      "attributes_values_ids": [50, 60]
    },
    {
      "attributes_values_ids": [51, 61]
    },
    {
      "attributes_values_ids": [52, 62]
    }
  ]
}
```

**النتيجة**:
- تنويع 1: صغير + فانيليا
- تنويع 2: متوسط + شوكولاتة
- تنويع 3: كبير + فراولة

#### الخطوة 4: جلب تفاصيل الفئة
```
GET /api/admin/category-details?category_id=8
```

**Response**:
```json
{
  "data": [
    {
      "id": 15,
      "name": "الوزن",
      "type": "number",
      "is_required": true
    },
    {
      "id": 16,
      "name": "تاريخ الإنتاج",
      "type": "date",
      "is_required": true
    },
    {
      "id": 17,
      "name": "تاريخ الانتهاء",
      "type": "date",
      "is_required": true
    },
    {
      "id": 18,
      "name": "بلد المنشأ",
      "type": "text",
      "is_required": false
    }
  ]
}
```

#### الخطوة 5: ملء تفاصيل الفئة
```json
{
  "category_details": [
    {
      "category_detail_id": 15,
      "detail_value": {
        "ar": "500 جرام",
        "en": "500g"
      }
    },
    {
      "category_detail_id": 16,
      "detail_value": {
        "ar": "2026-01-15",
        "en": "2026-01-15"
      }
    },
    {
      "category_detail_id": 17,
      "detail_value": {
        "ar": "2027-01-15",
        "en": "2027-01-15"
      }
    },
    {
      "category_detail_id": 18,
      "detail_value": {
        "ar": "السعودية",
        "en": "Saudi Arabia"
      }
    }
  ]
}
```

---

## ملاحظات مهمة

### 1. التعامل مع اللغات المتعددة

جميع الحقول متعددة اللغات يجب أن تكون بصيغة JSON object:
```json
{
  "name": {
    "ar": "النص بالعربية",
    "en": "Text in English"
  }
}
```

اللغات المتاحة يتم جلبها من:
```
GET /api/admin/languages
```

### 2. رفع الصور

- الصور يجب أن تكون من نوع `image` (jpg, jpeg, png, gif, webp)
- الحد الأقصى لحجم الصورة: 5MB
- يتم رفع الصور عبر `multipart/form-data`
- الصور يتم تخزينها في `storage/` ويتم إرجاع الرابط الكامل

### 3. التنويعات والمتاجر

- كل تنويع يمكن أن يكون له أسعار وكميات مختلفة في كل متجر
- إذا لم يتم تحديد `shop_variants`، لن يكون المنتج متاحاً في أي متجر
- يجب ربط كل تنويع بمتجر واحد على الأقل

### 4. حساب السعر بعد الخصم

السعر بعد الخصم يتم حسابه تلقائياً:
- إذا كان `discount_type = percentage`: `price - (price * discount / 100)`
- إذا كان `discount_type = fixed`: `price - discount`
- إذا كان `discount_type = none`: `price`


### 5. البحث والفلترة

البحث يشمل الحقول التالية:
- `name` (الاسم)
- `description` (الوصف)
- `sku` (رمز المنتج)
- `barcode` (الباركود)
- `brand.name` (اسم العلامة التجارية)
- `category.name` (اسم الفئة)
- `vendor.name` (اسم البائع)

مثال:
```
GET /api/admin/products?search=سامسونج
```

### 6. الترتيب (Sorting)

الحقول القابلة للترتيب:
- `id`
- `price`
- `created_at`
- `category_id`
- `name`
- `sku`
- `country`
- `model`
- `quantity`
- `time_prepare`

مثال:
```
GET /api/admin/products?sortField=price&sortOrder=desc
```

### 7. Soft Delete

عند حذف منتج:
- يتم تعيين `deleted_at` في جدول `products`
- يتم حذف التنويعات المرتبطة (Cascade Soft Delete)
- يتم حذف `shop_product_variants` المرتبطة
- الصور والوسائط تبقى في التخزين

لاستعادة منتج محذوف، يجب استخدام:
```php
Product::withTrashed()->find($id)->restore();
```

### 8. التحقق من الصلاحيات

جميع الـ endpoints تتطلب:
- Authentication: `auth:admin`
- يمكن إضافة Middleware للتحقق من الصلاحيات: `crud.permission:products`

### 9. معدل التقييم (Rating)

- `rating`: متوسط التقييم (من 1 إلى 5)
- `rating_count`: عدد التقييمات
- يتم حساب التقييم من جدول `ratings`
- التقييمات يمكن أن تكون للمنتج أو للتنويع

### 10. الإشعارات

عند قبول أو رفض منتج:
- يتم إرسال إشعار FCM للبائع
- يتم تسجيل الإشعار في `vendor_notifications`
- الإشعار يحتوي على:
  - عنوان
  - رسالة
  - نوع الإشعار
  - بيانات إضافية (product_id)

---

## أمثلة كاملة للاستخدام

### مثال 1: إضافة منتج بسيط بدون تنويعات

```javascript
const formData = new FormData();

// الحقول الأساسية
formData.append('category_id', 10);
formData.append('price', 50);
formData.append('quantity', 100);

// الاسم والوصف
formData.append('name[ar]', 'كتاب تعلم البرمجة');
formData.append('name[en]', 'Learn Programming Book');
formData.append('description[ar]', 'كتاب شامل لتعلم البرمجة');
formData.append('description[en]', 'Comprehensive programming book');

// الصور
formData.append('media[]', bookImage1);
formData.append('media[]', bookImage2);

// إرسال
fetch('/api/admin/products', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json'
  },
  body: formData
});
```

### مثال 2: إضافة منتج مع تنويعات وربطه بالمتاجر

```javascript
const formData = new FormData();

// الحقول الأساسية
formData.append('category_id', 5);
formData.append('brand_id', 3);
formData.append('price', 15000);

// الاسم والوصف
formData.append('name[ar]', 'هاتف سامسونج جالاكسي S23');
formData.append('name[en]', 'Samsung Galaxy S23');
formData.append('description[ar]', 'هاتف ذكي بمواصفات عالية');
formData.append('description[en]', 'High-end smartphone');

// الصور الرئيسية
formData.append('media[]', mainImage1);
formData.append('media[]', mainImage2);

// التنويع الأول: أسود + 128 جيجا
formData.append('variants[0][attributes_values_ids][]', 25); // أسود
formData.append('variants[0][attributes_values_ids][]', 30); // 128 جيجا
formData.append('variants[0][images][]', blackVariantImage);

// التنويع الثاني: أبيض + 256 جيجا
formData.append('variants[1][attributes_values_ids][]', 26); // أبيض
formData.append('variants[1][attributes_values_ids][]', 31); // 256 جيجا
formData.append('variants[1][images][]', whiteVariantImage);

// ربط التنويع الأول بالمتاجر
formData.append('shop_variants[0][shop_id]', 1); // فرع الرياض
formData.append('shop_variants[0][variant_index]', 0);
formData.append('shop_variants[0][price]', 14500);
formData.append('shop_variants[0][quantity]', 20);

formData.append('shop_variants[1][shop_id]', 2); // فرع جدة
formData.append('shop_variants[1][variant_index]', 0);
formData.append('shop_variants[1][price]', 14800);
formData.append('shop_variants[1][quantity]', 15);

// ربط التنويع الثاني بالمتاجر
formData.append('shop_variants[2][shop_id]', 1);
formData.append('shop_variants[2][variant_index]', 1);
formData.append('shop_variants[2][price]', 15500);
formData.append('shop_variants[2][quantity]', 10);

// تفاصيل الفئة
formData.append('category_details[0][category_detail_id]', 5);
formData.append('category_details[0][detail_value][ar]', '6.1 بوصة');
formData.append('category_details[0][detail_value][en]', '6.1 inch');

// إرسال
fetch('/api/admin/products', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json'
  },
  body: formData
});
```


### مثال 3: تعديل منتج موجود (تحديث السعر والكمية فقط)

```javascript
const formData = new FormData();

// تحديث السعر والكمية فقط
formData.append('price', 13500);
formData.append('quantity', 75);
formData.append('discount', 15);

// إرسال
fetch('/api/admin/products/123', {
  method: 'POST', // مع _method=PUT
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json'
  },
  body: formData
});
```

### مثال 4: تعديل صور المنتج

```javascript
const formData = new FormData();

// الاحتفاظ بالصورة الأولى وحذف الثانية وإضافة صورة جديدة
formData.append('existing_media_ids[]', 301); // الاحتفاظ بهذه الصورة
formData.append('media[]', newImage); // إضافة صورة جديدة

// إرسال
fetch('/api/admin/products/123', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json'
  },
  body: formData
});
```

### مثال 5: فلترة المنتجات حسب معايير متعددة

```javascript
// جلب المنتجات المقبولة في فئة الهواتف من علامة سامسونج
fetch('/api/admin/products?category_id=5&brand_id=3&approval_status=approved&sortField=price&sortOrder=asc&per_page=20', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json',
    'Accept-Language': 'ar'
  }
});
```

### مثال 6: البحث عن منتجات

```javascript
// البحث عن كلمة "سامسونج" في جميع الحقول
fetch('/api/admin/products?search=سامسونج&page=1&per_page=20', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json',
    'Accept-Language': 'ar'
  }
});
```

### مثال 7: قبول منتج

```javascript
fetch('/api/admin/products/123/approve', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json',
    'Accept-Language': 'ar'
  }
});
```

### مثال 8: رفض منتج مع سبب الرفض

```javascript
fetch('/api/admin/products/123/reject', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Accept-Language': 'ar'
  },
  body: JSON.stringify({
    rejection_reason: 'الصور غير واضحة. يرجى رفع صور بجودة أعلى.'
  })
});
```

---

## خريطة تدفق البيانات (Data Flow)

### 1. إضافة منتج جديد

```
المستخدم (Admin)
    ↓
اختيار الفئة (Category)
    ↓
جلب صفات الفئة (Category Attributes) → إنشاء التنويعات (Variants)
    ↓
جلب تفاصيل الفئة (Category Details) → ملء المواصفات
    ↓
اختيار المتاجر (Shops) → ربط التنويعات بالمتاجر (Shop Variants)
    ↓
رفع الصور (Media)
    ↓
إضافة الشارات والأيقونات (Badges & Icons)
    ↓
إرسال الطلب (POST /api/admin/products)
    ↓
حفظ في قاعدة البيانات
    ↓
إرجاع البيانات الكاملة للمنتج
```

### 2. تدفق الموافقة على المنتج

```
البائع (Vendor) يضيف منتج
    ↓
حالة المنتج: pending
    ↓
الإدارة (Admin) تراجع المنتج
    ↓
    ├─→ قبول (Approve)
    │       ↓
    │   حالة المنتج: approved
    │       ↓
    │   إرسال إشعار للبائع
    │       ↓
    │   المنتج يظهر للعملاء
    │
    └─→ رفض (Reject)
            ↓
        حالة المنتج: rejected
            ↓
        إرسال إشعار للبائع مع السبب
            ↓
        المنتج لا يظهر للعملاء
            ↓
        البائع يمكنه التعديل وإعادة التقديم
```


---

## قواعد التحقق (Validation Rules)

### عند الإضافة (Create)

| الحقل | القواعد | ملاحظات |
|------|---------|---------|
| `category_id` | required, exists:categories,id | مطلوب |
| `price` | required, integer, min:0 | مطلوب |
| `media` | nullable, array | اختياري — يمكن إنشاء منتج بدون صور |
| `media.*` | nullable, image, max:5120 | حجم أقصى 5MB |
| `name.{locale}` | nullable, string, max:255 | لكل لغة |
| `description.{locale}` | nullable, string | لكل لغة |
| `sku` | nullable, string, unique:products,sku | فريد |
| `model` | nullable, string, unique:products,model | فريد |
| `cost_price` | nullable, numeric, min:0 | اختياري |
| `discount` | nullable, integer, min:0, max:100 | 0-100 |
| `discount_type` | nullable, in:none,percentage,fixed | اختياري |
| `quantity` | nullable, integer, min:0 | اختياري |
| `brand_id` | nullable, integer, exists:brands,id | اختياري |
| `variants` | nullable, array | اختياري |
| `variants.*.attributes_values_ids` | nullable, array | اختياري |
| `variants.*.attributes_values_ids.*` | required, integer, exists:attribute_values,id | مطلوب إذا وجد |
| `variants.*.images` | nullable, array | اختياري |
| `variants.*.images.*` | nullable, image | اختياري |
| `shop_variants` | nullable, array | اختياري |
| `shop_variants.*.shop_id` | required, exists:shops,id | مطلوب إذا وجد |
| `shop_variants.*.variant_index` | required, integer, min:0 | مطلوب إذا وجد |
| `shop_variants.*.price` | nullable, integer, min:0 | اختياري |
| `shop_variants.*.quantity` | nullable, integer, min:0 | اختياري |
| `category_details` | nullable, array | اختياري |
| `category_details.*.category_detail_id` | nullable, exists:category_details,id | اختياري |
| `category_details.*.detail_value` | nullable, array | اختياري |
| `extra_details` | nullable, array | اختياري |
| `extra_details.*.detail_key` | nullable, array | اختياري |
| `extra_details.*.detail_value` | nullable, array | اختياري |
| `extra_details.*.price` | nullable, numeric, min:0 | اختياري |
| `badges` | nullable, array | اختياري |
| `badges.*.id` | required, integer, exists:badges,id | مطلوب إذا وجد |
| `badges.*.position` | required, in:top,bottom | مطلوب إذا وجد |
| `icon_ids` | nullable, array | اختياري |
| `icon_ids.*` | required, integer, exists:icons,id | مطلوب إذا وجد |
| `seo_title.{locale}` | nullable, string, max:160 | لكل لغة |
| `seo_description.{locale}` | nullable, string, max:320 | لكل لغة |
| `seo_keywords.{locale}` | nullable, array | لكل لغة |
| `seo_image` | nullable, image | اختياري |

### عند التعديل (Update)

نفس القواعد أعلاه، مع الاختلافات التالية:

| الحقل | القواعد | ملاحظات |
|------|---------|---------|
| `category_id` | nullable, exists:categories,id | اختياري |
| `price` | nullable, integer, min:0 | اختياري |
| `media` | nullable, array | اختياري |
| `sku` | nullable, string, unique:products,sku,{id} | فريد عدا المنتج الحالي |
| `model` | nullable, string, unique:products,model,{id} | فريد عدا المنتج الحالي |
| `existing_media_ids` | nullable, array | IDs الصور المحتفظ بها |
| `existing_media_ids.*` | integer | معرف صورة |
| `variants.*.id` | nullable, exists:product_variants,id | معرف التنويع |
| `variants.*.existing_images_ids` | nullable, array | IDs صور التنويع المحتفظ بها |
| `variants.*.existing_images_ids.*` | integer | معرف صورة |
| `category_details.*.id` | nullable, exists:product_category_details,id | معرف التفصيل |
| `extra_details.*.id` | nullable, exists:product_extra_details,id | معرف التفصيل |

### قواعد الموافقة/الرفض

| الحقل | القواعد | ملاحظات |
|------|---------|---------|
| `rejection_reason` | required, string, max:1000 | مطلوب عند الرفض |

---

## رسائل الأخطاء الشائعة

### 1. خطأ في الفئة
```json
{
  "errors": {
    "category_id": [
      "حقل الفئة مطلوب",
      "الفئة المحددة غير موجودة"
    ]
  }
}
```

### 2. خطأ في السعر
```json
{
  "errors": {
    "price": [
      "حقل السعر مطلوب",
      "يجب أن يكون السعر رقماً صحيحاً",
      "يجب أن يكون السعر أكبر من أو يساوي 0"
    ]
  }
}
```

### 3. خطأ في الصور
```json
{
  "errors": {
    "media": [
      "يجب رفع صورة واحدة على الأقل"
    ],
    "media.0": [
      "يجب أن يكون الملف صورة",
      "حجم الصورة يجب ألا يتجاوز 5MB"
    ]
  }
}
```

### 4. خطأ في التنويعات
```json
{
  "errors": {
    "variants.0.attributes_values_ids.0": [
      "قيمة الصفة غير موجودة"
    ]
  }
}
```

### 5. خطأ في SKU أو Model
```json
{
  "errors": {
    "sku": [
      "رمز المنتج مستخدم من قبل"
    ],
    "model": [
      "رقم الموديل مستخدم من قبل"
    ]
  }
}
```

### 6. خطأ في الموافقة
```json
{
  "success": false,
  "message": "يمكن قبول المنتجات في حالة الانتظار فقط"
}
```

### 7. خطأ في الرفض
```json
{
  "errors": {
    "rejection_reason": [
      "يجب إدخال سبب الرفض",
      "سبب الرفض يجب ألا يتجاوز 1000 حرف"
    ]
  }
}
```



---

## أسئلة شائعة (FAQ)

### 1. كيف أضيف منتج بدون تنويعات؟
لا تحتاج لإرسال حقل `variants` على الإطلاق. فقط أرسل الحقول الأساسية والصور.

### 2. هل يمكن إضافة منتج بدون ربطه بمتجر؟
نعم. إذا ما أُرسل `shop_variants`، يُربَط المنتج تلقائياً بفرع البائع الافتراضي (`is_default`) ويصير قابلاً للشراء. إذا ما وُجد فرع افتراضي/نشط للبائع، يبقى بدون ربط ولا يُشترى حتى يُربط يدوياً.

### 3. كيف أعرف الصفات المتاحة لفئة معينة؟
استخدم: `GET /api/admin/category-attributes?category_id={id}`

### 4. كيف أعرف التفاصيل المطلوبة لفئة معينة؟
استخدم: `GET /api/admin/category-details?category_id={id}`

### 5. هل يمكن تعديل منتج بعد قبوله؟
نعم، يمكن تعديل أي منتج في أي وقت بغض النظر عن حالة الموافقة.

### 6. ماذا يحدث عند حذف منتج؟
يتم Soft Delete، أي أن البيانات تبقى في قاعدة البيانات لكن لا تظهر في الاستعلامات العادية.

### 7. كيف أضيف صور لتنويع معين؟
أرسل الصور في `variants[index][images][]` حيث `index` هو رقم التنويع.

### 8. هل يمكن أن يكون للمنتج أكثر من سعر؟
نعم، كل تنويع يمكن أن يكون له سعر مختلف في كل متجر عبر `shop_variants`.

### 9. كيف أحدد موضع الشارة على المنتج؟
استخدم حقل `position` في `badges` ويمكن أن يكون `top` أو `bottom`.

### 10. ما الفرق بين `category_details` و `extra_details`؟
- `category_details`: تفاصيل محددة مسبقاً حسب الفئة (مثل: حجم الشاشة للهواتف)
- `extra_details`: تفاصيل مخصصة يمكن إضافتها لأي منتج (مثل: تغليف هدايا)

### 11. كيف أضيف خصم على المنتج؟
استخدم حقول `discount` و `discount_type`:
- `discount_type = percentage`: خصم بالنسبة المئوية
- `discount_type = fixed`: خصم بقيمة ثابتة

### 12. هل يمكن البحث في المنتجات؟
نعم، استخدم `?search=كلمة_البحث` وسيتم البحث في جميع الحقول النصية.

### 13. كيف أرتب المنتجات حسب السعر؟
استخدم: `?sortField=price&sortOrder=asc` (تصاعدي) أو `desc` (تنازلي)

### 14. ما هي الحقول المطلوبة عند الإضافة؟
فقط 3 حقول مطلوبة:
- `category_id`
- `price`
- `media` (اختياري)

### 15. كيف أحذف صورة من منتج موجود؟
عند التعديل، أرسل فقط IDs الصور التي تريد الاحتفاظ بها في `existing_media_ids`. أي صورة غير موجودة سيتم حذفها.

---

## نصائح وأفضل الممارسات

### 1. تحسين الأداء
- استخدم Pagination عند جلب قائمة المنتجات
- حدد `per_page` مناسب (20-50 عنصر)
- استخدم الفلترة بدلاً من جلب جميع المنتجات

### 2. إدارة الصور
- ضغط الصور قبل الرفع لتقليل حجم الملف
- استخدم صور بجودة عالية للمنتجات الرئيسية
- أضف صور متعددة من زوايا مختلفة

### 3. التنويعات
- أنشئ تنويعات فقط للخيارات المهمة (اللون، الحجم)
- لا تبالغ في عدد التنويعات
- تأكد من ربط كل تنويع بمتجر واحد على الأقل

### 4. SEO
- املأ حقول SEO لتحسين ظهور المنتج في محركات البحث
- استخدم كلمات مفتاحية ذات صلة
- اكتب وصف SEO جذاب (160 حرف للعنوان، 320 للوصف)

### 5. التفاصيل
- املأ جميع تفاصيل الفئة المطلوبة
- أضف تفاصيل إضافية مفيدة للعميل
- كن دقيقاً في المواصفات

### 6. الأسعار
- تأكد من دقة الأسعار
- استخدم `cost_price` لحساب الأرباح
- راجع الأسعار بشكل دوري

### 7. الموافقة
- راجع المنتجات المعلقة بانتظام
- اكتب سبب رفض واضح ومفيد للبائع
- تواصل مع البائع إذا كانت هناك مشاكل متكررة

### 8. الأمان
- تحقق من صلاحيات المستخدم قبل السماح بالعمليات
- سجل جميع العمليات الحساسة (Activity Log)
- استخدم HTTPS لجميع الطلبات

### 9. التعامل مع الأخطاء
- تحقق من الـ Response قبل معالجة البيانات
- اعرض رسائل خطأ واضحة للمستخدم
- سجل الأخطاء للمراجعة

### 10. الاختبار
- اختبر جميع السيناريوهات قبل النشر
- تأكد من عمل الفلترة والبحث بشكل صحيح
- اختبر رفع الصور بأحجام وأنواع مختلفة

---

## الخلاصة

هذا الدليل يغطي جميع جوانب إدارة المنتجات في لوحة الإدارة، بما في ذلك:

✅ بنية قاعدة البيانات الكاملة
✅ جميع الـ API Endpoints
✅ أمثلة كاملة للـ Request والـ Response
✅ شرح تفصيلي لكل حقل ومصدره
✅ كيفية التعامل مع التنويعات والمتاجر
✅ نظام الموافقة والرفض
✅ العلاقات مع الجداول الأخرى
✅ الحقول الديناميكية حسب الفئة
✅ قواعد التحقق والأخطاء الشائعة
✅ أفضل الممارسات والنصائح

للمزيد من المعلومات أو الاستفسارات، يرجى مراجعة الكود المصدري أو التواصل مع فريق التطوير.

---

**تاريخ آخر تحديث**: 27 مارس 2026
**الإصدار**: 1.0.0
