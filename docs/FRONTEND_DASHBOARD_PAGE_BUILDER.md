# Page Builder موحّد + صفحة الفئة التلقائية (تعديلات الداشبورد)

هذا المستند موجّه لفريق الداشبورد (React). الباك إند صار يدعم:

1. **CRUD كامل للصفحات** (إنشاء أي صفحة من الداشبورد).
2. **Endpoint موحّد** لإضافة قسم داخل صفحة بنداء واحد (بدل نداءين منفصلين).
3. **نوع قسم `categories`** (يدوي واختيار من API).
4. **صفحة تلقائية لكل فئة (بأي مستوى)** — تُنشأ فور إنشاء الفئة، تظهر في قائمة الصفحات، ويمكن إضافة أقسام (سلايدرات/بانرات/شبكات...) لها.
5. **قسم بانرات يختار من مكتبة البانرات الموجودة** (لا إنشاء بانر داخل الصفحة).

> كل التعديلات **إضافية** ولا تكسر الـ endpoints القديمة (`sections`, `page-sections`, `banners`) — تبقى شغّالة كما هي.

> **ملاحظة مهمة:** لا يوجد كيان "سلايدر" مستقل. **القسم (Section)** هو الكيان الوحيد.
> - **`layout`**: طريقة عرض القسم → `slider` \| `list` \| `grid`
> - **`variant`**: شكل الكارد جوا القسم → `horizontal` \| `vertical` \| `square`
>
> التفاصيل وخطوات الفورم: **`FRONTEND_SECTION_LAYOUT_AND_CARD.md`**. إدارة الأقسام: `FRONTEND_DASHBOARD_SLIDERS.md`.

---

## المشكلة التي نحلّها

سابقًا لنشر محتوى على صفحة، الأدمن لازم يعمل **خطوتين** بمكانين مختلفين:

1. ينشئ **قسم** (`POST /sections`) — اسم + نوع + عناصر.
2. يربط القسم بالصفحة (`POST /page-sections`) — ترتيب + شكل + ألوان.

هذا سبب الشكوى "صعب الاستخدام". الحل: الداشبورد يتعامل مع مفهوم واحد فقط — **"صفحة فيها أقسام"** — والباك إند يعمل الخطوتين بالخلفية عبر نداء واحد.

---

## 1) إدارة الصفحات (Page CRUD)

كل المسارات تحت `/api/admin` وتتطلب صلاحية `page.*` (مضافة تلقائيًا للسوبر أدمن).

| Method | Endpoint | الوصف |
|--------|----------|--------|
| GET | `/api/admin/pages` | قائمة الصفحات (بحث + ترقيم) |
| POST | `/api/admin/pages` | إنشاء صفحة |
| GET | `/api/admin/pages/{page}` | صفحة مع كل أقسامها |
| PUT/PATCH | `/api/admin/pages/{page}` | تعديل صفحة |
| DELETE | `/api/admin/pages/{page}` | حذف صفحة |

### إنشاء صفحة — `POST /api/admin/pages`

```json
{
  "title": "صفحة العروض",
  "slug": "offers",
  "filters": { "category_id": 5 }
}
```

- `title` مطلوب.
- `slug` اختياري — إذا لم يُرسل يُشتق تلقائيًا من `title`. يجب أن يكون فريدًا.
- `filters` اختياري (كائن).

### قائمة الصفحات — `GET /api/admin/pages`

Query params: `search`, `page`, `per_page`, `sort_field`, `sort_order`, بالإضافة إلى فلاتر جديدة:

| Param | القيم | الوصف |
|-------|-------|--------|
| `type` | `content` \| `category` | `content` = الصفحات العادية فقط (رئيسية/عروض...). `category` = صفحات الفئات فقط. بدون الباراميتر تُرجَّع الكل. |
| `category_id` | عدد | ترجّع صفحة فئة محددة. |

كل عنصر يرجّع:

- `sections_count` — عدد الأقسام.
- `is_category_page` — `true` إذا كانت الصفحة تخصّ فئة (مولّدة تلقائيًا).
- `category_id` — مُعرّف الفئة المرتبطة (أو `null`).

> اقتراح UX: ... صفحات الفئات: **لا** زر إنشاء/حذف صفحة — استخدم `can_delete_page` و `can_edit_metadata`. حذف الفئة من شاشة الفئات. **نعم** إدارة كاملة للأقسام داخل الصفحة. انظر `FRONTEND_DASHBOARD_CATEGORY_PAGES.md`.

### تفاصيل صفحة — `GET /api/admin/pages/{page}`

يرجّع الصفحة مع مصفوفة `sections` (مرتّبة حسب `order`)، كل قسم بنفس شكل `AdminOneResource` (نفس ما يرجّعه `page-sections`).

---

## 2) إضافة قسم داخل صفحة

### أ) اختيار قسم موجود (المسار الموصى به)

1. **`GET /api/admin/pages/{pageId}/sliders`** — يجلب **كل الأقسام** الموجودة (بحث + `content_type`).
2. **`POST /api/admin/pages/{pageId}/sections`** مع `section_id` فقط:

```json
{ "section_id": 12 }
```

### ب) إنشاء قسم جديد وربطه (نداء واحد — اختياري)

**`POST /api/admin/pages/{page}/sections`** — ينشئ `Section` ويربطه بالصفحة. يتطلب صلاحية `pagesection.create`.

> للإنشاء المسبق بدون صفحة، استخدم `POST /api/admin/sections` — انظر `FRONTEND_DASHBOARD_SLIDERS.md`.

### الحقول

| الحقل | مطلوب | القيم |
|-------|-------|-------|
| `type` | نعم | `manual` \| `api` |
| `name` | لا | `{ "ar": "...", "en": "..." }` |
| `manual_model` | مطلوب إذا `type=manual` | `banner` \| `product` \| `shop` \| `restaurant` \| `brand` \| `recipe` \| `basket` \| `category` |
| `content_type` | لا (بديل أبسط) | نفس القيم أعلاه — يُحوَّل تلقائيًا إلى `manual_model` أو `api_method` |
| `item_ids` | مطلوب إذا `type=manual` | `[{ "item_id": 1, "link": null, "order": 0 }]` |
| `api_method` | مطلوب إذا `type=api` | انظر جدول أنواع API أدناه |
| `filters` | لا | فلاتر العرض (`category_id`, `brand_id`, `shop_id`, `type`, ...) |
| `position` | لا (افتراضي `after`) | `before` \| `after` |
| `order` | لا (افتراضي = آخر ترتيب + 1) | عدد ≥ 1 |
| `layout` | لا (افتراضي `slider`) | `slider` \| `list` \| `grid` — **طريقة عرض القسم** |
| `variant` | لا (افتراضي `horizontal`) | `horizontal` \| `vertical` \| `square` — **شكل الكارد** |
| `background_color` | لا | نص |
| `background_card_color` | لا | نص |
| `show_when` | لا | كائن ظهور شرطي (مثل `{ "category_id": 5 }`) |

> ملاحظة: للأقسام من نوع `api`، **الفلاتر الفعّالة للعرض تؤخذ من هذا الـ `filters`** (على مستوى ربط الصفحة)، وليس من فلاتر الـ Section نفسه.

### مثال — قسم API منتجات رائجة

```json
{
  "type": "api",
  "name": { "ar": "المنتجات الرائجة", "en": "Trending" },
  "api_method": "products",
  "filters": { "type": "trend" },
  "layout": "slider",
  "variant": "vertical"
}
```

### مثال — قسم بانرات (اختيار من المكتبة)

قسم البانرات **يدوي** ويختار من **البانرات الموجودة مسبقًا** — لا يُنشأ بانر جديد داخل الصفحة.

**خطوات الواجهة:**

1. جِب البانرات من `GET /api/admin/banners` (يدعم البحث والترقيم — استخدمه لفلترة القائمة عند كثرتها).
2. البانر **صورة عرضية (wide)**؛ اعرض `image_url` بنسبة أفقية (≈ 16:6 أو 3:1) في شبكة الاختيار وفي المعاينة.
3. الأدمن يختار بانرًا واحدًا أو أكثر ويرتّبهم (`order`).
4. أرسل المعرّفات المختارة في `item_ids`.

```json
{
  "type": "manual",
  "name": { "ar": "سلايدر رئيسي", "en": "Main slider" },
  "manual_model": "banner",
  "item_ids": [
    { "item_id": 12, "order": 0 },
    { "item_id": 13, "order": 1 }
  ],
  "layout": "slider",
  "variant": "horizontal"
}
```

- **بانر واحد** = إعلان عريض ثابت.
- **عدة بانرات** + `layout: slider` = سلايدر أفقي (تمرير يمين/يسار).

> `link` لكل بانر يؤخذ تلقائيًا من البانر نفسه في استجابة المستخدم، فلا حاجة لإرساله يدويًا.

### الاستجابة

يرجّع القسم المُنشأ بشكل `AdminOneResource` (فيه `id`, `section_id`, `page_id`, `order`, `variant`, `display_type_id`, `filters` ...). استخدم هذا مباشرةً لتحديث قائمة الأقسام في الواجهة دون إعادة تحميل.

### الترتيب وإعادة الترتيب (موجود مسبقًا)

- إعادة الترتيب بالسحب والإفلات: `POST /api/admin/page-sections/pages/{page}/reorder`
- معاينة الصفحة كما يراها التطبيق: `GET /api/admin/page-sections/pages/{page}/preview`
- تعديل/حذف قسم مرتبط: `PUT/DELETE /api/admin/page-sections/{id}`

---

## 3) أنواع الأقسام

### يدوي (`type=manual`) — `manual_model`

`banner`, `product`, `shop`, `brand`, `recipe`, `basket`, **`category` (جديد)**.

عند اختيار عناصر يدويًا، أرسل `item_ids` (الباك إند يحلّ `item_type` تلقائيًا من نوع القسم).

### API (`type=api`) — `api_method`

| `api_method` | يعرض | الفلاتر المدعومة وقت التشغيل |
|--------------|-------|------------------------------|
| `products` | منتجات | `category_id` (شجرة), `brand_id`, `shop_id`, `type`, `sort_by`, `price_min`, `price_max` |
| `categories` **(جديد)** | فئات | `parent_id` (الأبناء), `brand_id`, `shop_id`, `type`, `sort_by` |
| `shops` | متاجر | `brand_id` |
| `restaurants` | مطاعم | `brand_id` (+ `is_restaurant=true` تلقائيًا) |
| `brands` | ماركات | — |
| `recipes` | وصفات | — |
| `baskets` | سلات | — |
| `schedule-basket` | سلات مجدولة | — |
| `suggested_products` | مقترحة | `category_id`, `brand_id`, `shop_id`, `type` |
| `suggested_shops` | متاجر مقترحة | `brand_id` |
| `suggested_baskets` | سلات مقترحة | — |

> اقتراح UX: اعرض هذه الأنواع بأسماء بلغة المستخدم (مثل "شبكة منتجات"، "قائمة فئات"، "سلايدر بانرات") بدل القيم التقنية.

---

## 4) صفحة الفئة = صفحة كاملة (لكل فئة بأي مستوى)

> **التوثيق الكامل:** `FRONTEND_DASHBOARD_CATEGORY_PAGES.md`

عند إنشاء **أي فئة** (جذر أو فرعية بأي عمق) يُنشئ الباك إند لها **صفحة خاصة بها** تلقائيًا:

- تظهر في قائمة الصفحات (`is_category_page: true`, `category_id`).
- `slug = category-{categoryId}`.
- تُبذَر بقسمين افتراضيين: **الأقسام الفرعية** + **منتجات الفئة (الشجرة كاملة)**.
- يمكن للأدمن إضافة أقسام (سلايدرات/بانرات/شبكات...) فوقها، وترتيبها، وحذفها.
- تعديل اسم الفئة يُحدّث عنوان الصفحة تلقائيًا.
- حذف الفئة يحذف صفحتها وأقسامها تلقائيًا.

> لا يوجد إنشاء/حذف يدوي لصفحة الفئة من الداشبورد — تتبع الفئة. الأدمن فقط **يحرّر أقسامها**.

### كيف يصل الأدمن لصفحة الفئة؟

- سجل الفئة (`GET /api/admin/categories` و `/categories/{id}`) صار يرجّع `page_id`.
- من صفحة الفئات: زر "بناء الصفحة" → افتح `GET /api/admin/pages/{page_id}` وتعامل معها كأي صفحة (إضافة قسم / ترتيب / معاينة).

### Endpoint المستخدم (Flutter/Web)

**`GET /api/user/categories/{categoryId}/page`**

يرجّع صفحة تلك الفئة تحديدًا (وإن لم توجد لأي سبب يرجع القالب المشترك `category-details` كخطة بديلة):

```json
{
  "status": true,
  "data": {
    "category": { "id": 1, "name": {"ar":"...","en":"..."}, "children": [ ... ] },
    "sections": [
      { "type": "api", "name": {...}, "variant": "square", "items": [ /* الفئات الفرعية */ ] },
      { "type": "api", "name": {...}, "variant": "vertical", "items": [ /* منتجات الفئة (شجرة) */ ] }
    ]
  }
}
```

- `sections` بنفس شكل مصفوفة أقسام الصفحات العادية → يمكن إعادة استخدام نفس مُعرّض الأقسام في التطبيق.

---

## 5) تصميم شاشة Page Builder المقترحة

1. قائمة **الصفحات** (`GET /pages`) مع تبويبين: "صفحات المحتوى" (`type=content`) و"صفحات الفئات" (`type=category`)، وزر "إنشاء صفحة" في تبويب المحتوى فقط.
2. عند فتح صفحة (`GET /pages/{id}`): اعرض أقسامها مرتّبة.
3. زر **"إضافة قسم"**:
   - **`GET /pages/{id}/sliders`** — قائمة كل الأقسام الموجودة (بحث + فلترة).
   - اختيار قسم → `POST /pages/{id}/sections` مع `section_id`.
   - أو "إنشاء قسم جديد" → `POST /sections` ثم ربطه.
4. سحب وإفلات لإعادة الترتيب → `POST /page-sections/pages/{id}/reorder`.
5. معاينة مباشرة → `GET /page-sections/pages/{id}/preview`.

> دُمجت "الأقسام" و"ربط الأقسام بالصفحات" في تجربة واحدة داخل الصفحة. أخفِ شاشات إنشاء الـ Section / Page Section المستقلة عن الأدمن العادي.

---

## ملاحظات للتشغيل (Backend/DevOps)

بعد سحب هذه التعديلات، شغّل:

```bash
php artisan migrate                                     # pages.category_id + sections.variant/colors
php artisan db:seed --class=RolePermissionSeeder        # صلاحيات page.*
php artisan db:seed --class=CategoryDetailsPageSeeder   # قالب category-details الاحتياطي (آمن للتكرار)
php artisan db:seed --class=CategoryPagesBackfillSeeder # ينشئ صفحة لكل فئة قائمة (آمن للتكرار)
```

- الفئات الجديدة تُنشئ صفحتها تلقائيًا عبر `CategoryObserver` (لا حاجة لأي نداء إضافي).
- الفئات القديمة تُعالَج بـ `CategoryPagesBackfillSeeder`.
- (على الـ installs الجديدة، `DatabaseSeeder` يشغّل هذه السيدرز تلقائيًا.)
