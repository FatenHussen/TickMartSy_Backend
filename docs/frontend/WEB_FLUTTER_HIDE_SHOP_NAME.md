# ويب + Flutter — لا تعرضوا اسم المتجر على صفحة المنتج

> **أرسلوا هذا الملف لفريقي الويب و Flutter.**  
> **آخر تحديث:** 20 أيلول 2026  
> Base: `GET /api/user/products/{id}`  
> **الباك جاهز بعد `git pull`**

على صفحة المنتج كان يظهر دروب داون **Branch / فرعة المنصة** لأن المنتج مربوط بمتجر.  
**هذا ملغى.** ما في اختيار متجر ولا اسم متجر على الصفحة.

الألوان والمقاسات تبقى. وقت التسليم يبقى.

---

## المطلوب في الواجهة

احذفوا بالكامل من صفحة المنتج:

- دروب داون **Branch** / **الفرع** / **المتجر**
- أي نص «فرعة المنصة» أو اسم متجر فوق الألوان
- أي UI مبني على `available_shops`

```js
// ❌ لا
product.available_shops.map(shop => <option>{shop.name}</option>)

// ✅ نعم — تجاهلوا القائمة
// available_shops دائماً []
```

`shop_variants[].shop_id` و `shop_variants[].id` للسلة فقط — **لا** تُعرض كاسم متجر.

---

## الـ API

```json
{
  "available_shops": [],
  "shop_variants": [
    { "id": 44, "shop_id": 1, "attributes": [] }
  ]
}
```

| حقل | بعد التحديث | الواجهة |
|-----|-------------|---------|
| `available_shops` | دائماً `[]` | **لا UI** |
| `shop_variants[].shop_id` | رقم داخلي | لا تعرضوه |
| `shop_variants[].id` | هوية السلة | أضيفوا للسلة فقط |

---

## Checklist

- [ ] احذفوا دروب داون Branch / اسم المتجر من صفحة المنتج
- [ ] لا تبنوا UI على `available_shops`
- [ ] الألوان / المقاسات من `attributes_map` و `shop_variants` تبقى
- [ ] السلة تبقى بـ `shop_product_variant_id` = `shop_variants[].id`
