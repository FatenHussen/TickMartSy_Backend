# Admin API Changes - Affiliate Commission Modes

هذا الملف يشرح التغييرات التي حصلت في قسم الأدمن بخصوص إعدادات عمولة المسوّق (Affiliate).

## الهدف

إضافة 3 حالات لعمولة المسوّق:

1. `percentage_order`
   - نسبة مئوية من إجمالي الطلب.
2. `fixed_per_order`
   - مبلغ ثابت لكل طلب.
3. `percentage_selected_products`
   - نسبة مئوية لكن على منتجات محددة فقط يحددها الأدمن.

---

## Admin Endpoints المتأثرة

### 1) إنشاء مستخدم (قد يكون مسوّق)
- `POST /api/admin/users`

### 2) تحديث مستخدم (وتحديث إعدادات المسوّق)
- `PUT /api/admin/users/{id}`

### 3) تحويل المسوّق إلى مستخدم عادي
- `POST /api/admin/users/{id}/demote-affiliate`

### 4) إعادة تفعيل المسوّق (خطوة واحدة)
- `POST /api/admin/users/{id}/reactivate-affiliate`

> جميع المسارات تتطلب `auth:admin`.

---

## الحقول الجديدة في Admin User API

يمكن إرسال الحقول التالية في `store` و `update`:

- `affiliate_commission_type`
  - القيم المسموحة:
    - `percentage_order`
    - `fixed_per_order`
    - `percentage_selected_products`

- `affiliate_fixed_commission`
  - رقم (>= 0)
  - مطلوب عندما يكون النوع `fixed_per_order`

- `affiliate_product_ids`
  - مصفوفة IDs منتجات
  - مطلوبة عندما يكون النوع `percentage_selected_products`
  - كل عنصر يجب أن يكون موجودًا في `products.id`

الحقول الحالية ما زالت مدعومة:
- `affiliate_id`
- `affiliate_rate`
- `is_affiliate`
- `affiliate_approved`

---

## قواعد التحقق (Business Validation)

تمت إضافة تحقق منطقي في `UserService`:

1. إذا النوع `fixed_per_order`:
   - يجب وجود `affiliate_fixed_commission`.
   - يتم تصفير `affiliate_rate` (لا تُستخدم في هذا النوع).

2. إذا النوع `percentage_order` أو `percentage_selected_products`:
   - يجب وجود `affiliate_rate`.
   - يتم تصفير `affiliate_fixed_commission`.

3. إذا النوع `percentage_selected_products`:
   - يجب وجود منتجات في `affiliate_product_ids`.

4. لا يمكن تعديل `affiliate_id` إذا كان موجودًا سابقًا لنفس المستخدم.

5. في حال وجود إعدادات عمولة فعّالة، يتم تفعيل:
   - `affiliate_approved = true`

---

## التخزين في قاعدة البيانات (Admin Scope)

### users
تمت إضافة:
- `affiliate_commission_type` (enum)
- `affiliate_fixed_commission` (decimal)

### affiliate_user_products
تمت إضافة جدول ربط:
- `user_id`
- `product_id`

يُستخدم فقط عندما النوع `percentage_selected_products`.

---

## سلوك demote-affiliate بعد التحديث

عند استدعاء:
- `POST /api/admin/users/{id}/demote-affiliate`

يتم:
- تعطيل كوبونات المسوّق النشطة.
- تصفير حالة المسوّق:
  - `is_affiliate = false`
  - `affiliate_approved = false`
  - `affiliate_rate = null`
  - `affiliate_commission_type = percentage_order`
  - `affiliate_fixed_commission = null`
- حذف المنتجات المرتبطة بنمط المنتجات المحددة (`affiliate_user_products`).

---

## سلوك reactivate-affiliate

عند استدعاء:
- `POST /api/admin/users/{id}/reactivate-affiliate`

يمكن إعادة تفعيل المستخدم كمسوّق في طلب واحد مع ضبط نوع العمولة.

### Payload
```json
{
  "affiliate_commission_type": "fixed_per_order",
  "affiliate_fixed_commission": 5
}
```

### سلوك التنفيذ
- يتم تفعيل:
  - `is_affiliate = true`
  - `affiliate_approved = true`
- يتم الاحتفاظ بـ `affiliate_id` الحالي إذا لم يتم إرسال واحد جديد.
- يتم رفض العملية إذا `affiliate_id` مستخدم في حساب آخر.

---

## أمثلة Payload للأدمن

### A) نسبة على كامل الطلب

```json
{
  "is_affiliate": true,
  "affiliate_id": "AFF-1001",
  "affiliate_commission_type": "percentage_order",
  "affiliate_rate": 10
}
```

### B) مبلغ ثابت لكل طلب

```json
{
  "is_affiliate": true,
  "affiliate_id": "AFF-1001",
  "affiliate_commission_type": "fixed_per_order",
  "affiliate_fixed_commission": 5
}
```

### C) نسبة على منتجات محددة

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

## رسائل الأخطاء الجديدة

تمت إضافة رسائل ترجمة (`ar/en`) للحالات التالية:
- `custom.marketer.invalid_commission_type`
- `custom.marketer.rate_required_for_percentage`
- `custom.marketer.fixed_amount_required`
- `custom.marketer.products_required_for_selected_percentage`

---

## ملاحظة تشغيل

بعد سحب التحديثات يجب تشغيل:

```bash
php artisan migrate
```

لإنشاء الأعمدة والجداول الجديدة الخاصة بإعدادات عمولات المسوّق.

