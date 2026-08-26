# User Promotions API

توثيق نقطة النهاية العامة لقائمة العروض الترويجية للمستخدم، حسب `app\Http\Controllers\User\PromotionController.php`.

## المسار

- **`GET /api/user/promotions`**

البادئة `api` من تسجيل مسارات Laravel، و`user` من `routes/api/user.php`. **لا يتطلب** تسجيل دخول مستخدم (`auth:user` غير مطبّق على هذا المسار).

---

## معلمات الاستعلام (Query)

| المعامل      | مطلوب | الوصف |
|-------------|-------|--------|
| `page_slug` | لا    | نص، حتى 255 حرفًا. إن وُجد يجب أن يطابق عمود **`slug`** في جدول **`pages`** (`exists:pages,slug`). |

### سلوك `page_slug`

- **بدون `page_slug`:** تُعاد كل العروض التي تمرّ بـ **`scopeActive`** (نشطة ضمن نافذة التواريخ إن وُجدت).
- **مع `page_slug`:** يُطبَّق **`scopeForPageSlug`**: العروض المرتبطة بتلك الصفحة **أو** العروض **غير المرتبطة بأي صفحة** (تظهر في كل السياقات).

---

## التحقق من الطلب

فشل التحقق يعيد **422** مع تفاصيل الحقول (مثل `page_slug` غير موجود في `pages`).

---

## الترتيب والتحميل

- الترتيب الحالي في الكنترولر: **`orderBy('id')`** تصاعديًا.
- تُحمّل علاقة **`pages`** لإظهار **`page_slugs`** في المورد.

---

## شكل عنصر العرض (`App\Http\Resources\Promotion\AllResource`)

| الحقل | النوع / الوصف |
|--------|----------------|
| `id` | معرّف العرض |
| `name` | ترجمة Spatie (مثلاً كائن `ar` / `en`) |
| `description` | ترجمة |
| `type` | نص؛ مثل `simple_discount`, `spend_x_discount`, … |
| `is_active` | منطقي |
| `position` | نص؛ **`top`** أو **`bottom`** (موضع العرض في الواجهة) |
| `created_at` | تنسيق `Y-m-d H:i` |
| `page_slugs` | مصفوفة من `slug` الصفحات المرتبطة (عند تحميل `pages`) |

### مثال عنصر

```json
{
  "id": 1,
  "name": { "en": "10% Off Everything", "ar": "خصم 10% على كل شيء" },
  "description": { "en": "…", "ar": "…" },
  "type": "simple_discount",
  "is_active": true,
  "position": "top",
  "created_at": "2026-05-01 12:00",
  "page_slugs": ["cart"]
}
```

---

## استجابة الـ API

يستخدم الكنترولر `sendResponse` من `App\Http\Controllers\Controller`:

```json
{
  "status": true,
  "message": "…",
  "data": [ … ]
}
```

حيث **`data`** مصفوفة من موارد العروض (ليس كائنًا يحتوي `items`).

---

## منطق «نشط» على الموديل (`Promotion::scopeActive`)

- `is_active === true`
- `starts_at` إن وُجدت ≤ الآن أو `null`
- `ends_at` إن وُجدت ≥ الآن أو `null`

---

## مراجع سريعة

| الملف |
|--------|
| `app/Http/Controllers/User/PromotionController.php` |
| `app/Http/Resources/Promotion/AllResource.php` |
| `app/Models/Promotion.php` — `pages()`, `scopeActive`, `scopeForPageSlug` |
| `routes/api/user.php` — مجموعة `promotions` |

لتوثيق إنشاء/تعديل العروض من لوحة التحكم، راجع `docs/promotion-admin.md`.
