# API طلبات الخدمة (Service Orders) — مستخدم

توثيق نقاط النهاية الخاصة بـ `App\Http\Controllers\User\ServiceOrder\ServiceOrderController` لاستخدامها من تطبيق الويب أو الموبايل (المستخدم المسجّل).

## أساسيات

| البند | القيمة |
|--------|--------|
| **المسار الأساسي** | `{APP_URL}/api/user/service-orders` |
| **المصادقة** | مطلوبة لجميع العمليات — حارس `user` (Laravel Sanctum). أرسل التوكن كما يعتمد المشروع (مثلاً `Authorization: Bearer {token}` و/أو كوكي حسب إعدادات الواجهة). |
| **تنسيق الاستجابة الناجحة** | JSON مع الحقول: `status` (boolean `true`)، `message` (نص)، `data` (حمولة الطلب). |

### مثال غلاف نجاح عام

```json
{
  "status": true,
  "message": "...",
  "data": { }
}
```

---

## 1) قائمة طلبات الخدمة للمستخدم الحالي

**`GET`** `/api/user/service-orders`

يعيد الطلبات المرتبطة بالمستخدم المسجّل فقط (يُفلتر من الخادم بـ `user_id`).

### معاملات الاستعلام (Query)

| المعامل | النوع | مطلوب | الوصف |
|---------|--------|--------|--------|
| `status` | string | لا | تصفية حسب الحالة. القيم المسموحة: `pending`, `canceled`, `rejected`, `completed` |
| `search` | string | لا | بحث في الحقول: `id`, `notes` |
| `sort_field` | string | لا | افتراضي: `id`. مسموح: `id`, `created_at`, `date` |
| `sort_order` | string | لا | افتراضي: `desc` |
| `page` | integer | لا | افتراضي: `1` |
| `per_page` | integer | لا | افتراضي: `10` |

### شكل `data` عند النجاح

```json
{
  "items": [ /* مصفوفة عناصر — انظر شكل العنصر في القائمة أدناه */ ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 10,
    "total": 42
  }
}
```

### شكل عنصر في القائمة (`items[]`)

| الحقل | النوع | الوصف |
|--------|--------|--------|
| `id` | number | المعرف |
| `status` | string | حالة الطلب |
| `price` | string/number | السعر |
| `price_unit` | string/null | وحدة السعر |
| `shop` | object/null | `{ id, name, lat, lng }` |
| `vendor_service` | object/null | `{ id, name }` |
| `created_at` | string/null | تاريخ ووقت الإنشاء |
| `date` | string/null | تاريخ الخدمة `Y-m-d` |
| `time` | string/null | وقت الخدمة بصيغة `H:i` (دقيقتان للساعة والدقيقة) |
| `notes` | string/null | ملاحظات |
| `user` | object/null | `{ id, name, phone }` |

---

## 2) تفاصيل طلب خدمة واحد

**`GET`** `/api/user/service-orders/{id}`

`{id}`: معرف السجل في جدول طلبات الخدمة. يُنصح باستدعاء التفاصيل فقط لمعرّفات ظهرت مسبقاً في قائمة المستخدم (`GET` القائمة) حتى لا يُعرَّض التطبيق لعرض بيانات غير مقصودة.

### شكل `data` عند النجاح (عنصر واحد)

| الحقل | النوع | الوصف |
|--------|--------|--------|
| `id` | number | |
| `status` | string | |
| `price` | string/number | |
| `price_unit` | string/null | |
| `notes` | string/null | |
| `date` | string/null | `Y-m-d` |
| `time` | string/null | `H:i` |
| `created_at` | string/null | |
| `shop` | object/null | `{ id, name, lat, lng }` |
| `vendor_service` | object/null | `{ id, name, description }` |
| `shop_vendor_service` | object/null | `{ id, extra_details, duration_minutes, schedule }` |
| `user` | object/null | `{ id, name, phone }` |

### عدم العثور على الطلب

إذا لم يوجد سجل بالمعرف: استجابة **404** بصيغة أخطاء التطبيق المخصصة، مثلاً:

```json
{
  "status": false,
  "message": "...",
  "errors": []
}
```

---

## 3) إنشاء طلب خدمة

**`POST`** `/api/user/service-orders`

`Content-Type: application/json` (أو حسب ما يقبل الخادم مع نفس الحقول).

### جسم الطلب (JSON)

| الحقل | النوع | مطلوب | القواعد |
|--------|--------|--------|---------|
| `shop_id` | integer | نعم | يجب أن يكون `id` موجوداً في جدول `shops` |
| `vendor_service_id` | integer | نعم | يجب أن يكون موجوداً في جدول `vendor_services` |
| `date` | string (تاريخ) | نعم | تاريخ صالح، **أكبر من أو يساوي اليوم** (`after_or_equal:today`) |
| `time` | string | نعم | وقت بصيغة **24 ساعة**: `HH:mm` أو `HH:mm:ss` (regex على الخادم) |
| `notes` | string | لا | اختياري |

### منطق العمل (للفهم وليس للعرض للمستخدم النهائي)

- يبحث الخادم عن ربط نشط `ShopVendorService` يطابق `shop_id` و`vendor_service_id` مع `is_active = true`.
- إن لم يوجد: **404** مع جسم مبسّط يحتوي على `message` فقط (مفتاح ترجمة مثل `custom.service_orders.service_not_available`).
- عند النجاح: يُنشأ الطلب بحالة أولية **`pending`**، مع السعر والوحدة المأخوذين من ربط المتجر بالخدمة.

### شكل `data` عند النجاح

نفس شكل **تفاصيل طلب واحد** (أعلاه) — مورد `OneResource`.

### أخطاء التحقق (Validation)

عند مخالفة قواعد الحقول: عادة **422** مع تفاصيل أخطاء Laravel القياسية في `errors`.

---

## حالات الطلب (`status`)

| القيمة | معنى تقريبي |
|--------|-------------|
| `pending` | قيد الانتظار |
| `canceled` | ملغى |
| `rejected` | مرفوض |
| `completed` | مكتمل |

---

## العمليات غير المفعّلة

المسار مُعرّف كـ `apiResource` مع **`only(['index', 'show', 'store'])`**.  
لا يوجد من الخادم للمستخدم:

- `PUT`/`PATCH` تحديث
- `DELETE` حذف

---

## ملاحظات للفرونت إند

1. **الترتيب والبحث**: استخدم `sort_field`, `sort_order`, `search`, `page`, `per_page` مع القائمة كما في الجدول.
2. **التاريخ والوقت**: أرسل `date` كتاريخ واضح (يوم/شهر/سنة حسب ما يتوقعه الخادم مع المنطقة الزمنية للتطبيق)، و`time` بصيغة 24 ساعة لتجنب رفض التحقق.
3. **الخدمة غير المتاحة**: عند 404 بعد `POST`، تعامل مع `message` كرسالة للمستخدم (حسب لغة التطبيق إن وُجدت ترجمة).
4. **الاتساق مع بقية الـ API**: رسائل النجاح تأتي من `sendResponse`؛ بعض الأخطاء المخصصة قد تختلف قليلاً في شكل JSON (مثل استجابة 404 لخدمة غير متاحة) — تعامل مع الحالتين عند عرض الرسائل.

---

*آخر تحديث يتوافق مع الكود في المشروع: مسارات `routes/api/user.php` والخدمة `App\Services\User\ServiceOrderService`.*
