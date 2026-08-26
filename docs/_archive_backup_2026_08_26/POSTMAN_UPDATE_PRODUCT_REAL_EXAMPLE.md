# مثال واقعي لتعديل المنتج (Product ID: 20)

## معلومات المنتج الحالي
- **ID**: 20
- **الاسم**: ايفون 15 / iPhone 15
- **السعر**: 18000
- **عدد التنويعات**: 3
- **عدد الصور الرئيسية**: 3 (IDs: 73, 74, 75)
- **صور التنويعات**: 
  - Variant 36: صورتين (IDs: 76, 77)
  - Variant 37: صورة واحدة (ID: 78)
  - Variant 38: صورة واحدة (ID: 79)

---

## 1. تعديل كامل مع الاحتفاظ بجميع الصور

### Endpoint
```
POST http://127.0.0.1:8000/api/admin/products/20
```

### Headers
```
Authorization: Bearer YOUR_ADMIN_TOKEN
Accept: application/json
Accept-Language: ar
```

### Body (form-data)

```
# ===== الحقول الأساسية =====
category_id: 1
price: 17500
brand_id: 2
sku: IPHONE-15-BLK-256
model: A2846
cost_price: 14000
discount: 15
discount_type: percentage
quantity: 60
unit: قطعة
warranty_period: 12
barcode: 0194253404316
time_prepare: 00:45
is_instant_delivery: 0
is_visible: 1

# ===== الاسم والوصف =====
name[ar]: ايفون 15 برو
name[en]: iPhone 15 Pro

description[ar]: أحدث هاتف من آبل مع مميزات احترافية
description[en]: Latest Apple phone with professional features

full_description[ar]: آيفون 15 برو يأتي بشاشة Super Retina XDR بحجم 6.1 بوصة ومعالج A17 Pro
full_description[en]: iPhone 15 Pro comes with 6.1 inch Super Retina XDR display and A17 Pro chip

country[ar]: الولايات المتحدة
country[en]: United States

# ===== المنتجات المقترحة =====
bought_with[0]: 5
bought_with[1]: 12

# ===== الصور الرئيسية - الاحتفاظ بالموجودة =====
existing_media_ids[0]: 73
existing_media_ids[1]: 74
existing_media_ids[2]: 75
# لا نرسل media[] جديدة، نحتفظ بالصور الموجودة فقط

# ===== الصورة المصغرة - لا نغيرها =====
# لا نرسل thumbnail، ستبقى كما هي

# ===== صورة SEO - لا نغيرها =====
# لا نرسل seo_image، ستبقى كما هي

# ===== حقول SEO =====
seo_title[ar]: شراء آيفون 15 برو - أفضل سعر في السعودية
seo_title[en]: Buy iPhone 15 Pro - Best Price in Saudi Arabia

seo_description[ar]: احصل على آيفون 15 برو بأفضل سعر مع ضمان سنة وشحن مجاني
seo_description[en]: Get iPhone 15 Pro at the best price with 1 year warranty and free shipping

seo_keywords[ar][0]: آيفون
seo_keywords[ar][1]: آبل
seo_keywords[ar][2]: هاتف ذكي
seo_keywords[en][0]: iphone
seo_keywords[en][1]: apple
seo_keywords[en][2]: smartphone

# ===== التنويعات - الاحتفاظ بالموجودة =====
# التنويع الأول (ID: 36) - نحتفظ بصوره
variants[0][attributes_values_ids][0]: 10
variants[0][attributes_values_ids][1]: 25
variants[0][existing_images_ids][0]: 76
variants[0][existing_images_ids][1]: 77
# لا نرسل images[] جديدة

# التنويع الثاني (ID: 37) - نحتفظ بصورته
variants[1][attributes_values_ids][0]: 10
variants[1][attributes_values_ids][1]: 26
variants[1][existing_images_ids][0]: 78

# التنويع الثالث (ID: 38) - نحتفظ بصورته
variants[2][attributes_values_ids][0]: 11
variants[2][attributes_values_ids][1]: 25
variants[2][existing_images_ids][0]: 79

# ===== ربط المتاجر =====
shop_variants[0][shop_id]: 1
shop_variants[0][variant_index]: 0
shop_variants[0][price]: 17000
shop_variants[0][quantity]: 25

shop_variants[1][shop_id]: 2
shop_variants[1][variant_index]: 0
shop_variants[1][price]: 17300
shop_variants[1][quantity]: 20

shop_variants[2][shop_id]: 1
shop_variants[2][variant_index]: 1
shop_variants[2][price]: 19000
shop_variants[2][quantity]: 15

shop_variants[3][shop_id]: 1
shop_variants[3][variant_index]: 2
shop_variants[3][price]: 17200
shop_variants[3][quantity]: 18

# ===== تفاصيل الفئة =====
category_details[0][category_detail_id]: 5
category_details[0][detail_value][ar]: 6.1 بوصة
category_details[0][detail_value][en]: 6.1 inch

category_details[1][category_detail_id]: 6
category_details[1][detail_value][ar]: A17 Pro
category_details[1][detail_value][en]: A17 Pro

category_details[2][category_detail_id]: 7
category_details[2][detail_value][ar]: 3274 مللي أمبير
category_details[2][detail_value][en]: 3274 mAh

category_details[3][category_detail_id]: 8
category_details[3][detail_value][ar]: 8 جيجا رام
category_details[3][detail_value][en]: 8GB RAM

# ===== التفاصيل الإضافية =====
extra_details[0][detail_key][ar]: تغليف هدايا
extra_details[0][detail_key][en]: Gift Wrapping
extra_details[0][detail_value][ar]: تغليف فاخر مع بطاقة تهنئة
extra_details[0][detail_value][en]: Premium wrapping with greeting card
extra_details[0][price]: 75

extra_details[1][detail_key][ar]: ضمان إضافي
extra_details[1][detail_key][en]: Extended Warranty
extra_details[1][detail_value][ar]: سنة إضافية
extra_details[1][detail_value][en]: One extra year
extra_details[1][price]: 600

extra_details[2][detail_key][ar]: خدمة التركيب
extra_details[2][detail_key][en]: Installation Service
extra_details[2][detail_value][ar]: تركيب وتفعيل الجهاز
extra_details[2][detail_value][en]: Device setup and activation
extra_details[2][price]: 150

# ===== الشارات =====
badges[0][id]: 1
badges[0][position]: top

badges[1][id]: 3
badges[1][position]: bottom

# ===== الأيقونات =====
icon_ids[0]:1
```

---

## 2. تعديل بسيط - السعر والكمية فقط

### Body (form-data)
```
price: 16999
quantity: 75
discount: 20
```

**النتيجة**: 
- ✅ السعر سيتحدث إلى 16999
- ✅ الكمية ستتحدث إلى 75
- ✅ الخصم سيتحدث إلى 20%
- ✅ جميع الصور ستبقى كما هي
- ✅ جميع البيانات الأخرى ستبقى كما هي

---

## 3. تعديل الصورة المصغرة فقط

### Body (form-data)
```
thumbnail: [اختر ملف صورة جديدة - new-thumbnail.png]
```

**النتيجة**:
- ✅ الصورة المصغرة القديمة ستُحذف من Storage
- ✅ الصورة الجديدة سترفع
- ✅ صورة SEO ستبقى كما هي
- ✅ الصور الرئيسية ستبقى كما هي
- ✅ صور التنويعات ستبقى كما هي

---

## 4. تعديل صورة SEO فقط

### Body (form-data)
```
seo_image: [اختر ملف صورة جديدة - new-seo-image.png]
```

**النتيجة**:
- ✅ صورة SEO القديمة ستُحذف من Storage
- ✅ الصورة الجديدة سترفع
- ✅ الصورة المصغرة ستبقى كما هي
- ✅ جميع الصور الأخرى ستبقى كما هي

---

## 5. إضافة صورة رئيسية جديدة مع الاحتفاظ بالقديمة

### Body (form-data)
```
# الاحتفاظ بالصور الموجودة
existing_media_ids[0]: 73
existing_media_ids[1]: 74
existing_media_ids[2]: 75

# إضافة صورة جديدة
media[0]: [اختر ملف صورة جديدة - new-product-image.png]
```

**النتيجة**:
- ✅ الصور 73, 74, 75 ستبقى
- ✅ صورة جديدة ستُضاف (ستأخذ ID جديد مثل 80)
- ✅ المجموع: 4 صور

---

## 6. حذف صورة رئيسية والاحتفاظ بالباقي

### Body (form-data)
```
# الاحتفاظ بصورتين فقط
existing_media_ids[0]: 73
existing_media_ids[1]: 74
# لم نذكر 75، سيتم حذفها
```

**النتيجة**:
- ✅ الصور 73, 74 ستبقى
- ❌ الصورة 75 ستُحذف من Storage وقاعدة البيانات
- ✅ المجموع: صورتين فقط

---

## 7. تحديث صور تنويع معين

### Body (form-data)
```
# التنويع الأول - نحتفظ بصورة واحدة ونضيف جديدة
variants[0][attributes_values_ids][0]: 10
variants[0][attributes_values_ids][1]: 25
variants[0][existing_images_ids][0]: 76
# لم نذكر 77، سيتم حذفها
variants[0][images][0]: [اختر ملف صورة جديدة]

# التنويع الثاني - نحتفظ بصورته
variants[1][attributes_values_ids][0]: 10
variants[1][attributes_values_ids][1]: 26
variants[1][existing_images_ids][0]: 78

# التنويع الثالث - نحتفظ بصورته
variants[2][attributes_values_ids][0]: 11
variants[2][attributes_values_ids][1]: 25
variants[2][existing_images_ids][0]: 79
```

**النتيجة**:
- ✅ التنويع الأول: صورة 76 تبقى، صورة 77 تُحذف، صورة جديدة تُضاف
- ✅ التنويع الثاني: صورة 78 تبقى
- ✅ التنويع الثالث: صورة 79 تبقى

---

## 8. إضافة تنويع جديد

### Body (form-data)
```
# التنويعات الموجودة
variants[0][attributes_values_ids][0]: 10
variants[0][attributes_values_ids][1]: 25
variants[0][existing_images_ids][0]: 76
variants[0][existing_images_ids][1]: 77

variants[1][attributes_values_ids][0]: 10
variants[1][attributes_values_ids][1]: 26
variants[1][existing_images_ids][0]: 78

variants[2][attributes_values_ids][0]: 11
variants[2][attributes_values_ids][1]: 25
variants[2][existing_images_ids][0]: 79

# تنويع جديد
variants[3][attributes_values_ids][0]: 11
variants[3][attributes_values_ids][1]: 26
variants[3][images][0]: [اختر ملف صورة]

# ربط التنويع الجديد بالمتاجر
shop_variants[4][shop_id]: 1
shop_variants[4][variant_index]: 3
shop_variants[4][price]: 19500
shop_variants[4][quantity]: 10
```

**النتيجة**:
- ✅ التنويعات الثلاثة الموجودة تبقى
- ✅ تنويع جديد يُضاف (سيأخذ ID جديد مثل 39)
- ✅ المجموع: 4 تنويعات

---

## 9. حذف تنويع

### Body (form-data)
```
# نرسل فقط التنويعات التي نريد الاحتفاظ بها
variants[0][attributes_values_ids][0]: 10
variants[0][attributes_values_ids][1]: 25
variants[0][existing_images_ids][0]: 76
variants[0][existing_images_ids][1]: 77

variants[1][attributes_values_ids][0]: 10
variants[1][attributes_values_ids][1]: 26
variants[1][existing_images_ids][0]: 78

# لم نذكر التنويع الثالث (ID: 38)، سيتم حذفه (Soft Delete)
```

**النتيجة**:
- ✅ التنويع الأول (ID: 36) يبقى
- ✅ التنويع الثاني (ID: 37) يبقى
- ❌ التنويع الثالث (ID: 38) يُحذف (Soft Delete)
- ❌ صورة التنويع الثالث (ID: 79) تُحذف
- ❌ ربط المتاجر للتنويع الثالث يُحذف

---

## 10. تحديث الاسم والوصف فقط

### Body (form-data)
```
name[ar]: آيفون 15 برو ماكس
name[en]: iPhone 15 Pro Max

description[ar]: أكبر شاشة وأفضل بطارية
description[en]: Biggest display and best battery
```

**النتيجة**:
- ✅ الاسم والوصف يتحدثان
- ✅ جميع الصور تبقى كما هي
- ✅ جميع البيانات الأخرى تبقى كما هي

---

## 11. تحديث تفاصيل الفئة فقط

### Body (form-data)
```
category_details[0][category_detail_id]: 5
category_details[0][detail_value][ar]: 6.7 بوصة
category_details[0][detail_value][en]: 6.7 inch

category_details[1][category_detail_id]: 6
category_details[1][detail_value][ar]: A17 Pro Bionic
category_details[1][detail_value][en]: A17 Pro Bionic
```

**النتيجة**:
- ✅ تفاصيل الفئة تتحدث
- ✅ جميع الصور تبقى كما هي
- ✅ جميع البيانات الأخرى تبقى كما هي

---

## 12. تحديث التفاصيل الإضافية فقط

### Body (form-data)
```
extra_details[0][detail_key][ar]: تغليف هدايا فاخر
extra_details[0][detail_key][en]: Premium Gift Wrapping
extra_details[0][detail_value][ar]: تغليف فاخر جداً مع بطاقة ذهبية
extra_details[0][detail_value][en]: Very premium wrapping with golden card
extra_details[0][price]: 100

extra_details[1][detail_key][ar]: ضمان شامل
extra_details[1][detail_key][en]: Full Warranty
extra_details[1][detail_value][ar]: سنتين كاملة
extra_details[1][detail_value][en]: Two full years
extra_details[1][price]: 800
```

**النتيجة**:
- ✅ التفاصيل الإضافية تتحدث
- ✅ جميع الصور تبقى كما هي
- ✅ جميع البيانات الأخرى تبقى كما هي

---

## 13. تحديث الشارات والأيقونات فقط

### Body (form-data)
```
# تغيير الشارات
badges[0][id]: 2
badges[0][position]: top

# إضافة أيقونة جديدة
icon_ids[0]: 1
icon_ids[1]: 2
```

**النتيجة**:
- ✅ الشارات تتحدث
- ✅ الأيقونات تتحدث
- ✅ جميع الصور تبقى كما هي
- ✅ جميع البيانات الأخرى تبقى كما هي

---

## 14. تحديث أسعار المتاجر فقط

### Body (form-data)
```
# نحتاج إرسال التنويعات أيضاً
variants[0][attributes_values_ids][0]: 10
variants[0][attributes_values_ids][1]: 25
variants[0][existing_images_ids][0]: 76
variants[0][existing_images_ids][1]: 77

variants[1][attributes_values_ids][0]: 10
variants[1][attributes_values_ids][1]: 26
variants[1][existing_images_ids][0]: 78

variants[2][attributes_values_ids][0]: 11
variants[2][attributes_values_ids][1]: 25
variants[2][existing_images_ids][0]: 79

# تحديث الأسعار
shop_variants[0][shop_id]: 1
shop_variants[0][variant_index]: 0
shop_variants[0][price]: 16500
shop_variants[0][quantity]: 30

shop_variants[1][shop_id]: 2
shop_variants[1][variant_index]: 0
shop_variants[1][price]: 16800
shop_variants[1][quantity]: 25

shop_variants[2][shop_id]: 1
shop_variants[2][variant_index]: 1
shop_variants[2][price]: 18500
shop_variants[2][quantity]: 20

shop_variants[3][shop_id]: 1
shop_variants[3][variant_index]: 2
shop_variants[3][price]: 16700
shop_variants[3][quantity]: 22
```

**النتيجة**:
- ✅ أسعار وكميات المتاجر تتحدث
- ✅ جميع الصور تبقى كما هي
- ✅ جميع البيانات الأخرى تبقى كما هي

---

## 15. مثال كامل - تحديث كل شيء مع صور جديدة

### Body (form-data)
```
# ===== الحقول الأساسية =====
price: 16500
quantity: 80
discount: 18

# ===== الاسم =====
name[ar]: آيفون 15 برو - إصدار محدث
name[en]: iPhone 15 Pro - Updated Edition

# ===== الصور الرئيسية - حذف واحدة وإضافة جديدة =====
existing_media_ids[0]: 73
existing_media_ids[1]: 74
# حذفنا 75
media[0]: [اختر ملف صورة جديدة - new-image-1.png]
media[1]: [اختر ملف صورة جديدة - new-image-2.png]

# ===== تحديث الصورة المصغرة =====
thumbnail: [اختر ملف صورة جديدة - new-thumbnail.png]

# ===== تحديث صورة SEO =====
seo_image: [اختر ملف صورة جديدة - new-seo-image.png]

# ===== التنويعات - تحديث صور التنويع الأول =====
variants[0][attributes_values_ids][0]: 10
variants[0][attributes_values_ids][1]: 25
variants[0][existing_images_ids][0]: 76
# حذفنا 77
variants[0][images][0]: [اختر ملف صورة جديدة - new-variant-1.png]

variants[1][attributes_values_ids][0]: 10
variants[1][attributes_values_ids][1]: 26
variants[1][existing_images_ids][0]: 78

variants[2][attributes_values_ids][0]: 11
variants[2][attributes_values_ids][1]: 25
variants[2][existing_images_ids][0]: 79

# ===== تحديث أسعار المتاجر =====
shop_variants[0][shop_id]: 1
shop_variants[0][variant_index]: 0
shop_variants[0][price]: 16000
shop_variants[0][quantity]: 35

shop_variants[1][shop_id]: 2
shop_variants[1][variant_index]: 0
shop_variants[1][price]: 16300
shop_variants[1][quantity]: 28

shop_variants[2][shop_id]: 1
shop_variants[2][variant_index]: 1
shop_variants[2][price]: 18000
shop_variants[2][quantity]: 18

shop_variants[3][shop_id]: 1
shop_variants[3][variant_index]: 2
shop_variants[3][price]: 16200
shop_variants[3][quantity]: 20

# ===== باقي الحقول =====
category_details[0][category_detail_id]: 5
category_details[0][detail_value][ar]: 6.1 بوصة
category_details[0][detail_value][en]: 6.1 inch

extra_details[0][detail_key][ar]: تغليف هدايا
extra_details[0][detail_key][en]: Gift Wrapping
extra_details[0][detail_value][ar]: تغليف فاخر
extra_details[0][detail_value][en]: Premium wrapping
extra_details[0][price]: 80

badges[0][id]: 1
badges[0][position]: top

icon_ids[0]: 1
```

**النتيجة**:
- ✅ السعر والكمية والخصم يتحدثون
- ✅ الاسم يتحدث
- ✅ الصور الرئيسية: 73, 74 تبقى، 75 تُحذف، صورتين جديدتين تُضافان
- ✅ الصورة المصغرة القديمة تُحذف، الجديدة تُرفع
- ✅ صورة SEO القديمة تُحذف، الجديدة تُرفع
- ✅ صور التنويع الأول: 76 تبقى، 77 تُحذف، صورة جديدة تُضاف
- ✅ صور التنويعات الأخرى تبقى كما هي
- ✅ أسعار المتاجر تتحدث
- ✅ باقي البيانات تتحدث

---

## ملخص القواعد المهمة

### ✅ للاحتفاظ بالصور:
```
# الصور الرئيسية
existing_media_ids[0]: 73
existing_media_ids[1]: 74

# صور التنويعات
variants[0][existing_images_ids][0]: 76
variants[0][existing_images_ids][1]: 77

# الصورة المصغرة وصورة SEO
# لا ترسل الحقل أصلاً
```

### ✅ لحذف صور:
```
# الصور الرئيسية - لا تذكر ID الصورة في existing_media_ids
existing_media_ids[0]: 73
# لم نذكر 74، سيتم حذفها

# صور التنويعات - لا تذكر ID الصورة في existing_images_ids
variants[0][existing_images_ids][0]: 76
# لم نذكر 77، سيتم حذفها
```

### ✅ لإضافة صور جديدة:
```
# الصور الرئيسية
media[0]: [ملف جديد]

# صور التنويعات
variants[0][images][0]: [ملف جديد]

# الصورة المصغرة
thumbnail: [ملف جديد]

# صورة SEO
seo_image: [ملف جديد]
```

### ✅ لعدم تغيير الصورة المصغرة أو صورة SEO:
```
# ببساطة لا ترسل الحقل
# thumbnail و seo_image لا يُرسلان
```

---

## Response المتوقع

```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": {
    "id": 20,
    "name": {
      "ar": "آيفون 15 برو",
      "en": "iPhone 15 Pro"
    },
    "price": 17500,
    "price_after_discount": 14875,
    "discount": 15,
    "thumbnail": "http://127.0.0.1:8000/storage/product/new-uuid.png",
    "seo_image": "http://127.0.0.1:8000/storage/product/new-uuid-2.png",
    "images": [
      {
        "id": 73,
        "url": "http://127.0.0.1:8000/storage/product/product/ca1286a6-1792-4e9d-b30d-96ea14942abc.png"
      },
      {
        "id": 74,
        "url": "http://127.0.0.1:8000/storage/product/product/3e76d4df-de24-42ef-aeda-6351f318bc24.png"
      }
    ],
    "variants": [
      {
        "id": 36,
        "images": [
          {
            "id": 76,
            "url": "http://127.0.0.1:8000/storage/product-variant/variant_images/83781a7f-ac30-4230-8501-feca7b0bbede.png"
          },
          {
            "id": 77,
            "url": "http://127.0.0.1:8000/storage/product-variant/variant_images/e3a19d57-c1df-42a8-a201-7add4ce70cf0.png"
          }
        ]
      }
    ]
  }
}
```

---

## نصائح للاختبار في Postman

1. **احفظ IDs الصور**: بعد الإنشاء، احفظ IDs الصور من الـ Response
2. **استخدم existing_media_ids**: عند التعديل، استخدم IDs الصور التي تريد الاحتفاظ بها
3. **لا ترسل الحقل**: إذا لم تريد تغيير thumbnail أو seo_image، لا ترسل الحقل أصلاً
4. **اختبر خطوة بخطوة**: ابدأ بتعديل بسيط ثم انتقل للمعقد

---

**تاريخ الإنشاء**: 27 مارس 2026
**Product ID المستخدم**: 20
