# الداشبورد — كل المتغيّرات لازم تنحفظ وتشتغل (مو بس الأول)

> **أرسلوا هذا الملف لفريق الداشبورد فقط.**  
> **آخر تحديث:** 20 أيلول 2026  
> Base: `/api/admin` + Admin token  
> **الباك جاهز بعد `git pull`**  
> ويب: [`WEB_PRODUCT_ALL_VARIANTS.md`](./WEB_PRODUCT_ALL_VARIANTS.md)  
> Flutter: [`FLUTTER_PRODUCT_ALL_VARIANTS.md`](./FLUTTER_PRODUCT_ALL_VARIANTS.md)

الأدمن يضيف متغيّرين (مثال: **أزرق S** و **أسود L**) والموقع ما يفعّل إلا واحد.  
السبب غالباً: التاني بلا `attributes_values_ids` أو بلا ربط متجر أو `is_active=0`.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [GET المنتج — خزّنوا الـ IDs](#2-get-المنتج--خزّنوا-ال-ids)
3. [الحفظ — كل صف متغيّر كامل](#3-الحفظ--كل-صف-متغيّر-كامل)
4. [ربط المتجر](#4-ربط-المتجر)
5. [غلط vs صح](#5-غلط-vs-صح)
6. [Checklist](#6-checklist)

---

## 1) القاعدة

كل ضغطة «إضافة متغيّر» = **تركيبة واحدة** (لون + مقاس)، مو لون لحاله.

| أضفتوا | على الموقع يشتغل |
|--------|-------------------|
| أزرق + S | أزرق مع S فقط |
| أسود + L | أسود مع L فقط |
| بدكم أزرق L كمان | أضيفوا صف ثالث: أزرق + L |

لا تفترضوا أن الباك يولّد كل الألوان × كل المقاسات.

بعد `git pull`: افتحوا المنتج واضغطوا **«تحديث المنتج»** مرة — حتى المتغيّر التاني ينربط بالمتجر إذا كان انحفظ ناقص.

---

## 2) GET المنتج — خزّنوا الـ IDs

```http
GET /api/admin/products/{id}
```

```json
{
  "variants": [
    {
      "id": 91,
      "sku": "JEANS-BLUE-S",
      "is_active": true,
      "quantity": 10,
      "attributes_values_ids": [40, 31],
      "attributes": [
        { "id": 40, "attribute": "لون", "value": "أزرق", "type": "color", "hex": "#0000FF", "category_attribute_id": 10 },
        { "id": 31, "attribute": "قياس", "value": "S", "type": "square", "hex": null, "category_attribute_id": 11 }
      ],
      "shops": [{ "id": 5, "shop_id": 1, "shop_name": "متجر المنصة" }]
    },
    {
      "id": 92,
      "sku": "JEANS-BLACK-L",
      "is_active": true,
      "quantity": 10,
      "attributes_values_ids": [41, 32],
      "attributes": [
        { "id": 41, "attribute": "لون", "value": "أسود", "type": "color", "hex": "#000000", "category_attribute_id": 10 },
        { "id": 32, "attribute": "قياس", "value": "L", "type": "square", "hex": null, "category_attribute_id": 11 }
      ],
      "shops": [{ "id": 6, "shop_id": 1, "shop_name": "متجر المنصة" }]
    }
  ]
}
```

- State الصف = `variant.id` + `attributes_values_ids` (أرقام).
- نص الكارد («أزرق S») للعرض فقط — **لا تعيدوا بناء الـ IDs من الاسم**.
- `attributes[].id` = نفس قيم `attributes_values_ids`.

---

## 3) الحفظ — كل صف متغيّر كامل

```http
PUT /api/admin/products/{id}
Content-Type: multipart/form-data
```

```text
variants[0][id]=91
variants[0][sku]=JEANS-BLUE-S
variants[0][price]=25
variants[0][quantity]=10
variants[0][is_active]=1
variants[0][attributes_values_ids][0]=40
variants[0][attributes_values_ids][1]=31

variants[1][id]=92
variants[1][sku]=JEANS-BLACK-L
variants[1][price]=25
variants[1][quantity]=10
variants[1][is_active]=1
variants[1][attributes_values_ids][0]=41
variants[1][attributes_values_ids][1]=32
```

صف جديد (بعد «إضافة»): **لا** ترسلوا `id`. أرسلوا `attributes_values_ids` من الـ dropdowns.

| حقل | إذا ما انرسل | الباك |
|-----|---------------|--------|
| `attributes_values_ids` | صف جديد فاضي الصفات | الموقع ما يقدر يختار هالصف |
| `is_active` | صف جديد → `true` | المتغيّر غير النشط **ما يظهر** على الموقع |
| `quantity` | صف جديد → كمية المنتج | `null` / `0` على الموقع = غير متوفر + سلة معطّلة |
| `price` | سعر المنتج | — |

مقبول كمان (الباك ينسخ الـ IDs):

```text
variants[1][attributes][0][id]=41
variants[1][attributes][1][id]=32
```

فضّلوا `attributes_values_ids` صريح.

---

## 4) ربط المتجر

الموقع يضيف للسلة بـ `shop_variants[].id` (= صف `shop_product_variants`).  
بلا ربط متجر: السعر يظهر، **السلة معطّلة**.

**لا ترسلوا قائمة فروع.** الباك يربط كل المتغيّرات بمتجر البائع.

| قناة | ماذا ترسلوا |
|------|-------------|
| `sale_channel=platform` | لا شيء — الباك يربط بمتجر المنصة |
| `sale_channel=shop` | `shop_id` مرة واحدة على المنتج |

إذا أرسلتوا `shop_variants` القديمة للمتغيّر `0` فقط، الباك **ينسخ** نفس المتجر للتاني.

بعد الحفظ: كل `variants[i].shops` لازم فيه متجر. إذا التاني `shops: []` — الحفظ ناقص.

---

## 5) غلط vs صح

**غلط — هذا سبب «ما يتفعّل إلا واحد»:**

```ts
// IDs بس للأول
form.append("variants[0][attributes_values_ids][0]", blueId);
form.append("variants[1][sku]", "JEANS-BLACK-L"); // بلا صفات

// متجر بس للأول — ما عاد لازم؛ الباك يربط الكل
form.append("shop_variants[0][variant_index]", "0");

// التوغل الثاني ينرسل 0 أو ما ينرسل وأنتم تقصدون مفعّل
form.append("variants[1][is_active]", "0");
```

**صح:**

```ts
variants.forEach((v, i) => {
  if (v.id) form.append(`variants[${i}][id]`, String(v.id));
  form.append(`variants[${i}][is_active]`, v.is_active ? "1" : "0");
  (v.attributes_values_ids ?? []).forEach((id, j) => {
    form.append(`variants[${i}][attributes_values_ids][${j}]`, String(id));
  });
});
```

لا تمسحوا `attributes_values_ids` من الـ state بعد الحفظ. خذوها من الـ GET.

---

## 6) Checklist

- [ ] كل صف متغيّر يرسل `attributes_values_ids` (IDs من dropdown الصفة)
- [ ] بعد GET: احتفظوا بـ `variants[].id` و `attributes_values_ids` — مو الاسم فقط
- [ ] `is_active=1` لكل متغيّر بدكم يظهر على الموقع
- [ ] كمية/سعر: املوا أو اتركوا فاضي (الباك يورث من المنتج للصف الجديد)
- [ ] `sale_channel=shop`: `shop_id` مرة واحدة — لا قائمة فروع
- [ ] بعد الحفظ: `GET` → المتغيّران في `variants[]` ولكل واحد `shops` غير فاضي
- [ ] المنتجات المحفوظة قبل الإصلاح: «تحديث المنتج» مرة
- [ ] بدكم كل لون × كل مقاس: صف لكل تركيبة (أزرق S، أزرق L، أسود S، أسود L)
