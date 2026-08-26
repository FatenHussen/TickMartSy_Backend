# إصلاح مشكلة صورة SEO والصورة المصغرة في المنتجات

## المشكلة

عند إضافة أو تعديل منتج في لوحة الإدارة:
- صورة SEO (`seo_image`) والصورة المصغرة (`thumbnail`) لم تكن تُخزن بشكل صحيح
- كانت تُحفظ في جدول `product_media` بدلاً من حقول مباشرة في جدول `products`
- عند التعديل بدون إرسال الصور، كانت تُحذف الصور القديمة

## الحل

### 1. تعديل `ProductService`

تم تغيير معالجة `thumbnail` و `seo_image` من `mediaCollections` إلى `singleImages`:

**قبل:**
```php
protected $mediaCollections = [
    'images' => [
        'collection' => 'product',
        'type'       => 'multiple',
    ],
    'variant_images' => [
        'collection' => 'variant',
        'type'       => 'multiple',
    ],
    'seo_image' => [
        'collection' => 'seo',
        'type'       => 'single',
    ],
    'thumbnail' => [
        'collection' => 'thumbnail',
        'type'       => 'single',
    ],
];
```

**بعد:**
```php
protected $mediaCollections = [
    'images' => [
        'collection' => 'product',
        'type'       => 'multiple',
    ],
    'variant_images' => [
        'collection' => 'variant',
        'type'       => 'multiple',
    ],
];

protected $singleImages = ['thumbnail', 'seo_image'];
```

### 2. تحسين `BaseService`

تم تحسين دالة `handleSingleImages` لمنع حذف الصور عند التعديل:

**التحسين الرئيسي:**
```php
// Remove from $data to prevent overwriting with null
unset($data[$column]);
```

هذا السطر يضمن أنه إذا لم يتم إرسال صورة جديدة، لن يتم تمرير الحقل إلى `update()` وبالتالي لن تُحذف الصورة القديمة.

## كيفية العمل الآن

### عند الإضافة (Create)
- إذا تم إرسال `thumbnail` أو `seo_image`، يتم رفعها وحفظ المسار في جدول `products`
- إذا لم يتم إرسالها، تبقى `null`

### عند التعديل (Update)
- **إذا تم إرسال صورة جديدة:**
  - يتم حذف الصورة القديمة من Storage
  - يتم رفع الصورة الجديدة
  - يتم تحديث المسار في قاعدة البيانات
- **إذا لم يتم إرسال الحقل:**
  - ✅ تبقى الصورة القديمة كما هي
  - ✅ لا يتم حذف أو تعديل أي شيء
  - ✅ الحقل يُزال من `$data` قبل `update()`

## التخزين

- الصور تُخزن في: `storage/app/public/product/`
- المسار المحفوظ في قاعدة البيانات: `product/{uuid}.{extension}`
- الرابط الكامل: `https://example.com/storage/product/{uuid}.{extension}`

## أمثلة API

### 1. إضافة منتج مع الصور
```javascript
const formData = new FormData();
formData.append('category_id', 5);
formData.append('price', 15000);
formData.append('name[ar]', 'هاتف سامسونج');
formData.append('name[en]', 'Samsung Phone');
formData.append('media[]', productImage1); // صور المنتج الرئيسية
formData.append('media[]', productImage2);
formData.append('thumbnail', thumbnailFile); // الصورة المصغرة
formData.append('seo_image', seoImageFile); // صورة SEO

fetch('/api/admin/products', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json'
  },
  body: formData
});
```

### 2. تعديل منتج - تحديث الصورة المصغرة فقط
```javascript
const formData = new FormData();
formData.append('thumbnail', newThumbnailFile); // فقط الصورة المصغرة الجديدة
// ✅ لا نرسل seo_image، ستبقى كما هي

fetch('/api/admin/products/123', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json'
  },
  body: formData
});
```

### 3. تعديل منتج - بدون تغيير الصور
```javascript
const formData = new FormData();
formData.append('price', 14000); // فقط تحديث السعر
formData.append('quantity', 100);
// ✅ لا نرسل thumbnail أو seo_image، ستبقى كما هي

fetch('/api/admin/products/123', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json'
  },
  body: formData
});
```

### 4. تعديل منتج - حذف صورة SEO (إذا أردت)
```javascript
// لحذف صورة SEO، يجب إرسال null أو empty string
// لكن هذا غير مدعوم حالياً - يجب تعديل الكود إذا أردت هذه الميزة
```

## Response

```json
{
  "success": true,
  "message": "تم تحديث المنتج بنجاح",
  "data": {
    "id": 123,
    "name": "هاتف سامسونج جالاكسي S23",
    "thumbnail": "https://example.com/storage/product/uuid-123.jpg",
    "seo_image": "https://example.com/storage/product/uuid-456.jpg",
    "images": [
      {
        "id": 301,
        "url": "https://example.com/storage/products/product-123-1.jpg"
      }
    ],
    ...
  }
}
```

## الفرق بين أنواع الصور

| النوع | الحقل | التخزين | الاستخدام |
|------|------|---------|-----------|
| صور المنتج الرئيسية | `media[]` | جدول `product_media` | معرض صور المنتج |
| الصورة المصغرة | `thumbnail` | حقل في جدول `products` | عرض سريع في القوائم |
| صورة SEO | `seo_image` | حقل في جدول `products` | محركات البحث والمشاركة |

## الملفات المعدلة

1. ✅ `app/Services/Admin/ProductService.php` - تغيير من mediaCollections إلى singleImages
2. ✅ `app/Services/BaseService.php` - إضافة unset للحماية من الكتابة بـ null

## الاختبار

### اختبار 1: إضافة منتج مع الصور
```bash
# يجب أن تُحفظ جميع الصور بنجاح
# thumbnail و seo_image في جدول products
# media في جدول product_media
```

### اختبار 2: تعديل منتج بدون إرسال الصور
```bash
# يجب أن تبقى الصور القديمة كما هي
# لا يتم حذف أي صورة
```

### اختبار 3: تعديل منتج مع صورة جديدة
```bash
# يجب حذف الصورة القديمة من Storage
# يجب رفع الصورة الجديدة
# يجب تحديث المسار في قاعدة البيانات
```

## تاريخ الإصلاح
27 مارس 2026
