# Dashboard Frontend — حفظ المتغيّرات وربط الفروع (مهم)

## الخلفية

كان في bug بالـ Backend: عند إنشاء/تعديل منتج، مصفوفتَي `variants` و `shop_variants`
كانت **تُتجاهل بالكامل** (الكود كان معلّق). النتيجة:

- المنتج ينحفظ بنجاح ويرجع `200`.
- بس بدون أي `product_variant` وبدون أي ربط مع فرع.
- وصفحة المنتج بالويب كانت تنكسر (`shop_variants: []`).

الـ Backend انصلح، وصار `variants` و `shop_variants` **يُحفظون فعلياً**.
هذا يعني أي payload ناقص أو غلط من الداشبورد **صار له تأثير مباشر** على البيانات.

> كل المسارات تحت `/api/admin` وتتطلب Admin token.

---

## 1) شنو تغيّر

| | قبل | بعد |
|---|---|---|
| `variants` بالـ payload | تُتجاهل | **تُحفظ** (إنشاء/تحديث/حذف) |
| `shop_variants` بالـ payload | تُتجاهل | **تُحفظ** كربط فرع |
| منتج بدون `variants` | ينحفظ بلا متغيّرات | يُنشأ **متغيّر افتراضي** تلقائياً |
| صور المتغيّر | ما تنحفظ | تُحفظ بـ collection `variant` |

**ما تغيّر شي بالـ validation ولا بأسماء الحقول.** الـ contract نفسه الموجود بـ
`FRONTEND_DASHBOARD_VARIANT_PRICE_QUANTITY.md`. الفرق إنه صار يُطبَّق فعلاً.

---

## 2) المتغيّر الافتراضي التلقائي

إذا ما أرسلت `variants` نهائياً، الـ Backend ينشئ متغيّر واحد من بيانات المنتج:

```text
sku      = product.sku
model    = product.model
barcode  = product.barcode
price    = product.price   (أو 0)
quantity = product.quantity (أو 0)
attributes_values_ids = []
is_active = true
```

هذا يمنع حالة "منتج بلا متغيّر"، بس **ما ينشئ ربط فرع**.
فالمنتج يظهر بالويب ولا ينفع يُضاف للسلة لأن `shop_id = null`.

**الخلاصة:** لازم ترسل `shop_variants` مع `shop_id` حتى يصير المنتج قابل للشراء.

---

## 3) الـ payload المطلوب

`POST /api/admin/products` — `multipart/form-data`

```text
name[ar]=بنطال جينز
name[en]=Street jeans
category_id=20
price=20
quantity=0

variants[0][sku]=JEANS-RED
variants[0][price]=25
variants[0][quantity]=12
variants[0][is_active]=1
variants[0][attributes_values_ids][0]=6

shop_variants[0][shop_id]=1
shop_variants[0][variant_index]=0
shop_variants[0][cost_price]=10000
```

قواعد ثابتة:

- `variant_index` = ترتيب المتغيّر في مصفوفة `variants` (0، 1، …) — **مو `variant_id`**.
- `shop_variants.*.price` و `shop_variants.*.quantity` **ما تُرسل** (السعر والكمية على المتغيّر).
- `shop_variants.*.cost_price` = تكلفة شراء للفرع، اختيارية.
- `variants.*.price` إذا ما أُرسلت → تُأخذ من `product.price`.
- `products.quantity` يُحسب تلقائياً كمجموع كميات المتغيّرات (rollup) — لا تعتمد على القيمة اللي أرسلتها.

---

## 4) سلوك التعديل — انتبهوا هنا

`PUT /api/admin/products/{id}`

### 4.1 `variants` بديل كامل (replace)

المنطق:

| الحالة | النتيجة |
|---|---|
| صف فيه `variants[i][id]` موجود | **تحديث** المتغيّر نفسه (نفس الـ id) |
| صف بدون `id` | **إنشاء** متغيّر جديد |
| متغيّر موجود بالـ DB وما أُرسل بالطلب | **soft delete** |

يعني إذا أرسلت `variants` بدون `id`، المتغيّرات القديمة تُحذف وتُنشأ غيرها **بـ IDs جديدة**،
وهذا يكسر أي ربط قديم (سلات، وصفات…).

```js
// خطأ — يحذف كل المتغيّرات القديمة وينشئ جديدة
variants: rows.map((r) => ({ sku: r.sku, price: r.price, quantity: r.quantity }))

// صح — حافظ على id للصفوف الموجودة
variants: rows.map((r) => ({
  ...(r.id ? { id: r.id } : {}),
  sku: r.sku,
  price: r.price,
  quantity: r.quantity,
  attributes_values_ids: r.attributeValueIds ?? [],
  is_active: r.isActive ? 1 : 0,
}))
```

**قاعدة الفورم:** عند التحميل، خزّن `variant.id` من `GET /api/admin/products/{id}`
داخل حالة كل صف، وأرجعه كما هو عند الحفظ.

### 4.2 `shop_variants` كذلك replace

إذا أرسلت `shop_variants`، روابط الفروع تُحذف أولاً ثم تُعاد كتابتها من الطلب.

| ما تُرسله | النتيجة |
|---|---|
| `shop_variants` بقائمة كاملة | الروابط تُستبدل بالمُرسل |
| لا تُرسل `shop_variants` نهائياً | الروابط الحالية **تبقى كما هي** |
| `shop_variants` = مصفوفة فاضية | **تُحذف كل روابط الفروع** |

فترسل دائماً **القائمة النهائية الكاملة**، مو المضاف الجديد فقط.
ولحذف فرع: أرسل القائمة بدونه — لا ترسل مصفوفة فاضية إلا إذا فعلاً تريد فصل كل الفروع.

⚠️ لا ترسل `shop_variants: []` كقيمة افتراضية عند فتح الفورم أو عند تعديل حقل غير متعلق
بالفروع — هذا يفصل المنتج عن كل فروعه ويرجعه غير قابل للشراء.

بنفس المنطق: `variants: []` تحذف كل المتغيّرات، والـ Backend ينشئ بدلها متغيّر افتراضي واحد
من سعر/كمية المنتج (شوف القسم 2).

### 4.3 لا ترسل `variants` أبداً إذا الفورم ما فيه تاب متغيّرات

لأن إرسال `variants` ناقصة يحذف الموجود. إذا الشاشة تعدّل بيانات المنتج فقط
(اسم/وصف/صور/SEO) → **لا تضمّن `variants` ولا `shop_variants`** بالـ payload.

---

## 5) صور المتغيّر

```text
variants[0][images][0]=<file>
variants[0][existing_images_ids][0]=373
```

- `images` = ملفات جديدة تُرفع على collection `variant`.
- `existing_images_ids` = الصور اللي تريد **الاحتفاظ** بها. أي صورة موجودة وغير مذكورة **تُحذف**.
- إذا أرسلت صف فيه `id` بدون `existing_images_ids` → كل صور المتغيّر تُحذف. أرسل الـ IDs الحالية حتى لو ما تغيّرت.

---

## 6) الحقول المقبولة (whitelist)

الـ Backend يقبل فقط هذي الحقول داخل كل صف `variants`:

```text
id, name, sku, model, barcode, price, quantity,
attributes_values_ids, is_trend, is_active,
images, existing_images_ids
```

أي حقل ثاني (مثل `shops`, `attributes`, `price_currencies`, `price_after_discount`)
**يُتجاهل**. لذلك لا ترجع كائن المتغيّر كما جاء من `GET` مباشرة — نظّفه قبل الإرسال:

```js
function toVariantPayload(row) {
  return {
    ...(row.id ? { id: row.id } : {}),
    name: row.name,                  // { ar, en }
    sku: row.sku,
    model: row.model,
    barcode: row.barcode,
    price: row.price,
    quantity: row.quantity,
    attributes_values_ids: row.attributeValueIds ?? [],
    is_trend: row.isTrend ? 1 : 0,
    is_active: row.isActive ? 1 : 0,
    existing_images_ids: row.existingImageIds ?? [],
  };
}
```

---

## 7) التحقق بعد الحفظ

`GET /api/admin/products/{id}` لازم يرجع:

```json
{
  "variants": [
    {
      "id": 44,
      "sku": "JEANS-RED",
      "price": 25,
      "quantity": 12,
      "shops": [
        { "id": 51, "shop_id": 1, "shop_name": "الفرع الرئيسي", "cost_price": 10000 }
      ]
    }
  ]
}
```

إذا `variants` فاضية أو `shops` فاضية → المنتج **مو قابل للشراء** بالويب.
اعرض تنبيه بالداشبورد بهذي الحالة (مثلاً badge "غير مرتبط بفرع" على صف المنتج).

---

## 8) المنتجات القديمة المتأثرة

كل منتج انحفظ خلال فترة الـ bug صار عنده `variants: []` أو بدون روابط فروع
(مثل المنتج 23 و 24 على السيرفر). لازم:

1. فتح المنتج بالداشبورد.
2. تعبئة تاب المتغيّرات (سعر + كمية).
3. ربط فرع واحد على الأقل في قسم الفروع.
4. الحفظ.

بعدها صفحة المنتج بالويب تعمل بشكل كامل مع إمكانية الشراء.

---

## 9) Checklist

- [ ] `variants` تُرسل فقط من الشاشة اللي فيها تاب متغيّرات
- [ ] كل صف موجود يُرسل مع `id` (ما يُنشأ متغيّر جديد بالغلط)
- [ ] `shop_variants[].variant_index` يشير لترتيب الصف بـ `variants`
- [ ] ما يُرسل `price` ولا `quantity` داخل `shop_variants`
- [ ] `existing_images_ids` يُرسل حتى لو الصور ما تغيّرت
- [ ] الحقول تُنظّف قبل الإرسال (لا تُرسل `shops` / `price_currencies`)
- [ ] ما يُرسل `variants: []` ولا `shop_variants: []` كقيمة افتراضية
- [ ] بعد الحفظ يتم التحقق من `variants[].shops[]` غير فاضية
- [ ] تنبيه بالجدول للمنتجات غير المرتبطة بفرع
