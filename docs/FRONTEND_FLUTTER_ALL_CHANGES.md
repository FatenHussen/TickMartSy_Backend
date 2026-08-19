# كل تعديلات Flutter — ملخّص شامل

هذا المستند يجمع **كل التعديلات** التي تحتاج تنفيذ في تطبيق Flutter.

---

## الفهرس

1. [شريط التنقّل الديناميكي (Nav Menu)](#1-شريط-التنقّل-الديناميكي)
2. [عرض الصفحات (Page Builder)](#2-عرض-الصفحات-page-builder)
3. [صفحات الفئات](#3-صفحات-الفئات)
4. [الفئات الدائرية (Circular Categories)](#4-الفئات-الدائرية)
5. [وراثة صفات الفئة](#5-وراثة-صفات-الفئة)
6. [صفحة المنتج (shop_variants + country)](#6-صفحة-المنتج)
7. [التسجيل بدون إيميل](#7-التسجيل-بدون-إيميل)

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

عام (بدون توكن). يرجّع العناصر **المفعّلة فقط** مرتّبة.

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

```dart
void openNavItem(BuildContext context, NavMenuItem item) {
  switch (item.type) {
    case 'route':
      final key = item.target['route_key'] as String?;
      switch (key) {
        case 'home':          context.go('/');
        case 'categories':    context.go('/categories');
        case 'brands':        context.go('/brands');
        case 'shops':         context.go('/shops');
        case 'baskets':       context.go('/my-baskets');
        case 'points':        context.go('/points');
        case 'help':          context.go('/help');
        case 'subscriptions': context.go('/subscription-packages');
        default: break;
      }
    case 'category':
      context.push('/category/${item.target['category_id']}');
    case 'brand':
      context.push('/brand/${item.target['brand_id']}');
    case 'page':
      context.push('/page/${item.target['slug'] ?? item.target['page_id']}');
    case 'url':
      final url = item.target['url'] as String?;
      if (url != null) launchUrl(Uri.parse(url));
  }
}
```

### Checklist

- [ ] احذف القائمة الثابتة
- [ ] `GET /api/user/nav-menu` واعرض بالترتيب
- [ ] خريطة `route_key` → شاشات
- [ ] `url` يفتح خارجيًا
- [ ] fallback آمن لأي `route_key` غير معروف
- [ ] أعد الجلب عند تغيير اللغة
- [ ] إذا `data` فاضية → أخفِ الشريط

---

## 2) عرض الصفحات (Page Builder)

> المرجع: `FRONTEND_FLUTTER_PAGE_BUILDER.md`

### Endpoints

| الغرض | Endpoint |
|-------|----------|
| أقسام الصفحة الرئيسية/عامة | `GET /api/user/sections?page_slug={slug}` |
| صفحة فئة | `GET /api/user/categories/{categoryId}/page` |

### شكل القسم

```json
{
  "id": 10,
  "name": { "ar": "...", "en": "..." },
  "type": "manual",
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

- `end_date` / `discount` / `discount_type`: تُملأ فقط عندما يكون القسم من نوع `latest_flash_sale` — خلاف ذلك `null`.
- `show_when`: شروط عرض القسم (مثلاً `{ "category_id": 5 }`) — إذا فاضي يُعرض دائمًا.

### تعيين `variant` لـ Widget

| `variant` | Widget |
|-----------|--------|
| `horizontal` | `ListView(scrollDirection: Axis.horizontal)` — سلايدر أفقي (شريط كاردات) |
| `vertical` | `GridView` / `Column` |
| `square` | شبكة مربّعات (فئات) |

### البانرات

- `type: "manual"` مع بانرات
- `item.image` **عرضية** → `AspectRatio` أفقي (≈ 16/6)
- عنصر واحد → بانر ثابت، عدة عناصر → `PageView`/carousel
- الضغط → `item.link`
- لا تعرض قسمًا `items` فارغ

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
    "category": { "id": 12, "name": {...}, "children": [...] },
    "sections": [...]
  }
}
```

### الشاشة

1. نادِ `categories/{id}/page`
2. AppBar: `category.name`
3. `ListView`/`CustomScrollView` يبني `sections` عبر **مُعرّض الأقسام الموحّد**

### التنقّل

| من | إلى |
|----|-----|
| كارد فئة | `CategoryPageScreen(categoryId: item.id)` |
| كارد منتج | `ProductDetailScreen` |
| `see_more` | شاشة القائمة مع `see_more.params` |

### ملاحظات

- لا تفترض عددًا ثابتًا للأقسام
- رتّب حسب `order` كما يصل
- كاش per `categoryId` + `RefreshIndicator`

---

## 4) الفئات الدائرية (Circular Categories)

> المرجع: `FRONTEND_FLUTTER_CATEGORIES_CIRCULAR.md`

### التغيير

| قبل | بعد |
|-----|-----|
| منتجات على الأوراق فقط | منتج على أي مستوى |
| دوائر للجذور فقط | **نفس الدائرة لكل مستوى** |
| الأب بدون منتجات مباشرة | `category_id` يرجع المنتجات + كل الأحفاد |

### حقول جديدة على الفئة

| الحقل | النوع |
|-------|-------|
| `parent_id` | int / null |
| `children_count` | int |
| `has_children` | bool |
| `children[].icon` | string (URL) |

### APIs

| الغرض | Endpoint |
|-------|----------|
| الجذور | `GET /api/user/categories` |
| أبناء | `GET /api/user/categories?parent_id={id}` |
| منتجات | `GET /api/user/products?category_id={id}` |
| صفات | `GET /api/user/categories/{id}/attributes` |

### UI المطلوب

```
┌─────────────────────────────┐
│  ← Food                     │
│  (○) Grains  (○) Veg  (○)   │ ← دوائر (أبناء)
│  ── Products ──              │
│  [card] [card] [card]        │ ← منتجات
└─────────────────────────────┘
```

- `has_children = true` → نفس الشاشة مع `parent_id`
- `has_children = false` → منتجات فقط
- نفس `CategoryCircleTile` لكل مستوى
- كل دائرة تستخدم `icon` (الأبناء صاروا يرجعون `icon`)

### Checklist

- [ ] نفس الدائرة للجذور والفرعيات
- [ ] `has_children` → نفس الشاشة مع `?parent_id=`
- [ ] منتجات للفئة الحالية تحت الدوائر دائمًا
- [ ] الصفات من `/categories/{id}/attributes` لأي مستوى

---

## 5) وراثة صفات الفئة

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
- **لا** تعيد الطلب عند النزول لأبناء بنفس الشجرة
- أعد الطلب فقط عند فتح شجرة مختلفة (جذر آخر)
- استخدم `root_category_id` كمفتاح كاش

### فلترة المنتجات

```http
GET /api/user/products?category_id={id}&attribute_values=31,40
```

### Checklist

- [ ] `/categories/{id}/attributes` عند فتح أي فئة
- [ ] عرض chips حتى لو `has_children == true`
- [ ] إعادة استخدام القائمة عند drill-down بنفس الشجرة
- [ ] صفحة المنتج: بعدها `attributes_map` / `shop_variants`

---

## 6) صفحة المنتج

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

### Models

```dart
class ProductDetail {
  final String? country; // كان Country object
  final List<ShopVariant> shopVariants;
}

class ShopVariant {
  final int? id;       // nullable
  final int? shopId;   // nullable
  final num price;
  final int quantity;
  final List<VariantAttribute> attributes;
  final List<ProductImage> images;
}
```

### قواعد

```dart
// لا تقرأ بدون حماية
final selected = variants.isNotEmpty ? variants.first : null;
final price = selected?.price ?? product.price;

// السلة
bool canAddToCart(ShopVariant? v) =>
    v?.shopId != null && v?.id != null && (v?.quantity ?? 0) > 0;

// country
product.country // "تركيا" أو null — مو country.name.ar

// images fallback
List<String> galleryUrls(ProductDetail p, ShopVariant? v) {
  final fromVariant = v?.images.map((e) => e.path).toList() ?? [];
  if (fromVariant.isNotEmpty) return fromVariant;
  return p.images.map((e) => e.path).toList();
}
```

### Checklist

- [ ] `country` كـ `String?`
- [ ] `ShopVariant.id` / `shopId` كـ `int?`
- [ ] لا `shopVariants[0]` بدون حماية
- [ ] السلة معطّلة إذا `shop_id` أو `id` null
- [ ] `attributes_map` فاضي → إخفاء picker
- [ ] fallback الصور
- [ ] Error/retry UI بدلاً من red screen

### نفس تغيير `country` على قائمة المنتجات

```http
GET /api/user/products
```

`country` صار string أو `null` (مو object).

---

## 7) التسجيل بدون إيميل

> المرجع: `FRONTEND_FLUTTER_REGISTER_EMAIL_OPTIONAL.md`

### التغيير

| الحقل | قبل | بعد |
|-------|-----|-----|
| `phone` | مطلوب إذا ما في إيميل | **مطلوب دائمًا** |
| `email` | مطلوب إذا ما في هاتف | **اختياري** |
| OTP | SMS أو إيميل | **SMS دائمًا** |

### Endpoint

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

- **لا ترسل `email: ""`** — احذف المفتاح
- `phone` أرقام فقط `digitsOnly`

### بناء الـ payload

```dart
final body = <String, dynamic>{
  'name': name.trim(),
  'phone': phone.replaceAll(RegExp(r'[^\d]'), ''),
  'password': password,
  'city_id': cityId,
  'governorate_id': governorateId,
};

final trimmedEmail = email?.trim() ?? '';
if (trimmedEmail.isNotEmpty) {
  body['email'] = trimmedEmail;
}
```

### التحقق

```http
POST /api/user/auth/verify-otp
{ "phone": "0501234567", "code": "12345" }
```

### الفورم

- حقل الهاتف: مطلوب + `digitsOnly`
- حقل الإيميل: label "(اختياري)" — validator فقط إذا مش فاضي
- احذف tabs/toggle "إيميل أو هاتف"
- بعد التسجيل → OTP بالهاتف

### Checklist

- [ ] حقل الهاتف مطلوب + `digitsOnly`
- [ ] الإيميل "(اختياري)"
- [ ] الـ payload بلا `email` عند الفراغ
- [ ] حذف tabs/toggle
- [ ] OTP بالهاتف + نص صحيح
- [ ] `email` nullable بكل الموديلات
- [ ] عرض `error` من 422 مباشرة
- [ ] الملف الشخصي يتعامل مع `email == null`

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
| `FRONTEND_FLUTTER_NAV_MENU.md` | شريط التنقّل |
| `FRONTEND_FLUTTER_PAGE_BUILDER.md` | عرض الصفحات |
| `FRONTEND_FLUTTER_CATEGORY_PAGES.md` | صفحات الفئات |
| `FRONTEND_FLUTTER_CATEGORIES_CIRCULAR.md` | الفئات الدائرية |
| `FRONTEND_FLUTTER_CATEGORY_ATTRIBUTE_INHERITANCE.md` | وراثة صفات الفئة |
| `FRONTEND_FLUTTER_PRODUCT_DETAIL_SHOP_VARIANTS.md` | صفحة المنتج |
| `FRONTEND_FLUTTER_REGISTER_EMAIL_OPTIONAL.md` | التسجيل بدون إيميل |
