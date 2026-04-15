# Frontend Guide - Admin Affiliate API Changes

هذا الملف مخصص لفريق الـ Frontend، ويشرح فقط ما يلزمكم معرفته عن تغييرات Admin API الخاصة بالمسوّق.

## What Changed

الأدمن أصبح قادرًا على تحديد طريقة عمولة المسوّق بثلاثة أنماط:

1. `percentage_order`
   - نسبة من كامل الطلب.
2. `fixed_per_order`
   - قيمة ثابتة لكل طلب.
3. `percentage_selected_products`
   - نسبة ولكن على منتجات يحددها الأدمن.

---

## Affected Admin APIs

### 1) Create User
- `POST /api/admin/users`

### 2) Update User
- `PUT /api/admin/users/{id}`

### 3) Demote Affiliate
- `POST /api/admin/users/{id}/demote-affiliate`

### 4) Reactivate Affiliate (one step)
- `POST /api/admin/users/{id}/reactivate-affiliate`

> جميع المسارات تتطلب Admin auth token.

---

## New Request Fields (Frontend)

في create/update للمستخدم، يمكن إرسال:

- `affiliate_commission_type`
  - values:
    - `percentage_order`
    - `fixed_per_order`
    - `percentage_selected_products`

- `affiliate_rate`
  - required إذا النوع:
    - `percentage_order`
    - `percentage_selected_products`

- `affiliate_fixed_commission`
  - required إذا النوع:
    - `fixed_per_order`

- `affiliate_product_ids` (array of product ids)
  - required إذا النوع:
    - `percentage_selected_products`

---

## Frontend Form Behavior (Recommended)

### When `affiliate_commission_type = percentage_order`
- Show field: `affiliate_rate`
- Hide fields:
  - `affiliate_fixed_commission`
  - `affiliate_product_ids`

### When `affiliate_commission_type = fixed_per_order`
- Show field: `affiliate_fixed_commission`
- Hide fields:
  - `affiliate_rate`
  - `affiliate_product_ids`

### When `affiliate_commission_type = percentage_selected_products`
- Show fields:
  - `affiliate_rate`
  - `affiliate_product_ids` (multi-select products)
- Hide field:
  - `affiliate_fixed_commission`

---

## Payload Examples

### A) Percentage on full order
```json
{
  "is_affiliate": true,
  "affiliate_id": "AFF-1001",
  "affiliate_commission_type": "percentage_order",
  "affiliate_rate": 10
}
```

### B) Fixed amount per order
```json
{
  "is_affiliate": true,
  "affiliate_id": "AFF-1001",
  "affiliate_commission_type": "fixed_per_order",
  "affiliate_fixed_commission": 5
}
```

### C) Percentage on selected products
```json
{
  "is_affiliate": true,
  "affiliate_id": "AFF-1001",
  "affiliate_commission_type": "percentage_selected_products",
  "affiliate_rate": 12,
  "affiliate_product_ids": [11, 15, 27]
}
```

---

## Validation/Error Messages to Handle

قد يرجع API رسائل مرتبطة بهذه الحالات:

- `custom.marketer.invalid_commission_type`
- `custom.marketer.rate_required_for_percentage`
- `custom.marketer.fixed_amount_required`
- `custom.marketer.products_required_for_selected_percentage`
- `custom.marketer.request_not_submitted`

يفضل عرض message القادمة من الـ API مباشرة للمستخدم الإداري.

---

## Response Notes for Frontend

بعض resources أصبحت تعيد معلومات إضافية داخل affiliate/order:
- `affiliate_commission_type`
- `affiliate_fixed_commission`
- `affiliate_commission_amount` (على الطلب)

بالتالي يمكن عرض نوع العمولة وقيمتها في واجهات:
- User details (admin)
- Orders list/details (admin)

---

## Demote Affiliate Impact (Frontend)

عند استدعاء:
- `POST /api/admin/users/{id}/demote-affiliate`

توقع أن بيانات التسويق للمستخدم تصبح غير مفعلة (`is_affiliate = false`, `affiliate_approved = false`).
ويجب تحديث الواجهة فورًا (refresh user data) بعد نجاح العملية.

---

## Reactivate Affiliate (One Step)

يمكن الآن إعادة تفعيل المسوّق من طلب واحد بدل خطوتين.

### Request
```json
{
  "affiliate_commission_type": "percentage_selected_products",
  "affiliate_rate": 12,
  "affiliate_product_ids": [11, 15, 27]
}
```

### Notes
- إذا لم يتم إرسال `affiliate_id` سيتم استخدام رقم المسوّق الحالي من المستخدم.
- إذا كان `affiliate_id` مفقودًا بالكامل، سيعيد API خطأ `custom.marketer.no_affiliate_number`.
- عند النجاح يتم تفعيل:
  - `is_affiliate = true`
  - `affiliate_approved = true`

