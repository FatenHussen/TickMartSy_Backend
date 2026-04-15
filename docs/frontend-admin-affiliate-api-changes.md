# Frontend Guide - Final User API Changes (Admin)

هذا الملف هو النسخة النهائية لفريق الـ Frontend، ويغطي كل التغييرات الجديدة المتعلقة بـ `User` في Admin API، خصوصًا المسوّق (Affiliate).

## Endpoints المتأثرة

- `POST /api/admin/users` (إنشاء مستخدم)
- `PUT /api/admin/users/{id}` (تحديث مستخدم)
- `POST /api/admin/users/{id}/demote-affiliate` (إيقاف المسوّق)
- `POST /api/admin/users/{id}/reactivate-affiliate` (إعادة تفعيل المسوّق بخطوة واحدة)
- `GET /api/admin/users`
- `GET /api/admin/users/{id}`

> جميع المسارات تتطلب Admin auth token.

---

## ملخص الميزات الجديدة للمسوّق

1) **أنماط عمولة الطلب (إلزامية عند تفعيل المسوّق)**
- `percentage_order`: نسبة من إجمالي الطلب.
- `fixed_per_order`: مبلغ ثابت لكل طلب.
- `percentage_selected_products`: نسبة على منتجات محددة.

2) **عمولة زيارات (اختيارية)**
- يمكن تفعيل قاعدة: كل X زيارات = مبلغ Y.
- مثال: كل 10 زيارات = 10.

3) **تشغيل/إيقاف المسوّق**
- إيقاف: `demote-affiliate`
- إعادة تفعيل خطوة واحدة: `reactivate-affiliate`

---

## الحقول الجديدة في User payload (Admin)

### Affiliate Core
- `is_affiliate` (boolean)
- `affiliate_id` (string or number as string)
- `affiliate_commission_type`
  - `percentage_order`
  - `fixed_per_order`
  - `percentage_selected_products`
- `affiliate_rate` (numeric 0..100)
- `affiliate_fixed_commission` (numeric >= 0)
- `affiliate_product_ids` (array of product ids)

### Affiliate Visit Commission (Optional)
- `affiliate_visit_commission_enabled` (boolean)
- `affiliate_visit_commission_threshold` (integer >= 1)
- `affiliate_visit_commission_amount` (numeric >= 0)

---

## قواعد إظهار الحقول في الواجهة

### إذا `affiliate_commission_type = percentage_order`
- أظهر: `affiliate_rate`
- أخفِ: `affiliate_fixed_commission`, `affiliate_product_ids`

### إذا `affiliate_commission_type = fixed_per_order`
- أظهر: `affiliate_fixed_commission`
- أخفِ: `affiliate_rate`, `affiliate_product_ids`

### إذا `affiliate_commission_type = percentage_selected_products`
- أظهر: `affiliate_rate`, `affiliate_product_ids`
- أخفِ: `affiliate_fixed_commission`

### إذا `affiliate_visit_commission_enabled = true`
- أظهر: `affiliate_visit_commission_threshold`, `affiliate_visit_commission_amount`

### إذا `affiliate_visit_commission_enabled = false`
- أخفِ: `affiliate_visit_commission_threshold`, `affiliate_visit_commission_amount`

---

## Payload Examples

### A) نسبة على كامل الطلب + بدون عمولة زيارات
```json
{
  "is_affiliate": true,
  "affiliate_id": "AFF-1001",
  "affiliate_commission_type": "percentage_order",
  "affiliate_rate": 10,
  "affiliate_visit_commission_enabled": false
}
```

### B) مبلغ ثابت لكل طلب + عمولة زيارات
```json
{
  "is_affiliate": true,
  "affiliate_id": "AFF-1001",
  "affiliate_commission_type": "fixed_per_order",
  "affiliate_fixed_commission": 5,
  "affiliate_visit_commission_enabled": true,
  "affiliate_visit_commission_threshold": 10,
  "affiliate_visit_commission_amount": 10
}
```

### C) نسبة على منتجات محددة + عمولة زيارات
```json
{
  "is_affiliate": true,
  "affiliate_id": "AFF-1001",
  "affiliate_commission_type": "percentage_selected_products",
  "affiliate_rate": 12,
  "affiliate_product_ids": [11, 15, 27],
  "affiliate_visit_commission_enabled": true,
  "affiliate_visit_commission_threshold": 20,
  "affiliate_visit_commission_amount": 5
}
```

### D) Reactivate Affiliate (one step)
```json
{
  "affiliate_commission_type": "percentage_selected_products",
  "affiliate_rate": 12,
  "affiliate_product_ids": [11, 15, 27],
  "affiliate_visit_commission_enabled": true,
  "affiliate_visit_commission_threshold": 10,
  "affiliate_visit_commission_amount": 10
}
```

---

## سلوك demote / reactivate

### Demote
`POST /api/admin/users/{id}/demote-affiliate`

بعد النجاح:
- `is_affiliate = false`
- `affiliate_approved = false`
- تصفير إعدادات عمولة الطلب
- تعطيل عمولة الزيارات وتصفير tracking
- مسح `affiliate_product_ids`

### Reactivate
`POST /api/admin/users/{id}/reactivate-affiliate`

بعد النجاح:
- `is_affiliate = true`
- `affiliate_approved = true`
- تطبيق إعدادات عمولة الطلب + عمولة الزيارات من نفس الطلب
- إذا `affiliate_id` لم يُرسل، يستخدم الموجود مسبقًا

---

## رسائل أخطاء يجب دعمها في UI

- `custom.marketer.request_not_submitted`
- `custom.marketer.cannot_change_number`
- `custom.marketer.invalid_commission_type`
- `custom.marketer.rate_required_for_percentage`
- `custom.marketer.fixed_amount_required`
- `custom.marketer.products_required_for_selected_percentage`
- `custom.marketer.visit_threshold_required`
- `custom.marketer.visit_amount_required`
- `custom.marketer.no_affiliate_number`
- `custom.marketer.affiliate_number_taken`
- `custom.marketer.pending_withdraw_requests`

يفضل عرض `message` القادمة من API مباشرة.

---

## حقول جديدة متوقعة في Responses

في بيانات `user.affiliate` (حسب الـ resource المستخدم):
- `affiliate_commission_type`
- `affiliate_fixed_commission`
- `affiliate_visit_commission_enabled`
- `affiliate_visit_commission_threshold`
- `affiliate_visit_commission_amount`

وفي بيانات الطلبات:
- `affiliate_commission_type`
- `affiliate_fixed_commission`
- `affiliate_commission_amount`

---

## ملاحظات مهمة للفرونت

- عمولة الطلب **إلزامية** للمسوّق (يجب اختيار نمط صحيح + حقوله).
- عمولة الزيارات **اختيارية**.
- بعد أي عملية (`update`, `demote`, `reactivate`) اعمل refresh لبيانات المستخدم فورًا.

