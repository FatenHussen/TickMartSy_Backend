# Page Builder موحّد + صفحة الفئة التلقائية (تعديلات الداشبورد)

هذا المستند موجّه لفريق الداشبورد (React). الباك إند صار يدعم:

1. **CRUD كامل للصفحات** (إنشاء أي صفحة من الداشبورد).
2. **Endpoint موحّد** لإضافة قسم داخل صفحة بنداء واحد (بدل نداءين منفصلين).
3. **نوع قسم `categories`** (يدوي واختيار من API).
4. **صفحة تلقائية لكل فئة** بدون أي بيانات مكرّرة لكل فئة.

> كل التعديلات **إضافية** ولا تكسر الـ endpoints القديمة (`sections`, `page-sections`, `banners`) — تبقى شغّالة كما هي.

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

Query params: `search`, `page`, `per_page`, `sort_field`, `sort_order`.

كل عنصر يرجّع `sections_count` لعرض عدد الأقسام.

### تفاصيل صفحة — `GET /api/admin/pages/{page}`

يرجّع الصفحة مع مصفوفة `sections` (مرتّبة حسب `order`)، كل قسم بنفس شكل `AdminOneResource` (نفس ما يرجّعه `page-sections`).

---

## 2) الـ Endpoint الموحّد: إضافة قسم داخل صفحة

**`POST /api/admin/pages/{page}/sections`** — ينشئ `Section` ويربطه بالصفحة في transaction واحدة. يتطلب صلاحية `pagesection.create`.

### الحقول

| الحقل | مطلوب | القيم |
|-------|-------|-------|
| `type` | نعم | `manual` \| `api` |
| `name` | لا | `{ "ar": "...", "en": "..." }` |
| `manual_model` | مطلوب إذا `type=manual` | `banner` \| `product` \| `shop` \| `brand` \| `recipe` \| `basket` \| `category` |
| `item_ids` | مطلوب إذا `type=manual` | `[{ "item_id": 1, "link": null, "order": 0 }]` |
| `api_method` | مطلوب إذا `type=api` | انظر جدول أنواع API أدناه |
| `filters` | لا | فلاتر العرض (`category_id`, `brand_id`, `shop_id`, `type`, ...) |
| `position` | لا (افتراضي `after`) | `before` \| `after` |
| `order` | لا (افتراضي = آخر ترتيب + 1) | عدد ≥ 1 |
| `variant` | لا (افتراضي `horizontal`) | `horizontal` \| `vertical` \| `square` |
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
  "variant": "vertical"
}
```

### مثال — قسم يدوي بانرات (سلايدر)

```json
{
  "type": "manual",
  "name": { "ar": "سلايدر رئيسي", "en": "Main slider" },
  "manual_model": "banner",
  "item_ids": [
    { "item_id": 12, "order": 0 },
    { "item_id": 13, "order": 1 }
  ],
  "variant": "horizontal"
}
```

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
| `brands` | ماركات | — |
| `recipes` | وصفات | — |
| `baskets` | سلات | — |
| `schedule-basket` | سلات مجدولة | — |
| `suggested_products` | مقترحة | `category_id`, `brand_id`, `shop_id`, `type` |
| `suggested_shops` | متاجر مقترحة | `brand_id` |
| `suggested_baskets` | سلات مقترحة | — |

> اقتراح UX: اعرض هذه الأنواع بأسماء بلغة المستخدم (مثل "شبكة منتجات"، "قائمة فئات"، "سلايدر بانرات") بدل القيم التقنية.

---

## 4) صفحة الفئة التلقائية

لا يُنشأ سجل صفحة لكل فئة. يوجد **قالب مشترك واحد** (`slug = category-details`) يُطبّق على أي فئة وقت الطلب.

### Endpoint المستخدم (Flutter/Web)

**`GET /api/user/categories/{categoryId}/page`**

يرجّع:

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

- قسم **الأقسام الفرعية**: أبناء الفئة الحالية (يستهلك `parent_id` تلقائيًا).
- قسم **المنتجات**: منتجات الفئة وكل الفئات التابعة لها (يستهلك `category_id`).
- `sections` بنفس شكل مصفوفة أقسام الصفحات العادية → يمكن إعادة استخدام نفس مُعرّض الأقسام في التطبيق.

### التحكم من الداشبورد

القالب `category-details` صفحة عادية في الـ Page Builder. يمكن للأدمن:

- إضافة أقسام إضافية لها (بانر، نص، ...) عبر `POST /api/admin/pages/{page}/sections`.
- استخدام `show_when: { "category_id": X }` على قسم لإظهاره لفئة معيّنة فقط.

أي فئة جديدة تعمل مباشرةً دون أي إعداد إضافي.

---

## 5) تصميم شاشة Page Builder المقترحة

1. قائمة **الصفحات** (`GET /pages`) مع زر "إنشاء صفحة".
2. عند فتح صفحة (`GET /pages/{id}`): اعرض أقسامها مرتّبة.
3. زر واحد **"إضافة قسم"** يفتح مُعالجًا:
   - اختيار النوع (بأسماء بلغة المستخدم).
   - إن كان يدويًا: اختيار العناصر. إن كان API: اختيار الفلاتر.
   - ضبط الشكل (variant / ألوان / ظهور شرطي).
   - حفظ → نداء واحد `POST /pages/{id}/sections`.
4. سحب وإفلات لإعادة الترتيب → `POST /page-sections/pages/{id}/reorder`.
5. معاينة مباشرة → `GET /page-sections/pages/{id}/preview`.

> يُفضّل دمج "الأقسام" و"سلايدرات الصفحات" في تجربة واحدة داخل الصفحة، وإخفاء إنشاء الـ Section المستقل عن الأدمن العادي.

---

## ملاحظات للتشغيل (Backend/DevOps)

بعد سحب هذه التعديلات، شغّل:

```bash
php artisan db:seed --class=RolePermissionSeeder      # يضيف صلاحيات page.*
php artisan db:seed --class=CategoryDetailsPageSeeder # ينشئ قالب category-details (آمن للتكرار)
```

(على الـ installs الجديدة، `DatabaseSeeder` يشغّلهما تلقائيًا.)
