# Flutter — الدليل الوحيد (بناء من الصفر)

> **ارسلوا هذا الملف فقط لفريق Flutter.**  
> يغطي كل التعديلات حتى آب 2026: الصفحات، الأقسام، الفئات، الفلاتر، Nav Menu، المنتج، التسجيل.

كل الـ routes تحت `/api/user`. أرسلوا `Accept-Language: ar` أو `en`. التوكن اختياري لمعظم الشاشات.

---

## الفهرس

1. [الفكرة العامة](#1-الفكرة-العامة)
2. [Endpoints — كل ما تحتاجه](#2-endpoints)
3. [الأقسام: layout / variant / display_type_id](#3-الأقسام)
4. [بناء الصفحات من الصفر](#4-بناء-الصفحات-من-الصفر)
5. [Nav Menu (الشريط العلوي)](#5-nav-menu)
6. [الفئات (دوائر + صفحات + شجرة)](#6-الفئات)
7. [فلاتر المنتجات](#7-فلاتر-المنتجات)
8. [صفحة المنتج](#8-صفحة-المنتج)
9. [التسجيل](#9-التسجيل)
10. [الأسعار دولار + ليرة](#10-الأسعار)
11. [Checklist شامل](#11-checklist-شامل)

---

## 1) الفكرة العامة

التطبيق يعرض **صفحات**، وكل صفحة = قائمة **أقسام (sections)** مرتّبة.

| نوع الصفحة | Endpoint |
|------------|----------|
| رئيسية / عروض / أي slug | `GET /api/user/sections?page_slug={slug}` |
| صفحة فئة (أي عمق) | `GET /api/user/categories/{categoryId}/page` |

**قاعدة ذهبية:** ويدجت **واحد** (`SectionRenderer`) للرئيسية + صفحة الفئة + أي `page_slug`.

---

## 2) Endpoints

| الغرض | Endpoint |
|-------|----------|
| أقسام صفحة | `GET /api/user/sections?page_slug=home` |
| صفحة فئة | `GET /api/user/categories/{id}/page` |
| جذور الفئات | `GET /api/user/categories` |
| أبناء فئة | `GET /api/user/categories?parent_id={id}` |
| منتجات + فلاتر | `GET /api/user/products?...` |
| صفات فلاتر (chips) | `GET /api/user/categories/{id}/attributes` |
| تفاصيل منتج | `GET /api/user/products/{id}` |
| Nav Menu | `GET /api/user/nav-menu` |
| إعدادات (ألوان + طلب سريع) | `GET /api/user/settings` |
| ماركات / متاجر / بلدان | `GET /api/user/brands` · `shops` · `countries` |
| تسجيل | `POST /api/user/auth/register` |
| OTP | `POST /api/user/auth/verify-otp` |

> **طلب سريع (الهوم):** من `settings.quick_order` — `is_enabled`، خلفية صورة/لون، `card_variant`، نصوص وخطوات. التفاصيل في `docs/CUSTOM_ORDER_REQUESTS_FLUTTER.md`.

> **لا يوجد** `GET /api/user/categories/{id}` لفئة واحدة — استخدم `?parent_id=` للأبناء.

---

## 3) الأقسام

### 3.1 الحقول الثلاثة — لا تخلط بينها

| الحقل | المعنى | القيم |
|-------|--------|-------|
| **`layout`** | تخطيط **القسم كامل** | `slider` \| `list` \| `grid` |
| **`variant`** | شكل **الكارد** داخل القسم | `horizontal` \| `vertical` \| `square` |
| **`display_type_id`** | نوع **المحتوى** | 1=banner … 8=category |

**غلط قديم (احذفوه):** `variant == horizontal` → سلايدر.  
**الصح:** السلايدر = `layout: slider`، شكل الكارد = `variant`.

```dart
final layout = section.layout ?? 'slider';
final cardVariant = section.variant ?? 'horizontal';
```

### 3.2 `layout` → ويدجت القسم

| `layout` | Widget |
|----------|--------|
| `slider` | `ListView(scrollDirection: Axis.horizontal)` — **افتراضي** |
| `list` | `ListView` / `Column` عمودي |
| `grid` | `GridView` |

### 3.3 `variant` → شكل الكارد

| `variant` | الاستخدام |
|-----------|-----------|
| `horizontal` | بانرات / كارد عريض |
| `vertical` | منتجات |
| `square` | فئات |

### 3.4 `display_type_id`

| id | النوع |
|----|--------|
| 1 | banner |
| 2 | product |
| 3 | shop |
| 4 | basket |
| 5 | schedule-basket |
| 6 | brand |
| 7 | recipe |
| 8 | category |

### 3.5 نموذج القسم (JSON)

```json
{
  "id": 10,
  "name": { "ar": "منتجات رائجة", "en": "Trending" },
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
  "items": []
}
```

| حقل | Flutter |
|-----|---------|
| `order` | رتّب كما يصل — لا تعيد الترتيب |
| `type` | `manual` = items ثابتة · `api` = ديناميكي |
| `see_more` | زر «عرض الكل» |
| `action.page_slug` | شاشة التفاصيل |
| `show_when` | فاضي = اعرض دائمًا |
| `end_date`/`discount` | flash sale فقط |
| `items` | **لا تعرض قسمًا فارغًا** |

### 3.6 العناصر `items[]`

```json
{
  "id": 1,
  "link": "https://...",
  "order": 0,
  "item": {
    "id": 44,
    "title": { "ar": "...", "en": "..." },
    "image": "https://...",
    "price": 10.5
  }
}
```

| نوع | عند الضغط |
|-----|-----------|
| بانر | `item.link` — صورة عرضية ≈ 16/6 |
| منتج | تفاصيل المنتج |
| فئة | `CategoryPageScreen(id)` |
| متجر | شاشة المتجر |

### 3.7 SectionRenderer (القلب)

```dart
class SectionRenderer extends StatelessWidget {
  final Section section;
  const SectionRenderer({super.key, required this.section});

  @override
  Widget build(BuildContext context) {
    if (section.items.isEmpty) return const SizedBox.shrink();

    final layout = section.layout ?? 'slider';
    final cardVariant = section.variant ?? 'horizontal';

    return ColoredBox(
      color: parseColor(section.backgroundColor) ?? Colors.transparent,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SectionHeader(title: section.name, seeMore: section.seeMore),
          switch (layout) {
            'list' => SectionListBody(items: section.items, cardVariant: cardVariant),
            'grid' => SectionGridBody(items: section.items, cardVariant: cardVariant),
            _ => SectionSliderBody(items: section.items, cardVariant: cardVariant),
          },
        ],
      ),
    );
  }
}
```

---

## 4) بناء الصفحات من الصفر

### 4.1 الصفحة الرئيسية

```dart
final res = await dio.get('/api/user/sections', queryParameters: {'page_slug': 'home'});
final sections = (res.data['data'] as List).map(Section.fromJson).toList();

return CustomScrollView(
  slivers: [
    for (final s in sections) SliverToBoxAdapter(child: SectionRenderer(section: s)),
  ],
);
```

### 4.2 صفحة الفئة

```dart
final res = await dio.get('/api/user/categories/$categoryId/page');
final category = res.data['data']['category'];
final sections = (res.data['data']['sections'] as List).map(Section.fromJson).toList();

return Scaffold(
  appBar: AppBar(title: Text(localizedName(category['name']))),
  body: RefreshIndicator(
    onRefresh: () => _load(categoryId),
    child: ListView(
      children: sections.map((s) => SectionRenderer(section: s)).toList(),
    ),
  ),
);
```

### 4.3 التنقّل

| من | إلى |
|----|-----|
| كارد فئة | `CategoryPageScreen(categoryId)` |
| كارد منتج | `ProductDetailScreen` |
| `see_more` | قائمة مع `see_more.params` |
| بانر | `item.link` |

### 4.4 موديلات مطلوبة

```dart
class Section {
  final int id;
  final String name;
  final String? layout;       // slider | list | grid
  final String? variant;      // horizontal | vertical | square
  final int? displayTypeId;
  final String type;            // manual | api
  final String? contentType;
  final List<SectionItem> items;
  final SeeMore? seeMore;
  final String? backgroundColor;
  final String? backgroundCardColor;
  // + show_when, flash fields, action...
}
```

---

## 5) Nav Menu

```http
GET /api/user/nav-menu
Accept-Language: ar
```

```json
{
  "data": [
    { "id": 1, "title": "الفئات", "type": "route", "order": 1,
      "open_in_new_tab": false, "target": { "route_key": "categories" } },
    { "id": 8, "title": "إلكترونيات", "type": "category",
      "target": { "category_id": 12, "name": "إلكترونيات" } }
  ]
}
```

| `type` | Flutter |
|--------|---------|
| `route` | `route_key`: home · categories · brands · shops · baskets · points · help · subscriptions |
| `category` | `CategoryPageScreen(target.category_id)` |
| `brand` | شاشة ماركة |
| `page` | شاشة slug |
| `url` | `launchUrl` |

```dart
void openNavItem(BuildContext context, NavMenuItem item) {
  switch (item.type) {
    case 'route':
      final key = item.target['route_key'];
      // map key → GoRoute
    case 'category':
      context.push('/category/${item.target['category_id']}');
    case 'page':
      context.push('/page/${item.target['slug']}');
    case 'url':
      launchUrl(Uri.parse(item.target['url']));
  }
}
```

- احذف القائمة الثابتة
- `data` فاضية → أخفِ الشريط
- أعد الجلب عند تغيير اللغة

---

## 6) الفئات

### 6.1 دوائر (كل مستوى)

```
┌─────────────────────────┐
│  ← Food                 │
│  (○) Grains  (○) Veg    │  ← أبناء
│  ── Products ──         │
│  [card] [card]          │
└─────────────────────────┘
```

| Endpoint | الغرض |
|----------|--------|
| `GET /categories` | جذور |
| `GET /categories?parent_id={id}` | أبناء |
| `GET /products?category_id={id}` | منتجات + أحفاد |

حقول جديدة: `parent_id`, `has_children`, `children_count`, `children[].icon`.

```dart
Future<List<CategoryCircle>> fetchCategories({int? parentId}) async {
  final path = parentId == null
      ? '/api/user/categories'
      : '/api/user/categories?parent_id=$parentId';
  final res = await dio.get(path);
  final items = res.data['data']['items'] ?? res.data['data'];
  return (items as List).map(CategoryCircle.fromJson).toList();
}
```

- **نفس** `CategoryCircleTile` لكل مستوى (حتى عمق 6)
- منتجات تحت الدوائر **دائمًا** (حتى لو `has_children == true`)
- لا `GET /categories/{id}` — استخدم `?parent_id=`

### 6.2 شجرة المنتجات — لا فلترة محلية

```http
GET /api/user/products?category_id={categoryId}&page=1&per_page=10
```

```dart
// خطأ — يحذف منتجات الفروع
products.where((p) => p.categoryId == selectedCategoryId);
```

- طلب **واحد** per فئة
- عند تغيير الفئة: امسح القائمة، `page = 1`
- empty: `!isLoading && products.isEmpty && pagination.total == 0`

---

## 7) فلاتر المنتجات

### 7.1 باراميترات `/products`

| Param | ملاحظة |
|-------|--------|
| `category_id` | الفئة + **كل الأحفاد** |
| `brand_id` / `shop_id` | |
| `price_min` / `price_max` | بعملة المستخدم |
| `search` | مو `name` |
| `country` | **نص** — مو `country_id` |
| `is_free_delivery` / `is_instant_delivery` / `on_sale` / `in_stock_only` | bool |
| `attribute_values` | `"31,40"` — OR |
| `type` | `new` · `trend` · `offers` · `latest_flash_sale` … |
| `sort_by` | `price_asc` · `price_desc` · `newest` · `oldest` · `rating` |
| `page` / `per_page` | |

### 7.2 صفات (chips)

```http
GET /api/user/categories/{categoryId}/attributes
```

- أي id بالشجرة → صفات **الجذر** + `root_category_id`
- كاش per `root_category_id` — لا تعيد الطلب عند drill-down
- chips حتى لو `has_children == true`
- صفحة المنتج: `attributes_map` — **مو** `/attributes`

### 7.3 Repository

```dart
Future<PaginatedProducts> loadProducts({
  int? categoryId,
  int page = 1,
  Map<String, dynamic> filters = const {},
}) async {
  final params = <String, dynamic>{
    'page': page,
    'per_page': 20,
    ...filters.where((k, v) => v != null && v != '' && v != false),
  };
  if (categoryId != null) params['category_id'] = categoryId;
  if (filters['attribute_values'] is Set) {
    params['attribute_values'] = (filters['attribute_values'] as Set).join(',');
  }
  final res = await dio.get('/api/user/products', queryParameters: params);
  return PaginatedProducts.fromJson(res.data['data']);
}
```

- أي تغيير فلتر → `page = 1`
- لا فلترة محلية بعد API

---

## 8) صفحة المنتج

```http
GET /api/user/products/{id}
```

| الحقل | بعد |
|-------|-----|
| `country` | **string** أو `null` (مو object) |
| `shop_variants` | **دائمًا عنصر واحد على الأقل** |
| `shop_variants[].id` / `shop_id` | ممكن **`null`** (منتجات قديمة أو بائع بلا فرع) |

> **تحديث آب 2026:** عند حفظ منتج من الأدمن بدون فرع، الباك يربطه تلقائياً بفرع البائع الافتراضي (`is_default`). بعدها `shop_id` و `id` يصيروا أرقام حقيقية والمنتج يُشترى. التطبيق **ما يحتاج endpoint جديد** — خلّوا فحص `null` للحالات القديمة.
> التفاصيل: `FRONTEND_LATEST_FLUTTER_2026_08_26.md`

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

final selected = product.shopVariants.isNotEmpty ? product.shopVariants.first : null;
bool canAddToCart(ShopVariant? v) =>
    v?.shopId != null && v?.id != null && (v?.quantity ?? 0) > 0;
```

- `shop_id` أو `id` null → عطّل السلة، **الصفحة تضل تعرض**
- `attributes_map` فاضي → إخفاء picker
- `country` على قائمة المنتجات أيضًا string

---

## 9) التسجيل

| الحقل | بعد |
|-------|-----|
| `phone` | **مطلوب دائمًا** (أرقام فقط) |
| `email` | **اختياري** |
| OTP | **SMS** على الهاتف |

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

- **لا ترسل** `email: ""` — احذف المفتاح إذا فاضي
- بعد النجاح → OTP: `POST /api/user/auth/verify-otp` مع `phone` + `code`

---

## 10) الأسعار

> التفصيل: `FRONTEND_FLUTTER_PRICES_DUAL_CURRENCY.md`

| الاستخدام | الحقل |
|-----------|--------|
| عرض عادي | `price_formatted` / `price_after_discount_formatted` |
| دولار + ليرة معاً | `price_currencies` / `price_after_discount_currencies` |

- لا تحسب سعر الصرف محلياً
- للشراء اعرض السعر بعد الخصم إن وُجد

---

## 11) Checklist شامل

### الصفحات والأقسام (الأولوية)
- [ ] `SectionRenderer` موحّد
- [ ] `layout` → slider / list / grid
- [ ] `variant` → شكل الكارد فقط
- [ ] fallback: بدون `layout` → `slider`
- [ ] Home: `sections?page_slug=home`
- [ ] Category: `categories/{id}/page`
- [ ] لا قسم `items` فارغ
- [ ] بانرات بنسبة أفقية

### الفئات
- [ ] دوائر لكل مستوى · `?parent_id=`
- [ ] منتجات + دوائر معًا
- [ ] subtree: لا فلترة محلية

### فلاتر
- [ ] `/attributes` + كاش `root_category_id`
- [ ] `/products` بكل params
- [ ] تغيير فلتر → `page = 1`

### Nav Menu
- [ ] `GET /nav-menu` ديناميكي

### طلب سريع
- [ ] `GET /settings` → `quick_order.is_enabled` يخفي/يظهر الهيدر والقسم
- [ ] خلفية: صورة إن وُجدت وإلا `background_color`
- [ ] كروت: `card_background_color` + `card_variant` + ريسبونسيف موبايل

### منتج + تسجيل + أسعار
- [ ] `country` string · nullable shop ids
- [ ] phone مطلوب · email اختياري
- [ ] أسعار من `*_formatted` / `*_currencies` (التفصيل: `FRONTEND_FLUTTER_PRICES_DUAL_CURRENCY.md`)

### ممنوع
- [ ] `variant` كبديل عن `layout`
- [ ] `display_type_id` لتحديد سلايدر/شبكة
- [ ] `GET /categories/{id}` (غير موجود)
- [ ] فلترة محلية على `category_id`

---

## ملخص جملة واحدة

1. جيب الأقسام من API  
2. `SectionRenderer`: `layout` للتخطيط، `variant` للكارد  
3. نفس الويدجت للرئيسية وصفحة الفئة  
4. الفئات: دوائر + `categories/{id}/page`  
5. الفلاتر: `/attributes` للـ chips + `/products` للنتائج  
6. Nav Menu من API  

**هذا الملف وحده كافي لبناء التطبيق من الصفر.**
