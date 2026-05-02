# Promotion — لوحة التحكم (Admin API)

**Base URL:** `/api/admin/promotions`  
**المصادقة:** `auth:admin` + `crud.permission:promotion`.

الكنترولر: `app\Http\Controllers\Admin\PromotionController.php`  
الطلبات: `app\Http\Requests\Admin\Promotion\StoreRequest.php`, `UpdateRequest.php`  
السيرفس: `app\Services\Admin\PromotionService.php`

---

## Endpoints (REST)

| الطريقة | المسار | الوصف |
|---------|--------|--------|
| `GET` | `/api/admin/promotions` | قائمة مع ترقيم وبحث (انظر `BaseService`) |
| `POST` | `/api/admin/promotions` | إنشاء |
| `GET` | `/api/admin/promotions/{id}` | تفاصيل (`OneResource`) |
| `PUT`/`PATCH` | `/api/admin/promotions/{id}` | تحديث |
| `DELETE` | `/api/admin/promotions/{id}` | حذف |

---

## حقول الإنشاء (`StoreRequest`)

- **`name`**, **`description`:** مطلوبان كـ `array` مع `name.en`, `name.ar`, `description.en`, `description.ar`.
- **`type`:** أحد:  
  `simple_discount`, `spend_x_discount`, `spend_x_get_gift`, `spend_x_get_points`, `free_shipping`, `spend_x_get_free_shipping`
- **`is_active`**, **`starts_at`**, **`ends_at`:** اختياري / تاريخ.
- **`min_spend`**, **`discount_value`**, **`discount_type`**, **`gift_description`**, **`reward_points`:** حسب النوع (انظر قواعد `required_if` في الملف).
- **`page_slugs`:** اختياري، مصفوفة؛ كل عنصر: `exists:pages,slug`. تُحفظ عبر pivot **`page_promotion`**.

### تحديث (`UpdateRequest`)

- الحقول غالباً `nullable` مع نفس قيود النوع عند الإرسال.
- **`page_slugs`:** إذا **وُجد** المفتاح في الطلب تُعاد مزامنة الصفحات (قائمة فارغة = إزالة كل الروابط). إذا **لم يُرسل** المفتاح لا تتغير الصفحات المرتبطة.

---

## السيرفس — مزامنة الصفحات

- عند **الإنشاء:** إذا وُجدت **`page_slugs`** في الطلب تُزامن مع `pages`؛ إن لم تُرسل لا يُنشأ ربط صفحات (عرض عام حسب منطق واجهة المستخدم).
- عند **التحديث:** مزامنة فقط عند وجود المفتاح في الـ payload.

---

## استجابة التفاصيل (`OneResource`)

حقول تقريبية:

- `id`, `name`, `description` (ترجمات), `type`, `is_active`, `starts_at`, `ends_at`
- `min_spend`, `discount_value`, `discount_type`
- `gift_description`, `reward_points`
- **`page_slugs`:** عند تحميل علاقة **`pages`** — مصفوفة **`slug`**.

---

## قائمة الحقول حسب النوع (`fieldsForType`)

الدالة **`PromotionService::fieldsForType(string $type)`** تعيد أسماء الحقول المناسبة للنموذج في الواجهة (تشمل **`page_slugs`** لكل الأنواع).

> ملاحظة: يوجد في `PromotionController` ميثود **`fieldsForType($type)`**؛ إذا لم يكن لها مسار في `routes/api/admin.php` يجب إضافة route يدوياً لاستخدامها من الـ dashboard.

---

## مراجع

- الموديل: `app\Models\Promotion.php`
- الهجرة: `database/migrations/2026_05_02_150000_create_page_promotion_table.php`
- البذور: `database/seeders/PromotionSeeder.php` (يربط صفحات نموذجية)
