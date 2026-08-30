# الداشبoard — متغيّرات المنتج (Single select + كارد حقول لكل متغيّر)

> **أرسلوا هذا الملف لفريق الداشبoard / Flutter Web فقط.**  
> Base: `/api/admin` + Admin token.  
> **آخر تحديث:** 2026-08-30

---

## الفهرس

1. [الفكرة العامة](#1-الفكرة-العامة)
2. [نموذج إضافة متغيّر — Single لكل select](#2-نموذج-إضافة-متغيّر--single-لكل-select)
3. [كارد المتغيّر — التخطيط والحقول](#3-كارد-المتغيّر--التخطيط-والحقول)
4. [قائمة المتغيّرات](#4-قائمة-المتغيّرات)
5. [جلب صفات الفئة](#5-جلب-صفات-الفئة)
6. [توليد SKU — إنجليزي فقط](#6-توليد-sku--إنجليزي-فقط)
7. [السعر · الخصم · التحديث](#7-السعر--الخصم--التحديث)
8. [Payload · Checklist](#8-payload--checklist)

---

## 1) الفكرة العامة

| المفهوم | الشرح |
|---------|--------|
| **متغيّر** | تركيبة **واحدة** (لون + مقاس + تصميم...) + SKU + أسعار + كمية |
| **إضافة** | كل select = **قيمة واحدة** → **متغيّر واحد** |
| **الباك** | `variants[]` — صف لكل متغيّر |

### محذوف

- ~~اسم المتغيّر (ar/en)~~
- ~~multi-select للمقاسات~~ — **كل الصفات single**
- ~~تفرد كروت حسب عدد المقاسات~~

---

## 2) نموذج إضافة متغيّر — Single لكل select

> مثل الصورة المرجعية: **dropdown واحد لكل صفة** — **قيمة واحدة فقط**.

```
┌─ إضافة متغيّر جديد ──────────────────────┐
│                                           │
│  لون      ▼  [ green        ]             │  ← single
│  قياس     ▼  [ l            ]             │  ← single
│  تصميم    ▼  [ تعبان       ]             │  ← single
│                                           │
│  [ إضافة متغيّر جديد ]                    │
└───────────────────────────────────────────┘
```

### قواعد

| # | القاعدة |
|---|---------|
| 1 | **كل صفة = select واحد** — لون، مقاس، تصميم... |
| 2 | **لا multi-select** — لا checkboxes للمقاسات |
| 3 | **لا حقول سعر/كمية** داخل نموذج الإضافة — فقط الصفات |
| 4 | كل ضغطة «إضافة» → **متغيّر واحد** جديد بالقائمة |
| 5 | لتكرار نفس اللون بمقاس آخر → **إضافة مرة ثانية** (green + M...) |

```js
// ✅ كل الصفات single
function getSelectionMode(/* attribute */) {
  return 'single'; // دائماً
}

function onAddVariant(selections) {
  const attrs = selections
    .filter(s => s.valueId != null)
    .map(s => s.valueId);

  if (attrs.length === 0) return;

  setVariantsTable(prev => [
    ...prev,
    {
      attributes_values_ids: attrs,
      sku: generateVariantSku(productSku, attrs), // English only — see §6
      price: null,
      priceSyp: null,
      discount: 0,
      discountType: 'none',
      quantity: 0,
      barcode: null,
      is_active: true,
    },
  ]);
}
```

### ❌ ممنوع

```js
// multi مقاس → 4 متغيّرات دفعة واحدة
selectedSizes.map(size => addVariant({ color, size }));

// تفرد كروت حقول عند الاختيار
attributes.map(() => <FieldsCard />);
```

### تكرار اللون — مسموح ✅ (مع مقاس / شكل مختلف)

> **نفس اللون كل مرة** — بس **غيّر المقاس أو الشكل (تصميم)** → متغيّr جديد.
> **ممنوع** فقط نفس **التركيبة الثلاثية** بالضبط (لون + مقاس + شكل).

#### مثال — أصفر × 4 مقاسات × نفس الشكل

```
إضافة 1:  أصفر + S   + تعبان  →  كارد 1
إضافة 2:  أصفر + M   + تعبان  →  كارد 2   ← نفس اللون ✅
إضافة 3:  أصفر + L   + تعبان  →  كارد 3   ← نفس اللون ✅
إضافة 4:  أصفر + XL  + تعبان  →  كارد 4   ← نفس اللون ✅
```

#### مثال — نفس اللون + مقاس + **شكل مختلف**

```
إضافة 1:  أصفر + L + تعبان   →  كارد 1
إضافة 2:  أصفر + L + نجوم    →  كارد 2   ← نفس اللون والمقاس، شكل مختلف ✅
```

| الحالة | مسموح؟ |
|--------|--------|
| أصفر + L + تعبان | ✅ |
| **أصفر + XL + تعبان** (نفس اللون، مقاس مختلف) | ✅ |
| **أصفر + L + نجوم** (نفس اللون والمقاس، شكل مختلف) | ✅ |
| أصفر + L + تعبان (نفس الثلاثة مرتين) | ❌ |

**الباك لا يمنع تكرار اللون** — يمنع فقط إذا أرسلت نفس `attributes_values_ids` بالكامل.

#### ❌ خطأ شائع في الفرونت

```js
// غلط — يخفي اللون إذا استُخدم مرة
const usedColors = variants.map(v => v.colorId);
colors.filter(c => !usedColors.includes(c.id));
```

```dart
// غلط — يمنع اختيار لون موجود
if (variants.any((v) => v.colorId == selectedColorId)) {
  return; // ← هذا يمنع أخضر + XL بعد أخضر + L
}
```

#### ✅ الصح — duplicate = التركيبة الكاملة فقط

```js
function variantSignature(attributeValueIds) {
  return [...attributeValueIds].sort((a, b) => a - b).join('-');
}

function isDuplicateVariant(existingVariants, newIds) {
  const sig = variantSignature(newIds);
  return existingVariants.some(
    (v) => variantSignature(v.attributes_values_ids ?? []) === sig
  );
}

function onAddVariant(selections) {
  const ids = selections.map((s) => s.valueId).filter(Boolean);
  if (ids.length === 0) return;
  if (isDuplicateVariant(variants, ids)) {
    toast('هذا المتغيّr موجود — غيّر المقاس أو الشكل');
    return;
  }
  // ... add variant — أخضر+L ثم أخضر+XL OK
}
```

```dart
bool isDuplicate(List<int> a, List<int> b) {
  final sa = [...a]..sort();
  final sb = [...b]..sort();
  return sa.length == sb.length && List.generate(sa.length, (i) => sa[i] == sb[i]).every((x) => x);
}
// أخضر(5)+L(10) vs أخضر(5)+XL(11) → false → مسموح
```

**لا تفلتر خيارات الـ dropdown** حسب المتغيّrات الموجودة — خلّي المستخدم يختار، وامنع فقط **نفس التركيبة** عند «إضافة».


---

## 3) كارد المتغيّر — التخطيط والحقول

> بعد الإضافة — **كارد واحد لكل متغيّر** (مثل المرجع + حقولكم).

```
┌─ متغيّرات المنتج ─────────────────────────────────────────┐
│  [توليد جميع SKU المفقودة]    إجمالي: 3 | SKU: 2 | ...  │
├─────────────────────────────────────────────────────────┤
│  ● green · l · تعبان                    [متوفر]         │
│                                                         │
│  ┌─ المعلومات الأساسية ─────────────────────────────┐  │
│  │  رمز المتغيّر (SKU)  [ LIG-8188-GREEN-L      ]   │  │
│  │                                                   │  │
│  │  ┌─────────┬─────────┬──────────┬──────────────┐ │  │
│  │  │ سعر ($) │ سعر(ل.س)│ نوع خصم  │ قيمة الخصم  │ │  │
│  │  │ [2300]  │ [0    ] │ [نسبة ▼] │ [10       ]  │ │  │
│  │  └─────────┴─────────┴──────────┴──────────────┘ │  │
│  │  سعر بعد الخصم (readonly): [ SYP … · $ … ]       │  │
│  │                                                   │  │
│  │  ┌─────────┬─────────┬──────────────┐            │  │
│  │  │ الكمية  │ الباركود│ تكلفة (اخ.) │            │  │
│  │  │ [30  ]  │ […    ] │ [188000  $] │            │  │
│  │  └─────────┴─────────┴──────────────┘            │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
│  [حفظ التغييرات]  [إلغاء]                              │
└─────────────────────────────────────────────────────────┘
```

### حقول الكارد — دمج المرجع + API

| # | الواجهة (عربي) | API | required؟ |
|---|----------------|-----|-----------|
| — | **الصفات** (لون · مقاس · تصميم) | `attributes_values_ids` | **للإضافة فقط** — لازم تختار صفات قبل «إضافة» |
| 1 | رمز المتغيّr (SKU) | `variants[].sku` | ❌ `nullable` |
| 2 | سعر ($) | `variants[].price` | ❌ `nullable` — إذا فارغ الباك يأخذ سعر المنتج |
| 3 | سعر (ل.س) | `variants[].price_syp` | ❌ — يُحوّل لـ `price` |
| 4 | نوع الخصم | `variants[].discount_type` | ❌ — افتراضي `none` |
| 5 | قيمة الخصm | `variants[].discount` | ❌ |
| 6 | سعر بعد الخصm | — | readonly |
| 7 | الكمية | `variants[].quantity` | ❌ `nullable` |
| 8 | الباركود | `variants[].barcode` | ❌ `nullable` |
| 9 | تكلفة (shop) | `shop_variants[].cost_price` | ❌ — على ربط الفرع |

**لا تعرض:** ~~الوزن~~ (مو موجود على `product_variants`) · ~~اسم المتغيّر~~



### كل الحقول — **مو required** (إلا الصفات للإضافة)

> **لا تضع `*` أو `required` على أي حقل** في كارد المتغيّr.
> النص «املأ كل الحقول» **خطأ** — الصحيح: «كل الحقول اختيارية».

| الحقل | الباك | الواجهة |
|-------|-------|---------|
| SKU | `nullable` | اختياري — يُولَّد تلقائياً إن أمكن |
| سعر $ / ل.س | `nullable` | اختياري — فارغ → سعر المنتج أو `0` |
| خصm | `nullable` | اختياري — افتراضي `none` / `0` |
| كمية | `nullable` | اختياري — فارغ → لا ترسل أو `0` |
| باركود | `nullable` | اختياري |

```text
# ✅ متغيّr بحد أدنى — مقبول
variants[0][attributes_values_ids][0]=5
variants[0][attributes_values_ids][1]=12

# ✅ بدون SKU ولا سعر ولا كمية — مقبول
variants[0][attributes_values_ids][0]=5
variants[0][attributes_values_ids][1]=12
```

**ملاحظة الباك:** إذا `price` فارغ → يُعيَّن تلقائياً من `product.price` (أو `0`).

### تحديث متغيّr واحد

```http
PUT /api/admin/product-variants/{id}
```

```json
{
  "sku": "LIG-8188-GREEN-L",
  "price": 2300,
  "discount": 10,
  "discount_type": "percentage",
  "quantity": 30,
  "barcode": "0194253404316"
}
```

أو ضمن المنتج:

```http
PUT /api/admin/products/{id}
```

```text
variants[0][id]=15
variants[0][sku]=LIG-8188-GREEN-L
variants[0][price]=2300
variants[0][quantity]=30
...
```

---

## 4) قائمة المتغيّرات

- **كارد واحد = متغيّر واحد** — مو كارد لكل مقاس بالنموذج.
- عدة متغيّرات = **عدة كروت** (أو tabs) — كل واحد فيه select values مختلفة.

```
إضافة 1: green + l + تعبان   →  كارد 1
إضافة 2: green + m + تعبان   →  كارد 2
إضافة 3: red   + l + تعبان   →  كارد 3
```

**بديل compact:** جدول + فتح كارد عند النقر على الصف — نفس الحقول.

---

## 5) جلب صفات الفئة

```http
GET /api/admin/category-attributes?category_id={categoryId}
```

| `type` | نموذج الإضافة |
|--------|--------------|
| `color` | dropdown — **single** |
| `square` (مقاس) | dropdown — **single** |
| `circle` (تصميم) | dropdown — **single** |

---



## 6) توليد SKU — إنجlيزi فقط

> **رمز التخزين التعريفي للمتغيّr** يُولَّد في **الواجهة**. **ممنوع** أي حرف عربي داخل SKU.

| ❌ خطأ | ✅ صح |
|---|---|
| `PROD-أخضر-XL` | `SKU-27T4376` |
| `PROD-بيج-YELXL` | `PROD-GREEN-XL` |

- **Dropdown** → `name.ar` للعرض
- **SKU** → `name.en` أو suffix عشوائي — **لا** `name.ar`
- لون: `name.en` أو `hex` بدون `#`

```js
function generateVariantSku(productSku) {
  const base = String(productSku).replace(/[^a-zA-Z0-9-]/g, '') || 'VAR';
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  const suffix = Array.from({ length: 6 }, () =>
    chars[Math.floor(Math.random() * chars.length)]
  ).join('');
  return `${base}-${suffix}`.toUpperCase(); // SKU-27T4376
}
```

```js
// بديل مقروء — English slugs فقط
function readableSku(productSku, selections) {
  const base = String(productSku).replace(/[^a-zA-Z0-9-]/g, '') || 'VAR';
  const parts = selections.map((s) => {
    const en = s.value?.name?.en ?? '';
    if (en && /^[\x00-\x7F]+$/.test(en)) return en.replace(/[^a-zA-Z0-9-]/g, '').toUpperCase();
    if (s.value?.hex) return s.value.hex.replace('#', '').toUpperCase();
    return String(s.valueId);
  });
  return [base, ...parts.filter(Boolean)].join('-');
}
```

---

## 7) السعر · الخصm · التحديث

### USD ↔ SYP

```js
const sypRate = currencies.find(c => c.code === 'SYP')?.exchange_rate ?? 1;
// كتب $ → عبّي ل.س والعكس
```

| الإرسال | الباك |
|---------|-------|
| `price` | ✅ USD |
| `price_syp` | يحوّل لـ USD |
| الاثنان | يعتمد **$** |

### سعر بعد الخصم

```js
function priceAfterDiscount(price, discountType, discount) {
  const p = Number(price) || 0;
  const d = Number(discount) || 0;
  if (!p || discountType === 'none' || d <= 0) return p;
  if (discountType === 'percentage') return Math.round((p - p * (d / 100)) * 100) / 100;
  if (discountType === 'fixed') return Math.max(0, p - d);
  return p;
}
```

---

## 8) Payload · Checklist

### مثال — 3 متغيّرات (single each)

```text
variants[0][sku]=PROD-GREEN-L
variants[0][price]=2300
variants[0][quantity]=30
variants[0][discount]=10
variants[0][discount_type]=percentage
variants[0][attributes_values_ids][0]=1
variants[0][attributes_values_ids][1]=2
variants[0][attributes_values_ids][2]=3

variants[1][sku]=PROD-GREEN-M
...
```

### Checklist

- [ ] **Single select** لكل صفة (لون · مقاس · تصميم) — **مو multi**
- [ ] نموذج الإضافة = **dropdowns فقط** — بدون حقول سعر
- [ ] كل «إضافة» = **متغيّر واحد** + **كارد واحد**
- [ ] **لا** `map` على المقاسات → كروت متعددة
- [ ] كارد المتغيّر: SKU + ($ · ل.س · خصم · بعد الخصم · كمية · باركود)
- [ ] **كل حقول المتغيّr اختيارية** — SKU · سعر · خصm · كمية · باركود — **بدون** `required` — مو `required` في الفورm
- [ ] تكلفة → `shop_variants[].cost_price` إن `sale_channel=shop`
- [ ] تحديث: `PUT product-variants/{id}` أو `PUT products/{id}` + `variants[i][id]`
- [ ] **SKU إنجlيزي فقط** — `SKU-27T4376` · لا `PROD-أخضر-XL` · استخدم `name.en` أو suffix عشوائي
- [ ] **احذف** `name.ar` / `name.en` من payload

---

## ملخص للمطور

```
نموذج إضافة:  لون ▼  +  مقاس ▼  +  تصميم ▼  (كلها single)
                    ↓ [إضافة]
كارد متغيّر:  SKU + حقولكم ($ · ل.س · خصم · كمية · باركود)
                    ↓ [حفظ]
API:           variants[i]  — صف واحد لكل متغيّر
```

**الباك جاهز — التعديل UI فقط.**
