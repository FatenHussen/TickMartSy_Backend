# كل تعديلات الداشبورد (React) — ملخّص شامل

هذا المستند يجمع **كل التعديلات** التي تمت على الباك إند وتحتاج تعديل في الداشبورد (React).
المراجع التفصيلية مذكورة في كل قسم.

---

## الفهرس

1. [Page Builder الموحّد](#1-page-builder-الموحّد)
2. [إدارة الأقسام (Sections)](#2-إدارة-الأقسام)
3. [صفحات الفئات التلقائية](#3-صفحات-الفئات-التلقائية)
4. [شريط التنقّل (Nav Menu)](#4-شريط-التنقّل-nav-menu)
5. [إسناد المنتج لأي مستوى فئة](#5-إسناد-المنتج-لأي-مستوى-فئة)
6. [وراثة صفات الفئة من الرئيسية](#6-وراثة-صفات-الفئة-من-الرئيسية)
7. [سعر وكمية المتغيّر (Variant)](#7-سعر-وكمية-المتغيّر)
8. [حفظ المتغيّرات وربط الفروع](#8-حفظ-المتغيّرات-وربط-الفروع)
9. [حذف المتغيّرات مع تأكيد](#9-حذف-المتغيّرات-مع-تأكيد)
10. [حذف صفات الفئة مع تأكيد](#10-حذف-صفات-الفئة-مع-تأكيد)

---

## 1) Page Builder الموحّد

> المرجع: `FRONTEND_DASHBOARD_PAGE_BUILDER.md`

### الفكرة

الأدمن يتعامل مع مفهوم واحد — **"صفحة فيها أقسام"**. لا يحتاج إنشاء Section ثم ربطه يدويًا.

### Endpoints

| Method | Endpoint | الوصف |
|--------|----------|--------|
| GET | `/api/admin/pages` | قائمة الصفحات (بحث + ترقيم) |
| POST | `/api/admin/pages` | إنشاء صفحة (`title` مطلوب، `slug` اختياري) |
| GET | `/api/admin/pages/{page}` | صفحة مع أقسامها |
| PUT/PATCH | `/api/admin/pages/{page}` | تعديل صفحة |
| DELETE | `/api/admin/pages/{page}` | حذف صفحة |
| GET | `/api/admin/pages/{id}/sliders` | كل الأقسام للاختيار |
| POST | `/api/admin/pages/{id}/sections` | إضافة قسم (بـ `section_id` أو إنشاء مباشر) |

### فلاتر قائمة الصفحات

| Param | القيم | الوصف |
|-------|-------|--------|
| `type` | `content` \| `category` | فلترة نوع الصفحة |
| `category_id` | رقم | صفحة فئة محددة |

كل صفحة ترجّع: `sections_count`, `is_category_page`, `category_id`.

### إضافة قسم لصفحة

**الطريقة الموصى بها:** اختيار قسم موجود:

```json
{ "section_id": 12 }
```

**أو إنشاء مباشر** بنداء واحد:

```json
{
  "type": "manual",
  "name": { "ar": "...", "en": "..." },
  "manual_model": "banner",
  "item_ids": [{ "item_id": 12, "order": 0 }],
  "layout": "slider",
  "variant": "horizontal",
  "background_color": "#F5F5F5"
}
```

### حقول القسم

| الحقل | القيم |
|-------|-------|
| `type` | `manual` \| `api` |
| `manual_model` | `banner` \| `product` \| `shop` \| `restaurant` \| `brand` \| `recipe` \| `basket` \| `category` |
| `api_method` | `products` \| `categories` \| `shops` \| `restaurants` \| `brands` \| `recipes` \| `baskets` \| `schedule-basket` \| `suggested_products` \| `suggested_shops` \| `suggested_baskets` |
| `layout` | `slider` \| `list` \| `grid` — طريقة عرض القسم |
| `variant` | `horizontal` \| `vertical` \| `square` — شكل الكارد |
| `content_type` | بديل أبسط يُحوَّل تلقائيًا |

> انظر **`FRONTEND_SECTION_LAYOUT_AND_CARD.md`** لترتيب خطوات الفورم.

### إعادة الترتيب والمعاينة

- `POST /api/admin/page-sections/pages/{page}/reorder`
- `GET /api/admin/page-sections/pages/{page}/preview`
- `PUT/DELETE /api/admin/page-sections/{id}`

### البانرات

قسم البانرات **يدوي** ويختار من البانرات الموجودة (`GET /api/admin/banners`). الصورة **عرضية** (≈ 16:6). بانر واحد = إعلان ثابت، عدة بانرات = سلايدر.

---

## 2) إدارة الأقسام

> المرجع: `FRONTEND_DASHBOARD_SLIDERS.md`

> **ملاحظة:** لا يوجد كيان "سلايدر" مستقل. **القسم (Section)** هو الكيان الوحيد.
> - `layout: slider` = سلايدر أفقي
> - `variant` = شكل الكارد داخل القسم
> التفاصيل: `FRONTEND_SECTION_LAYOUT_AND_CARD.md`.

### الفكرة

القسم يُنشأ **مرة واحدة** (بدون اختيار صفحة)، ثم يُضاف لأي صفحة. يمكن أن يحتوي منتجات، متاجر، مزودين خدمة، سلل، بانرات، فئات، أو أي نوع محتوى.

### Endpoints

| Method | Endpoint | الوصف |
|--------|----------|--------|
| GET | `/api/admin/sections` | قائمة كل الأقسام |
| POST | `/api/admin/sections` | إنشاء قسم |
| GET | `/api/admin/sections/{id}` | تفاصيل + عناصر |
| PUT/PATCH | `/api/admin/sections/{id}` | تعديل |
| DELETE | `/api/admin/sections/{id}` | حذف |

### حقول الإنشاء

| الحقل | مطلوب | الوصف |
|-------|-------|--------|
| `name.ar` / `name.en` | نعم | اسم القسم |
| `content_type` | لا | بديل مُبسّط يُحوَّل تلقائيًا (`banner` \| `product` \| `shop` \| `restaurant` \| `brand` \| `category` \| `recipe` \| `basket`) |
| `type` | **نعم** | `manual` أو `api` |
| `variant` | لا | `horizontal` (سلايدر) \| `vertical` (شبكة) \| `square` (مربعات). افتراضي `horizontal` |
| `background_color` | لا | لون خلفية |
| `background_card_color` | لا | لون خلفية الكارد |
| `item_ids` | إذا manual | العناصر المختارة |
| `filters` | إذا api | فلاتر |
| `page_id` | **ممنوع** | لا تُرسل |

### إضافة قسم لصفحة

من داخل الصفحة → `GET /api/admin/pages/{id}/sliders` → اختيار → `POST /api/admin/pages/{id}/sections` مع `section_id`.

### فلترة العناصر اليدوية

عند اختيار منتجات يدويًا في نموذج القسم، أضف فلاتر `search` + `category_id` + `brand_id` + `shop_id` فوق قائمة المنتجات (debounce 300ms على البحث). التفاصيل في `FRONTEND_DASHBOARD_SLIDERS.md` §5.

---

## 3) صفحات الفئات التلقائية

> المرجع: `FRONTEND_DASHBOARD_CATEGORY_PAGES.md`

### الفكرة

كل فئة (رئيسية أو فرعية بأي مستوى) = **صفحة تلقائية**. لا يُنشئها ولا يحذفها الأدمن يدويًا.

### دورة الحياة

```
إنشاء فئة → تُنشأ صفحتها + قسمان (فرعية + منتجات)
تعديل اسم الفئة → يتحدّث عنوان الصفحة
حذف الفئة → تُحذف الصفحة وأقسامها
```

### الوصول

- قائمة الصفحات: `GET /api/admin/pages?type=category`
- قائمة الفئات: كل فئة ترجّع `page_id`

### القيود

- `DELETE /pages/{id}` على صفحة فئة → **422 مرفوض**
- `PUT /pages/{id}` لتعديل title/slug → **422 مرفوض**
- الحذف فقط عبر `DELETE /categories/{id}`

### UX

- **لا** زر "إنشاء صفحة" أو "حذف صفحة" في تبويب صفحات الفئات
- استخدم `can_delete_page` و `can_edit_metadata` من API
- زر "بناء الصفحة" → Page Builder عادي

---

## 4) شريط التنقّل (Nav Menu)

> المرجع: `FRONTEND_DASHBOARD_NAV_MENU.md`

### الفكرة

الأدمن يتحكّم بالكامل بالشريط العلوي — إضافة/تعديل/حذف/ترتيب/تفعيل.

### Endpoints

| Method | Endpoint | الوصف |
|--------|----------|--------|
| GET | `/api/admin/nav-menu-items` | قائمة العناصر |
| POST | `/api/admin/nav-menu-items` | إنشاء عنصر (`multipart/form-data`) |
| GET | `/api/admin/nav-menu-items/{id}` | عنصر واحد |
| PUT/PATCH | `/api/admin/nav-menu-items/{id}` | تعديل |
| DELETE | `/api/admin/nav-menu-items/{id}` | حذف |
| POST | `/api/admin/nav-menu-items/reorder` | إعادة ترتيب |

### أنواع العناصر

| `type` | الحقل المطلوب |
|--------|---------------|
| `page` | `page_id` — صفحة من Page Builder |
| `category` | `category_id` — فئة |
| `brand` | `brand_id` — ماركة |
| `url` | `url` — رابط خارجي |
| `route` | `route_key` — شاشة ثابتة |

### قيم `route_key`

```
home | categories | brands | shops | baskets | points | help | subscriptions
```

### حقول أخرى

- `title` → `{ ar, en }` مطلوبان
- `icon` → ملف صورة اختياري (≤ 2MB)
- `order` → ترتيب الظهور
- `is_active` → إظهار/إخفاء
- `open_in_new_tab` → للويب مع `type=url`

### إعادة الترتيب

```json
{ "ordered_ids": [3, 1, 5, 2, 4] }
```

---

## 5) إسناد المنتج لأي مستوى فئة

> المرجع: `FRONTEND_DASHBOARD_PRODUCT_CATEGORY_ANY_LEVEL.md`

### التغيير

| قبل | بعد |
|-----|-----|
| لازم تختار لآخر فئة فرعية (ورقة) | المستوى 1 يكفي. 2–6 اختيارية |
| `has_children === true` يمنع الحفظ | `has_children` يظهر select التالي **كخيار** فقط |

### قواعد الفورم

1. المستوى 1 (الرئيسية) **مطلوب**.
2. إذا للفئة أبناء → أظهر select التالي كـ **اختياري**.
3. `category_id` المرسل = آخر فئة مختارة (أعمق select مش فاضي).
4. تغيير مستوى أعلى → امسح المستويات الأعمق.
5. **لا تمنع Submit** إذا `has_children === true`.

### الصفات

تُجلب عند اختيار المستوى 1 فقط:

```http
GET /api/admin/category-attributes?category_id={rootCategoryId}
```

النزول للمستويات 2–6 **لا** يعيد طلب الصفات.

---

## 6) وراثة صفات الفئة من الرئيسية

> المرجع: `FRONTEND_DASHBOARD_CATEGORY_ATTRIBUTE_INHERITANCE.md`

### التغيير

| قبل | بعد |
|-----|-----|
| صفة مربوطة بأي فئة | صفة مربوطة **بالجذر فقط** |
| فورم المنتج ينتظر الورقة | بمجرد اختيار المستوى 1 تظهر الصفات |

### شاشة إدارة الصفات

- `category_id` لازم فئة رئيسية → إذا فرعية → **422**
- Select الفئة: اعرض الرئيسية فقط (`is_root === true`)
- `GET /api/admin/category-attributes?category_id={id}` — أي id بالشجرة يرجع صفات الجذر

### فورم المنتج

- طلب الصفات عند اختيار المستوى 1
- عرض الصفات والقيم فورًا
- عدم إعادة الطلب عند النزول للمستويات 2–6
- إعادة الطلب فقط إذا تغيّرت الرئيسية

---

## 7) سعر وكمية المتغيّر

> المرجع: `FRONTEND_DASHBOARD_VARIANT_PRICE_QUANTITY.md`

### التغيير الكاسر

**السعر والكمية صاروا على المتغيّر (`product_variants`)**، مو على ربط المحل (`shop_product_variants`).

| قبل | بعد |
|-----|-----|
| `product.variants[].shops[].price` | `product.variants[].price` |
| `product.variants[].shops[].quantity` | `product.variants[].quantity` |

### تاب المتغيّرات

- Input سعر على كل variant
- Input كمية (integer) على كل variant

### قسم توفر الفروع

- `shop_variants.*.shop_id` → مطلوب
- `shop_variants.*.variant_index` → مطلوب
- `shop_variants.*.cost_price` → اختياري (تكلفة شراء)
- `shop_variants.*.price` → **محذوف — لا ترسله**
- `shop_variants.*.quantity` → **محذوف — لا ترسله**

---

## 8) حفظ المتغيّرات وربط الفروع

> المرجع: `FRONTEND_DASHBOARD_PRODUCT_VARIANTS_SAVE.md`

### التغيير

`variants` و `shop_variants` **صاروا يُحفظون فعليًا** (كانوا يُتجاهلون).

### قواعد مهمة

1. **`variants` بديل كامل (replace):**
   - صف فيه `id` → تحديث
   - صف بدون `id` → إنشاء جديد
   - متغيّر موجود بالـ DB وما أُرسل → **soft delete**

2. **`shop_variants` كذلك replace:**
   - إذا أرسلتها → تُستبدل بالمرسل
   - إذا ما أرسلتها → تبقى كما هي
   - `shop_variants: []` → **تحذف كل الروابط**

3. **لا ترسل `variants` إذا الفورم ما فيه تاب متغيّرات**

4. **احفظ `variant.id`** من GET عند التحميل وأرجعه عند الحفظ

5. **`existing_images_ids`** مطلوب دائمًا — أي صورة غير مذكورة تُحذف

### Payload

```text
variants[0][id]=15
variants[0][sku]=JEANS-RED
variants[0][price]=25
variants[0][quantity]=12
variants[0][is_active]=1
variants[0][attributes_values_ids][0]=6

shop_variants[0][shop_id]=1
shop_variants[0][variant_index]=0
shop_variants[0][cost_price]=10000
```

- `variant_index` = ترتيب المتغيّر (0، 1، …) — **مو `variant_id`**
- لا ترسل `price`/`quantity` داخل `shop_variants`

### المنتج بدون متغيّرات

يُنشأ **متغيّر افتراضي** تلقائيًا من بيانات المنتج بدون ربط فرع → غير قابل للشراء.

---

## 9) حذف المتغيّرات مع تأكيد

> المرجع: `FRONTEND_DASHBOARD_VARIANT_DELETE_CONFIRM.md`

### التغيير

الحذف **مسموح دائمًا** (كان ممنوعًا إذا مرتبط). الحذف **soft delete**.

### التدفق

1. `DELETE /api/admin/product-variants/{id}` بدون `confirm`
   - إذا مرتبط → **409** مع `warnings` و `counts`
   - إذا مو مرتبط → **200** حذف مباشر
2. عرض نافذة التأكيد مع `warnings[].message`
3. إعادة الطلب مع `?confirm=true`

### Endpoints

| الغرض | Method | Endpoint |
|-------|--------|----------|
| معاينة أثر الحذف | GET | `/api/admin/product-variants/{id}/delete-impact` |
| حذف متغيّر | DELETE | `/api/admin/product-variants/{id}?confirm=true` |
| معاينة أثر حذف ربط متجر | GET | `/api/admin/shop-product-variants/{id}/delete-impact` |
| حذف ربط متجر | DELETE | `/api/admin/shop-product-variants/{id}?confirm=true` |

### ضمانات

- سجلات الطلبات **لا تُحذف أبدًا**
- صور المتغيّر تبقى محفوظة
- تحديث المخزون للطلبات الجارية يستمر

---

## 10) حذف صفات الفئة مع تأكيد

> المرجع: `FRONTEND_DASHBOARD_CATEGORY_ATTRIBUTE_DELETE_CONFIRM.md`

### التغيير

الحذف **مسموح دائمًا** (كان يرجع 422 ويتوقف).

### التدفق

1. `GET /api/admin/category-attributes/{id}/delete-impact` → ملخص الأثر
2. `GET /api/admin/category-attributes/{id}/linked-items?page=1&per_page=10` → تبويب العناصر المرتبطة
3. `DELETE /api/admin/category-attributes/{id}?confirm=true` → تنفيذ

### ماذا يحدث عند الحذف

- قيم الخاصية (أحمر، أزرق...) **تُحذف**
- المتغيّرات **تبقى** وتُنظّف منها قيم هذه الخاصية
- المنتجات **تبقى**
- الطلبات **لا تتأثر**

---

## أوامر التشغيل (بعد سحب التعديلات)

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=CategoryPagesBackfillSeeder
php artisan db:seed --class=NavMenuSeeder
```

---

## مراجع تفصيلية

| الملف | الموضوع |
|-------|---------|
| `FRONTEND_DASHBOARD_PAGE_BUILDER.md` | Page Builder الموحّد |
| `FRONTEND_DASHBOARD_SLIDERS.md` | إدارة الأقسام (Sections) |
| `FRONTEND_DASHBOARD_CATEGORY_PAGES.md` | صفحات الفئات |
| `FRONTEND_DASHBOARD_NAV_MENU.md` | شريط التنقّل |
| `FRONTEND_DASHBOARD_PRODUCT_CATEGORY_ANY_LEVEL.md` | إسناد المنتج لأي مستوى |
| `FRONTEND_DASHBOARD_CATEGORY_ATTRIBUTE_INHERITANCE.md` | وراثة صفات الفئة |
| `FRONTEND_DASHBOARD_VARIANT_PRICE_QUANTITY.md` | سعر وكمية المتغيّر |
| `FRONTEND_DASHBOARD_PRODUCT_VARIANTS_SAVE.md` | حفظ المتغيّرات |
| `FRONTEND_DASHBOARD_VARIANT_DELETE_CONFIRM.md` | حذف المتغيّرات |
| `FRONTEND_DASHBOARD_CATEGORY_ATTRIBUTE_DELETE_CONFIRM.md` | حذف صفات الفئة |
