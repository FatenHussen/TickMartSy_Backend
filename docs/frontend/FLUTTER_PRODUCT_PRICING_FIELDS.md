# Flutter — سعر · خصم · كمية · باركود · SKU (كارد + تفاصيل المنتج)

> **أرسلوا هذا الملف لفريق Flutter (التطبيق) فقط.**  
> **آخر تحديث:** 8 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز — التعديل UI فقط**  
> الداشبورد (إدخال الحقول): [`DASHBOARD_PRODUCT_PRICING_FIELDS.md`](./DASHBOARD_PRODUCT_PRICING_FIELDS.md)  
> الويب: [`WEB_PRODUCT_PRICING_FIELDS.md`](./WEB_PRODUCT_PRICING_FIELDS.md)

نفس الحقول اللي الأدمن بيملأها في تاب معلومات المنتج / كارد المتغيّر — التطبيق **يعرضها**.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [الحقول — تسمية الداشبورد ↔ API](#2-الحقول--تسمية-الداشبورد--api)
3. [كارد القائمة](#3-كارد-القائمة)
4. [شاشة تفاصيل المنتج](#4-شاشة-تفاصيل-المنتج)
5. [عند اختيار متغيّر](#5-عند-اختيار-متغيّر)
6. [دولار + ليرة](#6-دولار--ليرة)
7. [نوع الخصم · لا يوجد خصم · السعر بعد الخصم](#7-نوع-الخصم--لا-يوجد-خصم--السعر-بعد-الخصم)
8. [الكمية · الباركود · SKU](#8-الكمية--الباركود--sku)
9. [Dart models](#9-dart-models)
10. [Checklist](#10-checklist)

---

## 1) القاعدة

كل منتج يملك هالحقول. المصدر حسب الشاشة:

| شاشة | المصدر |
|------|--------|
| **كارد** (هوم / فئة / بحث / سكشن) | معلومات **المنتج** — `GET /api/user/products` |
| **تفاصيل بلا متغيّرات** | `shop_variants.first` (fallback أو متغيّر مخفي من بيانات المنتج) |
| **تفاصيل مع متغيّرات** | المتغيّر المختار — عند التغيير تتبدّل كل الحقول |

`shop_variants` **دائماً فيها عنصر**. لا تستخدموا `shopVariants[0]` بدون فحص.

---

## 2) الحقول — تسمية الداشبورد ↔ API

| # | تسمية الداشبورد | كارد القائمة | شاشة التفاصيل (`shop_variants[i]`) |
|---|-----------------|--------------|--------------------------------------|
| 1 | سعر المتغير (دولار) | `price_currencies.USD` | `price_currencies.USD` |
| 2 | سعر المتغيّر (ليرة سورية) | `price_currencies.SYP` | `price_currencies.SYP` |
| 3 | نوع الخصم | غير موجود على الكرت | `discount_type` |
| 4 | لا يوجد خصم | `price == price_after_discount` | `discount_type == 'none'` |
| 5 | قيمة الخصم | لا تستخدموا `discount` على الكرت (سترينغ فاضي) | `discount_value` |
| 6 | السعر بعد الخصم | `price_after_discount_currencies` | `price_after_discount_currencies` |
| 7 | الكمية المتوفرة | `quantity` (`int?`) | `quantity` (`int?`) |
| 8 | الباركود | غير موجود على الكرت | `barcode` |
| 9 | رمز التخزين التعريفي للمتغير | غير موجود على الكرت | `sku` |

**لا تخلطوا:**

| المفتاح | المعنى |
|---------|--------|
| `discount_value` | الرقم اللي أدمنه الأدمن |
| `discount_type` | `none` \| `percentage` \| `fixed` |
| `discount` + `discount_currencies` | مبلغ **التوفير** للعرض |

لا تحسبوا الخصم محلياً. `price_after_discount*` جاهز من الباك.

---

## 3) كارد القائمة

```http
GET /api/user/products
GET /api/user/sections
```

من حقول **المنتج** — مو أول متغيّر:

```
┌─ كارد ────────────────────────────────┐
│  صورة                                 │
│  الاسم                                │
│  بعد الخصم:  $ …   ل.س …              │
│  الأصلي مشطوب إذا في خصم              │
│  الكمية اختيارية                      │
└───────────────────────────────────────┘
```

```dart
class ProductCardPricing {
  final Money? usd;
  final Money? syp;
  final Money? usdAfter;
  final Money? sypAfter;
  final int? quantity;

  bool get hasDiscount {
    final a = usd?.amount;
    final b = usdAfter?.amount;
    if (a == null || b == null) return false;
    return b < a;
  }
}
```

- `quantity` على الكرت ممكن `null` — لا `quantity!`.
- لا تعرضوا SKU ولا باركود على الكرت.
- الضغط → شاشة المنتج `{id}`.

---

## 4) شاشة تفاصيل المنتج

```http
GET /api/user/products/{id}?lat=&lng=
```

### بدون صفات (`attributes_map` فاضي)

ما في اختيار لون/مقاس. اعرضوا `shop_variants.first`:

سعر $ · سعر ل.س · خصم · بعد الخصم · الكمية المتوفرة · الباركود · SKU

### مع متغيّرات

1. Picker من `attributes_map`.
2. طابقوا القيم مع `shop_variants[].attributes`.
3. كل الحقول من الـ variant المختار.

الحالة الابتدائية: أول مطابقة أو `shop_variants.first`.

---

## 5) عند اختيار متغيّر

`setState` / Cubit يبدّل **كل** الحقول من نفس الـ `ShopVariant`:

```
سعر $ · سعر ل.س · نوع الخصم · قيمة الخصم · السعر بعد الخصم
الكمية المتوفرة · الباركود · SKU · الصور
```

```dart
void onVariantChanged(ShopVariant variant) {
  emit(state.copyWith(selected: variant));
}
```

لا تخلّوا السعر من `ProductDetail` والكمية من المتغيّر.

### السلة

```json
{ "shop_product_variant_id": 55, "quantity": 1 }
```

`id` أو `shopId` = `null` → الشاشة تشتغل والسلة معطّلة. لا ترسلوا سعراً.

---

## 6) دولار + ليرة

`*_currencies` فقط. لا `exchange_rate` محلي. لا تعتمدوا `price_usd` / `price_syp` flat.

```dart
class Money {
  final num? amount;
  final String? symbol;
  final String? formatted;

  factory Money.fromJson(Map<String, dynamic>? json) {
    if (json == null) return const Money();
    return Money(
      amount: json['amount'] as num?,
      symbol: json['symbol'] as String?,
      formatted: json['formatted'] as String?,
    );
  }

  const Money({this.amount, this.symbol, this.formatted});
}

Money? currencyOf(Map<String, dynamic>? currencies, String code) {
  final raw = currencies?[code];
  if (raw is! Map<String, dynamic>) return null;
  return Money.fromJson(raw);
}
```

```dart
final usd = currencyOf(variant.priceCurrencies, 'USD'); // سعر المتغير (دولار)
final syp = currencyOf(variant.priceCurrencies, 'SYP'); // سعر المتغيّر (ليرة سورية)
final usdAfter = currencyOf(variant.priceAfterDiscountCurrencies, 'USD');
final sypAfter = currencyOf(variant.priceAfterDiscountCurrencies, 'SYP');
```

---

## 7) نوع الخصم · لا يوجد خصم · السعر بعد الخصم

| `discountType` | الواجهة |
|----------------|--------|
| `none` أو `null` (fallback) | **لا يوجد خصم** — أخفوا قيمة الخصم |
| `percentage` | `discountValue%` — الأصلي مشطوب |
| `fixed` | مبلغ ثابت — الأصلي مشطوب |

السعر الأساسي المعروض = بعد الخصم إن وُجد، وإلا الأصلي.

```dart
bool hasDiscount(ShopVariant v) =>
    v.discountType != null &&
    v.discountType != 'none' &&
    (v.discountValue ?? 0) > 0;
```

Fallback المنتج البسيط قد **بدون** `discount_value` / `discount_type`:

```dart
final type = variant.discountType ?? product.discountType ?? 'none';
final cheaper = (variant.priceAfterDiscount ?? variant.price) < variant.price;
```

---

## 8) الكمية · الباركود · SKU

### الكمية المتوفرة

المخزون = `ShopVariant.quantity` — **ليس** كمية المنتج.

| القيمة | العرض |
|--------|--------|
| > 0 | «الكمية المتوفرة: N» |
| `0` أو `null` | غير متوفر — عطّلوا السلة |

```dart
bool canAddToCart(ShopVariant? v) =>
    v?.id != null &&
    v?.shopId != null &&
    (v?.quantity ?? 0) > 0;
```

### الباركود

- `variant.barcode`
- مثال: `6291101234567`
- `null` / فاضي → أخفوا الصف

### رمز التخزين التعريفي للمتغير

- `variant.sku` — إنجليزي مثل `LIG-8188-GREEN-L`
- `null` → أخفوا الصف
- لا حقل اسم متغيّر — الهوية من `attributes` + `sku`

---

## 9) Dart models

```dart
class ShopVariant {
  final int? id;
  final int? variantId;
  final int? shopId;
  final String? sku;
  final String? barcode;
  final num price;
  final num? priceAfterDiscount;
  final int? discountValue;
  final String? discountType; // none | percentage | fixed
  final int? quantity;
  final Map<String, dynamic>? priceCurrencies;
  final Map<String, dynamic>? priceAfterDiscountCurrencies;
  final Map<String, dynamic>? discountCurrencies;
  final List<VariantAttribute> attributes;

  factory ShopVariant.fromJson(Map<String, dynamic> json) {
    return ShopVariant(
      id: asInt(json['id']),
      variantId: asInt(json['variant_id']),
      shopId: asInt(json['shop_id']),
      sku: json['sku'] as String?,
      barcode: json['barcode'] as String?,
      price: (json['price'] as num?) ?? 0,
      priceAfterDiscount: json['price_after_discount'] as num?,
      discountValue: asInt(json['discount_value']),
      discountType: json['discount_type'] as String?,
      quantity: asInt(json['quantity']),
      priceCurrencies: json['price_currencies'] as Map<String, dynamic>?,
      priceAfterDiscountCurrencies:
          json['price_after_discount_currencies'] as Map<String, dynamic>?,
      discountCurrencies: json['discount_currencies'] as Map<String, dynamic>?,
      attributes: (json['attributes'] as List? ?? [])
          .map((e) => VariantAttribute.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

int? asInt(dynamic v) {
  if (v == null) return null;
  if (v is int) return v;
  return int.tryParse('$v');
}
```

ويدجت عرض واحد — نفس الفكرة للداشبورد بس **قراءة فقط**:

```dart
class ProductPricingReadout extends StatelessWidget {
  const ProductPricingReadout({super.key, required this.variant});

  final ShopVariant variant;

  @override
  Widget build(BuildContext context) {
    final discounted = hasDiscount(variant);
    final usd = currencyOf(variant.priceCurrencies, 'USD');
    final syp = currencyOf(variant.priceCurrencies, 'SYP');
    final usdAfter = currencyOf(variant.priceAfterDiscountCurrencies, 'USD');
    final sypAfter = currencyOf(variant.priceAfterDiscountCurrencies, 'SYP');

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(discounted ? (usdAfter?.formatted ?? '') : (usd?.formatted ?? '')),
        Text(discounted ? (sypAfter?.formatted ?? '') : (syp?.formatted ?? '')),
        if (discounted) ...[
          Text('${usd?.formatted} · ${syp?.formatted}',
              style: const TextStyle(decoration: TextDecoration.lineThrough)),
          Text(variant.discountType == 'percentage'
              ? '${variant.discountValue}%'
              : '${variant.discountValue}'),
        ],
        Text('الكمية المتوفرة: ${variant.quantity ?? 0}'),
        if (variant.barcode != null) Text('الباركود: ${variant.barcode}'),
        if (variant.sku != null) Text('SKU: ${variant.sku}'),
      ],
    );
  }
}
```

مرّروا `state.selected` — يتحدّث لحاله عند تغيير المتغيّر.

---

## 10) Checklist

- [ ] الكارد من حقول **المنتج** — لا أول `shop_variant`
- [ ] لا `discount!` على الكرت (سترينغ فاضي في API القائمة)
- [ ] التفاصيل: كل الحقول من `ShopVariant` المختار
- [ ] `attributes_map` فاضي → `shop_variants.first` بدون picker
- [ ] تغيير اللون/المقاس يبدّل سعر $ · ل.س · خصم · بعد الخصم · كمية · باركود · SKU · صور
- [ ] `*_currencies` فقط — لا تحويل سعر صرف
- [ ] `none` = لا يوجد خصم
- [ ] `discountValue` ≠ مبلغ `discount_currencies`
- [ ] باركود مثال الشكل `6291101234567` — أخفوا إذا null
- [ ] SKU إنجليزي — أخفوا إذا null
- [ ] `canAddToCart`: `id` + `shopId` + `quantity > 0`
- [ ] `quantity` كـ `int?` على الكرت والتفاصيل

**الباك جاهز — التطبيق يتبع هذا الملف.**
