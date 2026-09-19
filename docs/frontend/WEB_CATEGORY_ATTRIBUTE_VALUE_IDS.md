# الويب — قيم الصفات بالـ ID (إعادة التسمية ما تقطع المنتج)

> **أرسلوا هذا الملف لفريق الويب فقط.**  
> **آخر تحديث:** 20 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> داشبورد: [`DASHBOARD_CATEGORY_ATTRIBUTE_VALUE_IDS.md`](./DASHBOARD_CATEGORY_ATTRIBUTE_VALUE_IDS.md)  
> Flutter: [`FLUTTER_CATEGORY_ATTRIBUTE_VALUE_IDS.md`](./FLUTTER_CATEGORY_ATTRIBUTE_VALUE_IDS.md)

الأدمن يغيّر «صغير» → «XS». الـ ID يبقى. المنتج ما ينمسح.  
الموقع يعرض الاسم الجديد ويصفّي / يختار بالـ ID.

ما في endpoint جديد.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [فلاتر الفئة — chips](#2-فلاتر-الفئة--chips)
3. [صفحة المنتج](#3-صفحة-المنتج)
4. [اختيار اللون / المقاس](#4-اختيار-اللون--المقاس)
5. [غلط vs صح](#5-غلط-vs-صح)
6. [Checklist](#6-checklist)

---

## 1) القاعدة

| سطح | المفتاح الثابت | النص |
|-----|----------------|------|
| chips / فلتر | `values[].id` → `attribute_values=31` | `name` من آخر GET |
| picker | `attributes_map[].options[].id` أو `attributes[].id` | `name` / `value` |
| سلة | `shop_variants[].id` | — |

لا تخزّنوا «صغير» في localStorage. الاسم يتغيّر. الـ ID لا.

```http
GET /api/user/categories/{categoryId}/attributes
GET /api/user/products?category_id=12&attribute_values=31,40
GET /api/user/products/{id}
```

---

## 2) فلاتر الفئة — chips

```http
GET /api/user/categories/{categoryId}/attributes
Accept-Language: ar
```

```json
{
  "data": [
    {
      "id": 11,
      "name": { "ar": "القياس", "en": "Size" },
      "type": "square",
      "values": [
        { "id": 31, "name": { "ar": "XS", "en": "XS" } },
        { "id": 32, "name": { "ar": "XL", "en": "XL" } }
      ]
    }
  ]
}
```

- State = `Set<number>` من `values[].id`
- نص الـ chip = `values[].name`
- طلب: `attribute_values=31,40` — منطق **OR**
- كاش بـ `root_category_id` — بعد إعادة تسمية: أعدوا الجلب

```ts
const selectedIds = new Set<number>(); // 31 — مو "صغير"

api.get("/api/user/products", {
  params: { category_id: categoryId, attribute_values: [...selectedIds].join(",") },
});
```

---

## 3) صفحة المنتج

```http
GET /api/user/products/{id}
Accept-Language: ar
```

```json
{
  "attributes_map": [
    {
      "id": 10,
      "attribute": "لون",
      "type": "color",
      "values": ["أحمر", "أزرق"],
      "options": [
        { "id": 40, "name": "أحمر", "hex": "#FF0000" },
        { "id": 41, "name": "أزرق", "hex": "#0000FF" }
      ]
    },
    {
      "id": 11,
      "attribute": "قياس",
      "type": "square",
      "values": ["XS", "XL"],
      "options": [
        { "id": 31, "name": "XS", "hex": null },
        { "id": 32, "name": "XL", "hex": null }
      ]
    }
  ],
  "shop_variants": [
    {
      "id": 881,
      "attributes": [
        { "id": 40, "attribute": "لون", "value": "أحمر", "type": "color", "hex": "#FF0000" },
        { "id": 31, "attribute": "قياس", "value": "XS", "type": "square", "hex": null }
      ]
    }
  ]
}
```

- `values` (strings) للتوافق — فضّلوا `options[].id`
- لون الـ swatch: `hex`
- بعد إعادة التسمية: نفس `id`، `name` / `value` الجديد
- صفتان منفصلتان — لا تلزقوا `أحمر XS` في زر واحد

---

## 4) اختيار اللون / المقاس

المطابقة بـ **IDs**:

```ts
function findVariant(
  variants: ShopVariant[],
  selectedIds: number[]
) {
  if (!selectedIds.length) return null;
  return (
    variants.find((v) =>
      selectedIds.every((id) => (v.attributes ?? []).some((a) => a.id === id))
    ) ?? null
  );
}
```

`attribute + value` يشتغل بعد الـ refetch (الاسم الجديد). لا تحتفظوا بالاسم القديم.

عند تغيير اللون: إذا المقاس الحالي غير موجود لهاللون، أول مقاس متاح من `shop_variants`.

السلة: `shop_product_variant_id` = `shop_variants[].id` المختار.

---

## 5) غلط vs صح

**غلط:**

```ts
selectedSizes.add("صغير");
localStorage.setItem("size", "صغير");
const variant = product.shop_variants[0];
label = attrs.map((a) => a.value).join(" "); // أحمر XS
```

**صح:**

```ts
selectedIds.add(31);
const variant = findVariant(product.shop_variants, [...selectedIds]);
addToCart(variant.id);
```

---

## 6) Checklist

- [ ] chips: `key={value.id}` وفلتر `attribute_values` كـ IDs
- [ ] picker من `attributes_map[].options[]` — صفتان منفصلتان
- [ ] مطابقة المتغيّر بـ `attributes[].id`
- [ ] السلة: `shop_variants[].id` المختار — مو دائماً `[0]`
- [ ] بعد «صغير» → «XS»: الاسم يتبدّل والمتغيّر يبقى
- [ ] لا `أحمر XS` في خيار واحد
- [ ] لا تقارنوا أسماء محفوظة محلياً مع GET جديد
