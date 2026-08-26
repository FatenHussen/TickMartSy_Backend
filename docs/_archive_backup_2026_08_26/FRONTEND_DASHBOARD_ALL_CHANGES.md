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
11. [أسعار المنتج (USD/SYP) + حذف الفئات بتأكيد](#11-أسعار-المنتج-وحذف-الفئات)
12. [بلد المنشأ — Select من كل دول العالم](#12-بلد-المنشأ--select-من-كل-دول-العالم)
13. [بلدان المبيع — بدون رفع أيقونة + افتراضي سوريا](#13-بلدان-المبيع--بدون-رفع-أيقونة--افتراضي-سوريا)
14. [قناة البيع — للموقع أو ربط بمتجر](#14-قناة-البيع--للموقع-أو-ربط-بمتجر)

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
   - `shop_variants: []` → تُحذف الروابط ثم يُعاد الربط بفرع البائع الافتراضي (`is_default`)

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

يُنشأ **متغيّر افتراضي** تلقائيًا من بيانات المنتج.

> قناة البيع (موقع / متجر) صارت خيار صريح — انظر **§14**.

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

## 11) أسعار المنتج (USD/SYP) + حذف الفئات بتأكيد

> المرجع: `FRONTEND_DASHBOARD_PRODUCT_PRICE_AND_CATEGORY_DELETE.md`  
> حذف الفئات بالتفصيل: `FRONTEND_DASHBOARD_CATEGORY_DELETE_CONFIRM.md`

### فورم المنتج

- صف واحد: سعر $ + سعر ل.س + نوع خصم + خصم + سعر بعد الخصم (read-only) + تكلفة
- صف للكمية والوحدة
- لا تعرض `price` و `price_after_discount` كحقلين قابلين للتعديل معاً
- يمكن الإرسال بـ `price_syp` / `cost_price_syp` (الـ API يحوّل لدولار)
- **صورة المنتج (`media`)** و**بلد المنشأ (`country_id`)** اختياريان عند الإنشاء والتعديل — لا تضع `required` عليهما في الفورم
- **بلد المنشأ = Select فقط** (مش حقل نص): حمّل الخيارات من `GET /api/admin/countries` وأرسل `country_id` (رقم الدولة). القيمة الحالية من `country_id` أو `origin_country.id` — لا تستخدم `country.ar` / `country.en`

### حذف الفئات

1. `GET /api/admin/categories/{id}/delete-impact`
2. `GET /api/admin/categories/{id}/linked-items`
3. `DELETE /api/admin/categories/{id}?confirm=true`
4. اعتبر `409` + `requires_confirmation` طلب تأكيد (مو توست أحمر)

---

## 12) بلد المنشأ — Select من كل دول العالم

> المرجع: `FRONTEND_DASHBOARD_PRODUCT_PRICE_AND_CATEGORY_DELETE.md` (قسم بلد المنشأ)

### التغيير

- بلد المنشأ **قائمة منسدلة** (`country_id`) وليست حقل نص عربي/إنجليزي
- الخيارات من `GET /api/admin/countries` (كل الدول، بدون pagination)
- أرسل `country_id` فقط عند الحفظ؛ املأ القيمة من `country_id` / `origin_country.id`
- بعد السحب: `php artisan db:seed --class=CountrySeeder`

---

## 13) بلدان المبيع — بدون رفع أيقونة + افتراضي سوريا

> المرجع: `FRONTEND_LATEST_DASHBOARD_2026_08_26.md`

### التغيير

- فورم إنشاء/تعديل بلد المبيع: **احذف حقل رفع الأيقونة** بالكامل (كان يظهر بنص خاطئ)
- الحقول في الفورم فقط: `name.ar` / `name.en` + `is_active`
- `icon` يأتي من السيدر (إيموجي علم مثل `🇸🇾`) — الـ API ما زال يرجّعه للعرض فقط؛ **لا ترسله**
- عند إنشاء منتج بدون `sale_country_id` → الباك يضع **سوريا** تلقائياً
- في select بلد المبيع بالمنتج: اجعل **سوريا** الخيار الافتراضي

```bash
php artisan db:seed --class=SaleCountrySeeder
```

---

## 14) قناة البيع — للموقع أو ربط بمتجر

> المرجع: `FRONTEND_DASHBOARD_PRODUCT_SALE_CHANNEL.md`

### الفكرة

خياران في فورم المنتج — الأدمن ما يدخل متجر إلا إذا اختار الربط:

| واجهة | `sale_channel` | السلوك |
|-------|----------------|--------|
| **للموقع** (افتراضي) | `platform` | لا متجر/بائع في الفورم؛ ربط تلقائي بفرع المنصة |
| **ربط بمتجر** | `shop` | select فرع إلزامي عبر `shop_variants` |

### قواعد

- للموقع: أرسل `sale_channel=platform` و**لا ترسل** `shop_variants`
- لمتجر: `sale_channel=shop` + `shop_variants` (فرع واحد على الأقل) وإلا **422**
- GET يرجع `sale_channel` للفورم والقائمة
- شرط التشغيل: فرع لبائع المنصة (`vendor_id=1`) مع `is_default`

---

## أوامر التشغيل (بعد سحب التعديلات)

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=CategoryPagesBackfillSeeder
php artisan db:seed --class=NavMenuSeeder
php artisan db:seed --class=CountrySeeder
php artisan db:seed --class=SaleCountrySeeder
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
| `FRONTEND_DASHBOARD_PRODUCT_SALE_CHANNEL.md` | قناة البيع موقع / متجر |
| `FRONTEND_DASHBOARD_VARIANT_DELETE_CONFIRM.md` | حذف المتغيّرات |
| `FRONTEND_DASHBOARD_CATEGORY_ATTRIBUTE_DELETE_CONFIRM.md` | حذف صفات الفئة |
| `FRONTEND_DASHBOARD_CATEGORY_DELETE_CONFIRM.md` | حذف الفئات مع تأكيد |
| `FRONTEND_DASHBOARD_PRODUCT_PRICE_AND_CATEGORY_DELETE.md` | أسعار USD/SYP + حذف الفئات |
