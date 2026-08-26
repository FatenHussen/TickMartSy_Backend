# شريط التنقّل الرئيسي (Navigation Menu) — تحكّم كامل من الداشبورد

هذا المستند موجّه لفريق الداشبورد (React) وفريق الويب/الموبايل. الهدف: يتحكّم الأدمن **بالكامل** بالشريط العلوي (الفئات الرئيسية، الماركات، كل المتاجر، سلالي، النقاط، المساعدة، باقات الاشتراك...) **بدون أي كود** — إضافة/تعديل/حذف/ترتيب/تفعيل من واجهة بسيطة.

> ليست إعدادات JSON يدوية. هذا **جدول مستقل** (`nav_menu_items`) بعناصر منفصلة، كل عنصر صف مستقل يُدار بضغطات.

---

## 1) نموذج العنصر

كل عنصر في الشريط يحتوي:

| الحقل | النوع | الوصف |
|-------|-------|--------|
| `title` | كائن `{ar, en}` | نص العنصر بكل لغة |
| `type` | نص | وجهة العنصر: `page` \| `category` \| `brand` \| `url` \| `route` |
| `page_id` | رقم | مطلوب إذا `type=page` — صفحة من الـ Page Builder |
| `category_id` | رقم | مطلوب إذا `type=category` — فئة معيّنة |
| `brand_id` | رقم | مطلوب إذا `type=brand` — ماركة معيّنة |
| `url` | نص | مطلوب إذا `type=url` — رابط خارجي |
| `route_key` | نص | مطلوب إذا `type=route` — شاشة ثابتة داخل التطبيق (انظر أدناه) |
| `icon` | ملف صورة | أيقونة اختيارية (اختياري) |
| `order` | رقم | ترتيب الظهور |
| `is_active` | Boolean | إظهار/إخفاء العنصر بدون حذفه |
| `open_in_new_tab` | Boolean | فتح الرابط بتبويب جديد (مفيد للويب مع `type=url`) |

### قيم `route_key` المسموحة (الشاشات الثابتة)

```
home | categories | brands | shops | baskets | points | help | subscriptions
```

> اعرضها للأدمن بأسماء مفهومة بلغته (مثل "كل المتاجر"، "سلالي"، "النقاط والمكافآت") بدل القيمة التقنية.

---

## 2) مسارات الداشبورد (Admin API)

كل المسارات تحت `/api/admin` وتتطلب صلاحية `navmenuitem.*` (مضافة تلقائيًا للسوبر أدمن).

| Method | Endpoint | الوصف |
|--------|----------|--------|
| GET | `/api/admin/nav-menu-items` | قائمة العناصر مرتّبة حسب `order` |
| POST | `/api/admin/nav-menu-items` | إنشاء عنصر |
| GET | `/api/admin/nav-menu-items/{id}` | عنصر واحد |
| PUT/PATCH | `/api/admin/nav-menu-items/{id}` | تعديل عنصر |
| DELETE | `/api/admin/nav-menu-items/{id}` | حذف عنصر |
| POST | `/api/admin/nav-menu-items/reorder` | إعادة الترتيب بالسحب والإفلات |

### إنشاء عنصر — `POST /api/admin/nav-menu-items`

يُرسَل كـ `multipart/form-data` (بسبب حقل الأيقونة). أمثلة:

عنصر يفتح شاشة ثابتة (مثل "سلالي"):

```json
{
  "title": { "ar": "سلالي", "en": "My baskets" },
  "type": "route",
  "route_key": "baskets",
  "order": 4,
  "is_active": true
}
```

عنصر يفتح فئة معيّنة:

```json
{
  "title": { "ar": "إلكترونيات", "en": "Electronics" },
  "type": "category",
  "category_id": 12
}
```

عنصر يفتح صفحة من الـ Page Builder:

```json
{
  "title": { "ar": "عروض رمضان", "en": "Ramadan offers" },
  "type": "page",
  "page_id": 5
}
```

عنصر رابط خارجي:

```json
{
  "title": { "ar": "مدوّنتنا", "en": "Our blog" },
  "type": "url",
  "url": "https://example.com/blog",
  "open_in_new_tab": true
}
```

### قواعد التحقق المهمة

- `title.ar` و`title.en` مطلوبان.
- الحقل المرتبط بالنوع مطلوب: `type=page` ⇒ `page_id`، `type=category` ⇒ `category_id`، `type=brand` ⇒ `brand_id`، `type=url` ⇒ `url`، `type=route` ⇒ `route_key`.
- `icon` اختياري (صورة ≤ 2MB).
- عند التعديل بـ PUT مع رفع صورة، استخدم `multipart/form-data` وأضف `_method=PUT`.

### إعادة الترتيب — `POST /api/admin/nav-menu-items/reorder`

أرسل المعرّفات بالترتيب النهائي بعد السحب والإفلات:

```json
{ "ordered_ids": [3, 1, 5, 2, 4] }
```

الاستجابة:

```json
{ "status": true, "data": { "updated_count": 5 } }
```

---

## 3) تصميم الشاشة المقترحة في الداشبورد

1. جدول عناصر الشريط مرتّب حسب `order` مع مقبض سحب لإعادة الترتيب.
2. زر **"إضافة عنصر"** يفتح نموذجًا:
   - اسم عربي/إنجليزي.
   - قائمة **النوع** (شاشة ثابتة / فئة / ماركة / صفحة / رابط خارجي).
   - حسب النوع يظهر الحقل المناسب:
     - شاشة ثابتة ⇒ قائمة `route_key` بأسماء مفهومة.
     - فئة ⇒ منتقي فئات.
     - ماركة ⇒ منتقي ماركات.
     - صفحة ⇒ منتقي صفحات الـ Page Builder.
     - رابط ⇒ حقل URL + خيار "فتح بتبويب جديد".
   - أيقونة اختيارية.
   - مفتاح **تفعيل/إخفاء**.
3. مفتاح التفعيل مباشرة داخل الجدول لكل صف.

---

## 4) واجهة العرض (Web / Flutter)

**`GET /api/user/nav-menu`** — عام (بدون توكن). يرجّع العناصر **المفعّلة فقط** مرتّبة:

```json
{
  "status": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "title": "الفئات الرئيسية",
      "type": "route",
      "icon": null,
      "order": 1,
      "open_in_new_tab": false,
      "target": { "route_key": "categories" }
    },
    {
      "id": 8,
      "title": "إلكترونيات",
      "type": "category",
      "icon": "https://.../storage/icons/x.png",
      "order": 2,
      "open_in_new_tab": false,
      "target": { "category_id": 12, "name": "إلكترونيات" }
    },
    {
      "id": 9,
      "title": "عروض رمضان",
      "type": "page",
      "target": { "page_id": 5, "slug": "ramadan-offers" }
    },
    {
      "id": 10,
      "title": "مدوّنتنا",
      "type": "url",
      "open_in_new_tab": true,
      "target": { "url": "https://example.com/blog" }
    }
  ]
}
```

- `title` يرجع بلغة الطلب (حسب `Accept-Language`/الـ locale).
- كل عنصر يعطي **`target`** جاهزًا للتنقّل حسب `type`:
  - `route` ⇒ `target.route_key` → افتح الشاشة الثابتة المقابلة.
  - `category` ⇒ `target.category_id` → افتح صفحة الفئة (`GET /api/user/categories/{id}/page`).
  - `brand` ⇒ `target.brand_id`.
  - `page` ⇒ `target.slug` / `target.page_id` → افتح الصفحة من الـ Page Builder.
  - `url` ⇒ `target.url` (+ `open_in_new_tab` على الويب).

> خرائط `route_key` → شاشات التطبيق تُدار في الواجهة (ثابتة ومعروفة مسبقًا).

---

## 5) ملاحظات التشغيل (Backend/DevOps)

بعد سحب هذه التعديلات، شغّل:

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder   # يضيف صلاحيات navmenuitem.*
php artisan db:seed --class=NavMenuSeeder           # يزرع عناصر الشريط الافتراضية (آمن للتكرار)
```

على الـ installs الجديدة، `DatabaseSeeder` يشغّلهما تلقائيًا.
