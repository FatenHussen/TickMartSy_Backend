# الداشبورد — خصم ثابت: كسور وأكبر من 100

> **أرسلوا هذا الملف لفريق الداشبورد فقط.**  
> **آخر تحديث:** 20 أيلول 2026  
> Base: `/api/admin` + Admin token  
> **الباك جاهز بعد `git pull` + migrate**  
> ويب: [`WEB_FIXED_DISCOUNT.md`](./WEB_FIXED_DISCOUNT.md)  
> Flutter: [`FLUTTER_FIXED_DISCOUNT.md`](./FLUTTER_FIXED_DISCOUNT.md)  
> الحقول الكاملة: [`DASHBOARD_PRODUCT_PRICING_FIELDS.md`](./DASHBOARD_PRODUCT_PRICING_FIELDS.md)

لما نوع الخصم **قيمة ثابتة / مبلغ ثابت (`fixed`)** كان الفورم يرفض كسور ويرفض أي رقم أكبر من 100 (كأنّه نسبة).  
**هذا تغيّر.** الباك يقبل الكسور ويقبل مبالغ أكبر من 100.

النسبة المئوية تبقى حتى 100 فقط.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [الفورم](#2-الفورم)
3. [Payload](#3-payload)
4. [غلط vs صح](#4-غلط-vs-صح)
5. [Checklist](#5-checklist)

---

## 1) القاعدة

| `discount_type` | قيمة `discount` |
|-----------------|-----------------|
| `none` | اخفوا الحقل أو `0` — لا ترسلوا خصم |
| `percentage` | رقم `0…100` — كسور مسموحة (`10.5`) |
| `fixed` | مبلغ **دولار** ≥ 0 — كسور مسموحة **وأكبر من 100** (`150.75`) |

نفس القواعد على **المنتج** و**كل متغيّر** (`variants[i][discount]`).

- **لا** `int` / `integer` على قيمة الخصم.
- **لا** `max: 100` إذا النوع `fixed`.
- حد 100 **فقط** مع `percentage`.

---

## 2) الفورم

حقل «قيمة الخصم» = `number` / `decimal` (مو `int`).

```dart
// صح
if (discountType == 'percentage') {
  // 0 … 100 — كسور OK
}
if (discountType == 'fixed') {
  // ≥ 0 — 12.5 و 250.75 OK — لا سقف 100
}
```

السعر بعد الخصم (عرض فقط):

```dart
if (discountType == 'fixed') {
  return (price - discount).clamp(0, price);
}
```

---

## 3) Payload

```text
discount_type=fixed
discount=150.75

variants[0][discount_type]=fixed
variants[0][discount]=120.5
```

`Content-Type: multipart/form-data` كالعادة.

---

## 4) غلط vs صح

**غلط**

```text
discount_type=fixed
discount=150          ← مرفوض إذا عندكم max 100 في الفرونت
discount=12.5         ← مرفوض إذا الحقل int
```

**صح**

```text
discount_type=fixed
discount=150.75

discount_type=percentage
discount=10.5
```

---

## 5) Checklist

- [ ] نوع `fixed` يقبل فواصل (`12.5` · `150.75`)
- [ ] نوع `fixed` يقبل أكبر من 100
- [ ] نوع `percentage` يبقى حتى 100 (كسور OK)
- [ ] نفس الفاليديشن على كارد المتغيّر
- [ ] لا توست «يجب أن يكون بين 0 و 100» على الخصم الثابت
- [ ] النوع في الـ GET: `number` مو `int`

**الباك جاهز — الداشبورد يتبع هذا الملف.**
