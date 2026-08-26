# كل تعديلات Flutter — ملخّص شامل (آخر تحديث)

> **⚠️ تم دمج كل ملفات Flutter في ملف واحد:** [`FRONTEND_FLUTTER_COMPLETE.md`](./FRONTEND_FLUTTER_COMPLETE.md)  
> **ارسلوا هذا الملف فقط** لفريق Flutter (بناء من الصفر). هذا الملف أدناه للمرجع التفصيلي فقط.

هذا المستند يجمع **كل التعديلات** المطلوبة في تطبيق Flutter، بما فيها آخر تحديث **فصل `layout` / `variant` / `display_type_id`** (آب 2026).

---

## الفهرس

1. [شريط التنقّل الديناميكي (Nav Menu)](#1-شريط-التنقّل-الديناميكي)
2. [الصفحات والأقسام (Page Builder + layout)](#2-الصفحات-والأقسام)
3. [صفحات الفئات](#3-صفحات-الفئات)
4. [الفئات الدائرية (Circular Categories)](#4-الفئات-الدائرية)
5. [منتجات شجرة الفئة (Category Subtree)](#5-منتجات-شجرة-الفئة)
6. [وراثة صفات الفئة](#6-وراثة-صفات-الفئة)
7. [صفحة المنتج (shop_variants + country)](#7-صفحة-المنتج)
8. [التسجيل بدون إيميل](#8-التسجيل-بدون-إيميل)
9. [فلاتر المنتجات](#9-فلاتر-المنتجات)
10. [عرض الأسعار دولار + ليرة](#10-عرض-الأسعار)

---

## 1) شريط التنقّل الديناميكي

> المرجع: `FRONTEND_FLUTTER_NAV_MENU.md`

### التغيير

الشريط العلوي **لم يعد ثابتًا**. يُجلب من API.

### Endpoint

```http
GET /api/user/nav-menu
Accept-Language: ar
```

عام (بدون توكن). يرجّع العناصر **المفعّلة فقط** مرتّبة حسب `order`.

### Dart Model

```dart
class NavMenuItem {
  final int id;
  final String title;
  final String type; // route | category | brand | page | url
  final String? icon;
  final int order;
  final bool openInNewTab;
  final Map<String, dynamic> target;

  factory NavMenuItem.fromJson(Map<String, dynamic> json) => NavMenuItem(
    id: json['id'], title: json['title'], type: json['type'],
    icon: json['icon'], order: json['order'],
    openInNewTab: json['open_in_new_tab'] ?? false,
    target: (json['target'] as Map?)?.cast<String, dynamic>() ?? {},
  );
}
```

### التنقّل

| `type` | الوجهة |
|--------|--------|
| `route` | `target.route_key` → `home` \| `categories` \| `brands` \| `shops` \| `baskets` \| `points` \| `help` \| `subscriptions` |
| `category` | `CategoryPageScreen(categoryId: target.category_id)` |
| `brand` | شاشة الماركة |
| `page` | شاشة Page Builder (`slug` أو `page_id`) |
| `url` | فتح خارجي (`open_in_new_tab`) |

### Checklist

- [ ] احذف القائمة الثابتة
- [ ] `GET /api/user/nav-menu` واعرض بالترتيب
- [ ] خريطة `route_key` → شاشات
- [ ] fallback آمن لأي `route_key` غير معروف
- [ ] أعد الجلب عند تغيير اللغة
- [ ] إذا `data` فاضية → أخفِ الشريط

---

## 2) الصفحات والأقسام

> المراجع: `FRONTEND_FLUTTER_PAGE_BUILDER.md` · `FRONTEND_FLUTTER_SECTIONS_TODAY.md` · `FRONTEND_FLUTTER_SECTIONS_COMPLETE.md`

### Endpoints

| الغرض | Endpoint |
|-------|----------|
| صفحة عامة (رئيسية / عروض...) | `GET /api/user/sections?page_slug={slug}` |
| صفحة فئة (أي مستوى) | `GET /api/user/categories/{categoryId}/page` |

**قاعدة ذهبية:** ويدجت **واحد** لعرض الأقسام — استخدمه في الرئيسية وصفحة الفئة وكل `page_slug`.

### آخر تحديث: فصل 3 مفاهيم (مهم)

| الحقل | المعنى | القيم |
|-------|--------|-------|
| **`layout`** | طريقة عرض **القسم كامل** | `slider` \| `list` \| `grid` |
| **`variant`** | شكل **الكارد داخل** القسم | `horizontal` \| `vertical` \| `square` |
| **`display_type_id`** | نوع **المحتوى** | 1=banner, 2=product, 3=shop, 4=basket, 5=schedule-basket, 6=brand, 7=recipe, 8=category |

**غلط قديم (احذفوه):** `variant == horizontal` → سلايدر. **صار خطأ.**  
السلايدر = `layout: slider`، شكل الكارد = `variant`.

### Fallback

```dart
final layout = section.layout ?? 'slider';
final cardVariant = section.variant ?? 'horizontal';
```

### شكل القسم الكامل

```json
{
  "id": 10,
  "name": { "ar": "...", "en": "..." },
  "type": "manual",
  "content_type": "product",
  "manual_model": "product",
  "api_method": null,
  "position": "before",
  "order": 1,
  "layout": "slider",
  "variant": "vertical",
  "display_type_id": 2,
  "is_default": false,
  "background_color": "#F7F7F7",
  "background_card_color": "#FFFFFF",
  "end_date": null,
  "discount": null,
  "discount_type": null,
  "see_more": { "page_slug": "products", "params": { "category_id": 5 } },
  "show_when": {},
  "action": { "page_slug": "product_details" },
  "items": [...]
}
```

- `end_date` / `discount` / `discount_type`: لأقسام flash sale فقط — غير ذلك `null`.
- `show_when`: فاضي/`{}` = اعرض دائمًا.
- لا تعرض قسمًا `items` فارغة.

### `layout` → ويدجت القسم

| `layout` | Widget |
|----------|--------|
| `slider` | `ListView(scrollDirection: Axis.horizontal)` — **افتراضي** |
| `list` | `ListView` / `Column` عمودي |
| `grid` | `GridView` |

### `variant` → شكل الكارد فقط

| `variant` | الشكل |
|-----------|-------|
| `horizontal` | كارد عرضي عريض (بانرات) |
| `vertical` | كارد رأسي (منتجات) |
| `square` | كارد مربع (فئات) |

### مثال ويدجت موحّد

```dart
Widget buildSection(Section section) {
  if (section.items.isEmpty) return const SizedBox.shrink();

  final layout = section.layout ?? 'slider';
  final cardVariant = section.variant ?? 'horizontal';

  return switch (layout) {
    'list' => SectionListView(items: section.items, cardVariant: cardVariant),
    'grid' => SectionGridView(items: section.items, cardVariant: cardVariant),
    _ => SectionSliderView(items: section.items, cardVariant: cardVariant),
  };
}
```

### البانرات

- غالباً `layout: slider` + `variant: horizontal`
- صورة **عرضية** → `AspectRatio` ≈ 16/6
- عنصر واحد = بانر ثابت، عدة = `PageView`/carousel
- الضغط → `item.link`

### Checklist

- [ ] الموديل فيه `layout` + `variant` + `display_type_id`
- [ ] فرّع على `layout` أولاً، ثم مرّر `variant` للكارد
- [ ] احذف المنطق القديم (variant = تخطيط)
- [ ] fallback: بدون `layout` → `slider`
- [ ] نفس ويدجت الأقسام لـ `page_slug` و`categories/{id}/page`
- [ ] `see_more` → شاشة قائمة مع `params`
- [ ] `action.page_slug` → شاشة التفاصيل

---

## 3) صفحات الفئات

> المرجع: `FRONTEND_FLUTTER_CATEGORY_PAGES.md`

### Endpoint

```http
GET /api/user/categories/{categoryId}/page
```

```json
{
  "data": {
    "category": { "id": 12, "name": { "ar": "...", "en": "..." }, "children": [...] },
    "sections": [...]
  }
}
```

### الشاشة

1. نادِ `categories/{id}/page`
2. AppBar: `category.name`
3. `ListView`/`CustomScrollView` يبني `sections` عبر **مُعرّض الأقسام الموحّد** (قسم 2)

### التنقّل

| من | إلى |
|----|-----|
| كارد فئة | `CategoryPageScreen(categoryId: item.id)` |
| كارد منتج | `ProductDetailScreen` |
| `see_more` | شاشة القائمة مع `see_more.params` |

### Checklist

- [ ] لا تفترض عددًا ثابتًا للأقسام
- [ ] رتّب حسب `order`
- [ ] كاش per `categoryId` + `RefreshIndicator`

---

## 4) الفئات الدائرية

> المرجع: `FRONTEND_FLUTTER_CATEGORIES_CIRCULAR.md`

### التغيير

| قبل | بعد |
|-----|-----|
| منتجات على الأوراق فقط | منتج على أي مستوى |
| دوائر للجذور فقط | **نفس الدائرة لكل مستوى** |
| الأب بدون منتجات مباشرة | `category_id` يرجع المنتجات + كل الأحفاد |

### APIs

| الغرض | Endpoint |
|-------|----------|
| الجذور | `GET /api/user/categories` |
| أبناء فئة | `GET /api/user/categories?parent_id={id}` |
| منتجات | `GET /api/user/products?category_id={id}` |
| صفات | `GET /api/user/categories/{id}/attributes` |

> **ملاحظة:** لا يوجد `GET /api/user/categories/{id}` لفئة واحدة.  
> للأبناء استخدم `?parent_id=`. بيانات الفئة (اسم/أيقونة) تأتي من الكارد اللي ضغطته.

### حقول جديدة على الفئة

| الحقل | النوع |
|-------|-------|
| `parent_id` | int / null |
| `children_count` | int |
| `has_children` | bool |
| `children[].icon` | string (URL) |

### UI

```
┌─────────────────────────────┐
│  ← Food                     │
│  (○) Grains  (○) Veg        │ ← دوائر (أبناء)
│  ── Products ──              │
│  [card] [card] [card]        │ ← منتجات
└─────────────────────────────┘
```

- `has_children = true` → نفس الشاشة مع `?parent_id=`
- منتجات للفئة الحالية **تحت الدوائر دائمًا**
- نفس `CategoryCircleTile` لكل مستوى (حتى عمق 6)

### Checklist

- [ ] نفس الدائرة للجذور والفرعيات
- [ ] `has_children` → `?parent_id=`
- [ ] منتجات + دوائر معًا (لا تخفي المنتجات إذا في أبناء)
- [ ] الصفات من `/categories/{id}/attributes` لأي مستوى

---

## 5) منتجات شجرة الفئة

> المرجع: `FRONTEND_FLUTTER_CATEGORY_PRODUCT_SUBTREE.md`

### السلوك

```http
GET /api/user/products?category_id={categoryId}&page=1&per_page=10
```

يرجع منتجات **الفئة الحالية + كل الفروع بأي عمق** — طلب واحد، بدون endpoint جديد.

### ممنوع

```dart
// خطأ: يحذف منتجات الفروع
final visible = products
    .where((p) => p.categoryId == selectedCategoryId)
    .toList();
```

الـ Backend طبّق فلتر الشجرة مسبقًا — **لا تفلتر محلياً**.

### القواعد

- طلب **واحد** بـ `category_id` للفئة المفتوحة
- لا تشترط `has_children == false` لجلب المنتجات
- عند تغيير الفئة: امسح القائمة، `page = 1`، أعد الطلب
- load more: نفس `category_id` + الصفحة التالية
- empty state فقط عند: `!isLoading && products.isEmpty && pagination.total == 0`

### Checklist

- [ ] جلب المنتجات لكل مستوى (مو leaf فقط)
- [ ] لا requests منفصلة لكل child
- [ ] pagination من السيرفر كما هي

---

## 6) وراثة صفات الفئة

> المرجع: `FRONTEND_FLUTTER_CATEGORY_ATTRIBUTE_INHERITANCE.md`

### التغيير

| قبل | بعد |
|-----|-----|
| `category_id` حرفي فقط | أي id بالشجرة → صفات **الجذر** |
| فرعية بدون صفات = `[]` | الفرعية ترجع صفات الرئيسية |

### Endpoint

```http
GET /api/user/categories/{categoryId}/attributes
```

حقول مضافة: `category_id`, `root_category_id`.

### القواعد

- اجلب الصفات عند فتح **أي** فئة (حتى الجذر)
- **لا** تعيد الطلب عند drill-down بنفس الشجرة — استخدم `root_category_id` ككاش
- فلترة المنتجات: `GET /api/user/products?category_id={id}&attribute_values=31,40`
- صفحة المنتج: بعدها `attributes_map` / `shop_variants` (بدون تغيير)

### Checklist

- [ ] chips حتى لو `has_children == true`
- [ ] إعادة استخدام القائمة عند النزول لأبناء بنفس الجذر
- [ ] قائمة فاضية = الجذر ما عنده صفات (مو خطأ)

---

## 7) صفحة المنتج

> المرجع: `FRONTEND_FLUTTER_PRODUCT_DETAIL_SHOP_VARIANTS.md`

### تغييرات الـ API

```http
GET /api/user/products/{id}
```

| الحقل | قبل | بعد |
|-------|-----|-----|
| `shop_variants` | ممكن `[]` | **دائمًا عنصر واحد على الأقل** |
| `country` | object `{ id, name: {ar,en} }` | **string** أو `null` |
| `shop_variants[].shop_id` | موجود دائمًا | ممكن **`null`** |
| `shop_variants[].id` | موجود دائمًا | ممكن **`null`** |

نفس تغيير `country` على `GET /api/user/products` (قائمة).

### Models

```dart
class ProductDetail {
  final String? country;
  final List<ShopVariant> shopVariants;
}

class ShopVariant {
  final int? id;
  final int? shopId;
  final num price;
  final int quantity;
}
```

### قواعد

```dart
final selected = variants.isNotEmpty ? variants.first : null;
final price = selected?.price ?? product.price;

bool canAddToCart(ShopVariant? v) =>
    v?.shopId != null && v?.id != null && (v?.quantity ?? 0) > 0;

// country
product.country // "تركيا" أو null — مو country.name.ar
```

- `shop_id` أو `id` = `null` → عطّل السلة، **الصفحة تضل تعرض**
- `attributes_map` فاضي → إخفاء picker (مو خطأ)

### Checklist

- [ ] `country` كـ `String?` على detail **و** list
- [ ] لا `shopVariants[0]` بدون حماية
- [ ] fallback الصور: variant → product → thumbnail
- [ ] Error/retry UI بدل red screen

---

## 8) التسجيل بدون إيميل

> المرجع: `FRONTEND_FLUTTER_REGISTER_EMAIL_OPTIONAL.md`

### التغيير

| الحقل | قبل | بعد |
|-------|-----|-----|
| `phone` | مطلوب إذا ما في إيميل | **مطلوب دائمًا** |
| `email` | مطلوب إذا ما في هاتف | **اختياري** |
| OTP | SMS أو إيميل | **SMS دائمًا** على `phone` |

### Request

```http
POST /api/user/auth/register
```

```json
{
  "name": "أحمد",
  "phone": "0501234567",
  "password": "Passw0rd!",
  "city_id": 1,
  "governorate_id": 1
}
```

- **لا ترسل `email: ""`** — احذف المفتاح إذا فاضي
- `phone` أرقام فقط (`digitsOnly`)

### التحقق

```http
POST /api/user/auth/verify-otp
{ "phone": "0501234567", "code": "12345" }
```

### Checklist

- [ ] حقل الهاتف مطلوب + `digitsOnly`
- [ ] الإيميل "(اختياري)"
- [ ] حذف tabs/toggle "إيميل أو هاتف"
- [ ] OTP بالهاتف
- [ ] `email` nullable بكل الموديلات
- [ ] الملف الشخصي يتعامل مع `email == null`

---

## 9) فلاتر المنتجات

> المرجع الكامل: **`FRONTEND_FLUTTER_FILTERS.md`**

### Endpoints

| الغرض | Endpoint |
|-------|----------|
| منتجات + فلاتر | `GET /api/user/products` |
| chips (لون/وزن/...) | `GET /api/user/categories/{id}/attributes` |

### باراميترات رئيسية

`category_id` · `brand_id` · `shop_id` · `price_min`/`price_max` · `search` · `country` (نص) · `is_free_delivery` · `is_instant_delivery` · `on_sale` · `in_stock_only` · `attribute_values` · `type` · `sort_by` · `page` · `per_page`

### قواعد

- `category_id` = الفئة الحالية + **كل الأحفاد** — لا تفلتر محلياً
- `/attributes` على **أي** مستوى → صفات الجذر — كاش بـ `root_category_id`
- أي تغيير فلتر → `page = 1`
- `country` على البطاقة = string (مو object)

### Checklist

- [ ] chips من `/attributes` + منتجات من `/products` بنفس الطلب
- [ ] toggles + سعر + ترتيب
- [ ] empty state من `pagination.total === 0`

---

## 10) عرض الأسعار دولار + ليرة

> المرجع: `FRONTEND_FLUTTER_PRICES_DUAL_CURRENCY.md`

- اعرض `*_formatted` حسب عملة المستخدم
- للعرض الثنائي استخدم `price_currencies` / `price_after_discount_currencies` (USD + SYP)
- **لا تحسب** سعر الصرف داخل التطبيق
- في صفحة المنتج: السعر بعد الخصم هو الأساسي

---

## أوامر التشغيل (Backend)

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=CategoryPagesBackfillSeeder
php artisan db:seed --class=NavMenuSeeder
```

---

## مراجع تفصيلية (كل الملفات)

| الملف | الموضوع | متى تقرأه |
|-------|---------|-----------|
| `FRONTEND_FLUTTER_NAV_MENU.md` | شريط التنقّل | تفاصيل Nav Menu |
| `FRONTEND_FLUTTER_PAGE_BUILDER.md` | Page Builder | endpoints + شكل القسم |
| `FRONTEND_FLUTTER_SECTIONS_TODAY.md` | **layout / variant** | آخر تعديل الأقسام (آب 2026) |
| `FRONTEND_FLUTTER_SECTIONS_COMPLETE.md` | **دليل من الصفر** | بناء الصفحات والأقسام كامل |
| `FRONTEND_FLUTTER_CATEGORY_PAGES.md` | صفحات الفئات | شاشة الفئة |
| `FRONTEND_FLUTTER_CATEGORIES_CIRCULAR.md` | الفئات الدائرية | دوائر + drill-down |
| `FRONTEND_FLUTTER_CATEGORY_PRODUCT_SUBTREE.md` | منتجات الشجرة | pagination + لا فلترة محلية |
| `FRONTEND_FLUTTER_CATEGORY_ATTRIBUTE_INHERITANCE.md` | وراثة الصفات | فلاتر chips |
| `FRONTEND_FLUTTER_PRODUCT_DETAIL_SHOP_VARIANTS.md` | صفحة المنتج | shop_variants + country |
| `FRONTEND_FLUTTER_REGISTER_EMAIL_OPTIONAL.md` | التسجيل | phone مطلوب، email اختياري |
| **`FRONTEND_FLUTTER_FILTERS.md`** | **فلاتر المنتجات** | **كل الفلاتر + Dart** |
| `FRONTEND_FLUTTER_PRICES_DUAL_CURRENCY.md` | أسعار USD + SYP | عرض `*_currencies` و `*_formatted` |
| `FRONTEND_SECTION_LAYOUT_AND_CARD.md` | layout / variant / display_type_id | مرجع مشترك (Web + Flutter) |

---

## Checklist شامل (كل التطبيق)

### أقسام وصفحات
- [ ] ويدجت أقسام موحّد
- [ ] `layout` → slider/list/grid
- [ ] `variant` → شكل الكارد فقط
- [ ] fallback بدون `layout` → `slider`
- [ ] صفحة فئة = نفس الويدجت

### فئات
- [ ] دوائر لكل مستوى (`?parent_id=` — **مو** `/categories/{id}`)
- [ ] منتجات + أبناء معًا
- [ ] subtree: لا فلترة محلية
- [ ] صفات الجذر لأي مستوى

### منتج + تسجيل + أسعار
- [ ] `country` string
- [ ] `shop_variants` nullable ids
- [ ] تسجيل: phone مطلوب، email اختياري
- [ ] أسعار من `*_formatted` / `*_currencies`

### Nav
- [ ] `GET /nav-menu` ديناميكي

### فلاتر
- [ ] `FRONTEND_FLUTTER_FILTERS.md` — chips + `/products` + toggles + ترتيب
