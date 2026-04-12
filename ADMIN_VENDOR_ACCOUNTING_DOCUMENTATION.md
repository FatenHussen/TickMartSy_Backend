# Vendor Accounting (Admin) Documentation
# توثيق محاسبة البائعين - لوحة الإدارة

**Last Updated:** 2026-04-09  
**Base URL:** `/api/admin`  
**Auth:** `Authorization: Bearer <admin_token>`

---

## 1) الهدف من الميزة

ميزة **محاسبة البائعين** في الأدمن تعطيك صورة مالية واضحة لكل بائع:

- إجمالي المبيعات (قبل عمولة المنصة)
- عمولة المنصة
- حصة الخصومات
- الصافي المستحق للبائع
- المدفوع للبائع
- المبالغ المعلقة للسحب
- المتبقي والمتاح للسحب

كما تضيف إدارة كاملة لطلبات سحب البائعين من طرف الأدمن.

---

## 2) ما تم تطبيقه فعلياً

### APIs محاسبة البائعين (Admin)

1. `GET /vendor-accounting/summary`
2. `GET /vendor-accounting/vendors`
3. `GET /vendor-accounting/vendors/{vendorId}`

### APIs إدارة سحب البائعين (Admin)

1. `GET /vendor-withdraw-requests`
2. `GET /vendor-withdraw-requests/{id}`
3. `PUT /vendor-withdraw-requests/{id}`

---

## 3) مصادر البيانات المحاسبية

الحسابات تعتمد على:

- `orders` (فقط الطلبات بحالة `delivered`)
- `order_items`
- `order_item_extras` (إن وجدت)
- `products.vendor_id` لتحديد صاحب البند (البائع)
- `vendors.commission_rate`
- `vendor_withdraw_requests` للسحوبات (`pending`, `paid`, `rejected`)

---

## 4) المعادلات المالية المعتمدة

### 4.1 إجمالي المبيعات للبائع `gross_sales`

يتم حسابه من عناصر الطلبات المسلمة للبائع:

`(سعر العنصر × الكمية) + (إضافات العنصر × الكمية)`

### 4.2 عمولة المنصة `platform_commission`

`gross_sales × (commission_rate / 100)`

### 4.3 حصة الخصومات `discounts_share`

الخصومات على مستوى الطلب:

- `basket_discount`
- `coupon_discount`
- `coupon_discount_from_points`
- `subscription_discount`
- `promotion_discount`

يتم توزيعها على البائعين بنسبة مساهمة كل بائع في `subtotal` الطلب.

### 4.4 الصافي المستحق `net_due`

`gross_sales - platform_commission - discounts_share - refunds`

### 4.5 المدفوع والمتبقي

- `paid`: مجموع طلبات السحب التي حالتها `paid`
- `pending_withdrawals`: مجموع طلبات السحب `pending`
- `remaining_after_paid = net_due - paid`
- `available_for_withdraw = remaining_after_paid - pending_withdrawals`

> ملاحظة: `refunds` حالياً محسوبة `0` لأنه لا يوجد تدفق مرتجعات مالي مستقل بعد.

---

## 5) شرح الـ Endpoints

## 5.1 `GET /api/admin/vendor-accounting/summary`

ملخص إجمالي لكل البائعين.

### Query Params (اختياري)

- `from_date` (date)
- `to_date` (date)

### مثال Response

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "vendors_count": 12,
    "active_vendors_count": 10,
    "gross_sales": 25400.5,
    "platform_commission": 1720.04,
    "discounts_share": 410.5,
    "refunds": 0,
    "net_due": 23269.96,
    "paid": 15000,
    "pending_withdrawals": 1800,
    "remaining_after_paid": 8269.96,
    "available_for_withdraw": 6469.96
  }
}
```

---

## 5.2 `GET /api/admin/vendor-accounting/vendors`

قائمة محاسبة لكل بائع مع Pagination.

### Query Params

- `search` (يبحث في `owner_name` واسم البائع `ar/en`)
- `is_active` (`true/false`)
- `from_date`
- `to_date`
- `page`
- `per_page`

### شكل العنصر الواحد

```json
{
  "vendor": {
    "id": 4,
    "name": {
      "ar": "متجر ألف",
      "en": "Store A"
    },
    "name_translations": {
      "ar": "متجر ألف",
      "en": "Store A"
    },
    "owner_name": "Ahmad",
    "commission_rate": 7.5,
    "is_active": true,
    "created_at": "2026-03-01 10:20:30"
  },
  "wallet": {
    "orders_count": 44,
    "commission_rate": 7.5,
    "gross_sales": 5400,
    "platform_commission": 405,
    "discounts_share": 120,
    "refunds": 0,
    "net_due": 4875,
    "paid": 3000,
    "pending_withdrawals": 500,
    "remaining_after_paid": 1875,
    "available_for_withdraw": 1375
  }
}
```

---

## 5.3 `GET /api/admin/vendor-accounting/vendors/{vendorId}`

كشف حساب تفصيلي لبائع واحد + طلبات السحب الخاصة به.

### Query Params

- `from_date`
- `to_date`
- `withdraw_status` (`pending|paid|rejected`)
- `withdraw_per_page`

---

## 5.4 `GET /api/admin/vendor-withdraw-requests`

جلب طلبات سحب البائعين في لوحة الأدمن.

### Query Params

- `status` (`pending|paid|rejected`)
- `vendor_id`
- `payment_method` (`bank_transfer|cash|wallet|other`)
- `from`
- `to`
- `min_amount`
- `max_amount`
- `search`
- `sort_field`
- `sort_order`
- `page`
- `per_page`

---

## 5.5 `PUT /api/admin/vendor-withdraw-requests/{id}`

تحديث حالة طلب السحب.

### القواعد

- لا يمكن تحديث إلا الطلبات `pending`.
- الحالة الجديدة يجب أن تكون:
  - `paid`
  - أو `rejected`
- عند `paid` يتم التحقق أن المبلغ لا يتجاوز الرصيد المتاح.

### Payload عند الدفع

```json
{
  "status": "paid",
  "payment_method": "bank_transfer",
  "transfer_reference": "TRX-2026-00012",
  "note": "Paid successfully"
}
```

### Payload عند الرفض

```json
{
  "status": "rejected",
  "rejection_reason": "Bank details missing",
  "note": "Please resubmit with valid IBAN"
}
```

---

## 6) الفلو التشغيلي في الإدارة

1. الأدمن يبدأ من `vendor-accounting/summary` لمشاهدة الصورة العامة.
2. ينتقل إلى `vendor-accounting/vendors` لمقارنة البائعين.
3. يفتح `vendor-accounting/vendors/{id}` لكشف الحساب التفصيلي.
4. يراجع طلبات السحب عبر `vendor-withdraw-requests`.
5. يعالج الطلب المعلق:
   - `paid` إذا الرصيد كافٍ
   - `rejected` مع سبب واضح

---

## 7) القيود الحالية (مهم)

1. **المرتجعات (`refunds`)**: حالياً صفر (غير مربوط بتدفق مرتجعات مالي مستقل).
2. **دورة التسوية الأسبوعية/الشهرية**: غير مطبقة كإغلاق دوري رسمي بعد.
3. **Ledger كامل** (قيود محاسبية مفصلة لكل حركة): غير مفعل بعد، والحساب الآن تحليلي من جداول الطلبات + السحب.

---

## 8) الملفات المرتبطة بالتنفيذ

### Services

- `app/Services/Admin/VendorAccountingService.php`
- `app/Services/Admin/VendorWithdrawRequestService.php`

### Controllers

- `app/Http/Controllers/Admin/VendorAccounting/VendorAccountingController.php`
- `app/Http/Controllers/Admin/VendorWithdrawRequest/VendorWithdrawRequestController.php`

### Requests

- `app/Http/Requests/Admin/VendorWithdrawRequest/FilterRequest.php`
- `app/Http/Requests/Admin/VendorWithdrawRequest/UpdateRequest.php`

### Resources

- `app/Http/Resources/Admin/VendorWithdrawRequest/AllResource.php`
- `app/Http/Resources/Admin/VendorWithdrawRequest/OneResource.php`

### Routes

- `routes/api/admin.php`

### Model relation

- `app/Models/Vendor.php` (`withdrawRequests`)

---

## 9) اقتراح المرحلة التالية

للوصول إلى محاسبة أدق (Accounting-grade):

1. إنشاء `vendor_wallet_transactions` (دفتر قيود).
2. إدخال قيد محاسبي عند تسليم الطلب (sale/commission/discount share).
3. إضافة قيد عند السحب المدفوع.
4. إنشاء `vendor_settlements` لدورات التسوية الأسبوعية/الشهرية.
5. ربط المرتجعات بقيود عكسية.

