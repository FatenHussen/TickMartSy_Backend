# الداشبورد — إضافة متغيّرات المنتج (أول متغيّر Single + الباقي Multi)

> **أرسلوا هذا الملف لفريق الداشبورد فقط.**  
> Base: `/api/admin` + Admin token.  
> **آخر تحديث:** 2026-08-30

---

## الفهرس

1. [الفكرة العامة](#1-الفكرة-العامة)
2. [قاعدة Single vs Multi](#2-قاعدة-single-vs-multi)
3. [جلب صفات الفئة](#3-جلب-صفات-الفئة)
4. [تدفق «إضافة متغيّر»](#4-تدفق-إضافة-متغيّر)
5. [أمثلة حسب نوع المنتج](#5-أمثلة-حسب-نوع-المنتج)
6. [حقول كل متغيّر (بدون اسم)](#6-حقول-كل-متغيّر-بدون-اسم)
7. [السعر USD ↔ SYP](#7-السعر-usd--syp)
8. [الخصم وسعر بعد الخصم](#8-الخصم-وسعر-بعد-الخصم)
9. [الكمية والمخزون](#9-الكمية-والمخزون)
10. [موعد التسليم](#10-موعد-التسليم)
11. [توليد SKU تلقائي (واجهة)](#11-توليد-sku-تلقائي-واجهة)
12. [حفظ المنتج — Payload](#12-حفظ-المنتج--payload)
13. [Checklist](#13-checklist)

---

## 1) الفكرة العامة

| المفهوم | الشرح |
|---------|--------|
| **صفة الفئة** | خاصية مرتبطة بالفئة الرئيسية (لون، مقاس، تصميم...) |
| **قيمة الصفة** | قيمة محددة (أحمر، S، M، L...) |
| **متغيّر (Variant)** | **صف واحد** = تركيبة واحدة من القيم + سعر + كمية + خصم |

**قاعدة ذهبية:** الباك يستقبل **صف متغيّر لكل تركيبة**. الواجهة توسّع الاختيارات المتعددة إلى عدة صفوف قبل الحفظ.

**قاعدة الاختيار (Single / Multi):**
- **أول متغيّر** (جدول فارغ) → **Single** لكل الصفات → **صف واحد فقط**
- **من الثاني فما فوق** → **Multi** لكل الصفات → يمكن توليد **عدة صفوف** دفعة واحدة

```
الدفعة 1 (جدول فارغ — Single)     →    1 variant
  لون: أحمر + مقاس: S              →    SWEAT-RED-S

الدفعة 2 (Multi)                   →    3 variants
  لون: أحمر + مقاسات: [M, L, XL]   →    SWEAT-RED-M, SWEAT-RED-L, SWEAT-RED-XL

الدفعة 3 (Multi)                   →    3 variants
  لون: فضي + مقاسات: [37, 38, 40]  →    3 صفوف
```

**لا يوجد حقل اسم للمتغيّر:** احذفوا من الواجهة:
- ~~اسم المتغيّر (الإنكليزية)~~ — `variants[].name.en`
- ~~اسم المتغيّر (عربي)~~ — `variants[].name.ar`

**هوية المتغيّر** تُعرَف من:
1. **قيم الصفات** (لون + مقاس...) في الجدول
2. **SKU** (مُولَّد أو يدوي)

---

## 2) قاعدة Single vs Multi

| الحالة | وضع الاختيار | النتيجة |
|--------|--------------|---------|
| **جدول المتغيّرات فارغ** (أول إضافة) | **Single** — كل صفة = قيمة **واحدة** | **صف واحد** دائماً |
| **جدول فيه متغيّرات** (إضافة ثانية+) | **Multi** — كل صفة = قيم **متعددة** | **1 أو أكثر** صف حسب التركيبات |

### منطق الواجهة

```js
const isFirstVariant = variantsTable.length === 0;
const selectionMode = isFirstVariant ? 'single' : 'multi';

// single → كل valueIds فيها عنصر واحد كحد أقصى
// multi  → valueIds يمكن أن تحتوي عدة عناصر → expandVariantCombinations
```

| `selectionMode` | UI لكل صفة | مثال |
|-----------------|------------|------|
| `single` | dropdown / radio — **اختيار واحد** | لون: أحمر · مقاس: S |
| `multi` | multi-select / checkboxes — **عدة قيم** | لون: أحمر · مقاس: [M, L, XL] |

> **مهم:** الفرق **ليس** حسب نوع الصفة (لون vs مقاس). الفرق حسب **هل هذا أول متغيّر أم لا**.

---

## 3) جلب صفات الفئة

عند اختيار **المستوى 1** من الفئة في فورم المنتج:

```http
GET /api/admin/category-attributes?category_id={categoryId}
Authorization: Bearer {admin_token}
```

- أي `category_id` بالشجرة يرجع صفات **الفئة الرئيسية (الجذر)**.
- **لا تعيد الطلب** عند النزول للمستويات 2–6.
- أعد الطلب **فقط** إذا تغيّرت الفئة الرئيسية.

### شكل الاستجابة (مختصر)

```json
{
  "status": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": { "ar": "لون", "en": "Color" },
        "type": "color",
        "values": [
          { "id": 6, "name": { "ar": "أحمر", "en": "Red" }, "color": { "hex": "#FF0000" } },
          { "id": 7, "name": { "ar": "أسود", "en": "Black" } }
        ]
      },
      {
        "id": 2,
        "name": { "ar": "قياس", "en": "Size" },
        "type": "square",
        "values": [
          { "id": 10, "name": { "ar": "S", "en": "S" } },
          { "id": 11, "name": { "ar": "M", "en": "M" } },
          { "id": 12, "name": { "ar": "L", "en": "L" } }
        ]
      }
    ]
  }
}
```

| `type` | عرض في الواجهة |
|--------|----------------|
| `color` | دائرة ملونة + اسم |
| `square` | مربع (مقاس) |
| `circle` | دائرة (تصميم/نمط) |

---

## 4) تدفق «إضافة متغيّر»

### الخطوات (UX)

```
┌─────────────────────────────────────────────────────────┐
│  [+ إضافة متغيّر جديد]                                   │
└─────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│  لكل صفة من صفات الفئة:                                 │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐              │
│  │ لون ▼    │  │ قياس ▼   │  │ تصميم ▼  │              │
│  │ (single) │  │(multi)   │  │ (single) │              │
│  └──────────┘  └──────────┘  └──────────┘              │
│                                                         │
│  [توليد المتغيّرات]                                     │
└─────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│  جدول المتغيّرات المُولَّدة:                            │
│  ┌──────┬──────┬──────┬───────┬───────┬──────┬──────┐  │
│  │ SKU  │ لون  │ مقاس │ $     │ ل.س   │ خصم  │ كمية │  │
│  ├──────┼──────┼──────┼───────┼───────┼──────┼──────┤  │
│  │ ...  │ أحمر │ S    │ 25.00 │325000 │ 10%  │ 5    │  │
│  │ ...  │ أحمر │ M    │ 25.00 │325000 │ 10%  │ 8    │  │
│  └──────┴──────┴──────┴───────┴───────┴──────┴──────┘  │
│  + سعر بعد الخصم (عرض فقط) لكل صف                       │
└─────────────────────────────────────────────────────────┘
```

### قواعد الاختيار

1. **أول «إضافة متغيّر»** (جدول فارغ) → **Single** لكل الصفات → **صف واحد** فقط.
2. **كل إضافة بعدها** → **Multi** لكل الصفات → توسّع لـ **1..N صف**.
3. الأدمن يختار **فقط ما يحتاجه** (يمكن ترك صفة فارغة).
4. بعد «توليد» → تُضاف الصفوف للجدول. الإضافة التالية دائماً **Multi**.

### دالة توسيع الاختيارات (JavaScript)

```js
/**
 * @param {Array<{categoryAttributeId: number, valueIds: number[]}>} selections
 * @param {'single'|'multi'} mode — single إذا جدول فارغ، multi بعدها
 * @returns {number[][]} مصفوفة attributes_values_ids لكل متغيّر
 */
function expandVariantCombinations(selections, mode = 'multi') {
  const withValues = selections.filter(s => s.valueIds?.length > 0);
  if (withValues.length === 0) return [];

  if (mode === 'single') {
    return [withValues.map(s => s.valueIds[0])];
  }

  return withValues.reduce(
    (acc, { valueIds }) => {
      if (acc.length === 0) return valueIds.map(id => [id]);
      const next = [];
      for (const combo of acc) {
        for (const id of valueIds) {
          next.push([...combo, id]);
        }
      }
      return next;
    },
    []
  );
}
```

---

## 5) أمثلة حسب نوع المنتج

### أ) كنزة — أول single ثم multi

**الدفعة 1 (جدول فارغ — Single):** لون أحمر · مقاس S → **1 صف**  
**الدفعة 2 (Multi):** لون أحمر · مقاسات M, L, XL → **3 صفوف**  
**المجموع:** 4 variants

### ب) حذاء — single ثم multi

**الدفعة 1 (Single):** فضي + 37 → **1 صف**  
**الدفعة 2 (Multi):** فضي + [38, 40] → **2 صف**  
**الدفعة 3 (Multi):** أسود + [42] → **1 صف**  
**المجموع:** 4 variants

---

## 6) حقول كل متغيّر (بدون اسم)

> **لكل متغيّر — 6 حقول أساسية:**

| # | الحقل في الواجهة | مفتاح API | قابل للتعديل |
|---|------------------|-----------|--------------|
| 1 | سعر المتغيّر (دولار) `$` | `variants[].price` | نعم |
| 2 | سعر المتغيّر (ليرة سورية) `ل.س` | `variants[].price_syp` | نعم — **مزامنة تلقائية** مع الدولار |
| 3 | قيمة الخصم + نوع الخصم | `variants[].discount` + `variants[].discount_type` | نعم |
| 4 | سعر بعد الخصم | — | **عرض فقط** — يُحسب حيّاً |
| 5 | الكمية المتوفرة | `variants[].quantity` | نعم |
| 6 | الباركود | `variants[].barcode` | نعم |

**محذوف من الواجهة:** ~~اسم المتغيّر (ar/en)~~ — الهوية من **الصفات + SKU**.

### جدول تفصيلي

### ما يُعرض في كل صف

| الحقل في الواجهة | مفتاح API | مطلوب | قابل للتعديل | ملاحظة |
|------------------|-----------|--------|--------------|--------|
| ~~اسم المتغيّر (en)~~ | `variants[].name.en` | — | **محذوف** | **لا تعرضه ولا ترسله** |
| ~~اسم المتغيّر (ar)~~ | `variants[].name.ar` | — | **محذوف** | **لا تعرضه ولا ترسله** |
| سعر المتغيّر (دولار) `$` | `variants[].price` | نعم* | نعم | يُخزَّن بالدولار (USD) |
| سعر المتغيّر (ليرة سورية) `ل.س` | `variants[].price_syp` | لا | نعم | **مزامنة تلقائية** مع الدولار |
| نوع الخصم | `variants[].discount_type` | لا | نعم | `none` \| `percentage` \| `fixed` |
| قيمة الخصم | `variants[].discount` | لا | نعم | رقم (10 = 10% أو 10$) |
| سعر بعد الخصم | — | — | **عرض فقط** | يُحسب حيّاً في الواجهة |
| الكمية المتوفرة | `variants[].quantity` | نعم | نعم | **حقل واحد** = المخزون |
| الباركود | `variants[].barcode` | لا | نعم | نص — اختياري |
| قيم الصفات | `variants[].attributes_values_ids` | نعم** | — | من اختيار الأدمن |
| SKU | `variants[].sku` | لا | نعم | توليد تلقائي أو يدوي |
| نشط | `variants[].is_active` | لا | نعم | افتراضي `true` |
| صور | `variants[].images` | لا | نعم | صور خاصة بالمتغيّر |

\* مطلوب `price` **أو** `price_syp` (واحد على الأقل).  
\** إذا المنتج بدون صفات → متغيّر افتراضي واحد بدون `attributes_values_ids`.

### تخطيط الصف المقترح

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  [SKU]  [لون: أحمر]  [مقاس: M]                                              │
│                                                                             │
│  سعر ($) [25.00]    سعر (ل.س) [325000]    ← مزامنة تلقائية                 │
│  نوع خصم [نسبة ▼]   خصم [10]              سعر بعد الخصم [22.50] (readonly) │
│  الكمية المتوفرة [8]    الباركود [0194253404316]                            │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 7) السعر USD ↔ SYP

### مصدر سعر الصرف

```http
GET /api/admin/currencies
```

```js
const sypRate = currencies.find(c => c.code === 'SYP')?.exchange_rate ?? 1;
```

### مزامنة في الواجهة

```js
function usdToSyp(usd, rate) {
  return Math.round((Number(usd) || 0) * rate);
}

function sypToUsd(syp, rate) {
  return Math.round((Number(syp) / rate) * 100) / 100;
}

// عند الكتابة بالدولار → عبّي الليرة
onPriceUsdChange(usd) {
  setPriceSyp(usdToSyp(usd, sypRate));
}

// عند الكتابة بالليرة → عبّي الدولار
onPriceSypChange(syp) {
  setPriceUsd(sypToUsd(syp, sypRate));
}
```

### قواعد الإرسال للباك

| الحالة | ما يُرسل |
|--------|----------|
| دولار فقط | `variants[i][price]=25` |
| ليرة فقط | `variants[i][price_syp]=325000` → الباك يحوّل لـ USD |
| الاثنان معاً | الباك يعتمد **الدولار** ويتجاهل الليرة |

```text
variants[0][price]=25
# أو
variants[0][price_syp]=325000
```

**لا ترسل** `price_after_discount` ولا `price_currencies` — للعرض فقط من GET.

### GET — استجابة السعر

```json
{
  "price": 25,
  "price_currencies": {
    "USD": { "amount": 25, "symbol": "$", "formatted": "$ 25" },
    "SYP": { "amount": 325000, "symbol": "ل.س", "formatted": "ل.س 325000" }
  }
}
```

---

## 8) الخصم وسعر بعد الخصم

**أولوية الخصم:**
1. خصم المتغيّر (`variants[].discount` + `discount_type`) — إن وُجد
2. وإلا خصم المنتج / Flash Sale

### حساب سعر بعد الخصم (واجهة — live)

```js
function priceAfterDiscount(price, discountType, discount) {
  const p = Number(price) || 0;
  const d = Number(discount) || 0;
  if (!p || !discountType || discountType === 'none' || d <= 0) return p;
  if (discountType === 'percentage') return Math.round((p - p * (d / 100)) * 100) / 100;
  if (discountType === 'fixed') return Math.max(0, Math.round((p - d) * 100) / 100);
  return p;
}
```

| `discount_type` | `discount` | مثال: سعر 100 $ |
|-----------------|------------|-----------------|
| `none` | 0 | 100 |
| `percentage` | 10 | 90 |
| `fixed` | 15 | 85 |

### حقول الإرسال

```text
variants[0][discount]=10
variants[0][discount_type]=percentage
```

### حقول GET (محسوبة — للعرض)

```json
{
  "price": 25,
  "discount": 10,
  "discount_type": "percentage",
  "discount_amount": 2.5,
  "price_after_discount": 22.5,
  "discount_amount_currencies": { "USD": {...}, "SYP": {...} },
  "price_after_discount_currencies": { "USD": {...}, "SYP": {...} }
}
```

| الحقل | المعنى |
|-------|--------|
| `discount` | **قيمة الإدخال** (10 = 10%) |
| `discount_type` | نوع الخصم |
| `discount_amount` | **المبلغ المخصوم** (محسوب) |
| `price_after_discount` | السعر النهائي — **عرض فقط، لا ترسله** |

---

## 9) الكمية والمخزون

| الحقل | استخدمه؟ | ملاحظة |
|-------|----------|--------|
| `variants[].quantity` | **نعم** | المصدر الوحيد لمخزون المتغيّر — «الكمية المتوفرة» |
| `products.quantity` | قراءة فقط | يُحسب تلقائيًا = مجموع كميات المتغيّرات |
| `products.stock` | **لا ترسله** | حقل قديم — **لا تعرضه** |

---

## 10) موعد التسليم

> **موعد التسليم على مستوى المنتج** — ليس حقلًا منفصلًا لكل متغيّر.

| قناة البيع | `sale_channel` | سلوك «موعد التسليم» |
|------------|----------------|---------------------|
| للموقع | `platform` | نص ثابت للمنصة — **لا حقل إدخال** |
| ربط بمتجر | `shop` | حقل `delivery_time` في فورم المنتج |

```text
delivery_time=12-48 ساعة
```

- يظهر **مرة واحدة** في فورم المنتج (خارج جدول المتغيّرات).
- GET يرجع `delivery_time` على المنتج (`effective_delivery_time`).
- **لا ترسل** `delivery_time` داخل `variants[]`.

---

## 11) توليد SKU تلقائي (واجهة)

الباك **لا يولّد SKU تلقائيًا**. الواجهة تولّده من بيانات المنتج + الصفات:

```js
function generateSku(productSku, attributeValues) {
  const base = (productSku || 'PROD').toUpperCase().replace(/\s+/g, '');
  const suffix = attributeValues
    .map(v => (v.name?.en || v.name?.ar || '').slice(0, 4).toUpperCase())
    .join('');
  return `${base}-${suffix}`;
}
// مثال: NIKRUN-123 + أحمر + L → NIKRUN-123-REDLAR
```

- SKU **قابل للتعديل** يدويًا بعد التوليد.
- يجب أن يكون **فريدًا** عبر كل المتغيّرات.

---

## 12) حفظ المنتج — Payload

```text
POST /api/admin/products
PUT  /api/admin/products/{id}
Content-Type: multipart/form-data

delivery_time=3-5 أيام

variants[0][sku]=SWEAT-RED-S
variants[0][barcode]=0194253404316
variants[0][price]=25
variants[0][quantity]=5
variants[0][discount]=10
variants[0][discount_type]=percentage
variants[0][is_active]=1
variants[0][attributes_values_ids][0]=6
variants[0][attributes_values_ids][1]=10

variants[1][sku]=SWEAT-RED-M
variants[1][barcode]=0194253404317
variants[1][price_syp]=325000
variants[1][quantity]=8
variants[1][discount]=10
variants[1][discount_type]=percentage
variants[1][is_active]=1
variants[1][attributes_values_ids][0]=6
variants[1][attributes_values_ids][1]=11

shop_variants[0][shop_id]=1
shop_variants[0][variant_index]=0
shop_variants[0][cost_price]=10000
```

### ما **لا** ترسله

| محذوف | السبب |
|-------|--------|
| `variants[].name.ar` / `name.en` | اسم المتغيّر — **محذوف من الواجهة** |
| `variants[].price_after_discount` | يُحسب من الباك |
| `variants[].price_currencies` | للقراءة فقط |
| `shop_variants[].price` / `quantity` | انتقلت للمتغيّر |

### قواعد الحفظ

1. **`variants` = replace كامل** — المتغيّرات غير المُرسلة تُحذف (soft delete).
2. احفظ `variants[i][id]` من GET عند التعديل.
3. `variant_index` = ترتيب الصف في `variants[]` (0, 1, 2...).
4. **لا ترسل** `price`/`quantity` داخل `shop_variants`.

---

## 13) Checklist

- [ ] **أول متغيّر = Single** (جدول فارغ) → صف واحد
- [ ] **من الثاني = Multi** → توسّع لعدة صفوف
- [ ] **احذفوا** حقول اسم المتغيّر (ar/en) من الفورم والجدول
- [ ] جلب صفات الفئة عند اختيار المستوى 1 فقط
- [ ] زر «إضافة متغيّر» — Single أو Multi حسب `variantsTable.length`
- [ ] توسيع الاختيارات إلى صفوف variants قبل الحفظ
- [ ] **سعر $ + سعر ل.س** مع مزامنة تلقائية لكل متغيّر
- [ ] **خصم + نوع خصم + سعر بعد الخصم** (عرض فقط) لكل متغيّر
- [ ] **الكمية المتوفرة** — حقل واحد (`quantity`)
- [ ] **الباركود** — `variants[].barcode` لكل متغيّر
- [ ] **موعد التسليم** — `delivery_time` على المنتج (ليس per variant)
- [ ] SKU توليد من الواجهة + قابل للتعديل
- [ ] دعم دفعات متعددة (لون فضي + مقاسات، ثم أسود + 42)

---

## Migration مطلوب

```bash
php artisan migrate
```

يضيف `discount` و `discount_type` لجدول `product_variants`.
