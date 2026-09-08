# الويب — عرض متغيّرات المنتج (صفات + سعر + خصم + كمية)

> **أرسلوا هذا الملف لفريق الويب فقط.**  
> Base: `/api/user` + `Accept-Language: ar|en`.  
> **آخر تحديث:** 2026-09-08  
> **ملخص التغييرات:** [`product-variants-storefront-update.md`](product-variants-storefront-update.md)  
> **سعر · خصم · كمية · باركود · SKU:** [`WEB_PRODUCT_PRICING_FIELDS.md`](./WEB_PRODUCT_PRICING_FIELDS.md)

---

## الفهرس

1. [نظرة عامة](#1-نظرة-عامة)
2. [Endpoints](#2-endpoints)
3. [اختيار المتغيّر في صفحة المنتج](#3-اختيار-المتغيّر-في-صفحة-المنتج)
4. [السعر USD / SYP](#4-السعر-usd--syp)
5. [الخصم والسعر](#5-الخصم-والسعر)
6. [الكمية](#6-الكمية)
7. [موعد التسليم](#7-موعد-التسليم)
8. [React — أمثلة](#8-react--أمثلة)
9. [Checklist](#9-checklist)

---

## 1) نظرة عامة

- **إدارة المتغيّرات:** من الداشبورد فقط.
- **الموقع:** يعرض ويختار من `shop_variants` في صفحة المنتج.
- كل متغيّر = تركيبة صفات (لون + مقاس...) + سعر + كمية + خصم اختياري.
- **لا يوجد اسم متغيّر** — الهوية من `attributes` + `sku`.

---

## 2) Endpoints

| الغرض | Method | Endpoint |
|-------|--------|----------|
| صفات الفئة | GET | `/api/user/categories/{categoryId}/attributes` |
| تفاصيل المنتج | GET | `/api/user/products/{id}?lat=&lng=` |
| إضافة للسلة | POST | `/api/user/cart/items` |

### shop_variants — الحقول

```json
{
  "delivery_time": "3-5 أيام",
  "shop_variants": [{
    "id": 55,
    "variant_id": 15,
    "sku": "SHOE-SILVER-40",
    "price": 80,
    "price_usd": 80,
    "price_syp": 1040000,
    "price_currencies": {
      "USD": { "amount": 80, "symbol": "$", "formatted": "$ 80" },
      "SYP": { "amount": 1040000, "symbol": "ل.س", "formatted": "ل.س 1040000" }
    },
    "discount_value": 10,
    "discount_type": "percentage",
    "discount": 8,
    "discount_usd": 8,
    "discount_syp": 104000,
    "price_after_discount": 72,
    "price_after_discount_usd": 72,
    "price_after_discount_syp": 936000,
    "price_after_discount_currencies": {
      "USD": { "amount": 72, "symbol": "$", "formatted": "$ 72" },
      "SYP": { "amount": 936000, "symbol": "ل.س", "formatted": "ل.س 936000" }
    },
    "quantity": 3,
    "attributes": [
      {
        "id": 8,
        "name": "فضي",
        "hex": "#C0C0C0",
        "category_attribute": { "id": 1, "name": "لون", "type": "color" }
      },
      {
        "id": 22,
        "name": "40",
        "category_attribute": { "id": 2, "name": "قياس", "type": "square" }
      }
    ],
    "images": []
  }]
}
```

| الحقل | المعنى |
|-------|--------|
| `attributes` | **هوية المتغيّر** — لا يوجد `name` |
| `price_usd` / `price_syp` | السعر الأصلي بعملتين (محسوب من USD) |
| `discount_value` | قيمة الخصم المُدخلة (مثلاً 10 لـ 10%) |
| `discount_type` | `none` \| `percentage` \| `fixed` |
| `discount` | **المبلغ المخصوم** (للعرض) |
| `price_after_discount` | السعر النهائي |
| `quantity` | المخزون — **حقل واحد** |

---

## 3) اختيار المتغيّر في صفحة المنتج

### UI مقترح

```
اللون:    ( ● أحمر ) ( ○ أسود ) ( ○ فضي )
المقاس:   [ 37 ] [ 38 ] [ 40 ] [ 42 ]

السعر:    72 $   ← price_after_discount_usd
          ~~80 $~~  ← price_usd (إذا discount > 0)
أو:       936,000 ل.س

التوفر:   3 متبقي
التسليم:  3-5 أيام  ← product.delivery_time
```

### المنطق

```js
function buildAttributesMap(variants) {
  const map = new Map();
  for (const v of variants) {
    for (const attr of v.attributes ?? []) {
      const caId = attr.category_attribute?.id;
      if (!caId) continue;
      if (!map.has(caId)) map.set(caId, new Map());
      map.get(caId).set(attr.id, attr);
    }
  }
  return map;
}

function findSelectedVariant(variants, selected) {
  const needed = Object.values(selected);
  return variants.find(v =>
    needed.every(id => (v.attributes ?? []).some(a => a.id === id))
  ) ?? null;
}

function availableSizesForColor(variants, colorValueId) {
  return variants
    .filter(v => (v.attributes ?? []).some(a => a.id === colorValueId))
    .flatMap(v => (v.attributes ?? []).filter(a => a.category_attribute?.type === 'square'));
}
```

### قواعد UX

1. **لا تفترض** كل الألوان × كل المقاسات — اعرض فقط ما في `shop_variants`.
2. عند تغيير اللون → أعد تعيين المقاس إذا لم يعد متاحًا.
3. `quantity === 0` → زر «أضف للسلة» معطّل + «غير متوفر».
4. منتج بدون صفات → اختر `shop_variants[0]` تلقائيًا.
5. **لا تعرض** اسم متغيّر — استخدم الصفات (لون + مقاس).

---

## 4) السعر USD / SYP

الباك يحوّل تلقائياً من USD المخزّن إلى SYP.

| للعرض | الحقل |
|-------|-------|
| سعر USD | `price_usd` أو `price_currencies.USD.amount` |
| سعر SYP | `price_syp` أو `price_currencies.SYP.amount` |
| بعد الخصم USD | `price_after_discount_usd` |
| بعد الخصم SYP | `price_after_discount_syp` |

```js
function formatPrice(variant, { useSyp = false } = {}) {
  const hasDiscount = (variant.discount ?? 0) > 0;
  if (useSyp) {
    return hasDiscount ? variant.price_after_discount_syp : variant.price_syp;
  }
  return hasDiscount ? variant.price_after_discount_usd : variant.price_usd;
}
```

- **لا تحسب** سعر الصرف محلياً — استخدم قيم API.
- اعرض العملة حسب تفضيل المستخدم.

---

## 5) الخصم والسعر

**أولوية الخصم (محسوبة من الباك):**
1. خصم المتغيّر
2. خصم المنتج / Flash Sale

```js
function renderPrice(variant) {
  const hasDiscount = (variant.discount ?? 0) > 0;
  return {
    currentUsd: variant.price_after_discount_usd ?? variant.price_after_discount,
    originalUsd: hasDiscount ? variant.price_usd ?? variant.price : null,
    currentSyp: variant.price_after_discount_syp,
    originalSyp: hasDiscount ? variant.price_syp : null,
    badge: variant.discount_type === 'percentage' && variant.discount_value > 0
      ? `-${variant.discount_value}%`
      : null,
  };
}
```

| للعرض | الحقل |
|-------|-------|
| السعر الحالي | `price_after_discount` (+ `_usd` / `_syp`) |
| السعر قبل الخصم | `price` / `price_usd` (مع خط) |
| شارة النسبة | `discount_value` + `discount_type === 'percentage'` |
| «وفّرت X» | `discount` (المبلغ) |

---

## 6) الكمية

- استخدم **`quantity` فقط** — «الكمية المتوفرة».
- الحقل ممكن **`null`** — اعتبروه 0 (غير متوفر).
- لا حقل `stock` على المتغيّر.
- `maxQuantity = Math.min(variant.quantity ?? 0, product.max_purchase_quantity ?? variant.quantity ?? 0)`.

```js
const canAddToCart = Boolean(
  selectedVariant?.id &&
  (selectedVariant.quantity ?? 0) > 0
);
```

---

## 7) موعد التسليم

- **على مستوى المنتج** — `product.delivery_time` (ليس per variant).
- اعرضه مرة واحدة في صفحة المنتج.

```jsx
{product.delivery_time && (
  <p className="delivery-time">التسليم: {product.delivery_time}</p>
)}
```

---

## 8) React — أمثلة

### ProductVariantPicker

```jsx
function ProductVariantPicker({ product }) {
  const variants = product.shop_variants ?? [];
  const [selected, setSelected] = useState({});
  const useSyp = useCurrencyPreference() === 'SYP';

  const current = useMemo(
    () => findSelectedVariant(variants, selected),
    [variants, selected]
  );

  const price = current ? renderPrice(current) : null;

  return (
    <div>
      {/* Color swatches — from attributes, not variant name */}
      {/* Size buttons — filtered by selected color */}
      {price && (
        <div className="price">
          {useSyp ? (
            <>
              {price.originalSyp && <s>{price.originalSyp.toLocaleString()} ل.س</s>}
              <strong>{price.currentSyp?.toLocaleString()} ل.س</strong>
            </>
          ) : (
            <>
              {price.originalUsd && <s>{price.originalUsd} $</s>}
              <strong>{price.currentUsd} $</strong>
            </>
          )}
          {price.badge && <span className="badge">{price.badge}</span>}
        </div>
      )}
      {product.delivery_time && (
        <p>التسليم: {product.delivery_time}</p>
      )}
      <p>متوفر: {current?.quantity ?? 0}</p>
      <button disabled={!canAddToCart(current)} onClick={() => addToCart(current.id)}>
        أضف للسلة
      </button>
    </div>
  );
}
```

### إضافة للسلة

```js
await fetch('/api/user/cart/items', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${token}` },
  body: JSON.stringify({
    shop_product_variant_id: selectedVariant.id,
    quantity: 1,
  }),
});
```

---

## 9) Checklist

- [ ] **لا تعرض** اسم متغيّر — استخدم `attributes` + `sku`
- [ ] صفات من `attributes_map` أو استنتاج من `shop_variants`
- [ ] فلترة المقاسات حسب اللون (فقط المتاح في API)
- [ ] لا Cartesian افتراضي — اعرض تركيبات موجودة فقط
- [ ] `quantity` = مخزون (حقل واحد)
- [ ] عرض `price_after_discount` + `discount_value`/`discount_type`
- [ ] دعم **USD و SYP** من API
- [ ] `delivery_time` من المنتج — ليس per variant
- [ ] حماية `shop_variants[].id` null (منتجات المنصة)
- [ ] fallback الصور: variant → product → thumbnail
