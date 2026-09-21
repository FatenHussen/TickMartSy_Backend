# الويب — إضافات المنتج: اسم + سعر

> **أرسلوا هذا الملف لفريق الويب فقط.**  
> **آخر تحديث:** 21 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull` + migrate**  
> داشبورد: [`DASHBOARD_PRODUCT_EXTRA_DETAILS.md`](./DASHBOARD_PRODUCT_EXTRA_DETAILS.md)

الإضافات = خيارات مدفوعة على المنتج (تغليف، ضمان، تركيب…) — **اسم + سعر**.  
ليست مواصفات نصية مثل «الخامة: قطن».

---

## الفهرس

1. [من أين تُجلب](#1-من-أين-تجلب)
2. [شكل الحقول](#2-شكل-الحقول)
3. [العرض على صفحة المنتج](#3-العرض-على-صفحة-المنتج)
4. [الطلب / السلة](#4-الطلب--السلة)
5. [غلط vs صح](#5-غلط-vs-صح)
6. [Checklist](#6-checklist)

---

## 1) من أين تُجلب

`GET /api/user/products/{id}` → المصفوفة `extra_details`.

إذا `extra_details` فارغة `[]` → اخفوا قسم الإضافات بالكامل.

---

## 2) شكل الحقول

```json
{
  "extra_details": [
    {
      "id": 12,
      "key": { "ar": "تغليف هدايا", "en": "Gift Wrapping" },
      "value": { "ar": "تغليف فاخر", "en": "Premium wrap" },
      "quantity": 1,
      "price": 50,
      "price_currencies": {
        "USD": { "amount": 50, "formatted": "$ 50.00" },
        "SYP": { "amount": 650000, "formatted": "650,000 ل.س" }
      }
    }
  ]
}
```

| الحقل | الاستخدام |
|-------|-----------|
| `id` | يُرسل عند الطلب |
| `key` | **اسم الإضافة** — اعرضوا حسب `Accept-Language` |
| `value` | وصف اختياري — أخفوه إذا فاضي |
| `price` | السعر الأساسي (USD) |
| `price_currencies` | للعرض — نفس أسلوب باقي الأسعار |
| `quantity` | حد الكمية المتاح من الإضافة على هذا المنتج (إن لزم) |

**لا** تعاملوا `value` كسعر. السعر دائماً من `price` / `price_currencies`.

---

## 3) العرض على صفحة المنتج

```js
const lang = locale; // 'ar' | 'en'
const extras = product.extra_details ?? [];

extras.map((e) => ({
  id: e.id,
  name: e.key?.[lang] ?? e.key?.ar ?? e.key?.en ?? '',
  description: e.value?.[lang] || null,
  priceLabel: e.price_currencies?.USD?.formatted // أو حسب عملة المستخدم
}));
```

- Checkbox / chip لكل إضافة: **الاسم** + **السعر**
- مجموع السطر = سعر المنتج (بعد الخصم) + مجموع أسعار الإضافات المختارة × كمياتها
- **لا تحسبوا** تحويل العملة يدوياً — استخدموا `price_currencies`

---

## 4) الطلب / السلة

عند إنشاء الطلب `POST /api/user/orders` (ونفس الشكل في preview إن وُجد):

```json
{
  "items": [
    {
      "shop_product_variant_id": 5,
      "quantity": 1,
      "extras": [
        { "id": 12, "quantity": 1 },
        { "id": 15, "quantity": 1 }
      ]
    }
  ]
}
```

- `extras` اختياري
- كل عنصر: `id` (من `extra_details[].id`) + `quantity` ≥ 1
- الباك يحسب سعر الإضافات من سعر الربط على المنتج (مو من الواجهة)

---

## 5) غلط vs صح

**غلط**

```js
// عرض value كعنوان أو كسعر
label = extra.value.ar; // "قطن" ❌
price = Number(extra.value); // ❌
```

**صح**

```js
label = extra.key[lang];
priceFormatted = extra.price_currencies.USD.formatted;
```

---

## 6) Checklist

- [ ] قسم إضافات على صفحة المنتج من `extra_details`
- [ ] الاسم من `key` · السعر من `price_currencies`
- [ ] `value` وصف اختياري فقط
- [ ] إخفاء القسم إذا المصفوفة فارغة
- [ ] عند الطلب: `extras: [{ id, quantity }]`
- [ ] لا `parseInt` على السعر — كسور مسموحة

**الباك جاهز — الموقع يتبع هذا الملف.**
