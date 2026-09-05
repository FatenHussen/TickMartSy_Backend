# الداشبورد — قسم الضمانات + دروب داون المنتج

> **أرسلوا هذا الملف لفريق الداشبورد**  
> **آخر تحديث:** 5 أيلول 2026  
> **Base:** `/api/admin` + Admin token  
> **الباك:** بعد `git pull` + `php artisan migrate`

انسخوا شاشة **الوحدات** أو **الأيقونات**. نفس الفكرة: قسم مستقل يعرّف الخيارات، وفورم المنتج يختار واحداً.

---

## ماذا تعملون (ملخص)

| أين | المطلوب |
|-----|---------|
| السايدبار | بند جديد تحت المنتجات — الباك **لا** يرسم القائمة |
| صفحة CRUD | `/products/warranties` |
| فورم المنتج | `<select name="warranty_id">` بدل أشهر `warranty_period` |
| الأدوار | مجموعة `warranty.*` (مفرد) |

---

## 1) السايدبار — بدون هالبند القسم ما بيطلع

| | |
|--|--|
| العنوان | الضمانات / Warranties |
| المسار | `/products/warranties` |
| إظهار البند | `profile.permissions` فيها **`warranty.view`** |

```ts
const canViewWarranties = permissions.includes('warranty.view');
```

**ممنوع:** `warranties.view` — المفتاح مفرد زي `unit.view` و`icon.view`.

المصدر:

```http
GET /api/admin/auth/profile
```

```json
{
  "permissions": [
    "warranty.view",
    "warranty.create",
    "warranty.update",
    "warranty.delete"
  ]
}
```

إذا المفاتيح مو موجودة: على السيرفر `php artisan migrate` ثم **خروج ودخول**. لا تعتمدوا توكن قديم.

---

## 2) الصلاحيات

| مفتاح | الواجهة |
|--------|---------|
| `warranty.view` | السايدبار + قائمة + عرض |
| `warranty.create` | زر إضافة |
| `warranty.update` | تعديل + تفعيل |
| `warranty.delete` | حذف |

الـ API مثل الوحدات: أدمن مسجّل يكفي لـ `GET /warranties` (دروب داون المنتج). السايدبار وصفحة CRUD يخضعون للمفاتيح فوق.

أضيفوا المجموعة في شاشة الأدوار بنفس الأسماء. لا تشتقوا الاسم من المسار `warranties`.

---

## 3) CRUD — نفس الوحدات

| Method | Endpoint | صلاحية |
|--------|----------|--------|
| GET | `/api/admin/warranties` | `warranty.view` |
| GET | `/api/admin/warranties/{id}` | `warranty.view` |
| POST | `/api/admin/warranties` | `warranty.create` |
| PATCH | `/api/admin/warranties/{id}` | `warranty.update` |
| DELETE | `/api/admin/warranties/{id}` | `warranty.delete` |

فلاتر القائمة: `page` · `per_page` · `name` · `is_active=1` (أو `true`).

### إنشاء / تعديل

```json
{
  "name": { "ar": "إرجاع مجاني", "en": "FREE Returns" },
  "description": {
    "ar": "يمكنك إرجاع المنتج مجاناً…",
    "en": "You can return this item for FREE…"
  },
  "is_active": true
}
```

- `name.ar` و `name.en` مطلوبان عند الإنشاء
- `description` اختياري
- لا صورة

### عنصر القائمة / التفاصيل

```json
{
  "id": 1,
  "name": "إرجاع مجاني",
  "name_translations": { "ar": "إرجاع مجاني", "en": "FREE Returns" },
  "description": "يمكنك إرجاع المنتج مجاناً…",
  "description_translations": { "ar": "…", "en": "…" },
  "is_active": true,
  "created_at": "2026-09-05T20:00:00.000000Z",
  "updated_at": "2026-09-05T20:00:00.000000Z"
}
```

`name` و `description` = نص حسب `Accept-Language`. للفورم استخدموا `name_translations` / `description_translations`.

القائمة مغلفة كباقي الـ CRUD: `data.items` + `data.pagination`.

---

## 4) فورم إنشاء / تعديل المنتج

الحقل **مو** عدد أشهر.

| | |
|--|--|
| الحقل | `<select name="warranty_id">` — اختياري |
| الخيارات | `GET /api/admin/warranties?is_active=1&per_page=500` |
| القيمة | `id` |
| العرض | `name` (أو `name_translations` حسب لغة الداش) |
| إنشاء بدون ضمان | لا ترسلوا `warranty_id` |
| تعديل لتفريغ الضمان | أرسلوا `warranty_id=` (فاضي) |
| **لا ترسلوا** | `warranty_period` |

### رد المنتج

```json
{
  "warranty_id": 1,
  "warranty": {
    "id": 1,
    "name": "إرجاع مجاني",
    "name_translations": { "ar": "إرجاع مجاني", "en": "FREE Returns" },
    "description": "…",
    "description_translations": { "ar": "…", "en": "…" }
  },
  "warranty_period": null
}
```

اعرضوا المختار من `warranty` / `warranty_id`. `warranty_period` قديم — تجاهلوه.

---

## 5) لا متغيّر افتراضي

لا تولّدوا كارد «المتغير رقم 1» بعد اختيار نوع المنتج. تاب المتغيّرات فاضي إلى أن يضغط الأدمن «إضافة».

استثناء: منتج مطعم مربوط بفرع ما زال يحتاج `variants[0]` عند اختيار المحل — هذا مو الكارد الفارغ العام.

---

## 6) Checklist

- [ ] سايدبار: الضمانات → `/products/warranties` إذا `warranty.view`
- [ ] المفتاح `warranty.view` مو `warranties.view`
- [ ] CRUD: اسم ar/en · وصف ar/en · تفعيل · تعديل · حذف
- [ ] أدوار: مجموعة `warranty.*`
- [ ] فورم المنتج: `warranty_id` من `GET /warranties?is_active=1&per_page=500`
- [ ] لا `warranty_period`
- [ ] بعد migrate: خروج ودخول ثم فحص `profile.permissions`
- [ ] لا كارد متغيّر تلقائي
