# ويب — السلة المخصصة

> **الجمهور:** فريق الويب  
> **تاريخ:** 5 أيلول 2026  
> **Base:** `/api/user` + `Accept-Language: ar|en`  
> **Auth:** كروت الفئات عامة. التخصيص والتأكيد يحتاجون Bearer user.

الأسعار: اعرضوا `*_formatted` أو `*_currencies`. لا تحسبوا الخصم محلياً.

---

## الفكرة

الأدمن يعرّف فئات (أسبوعي، شهري…). المستخدم يفتح فئة ويختار هو المنتجات والكميات والشركات. يقدر يخصّص أكثر من فئة (أسبوع + شهر). الخصم من الفئة على **السلة كاملة**.

---

## 1) كروت الفئات

كرت عمودي، صورة دائرية جوّاه. ضغط الكرت → صفحة التخصيص.

نفس الكروت تظهر داخل أقسام الصفحة عندما `content_type` = `schedule` أو `display_type_id` = `11` (`GET /api/user/sections?page_slug=home`). `items[]` نفس حقول الكرت أعلاه. الضغط على item → `/schedules/{id}`.

لا تخلطوا مع `display_type_id` = `5` (`schedule-basket`) — تلك سلل أدمن جاهزة.

```http
GET /api/user/schedules
GET /api/user/schedules/{id}
```

الفعّال فقط. بلا ترقيم (`data.items`).

| حقل | استخدام |
|-----|---------|
| `id` | مسار الصفحة `/schedules/{id}` |
| `name` | اسم الفئة |
| `description` | وصف مصغّر |
| `image` | الصورة الأساسية |
| `images[]` | تناوب صورتين / GIF |
| `interval_days` | للنص «كل 7 أيام» إن لزم |
| `discount_type` `discount_value` | شارة خصم اختيارية |
| `top_badges` `bottom_badges` | بادج علوي / سفلي — `name` + `image` + `color` |

---

## 2) صفحة التخصيص

هيدر: `schedule.image` + `schedule.name` + زر **عرض السلة**.

تحت: فئات رئيسية + بحث + (اختياري) كروت شركات مربعة.

| الغرض | Endpoint |
|--------|----------|
| هيدر + مسودة السلة | `GET /api/user/schedules/{id}/custom-basket` **Auth** |
| فئات | `GET /api/user/categories` |
| منتجات / بحث | `GET /api/user/products?category_id=&search=&brand_id=` |
| شركات | `GET /api/user/brands` |
| تفاصيل منتج | `GET /api/user/products/{id}` — أضيفوا `shop_variants[].id` |

`GET custom-basket` ينشئ مسودة فاضية إذا ما في. كل فئة (`id`) مسودة مستقلة.

---

## 3) إضافة للمنتجات

```http
POST /api/user/schedules/{id}/custom-basket/items
Authorization: Bearer {token}
Content-Type: application/json

{ "shop_product_variant_id": 25, "quantity": 2 }
```

نفس `shop_product_variant_id` يحدّث الكمية (ما يكرّر السطر).

```http
PUT /api/user/schedules/{id}/custom-basket/items/{itemId}
{ "quantity": 3 }

DELETE /api/user/schedules/{id}/custom-basket/items/{itemId}
```

`itemId` = `items[].id` من المسودة، **مو** معرّف المنتج.

---

## 4) عرض السلة

نفس `GET /api/user/schedules/{id}/custom-basket`.

### صف الصنف

| عمود | حقل |
|------|-----|
| صورة | `product.image` |
| اسم | `product.name` |
| متغيّر | `variant.name` (مصفوفة نصوص) |
| كمية | `quantity` |
| وحدة | `unit` |
| سعر | `original_price_formatted` أو `line_total_formatted` |
| شركة | `shop.name` / `product.brand.name` |

### سطر الإجمالي = `summary`

| العرض | حقل |
|--------|-----|
| عدد الأصناف | `summary.items_count` |
| الكمية | `summary.total_quantity` |
| السعر | `summary.original_price_formatted` |
| الخصم | `summary.discount_value` + `discount_type` |
| وفّرت | `summary.savings_formatted` |
| النهائي | `summary.final_price_formatted` |

لا تعيدوا حساب خصم الفئة على الفرونت.

---

## 5) تأكيد الطلب — نعم / لا

بعد عرض السلة، زر تأكيد يفتح السؤال:

> بدك تطلب هالسلة كل **{schedule.name}** ونبعت تذكير قبل الموعد؟

```http
POST /api/user/schedules/{id}/custom-basket/confirm
```

**نعم**

```json
{ "confirm_schedule": true, "start_date": "2026-09-08" }
```

`start_date` ≥ اليوم. تُحفظ في طلباتي المجدولة + تذكير. أضيفوا `cart_items` لأول توصيل (`POST /orders`).

**لا**

```json
{ "confirm_schedule": false }
```

مسودة تُحذف. أضيفوا `cart_items` للسلة العادية مرة واحدة.

```json
"cart_items": [
  { "shop_product_variant_id": 25, "quantity": 2 }
]
```

القائمة المجدولة بعد نعم: `GET /api/user/scheduled-baskets` أو `GET /api/user/my-baskets`.

---

## 6) Checklist

- [ ] كروت من `/schedules` — صورة دائرية + وصف + بادجز
- [ ] صفحة `{id}`: هيدر + فئات + بحث + براند
- [ ] إضافة بـ `shop_product_variant_id` فور اختيار الكمية
- [ ] عرض السلة من `items` + `summary` (وفّرت من الـ API)
- [ ] نعم/لا على `confirm` — نعم يحتاج `start_date`
- [ ] `cart_items` → سلة/طلب
- [ ] أسبوعي وشهري مسودتان منفصلتان
- [ ] أسعار `*_formatted` فقط
