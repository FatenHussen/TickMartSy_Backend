# داشبورد — كل التعديلات (نسخة نهائية)

> **أرسلوا هذا الملف لفريق الداشبورد فقط.**  
> Base: `/api/admin` + Admin token.  
> يجمع **كل** تعديلات الباك التي تحتاج تنفيذ في الداشبورد (مو بس المنتج).

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
15. [الطلب السريع — إعدادات + صفحات الظهور](#15-الطلب-السريع--إعدادات--صفحات-الظهور)
16. [استيراد منتجات من Excel](#16-استيراد-منتجات-من-excel)
17. [متغيّرات المنتج — صفات + Multi-select + خصم](#17-متغيّرات-المنتج--صفات--multi-select--خصم)

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

### إظهار / إخفاء قسم (Eye toggle)

> **الدليل الكامل:** [`../page-sections/dashboard.md`](../page-sections/dashboard.md)

> **لا تحذف القسم** إذا تريد إخفاءه مؤقتًا — استخدم `is_active`.

كل قسم في `GET /api/admin/pages/{id}` يرجع حقل **`is_active`**:

| `is_active` | السلوك |
|-------------|--------|
| `true` | يظهر في الموقع/التطبيق |
| `false` | مخفي عن المستخدمين — يبقى في الصفحة ويمكن إعادة إظهاره |

**في شاشة تفاصيل الصفحة** (`/sections/pages/details/{id}`):

- أيقونة **عين** بجانب كل قسم
- `is_active: true` → عين مفتوحة (مرئي)
- `is_active: false` → عين مغلقة / خط (مخفي) + تمييز بصري خفيف (opacity أو badge "Hidden")

**طريقة 1 — Toggle API (موصى بها للأيقونة):**

```http
POST /api/admin/toggle-status
Content-Type: application/json

{
  "type": "page_section",
  "id": 12,
  "is_active": 0
}
```

**طريقة 2 — PATCH مباشر:**

```http
PATCH /api/admin/page-sections/12
Content-Type: application/json

{ "is_active": false }
```

> **Preview:** `GET .../preview` يعرض الأقسام **النشطة فقط** (كما يراها المستخدم). قائمة الأقسام في تفاصيل الصفحة تعرض **الكل** مع `is_active` لتمكين التوغل.

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

### فورم المنتج

**سطر 1:** سعر `$` · سعر `ل.س` · نوع الخصم · قيمة الخصم · سعر بعد الخصم (**عرض فقط — يُحسب حيّاً**) · التكلفة  
**سطر 2:** الكمية · الوحدة

- لا تعرض `price` و `price_after_discount` كحقلين قابلين للتعديل معاً
- **لا ترسل** `price_after_discount` ولا `*_currencies`

#### حساب سعر بعد الخصم في الواجهة

```js
function priceAfterDiscount(price, discountType, discount) {
  const p = Number(price) || 0;
  const d = Number(discount) || 0;
  if (!p || !discountType || discountType === 'none' || d <= 0) return p;
  if (discountType === 'percentage') return Math.round((p - p * (d / 100)) * 100) / 100;
  if (discountType === 'fixed') return Math.max(0, Math.round((p - d) * 100) / 100);
  return p;
}
```

| `discount_type` | الحساب |
|-----------------|--------|
| `none` | = السعر |
| `percentage` | `price - price * (discount/100)` |
| `fixed` | `max(0, price - discount)` — المبلغ بالدولار |

#### مزامنة USD ↔ SYP

```js
// GET /api/admin/currencies → SYP.exchange_rate
const sypRate = currencies.find(c => c.code === 'SYP')?.exchange_rate ?? 1;
// كتب دولار → عبّي ليرة؛ كتب ليرة → عبّي دولار
// نفس المنطق لـ cost_price / cost_price_syp
```

إذا أُرسل `price` و `price_syp` معاً → الباك يعتمد **الدولار**.

```js
{
  price: usd || undefined,
  price_syp: !usd && syp ? syp : undefined,
  cost_price: costUsd || undefined,
  cost_price_syp: !costUsd && costSyp ? costSyp : undefined,
  discount_type, discount, quantity, unit_id,
}
```

- **`media`** و **`country_id`** اختياريان — لا تضع `required`

### حذف الفئات

| الغرض | Endpoint |
|-------|----------|
| ملخص الأثر | `GET /api/admin/categories/{id}/delete-impact` |
| العناصر المرتبطة | `GET /api/admin/categories/{id}/linked-items?page=1&per_page=10` |
| تنفيذ | `DELETE /api/admin/categories/{id}?confirm=true` |

1. حذف → `delete-impact`
2. نافذة: تبويب تنبيه + تبويب عناصر مرتبطة
3. موافقة → `DELETE ?confirm=true`
4. **`409` + `requires_confirmation` = تأكيد** — مو توست أحمر

| مفتاح | عند التأكيد |
|-------|-------------|
| `child_categories` | حذف الفرعية |
| `products` | حذف ناعم + فك ارتباط (الطلبات تبقى) |
| `baskets` | تُحذف |
| `pages` | تُحذف صفحة الفئة وأقسامها |
| `recipe_links` | فك ارتباط فقط |

---

## 12) بلد المنشأ — Select من كل دول العالم

- بلد المنشأ **قائمة منسدلة** (`country_id`) وليست حقل نص
- الخيارات: `GET /api/admin/countries` (كل الدول، بدون pagination)
- أرسل `country_id` فقط؛ املأ من `country_id` / `origin_country.id`
- **لا تستخدم** `country.ar` / `country.en`

```bash
php artisan db:seed --class=CountrySeeder
```

---

## 13) بلدان المبيع — بدون رفع أيقونة + افتراضي سوريا

**المشكلة:** فورم بلد المبيع يعرض رفع ملف بعنوان خاطئ.

| الحقل | في الفورم؟ |
|-------|------------|
| `name.ar` / `name.en` | نعم |
| `is_active` | نعم |
| `icon` | **لا — احذفوه** (من السيدر، عرض فقط) |

- بدون `sale_country_id` عند إنشاء منتج → الباك يضع **سوريا**
- في select المنتج: افتراضي **سوريا**

```bash
php artisan db:seed --class=SaleCountrySeeder
```

---

## 14) قناة البيع — للموقع أو ربط بمتجر

> **احذفوا** صف الراديو «مستودعاتي / خارجي» — مو موجود بالباك ويتداخل مع `sale_channel`.

| واجهة | `sale_channel` | السلوك |
|-------|----------------|--------|
| **للموقع** (افتراضي) | `platform` | لا متجر/بائع؛ ربط تلقائي بفرع المنصة |
| **ربط بمتجر** | `shop` | بائع اختياري للفلترة + فرع إلزامي عبر `shop_variants` |

| القناة | وقت التسليم |
|--------|-------------|
| للموقع | نص ثابت للمنصة — لا حقل إدخال |
| ربط بمتجر | أظهروا `delivery_time` |

### قواعد

- للموقع: `sale_channel=platform` و**لا ترسل** `shop_variants` ولا `vendor_id`
- لمتجر: `sale_channel=shop` + `shop_variants` (فرع واحد على الأقل) وإلا **422**
- GET يرجع `sale_channel` للفورم والقائمة (badge: للموقع / متجر)
- تحويل لموقع: أرسل `sale_channel=platform` فقط
- تعديل اسم فقط: **لا ترسل** `sale_channel` ولا `shop_variants`
- شرط التشغيل: فرع لبائع المنصة (`vendor_id=1`) مع `is_default`
- تحذير «غير مرتبط بفرع» فقط لـ `shop` بدون روابط

```text
# موقع
sale_channel=platform
price=25
quantity=10

# متجر
sale_channel=shop
shop_variants[0][shop_id]=5
shop_variants[0][variant_index]=0
```

---

## 15) الطلب السريع — إعدادات + صفحات الظهور

> التفصيل الكامل (تحويل الطلبات + convert): [`../custom-orders/dashboard.md`](../custom-orders/dashboard.md)

### الفكرة

المحتوى والشكل **مركزي من Settings** (ليس قسم Page Builder).  
الأدمن يختار:
1. تفعيل/إيقاف الميزة (`quick_order_enabled`) — يشمل زر الهيدر
2. **أي صفحات يظهر عليها القسم** (`quick_order_page_ids`)
3. خلفية، ألوان، شكل الكروت، نصوص AR/EN، خطوات

### Endpoints

**Base:** `/api/admin/settings`  
**صلاحيات:** `setting.view` / `setting.update`  
قائمة الصفحات للـ multi-select: `GET /api/admin/pages`

| Key | Type | وصف |
|-----|------|-----|
| `quick_order_enabled` | boolean | **إظهار/إخفاء** القسم + زر الهيدر |
| `quick_order_page_ids` | json | مصفوفة `pages.id` — الصفحات التي يظهر عليها القسم. الافتراضي = صفحة `home` فقط. `[]` = لا يظهر على أي صفحة |
| `quick_order_background_image` | file | صورة خلفية (`multipart` حقل `value`) |
| `quick_order_background_color` | string | لون احتياطي (مثال `#FFE8D6`) |
| `quick_order_card_background_color` | string | خلفية كروت الخطوات |
| `quick_order_card_variant` | string | `horizontal` \| `vertical` \| `square` |
| `quick_order_badge` / `title` / `subtitle` / `cta` | json | `{ "ar": "...", "en": "..." }` |
| `quick_order_steps` | json | مصفوفة خطوات (حتى 6) |

```http
PUT /api/admin/settings/quick_order_enabled
{ "value": false }
```

```http
PUT /api/admin/settings/quick_order_page_ids
{ "value": [1, 5, 12] }
```

```http
PUT /api/admin/settings/quick_order_background_image
Content-Type: multipart/form-data
value: <image file>
```

### ما يقرأه التطبيق/الويب

```http
GET /api/user/settings → data.quick_order
```

| حقل | معنى |
|-----|------|
| `is_enabled` | ماستر سويتش (قسم + زر الهيدر) |
| `page_ids` | IDs الصفحات المعتمدة |
| `page_slugs` | نفس الصفحات كـ slug |

**قاعدة العرض عند العميل:** إن `is_enabled` و (الصفحة الحالية ∈ `page_slugs` أو ∈ `page_ids`) → اعرض القسم.

### UI مقترح

تبويب «طلب سريع» ضمن الإعدادات:
- سويتش تفعيل
- **multi-select صفحات** من `GET /api/admin/pages`
- صورة خلفية + ألوان
- شكل الكارد
- نصوص AR/EN
- محرر الخطوات

**مهم:** استلام الطلبات وتحويلها (`convert` / `cancel`) مسار منفصل تحت `/api/admin/custom-order-requests` — انظر [`../custom-orders/dashboard.md`](../custom-orders/dashboard.md).

---

## 16) استيراد منتجات من Excel

استيراد جماعي من قالب SPBS (أعمدة عربية ثابتة). الكود يقرأ **هذه الأعمدة فقط** ويتجاهل أي عمود زيادة.

### Endpoints

| Method | Path | صلاحية | وصف |
|--------|------|--------|-----|
| `GET` | `/api/admin/products/import-template` | `product.view` | تنزيل قالب `.xlsx` |
| `POST` | `/api/admin/products/import` | `product.create` | رفع الملف والاستيراد |

```http
POST /api/admin/products/import
Content-Type: multipart/form-data
file: <products.xlsx>
```

### أعمدة القالب (Mapping)

| عمود الإكسل | حقل النظام |
|-------------|------------|
| كود المنتج | `sku` |
| الفئة الرئيسية | اسم فئة → `category_id` (مطابقة دقيقة ar/en) |
| اسم المنتج عربي | `name.ar` |
| اسم المنتج انكليزي | `name.en` |
| الوصف المختصر | `description.ar` |
| العلامة التجارية | اسم براند → `brand_id` (اختياري) |
| الباركود | `barcode` |
| السعر دولار | `price` (USD) |
| السعر سوري | يتحوّل لـ USD إذا ما في دولار |
| نوع الخصم | `discount_type` (`none` / `percentage` / `fixed` أو بدون / نسبة / ثابت) |
| الخصم | `discount` |
| الكمية | `quantity` |
| تاريخ انتهاء الصلاحية | `expiry_date` |

### Upsert

1. إن وُجد **باركود** مطابق → تحديث  
2. وإلا إن وُجد **كود منتج (sku)** مطابق → تحديث  
3. وإلا → إضافة جديدة  

عند التحديث: الخلايا الفارغة **لا تمسح** القيم الموجودة.  
المنتجات بسيطة (`sale_channel=platform`)؛ الباك ينشئ/يزامن الـ variant الافتراضي (سعر + كمية + باركود + sku).

### تحقق عند الإضافة

- اسم عربي + إنكليزي مطلوبان  
- الفئة مطلوبة وموجودة (اسم فريد)  
- سعر دولار أو سوري (≥ 0)  
- كمية مطلوبة (≥ 0 عدد صحيح)  

الصفوف الخاطئة تُتخطّى؛ الصحيحة تُستورد.

### استجابة

```json
{
  "status": true,
  "message": "تم استيراد المنتجات",
  "data": {
    "created": 10,
    "updated": 3,
    "failed": [
      { "row": 5, "errors": ["الفئة الرئيسية غير موجودة: ألبان"] }
    ]
  }
}
```

### UI مطلوب

- زر **تنزيل القالب** → `GET import-template`
- زر **استيراد من Excel** → رفع ملف → عرض ملخص: أُضيف / حُدّث / فشل (رقم الصف + السبب)
- لا Mapping يدوي من الواجهة — الأعمدة ثابتة من القالب

---

## Checklist شامل

### صفحات وأقسام
- [ ] Page Builder: صفحات + أقسام + reorder + preview
- [ ] أقسام مستقلة (بدون `page_id` عند الإنشاء)
- [ ] صفحات الفئات تلقائية — لا إنشاء/حذف يدوي

### Nav
- [ ] CRUD + reorder لـ nav-menu-items

### منتج / متغيّرات / فئات
- [ ] منتج لأي مستوى فئة؛ صفات من الجذر
- [ ] سعر وكمية على الـ variant؛ لا price/qty داخل shop_variants
- [ ] variants/shop_variants = replace؛ احفظ variant.id
- [ ] حذف متغيّر/صفة/فئة مع confirm + 409
- [ ] أسعار: بعد الخصم حيّ؛ USD↔SYP؛ لا ترسل currencies
- [ ] بلد المنشأ Select؛ media اختياري
- [ ] بلد مبيع بدون icon؛ افتراضي سوريا
- [ ] sale_channel فقط (موقع/متجر) — احذف مستودعاتي/خارجي
- [ ] استيراد Excel: تنزيل قالب + رفع + ملخص created/updated/failed

### طلب سريع
- [ ] إعدادات: سويتش `quick_order_enabled` + multi-select `quick_order_page_ids` + خلفية + كروت + نصوص
- [ ] قائمة الصفحات من `GET /api/admin/pages`
- [ ] حفظ `PUT /api/admin/settings/quick_order_page_ids` بمصفوفة IDs
- [ ] شاشة قائمة/تفاصيل custom-order-requests + convert/cancel

---

## أوامر التشغيل (آمنة — ما تمسح ولا تعيد كتابة داتا قديمة)

```bash
# 1) هيكل فقط: أعمدة + إعدادات quick_order الناقصة + sale_channel
php artisan migrate

# 2) صلاحيات ناقصة فقط (CustomOrderRequest …) — firstOrCreate
php artisan db:seed --class=RolePermissionSeeder

# 3) دول منشأ ناقصة فقط — ما يغيّر is_active/أسماء موجودة
php artisan db:seed --class=CountrySeeder

# 4) بلدان مبيع ناقصة + يعبّي icon إن فاضي فقط
php artisan db:seed --class=SaleCountrySeeder
```

**لا تشغّلوا:**

```bash
php artisan migrate:fresh
php artisan migrate:refresh
php artisan db:seed          # كامل — خطر
php artisan db:seed --class=SettingSeeder   # غير لازم؛ الـ migrate يضيف quick_order
```

Migrations آب 2026:

- `2026_08_26_103600_add_quick_order_settings` — مفاتيح ناقصة فقط
- `2026_08_26_131500_add_quick_order_page_ids_setting` — صفحات الظهور (`quick_order_page_ids`)
- `2026_08_26_104500_make_product_category_id_nullable_for_category_delete`
- `2026_08_26_111600_add_sale_channel_to_products_table` — عمود جديد + تعبئة من `vendor_id`

---

## 17) متغيّرات المنتج — صفات + Multi-select + سعر + خصم

> **الدليل الكامل:** [`product-variants-dashboard.md`](product-variants-dashboard.md)

### ملخص سريع

| البند | التفاصيل |
|-------|----------|
| **Single / Multi** | **أول متغيّر = Single** (صف واحد) · **من الثاني = Multi** (عدة صفوف) |
| **اسم المتغيّر** | **محذوف** — لا `name.ar` / `name.en`؛ الهوية من الصفات + SKU |
| صفات الفئة | `GET /api/admin/category-attributes?category_id={id}` عند المستوى 1 |
| إضافة متغيّر | `variantsTable.length === 0` → Single · وإلا → Multi |
| السعر | **`variants[].price` ($)** + **`variants[].price_syp` (ل.س)** — مزامنة تلقائية |
| سعر بعد الخصم | **عرض فقط** — يُحسب حيّاً في الواجهة |
| الكمية | **`variants[].quantity`** — «الكمية المتوفرة» |
| الباركود | **`variants[].barcode`** لكل متغيّر |
| الخصم | **`variants[].discount` + `discount_type`** لكل متغيّر |
| موعد التسليم | **`delivery_time`** على المنتج — ليس per variant |
| SKU | توليد من الواجهة (الباك لا يولّد تلقائيًا) |

### Migration

```bash
php artisan migrate   # 2026_08_30_120000_add_discount_to_product_variants_table
```

