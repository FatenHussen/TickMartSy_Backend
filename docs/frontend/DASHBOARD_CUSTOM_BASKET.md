# الداشبورد — فئات الجدولة + سلة أدمن جاهزة + قسم الصفحة

> **الجمهور:** فريق الداشبورد فقط  
> **تاريخ:** 5 أيلول 2026  
> **Base:** `/api/admin` + Admin token  
> **الباك:** جاهز

صلاحيات: `schedule.*` لفئات الجدولة · `scheduled-baskets` / سلل للسلة الجاهزة.

---

## لا تخلطوا بين الشاشات الثلاث

| الشاشة | شو هي | أين في الداشبورد |
|--------|--------|------------------|
| **فئات الجدولة** | كرت أسبوعي / شهري / كل 3 أيام | كتالوج `GET /api/admin/schedules` |
| **سلة أدمن جاهزة** | سلة بمنتجات ثابتة مربوطة بفئة واحدة | `POST /api/admin/scheduled-baskets` |
| **قسم الصفحة** | عرض الكروت على الرئيسية | `/sections/pages/{id}/sections/create` |

مسار المستخدم الأساسي: يفتح **فئة جدولة** ويخصّص هو المنتجات. سلة الأدمن الجاهزة **اختيارية**.

---

## 1) فئات الجدولة (الكرت)

| Method | Endpoint |
|--------|----------|
| GET | `/api/admin/schedules` |
| POST | `/api/admin/schedules` (multipart) |
| GET | `/api/admin/schedules/{id}` |
| PUT | `/api/admin/schedules/{id}` (multipart) |
| DELETE | `/api/admin/schedules/{id}` |

فلتر القائمة: `is_active=1` · `discount_type=percentage|fixed`

### حقول الفورم

| حقل | مطلوب | ملاحظة |
|-----|--------|--------|
| `name[ar]` `name[en]` | نعم | اسم الكرت |
| `description[ar]` `description[en]` | لا | وصف قصير على الكرت |
| `interval_days` | نعم | عدد الأيام (≥ 1) |
| `discount_type` | لا | `percentage` أو `fixed` |
| `discount_value` | لا | خصم **السلة كاملة** عند تخصيص المستخدم |
| `is_active` | لا | إيقاف الفئة يخفيها من `GET /api/user/schedules` |
| `image` | لا | jpeg/png/jpg/gif/webp |
| `images[]` | لا | صورة ثانية للتناوب |
| `deleted_image_ids[]` | لا | عند التعديل فقط |
| `badges[][id]` | لا | من `GET /api/admin/badges` |
| `badges[][position]` | لا | `top` أو `bottom` |

**لا** تكتبوا أيام أو خصم جوّا سلة المستخدم. المصدر هنا.

```http
POST /api/admin/schedules
Content-Type: multipart/form-data
```

```
name[ar]=أسبوعي
name[en]=Weekly
description[ar]=خضار ومنظفات كل أسبوع
description[en]=Groceries every week
interval_days=7
discount_type=percentage
discount_value=20
is_active=1
image=<file>
images[]=<file>
badges[0][id]=3
badges[0][position]=top
```

القائمة ترجع `name` / `description` حسب لغة الطلب. التفاصيل ترجع ترجمات كاملة.

---

## 2) قسم الصفحة — `/sections/pages/{id}/sections/create`

أضيفوا في «What should it show?» خيارين منفصلين:

| في الفورم | فئات الجدولة الزمنية | Scheduled baskets |
|-----------|----------------------|-------------------|
| التسمية | **فئات الجدولة الزمنية** | سلل مجدولة جاهزة |
| `content_type` | `schedule` | `schedule-basket` |
| `manual_model` | `schedule` | `schedule-basket` |
| `api_method` | `schedules` | `schedule-basket` |
| `display_type_id` | `11` (تلقائي) | `5` (تلقائي) |
| `variant` الموصى به | `vertical` | حسب التصميم |
| عناصر يدوية | `GET /api/admin/schedules` | سلل `is_schedule` |

`GET /api/admin/sections/item-types` فيه `schedule` → `admin/schedules` و`schedule-basket` → `admin/scheduled-baskets`.

لا ترسلوا `display_type_id` من الفرونت. الباك يعيّنه.

إنشاء قسم كروت الفئات (موصى به):

```http
POST /api/admin/pages/{pageId}/sections
```

```json
{
  "type": "api",
  "content_type": "schedule",
  "name": { "ar": "فئات الجدولة الزمنية", "en": "Schedule categories" },
  "layout": "slider",
  "variant": "vertical"
}
```

اختيار فئات محددة:

```json
{
  "type": "manual",
  "manual_model": "schedule",
  "name": { "ar": "فئات الجدولة الزمنية", "en": "Schedule categories" },
  "item_ids": [{ "item_id": 2, "order": 0 }],
  "layout": "slider",
  "variant": "vertical"
}
```

قسم سلل جاهزة:

```json
{
  "type": "api",
  "content_type": "schedule-basket",
  "name": { "ar": "سلل مجدولة", "en": "Scheduled baskets" },
  "layout": "slider",
  "variant": "vertical"
}
```

`content_type` المسموحة لهذا الفورم تشمل أيضاً: `suggested_products` · `suggested_shops` · `suggested_baskets`.

إذا رجع 422 `The selected content type is invalid` — حدّثوا الباك. القيمة `schedule-basket` صارت مسموحة.

---

## 3) سلة أدمن جاهزة (اختياري)

مو مطلوبة لمسار «المستخدم يخصّص». إذا بقيتوها: السلة **تختار فئة من الكتالوج**، ما تكتب أيام لحالا.

```http
POST /api/admin/scheduled-baskets
```

| حقل | مطلوب | ملاحظة |
|-----|--------|--------|
| `schedule_id` | نعم | جدولة **مفعّلة** من `/schedules` |
| `category_ids[]` | نعم | فئة **المنتج** (خضار…) — لتصنيف السلة ولفلتر الأصناف |
| `category_id` | لا | إذا أُرسل لوحده يُحوَّل إلى `category_ids` |
| `name[ar\|en]` | نعم | |
| `items[]` | نعم | انظر §4 |
| `discount` + `discount_type` | لا | فاضي = يرث خصم الفئة. معبّأ = استثناء لهالسلة (عيد 40%) |
| `image` / `images[]` | لا | |
| `badges[]` | لا | IDs من `/badges` |

**لا** ترسلوا `schedules[].number_of_days`.

فلتر القائمة: `GET /api/admin/scheduled-baskets?schedule_id=2`

الرد فيه `schedule_id`، `schedule`، `has_custom_discount`، و`discount` الفعلي.

لمسح استثناء الخصم عند التعديل: أرسلوا `discount` فاضي / `null`.

---

## 4) كارد «العنصر 1» — اختيار المنتجات

هذا **ليس** فورم إنشاء منتج. لا تستخدموا `GET /api/admin/category-attributes`.  
النص «حدد الفئة قبل المتغيرات» **غلط** لهالشاشة — احذفوه.

### ليش `shop_product_variant`؟

الطلب دائماً: **منتج × متغيّر (وزن/لون) × متجر**.

| المفهوم | مثال | الحقل |
|---------|------|--------|
| منتج | طماطم | `product_id` |
| متغيّر كتالوج | 1 كغ | `product_variant_id` |
| صف البيع بالمتجر | طماطم 1كغ في متجر الهدى | **`shop_product_variant_id`** |

نفس الطماطم من متجرين = صفّان، لكل متجر `id` مختلف. السلة تحفظ هذا الـ `id` حتى نعرف من أي متجر نطلب.

### من وين نجيب القائمة

بعد اختيار **فئة المنتج** على فورم السلة (`category_ids`):

```http
GET /api/admin/shop-product-variants?category_id=5&brand_id=3&shop_id=2&search=طماطم&price_min=1&price_max=20&per_page=50
```

debounce 300ms على `search`.

| Query | المعنى |
|-------|--------|
| `category_id` أو `category_ids[]` | منتجات هالفئة + فروعها |
| `brand_id` | ماركة |
| `shop_id` | متجر |
| `product_id` | أصناف منتج واحد |
| `search` | اسم / رقم منتج / SKU / باركود |
| `price_min` `price_max` | سعر البيع ($) |
| `cost_price_min` `cost_price_max` | سعر التكلفة (اختياري) |

انقلوا `category_ids` من فورم السلة إلى هذا الطلب حتى ما يطلع منتجات من فئة ثانية.

### شكل الصف

```json
{
  "id": 25,
  "label": "طماطم (وزن: 1كغ) - متجر الهدى",
  "variant_image": "https://…",
  "shop_id": 2,
  "product_variant_id": 10,
  "variant": {
    "id": 10,
    "price": 2.5,
    "discount": 0,
    "price_after_discount": 2.5,
    "quantity": 80
  }
}
```

اعرضوا `label` في الـ select. القيمة المحفوظة = `id`.

### ربط الحقول بالـ API

| في الواجهة | أرسلوا | القيمة |
|------------|--------|--------|
| المتغير الأساسي | `items[i][shop_product_variant_id]` | `id` واحد من القائمة |
| البدائل (التسمية الحالية «سلال مجدولة بديلة» غلط — صيّروها **بدائل المنتج**) | `items[i][shop_product_variant_ids][]` | `id`s أخرى — اختياري |
| كمية | `items[i][quantity]` | ≥ 1 |
| الحد الأدنى | `items[i][min_quantity]` | اختياري ≥ 1 |
| الكمية القصوى | `items[i][max_quantity]` | اختياري ≥ 1 |
| مطلوب | `items[i][is_required]` | `true` = ما ينحذف |
| إضافي | `items[i][is_extra]` | `true` = إضافة فوق الأساسيات |

مثال صنف:

```
items[0][shop_product_variant_id]=25
items[0][shop_product_variant_ids][0]=31
items[0][shop_product_variant_ids][1]=44
items[0][quantity]=2
items[0][min_quantity]=1
items[0][max_quantity]=5
items[0][is_required]=1
items[0][is_extra]=0
```

البدائل: نفس المنتج بوزن ثاني، أو نفس المتغيّر من متجر ثاني. المستخدم يقدر يبدّل الأساسي بواحد منها.

---

## 5) Checklist

### فئات الجدولة
- [ ] شاشة مستقلة عن السلة الجاهزة
- [ ] اسم ثنائي اللغة + وصف + أيام + خصم + تفعيل + صورة + بادجز
- [ ] إيقاف الفئة يخفيها من المستخدم

### قسم الصفحة
- [ ] خيار **فئات الجدولة الزمنية** = `content_type: schedule` (مو `schedule-basket`)
- [ ] خيار **سلل مجدولة** = `content_type: schedule-basket`
- [ ] `variant: vertical` لكروت الفئات
- [ ] لا ترسلوا `display_type_id`

### سلة جاهزة
- [ ] `schedule_id` من الكتالوج — لا أيام مكتوبة بالسلة
- [ ] خصم فاضي = يرث الفئة
- [ ] كارد الصنف يحمّل `GET /shop-product-variants` مع فلاتر فئة / ماركة / سعر / بحث / متجر
- [ ] القيمة المحفوظة `shop_product_variant_id` = `id` من هالقائمة
- [ ] «سلال مجدولة بديلة» → **بدائل المنتج** (`shop_product_variant_ids`)
- [ ] لا `category-attributes` ولا «حدد الفئة قبل المتغيرات»
