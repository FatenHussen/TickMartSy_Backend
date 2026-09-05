# Flutter — آخر نسخة (كل التعديلات)

> **أرسلوا هذا الملف لفريق Flutter.**  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **آخر تحديث | Last Updated:** 2026-09-05 (مساءً)  
> الملف الشامل السابق يبقى: [`flutter.md`](./flutter.md)

يجمع **كل** ما يحتاجه التطبيق حتى اليوم: Nav · أقسام · فئات دائرية · فلاتر · تسجيل · طلب سريع · أسعار · متغيّرات · **ضمان** · **كمية** · **سلة مخصصة**.

---

## الفهرس

0. [ماذا تغيّر في 5 أيلول 2026](#0-ماذا-تغيّر-في-5-أيلول-2026)
1. [شريط التنقّل](#1-شريط-التنقّل)
2. [الصفحات والأقسام](#2-الصفحات-والأقسام)
3. [الفئات الدائرية والشجرة](#3-الفئات-الدائرية-والشجرة)
4. [فلاتر المنتجات](#4-فلاتر-المنتجات)
5. [صفحة المنتج](#5-صفحة-المنتج)
6. [الضمان](#6-الضمان)
7. [الكمية والسلة](#7-الكمية-والسلة)
8. [متغيّرات المنتج](#8-متغيّرات-المنتج)
9. [التسجيل](#9-التسجيل)
10. [الطلب السريع](#10-الطلب-السريع)
11. [الأسعار](#11-الأسعار)
12. [Dart — الضمان والكمية](#12-dart--الضمان-والكمية)
13. [Checklist](#13-checklist)
14. [السلة المخصصة](#14-السلة-المخصصة)

---

## 0) ماذا تغيّر في 5 أيلول 2026

| البند | قبل | بعد (التطبيق) |
|-------|------|----------------|
| **الضمان** | `warranty_period` (أشهر) | `warranty: { id, name, description }` حسب `Accept-Language` |
| **كمية المنتج** | رقم على المنتج | المخزون = `shop_variants[].quantity` (ممكن `null`) |
| **منتج بلا متغيّرات** | افتراض صف | الأدمن ما عاد يضيف متغيّر فاضي — الباك يبقى يرجّع `shop_variants[0]` fallback حتى ما يكسر الشاشة |
| **canAddToCart** | `quantity > 0` | + `id != null` + `shopId != null` — كمية `null` = 0 |
| **السلة المخصصة** | — | كروت `/schedules` + تخصيص داخل الفئة + تأكيد نعم/لا — [`FLUTTER_CUSTOM_BASKET.md`](./FLUTTER_CUSTOM_BASKET.md) |
| **حقول الكرت** | شكّ إن الصورة اسمها ثاني | `image` · `images` · `top_badges` / `bottom_badges` — ما في `cover_image` / `gallery` |
| **اسم / خصم** | كائن ترجمة أو `none` | `name` = `String` · `discountType` = `percentage` \| `fixed` \| `null` |

ضمان/كمية: نفس `GET /api/user/products/{id}`. السلة المخصصة: القسم 14 + [`FLUTTER_CUSTOM_BASKET.md`](./FLUTTER_CUSTOM_BASKET.md). إذا الكرت بلا صورة: الأدمن ما حفظها بعد.

---

## 1) شريط التنقّل

```http
GET /api/user/nav-menu
Accept-Language: ar
```

| `type` | الوجهة |
|--------|--------|
| `route` | `target.route_key` → شاشات ثابتة |
| `category` | `CategoryPageScreen(categoryId:)` |
| `brand` | شاشة الماركة |
| `page` | Page Builder (`slug`) |
| `url` | فتح خارجي |

احذف القائمة الثابتة. أعد الجلب عند تغيير اللغة. `data` فاضية → أخفوا الشريط.

---

## 2) الصفحات والأقسام

| الغرض | Endpoint |
|-------|----------|
| صفحة عامة | `GET /api/user/sections?page_slug={slug}` |
| صفحة فئة | `GET /api/user/categories/{id}/page` |

ويدجت **واحد** لكل الصفحات.

| حقل | المعنى |
|-----|--------|
| `layout` | `slider` \| `list` \| `grid` — تخطيط القسم |
| `variant` | `horizontal` \| `vertical` \| `square` — شكل الكارد |
| `display_type_id` | نوع المحتوى (بانر، منتج، متجر...) |

**غلط قديم:** `variant == horizontal` يعني سلايدر. **احذفوه.**  
السلايدر = `layout: slider`.

Fallback: `layout ?? 'slider'` · `variant ?? 'horizontal'`.  
قسم `items` فارغ → `SizedBox.shrink()`.  
القسم المخفي من الأدمن لا يصل للـ API.

---

## 3) الفئات الدائرية والشجرة

| الغرض | Endpoint |
|-------|----------|
| الجذور | `GET /api/user/categories` |
| الأبناء | `GET /api/user/categories?parent_id={id}` |
| منتجات | `GET /api/user/products?category_id={id}` |
| صفات | `GET /api/user/categories/{id}/attributes` |

**لا يوجد** `GET /api/user/categories/{id}` لفئة واحدة.

نفس الدائرة لكل مستوى. `has_children` → `?parent_id=`. المنتجات تظهر **تحت الدوائر دائماً**.

`category_id` = الفئة + **كل الأحفاد**. ممنوع:

```dart
products.where((p) => p.categoryId == selectedId); // خطأ
```

صفات أي مستوى = صفات الجذر. كاش `root_category_id`.

---

## 4) فلاتر المنتجات

`GET /api/user/products` — توكن اختياري لـ `is_favorite`.

باراميترات: `category_id` · `brand_id` · `shop_id` · `price_min`/`price_max` · `search` · `country` (نص) · `is_free_delivery` · `is_instant_delivery` · `on_sale` · `in_stock_only` · `attribute_values` · `type` · `sort_by` · `page` · `per_page`

- لا `name` ولا `country_id` على `/products`
- أي فلتر → `page = 1`
- empty state من `pagination.total == 0`

---

## 5) صفحة المنتج

```http
GET /api/user/products/{id}?lat=&lng=
Accept-Language: ar
```

| الحقل | النوع |
|-------|--------|
| `country` | `String?` — مو object |
| `warranty` | `{ id, name, description }` أو `null` |
| `warranty_period` | `int?` — قديم |
| `quantity` | `int?` على المنتج — **ليس المخزون** |
| `delivery_time` | `String?` على المنتج |
| `shop_variants` | دائماً عنصر واحد على الأقل |
| `shop_variants[].id` / `shop_id` | `int?` |
| `shop_variants[].quantity` | `int?` — المخزون |
| `attributes_map` | قائمة؛ فاضية → أخفوا الـ picker |
| `icons` / `top_badges` / `bottom_badges` | كما هي |
| `bought_with` | منتجات مقترنة |

ربط المنصة بفرع افتراضي يتم من الأدمن — **لا endpoint جديد**. خلّوا حماية `null`.

---

## 6) الضمان

```dart
String? warrantyTitle(ProductDetail p) {
  if (p.warranty?.name != null && p.warranty!.name.isNotEmpty) {
    return p.warranty!.name; // Localized string
  }
  if (p.warrantyPeriod != null) return '${p.warrantyPeriod} شهر';
  return null;
}
```

- `warranty == null` وبدون أشهر → أخفوا الويدجت.
- على `/user` الحقول `name` و `description` = **String** حسب اللغة — مو `{ar, en}`.
- لا تستدعوا Admin warranties API.

---

## 7) الكمية والسلة

```dart
bool canAddToCart(ShopVariant? v) =>
    v?.id != null &&
    v?.shopId != null &&
    (v?.quantity ?? 0) > 0;

int maxBuy(ShopVariant v, ProductDetail p) {
  final qty = v.quantity ?? 0;
  final cap = p.maxPurchaseQuantity;
  if (cap == null) return qty;
  return qty < cap ? qty : cap;
}
```

| `quantity` | العرض |
|------------|--------|
| رقم > 0 | «متوفر: N» |
| `0` أو `null` | غير متوفر — عطّل السلة |

```json
{ "shop_product_variant_id": 55, "quantity": 1 }
```

لا ترسلوا سعراً من التطبيق.

قائمة المنتجات: `quantity` على الكرت ممكن `null` — لا تعملوا `quantity!`.

---

## 8) متغيّرات المنتج

تفصيل: [`product-variants-flutter.md`](./product-variants-flutter.md) · [`product-variants-storefront-update.md`](./product-variants-storefront-update.md)

- لا اسم متغيّر — `attributes` + `sku`.
- لون → فلتر المقاسات المتاحة من `shop_variants` فقط.
- لا تفترضوا كل لون × كل مقاس.
- السعر/الخصم من المتغيّر المختار (`*_currencies`).
- `delivery_time` مرة واحدة على المنتج.
- `attributes_map` فاضي → استخدموا `shop_variants.first` بدون picker.

---

## 9) التسجيل

- `phone` مطلوب (`digitsOnly`).
- `email` اختياري — لا ترسلوا `""`.
- OTP SMS: `POST /api/user/auth/verify-otp`.
- كل الموديلات: `email` nullable.

---

## 10) الطلب السريع

تفصيل: [`../custom-orders/flutter.md`](../custom-orders/flutter.md)

`GET /api/user/settings` → `quick_order`:

- `is_enabled == false` → لا زر ولا قسم.
- الزر عام عند التفعيل؛ **القسم** فقط إذا الصفحة ∈ `page_slugs` (افتراضي `home`).
- CTA → `POST /api/user/custom-order-requests` (`description` ≥ 10، `address_id`).

---

## 11) الأسعار

`*_formatted` / `*_currencies` فقط. لا تحويل محلي.

صفحة المنتج: بعد الخصم أساسي؛ الأصلي مشطوب.

---

## 12) Dart — الضمان والكمية

```dart
class ProductWarranty {
  final int id;
  final String name;
  final String? description;

  factory ProductWarranty.fromJson(Map<String, dynamic> json) =>
      ProductWarranty(
        id: json['id'] as int,
        name: json['name'] as String? ?? '',
        description: json['description'] as String?,
      );
}

class ProductDetail {
  final String? country;
  final int? quantity; // product-level — nullable, not stock
  final int? warrantyPeriod;
  final ProductWarranty? warranty;
  final String? deliveryTime;
  final int? maxPurchaseQuantity;
  final List<ShopVariant> shopVariants;
}

class ShopVariant {
  final int? id;
  final int? shopId;
  final num price;
  final int? quantity; // stock — nullable
}
```

Parse آمن:

```dart
int? asInt(dynamic v) {
  if (v == null) return null;
  if (v is int) return v;
  return int.tryParse('$v');
}
```

---

## 13) Checklist

### أقسام وفئات
- [ ] ويدجت أقسام موحّد (`layout` ثم `variant`)
- [ ] دوائر لكل مستوى + منتجات معها
- [ ] لا فلترة محلية على `category_id`

### منتج + سلة
- [ ] `country` كـ `String?`
- [ ] لا `shopVariants[0]` بدون حماية
- [ ] **ضمان:** `warranty.name` / `.description`
- [ ] **كمية:** `ShopVariant.quantity` كـ `int?`
- [ ] `canAddToCart`: id + shopId + qty > 0
- [ ] لا Cartesian
- [ ] سلة مخصصة: كروت من `image` / `images` / `top_badges` + تخصيص `/schedules/{id}/custom-basket` — [`FLUTTER_CUSTOM_BASKET.md`](./FLUTTER_CUSTOM_BASKET.md)

### باقي التطبيق
- [ ] Nav ديناميكي
- [ ] فلاتر + chips الجذر
- [ ] تسجيل: هاتف مطلوب
- [ ] أسعار من API
- [ ] طلب سريع من `settings.quick_order`

---

## 14) السلة المخصصة

> الدليل الكامل للإرسال: [`FLUTTER_CUSTOM_BASKET.md`](./FLUTTER_CUSTOM_BASKET.md) — **5 أيلول 2026 مساءً**

كروت: `GET /api/user/schedules` — صورة دائرية + وصف + بادجز.

عقد ثابت: `image` (URL) · `images` (`List<String>`) · `top_badges` / `bottom_badges`. لا `cover_image` / `photo` / `gallery`. `name` = `String`. `discount_type` = `percentage` \| `fixed` \| `null`.

تخصيص داخل فئة (`Auth`):

| | Endpoint |
|--|----------|
| هيدر + مسودة | `GET /api/user/schedules/{id}/custom-basket` |
| إضافة | `POST .../items` `{ shop_product_variant_id, quantity }` |
| كمية | `PUT .../items/{itemId}` `{ quantity }` |
| حذف | `DELETE .../items/{itemId}` |
| تأكيد | `POST .../confirm` |

نعم: `{ "confirm_schedule": true, "start_date": "2026-09-08" }` → طلباتي المجدولة + `cart_items`  
لا: `{ "confirm_schedule": false }` → `cart_items` مرة واحدة

`summary.savings` = وفّرت. لا تحسبوا خصم الفئة على الجهاز.
