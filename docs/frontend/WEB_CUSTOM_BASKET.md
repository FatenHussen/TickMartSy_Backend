# ويب — فئات الجدولة + السلة المخصصة + أقسام الصفحة

> **الجمهور:** فريق الموقع فقط  
> **تاريخ:** 5 أيلول 2026  
> **Base:** `/api/user` + `Accept-Language: ar|en`  
> **Auth:** كروت الفئات عامة. التخصيص والتأكيد: Bearer user.

الأسعار من الـ API فقط: `*_formatted` أو `*_currencies`. لا تحسبوا الخصم على الفرونت.

---

## لا تخلطوا بين النوعين

| | فئات الجدولة | سلل أدمن جاهزة |
|--|-------------|----------------|
| شو هي | كرت أسبوعي / شهري — المستخدم يملأ المنتجات | سلة جاهزة من الأدمن |
| `content_type` | `schedule` | `schedule-basket` |
| `api_method` | `schedules` | `schedule-basket` |
| `display_type_id` | **11** | **5** |
| الضغط على الكرت | `/schedules/{id}` تخصيص | تفاصيل سلة جاهزة |

المسار الأساسي للموقع: **فئات الجدولة**. السلل الجاهزة اختيارية إذا ظهر قسم `display_type_id = 5`.

---

## 1) كروت الفئات على الصفحة الرئيسية

من أقسام الصفحة:

```http
GET /api/user/sections?page_slug=home
Accept-Language: ar
```

إذا `content_type === "schedule"` أو `display_type_id === 11`:

- `layout`: `slider` | `list` | `grid` — تخطيط القسم
- `variant`: يُفضَّل `vertical` — كرت عمودي، **صورة دائرية** جوّاه
- `items[]` = كروت الفئات (نفس عقد `GET /schedules`)

ضغط الكرت → `/schedules/{items[].id}`

إذا القسم `type: "manual"` العنصر ملفوف: `items[].item` فيه حقول الكرت. إذا `type: "api"` الحقول على `items[]` مباشرة.

قائمة مستقلة (كل الفئات المفعّلة، بلا ترقيم):

```http
GET /api/user/schedules
GET /api/user/schedules/{id}
```

`data.items` للقائمة.

| حقل | استخدام |
|-----|---------|
| `id` | مسار `/schedules/{id}` |
| `name` | اسم الفئة |
| `description` | وصف مصغّر |
| `image` | الصورة الأساسية |
| `images[]` | تناوب / GIF |
| `interval_days` | «كل 7 أيام» إن لزم |
| `discount_type` `discount_value` | شارة خصم اختيارية |
| `top_badges` `bottom_badges` | `name` + `image` + `color` |

بادج: `id`, `name`, `image`, `color`, `position`.

---

## 2) صفحة التخصيص `/schedules/{id}`

**Auth مطلوب** لمسودة السلة. الزائر غير المسجّل: اعرضوا الهيدر من `GET /schedules/{id}` واطلبوا تسجيل الدخول قبل الإضافة.

```http
GET /api/user/schedules/{id}/custom-basket
Authorization: Bearer {token}
```

ينشئ مسودة فاضية إذا ما في. كل `schedule_id` مسودة مستقلة (أسبوعي وشهري مع بعض).

هيدر الصفحة من `schedule`:

- صورة: `schedule.image`
- اسم: `schedule.name`
- زر **عرض السلة** (يفتح `items` + `summary`)

`is_draft: true` حتى التأكيد.

تحت الهيدر: تصفّح موجود:

| الغرض | Endpoint |
|--------|----------|
| فئات | `GET /api/user/categories` |
| منتجات / بحث | `GET /api/user/products?category_id=&search=&brand_id=` |
| شركات | `GET /api/user/brands` |
| تفاصيل منتج | `GET /api/user/products/{id}` |

### ليش `shop_product_variant_id`؟

البيع: منتج × متغيّر × **متجر**.  
من تفاصيل المنتج خذوا `shop_variants[].id` (مو `variant_id`). لازم `shop_id != null`.

---

## 3) إضافة / كمية / حذف

فور اختيار المتغيّر والكمية:

```http
POST /api/user/schedules/{id}/custom-basket/items
Authorization: Bearer {token}
Content-Type: application/json

{ "shop_product_variant_id": 25, "quantity": 2 }
```

نفس `shop_product_variant_id` يحدّث الكمية (ما يكرّر السطر). الرد = المسودة كاملة (`items` + `summary`).

```http
PUT /api/user/schedules/{id}/custom-basket/items/{itemId}
{ "quantity": 3 }

DELETE /api/user/schedules/{id}/custom-basket/items/{itemId}
```

`itemId` = `items[].id` من المسودة، **مو** معرّف المنتج ولا `shop_product_variant_id`.

---

## 4) عرض السلة

نفس `GET /api/user/schedules/{id}/custom-basket`. لا تخزّنوا الإجمالي محلياً.

### صف الصنف

| العمود | الحقل |
|--------|--------|
| صورة | `product.image` |
| اسم | `product.name` |
| متغيّر | `variant.name` (مصفوفة نصوص) |
| كمية | `quantity` |
| وحدة | `unit` |
| سعر الوحدة | `original_price_formatted` |
| سطر | `line_total_formatted` |
| متجر | `shop.name` |
| شركة | `product.brand.name` |

`items[].id` للحذف/تعديل الكمية. `items[].shop_product_variant_id` للطلب لاحقاً.

### سطر الإجمالي = `summary`

| العرض | الحقل |
|--------|--------|
| عدد الأصناف | `summary.items_count` |
| الكمية | `summary.total_quantity` |
| السعر | `summary.original_price_formatted` |
| الخصم | `summary.discount_value` + `discount_type` |
| وفّرت | `summary.savings_formatted` |
| النهائي | `summary.final_price_formatted` |

الخصم من **فئة الجدولة** على مجموع السلة. لا تعيدوا حسابه.

---

## 5) تأكيد — نعم / لا

بعد عرض السلة، السؤال:

> بدك تطلب هالسلة كل **{schedule.name}** ونبعت تذكير قبل الموعد؟

```http
POST /api/user/schedules/{id}/custom-basket/confirm
```

### نعم — تكرار حسب الفئة

```json
{ "confirm_schedule": true, "start_date": "2026-09-08" }
```

`start_date` ≥ اليوم. تُحفظ في طلباتي المجدولة + تذكير.

الرد: `scheduled: true` · `next_run_date` · `cart_items` لأول توصيل.

أضيفوا `cart_items` لسلة الموقع / `POST /orders`.

### لا — مرة واحدة

```json
{ "confirm_schedule": false }
```

المسودة تُحذف. الرد: `scheduled: false` · `cart_items`.

أضيفوا `cart_items` للسلة العادية مرة واحدة.

```json
"cart_items": [
  { "shop_product_variant_id": 25, "quantity": 2 }
]
```

القائمة بعد نعم: `GET /api/user/scheduled-baskets` أو `GET /api/user/my-baskets`.

سلة فاضية عند التأكيد → 422: أضيفوا منتج واحد على الأقل.

---

## 6) سلل أدمن جاهزة (اختياري)

إذا وصل قسم `display_type_id === 5` أو `content_type === "schedule-basket"` / `api_method === "schedule-basket"`:

- هذي سلل جاهزة من الأدمن، مو كروت التخصيص
- فلتر اختياري على القسم: `schedule_id` يربطها بفئة جدولة
- لا تفتحوا عليها `/schedules/{id}/custom-basket`

---

## 7) Checklist

- [ ] قسم `display_type_id=11` / `content_type=schedule` → كروت فئات (صورة دائرية)
- [ ] `display_type_id=5` → سلل جاهزة، مسار مختلف
- [ ] ضغط الكرت → `/schedules/{id}`
- [ ] صفحة التخصيص: هيدر من `schedule` + فئات + بحث + براند
- [ ] إضافة بـ `shop_variants[].id` فور اختيار الكمية
- [ ] عرض السلة من `items` + `summary` (وفّرت من الـ API)
- [ ] نعم يحتاج `start_date` · لا يحذف المسودة
- [ ] `cart_items` → سلة / `POST /orders`
- [ ] أسبوعي وشهري مسودتان منفصلتان
- [ ] أسعار `*_formatted` فقط — لا خصم محلي
