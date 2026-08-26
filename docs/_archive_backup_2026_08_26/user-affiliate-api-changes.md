# User API Changes - Affiliate (Marketer)

هذا الملف مخصص لفريق الـ Frontend ويوضح التعديلات الخاصة بالمسوّق من جهة `User API`.

## المسارات المتأثرة

## 1) Public visit tracking
- `POST /api/user/visit-website-bymarkter`
- لا يحتاج تسجيل دخول.
- يستخدم لاحتساب زيارة لرابط/كود المسوّق.

### Request body
```json
{
  "affiliate_id": "AFF-1001"
}
```

### السلوك الجديد
- يزيد `affiliate_visits` للمسوّق.
- إذا كانت عمولة الزيارات مفعلة للمسوّق، يتم احتسابها تلقائيًا.
- عند تحقق شرط الزيارات (مثل كل 10 زيارات) يتم إضافة حركة محفظة:
  - `type = visit_commission`
  - `amount = قيمة عمولة الزيارات`

---

## 2) Marketer protected endpoints (requires `auth:user`)

prefix: `/api/user/markter`

- `GET /statistics`
- `GET /profile`
- `GET /orders`
- `GET /transactions`
- `POST /withdraw-request`
- `GET /withdraw-requests`
- `GET /monthly-orders`

---

## التغييرات في Responses

## A) `GET /api/user/markter/profile`

أضيفت حقول جديدة في البيانات:
- `commission_type`
- `fixed_commission`
- `visit_commission_enabled`
- `visit_commission_threshold`
- `visit_commission_amount`

### مثال
```json
{
  "affiliate_id": "AFF-1001",
  "affiliate_link": "https://example.com/affiliate/AFF-1001",
  "rate": 12,
  "commission_type": "percentage_selected_products",
  "fixed_commission": null,
  "total_visites": 42,
  "visit_commission_enabled": true,
  "visit_commission_threshold": 10,
  "visit_commission_amount": 10
}
```

## B) `GET /api/user/markter/statistics`

الحسابات الآن تشمل عمولة الزيارات ضمن الأرباح:
- `earned_commission` = عمولة الطلبات + عمولة الزيارات
- `available_balance` يشمل عمولة الزيارات أيضًا
- `visit_commission` تمت إضافتها كحقل مستقل

### مثال مختصر
```json
{
  "earned_commission": 120.5,
  "visit_commission": 30,
  "withdrawn": 20,
  "available_balance": 130.5
}
```

## C) `GET /api/user/markter/transactions`

نوع الحركة `type` قد يكون الآن:
- `commission` (عمولة طلبات)
- `visit_commission` (عمولة زيارات)
- `withdraw` (سحب)

---

## التغييرات في السحب

عند طلب السحب:
- الرصيد المتاح أصبح محسوبًا من:
  - `commission`
  - `visit_commission`
  - ناقص `withdraw`

بالتالي عمولة الزيارات تعتبر رصيد قابل للسحب مثل عمولة الطلب.

---

## ملاحظات مهمة للفرونت

- endpoint الزيارة (`visit-website-bymarkter`) يبقى بسيطًا: فقط أرسل `affiliate_id`.
- لا تحتاج الواجهة لحساب عمولة الزيارات؛ الحساب يتم في الباك بالكامل.
- في صفحة المسوّق:
  - اعرض إعدادات عمولة الزيارات من `profile`.
  - اعرض `visit_commission` بشكل منفصل في `statistics` إن رغبت.
  - في شاشة الحركات اعرض نوع `visit_commission` ضمن فلترة/بادج النوع.

