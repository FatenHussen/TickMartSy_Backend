# كل تعديلات الويب (React/Next.js) — ملخّص شامل

هذا المستند يجمع **كل التعديلات** التي تحتاج تنفيذ في الواجهة الويب.

> **آخر تحديث للفلاتر:** 19 آب 2026 — التفصيل في `FRONTEND_WEB_FILTERS.md` (مطابق للكود الحالي، وليس `PRODUCTS_FILTERS_API.md`).

---

## الفهرس

1. [شريط التنقّل الديناميكي (Nav Menu)](#1-شريط-التنقّل-الديناميكي)
2. [عرض الصفحات (Page Builder)](#2-عرض-الصفحات-page-builder)
3. [صفحات الفئات](#3-صفحات-الفئات)
4. [منتجات شجرة الفئة (Category Subtree)](#4-منتجات-شجرة-الفئة)
5. [صفحة المنتج (shop_variants + country)](#5-صفحة-المنتج)
6. [التسجيل بدون إيميل](#6-التسجيل-بدون-إيميل)
7. [فلاتر المنتجات](#7-فلاتر-المنتجات)

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
```

### قواعد العرض حسب `variant`

**الويب:** كل الأقسام = **سلايدر أفقي**. `variant` يحدّد شكل الكارد فقط:

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

## 7) فلاتر المنتجات

> المرجع: `FRONTEND_WEB_FILTERS.md` — **هذا هو آخر تحديث؛ اعتمدوه للتنفيذ.**

### التغيير

نفس `GET /api/user/products` (عام، بدون توكن إلزامي). تغيّر السلوك + باراميترات:

| الموضوع | بعد |
|---------|-----|
| `category_id` | الفئة + **كل الفروع** — ممنوع فلترة محلية |
| الصفات | `GET /categories/{id}/attributes` لأي مستوى → صفات **الجذر** |
| جديد | `is_instant_delivery` |
| ترتيب يعمل | `sort_by`: `price_asc` \| `price_desc` \| `newest` \| `oldest` \| `rating` |
| بحث | `search` (ليس `name`) |
| بلد في القائمة | أرسل `country` كنص. لا ترسل `country_id` على `/products` |

### مثال

```http
GET /api/user/products?category_id=2&attribute_values=31,40&in_stock_only=1&is_instant_delivery=1&sort_by=newest
```

```http
GET /api/user/categories/2/attributes
```

كاش الصفات بـ `root_category_id`. لا تعيد الطلب داخل نفس الشجرة.

### Checklist

- [ ] chips من `/attributes` على الجذر والفرعية
- [ ] كل الفلاتر في طلب المنتجات نفسه
- [ ] `is_free_delivery` / `is_instant_delivery` / `on_sale` / `in_stock_only`
- [ ] `price_min` / `price_max` بعملة العرض
- [ ] تغيير فلتر يعيد `page=1`
- [ ] أقسام API: query الـ URL يغلّب فلاتر القسم

---

## أوامر التشغيل (Backend)

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
| `FRONTEND_WEB_NAV_MENU.md` | شريط التنقّل |
| `FRONTEND_WEB_PAGE_BUILDER.md` | عرض الصفحات |
| `FRONTEND_WEB_CATEGORY_PAGES.md` | صفحات الفئات |
| `FRONTEND_WEB_CATEGORY_PRODUCT_SUBTREE.md` | منتجات شجرة الفئة |
| `FRONTEND_WEB_PRODUCT_DETAIL_SHOP_VARIANTS.md` | صفحة المنتج |
| `FRONTEND_WEB_REGISTER_EMAIL_OPTIONAL.md` | التسجيل بدون إيميل |
| `FRONTEND_WEB_FILTERS.md` | فلاتر المنتجات (آخر تحديث 19 آب 2026) |
