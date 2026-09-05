# السلة المخصصة — ملخص العقد

> 5 أيلول 2026 — العقد بعد التنفيذ

أرسلوا لكل فريق ملفه:

| الفريق | الملف |
|--------|--------|
| داشبورد | [`DASHBOARD_CUSTOM_BASKET.md`](./DASHBOARD_CUSTOM_BASKET.md) |
| ويب | [`WEB_CUSTOM_BASKET.md`](./WEB_CUSTOM_BASKET.md) |
| Flutter | [`FLUTTER_CUSTOM_BASKET.md`](./FLUTTER_CUSTOM_BASKET.md) |

## أدمن

`POST /api/admin/schedules` (multipart)

| حقل | ملاحظة |
|-----|--------|
| `name[ar\|en]` | اسم الفئة (أسبوعي، شهري…) |
| `description[ar\|en]` | وصف الكرت |
| `interval_days` | كل كم يوم |
| `discount_type` / `discount_value` | خصم على **السلة كاملة** |
| `is_active` | تفعيل الكرت |
| `image` | صورة الكرت (GIF مسموح) |
| `images[]` | صورة ثانية للتناوب |
| `badges[][id]` + `badges[][position]` | `top` أو `bottom` |

الخصم على الفئة ينطبق على تخصيص المستخدم. سلل الأدمن الجاهزة (`/scheduled-baskets` + `schedule_id`) مسار إضافي اختياري.

## مستخدم

**كروت الفئات**

```http
GET /api/user/schedules
GET /api/user/schedules/{id}
```

يرجع: `name`, `description`, `image`, `images[]`, `interval_days`, `discount_*`, `top_badges`, `bottom_badges`.

الضغط على الكرت → صفحة التخصيص (`{id}` = مثلاً أسبوعي).

**هيدر الصفحة + مسودة السلة** (Auth)

```http
GET /api/user/schedules/{id}/custom-basket
```

ينشئ مسودة إذا ما في. `schedule` للهيدر + `items` + `summary`.

**المنتجات / البحث / الشركات** — APIs موجودة:

- فئات: `GET /api/user/categories`
- بحث ومنتجات: `GET /api/user/products?category_id=&search=&brand_id=`
- شركات: `GET /api/user/brands`

**إضافة / تعديل / حذف**

```http
POST   /api/user/schedules/{id}/custom-basket/items
       { "shop_product_variant_id": 25, "quantity": 2 }

PUT    /api/user/schedules/{id}/custom-basket/items/{itemId}
       { "quantity": 3 }

DELETE /api/user/schedules/{id}/custom-basket/items/{itemId}
```

نفس الـ variant يتحدّث الكمية بدل تكرار السطر.

**عرض السلة** = نفس `GET custom-basket`

كل صنف: صورة، اسم، متغيّر، كمية، وحدة، سعر، متجر.  
`summary`: عدد الأصناف، الكمية، السعر، الخصم، `savings` (وفّرت)، النهائي.

**تأكيد نعم / لا**

```http
POST /api/user/schedules/{id}/custom-basket/confirm
```

سؤال الواجهة: «بدك هالسلة كل {اسم الفئة} مع تذكير قبل الموعد؟»

| | Body | النتيجة |
|--|------|---------|
| نعم | `{ "confirm_schedule": true, "start_date": "2026-09-08" }` | تُحفظ في طلباتي المجدولة + تذكير + `cart_items` لأول طلب |
| لا | `{ "confirm_schedule": false }` | `cart_items` لمرة واحدة — المسودة تُحذف |

`cart_items`: `[{ shop_product_variant_id, quantity }]` — أضيفوهم لسلة الموقع/اطلبوا `POST /orders`.

أكثر من فئة = مسودة منفصلة لكل `schedule_id` (أسبوعي وشهري مع بعض).
