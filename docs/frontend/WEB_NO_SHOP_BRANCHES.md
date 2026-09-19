# الويب — إلغاء الفروع واسم المتجر من صفحة المنتج

> **أرسلوا هذا الملف لفريق الويب فقط.**  
> **آخر تحديث:** 20 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> داشبورد: [`DASHBOARD_NO_SHOP_BRANCHES.md`](./DASHBOARD_NO_SHOP_BRANCHES.md)  
> Flutter: [`FLUTTER_NO_SHOP_BRANCHES.md`](./FLUTTER_NO_SHOP_BRANCHES.md)

على صفحة المنتج كان يظهر دروب داون **Branch / فرعة المنصة** لأن المنتج مربوط بمتجر.  
**هذا ملغى.** ما في فروع. ما في اختيار متجر. ما في اسم متجر على الصفحة.

الألوان والمقاسات تبقى. وقت التسليم يبقى. السلة تبقى.

ما في endpoint جديد: `GET /api/user/products/{id}`.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [ماذا يُحذف](#2-ماذا-يحذف)
3. [شكل الـ API](#3-شكل-الـ-api)
4. [السلة](#4-السلة)
5. [غلط vs صح](#5-غلط-vs-صح)
6. [Checklist](#6-checklist)

---

## 1) القاعدة

| قبل | بعد |
|-----|-----|
| دروب داون Branch + اسم المتجر | **لا UI** |
| `available_shops = [{ id, name }]` | `available_shops = []` دائماً |
| الزبون يختار فرع | الزبون يختار لون / مقاس فقط |

`shop_variants` = تركيبات المنتج (أزرق S، أسود L) — **مو** قائمة فروع.

---

## 2) ماذا يُحذف

احذفوا من صفحة `/product/{id}` بالكامل:

- دروب داون **Branch** / **الفرع** / **المتجر**
- أي نص «فرعة المنصة» أو اسم متجر فوق الألوان
- أي UI مبني على `available_shops` أو `shop_name`

```js
// ❌ لا
(product.available_shops ?? []).map((shop) => (
  <option key={shop.id}>{shop.name}</option>
));

// ✅ نعم — تجاهلوا القائمة
// available_shops دائماً []
```

`shop_variants[].shop_id` رقم داخلي — **لا** تعرضوه كاسم.

---

## 3) شكل الـ API

```http
GET /api/user/products/{id}
Accept-Language: ar
```

```json
{
  "available_shops": [],
  "attributes_map": [
    {
      "attribute": "لون",
      "type": "color",
      "values": ["أزرق", "أسود"]
    }
  ],
  "shop_variants": [
    {
      "id": 44,
      "variant_id": 91,
      "shop_id": 1,
      "sku": "JEANS-BLUE-S",
      "attributes": [
        { "attribute": "لون", "value": "أزرق", "type": "color", "hex": "#0000FF" }
      ]
    }
  ]
}
```

| حقل | بعد التحديث | الواجهة |
|-----|-------------|---------|
| `available_shops` | دائماً `[]` | **لا UI** |
| `shop_variants[].shop_id` | رقم أو `null` | لا تعرضوه |
| `shop_variants[].id` | هوية السلة | أضيفوا للسلة فقط |
| `shop_variants[].attributes` | لون / مقاس | اعرضوا الـ picker |

---

## 4) السلة

```js
{
  shop_product_variant_id: selected.id, // shop_variants[].id
  quantity: 1,
}
```

إذا `id == null` أو `shop_id == null` → اعرضوا السعر ولا تفعّلوا السلة.

---

## 5) غلط vs صح

**غلط**

```js
if (product.available_shops?.length) showBranchSelect(product.available_shops);
```

**صح**

```js
const variants = product.shop_variants ?? [];
const selected = findVariant(variants, { color, size });
// لا Branch — فقط اللون / المقاس
```

---

## 6) Checklist

- [ ] احذفوا دروب داون Branch / اسم المتجر من صفحة المنتج
- [ ] لا تبنوا UI على `available_shops`
- [ ] الألوان / المقاسات من `attributes_map` و `shop_variants` تبقى
- [ ] السلة تبقى بـ `shop_product_variant_id` = `shop_variants[].id`
- [ ] وقت التسليم (`delivery_time`) يبقى إن كان موجود
