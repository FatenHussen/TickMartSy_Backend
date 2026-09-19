# الويب — خصم ثابت: كسور وأكبر من 100

> **أرسلوا هذا الملف لفريق الويب فقط.**  
> **آخر تحديث:** 20 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> داشبورد: [`DASHBOARD_FIXED_DISCOUNT.md`](./DASHBOARD_FIXED_DISCOUNT.md)  
> Flutter: [`FLUTTER_FIXED_DISCOUNT.md`](./FLUTTER_FIXED_DISCOUNT.md)  
> الحقول الكاملة: [`WEB_PRODUCT_PRICING_FIELDS.md`](./WEB_PRODUCT_PRICING_FIELDS.md)

الأدمن صار يقدر يحفظ خصم **ثابت** بكسور وأكبر من 100 (مثال: `150.75`).  
الموقع **يعرض** هالقيمة — ما في فورم إدخال.

ما في endpoint جديد: `GET /api/user/products/{id}`.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [الحقول](#2-الحقول)
3. [العرض](#3-العرض)
4. [غلط vs صح](#4-غلط-vs-صح)
5. [Checklist](#5-checklist)

---

## 1) القاعدة

| `discount_type` | `discount_value` |
|-----------------|------------------|
| `none` | لا شارة خصم |
| `percentage` | نسبة — قد تكون كسر (`10.5`) وحدّها 100 |
| `fixed` | مبلغ دولار — قد يكون كسر **وأكبر من 100** (`150.75`) |

- اقرأوا `discount_value` كـ `number` — **لا** `parseInt` ولا افترضوا ≤ 100.
- **لا تحسبوا** السعر بعد الخصم. استخدموا `price_after_discount_currencies`.
- `discount` + `discount_currencies` = مبلغ التوفير (محسوب) — مو قيمة الإدخال.

---

## 2) الحقول

من `shop_variants[i]` (أو حقول المنتج على الكرت للمقارنة):

```json
{
  "discount_type": "fixed",
  "discount_value": 150.75,
  "discount": 150.75,
  "price": 200,
  "price_after_discount": 49.25,
  "price_after_discount_currencies": {
    "USD": { "amount": 49.25, "formatted": "$ 49.25" },
    "SYP": { "amount": 640250, "formatted": "640,250 ل.س" }
  }
}
```

`discount_value` رقم عشري. لا تقطعوه لـ `150`.

---

## 3) العرض

```js
const type = selected?.discount_type ?? product.discount_type ?? 'none';
const value = Number(selected?.discount_value ?? 0);

const hasDiscount = type !== 'none' && value > 0;

const badge =
  type === 'percentage' ? `-${value}%` :
  type === 'fixed' ? `خصم ${value}` :
  null;
```

اعرضوا `price_after_discount_currencies` كسعر أساسي إذا `hasDiscount`.

لا تخفوا الشارة إذا `value > 100`. لا تقرّبوا لعدد صحيح إلا للعرض إذا التصميم يطلب منزلتين — القيمة نفسها تبقى `150.75`.

---

## 4) غلط vs صح

**غلط**

```js
const value = parseInt(v.discount_value, 10); // 150.75 → 150
if (value > 100) hideBadge();
```

**صح**

```js
const value = Number(v.discount_value ?? 0);
// fixed: 150.75 يظهر · percentage: حدّ 100 من الباك
```

---

## 5) Checklist

- [ ] `discount_value` نوع `number` (مو `int`)
- [ ] `fixed` يظهر كسور وأكبر من 100
- [ ] لا شرط `<= 100` على الشارة إذا النوع ثابت
- [ ] السعر من `price_after_discount_currencies` — لا حساب محلي
- [ ] نفس المنطق على الكرت (مقارنة السعر / بعد الخصم) وصفحة التفاصيل

**الباك جاهز — الموقع يتبع هذا الملف.**
