# Admin Driver Coverage APIs

هذا الملف يشرح واجهات الـ API الخاصة بالـ admin المتعلقة بتصفية الطلبات حسب تغطية السائق (`coverage`) وربط ذلك بالواجهة الأمامية.

## Base URL

`/api/admin`

## Authentication

جميع هذه الـ endpoints تتطلب `auth:admin` (Bearer token).

Header:
```http
Authorization: Bearer {admin_token}
```

## Response Envelope

استجابة النجاح تكون بالشكل التالي:
```json
{
  "status": true,
  "message": "Success",
  "data": ...
}
```

---

## 1) جلب الطلبات المتاحة للتعيين (مع/بدون فلترة تغطية السائق)

### Endpoint

`GET /api/admin/orders/to-assign`

### Query Params

- `filter_by_driver_coverage` (optional, boolean)
  - إذا `false` (أو غير موجود) => لا يتم تطبيق فلترة الـ coverage
  - إذا `true` => يتم تطبيق فلترة الـ coverage بناءً على `driver_id`

- `driver_id` (required إذا `filter_by_driver_coverage=true`)
  - رقم السائق

- `status` (optional)
  - `pending` أو `preparing`
  - إذا لم يتم الإرسال => يرجع الاثنين معًا (`pending` + `preparing`)

- `is_instant_delivery` (optional, boolean, default=true)
  - يتم إرجاع الطلبات التي تطابق هذه القيمة

### ما الذي يرجع؟

الاستعلام يرجع طلبات:
- `driver_id` = `null` (غير مُسندة حاليًا لأي سائق)
- `status` ∈ (`pending`, `preparing`) (حسب `status` إن تم إرساله)
- `is_instant_delivery` حسب قيمة `is_instant_delivery`
- (اختياري) فلترة إضافية حسب `coverage`

### Coverage Rules (عند تفعيل `filter_by_driver_coverage=true`)

يتم حساب صلاحية الطلب للسائق كالتالي:

1) فلترة **عنوان التوصيل**:
   - يجب أن يكون `order.address.area_id` ضمن مناطق التغطية المسموحة للسائق.
   - مناطق التغطية المسموحة = اتحاد:
     - مناطق مدن السائق (`cities -> areas`) إذا السائق مرتبط بـ `cities`
     - `area_id` الخاصة بمتاجر السائق (`shops`) إذا السائق مرتبط بـ `shops`
     - `area_id` الخاصة بمتاجر vendors المرتبطين بالسائق (`vendors -> shops`) إذا السائق مرتبط بـ `vendors`

2) فلترة **متاجر عناصر الطلب** (Shop per item):
   - أي عنصر داخل الطلب (عن طريق `shop` الخاص بالـ item) يجب أن يكون من متجر “مسموح”.
   - المسموح:
     - متجر ضمن `driver_shops` أو
     - متجر تابع لـ `driver_vendors` أو
     - متجر يقع في `area_id` ضمن مناطق مدن السائق

3) قاعدة مهمة جدًا (اختياري لكل بعد):
   - إذا السائق **غير مربوط** بأي `cities` => لا يوجد قيد على مناطق المدن (السائق يخدم كل المناطق من جهة المدن)
   - إذا السائق **غير مربوط** بأي `vendors` => لا يوجد قيد على الـ vendors
   - إذا السائق **غير مربوط** بأي `shops` => لا يوجد قيد على الـ shops

### Response (data)

`data` تكون مصفوفة من عناصر dropdown بالشكل:
```json
[
  { "id": 15, "value": "ORD-15 . Ahmad Ali" },
  { "id": 16, "value": "ORD-16 . Sara Mohamed" }
]
```

- `value` يتم بناؤه كالتالي:
  - `(order_code || id) + " . " + (user.name || "-")`

### مثال Requests

بدون coverage (فقط غير مُسند + instant delivery=true):
```http
GET /api/admin/orders/to-assign?status=pending&is_instant_delivery=true
```

مع coverage:
```http
GET /api/admin/orders/to-assign?filter_by_driver_coverage=true&driver_id=3&status=preparing
```

---

## 2) تعيين السائق على طلب (Existing Endpoint)

بعد ما تختاري `order` من الـ dropdown، يتم إسناده للسائق عبر:

`POST /api/admin/orders/{orderId}/assign-driver`

Body:
```json
{
  "driver_id": 3
}
```

---
