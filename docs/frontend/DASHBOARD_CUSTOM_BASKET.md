# الداشبورد — آخر نسخة: فئات الجدولة + قسم الصفحة + سلة جاهزة

> **أرسلوا هذا الملف لفريق الداشبورد**  
> **آخر تحديث:** 5 أيلول 2026 (مساءً)  
> **Base:** `/api/admin` + Admin token  
> **الباك:** بعد `git pull` + `php artisan migrate`

صلاحيات: `schedule.*` · سلل جاهزة: `scheduled-baskets`.

---

## ماذا تغيّر اليوم (مهم)

| المشكلة | المطلوب من الداشبورد |
|---------|----------------------|
| تعديل الجدول يرجّع نجاح والصورة/البادجز ما تنحفظ | التعديل = **POST** + `_method=PUT` (مو PUT خام). **لا** تضعوا `Content-Type: multipart/form-data` يدوياً — خلّوا المتصفح يضيف الـ boundary |
| خصم فاضي يفشل الـ update | إذا ما في خصم: **لا ترسلوا** `discount_type` ولا `discount_value` (لا `''`) |
| 422 `content type` / `manual model` / `item_type` | فئات الجدولة = `content_type: schedule`. سلل جاهزة = `schedule-basket`. لا ترسلوا `item_type` — الباك يعبّيه |
| الرد بعد التعديل | لازم يوصل `description` · `image` · `images` · `top_badges` · `bottom_badges` و`updated_at` يتغيّر. إذا نفس `created_at` = ما انحفظ |

---

## لا تخلطوا بين الشاشات

| الشاشة | شو هي | أين |
|--------|--------|-----|
| **فئات الجدولة** | كرت أسبوعي / شهري | `GET/POST /api/admin/schedules` |
| **سلة أدمن جاهزة** | سلة بمنتجات ثابتة + `schedule_id` | `/scheduled-baskets` |
| **قسم الصفحة** | عرض الكروت على الرئيسية | `/sections/create` أو `/sections/pages/{id}/sections/create` |

المسار الأساسي للمستخدم: يفتح **فئة جدولة** ويخصّص هو. السلة الجاهزة اختيارية.

---

## 1) فئات الجدولة

| Method | Endpoint |
|--------|----------|
| GET | `/api/admin/schedules` |
| POST | `/api/admin/schedules` (multipart) |
| GET | `/api/admin/schedules/{id}` |
| POST + `_method=PUT` | `/api/admin/schedules/{id}` (multipart) |
| DELETE | `/api/admin/schedules/{id}` |

فلتر القائمة: `is_active=1` · `discount_type=percentage\|fixed`

### حقول الفورم (إنشاء وتعديل)

| حقل | مطلوب | ملاحظة |
|-----|--------|--------|
| `name[ar]` `name[en]` | نعم | اسم الكرت |
| `description[ar]` `description[en]` | لا | وصف قصير |
| `interval_days` | نعم | ≥ 1 |
| `discount_type` | لا | `percentage` أو `fixed` — **احذفوا الحقل** إذا فاضي |
| `discount_value` | لا | خصم **السلة كاملة** |
| `is_active` | لا | `1` / `0` — إيقافها يخفيها من المستخدم |
| `image` | لا | صورة الغلاف jpeg/png/jpg/gif/webp |
| `images[]` | لا | صور إضافية للتناوب |
| `deleted_image_ids[]` | تعديل فقط | IDs من `images` للتفاصيل |
| `badges[][id]` | لا | من `GET /api/admin/badges` |
| `badges[][position]` | لا | `top` أو `bottom` |

```
POST /api/admin/schedules/{id}
name[ar]=أسبوعي
name[en]=Weekly
description[ar]=خضار كل أسبوع
interval_days=7
discount_type=percentage
discount_value=20
is_active=1
image=<file>
badges[0][id]=3
badges[0][position]=top
_method=PUT
```

### رد التفاصيل (بعد الحفظ)

القائمة ترجع `name` / `description` ككائن `{ ar, en }`.

```json
{
  "id": 5,
  "name": { "ar": "أسبوعي", "en": "Weekly" },
  "description": { "ar": "…", "en": "…" },
  "image": "https://…/storage/schedules/….jpg",
  "images": ["https://…", "https://…"],
  "interval_days": 7,
  "discount_type": "percentage",
  "discount_value": 20,
  "is_active": true,
  "top_badges": [{ "id": 3, "name": "…", "image": "…", "color": "…", "position": "top" }],
  "bottom_badges": [],
  "updated_at": "2026-09-05T13:00:00.000000Z"
}
```

---

## 2) قسم الصفحة

خياران منفصلان في «What should it show?»:

| | فئات الجدولة الزمنية | Scheduled baskets |
|--|--|--|
| التسمية | فئات الجدولة الزمنية | سلل مجدولة جاهزة |
| `content_type` | `schedule` | `schedule-basket` |
| `manual_model` | `schedule` | `schedule-basket` |
| `api_method` | `schedules` | `schedule-basket` |
| `display_type_id` | `11` (تلقائي) | `5` (تلقائي) |
| `variant` | `vertical` | حسب التصميم |
| عناصر يدوية | `GET /api/admin/schedules` | سلل `is_schedule` |

لا ترسلوا `display_type_id` ولا `item_type`.

```json
{
  "type": "api",
  "content_type": "schedule",
  "name": { "ar": "فئات الجدولة الزمنية", "en": "Schedule categories" },
  "layout": "slider",
  "variant": "vertical"
}
```

يدوي:

```json
{
  "type": "manual",
  "content_type": "schedule",
  "manual_model": "schedule",
  "name": { "ar": "فئات الجدولة الزمنية", "en": "Schedule categories" },
  "item_ids": [{ "item_id": 2, "order": 0 }]
}
```

مسموح أيضاً: `suggested_products` · `suggested_shops` · `suggested_baskets`.

---

## 3) سلة أدمن جاهزة (اختياري)

```http
POST /api/admin/scheduled-baskets
```

| حقل | مطلوب |
|-----|--------|
| `schedule_id` | نعم — فئة مفعّلة من `/schedules` |
| `category_ids[]` | نعم — فئة المنتج لفلتر الأصناف |
| `name[ar\|en]` | نعم |
| `items[]` | نعم — `shop_product_variant_id` + كمية |
| `discount` + `discount_type` | لا — فاضي = يرث الفئة |

**لا** `schedules[].number_of_days`.  
الأصناف: `GET /api/admin/shop-product-variants?category_id=&brand_id=&shop_id=&search=`  
القيمة = `id` من القائمة → `items[i][shop_product_variant_id]`.  
البدائل: `items[i][shop_product_variant_ids][]` (مو «سلال مجدولة بديلة»).  
لا `category-attributes`.

---

## 4) Checklist

- [ ] تعديل الجدول: POST + `_method=PUT` + بدون Content-Type يدوي
- [ ] خصم فاضي = لا ترسلوا الحقل
- [ ] بعد الحفظ: `image` + `top_badges` بالرد و`updated_at` جديد
- [ ] قسم الصفحة: `schedule` ≠ `schedule-basket`
- [ ] سلة جاهزة مربوطة بـ `schedule_id` وأصناف من `shop-product-variants`
