# Flutter — فئات الجدولة + السلة المخصصة + أقسام الصفحة

> **أرسلوا هذا الملف لفريق Flutter**  
> **آخر تحديث:** 5 أيلول 2026  
> **Base:** `/api/user` + `Accept-Language: ar|en`  
> **Auth:** الكروت عامة. التخصيص والتأكيد = Bearer user.

نفس عقد الويب. الأسعار من `*_formatted` / `*_currencies`. لا تحسبوا خصم الفئة على الجهاز.

---

## لا تخلطوا بين النوعين

| | فئات الجدولة | سلل أدمن جاهزة |
|--|-------------|----------------|
| شو هي | كرت أسبوعي / شهري — المستخدم يملأ المنتجات | سلة جاهزة من الأدمن |
| `content_type` | `schedule` | `schedule-basket` |
| `api_method` | `schedules` | `schedule-basket` |
| `display_type_id` | **11** | **5** |
| `onTap` | شاشة تخصيص `scheduleId` | تفاصيل سلة جاهزة |

المسار الأساسي: **فئات الجدولة**. `display_type_id == 5` اختياري إذا ظهر القسم.

عقد الكرت ثابت — لا تنتظروا أسماء ثانية:

| عندكم | من الباك |
|--------|-----------|
| غلاف | `image` (URL) |
| معرض | `images` (`List<String>`) |
| شارات | `top_badges` / `bottom_badges` |

لا `cover_image` / `photo` / `thumbnail` / `gallery` / `media`.  
`name` = `String` حسب اللغة.  
`discount_type` = `percentage` \| `fixed` \| `null` (مو `none`).

إذا الصورة فاضية: الأدمن ما حفظها بعد.

---

## 1) كروت الفئات على الرئيسية

من أقسام الصفحة — ويدجت الأقسام الموحّد:

```http
GET /api/user/sections?page_slug=home
Accept-Language: ar
```

إذا `content_type == 'schedule'` أو `display_type_id == 11`:

- `layout`: `slider` \| `list` \| `grid`
- `variant`: يُفضَّل `vertical` — مستطيل عمودي + **صورة دائرية**
- `items` = كروت الفئات

`onTap` → `CustomBasketScreen(scheduleId: item.id)`

قسم `type == 'manual'`: الحقول داخل `items[].item`.  
قسم `type == 'api'`: الحقول على `items[]` مباشرة.

قائمة مستقلة (فعّال فقط، بلا ترقيم):

```http
GET /api/user/schedules
GET /api/user/schedules/{id}
```

`data.items`.

```dart
class ScheduleCategory {
  final int id;
  final String name;
  final String? description;
  final String? image;
  final List<String> images;
  final int intervalDays;
  final String? discountType; // percentage | fixed | null
  final double discountValue;
  final List<Badge> topBadges;
  final List<Badge> bottomBadges;
}
```

بادج: `id`, `name`, `image`, `color`, `type`, `position`.

---

## 2) شاشة التخصيص

غير المسجّل: هيدر من `GET /schedules/{id}` ثم اطلبوا تسجيل الدخول قبل الإضافة.

```http
GET /api/user/schedules/{id}/custom-basket
Authorization: Bearer {token}
```

ينشئ مسودة فاضية. `is_draft: true` حتى التأكيد. **مسودة لكل `scheduleId`** (أسبوعي ≠ شهري).

1. هيدر: `schedule.image` + `schedule.name` + زر **عرض السلة**
2. فئات أفقية + بحث + (اختياري) كروت براند مربعة
3. شبكة منتجات → متغيّر + كمية → POST فوري

| الغرض | Endpoint |
|--------|----------|
| هيدر + مسودة | `GET .../custom-basket` |
| فئات | `GET /api/user/categories` |
| منتجات | `GET /api/user/products?category_id=&search=&brand_id=` |
| براند | `GET /api/user/brands` |
| منتج | `GET /api/user/products/{id}` → `shop_variants[].id` |

### ليش `shop_product_variant_id`؟

البيع: منتج × متغيّر × **متجر**.  
خذوا `shop_variants[].id` (مو `variant_id`). لازم `shop_id != null` و`(quantity ?? 0) > 0`.

```dart
bool canAdd(ShopVariant? v) =>
    v?.id != null && v?.shopId != null && (v?.quantity ?? 0) > 0;
```

---

## 3) إضافة / كمية / حذف

```http
POST /api/user/schedules/{id}/custom-basket/items
{ "shop_product_variant_id": 25, "quantity": 2 }

PUT /api/user/schedules/{id}/custom-basket/items/{itemId}
{ "quantity": 3 }

DELETE /api/user/schedules/{id}/custom-basket/items/{itemId}
```

نفس `shop_product_variant_id` يحدّث الكمية (ما يكرّر السطر). الرد = المسودة كاملة.

`itemId` = `items[].id` من المسودة — **مو** المنتج ولا `shop_product_variant_id`.

عدّاد الزر من `summary.items_count`.

---

## 4) عرض السلة = نفس GET

لا تخزّنوا الإجمالي على الجهاز.

| العمود | JSON |
|--------|------|
| صورة | `items[].product.image` |
| اسم | `items[].product.name` |
| متغيّر | `items[].variant.name` (`List<String>`) |
| كمية | `items[].quantity` |
| وحدة | `items[].unit` |
| سعر الوحدة | `items[].original_price_formatted` |
| سطر | `items[].line_total_formatted` |
| متجر | `items[].shop.name` |
| شركة | `items[].product.brand.name` |

```dart
summary.itemsCount
summary.totalQuantity
summary.originalPriceFormatted
summary.discountValue + summary.discountType
summary.savingsFormatted   // وفّرت
summary.finalPriceFormatted
```

الخصم من فئة الجدولة على مجموع السلة.

---

## 5) تأكيد نعم / لا

> بدك تطلب هالسلة كل **{schedule.name}** ونبعت تذكير قبل الموعد؟

```http
POST /api/user/schedules/{id}/custom-basket/confirm
```

| الزر | Body |
|------|------|
| نعم | `{ "confirm_schedule": true, "start_date": "2026-09-08" }` — ≥ اليوم |
| لا | `{ "confirm_schedule": false }` |

```json
{
  "scheduled": true,
  "next_run_date": "2026-09-08",
  "cart_items": [{ "shop_product_variant_id": 25, "quantity": 2 }]
}
```

- نعم → `scheduled: true` + تُحفظ في طلباتي المجدولة + `cart_items` لأول طلب
- لا → `scheduled: false` + المسودة اتمسحت + `cart_items` مرة

`POST /orders` بنفس `shop_product_variant_id` + `quantity`.  
بعد نعم: `GET /api/user/scheduled-baskets` أو `GET /api/user/my-baskets`.

سلة فاضية → 422.

---

## 6) سلل أدمن جاهزة (اختياري)

إذا `display_type_id == 5` أو `content_type == 'schedule-basket'`:

- سلل جاهزة من الأدمن — مو شاشة التخصيص
- لا تفتحوا `custom-basket` عليها
- فلتر قسم اختياري: `schedule_id`

---

## 7) Checklist

- [ ] قسم `display_type_id=11` / `content_type=schedule` → كروت فئات (صورة دائرية)
- [ ] `display_type_id=5` → سلل جاهزة، مسار مختلف
- [ ] كروت: `image` / `images` / `top_badges` / `bottom_badges`
- [ ] `name` String — `discount_type` null مسموح
- [ ] `onTap` → شاشة `{scheduleId}`
- [ ] `shop_variants[].id` + `shop_id != null` قبل الإضافة
- [ ] `itemId` = `items[].id`
- [ ] `summary` بدون حساب محلي
- [ ] نعم يحتاج `start_date` · لا يحذف المسودة
- [ ] `cart_items` → سلة / `POST /orders`
- [ ] مسودة لكل `scheduleId`
