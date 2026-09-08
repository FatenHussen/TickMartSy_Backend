# الداشبورد (Flutter Web) — حقول السعر · الخصم · الكمية · الباركود · SKU

> **أرسلوا هذا الملف لفريق الداشبورد / Flutter Web فقط.**  
> **آخر تحديث:** 8 أيلول 2026  
> Base: `/api/admin` + Admin token  
> **الباك جاهز — التعديل UI فقط**

يلغي قرار «احذفوا الكمية من تاب المعلومات» في [`DASHBOARD_PRODUCT_QUANTITY_VALIDATION.md`](./DASHBOARD_PRODUCT_QUANTITY_VALIDATION.md).  
توست «يجب أن تكون الكمية موجبة» يبقى باگ فرونت: الحقول `.optional()` — **لا** `required`.

---

## الفهرس

1. [الفكرة](#1-الفكرة)
2. [نفس البلوك في مكانين](#2-نفس-البلوك-في-مكانين)
3. [الحقول — تسميات الواجهة ↔ API](#3-الحقول--تسميات-الواجهة--api)
4. [نوع الخصم + السعر بعد الخصم](#4-نوع-الخصم--السعر-بعد-الخصم)
5. [مزامنة دولار ↔ ليرة](#5-مزامنة-دولار--ليرة)
6. [SKU إنجليزي + باركود](#6-sku-إنجليزي--باركود)
7. [Widget Flutter مشترك](#7-widget-flutter-مشترك)
8. [Payload](#8-payload)
9. [التعبئة عند التعديل (GET)](#9-التعبئة-عند-التعديل-get)
10. [الكارد وصفحة التفاصيل (للمتجر)](#10-الكارد-وصفحة-التفاصيل-للمتجر)
11. [Checklist](#11-checklist)

---

## 1) الفكرة

كل منتج **لازم** يملك هالحقول في تاب **معلومات المنتج** (المعلومات العامة).

نفس الحقول تُكرَّر على **كارد المتغيّر** إذا الأدمن أضاف متغيّرات.

| الحالة | وين تتعبّى | وين بتظهر بالمتجر |
|--------|------------|-------------------|
| **بدون متغيّرات** | تاب معلومات المنتج فقط — **لا** كارد متغيّر فاضي | الكارد + صفحة التفاصيل = حقول المنتج |
| **مع متغيّرات** | تاب المعلومات **و** كل كارد متغيّر | الكارد = معلومات المنتج · صفحة التفاصيل = المتغيّر المختار |

لا تولّدوا كارد «المتغير رقم 1» تلقائياً. القائمة فاضية إلى أن يضغط الأدمن «إضافة متغيّر».

---

## 2) نفس البلوك في مكانين

ابنوا **widget واحد** (`ProductPricingFields`) واستعملوه مرتين. لا تنسخوا الفورم.

```
┌─ تاب معلومات المنتج (دائماً ظاهر) ─────────────────────────┐
│  [ProductPricingFields]  →  price / price_syp / …         │
│  الباركود → barcode                                      │
│  SKU المنتج → sku   (مو sku المتغيّر)                    │
└──────────────────────────────────────────────────────────┘

┌─ تاب المتغيّرات (فقط بعد «إضافة متغيّر») ─────────────────┐
│  كارد 1: [ProductPricingFields]  →  variants[i][…]        │
│  كارد 2: [ProductPricingFields]  →  variants[i][…]        │
└──────────────────────────────────────────────────────────┘
```

### تسميات حسب المكان

| تاب المعلومات | كارد المتغيّر |
|---------------|----------------|
| سعر (دولار) | سعر المتغير (دولار) |
| سعر (ليرة سورية) | سعر المتغيّر (ليرة سورية) |
| نوع الخصم | نوع الخصم |
| قيمة الخصم | قيمة الخصم |
| السعر بعد الخصم | السعر بعد الخصم |
| الكمية المتوفرة | الكمية المتوفرة |
| الباركود | الباركود |
| رمز التخزين التعريفي | رمز التخزين التعريفي للمتغير |

---

## 3) الحقول — تسميات الواجهة ↔ API

كلها **اختيارية** في الباك (`nullable`). لا نجمة `*` ولا `required` ولا توست «موجبة».

| # | الواجهة | مفتاح المنتج | مفتاح المتغيّر | نوع | ملاحظة |
|---|---------|--------------|----------------|------|--------|
| 1 | سعر المتغير (دولار) | `price` | `variants[i][price]` | `number` ≥ 0 | يُخزَّن USD |
| 2 | سعر المتغيّر (ليرة سورية) | `price_syp` | `variants[i][price_syp]` | `number` ≥ 0 | **ما ينحفظ** — يتحوّل لـ `price` |
| 3 | نوع الخصم | `discount_type` | `variants[i][discount_type]` | enum | افتراضي `none` |
| 4 | قيمة الخصم | `discount` | `variants[i][discount]` | `int` 0–100 | تظهر إذا النوع ≠ لا يوجد خصم |
| 5 | السعر بعد الخصم | — | — | readonly | **لا ترسلوه** |
| 6 | الكمية المتوفرة | `quantity` | `variants[i][quantity]` | `int` ≥ 0 | |
| 7 | الباركود | `barcode` | `variants[i][barcode]` | string | placeholder `6291101234567` |
| 8 | رمز التخزين التعريفي للمتغير | `sku` | `variants[i][sku]` | string | **إنجليزي فقط** على المتغيّر |

**لا ترسلوا:** `price_after_discount` · `price_currencies` · `*_formatted` · وزن (مو موجود على `product_variants`).

إذا الاثنين `price` و `price_syp` انرسلوا → الباك يعتمد **الدولار**.

إذا `variants[i][price]` فاضي → الباك يعبّيه من `product.price` (أو `0`).

---

## 4) نوع الخصم + السعر بعد الخصم

### خيارات الـ dropdown

| الواجهة | القيمة المُرسلة |
|---------|-----------------|
| لا يوجد خصم | `none` |
| نسبة مئوية | `percentage` |
| مبلغ ثابت ($) | `fixed` |

- إذا `none` → اخفوا **قيمة الخصم** أو خلّوها `0` ولا ترسلوا خصم.
- `discount` عدد صحيح `0…100` (حتى مع `fixed`).

### السعر بعد الخصم — عرض فقط · يُحسب حيّاً

```dart
double priceAfterDiscount({
  required double price,
  required String discountType,
  required int discount,
}) {
  if (price <= 0 || discountType == 'none' || discount <= 0) {
    return price;
  }
  if (discountType == 'percentage') {
    return ((price - price * (discount / 100)) * 100).round() / 100;
  }
  if (discountType == 'fixed') {
    final after = price - discount;
    return after < 0 ? 0 : ((after * 100).round() / 100);
  }
  return price;
}
```

اعرضوا الناتج **بالدولار والليرة** (نفس سعر الصرف). لا input قابل للتعديل.

---

## 5) مزامنة دولار ↔ ليرة

```http
GET /api/admin/currencies
```

```dart
final sypRate = currencies
    .firstWhere((c) => c['code'] == 'SYP', orElse: () => {'exchange_rate': 1})
    ['exchange_rate'] as num;
```

| المستخدم كتب | المطلوب |
|--------------|---------|
| دولار | عبّوا حقل الليرة = `usd * sypRate` |
| ليرة | عبّوا حقل الدولار = `syp / sypRate` |

نفس المنطق للكتلة على المنتج وعلى كل متغيّر (كل كارد إله سعره).

---

## 6) SKU إنجليزي + باركود

### رمز التخزين التعريفي للمتغير

يُولَّد في **الواجهة**. **ممنوع** حرف عربي.

| ❌ | ✅ |
|---|---|
| `PROD-أخضر-XL` | `SKU-27T4376` |
| `PROD-بيج-YELXL` | `PROD-GREEN-XL` |

```dart
String generateVariantSku(String? productSku) {
  final base = (productSku ?? 'VAR').replaceAll(RegExp(r'[^a-zA-Z0-9-]'), '');
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  final rnd = String.fromCharCodes(
    List.generate(6, (_) => chars.codeUnitAt(Random().nextInt(chars.length))),
  );
  return '${(base.isEmpty ? 'VAR' : base)}-$rnd'.toUpperCase();
}
```

- Dropdown الصفات يعرض `name.ar`.
- SKU من `name.en` أو suffix عشوائي — **لا** `name.ar`.
- فارغ مسموح — لا `required`.

### الباركود

- Placeholder: `6291101234567`
- اختياري · string · فاضي = أرسلوا `null` أو لا ترسلوا الحقل

---

## 7) Widget Flutter مشترك

```dart
class ProductPricingFields extends StatelessWidget {
  const ProductPricingFields({
    super.key,
    required this.priceUsd,
    required this.priceSyp,
    required this.discountType, // none | percentage | fixed
    required this.discount,
    required this.quantity,
    required this.barcode,
    required this.sku,
    required this.onChanged,
    this.usdLabel = 'سعر المتغير (دولار)',
    this.sypLabel = 'سعر المتغيّر (ليرة سورية)',
    this.skuLabel = 'رمز التخزين التعريفي للمتغير',
  });

  final double? priceUsd;
  final double? priceSyp;
  final String discountType;
  final int discount;
  final int? quantity;
  final String? barcode;
  final String? sku;
  final void Function(ProductPricingPatch patch) onChanged;
  final String usdLabel;
  final String sypLabel;
  final String skuLabel;

  // …
}
```

تخطيط الكارد (مرجع الواجهة):

```
┌─ المعلومات الأساسية ──────────────────────────────────────┐
│  رمز التخزين التعريفي للمتغير  [ LIG-8188-GREEN-L      ] │
│                                                           │
│  ┌──────────┬───────────┬────────────┬──────────────┐    │
│  │ سعر ($)  │ سعر (ل.س) │ نوع الخصم  │ قيمة الخصم  │    │
│  │ [2300]   │ [0     ]  │ [لا يوجد خصم ▼] │ [—   ]  │    │
│  └──────────┴───────────┴────────────┴──────────────┘    │
│  السعر بعد الخصم (readonly):  SYP …  ·  $ …               │
│                                                           │
│  ┌──────────────┬─────────────────────────────┐           │
│  │ الكمية المتوفرة │ الباركود                   │           │
│  │ [30        ]    │ [6291101234567          ] │           │
│  └──────────────┴─────────────────────────────┘           │
└───────────────────────────────────────────────────────────┘
```

نوع الخصم الافتراضي في الـ dropdown: **لا يوجد خصم**.

---

## 8) Payload

`Content-Type: multipart/form-data`

### أ) منتج بلا متغيّرات — تاب المعلومات فقط

```text
POST /api/admin/products

name[ar]=...
category_id=5
price=20
price_syp=260000
discount_type=percentage
discount=10
quantity=50
barcode=6291101234567
sku=LIG-8188-BASE
```

لا ترسلوا `variants[]`. الباك ينشئ متغيّر افتراضي **مخفي** من هالبيانات — الواجهة ما تعرض كارد.

### ب) منتج مع متغيّرات — المعلومات + الكروت

```text
POST /api/admin/products

price=20
discount_type=percentage
discount=10
quantity=50
barcode=6291100000001
sku=LIG-8188-BASE

variants[0][sku]=LIG-8188-GREEN-L
variants[0][price]=2300
variants[0][price_syp]=29900000
variants[0][discount_type]=percentage
variants[0][discount]=10
variants[0][quantity]=30
variants[0][barcode]=6291101234567
variants[0][attributes_values_ids][0]=3
variants[0][attributes_values_ids][1]=12
```

إنشاء منتج: المتغيّرات **state محلي** ثم طلب واحد. لا toast «احفظ المنتج أولاً».

تعديل متغيّر واحد موجود:

```http
PUT /api/admin/product-variants/{id}
```

هذا الـ endpoint **ما يقبل** `price_syp` — حوّلوا الليرة لدولار بالواجهة وأرسلوا `price`.  
أو احفظوا ضمن `PUT /api/admin/products/{id}` مع `variants[i][price_syp]`.

فارغ = **لا ترسلوا** الحقل (أفضل من `''`).

---

## 9) التعبئة عند التعديل (GET)

```http
GET /api/admin/products/{id}
```

### تاب المعلومات

| الحقل | من الرد |
|-------|---------|
| سعر $ | `price` أو `price_currencies.USD.amount` |
| سعر ل.س | `price_currencies.SYP.amount` |
| نوع الخصم | `discount_type` |
| قيمة الخصم | `discount` |
| كمية | `quantity` |
| باركود | `barcode` |
| SKU | `sku` |

### كارد المتغيّر `data.variants[i]`

| الحقل | من الرد |
|-------|---------|
| سعر $ | `price` أو `price_currencies.USD.amount` |
| سعر ل.س | `price_currencies.SYP.amount` |
| نوع الخصم | `discount_type` (`none` إذا `null`) |
| قيمة الخصم | `discount` |
| كمية | `quantity` |
| باركود | `barcode` |
| SKU | `sku` |

`price_after_discount` للعرض فقط — أعيدوا حسابه حيّاً إذا المستخدم عدّل السعر/الخصم.

---

## 10) الكارد وصفحة التفاصيل (للمتجر)

هذا **مو شغل فورم الداشبورد** — للتوافق مع نفس الحقول:

| سطح | المصدر |
|-----|--------|
| كارد القائمة | حقول **المنتج** (`price` · `quantity` · خصم المنتج) |
| صفحة التفاصيل بلا متغيّرات | نفس حقول المنتج (`shop_variants[0]` fallback) |
| صفحة التفاصيل مع متغيّرات | عند اختيار لون/مقاس → `shop_variants[]` المطابق: سعر · خصم · كمية · صور |

لما في متغيّرات، كمية المنتج بعد الحفظ = **مجموع** `variants[].quantity`. سعر/خصم المنتج ما بيتزامنوا من الكروت — اللي انكتب بتاب المعلومات هو اللي على الكارد.

---

## 11) Checklist

- [ ] تاب معلومات المنتج فيه البلوك كاملاً **لكل** منتج (مع أو بدون متغيّرات)
- [ ] نفس الـ widget على كارد كل متغيّر
- [ ] لا كارد متغيّر تلقائي عند فتح `/products/create`
- [ ] تسميات الكارد مطابقة: سعر المتغير (دولار) · سعر المتغيّر (ليرة سورية) · نوع الخصم · لا يوجد خصم · قيمة الخصم · السعر بعد الخصم · الكمية المتوفرة · الباركود · رمز التخزين التعريفي للمتغير
- [ ] السعر بعد الخصم **readonly** — لا يُرسل
- [ ] مزامنة `$` ↔ `ل.س` من `GET /currencies`
- [ ] `discount_type`: `none` \| `percentage` \| `fixed`
- [ ] كل الحقول `.optional()` — لا توست «الكمية موجبة»
- [ ] SKU المتغيّر إنجليزي فقط
- [ ] باركود placeholder `6291101234567`
- [ ] إنشاء: `POST /products` + `variants[]` دفعة واحدة
- [ ] بلا متغيّرات: أرسلوا `price` · `quantity` · `discount` · `barcode` · `sku` على المنتج **بدون** `variants[]`

**الباك جاهز — الداشبورد Flutter Web يتبع هذا الملف.**
