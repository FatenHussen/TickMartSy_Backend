# Flutter — السلة المخصصة

> **الجمهور:** فريق Flutter  
> **تاريخ:** 5 أيلول 2026  
> **Base:** `/api/user` + `Accept-Language: ar|en`  
> **Auth:** كروت الفئات عامة. التخصيص والتأكيد: Bearer user.

الأسعار من الـ API (`*_formatted` / `*_currencies`). لا تحسبوا خصم الفئة على الجهاز.

نفس عقد الويب. التفصيل للشاشة.

---

## الفكرة

فئات من الأدمن (أسبوعي، شهري…). المستخدم يفتح فئة ويملأها هو. أكثر من فئة مسموح. الخصم على مجموع السلة.

---

## 1) كروت الفئات

شكل المستند: مستطيل عمودي، **صورة دائرية** جوّاه.

```http
GET /api/user/schedules
GET /api/user/schedules/{id}
```

`data.items` — بدون صفحات.

```dart
class ScheduleCategory {
  final int id;
  final String name;
  final String? description;
  final String? image;
  final List<String> images; // تناوب / GIF
  final int intervalDays;
  final String? discountType; // percentage | fixed
  final double discountValue;
  final List<Badge> topBadges;
  final List<Badge> bottomBadges;
}
```

`onTap` → شاشة التخصيص مع `id`.

نفس الكروت تظهر في أقسام الصفحة إذا `content_type == 'schedule'` أو `display_type_id == 11`. الضغط على `items[].id` → شاشة التخصيص. `display_type_id == 5` يبقى سلل أدمن جاهزة (`schedule-basket`).

بادج: `id`, `name`, `image`, `color`, `position`.

---

## 2) شاشة التخصيص

1. هيدر: صورة الفئة + الاسم + زر **عرض السلة**
2. فئات رئيسية (أفقي)
3. بحث
4. (اختياري) كروت براند مربعة
5. شبكة منتجات — اختيار متغيّر + كمية → POST فوري

| الغرض | Endpoint |
|--------|----------|
| هيدر + مسودة | `GET /api/user/schedules/{id}/custom-basket` |
| فئات | `GET /api/user/categories` |
| منتجات | `GET /api/user/products?category_id=&search=&brand_id=` |
| براند | `GET /api/user/brands` |
| منتج | `GET /api/user/products/{id}` → `shop_variants[].id` للسلة |

`GET custom-basket` ينشئ مسودة. `is_draft: true` حتى التأكيد.

---

## 3) إضافة / كمية / حذف

```http
POST /api/user/schedules/{id}/custom-basket/items
{ "shop_product_variant_id": 25, "quantity": 2 }

PUT /api/user/schedules/{id}/custom-basket/items/{itemId}
{ "quantity": 3 }

DELETE /api/user/schedules/{id}/custom-basket/items/{itemId}
```

`itemId` = `items[].id`. نفس المتغيّر يحدّث الكمية.

حدّثوا عدّاد زر «عرض السلة» من `summary.items_count` أو `summary.total_quantity`.

---

## 4) شاشة عرض السلة

نفس `GET custom-basket`.

### الأعمدة

| العمود | JSON |
|--------|------|
| صورة | `items[].product.image` |
| اسم | `items[].product.name` |
| متغيّر | `items[].variant.name` (`List<String>`) |
| كمية | `items[].quantity` |
| وحدة | `items[].unit` |
| سعر السطر | `items[].line_total_formatted` |
| متجر | `items[].shop.name` |

Stepper الكمية → `PUT`. سلايد حذف → `DELETE`.

### الفوتر = `summary`

```dart
summary.itemsCount
summary.totalQuantity
summary.originalPriceFormatted
summary.discountValue + summary.discountType
summary.savingsFormatted   // وفّرت
summary.finalPriceFormatted
```

---

## 5) تأكيد نعم / لا

> بدك تطلب هالسلة كل **{schedule.name}** ونبعت تذكير قبل الموعد؟

```http
POST /api/user/schedules/{id}/custom-basket/confirm
```

| الزر | Body |
|------|------|
| نعم | `{ "confirm_schedule": true, "start_date": "2026-09-08" }` — DatePicker ≥ اليوم |
| لا | `{ "confirm_schedule": false }` |

الرد:

```json
{
  "scheduled": true,
  "next_run_date": "2026-09-08",
  "cart_items": [
    { "shop_product_variant_id": 25, "quantity": 2 }
  ]
}
```

- **نعم:** `scheduled: true` — أضيفوا `cart_items` للكارت/الطلب الأول. القائمة: `GET /api/user/scheduled-baskets`
- **لا:** `scheduled: false` — `cart_items` مرة. المسودة اتمسحت؛ لا تحتفظوا بـ GET المسودة

`POST /orders` بنفس `shop_product_variant_id` + `quantity`. نوع السلة العادية ما لم يكن عندكم `cart_type` خاص.

---

## 6) Checklist

- [ ] كروت `/schedules` مع صورة دائرية وبادجز
- [ ] شاشة `{id}`: هيدر + فئات + بحث + براند
- [ ] `shop_variants[].id` + `shop_id != null` قبل الإضافة
- [ ] عرض السلة من `summary` بدون حساب محلي
- [ ] نعم يحتاج `start_date`؛ لا يمسح المسودة
- [ ] `cart_items` → كارت/طلب
- [ ] مسودة لكل `scheduleId` (أسبوع وشهر منفصلين)
