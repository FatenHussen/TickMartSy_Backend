# ويب — كل التعديلات (نسخة نهائية — 26 آب 2026)

> **أرسلوا هذا الملف لفريق الويب فقط.**  
> يجمع **كل** التعديلات: Nav · أقسام · فئات · منتج · تسجيل · **فلاتر (تفصيلي)** · طلب سريع · نص تحميل · أسعار.  
> **آخر تحديث | Last Updated:** 2026-08-26

---

## الفهرس

1. [شريط التنقّل الديناميكي (Nav Menu)](#1-شريط-التنقّل-الديناميكي)
2. [عرض الصفحات (Page Builder)](#2-عرض-الصفحات-page-builder)
3. [صفحات الفئات](#3-صفحات-الفئات)
4. [منتجات شجرة الفئة (Category Subtree)](#4-منتجات-شجرة-الفئة)
5. [صفحة المنتج (shop_variants + country)](#5-صفحة-المنتج)
6. [التسجيل بدون إيميل](#6-التسجيل-بدون-إيميل)
7. [فلاتر المنتجات](#7-فلاتر-المنتجات)
8. [قسم الطلب السريع (حسب الصفحة)](#8-قسم-الطلب-السريع-حسب-الصفحة)
9. [نص تحميل التطبيق + عرض الأسعار](#9-نص-تحميل-التطبيق-وعرض-الأسعار)

---

## 1) شريط التنقّل الديناميكي

> المرجع: `FRONTEND_WEB_NAV_MENU.md`

### التغيير

الشريط العلوي **لم يعد ثابتًا بالكود**. يُجلب من API ويُعرض ديناميكيًا.

### Endpoint

```http
GET /api/user/nav-menu
Accept-Language: ar
```

عام (بدون توكن). يرجّع العناصر **المفعّلة فقط** مرتّبة.

### استجابة

```json
{
  "status": true,
  "data": [
    { "id": 1, "title": "الفئات", "type": "route", "icon": null, "order": 1, "open_in_new_tab": false, "target": { "route_key": "categories" } },
    { "id": 8, "title": "إلكترونيات", "type": "category", "icon": "https://...", "target": { "category_id": 12, "name": "إلكترونيات" } },
    { "id": 9, "title": "عروض رمضان", "type": "page", "target": { "page_id": 5, "slug": "ramadan-offers" } },
    { "id": 10, "title": "مدوّنتنا", "type": "url", "open_in_new_tab": true, "target": { "url": "https://example.com/blog" } }
  ]
}
```

### خريطة التنقّل

| `type` | القيمة | الوجهة |
|--------|--------|--------|
| `route` | `target.route_key` | مسار ثابت (انظر الجدول أدناه) |
| `category` | `target.category_id` | `/categories/{id}` → `GET /api/user/categories/{id}/page` |
| `brand` | `target.brand_id` | `/brands/{id}` |
| `page` | `target.slug` | `/pages/{slug}` |
| `url` | `target.url` | رابط خارجي (+ `open_in_new_tab`) |

### خريطة `route_key`

```js
const ROUTE_MAP = {
  home: "/",
  categories: "/categories",
  brands: "/brands",
  shops: "/shops",
  baskets: "/my-baskets",
  points: "/points",
  help: "/help",
  subscriptions: "/subscription-packages",
};
```

### React مثال

```jsx
function hrefFor(item) {
  switch (item.type) {
    case "route":    return ROUTE_MAP[item.target.route_key] ?? null;
    case "category": return `/categories/${item.target.category_id}`;
    case "brand":    return `/brands/${item.target.brand_id}`;
    case "page":     return `/pages/${item.target.slug ?? item.target.page_id}`;
    case "url":      return item.target.url;
    default:         return null;
  }
}
```

### المطلوب

- [ ] احذف القائمة الثابتة من الكود
- [ ] `GET /api/user/nav-menu` واعرض بالترتيب
- [ ] خريطة `route_key` → مسارات الويب
- [ ] `open_in_new_tab` مع `rel="noopener noreferrer"`
- [ ] fallback آمن لأي `route_key` غير معروف
- [ ] أعد الجلب عند تغيير اللغة

---

## 2) عرض الصفحات (Page Builder)

> المرجع: `FRONTEND_WEB_PAGE_BUILDER.md`

### Endpoints

| الغرض | Endpoint |
|-------|----------|
| أقسام صفحة عامة | `GET /api/user/sections?page_slug={slug}` |
| صفحة فئة | `GET /api/user/categories/{categoryId}/page` |

### شكل القسم

```json
{
  "id": 10,
  "name": { "ar": "...", "en": "..." },
  "type": "manual",
  "position": "main",
  "order": 1,
  "variant": "horizontal",
  "background_color": "#F7F7F7",
  "background_card_color": "#FFFFFF",
  "display_type_id": 3,
  "end_date": null,
  "discount": null,
  "discount_type": null,
  "see_more": { "page_slug": "products", "params": { "category_id": 5 } },
  "show_when": {},
  "action": { "page_slug": "product_details" },
  "items": [...]
}
```

- `end_date` / `discount` / `discount_type`: تُملأ فقط لأقسام `latest_flash_sale` — خلاف ذلك `null`.
- `show_when`: شروط عرض القسم — إذا فاضي يُعرض دائمًا.

### قواعد العرض: `layout` ثم `variant`

| حقل | المعنى | القيم |
|-----|--------|-------|
| `layout` | طريقة عرض القسم | `slider` \| `list` \| `grid` |
| `variant` | شكل الكارد جوا | `horizontal` \| `vertical` \| `square` |

التفاصيل: **`FRONTEND_SECTION_LAYOUT_AND_CARD.md`**.

1. `layout` يختار تخطيط القسم (سلايدر / ليست / شبكة).
2. `variant` يحدد شكل كل كارد:

| `variant` | شكل الكارد |
|-----------|------------|
| `horizontal` | كارد أفقي عريض (الافتراضي) |
| `vertical` | كارد رأسي ضيق |
| `square` | كارد مربّع (فئات) |

### البانرات

- `type: "manual"` مع عناصر بانرات
- الصورة في `item.image` **عرضية** — نسبة ≈ 16:6
- عنصر واحد = إعلان ثابت، عدة عناصر = سلايدر
- الضغط يفتح `item.link`
- استخدم `background_color` للقسم و `background_card_color` للكارد
- تجاهل قسمًا `items` فارغة

---

## 3) صفحات الفئات

> المرجع: `FRONTEND_WEB_CATEGORY_PAGES.md`

### Endpoint

```http
GET /api/user/categories/{categoryId}/page
```

```json
{
  "status": true,
  "data": {
    "category": { "id": 12, "name": { "ar": "إلكترونيات", "en": "Electronics" }, "children": [...] },
    "sections": [...]
  }
}
```

### المطلوب

- `sections` بنفس شكل أي صفحة → **نفس مكوّن عرض الأقسام**
- عنوان الصفحة = `category.name`
- `category.children` ممكن عرضها كـ tabs أو breadcrumb
- لا تفترض عددًا ثابتًا للأقسام
- كارد فئة يفتح `GET /categories/{id}/page` (تنقّل متداخل)

---

## 4) منتجات شجرة الفئة

> المرجع: `FRONTEND_WEB_CATEGORY_PRODUCT_SUBTREE.md`

### التغيير

```http
GET /api/user/products?category_id={id}
```

يرجع منتجات **هذه الفئة + كل الفروع بأي عمق**.

### مهم

- **ممنوع** فلترة محلية بـ `product.category_id === selectedCategoryId` (يحذف منتجات الفروع)
- الـ Backend طبّق فلتر الشجرة مسبقًا
- أرسل `category_id` للفئة الحالية فقط
- لا تشترط فئة ورقة
- عند تغيير الفئة أعد `page` إلى `1`
- كل الفلاتر الحالية تبقى وتُطبّق مع الشجرة

---

## 5) صفحة المنتج

> المرجع: `FRONTEND_WEB_PRODUCT_DETAIL_SHOP_VARIANTS.md`

### تغييرات الـ API

```http
GET /api/user/products/{id}
```

| الحقل | قبل | بعد |
|-------|-----|-----|
| `shop_variants` | ممكن `[]` | **دائمًا عنصر واحد على الأقل** |
| `country` | object `{ id, name: {ar,en} }` | **string** (`"تركيا"`) أو `null` |
| `shop_variants[].shop_id` | موجود دائمًا | ممكن **`null`** |
| `shop_variants[].id` | موجود دائمًا | ممكن **`null`** |

### الحالة العادية (مربوط بفرع)

```json
{
  "shop_variants": [{
    "id": 44, "variant_id": 44, "sku": "JEANS-RED",
    "attributes": [{ "attribute": "اللون", "value": "أزرق", "type": "color" }],
    "price": 25, "currency": "USD", "price_formatted": "$ 25",
    "quantity": 12, "shop_id": 1,
    "images": [{ "id": 373, "path": "https://..." }]
  }]
}
```

### حالة الـ fallback (بدون فرع)

```json
{
  "shop_variants": [{
    "id": null, "variant_id": null,
    "price": 20, "quantity": 100, "shop_id": null,
    "images": [...]
  }]
}
```

### المطلوب

```js
// لا تقرأ بدون حماية
const variants = product.shop_variants ?? [];
const variant = variants[0] ?? null;
const price = variant?.price ?? product.price;

// تفعيل السلة
const canAddToCart =
  Boolean(variant?.shop_id) && Boolean(variant?.id) && (variant?.quantity ?? 0) > 0;

// country صار string
product.country  // "تركيا" أو null

// attributes_map قد يكون فاضي
const attributesMap = product.attributes_map ?? [];
if (attributesMap.length > 0) { /* اعرض picker */ }
```

### Checklist

- [ ] لا وصول مباشر لـ `shop_variants[0].*` بدون `?.`
- [ ] `shop_id === null` → زر السلة معطّل، الصفحة تعرض
- [ ] `country` يُعرض كنص
- [ ] `attributes_map` فاضي → إخفاء المتغيّرات
- [ ] fallback الصور من صور المنتج
- [ ] `errorElement` مخصص لمسار `/product/:id`

---

## 6) التسجيل بدون إيميل

> المرجع: `FRONTEND_WEB_REGISTER_EMAIL_OPTIONAL.md`

### التغيير

| الحقل | قبل | بعد |
|-------|-----|-----|
| `phone` | مطلوب إذا ما في إيميل | **مطلوب دائمًا** |
| `email` | مطلوب إذا ما في هاتف | **اختياري** |
| OTP | SMS أو إيميل | **SMS دائمًا** على `phone` |

### Endpoint

```http
POST /api/user/auth/register
```

```json
{
  "name": "أحمد محمد",
  "phone": "0501234567",
  "password": "Passw0rd!",
  "city_id": 1,
  "governorate_id": 1
}
```

- **لا ترسل `email: ""`** — احذف المفتاح إذا فاضي
- `phone` أرقام فقط بدون `+` أو مسافات
- بعد التسجيل → شاشة OTP بالهاتف

### التحقق

```http
POST /api/user/auth/verify-otp
{ "phone": "0501234567", "code": "12345" }
```

### Checklist

- [ ] حقل الهاتف مطلوب + تنظيف الأرقام
- [ ] حقل الإيميل "(اختياري)" بدون `required`
- [ ] الإيميل الفاضي لا يُرسل
- [ ] حذف tabs/toggle "إيميل أو هاتف"
- [ ] بعد التسجيل → OTP بالهاتف
- [ ] `error` من 422 يُعرض مباشرة
- [ ] الملف الشخصي يتعامل مع `email = null`

---

## 7) فلاتر المنتجات (تفصيلي — اعتمدوه للتنفيذ)

نفس `GET /api/user/products` (عام، بدون توكن إلزامي). التوكن اختياري — يفعّل فقط `is_favorite`.

### قبل → بعد

| الموضوع | بعد |
|---------|-----|
| `category_id` | الفئة + **كل الفروع بأي عمق** — ممنوع فلترة محلية |
| الصفات | أي مستوى → صفات **الجذر** + `root_category_id` للكاش |
| جديد | `is_instant_delivery` |
| ترتيب | `sort_by`: `price_asc` \| `price_desc` \| `newest` \| `oldest` \| `rating` |
| بحث | `search` (ليس `name`) |
| بلد | أرسل `country` كنص — **لا** `country_id` على `/products` |
| أقسام API | merge(فلاتر القسم, query URL) والـ URL يغلّب |

### باراميترات تعمل

| Param | مثال | السلوك |
|-------|------|--------|
| `category_id` | `12` | الفئة + كل الأحفاد |
| `brand_id` | `7` | ماركة |
| `shop_id` | `3` | متوفر في الفرع |
| `price_min` / `price_max` | `100` | بعملة العرض؛ الباك يحوّل |
| `search` | `أرز` | اسم + وصف |
| `country` | `تركيا` | تطابق جزئي على نص البلد |
| `is_free_delivery` | `1` | توصيل مجاني |
| `is_instant_delivery` | `1` | توصيل فوري |
| `on_sale` | `1` | عليه خصم |
| `in_stock_only` | `1` | كمية > 0 |
| `attribute_values` | `31,40` أو `[]` | منطق **OR** |
| `type` | `trend` | قائمة جاهزة (انظر تحت) |
| `sort_by` | `newest` | بعد `type` إن وُجدا معاً |
| `page` / `per_page` | `1` / `15` | ترقيم |

**لا تستخدم على `/products`:** `name` · `country_id` · `sort_by=rating_desc` (استخدم `rating`).

أرسل فقط المفاتيح المفعّلة — لا `null` ولا `""`. للبوليان المفعّل أرسل `true`؛ عند إلغاء الخيار **احذف المفتاح**.

### صفات الفئة (chips)

```http
GET /api/user/categories/{categoryId}/attributes
```

```json
{
  "data": [{
    "id": 10,
    "category_id": 5,
    "root_category_id": 5,
    "name": { "ar": "اللون", "en": "Color" },
    "type": "color",
    "values": [{ "id": 31, "name": { "ar": "أحمر", "en": "Red" } }]
  }]
}
```

- اعرض chips فور فتح **أي** فئة (حتى الجذر)
- كاش بـ `root_category_id` — لا تعيد الطلب داخل نفس الشجرة
- قائمة فاضية = الجذر بلا صفات (مو خطأ)
- صفحة تفاصيل المنتج: استخدم `attributes_map` / `shop_variants` — **ليس** هذا الـ endpoint

```http
GET /api/user/products?category_id=12&attribute_values=31,40
GET /api/user/products?category_id=12&attribute_values[]=31&attribute_values[]=40
```

### `type` (قوائم جاهزة)

| قيمة | معنى |
|------|------|
| `new` | الأحدث |
| `trend` / `most_popular` | متغيّر trend + مبيعات |
| `top_rated` | أعلى تقييم |
| `offers` | خصم، مرتّب بالنسبة |
| `latest_flash_sale` | آخر فلاش سيل — إن ما في: قائمة فارغة |
| `recommended` / `for_you` | حالياً placeholder — لا تعتمد عليهما |
| `search_based` | يحتاج `search` |

### `sort_by`

| قيمة | ترتيب |
|------|--------|
| `price_asc` / `price_desc` | سعر |
| `newest` / `oldest` | تاريخ |
| `rating` | أعلى تقييم |

بدون `type` وبدون `sort_by` → `latest()`.

### مصادر الـ dropdowns

| فلتر | المصدر |
|------|--------|
| صفات | `/categories/{id}/attributes` |
| ماركة | `/api/user/brands` → `brand_id` |
| متجر | `/api/user/shops` → `shop_id` |
| بلد | `/api/user/countries` → أرسل **الاسم** في `country` |
| سعر | `price_min` / `price_max` يدوياً |

لا يوجد `GET /products/filter-options`.

### أمثلة

```http
GET /api/user/products?category_id=2&in_stock_only=1&is_free_delivery=1&sort_by=newest
GET /api/user/products?category_id=5&attribute_values=31,40&price_min=10&price_max=80
GET /api/user/products?on_sale=true&is_instant_delivery=true
GET /api/user/products?category_id=3&search=jeans&in_stock_only=true
```

### ممنوع

```js
// خطأ — يحذف منتجات الفروع
products.filter((p) => p.category_id === selectedCategoryId);
```

- لا تفلتر محلياً بعد الـ API
- لا تشترط leaf قبل إظهار الفلاتر/المنتجات
- `product.country` نص للعرض — ليس `{ name.ar }`
- أي تغيير فلتر → `page = 1`
- empty state من `pagination.total === 0` فقط

### Checklist فلاتر

- [ ] شريط فلاتر على صفحة الفئة **و** صفحة المنتجات
- [ ] `/attributes` على أي فئة + كاش `root_category_id`
- [ ] كل الفلاتر في نفس `GET /products`
- [ ] toggles: free / instant / on_sale / in_stock
- [ ] `price_min`/`price_max` + `sort_by`
- [ ] أقسام API: query URL يغلّب

---

## 8) قسم الطلب السريع (حسب الصفحة)

> المرجع التفصيلي: [`../custom-orders/web.md`](../custom-orders/web.md)

### التغيير

قسم «طلب سريع» يظهر على **الصفحات التي يختارها الأدمن من Settings** (افتراضي: `home` فقط) + زر الهيدر عند التفعيل.

المحتوى/الشكل مركزي من الإعدادات — **ليس** قسم Page Builder.

### Endpoint

```http
GET /api/user/settings
Accept-Language: ar
```

استخدم `data.quick_order`:

| حقل | معنى |
|-----|------|
| `is_enabled` | أخفِ الزر والقسم إن `false` |
| `page_ids` / `page_slugs` | اعرض القسم فقط إن الصفحة الحالية ضمن القائمة (افتراضي: `home`) |
| `background_image` / `background_color` | خلفية القسم |
| `card_background_color` / `card_variant` | تصميم كروت الخطوات |
| `badge` / `title` / `subtitle` / `cta` / `steps` | المحتوى |

```jsx
const qo = settings.quick_order;
const showSection =
  qo?.is_enabled && qo.page_slugs?.includes(currentPageSlug);

{qo?.is_enabled && <QuickOrderHeaderButton />}
{showSection && <QuickOrderSection config={qo} />}
```

الـ CTA يفتح إنشاء طلب: `POST /api/user/custom-order-requests` (تفاصيل الفلو في المرجع أعلاه).

### Checklist

- [ ] قراءة `quick_order` من settings
- [ ] احترام `is_enabled` (زر الهيدر + القسم)
- [ ] احترام `page_slugs` / `page_ids` حسب الصفحة الحالية
- [ ] خلفية صورة أو لون + ريسبونسيف

---

## 9) نص تحميل التطبيق + عرض الأسعار

### نص قسم Google Play / App Store

النص **ثابت في i18n** — مو من الـ API.

| | |
|--|--|
| **احذف** | «ومنتجات طازجة» |
| **بعد** | كل ما تحبه، يصلك بابتسامة. عروض رائعة وفرحة صغيرة في كل سلة. |

### الأسعار

- اعرض `*_formatted` أو `*_currencies` من الـ API
- **لا تحسب** سعر الصرف محلياً
- في صفحة المنتج: السعر بعد الخصم هو الأساسي؛ الأصلي مشطوب عند الخصم

```js
function formatDual(currencies) {
  if (!currencies) return '';
  return [currencies.USD?.formatted, currencies.SYP?.formatted]
    .filter(Boolean)
    .join(' / ');
}
```

---

## Checklist شامل (أرسلوه مع الملف)

- [ ] Nav Menu ديناميكي من `/api/user/nav-menu`
- [ ] عرض أقسام موحّد (`layout` ثم `variant`)
- [ ] صفحات الفئات عبر `/categories/{id}/page`
- [ ] منتجات الشجرة بدون فلترة محلية
- [ ] صفحة منتج: حماية `shop_variants` null + `country` string
- [ ] تسجيل: phone مطلوب، email اختياري، OTP SMS
- [ ] **فلاتر كاملة** (§7): attributes + toggles + سعر + ترتيب + كاش جذر
- [ ] طلب سريع من `settings.quick_order` (`is_enabled` + `page_slugs`)
- [ ] نص تحميل بدون «ومنتجات طازجة»
- [ ] أسعار من `*_formatted` / `*_currencies` فقط

---

## Backend (آمن)

```bash
php artisan migrate
```

> فلو الطلب السريع بالكامل: [`../custom-orders/web.md`](../custom-orders/web.md)

---

**آخر تحديث | Last Updated:** 2026-08-26
