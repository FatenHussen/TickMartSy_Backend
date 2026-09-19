# Flutter — خصم ثابت: كسور وأكبر من 100

> **أرسلوا هذا الملف لفريق Flutter فقط.**  
> **آخر تحديث:** 20 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> داشبورد: [`DASHBOARD_FIXED_DISCOUNT.md`](./DASHBOARD_FIXED_DISCOUNT.md)  
> ويب: [`WEB_FIXED_DISCOUNT.md`](./WEB_FIXED_DISCOUNT.md)  
> الحقول الكاملة: [`FLUTTER_PRODUCT_PRICING_FIELDS.md`](./FLUTTER_PRODUCT_PRICING_FIELDS.md)

الأدمن صار يقدر يحفظ خصم **ثابت** بكسور وأكبر من 100 (مثال: `150.75`).  
التطبيق **يعرض** هالقيمة.

ما في endpoint جديد: `GET /api/user/products/{id}`.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [الموديل](#2-الموديل)
3. [العرض](#3-العرض)
4. [غلط vs صح](#4-غلط-vs-صح)
5. [Checklist](#5-checklist)

---

## 1) القاعدة

| `discountType` | `discountValue` |
|----------------|-----------------|
| `none` | لا شارة خصم |
| `percentage` | نسبة — قد تكون كسر (`10.5`) وحدّها 100 |
| `fixed` | مبلغ دولار — قد يكون كسر **وأكبر من 100** (`150.75`) |

- `discountValue` = `num?` — **لا** `int?` ولا `asInt`.
- **لا تحسبوا** السعر بعد الخصم. استخدموا `priceAfterDiscountCurrencies`.
- `discount` / `discountCurrencies` = مبلغ التوفير — مو قيمة الإدخال.

---

## 2) الموديل

```dart
class ShopVariant {
  final num? discountValue; // مو int
  final String? discountType; // none | percentage | fixed
  // …
}

num? asNum(dynamic v) {
  if (v == null) return null;
  if (v is num) return v;
  return num.tryParse('$v');
}

// fromJson
discountValue: asNum(json['discount_value']),
```

`asInt(json['discount_value'])` يحوّل `150.75` إلى `150` — **ممنوع**.

---

## 3) العرض

```dart
bool hasDiscount(ShopVariant v) =>
    v.discountType != null &&
    v.discountType != 'none' &&
    (v.discountValue ?? 0) > 0;

String? discountBadge(ShopVariant v) {
  final value = v.discountValue;
  if (!hasDiscount(v) || value == null) return null;
  if (v.discountType == 'percentage') return '-$value%';
  if (v.discountType == 'fixed') return 'خصم $value';
  return null;
}
```

لا تخفوا الشارة إذا `discountValue > 100`.  
السعر الأساسي = `priceAfterDiscountCurrencies` إذا في خصم.

---

## 4) غلط vs صح

**غلط**

```dart
final int? discountValue;
discountValue: asInt(json['discount_value']),
if ((v.discountValue ?? 0) > 100) return null;
```

**صح**

```dart
final num? discountValue;
discountValue: asNum(json['discount_value']),
// 10.5 و 150.75 يظهروا كما هما
```

---

## 5) Checklist

- [ ] `discountValue` نوع `num?` (مو `int?`)
- [ ] `fromJson` بـ `asNum` مو `asInt`
- [ ] `fixed` يظهر كسور وأكبر من 100
- [ ] لا شرط `<= 100` على الشارة إذا النوع ثابت
- [ ] السعر من `priceAfterDiscountCurrencies` — لا حساب محلي
- [ ] نفس المنطق على الكرت وشاشة التفاصيل

**الباك جاهز — التطبيق يتبع هذا الملف.**
