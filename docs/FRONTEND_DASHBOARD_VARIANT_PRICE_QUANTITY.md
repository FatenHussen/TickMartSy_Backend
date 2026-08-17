# Dashboard Frontend — سعر وكمية المتغير

تغيير كاسر للداشبورد: **السعر والكمية صاروا على المتغير (`product_variants`)**، مو على ربط المحل (`shop_product_variants`).

كل محل كان يسعّر ويخزّن لحاله. هلق سعر البيع والمخزون موحّدين لكل المتغير، بكل الفروع.

> كل المسارات تحت `/api/admin` وتتطلب Admin token.

---

## 1) شنو تغيّر بالواجهة

### تاب المتغيرات (مكان التحكم)

لما تضيف / تعدّل متغير، الحقول على **صف المتغير نفسه**:

| الحقل | النوع | ملاحظات |
|---|---|---|
| `variants.*.price` | `number` (float, min 0) | سعر البيع الوحيد لهذا الـ SKU |
| `variants.*.quantity` | `integer` (min 0) | المخزون الوحيد لهذا الـ SKU |

**ما في** حقل سعر أو كمية جوّا قسم المحلات.

### قسم توفر الفروع

المحلات صارت ربط توفر فقط:

| الحقل | الحالة |
|---|---|
| `shop_variants.*.shop_id` | مطلوب |
| `shop_variants.*.variant_index` | مطلوب (يربط الصف بالمتغير) |
| `shop_variants.*.cost_price` | اختياري — تكلفة شراء للمحل، **مو سعر بيع** |
| `shop_variants.*.price` | **محذوف — لا ترسله** |
| `shop_variants.*.quantity` | **محذوف — لا ترسله** |

`products.quantity` بالمنتج الأب مجموع كميات المتغيرات (rollup). مصدر الحقيقة هو `variants[].quantity`.

---

## 2) Breaking changes — وين تقرأ الحقول

| قبل | بعد |
|---|---|
| `product.variants[].shops[].price` | `product.variants[].price` |
| `product.variants[].shops[].quantity` | `product.variants[].quantity` |
| `product.variants[].shops[].discount` | `product.variants[].discount` |
| `product.variants[].shops[].price_after_discount` | `product.variants[].price_after_discount` |
| `shop_variant.price` / `shop_variant.quantity` | `variant.price` / `variant.quantity` |

احذف من الفورم أي input سعر/كمية مربوط بـ shop row.

إذا عندك كود مثل:

```js
shop.price
shop.quantity
variant.shops[i].price
variant.shops[i].quantity
shop_variant.price
```

بدّله إلى:

```js
variant.price
variant.quantity
```

---

## 3) Endpoints المتأثرة

- `POST /api/admin/products`
- `PUT /api/admin/products/{id}`
- `GET /api/admin/products/{id}`
- `GET /api/admin/product-variants`
- `GET /api/admin/product-variants/{id}`
- `PUT /api/admin/product-variants/{id}`
- `GET /api/admin/shop-product-variants`
- `GET /api/admin/shop-product-variants/{id}`
- `PUT /api/admin/shop-product-variants/{id}`
- سلال الأدمن / السلال المجدولة: `shop_variant.quantity` صارت كمية المتغير (نفس المفتاح، مصدر جديد)

---

## 4) إنشاء / تعديل منتج

### Create — `POST /api/admin/products`

```http
POST /api/admin/products
Content-Type: multipart/form-data
```

```text
variants[0][name][ar]=أحمر كبير
variants[0][name][en]=Red Large
variants[0][sku]=VAR-1-RED
variants[0][price]=14500
variants[0][quantity]=80
variants[0][is_active]=1
variants[0][attributes_values_ids][]=12

variants[1][name][ar]=أزرق كبير
variants[1][name][en]=Blue Large
variants[1][sku]=VAR-1-BLUE
variants[1][price]=15000
variants[1][quantity]=40
variants[1][is_active]=1
variants[1][attributes_values_ids][]=13

shop_variants[0][shop_id]=1
shop_variants[0][variant_index]=0
shop_variants[0][cost_price]=10000

shop_variants[1][shop_id]=2
shop_variants[1][variant_index]=0
shop_variants[1][cost_price]=10200

shop_variants[2][shop_id]=1
shop_variants[2][variant_index]=1
shop_variants[2][cost_price]=11000
```

`variant_index` = رقم المتغير بنفس ترتيب مصفوفة `variants` (0، 1، …).

### Update — `PUT /api/admin/products/{id}`

نفس الشكل، مع `variants[0][id]` للمتغيرات الموجودة.

```text
variants[0][id]=15
variants[0][price]=14500
variants[0][quantity]=75
```

### قواعد الفاليديشن

**المتغير**

- `variants.*.price` → `nullable|numeric|min:0`
- `variants.*.quantity` → `nullable|integer|min:0`

**المحل**

- `shop_variants.*.shop_id` → `required|exists:shops,id`
- `shop_variants.*.variant_index` → `required|integer|min:0`
- `shop_variants.*.cost_price` → `nullable|numeric|min:0`
- **لا ترسل** `shop_variants.*.price` ولا `shop_variants.*.quantity`

---

## 5) تعديل متغير لحاله

`PUT /api/admin/product-variants/{id}`

```json
{
  "price": 14500,
  "quantity": 75,
  "is_active": true
}
```

| الحقل | النوع |
|---|---|
| `price` | `nullable|numeric|min:0` |
| `quantity` | `nullable|integer|min:0` |

فلاتر القائمة `GET /api/admin/product-variants`:

- `price_min` / `price_max`
- `quantity_min` / `quantity_max`  ← جديد
- `category_id`, `shop_id`, `product_id`, `search`

---

## 6) ربط المحل (shop-product-variant)

`PUT /api/admin/shop-product-variants/{id}`

يقدر يعدّل **تكلفة الشراء فقط**:

```json
{
  "cost_price": 10000
}
```

لا يوجد `price` ولا `quantity` بهالـ endpoint. إرسالهم ما بيحدّث شي (مو fillable).

---

## 7) شكل الـ Response

### `GET /api/admin/products/{id}`

```json
{
  "id": 10,
  "price": 14000,
  "quantity": 120,
  "variants": [
    {
      "id": 15,
      "name": { "ar": "أحمر كبير", "en": "Red Large" },
      "sku": "VAR-1-RED",
      "price": 14500,
      "price_currencies": {},
      "discount": 0,
      "price_after_discount": 14500,
      "quantity": 80,
      "is_trend": false,
      "is_active": true,
      "shops": [
        {
          "id": 44,
          "shop_id": 1,
          "shop_name": "فرع الرياض",
          "cost_price": 10000
        }
      ]
    }
  ]
}
```

`product.quantity` = 80 + 40 مثلاً (مجموع المتغيرات).  
`shops[]` **ما فيها** `price` ولا `quantity`.

### `GET /api/admin/product-variants/{id}`

```json
{
  "id": 15,
  "price": 14500,
  "quantity": 80,
  "discount": 0,
  "price_after_discount": 14500,
  "shop_variants": [
    {
      "id": 44,
      "shop": { "id": 1, "name": "فرع الرياض" },
      "cost_price": 10000
    }
  ]
}
```

### `GET /api/admin/shop-product-variants/{id}`

```json
{
  "id": 44,
  "product_variant_id": 15,
  "variant": {
    "id": 15,
    "price": 14500,
    "quantity": 80,
    "discount": 0,
    "price_after_discount": 14500
  },
  "cost_price": 10000,
  "quantity": 80
}
```

المفتاح العلوي `quantity` هون **للعرض فقط** (نفس قيمة `variant.quantity`) حتى ما ينكسر سيلكت قديم. **ما تعدّله من صف المحل.** التعديل من المتغير.

قائمة الـ picker `GET /api/admin/shop-product-variants`:

```json
{
  "id": 44,
  "label": "منتج (لون: أحمر) - فرع الرياض",
  "variant": {
    "id": 15,
    "price": 14500,
    "quantity": 80,
    "discount": 0,
    "price_after_discount": 14500
  }
}
```

---

## 8) UI Checklist

### فورم المنتج — تاب المتغيرات

- [ ] Input سعر على كل variant
- [ ] Input كمية (integer) على كل variant
- [ ] Repeater المحلات: اختيار `shop_id` فقط (+ `cost_price` إذا ظاهر)
- [ ] احذف inputs السعر/الكمية من صف المحل
- [ ] نفس السعر والكمية يظهران بكل الفروع — ما في تسعير لكل محل

### صفحات المتغيرات / الجرد

- [ ] جدول المتغيرات يعرض `price` و `quantity` من الـ variant
- [ ] فلتر كمية: `quantity_min` / `quantity_max`
- [ ] جرد المورد: حقل كمية **لكل متغير**، مو لكل محل×متغير

### سلال / وصفات / طلبات (عرض)

- [ ] سعر البيع من `variant.price` (أو `shop_variant.productVariant` عبر الـ API)
- [ ] كمية المخزون من `variant.quantity`
- [ ] `shop_product_variant_id` ما زال معرّف التنفيذ (أي فرع)، بس مو مصدر السعر/المخزون

---

## 9) ملاحظات

- سعر المنتج الأب `products.price` بقي للعرض/الفلاتر العامة. سعر البيع الفعلي = `variants[].price`.
- الخصم ما زال على المنتج، و`price_after_discount` على المتغير ينحسب من سعر المتغير + خصم المنتج.
- `cost_price` على المحل تكلفة شراء الفرع، لا تعرضه كسعر بيع للزبون.
- المخزون واحد: بيع من أي فرع ينقص نفس `variants[].quantity`.
