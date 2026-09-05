# Flutter — آخر نسخة: كروت الجدولة + السلة المخصصة

> **أرسلوا هذا الملف لفريق Flutter**  
> **آخر تحديث:** 5 أيلول 2026 (مساءً)  
> **Base:** `/api/user` + `Accept-Language: ar|en`  
> **Auth:** الكروت عامة. التخصيص والتأكيد = Bearer user.

نفس عقد الويب. الأسعار من `*_formatted` / `*_currencies`. لا تحسبوا خصم الفئة على الجهاز.

---

## ماذا تغيّر اليوم

أسماء الصورة والبادجز ثابتة — ما تغيّرت:

| عندكم | من الباك |
|--------|-----------|
| غلاف | `image` (URL) |
| معرض | `images` (`List<String>`) |
| شارات | `top_badges` / `bottom_badges` |

لا تنتظروا `cover_image` / `photo` / `thumbnail` / `gallery` / `media`.

`name` = `String` حسب اللغة.  
`discount_type` = `percentage` \| `fixed` \| `null` (مو `none`).

إذا الصورة فاضية على الكرت: الأدمن ما حفظها بعد. نفس الحقول تتعبّى بعد رفع الباك.

---

## 1) كروت الفئات

مستطيل عمودي + **صورة دائرية**.

```http
GET /api/user/schedules
GET /api/user/schedules/{id}
```

`data.items` — بدون صفحات. الفعّال فقط.

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

`onTap` → شاشة التخصيص بـ `id`.

أقسام الصفحة: `content_type == 'schedule'` أو `display_type_id == 11` → نفس الكرت.  
`display_type_id == 5` = سلل أدمن جاهزة.

بادج: `id`, `name`, `image`, `color`, `type`, `position`.

---

## 2) شاشة التخصيص

1. هيدر: `schedule.image` + `schedule.name` + زر عرض السلة  
2. فئات + بحث + (اختياري) براند  
3. شبكة منتجات → `shop_variants[].id` + كمية → POST فوري

```http
GET /api/user/schedules/{id}/custom-basket
```

ينشئ مسودة. `is_draft: true` حتى التأكيد. مسودة لكل `scheduleId`.

| الغرض | Endpoint |
|--------|----------|
| هيدر + مسودة | `GET .../custom-basket` |
| فئات | `GET /api/user/categories` |
| منتجات | `GET /api/user/products?category_id=&search=&brand_id=` |
| براند | `GET /api/user/brands` |
| منتج | `GET /api/user/products/{id}` → `shop_variants[].id` |

---

## 3) إضافة / كمية / حذف

```http
POST /api/user/schedules/{id}/custom-basket/items
{ "shop_product_variant_id": 25, "quantity": 2 }

PUT /api/user/schedules/{id}/custom-basket/items/{itemId}
{ "quantity": 3 }

DELETE /api/user/schedules/{id}/custom-basket/items/{itemId}
```

`itemId` = `items[].id`. عدّاد الزر من `summary.items_count`.

---

## 4) عرض السلة = نفس GET

| العمود | JSON |
|--------|------|
| صورة | `items[].product.image` |
| اسم | `items[].product.name` |
| متغيّر | `items[].variant.name` (`List<String>`) |
| كمية | `items[].quantity` |
| وحدة | `items[].unit` |
| سعر السطر | `items[].line_total_formatted` |
| متجر | `items[].shop.name` |

```dart
summary.itemsCount
summary.totalQuantity
summary.originalPriceFormatted
summary.discountValue + summary.discountType
summary.savingsFormatted
summary.finalPriceFormatted
```

---

## 5) تأكيد نعم / لا

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

- نعم → `scheduled: true` + `cart_items` لأول طلب + `GET /scheduled-baskets`
- لا → `scheduled: false` + `cart_items` مرة (المسودة اتمسحت)

`POST /orders` بنفس `shop_product_variant_id` + `quantity`.

---

## 6) Checklist

- [ ] كروت: `image` / `images` / `top_badges` / `bottom_badges`
- [ ] `name` String — `discount_type` null مسموح
- [ ] `shop_variants[].id` + `shop_id != null` قبل الإضافة
- [ ] `summary` بدون حساب محلي
- [ ] نعم يحتاج `start_date`
- [ ] مسودة لكل `scheduleId`
