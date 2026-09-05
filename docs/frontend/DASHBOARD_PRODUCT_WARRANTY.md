# الداشبورد — الضمان دروب داون + بدون متغيّر افتراضي

> **الجمهور:** فريق الداشبورد  
> **تاريخ:** 5 أيلول 2026  
> **الباك:** جاهز بعد `migrate` + إعادة صلاحيات `warranty.*`

---

## 1) الضمان = قائمة منسدلة من قسم مستقل

حقل الضمان في إنشاء المنتج **ليس** عدد أشهر (`warranty_period`).

الأدمن يعرّف خيارات الضمان من قسم **الضمانات** (مثل الأيقونات: اسم عربي/إنجليزي + وصف عربي/إنجليزي + تفعيل/تعديل/حذف)، ثم يختار واحداً من الدروب داون على فورم المنتج.

| الغرض | Endpoint |
|--------|----------|
| قائمة الضمانات | `GET /api/admin/warranties` |
| إنشاء | `POST /api/admin/warranties` |
| تعديل | `PATCH /api/admin/warranties/{id}` |
| حذف | `DELETE /api/admin/warranties/{id}` |
| ربط المنتج | `warranty_id` في `POST/PUT /api/admin/products` |

صلاحيات الواجهة (مفرد `warranty` — مو `warranties`):

`warranty.view` · `warranty.create` · `warranty.update` · `warranty.delete`

الـ API مثل الوحدات: أدمن مسجّل يكفي لـ `GET /warranties` (دروب داون المنتج). السايدبار يخضع لـ `warranty.view`.

### Payload الضمان

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

الاسم مطلوب. الوصف اختياري.

### فورم المنتج

- `<select name="warranty_id">` — اختياري
- الخيارات: `GET /api/admin/warranties?is_active=1&per_page=500`
- لا ترسلوا `warranty_period` من الواجهة
- فارغ = لا ترسلوا `warranty_id` عند الإنشاء؛ عند التعديل أرسلوا `warranty_id=` لتفريغه
- رد المنتج: `warranty: { id, name, description }` + `warranty_id`

مسار الداشبورد: `/products/warranties`

### سايدبار — الباك ما بيرجّع قائمة أقسام

قسم **الضمانات** ما بيطلع لحاله. زي الأيقونات/الوحدات: تضيفوا بند تحت المنتجات.

| | |
|--|--|
| العنوان | الضمانات |
| المسار | `/products/warranties` |
| إظهار البند | `permissions` فيها **`warranty.view`** (مو `warranties.view`) |
| CRUD الصفحة | نفس مفاتيح `warranty.*` |

المصدر: `GET /api/admin/auth/profile` → `permissions: ["warranty.view", ...]`.  
إذا المفتاح مو موجود: على السيرفر `php artisan migrate` ثم **تسجيل خروج/دخول** (كاش Spatie).

---

## 2) ليش كان في «المتغير رقم 1» دائماً؟

الداشبورد كان **يولّد كارد متغيّر فاضي تلقائياً** بعد اختيار نوع المنتج (حتى بدون صفات). هذا مو من الباك.

**القرار:** لا متغيّر افتراضي. تاب المتغيّرات فاضي إلى أن يضغط الأدمن «إضافة».

استثناء: منتج مطعم مربوط بفرع ما زال يحتاج `variants[0]` عند اختيار المحل (`shop_variants`) — هذا مو الكارد الفارغ العام.

المنتج يُنشأ بدون `variants` إذا الأدمن ما أضاف ولا كارد. الكمية تبقى على `variants[].quantity` عند وجود كارد.

---

## 3) تشغيل الباك

```bash
php artisan migrate
php artisan db:seed --class=AdminRolePermissionSeeder
```

الثاني يضيف `warranty.*` ويعيد مزامنة صلاحيات سوبر أدمن.
