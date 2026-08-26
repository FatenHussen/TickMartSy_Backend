# دليل تحديث صور المنتجات والـ Variants

## نظرة عامة
عند تحديث منتج، يمكنك التحكم الكامل بالصور:
- حذف صور موجودة
- إضافة صور جديدة
- الاحتفاظ بصور معينة

## ملاحظة تقنية
النظام يستخدم `ProductMedia` model مع علاقة `media()` polymorphic، مش Spatie Media Library.

## الآلية

### 1. صور المنتج الرئيسية

#### الحقول:
- `existing_media_ids[]`: مصفوفة بـ IDs الصور اللي بدك تخليها
- `media[]`: صور جديدة بدك تضيفها

#### مثال:

```
PUT /api/admin/products/{id}

existing_media_ids[]: 5
existing_media_ids[]: 8
existing_media_ids[]: 12
media[]: (binary - صورة جديدة 1)
media[]: (binary - صورة جديدة 2)
```

#### النتيجة:
- الصور بـ IDs: 5, 8, 12 → تبقى
- باقي الصور القديمة → تُحذف
- الصور الجديدة → تُضاف

### 2. صور الـ Variants

#### الحقول لكل variant:
- `variants[0][existing_images_ids][]`: IDs الصور اللي بدك تخليها
- `variants[0][images][]`: صور جديدة

#### مثال:

```
PUT /api/admin/products/{id}

variants[0][attributes_values_ids][]: 70
variants[0][attributes_values_ids][]: 67
variants[0][existing_images_ids][]: 15
variants[0][existing_images_ids][]: 18
variants[0][images][]: (binary - صورة جديدة)

variants[1][attributes_values_ids][]: 71
variants[1][attributes_values_ids][]: 68
variants[1][images][]: (binary - صورة جديدة 1)
variants[1][images][]: (binary - صورة جديدة 2)
```

#### النتيجة:
- **Variant 0**: الصور 15 و 18 تبقى + صورة جديدة تُضاف
- **Variant 1**: كل الصور القديمة تُحذف + صورتين جديدتين تُضافوا

## سيناريوهات الاستخدام

### سيناريو 1: حذف صورة واحدة فقط

المنتج عنده 3 صور بـ IDs: 10, 11, 12
بدك تحذف الصورة 11:

```
existing_media_ids[]: 10
existing_media_ids[]: 12
```

### سيناريو 2: إضافة صور جديدة بدون حذف

المنتج عنده 2 صور بـ IDs: 20, 21
بدك تضيف صورة جديدة:

```
existing_media_ids[]: 20
existing_media_ids[]: 21
media[]: (binary - صورة جديدة)
```

### سيناريو 3: استبدال كل الصور

بدك تحذف كل الصور القديمة وتضيف صور جديدة:

```
// لا ترسل existing_media_ids
media[]: (binary - صورة 1)
media[]: (binary - صورة 2)
media[]: (binary - صورة 3)
```

### سيناريو 4: حذف كل الصور

بدك تحذف كل الصور بدون إضافة جديدة:

```
existing_media_ids: []
// أو لا ترسل الحقل نهائياً
```

### سيناريو 5: عدم التعديل على الصور

إذا ما بدك تعدل على الصور، لا ترسل أي من الحقلين:
- لا ترسل `existing_media_ids`
- لا ترسل `media`

## مثال كامل - تحديث منتج

```
PUT /api/admin/products/5

name[ar]: منتج محدث
name[en]: Updated Product
price: 150

// صور المنتج الرئيسية
existing_media_ids[]: 10
existing_media_ids[]: 12
media[]: (binary - صورة جديدة)

// Variant 0 - نخلي صورة وحدة ونضيف صورة جديدة
variants[0][attributes_values_ids][]: 70
variants[0][attributes_values_ids][]: 67
variants[0][price]: 200
variants[0][existing_images_ids][]: 15
variants[0][images][]: (binary - صورة جديدة)

// Variant 1 - نحذف كل الصور القديمة ونضيف صور جديدة
variants[1][attributes_values_ids][]: 71
variants[1][attributes_values_ids][]: 68
variants[1][price]: 180
variants[1][images][]: (binary - صورة 1)
variants[1][images][]: (binary - صورة 2)

// Variant 2 - ما نعدل على الصور (ما نرسل existing_images_ids ولا images)
variants[2][attributes_values_ids][]: 72
variants[2][attributes_values_ids][]: 69
variants[2][price]: 220
```

## ملاحظات مهمة

1. **IDs الصور**: احصل عليها من الـ response عند عرض المنتج
2. **الترتيب**: الصور الجديدة تُضاف بعد الصور الموجودة
3. **الحذف التلقائي**: أي صورة مش موجودة بـ `existing_media_ids` تُحذف تلقائياً
4. **Variants**: كل variant له صوره المستقلة
5. **الصور الأخرى**: `thumbnail` و `seo_image` تبقى كما هي (single images)

## الحصول على IDs الصور

عند عرض المنتج:

```json
{
  "id": 5,
  "name": "منتج",
  "media": [
    {
      "id": 10,
      "url": "https://example.com/image1.jpg"
    },
    {
      "id": 11,
      "url": "https://example.com/image2.jpg"
    },
    {
      "id": 12,
      "url": "https://example.com/image3.jpg"
    }
  ],
  "variants": [
    {
      "id": 1,
      "media": [
        {
          "id": 15,
          "url": "https://example.com/variant1.jpg"
        },
        {
          "id": 16,
          "url": "https://example.com/variant2.jpg"
        }
      ]
    }
  ]
}
```

استخدم هذه الـ IDs في `existing_media_ids` و `existing_images_ids`.
