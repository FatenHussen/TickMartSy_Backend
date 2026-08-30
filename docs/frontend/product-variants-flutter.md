# Flutter — عرض متغيّرات المنتج (صفات + سعر + خصم + كمية)

> **أرسلوا هذا الملف لفريق Flutter فقط.**  
> Base: `/api/user` + `Accept-Language: ar|en`.  
> **آخر تحديث:** 2026-08-30

---

## الفهرس

1. [نظرة عامة](#1-نظرة-عامة)
2. [جلب صفات الفئة](#2-جلب-صفات-الفئة)
3. [صفحة المنتج — shop_variants](#3-صفحة-المنتج--shop_variants)
4. [اختيار المتغيّر (UX)](#4-اختيار-المتغيّر-ux)
5. [السعر USD / SYP](#5-السعر-usd--syp)
6. [الخصم على مستوى المتغيّر](#6-الخصم-على-مستوى-المتغيّر)
7. [الكمية والمخزون](#7-الكمية-والمخزون)
8. [موعد التسليم](#8-موعد-التسليم)
9. [Dart Models](#9-dart-models)
10. [Checklist](#10-checklist)

---

## 1) نظرة عامة

كل منتج قد يحتوي متغيّرات (SKU) حسب صفات الفئة (لون، مقاس، تصميم...).

| المفهوم | API |
|---------|-----|
| صفات الفئة | `GET /api/user/categories/{id}/attributes` |
| تفاصيل المنتج + متغيّرات | `GET /api/user/products/{id}` |
| إضافة للسلة | `shop_product_variant_id` من المتغيّر المختار |

**ملاحظات:**
- إضافة/تعديل المتغيّرات **من الداشبورد فقط** — التطبيق يعرض ويختار.
- **لا يوجد اسم متغيّر** في API — الهوية من `attributes` (لون، مقاس...) + `sku`.

---

## 2) جلب صفات الفئة

```http
GET /api/user/categories/{categoryId}/attributes
Accept-Language: ar
```

```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "name": "لون",
      "type": "color",
      "values": [
        { "id": 6, "name": "أحمر", "hex": "#FF0000" }
      ]
    },
    {
      "id": 2,
      "name": "قياس",
      "type": "square",
      "values": [
        { "id": 10, "name": "S" },
        { "id": 11, "name": "M" }
      ]
    }
  ]
}
```

استخدم `attributes_map` من صفحة المنتج إن وُجد — يوفّر نفس البيانات مرتبة حسب المتغيّرات المتاحة.

---

## 3) صفحة المنتج — shop_variants

```http
GET /api/user/products/{id}?lat=...&lng=...
```

### شكل المتغيّر

```json
{
  "delivery_time": "3-5 أيام",
  "shop_variants": [
    {
      "id": 55,
      "variant_id": 15,
      "sku": "SWEAT-RED-M",
      "price": 25,
      "price_usd": 25,
      "price_syp": 325000,
      "price_currencies": {
        "USD": { "amount": 25, "symbol": "$", "formatted": "$ 25" },
        "SYP": { "amount": 325000, "symbol": "ل.س", "formatted": "ل.س 325000" }
      },
      "discount_value": 10,
      "discount_type": "percentage",
      "discount": 2.5,
      "discount_usd": 2.5,
      "discount_syp": 32500,
      "price_after_discount": 22.5,
      "price_after_discount_usd": 22.5,
      "price_after_discount_syp": 292500,
      "price_after_discount_currencies": {
        "USD": { "amount": 22.5, "symbol": "$", "formatted": "$ 22.5" },
        "SYP": { "amount": 292500, "symbol": "ل.س", "formatted": "ل.س 292500" }
      },
      "quantity": 8,
      "shop_id": 1,
      "attributes": [
        {
          "id": 6,
          "name": "أحمر",
          "hex": "#FF0000",
          "category_attribute": { "id": 1, "name": "لون", "type": "color" }
        },
        {
          "id": 11,
          "name": "M",
          "category_attribute": { "id": 2, "name": "قياس", "type": "square" }
        }
      ],
      "images": [{ "id": 1, "url": "https://..." }]
    }
  ]
}
```

### حقول مهمة

| الحقل | الاستخدام |
|-------|-----------|
| `id` | `shop_product_variant_id` للسلة — قد يكون `null` لمنتجات المنصة |
| `variant_id` | ID المتغيّر الأساسي |
| `sku` | معرّف اختياري — **لا يوجد `name`** |
| `attributes` | **هوية المتغيّر** — لون + مقاس... |
| `price` / `price_usd` / `price_syp` | السعر الأصلي بعملتين |
| `quantity` | المخزون المتاح — **حقل واحد** |
| `discount_value` + `discount_type` | إعداد الخصم (10 + percentage) |
| `discount` | **المبلغ المخصوم** (للعرض) |
| `price_after_discount` | السعر النهائي للعرض |

---

## 4) اختيار المتغيّر (UX)

### منتج بصفات (كنزة / حذاء)

```
┌─────────────────────────────────────┐
│  اللون:  ● أحمر  ○ أسود  ○ فضي    │
│  المقاس: [ S ] [ M ] [ L ]         │
│                                     │
│  السعر: 22.5 $  (كان 25 $)       │
│  أو:    292,500 ل.س                │
│  متوفر: 8                           │
│  التسليم: 3-5 أيام                  │
└─────────────────────────────────────┘
```

**المنطق:**

1. اعرض صفات من `attributes_map` أو استنتجها من `shop_variants[].attributes`.
2. عند اختيار لون → فلتر المقاسات المتاحة لهذا اللون فقط.
3. عند اختيار مقاس → حدّد `shop_variant` المطابق.
4. إذا `quantity <= 0` → عطّل «أضف للسلة».
5. **لا تفترض** أن كل لون × كل مقاس موجود — اعرض فقط ما في `shop_variants`.

```dart
ShopVariant? findVariant(
  List<ShopVariant> variants,
  Map<int, int> selectedValueIds, // categoryAttributeId → valueId
) {
  return variants.cast<ShopVariant?>().firstWhere(
    (v) {
      if (v == null) return false;
      final ids = v.attributes.map((a) => a.id).toSet();
      return selectedValueIds.values.every(ids.contains);
    },
    orElse: () => null,
  );
}
```

### منتج بدون صفات

- `shop_variants` عنصر واحد على الأقل.
- اختيار تلقائي للعنصر الأول.

---

## 5) السعر USD / SYP

الباك يرجع السعر **بالعملتين** تلقائياً من سعر USD المخزّن.

| للعرض | الحقل |
|-------|-------|
| سعر بالدولار | `price_usd` أو `price_currencies.USD.amount` |
| سعر بالليرة | `price_syp` أو `price_currencies.SYP.amount` |
| السعر بعد الخصم (USD) | `price_after_discount_usd` |
| السعر بعد الخصم (SYP) | `price_after_discount_syp` |

```dart
String formatVariantPrice(ShopVariant v, {required bool useSyp}) {
  final hasDiscount = v.discountAmount > 0;
  if (useSyp) {
    return hasDiscount
        ? '${v.priceAfterDiscountSyp} ل.س'
        : '${v.priceSyp} ل.س';
  }
  return hasDiscount
      ? '${v.priceAfterDiscountUsd} \$'
      : '${v.priceUsd} \$';
}
```

- اعرض العملة حسب إعدادات المستخدم أو `currency` من الاستجابة.
- **لا تحسب** التحويل محلياً — استخدم قيم API.

---

## 6) الخصم على مستوى المتغيّر

**أولوية الخصم (من الباك):**
1. خصم المتغيّر (`discount_value` + `discount_type`)
2. وإلا خصم المنتج / Flash Sale

### عرض السعر

```dart
Widget buildPrice(ShopVariant v) {
  if (v.discountAmount > 0) {
    return Row(
      children: [
        Text('${v.priceAfterDiscount} \$', style: boldStyle),
        Text('${v.price} \$', style: strikethroughStyle),
        if (v.discountType == 'percentage')
          Badge(text: '-${v.discountValue}%'),
      ],
    );
  }
  return Text('${v.price} \$');
}
```

| للعرض | استخدم |
|-------|--------|
| السعر الأصلي | `price` / `price_usd` / `price_syp` |
| السعر بعد الخصم | `price_after_discount` (+ `_usd` / `_syp`) |
| مبلغ التوفير | `discount` (المبلغ) |
| شارة «-10%» | `discount_value` + `discount_type == 'percentage'` |

---

## 7) الكمية والمخزون

- **حقل واحد:** `quantity` على المتغيّر — «الكمية المتوفرة».
- لا يوجد `stock` منفصل على المتغيّر.
- `canAddToCart = quantity > 0 && selectedVariant != null`.

```dart
bool get canAddToCart =>
    selectedVariant != null &&
    (selectedVariant!.quantity ?? 0) > 0 &&
    selectedVariant!.id != null;
```

---

## 8) موعد التسليم

- **على مستوى المنتج** — `product.delivery_time` (ليس per variant).
- اعرضه مرة واحدة في صفحة المنتج بجانب السعر/التوفر.

```dart
Text(product.deliveryTime ?? '—'); // "3-5 أيام"
```

---

## 9) Dart Models

```dart
class ShopVariant {
  final int? id;
  final int variantId;
  final String? sku;
  final double price;
  final double priceUsd;
  final double priceSyp;
  final int discountValue;
  final String discountType;
  final double discountAmount;
  final double priceAfterDiscount;
  final double priceAfterDiscountUsd;
  final double priceAfterDiscountSyp;
  final int quantity;
  final int? shopId;
  final List<VariantAttribute> attributes;
  final List<ProductImage> images;

  factory ShopVariant.fromJson(Map<String, dynamic> json) => ShopVariant(
    id: json['id'],
    variantId: json['variant_id'] ?? 0,
    sku: json['sku'],
    price: (json['price'] as num?)?.toDouble() ?? 0,
    priceUsd: (json['price_usd'] as num?)?.toDouble()
        ?? (json['price_currencies']?['USD']?['amount'] as num?)?.toDouble()
        ?? (json['price'] as num?)?.toDouble()
        ?? 0,
    priceSyp: (json['price_syp'] as num?)?.toDouble()
        ?? (json['price_currencies']?['SYP']?['amount'] as num?)?.toDouble()
        ?? 0,
    discountValue: json['discount_value'] ?? 0,
    discountType: json['discount_type'] ?? 'none',
    discountAmount: (json['discount'] as num?)?.toDouble() ?? 0,
    priceAfterDiscount: (json['price_after_discount'] as num?)?.toDouble() ?? 0,
    priceAfterDiscountUsd: (json['price_after_discount_usd'] as num?)?.toDouble()
        ?? (json['price_after_discount'] as num?)?.toDouble()
        ?? 0,
    priceAfterDiscountSyp: (json['price_after_discount_syp'] as num?)?.toDouble() ?? 0,
    quantity: json['quantity'] ?? 0,
    shopId: json['shop_id'],
    attributes: (json['attributes'] as List?)
        ?.map((e) => VariantAttribute.fromJson(e))
        .toList() ?? [],
    images: (json['images'] as List?)
        ?.map((e) => ProductImage.fromJson(e))
        .toList() ?? [],
  );
}

class VariantAttribute {
  final int id;
  final String name;
  final String? hex;
  final CategoryAttributeRef categoryAttribute;

  factory VariantAttribute.fromJson(Map<String, dynamic> json) =>
      VariantAttribute(
        id: json['id'],
        name: json['name'] ?? '',
        hex: json['hex'],
        categoryAttribute: CategoryAttributeRef.fromJson(
          json['category_attribute'] ?? {},
        ),
      );
}
```

---

## 10) Checklist

- [ ] **لا تعرض** اسم متغيّر — استخدم `attributes` + `sku`
- [ ] اعرض صفات من `attributes_map` / `shop_variants`
- [ ] فلتر المقاسات حسب اللون المختار (فقط المتاح)
- [ ] لا تفترض Cartesian كامل — اعرض ما يُرجعه API فقط
- [ ] `quantity` حقل واحد للمخزون
- [ ] اعرض `price_after_discount` + شارة خصم
- [ ] دعم **USD و SYP** من API (لا تحويل محلي)
- [ ] `delivery_time` من المنتج — ليس per variant
- [ ] `shop_product_variant_id` = `id` (مع حماية null)
- [ ] fallback الصور: variant → product → thumbnail
