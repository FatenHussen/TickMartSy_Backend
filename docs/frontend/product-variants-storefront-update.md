# آخر تحديث — متغيّرات المنتج (Flutter + Web)

> **أرسلوا هذا الملف لفريق Flutter و Web معاً.**  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **آخر تحديث:** 2026-08-30

---

## الفهرس

1. [ملخص التغييرات](#1-ملخص-التغييرات)
2. [Endpoint](#2-endpoint)
3. [شكل shop_variants (الحقيقي من الباك)](#3-شكل-shop_variants-الحقيقي-من-الباك)
4. [الخصم على مستوى المتغيّر](#4-الخصم-على-مستوى-المتغيّر)
5. [اختيار المتغيّر — منطق مشترك](#5-اختيار-المتغيّر--منطق-مشترك)
6. [حالات edge](#6-حالات-edge)
7. [Flutter — مطلوب](#7-flutter--مطلوب)
8. [Web — مطلوب](#8-web--مطلوب)
9. [Checklist مشترك](#9-checklist-مشترك)
10. [مراجع](#10-مراجع)

---

## 1) ملخص التغييرات

| # | التغيير | Flutter | Web |
|---|---------|---------|-----|
| 1 | **السعر والكمية والخصم** صاروا على `product_variants` — مو على `shop_product_variants` | ✅ | ✅ |
| 2 | **خصم per variant** — حقول جديدة: `discount_value` + `discount_type` | ✅ | ✅ |
| 3 | **لا اسم متغيّr** — الهوية من `attributes` (صفة + قيمة) + `sku` | ✅ | ✅ |
| 4 | `shop_variants` **دائماً عنصر واحد على الأقل** (fallback من المنتج) | ✅ | ✅ |
| 5 | `shop_variants[].id` و `shop_id` ممكن **`null`** — منتجات المنصة | ✅ | ✅ |
| 6 | `country` في صفحة المنتج = **string** (مو object) | ✅ | ✅ |
| 7 | `attributes` = `{ attribute, value, type }` — **مو** `id` / `category_attribute` | ✅ | ✅ |
| 8 | الأسعار بعملتين عبر `*_currencies.USD` / `*_currencies.SYP` | ✅ | ✅ |
| 9 | `delivery_time` على **مستوى المنتج** — ليس per variant | ✅ | ✅ |
| 10 | `quantity` = **حقل واحد** للمخزون — لا `stock` منفصل | ✅ | ✅ |

### محذوف / لا تعتمد عليه

- ~~`variant.name`~~ — غير موجود في استجابة المتجر
- ~~`shop_variants[].price` من ربط المحل~~ — السعر من المتغيّر الأساسي
- ~~افتراض `shop_variants[0]` بدون حماية~~ — يسبب crash
- ~~Cartesian كامل (كل لون × كل مقاس)~~ — اعرض فقط التركيبات الموجودة في API
- ~~`price_usd` / `price_syp` كحقول flat~~ — استخدم `price_currencies`

---

## 2) Endpoint

```http
GET /api/user/products/{id}?lat={lat}&lng={lng}
Accept-Language: ar
Authorization: Bearer {token}   ← اختياري
```

حقول المنتج المرتبطة بالمتغيّرات:

| الحقل | الاستخدام |
|-------|-----------|
| `attributes_map` | خريطة الصفات المتاحة (لون، مقاس...) — لبناء الـ picker |
| `shop_variants` | كل تركيبة + سعر + كمية + خصم |
| `delivery_time` | موعد التسليم — **مرة واحدة** في الصفحة |
| `max_purchase_quantity` | حد أقصى للشراء (اختياري) |

---

## 3) شكل shop_variants (الحقيقي من الباك)

### منتج بمتغيّرات (مربوط بفرع)

```json
{
  "delivery_time": "3-5 أيام",
  "attributes_map": [
    {
      "attribute": "لون",
      "type": "color",
      "values": ["أحمر", "أسود"]
    },
    {
      "attribute": "قياس",
      "type": "square",
      "values": ["S", "M", "L"]
    }
  ],
  "shop_variants": [
    {
      "id": 55,
      "variant_id": 15,
      "sku": "SWEAT-RED-M",
      "model": "4280",
      "barcode": "1234567890",
      "attributes": [
        { "attribute": "لون", "value": "أحمر", "type": "color" },
        { "attribute": "قياس", "value": "M", "type": "square" }
      ],
      "price": 25,
      "currency": "USD",
      "currency_symbol": "$",
      "price_formatted": "$ 25",
      "price_currencies": {
        "USD": { "amount": 25, "currency": "USD", "symbol": "$", "formatted": "$ 25" },
        "SYP": { "amount": 325000, "currency": "SYP", "symbol": "ل.س", "formatted": "ل.س 325,000" }
      },
      "discount_value": 10,
      "discount_type": "percentage",
      "discount": 2.5,
      "currency": "USD",
      "discount_formatted": "$ 2.5",
      "discount_currencies": {
        "USD": { "amount": 2.5, "symbol": "$", "formatted": "$ 2.5" },
        "SYP": { "amount": 32500, "symbol": "ل.س", "formatted": "ل.س 32,500" }
      },
      "price_after_discount": 22.5,
      "price_after_discount_formatted": "$ 22.5",
      "price_after_discount_currencies": {
        "USD": { "amount": 22.5, "symbol": "$", "formatted": "$ 22.5" },
        "SYP": { "amount": 292500, "symbol": "ل.س", "formatted": "ل.س 292,500" }
      },
      "quantity": 8,
      "shop_id": 1,
      "is_restaurant": false,
      "city_id": 3,
      "images": [{ "id": 1, "path": "https://cdn.example.com/variant.webp" }]
    }
  ]
}
```

### fallback — منتج بدون متغيّr / بدون فرع

```json
{
  "shop_variants": [{
    "id": null,
    "variant_id": null,
    "sku": "LIG-8188-BASE",
    "attributes": [],
    "price": 20,
    "price_currencies": { "USD": { "amount": 20 }, "SYP": { "amount": 260000 } },
    "discount_value": 0,
    "discount_type": "none",
    "discount": 0,
    "price_after_discount": 20,
    "quantity": 100,
    "shop_id": null,
    "images": [{ "id": 373, "path": "https://..." }]
  }]
}
```

### قراءة الأسعار

| للعرض | الحقل |
|-------|-------|
| USD | `price_currencies.USD.amount` |
| SYP | `price_currencies.SYP.amount` |
| بعد الخصm USD | `price_after_discount_currencies.USD.amount` |
| بعد الخصm SYP | `price_after_discount_currencies.SYP.amount` |
| العملة الافتراضية للمستخدم | `price` / `price_after_discount` |

> **لا تحسب** سعر الصرف محلياً — استخدم `*_currencies` من API.

---

## 4) الخصm على مستوى المتغيّr

**أولوية الخصm (محسوبة من الباك):**

1. خصm المتغيّr (`discount_value` + `discount_type`)
2. وإلا خصm المنتج / Flash Sale

| الحقل | المعنى |
|-------|--------|
| `discount_value` | القيمة المُدخلة (مثلاً `10` لـ 10%) |
| `discount_type` | `none` \| `percentage` \| `fixed` |
| `discount` | **المبلغ المخصوم** (للعرض — «وفّرت X») |
| `price_after_discount` | السعر النهائي |

```js
// Web
function hasVariantDiscount(v) {
  return (v.discount ?? 0) > 0 && v.discount_type !== 'none';
}

function discountBadge(v) {
  return v.discount_type === 'percentage' && v.discount_value > 0
    ? `-${v.discount_value}%`
    : null;
}
```

```dart
// Flutter
bool get hasDiscount =>
    discountType != 'none' && discountAmount > 0;

String? get badge =>
    discountType == 'percentage' && discountValue > 0
        ? '-$discountValue%'
        : null;
```

---

## 5) اختيار المتغيّr — منطق مشترك

```
┌─────────────────────────────────────┐
│  اللون:  ● أحمر  ○ أسود           │
│  المقاس: [ S ] [ M ] [ L ]         │
│                                     │
│  السعر: 22.5 $  (كان 25 $)        │
│  متوفر: 8                           │
│  التسليم: 3-5 أيام  ← product      │
└─────────────────────────────────────┘
```

### بناء خريطة الصفات

```js
// من attributes_map (مفضّل) أو استنتاج من shop_variants
function buildPickerFromMap(attributesMap) {
  return attributesMap.map(row => ({
    name: row.attribute,
    type: row.type,
    values: row.values,
  }));
}
```

### إيجاد المتغيّr المختار

> المطابقة بـ **`attribute` + `value`** (string) — ليس بـ `id`.

```js
function findVariant(variants, selected) {
  // selected = { "لون": "أحمر", "قياس": "M" }
  const entries = Object.entries(selected).filter(([, v]) => v);
  if (!entries.length) return null;

  return variants.find(v =>
    entries.every(([attr, val]) =>
      (v.attributes ?? []).some(
        a => a.attribute === attr && a.value === val
      )
    )
  ) ?? null;
}
```

```dart
ShopVariant? findVariant(
  List<ShopVariant> variants,
  Map<String, String> selected, // attribute name → value
) {
  if (selected.isEmpty) return null;
  for (final v in variants) {
    final ok = selected.entries.every((e) =>
      v.attributes.any((a) => a.attribute == e.key && a.value == e.value));
    if (ok) return v;
  }
  return null;
}
```

### فلترة القيم المتاحة

```js
function availableValues(variants, attributeName, currentSelection) {
  const filtered = variants.filter(v => {
    return Object.entries(currentSelection).every(([attr, val]) => {
      if (attr === attributeName || !val) return true;
      return (v.attributes ?? []).some(a => a.attribute === attr && a.value === val);
    });
  });
  return [...new Set(
    filtered.flatMap(v =>
      (v.attributes ?? [])
        .filter(a => a.attribute === attributeName)
        .map(a => a.value)
    )
  )];
}
```

### قواعد UX

1. اعرض **فقط** التركيبات الموجودة في `shop_variants` — لا Cartesian افتراضي.
2. عند تغيير اللون → أعد تعيين المقاس إذا لم يعد متاحاً.
3. `quantity <= 0` → «غير متوفر» + تعطيل «أضف للسلة».
4. منتج بدون صفات → اختر `shop_variants[0]` تلقائياً.
5. **لا تعرض** اسم متغيّr — استخدم الصفات + `sku` اختياري.

---

## 6) حالات edge

| الحالة | السلوك |
|--------|--------|
| `shop_variants[].id == null` | اعرض السعر/الصور — **لا تضف للسلة** |
| `shop_id == null` | نفس الشي — `canAddToCart = false` |
| `attributes_map` فاضي + `attributes: []` | منتج بس variant واحد — اختيار تلقائي |
| `quantity == 0` | «غير متوفر» |
| لا صور على المتغيّr | fallback: `variant.images` → `product.images` → `thumbnail` |
| `country` | string — `"تركيا"` / `"Turkey"` — **مو** `{ id, name: {...} }` |

```js
const canAddToCart = Boolean(
  selected?.id &&
  selected?.shop_id &&
  (selected.quantity ?? 0) > 0
);
```

```dart
bool get canAddToCart =>
    id != null &&
    shopId != null &&
    quantity > 0;
```

### إضافة للسلة

```http
POST /api/user/cart/items
```

```json
{
  "shop_product_variant_id": 55,
  "quantity": 1
}
```

`shop_product_variant_id` = `shop_variants[].id` (ليس `variant_id`).

---

## 7) Flutter — مطلوب

| الملف / المنطقة | التعديل |
|-----------------|---------|
| Model `ShopVariant` | أضف `discountValue`, `discountType` — اقرأ من `price_currencies` |
| Model `VariantAttribute` | `attribute`, `value`, `type` — **احذف** `id` / `category_attribute` |
| Product detail | بناء picker من `attributes_map` |
| Matching | `Map<String, String>` (اسم صفة → قيمة) |
| Price widget | `price_after_discount_currencies` + شارة `-X%` |
| canAddToCart | `id != null && shopId != null && quantity > 0` |
| country | `String?` — مو nested object |
| delivery_time | من `product.deliveryTime` — مرة واحدة |

**الدليل التفصيلي:** [`product-variants-flutter.md`](product-variants-flutter.md)

---

## 8) Web — مطلوب

| الملف / المنطقة | التعديل |
|-----------------|---------|
| Product page | حماية `shop_variants?.[0]` — optional chaining |
| Attribute picker | swatches للون (`type === 'color'`) + أزرار للمقاس |
| Matching | `attribute` + `value` strings |
| Price block | `price_currencies` / `price_after_discount_currencies` |
| Discount UI | خط على السعر الأصلي + `price_after_discount` + badge |
| Add to cart | `disabled` إذا `!id \|\| !shop_id \|\| quantity <= 0` |
| country | string مباشرة في JSX |
| Images | `images[].path` — مو `url` |

**الدليل التفصيلي:** [`product-variants-web.md`](product-variants-web.md)

---

## 9) Checklist مشترك

- [ ] **لا crash** على `shop_variants[0]` — استخدم fallback آمن
- [ ] **لا اسم متغيّr** — `attributes` + `sku` فقط
- [ ] مطابقة بـ `attribute` + `value` (مو `id`)
- [ ] picker من `attributes_map` أو استنتاج من variants
- [ ] فلترة القيم حسب الاختيار الحالي (فقط المتاح)
- [ ] لا Cartesian افتراضي
- [ ] `quantity` = مخزون واحد
- [ ] خصm: `discount_value` + `discount_type` → `price_after_discount`
- [ ] أسعار USD/SYP من `*_currencies` — بدون تحويل محلي
- [ ] `delivery_time` من المنتج
- [ ] `canAddToCart` يتطلب `id` + `shop_id` + `quantity > 0`
- [ ] fallback الصور: variant → product → thumbnail
- [ ] `country` = string

---

## 10) مراجع

| الجمهور | الملف |
|---------|-------|
| Flutter (تفصيلي) | [`product-variants-flutter.md`](product-variants-flutter.md) |
| Web (تفصيلي) | [`product-variants-web.md`](product-variants-web.md) |
| Dashboard (إدارة) | [`product-variants-dashboard.md`](product-variants-dashboard.md) |
| Flutter (عام) | [`flutter.md`](flutter.md) §13 |
| Web (عام) | [`web.md`](web.md) §10 |
| API Admin | [`../api/ADMIN_PRODUCT_VARIANTS_UPDATE_DELETE.md`](../api/ADMIN_PRODUCT_VARIANTS_UPDATE_DELETE.md) |

---

## ملخص سريع

```
GET /products/{id}
    ↓
attributes_map → بناء picker (لون · مقاس · ...)
shop_variants  → مطابقة attribute+value → سعر · خصm · quantity
    ↓
canAddToCart?  id + shop_id + quantity > 0
    ↓
POST /cart/items  { shop_product_variant_id: id }
```

**الباك جاهز — التعديل في الواجهة فقط.**
