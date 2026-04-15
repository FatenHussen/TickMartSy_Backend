# Demote Affiliate API (Admin)

توثيق API الخاص بالأدمن لتحويل حساب المستخدم من `affiliate` إلى مستخدم عادي عبر:
- `UserCrudController::demoteAffiliate`
- `UserService::demoteAffiliate`

## Base Endpoint
- `POST /api/admin/users/{id}/demote-affiliate`
- يتطلب `auth:admin`

## Purpose
هذا الـ endpoint يقوم بإلغاء صلاحية التسويق للمستخدم بشكل آمن **بدون خسارة البيانات التاريخية**.

## Business Rules

قبل تنفيذ التحويل:
- يجب أن يكون المستخدم لديه `affiliate_id` (وإلا يتم رفض الطلب).


عند نجاح التحويل:
- يتم تعطيل كل الكوبونات النشطة المرتبطة بنفس `affiliate_id` (`is_active = false`).
- يتم تحديث بيانات المستخدم:
  - `is_affiliate = false`
  - `affiliate_approved = false`
  - `affiliate_rate = null`
- **لا يتم حذف `affiliate_id`** للحفاظ على الربط التاريخي مع:
  - حركات المحفظة (`affiliate_wallet_transactions`)
  - طلبات السحب (`affiliate_withdraw_requests`)
  - الطلبات التاريخية التي تحمل affiliate data
- يتم إرسال إشعار للمستخدم بأن حساب التسويق تم إيقافه.

## Why `affiliate_id` Is Kept
الإبقاء على `affiliate_id` يمنع كسر أي تقارير أو علاقات تاريخية مبنية على هذا الحقل.  
بالتالي نلغي الصلاحية التشغيلية للمسوّق، لكن نحافظ على الأثر المحاسبي والتاريخي.

## Request

### Method & URL
```http
POST /api/admin/users/15/demote-affiliate
Authorization: Bearer {admin_token}
Accept: application/json
```

### Body
لا يوجد body مطلوب.

## Success Response

```json
{
  "status": true,
  "message": "تم تحويل الحساب إلى مستخدم عادي بنجاح.",
  "data": {
    "id": 15,
    "name": "User Name",
    "is_affiliate": false,
    "affiliate_approved": false,
    "affiliate_id": "AFF-1001",
    "affiliate_rate": null
  }
}
```

> ملاحظة: شكل `data` النهائي يعتمد على serializer/response formatter المستخدم بالمشروع.

## Error Cases

### 1) User has no affiliate number
عند عدم وجود `affiliate_id` للمستخدم:

```json
{
  "status": false,
  "message": "هذا المستخدم لا يملك رقم مسوّق."
}
```

### 2) Pending withdraw requests exist
عند وجود طلبات سحب معلّقة:

```json
{
  "status": false,
  "message": "لا يمكن تحويل الحساب إلى مستخدم عادي قبل معالجة طلبات السحب المعلّقة."
}
```

## Atomicity
العملية تتم داخل `DB::transaction` لضمان الاتساق:
- إما تنفيذ كل الخطوات بنجاح.
- أو rollback كامل في حال أي خطأ.

## Route Reference
تم تسجيل المسار في:
- `routes/api/admin.php`
  - `POST /api/admin/users/{id}/demote-affiliate`

