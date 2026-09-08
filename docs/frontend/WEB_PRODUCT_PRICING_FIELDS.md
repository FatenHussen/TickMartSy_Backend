# الويب — سعر · خصم · كمية · باركود · SKU (كارد + تفاصيل المنتج)

> **أرسلوا هذا الملف لفريق الويب فقط.**  
> **آخر تحديث:** 8 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز — التعديل UI فقط**  
> الداشبورد (إدخال الحقول): [`DASHBOARD_PRODUCT_PRICING_FIELDS.md`](./DASHBOARD_PRODUCT_PRICING_FIELDS.md)  
> Flutter: [`FLUTTER_PRODUCT_PRICING_FIELDS.md`](./FLUTTER_PRODUCT_PRICING_FIELDS.md)

نفس الحقول اللي الأدمن بيملأها في تاب معلومات المنتج / كارد المتغيّر — الموقع **يعرضها** (ما في فورم إدخال).

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [الحقول — تسمية الداشبورد ↔ API المتجر](#2-الحقول--تسمية-الداشبورد--api-المتجر)
3. [كارد القائمة](#3-كارد-القائمة)
4. [صفحة تفاصيل المنتج](#4-صفحة-تفاصيل-المنتج)
5. [عند اختيار متغيّر](#5-عند-اختيار-متغيّر)
6. [عرض الدولار والليرة](#6-عرض-الدولار-والليرة)
7. [نوع الخصم · لا يوجد خصم · السعر بعد الخصم](#7-نوع-الخصم--لا-يوجد-خصم--السعر-بعد-الخصم)
8. [الكمية · الباركود · SKU](#8-الكمية--الباركود--sku)
9. [React](#9-react)
10. [Checklist](#10-checklist)

---

## 1) القاعدة

كل منتج يملك هالحقول. المصدر حسب السطح:

| سطح | المصدر |
|-----|--------|
| **كارد** (قائمة / سكشن / بحث) | معلومات **المنتج** — `GET /api/user/products` |
| **تفاصيل بلا متغيّرات** | نفس معلومات المنتج عبر `shop_variants[0]` (fallback أو متغيّر مخفي) |
| **تفاصيل مع متغيّرات** | المتغيّر المختار من `shop_variants[]` — عند التغيير تتبدّل كل الحقول |

`shop_variants` **دائماً ≥ 1**. لا تفترضوا `shop_variants[0]` بدون `?.`.

---

## 2) الحقول — تسمية الداشبورد ↔ API المتجر

| # | تسمية الداشبورد | كارد القائمة | صفحة التفاصيل (`shop_variants[i]`) |
|---|-----------------|--------------|-------------------------------------|
| 1 | سعر المتغير (دولار) | `price_currencies.USD` | `price_currencies.USD` |
| 2 | سعر المتغيّر (ليرة سورية) | `price_currencies.SYP` | `price_currencies.SYP` |
| 3 | نوع الخصم | لا يُرسل على الكرت | `discount_type` |
| 4 | لا يوجد خصم | `price === price_after_discount` | `discount_type === 'none'` أو `discount_value === 0` |
| 5 | قيمة الخصم | لا تعتمدوا على `discount` في الكرت (سترينغ فاضي) | `discount_value` (10 = 10% أو 10$) |
| 6 | السعر بعد الخصم | `price_after_discount_currencies` | `price_after_discount_currencies` |
| 7 | الكمية المتوفرة | `quantity` (قد تكون `null`) | `quantity` (قد تكون `null`) |
| 8 | الباركود | **غير موجود على الكرت** | `barcode` |
| 9 | رمز التخزين التعريفي للمتغير | **غير موجود على الكرت** | `sku` |

**لا تخلطوا:**

| المفتاح | المعنى |
|---------|--------|
| `discount_value` | القيمة اللي أدمنها الأدمن (10) |
| `discount_type` | `none` \| `percentage` \| `fixed` |
| `discount` + `discount_currencies` | **مبلغ التوفير** بالدولار/الليرة — للعرض «وفّرت X» |

لا تحسبوا خصم على الفرونت. استخدموا `price_after_discount*`.

---

## 3) كارد القائمة

```http
GET /api/user/products
GET /api/user/sections   ← نفس عقد المنتج داخل الأقسام
```

اعرضوا من حقول **المنتج** (مو أول متغيّر):

```
┌─ كارد المنتج ─────────────────────────┐
│  [صورة]                               │
│  الاسم                                │
│  السعر بعد الخصم   $ …  ·  ل.س …     │  ← أساسي
│  السعر الأصلي مشطوب (إذا في خصم)      │
│  الكمية / متوفر (اختياري)             │
└───────────────────────────────────────┘
```

| عرض | من الرد |
|------|---------|
| سعر $ | `price_currencies.USD.formatted` |
| سعر ل.س | `price_currencies.SYP.formatted` |
| بعد الخصم $ | `price_after_discount_currencies.USD.formatted` |
| بعد الخصم ل.س | `price_after_discount_currencies.SYP.formatted` |
| وفّرت | `amount_saved_currencies` |
| كمية | `quantity` — تعاملوا مع `null` |

لا SKU ولا باركود على الكرت. الضغط → صفحة `{id}`.

---

## 4) صفحة تفاصيل المنتج

```http
GET /api/user/products/{id}?lat=&lng=
```

### منتج بلا صفات (`attributes_map` فاضي)

ما في picker. اعرضوا `shop_variants[0]`:

- سعر $ / ل.س
- نوع الخصم (أو اخفوا البلوك إذا `none`)
- قيمة الخصم + السعر بعد الخصم
- الكمية المتوفرة
- الباركود (إذا مو `null`)
- SKU (إذا مو `null`)

### منتج بمتغيّرات

1. ابنوا الـ picker من `attributes_map` (لون · مقاس · …).
2. طابقوا الاختيار مع عنصر في `shop_variants` عبر `attributes`.
3. اعرضوا حقول **ذلك** العنصر — مو حقول المنتج.

القيم الابتدائية = أول متغيّر مطابق أو `shop_variants[0]`.

---

## 5) عند اختيار متغيّر

كل ضغطة لون/مقاس → حدّثوا **كل** الحقول دفعة واحدة من الـ `shop_variant` الجديد:

```
السعر $ · السعر ل.س · الخصم · بعد الخصم · الكمية · الباركود · SKU · الصور
```

```js
function onSelectVariant(variant) {
  setSelected(variant);
  // السعر / الخصم / الكمية / باركود / SKU / صور — كلها من variant
}
```

لا تخلّوا السعر من المنتج والكمية من المتغيّر.

### السلة

```http
POST /api/user/cart/items
{ "shop_product_variant_id": selected.id, "quantity": 1 }
```

`selected.id` أو `shop_id` = `null` → اعرضوا الصفحة وعطّلوا السلة. لا ترسلوا سعراً.

---

## 6) عرض الدولار والليرة

استخدموا `*_currencies` جاهزة. **لا** تحويل محلي ولا `price_usd` / `price_syp` flat (قد لا تُرسل).

```js
function money(currencies, code) {
  return currencies?.[code]?.formatted ?? null;
}

const usd = money(v.price_currencies, 'USD');       // سعر المتغير (دولار)
const syp = money(v.price_currencies, 'SYP');       // سعر المتغيّر (ليرة سورية)
const usdAfter = money(v.price_after_discount_currencies, 'USD');
const sypAfter = money(v.price_after_discount_currencies, 'SYP');
```

اعرضوا العملتين معاً إذا التصميم يدعم، أو عملة المستخدم + الثانية كسطر ثانوي.

---

## 7) نوع الخصم · لا يوجد خصم · السعر بعد الخصم

| `discount_type` | الواجهة |
|-----------------|--------|
| `none` أو ناقص (fallback) | **لا يوجد خصم** — أخفوا قيمة الخصم · لا تشطبوا السعر |
| `percentage` | قيمة الخصم + `%` — السعر الأصلي مشطوب · بعد الخصم أساسي |
| `fixed` | قيمة الخصم كمبلغ `$` |

```js
const hasDiscount =
  selected?.discount_type &&
  selected.discount_type !== 'none' &&
  (selected.discount_value ?? 0) > 0;

const displayPrice = hasDiscount
  ? selected.price_after_discount_currencies
  : selected.price_currencies;
```

Fallback المنتج البسيط **قد لا يحتوي** `discount_value` / `discount_type`. وقتها:

```js
const type = selected.discount_type ?? product.discount_type ?? 'none';
const hasDiscountFallback =
  (selected.price_after_discount ?? selected.price) < (selected.price ?? 0);
```

---

## 8) الكمية · الباركود · SKU

### الكمية المتوفرة

المخزون للشراء = `shop_variants[].quantity` — **ليس** `product.quantity`.

| القيمة | العرض |
|--------|--------|
| رقم > 0 | «متوفر: N» أو «الكمية المتوفرة: N» |
| `0` أو `null` | غير متوفر — عطّلوا السلة |

```js
const qty = selected?.quantity ?? 0;
const canAdd =
  Boolean(selected?.id) &&
  Boolean(selected?.shop_id) &&
  qty > 0;
```

### الباركود

- من `selected.barcode`
- مثال الشكل: `6291101234567`
- `null` / فاضي → أخفوا الصف

### رمز التخزين التعريفي للمتغير

- من `selected.sku`
- إنجليزي فقط (مثل `LIG-8188-GREEN-L`)
- `null` → أخفوا الصف
- **لا اسم متغيّر** — الهوية = `attributes` + `sku`

---

## 9) React

```js
function ProductPricingDisplay({ variant, product }) {
  const v = variant ?? product.shop_variants?.[0];
  if (!v) return null;

  const type = v.discount_type ?? product.discount_type ?? 'none';
  const hasDiscount = type !== 'none' && (v.discount_value ?? 0) > 0;
  const usd = v.price_currencies?.USD;
  const syp = v.price_currencies?.SYP;
  const usdAfter = v.price_after_discount_currencies?.USD;
  const sypAfter = v.price_after_discount_currencies?.SYP;

  return (
    <div>
      <p>{hasDiscount ? usdAfter?.formatted : usd?.formatted}</p>
      <p>{hasDiscount ? sypAfter?.formatted : syp?.formatted}</p>
      {hasDiscount && (
        <>
          <s>{usd?.formatted} · {syp?.formatted}</s>
          <span>
            {type === 'percentage' ? `${v.discount_value}%` : usd?.symbol}
          </span>
        </>
      )}
      <p>الكمية المتوفرة: {v.quantity ?? 0}</p>
      {v.barcode && <p>الباركود: {v.barcode}</p>}
      {v.sku && <p>SKU: {v.sku}</p>}
    </div>
  );
}
```

عند تغيّر الـ picker مرّروا الـ `variant` الجديد لنفس المكوّن.

---

## 10) Checklist

- [ ] الكارد من حقول **المنتج** (`price_currencies` · `price_after_discount_currencies` · `quantity`)
- [ ] لا `discount` على الكرت (سترينغ فاضي) — استخدموا مقارنة السعر / بعد الخصم
- [ ] صفحة التفاصيل: `shop_variants[i]` بعد الاختيار
- [ ] بلا picker (`attributes_map` فاضي) → `shop_variants[0]`
- [ ] تغيير المتغيّر يبدّل: سعر $ · ل.س · خصم · بعد الخصم · كمية · باركود · SKU · صور
- [ ] `*_currencies` فقط — لا تحويل محلي
- [ ] `discount_type === 'none'` → لا يوجد خصم
- [ ] `discount_value` = قيمة الإدخال · `discount` = مبلغ التوفير
- [ ] باركود / SKU: أخفوا إذا `null`
- [ ] سلة: `shop_product_variant_id` = `selected.id` — لا سعر من الفرونت
- [ ] `quantity` nullable على الكرت والتفاصيل

**الباك جاهز — الموقع يتبع هذا الملف.**
