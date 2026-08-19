# إدارة الأقسام (Sections) — الداشبورد

> **ملاحظة مهمة:** لا يوجد كيان "سلايدر" مستقل. **القسم (Section)** هو الكيان الوحيد، و"السلايدر" هو مجرد **شكل عرض** (`variant: horizontal`) — أي شريط أفقي فيه كاردات تتحرك يمين/يسار.

---

## المفهوم

| المصطلح | المعنى |
|---------|--------|
| **قسم (Section)** | الكيان الأساسي — اسم + نوع محتوى + شكل عرض + ألوان |
| **سلايدر** | قسم بـ `variant: horizontal` — شريط أفقي فيه كاردات (منتجات، متاجر، سلل، مزودين خدمة...) |
| **بانر** | قسم يدوي محتواه بانرات (صور عرضية إعلانية) |
| **شبكة** | قسم بـ `variant: vertical` أو `square` — عرض كشبكة أو قائمة |

القسم يمكن أن يحتوي على **أي نوع محتوى**: منتجات، متاجر، شوبات، مطاعم، مزودين خدمة، سلل، ماركات، فئات، وصفات، بانرات — أي شيء يمكن طلب أوردر عليه أو عرضه.

---

## 1) إنشاء قسم — `POST /api/admin/sections`

صلاحية: `section.create`

### الحقول

| الحقل | مطلوب | الوصف |
|-------|-------|--------|
| `name.ar` / `name.en` | نعم | اسم القسم (يظهر كعنوان) |
| `content_type` | لا | بديل مُبسّط — يُحوَّل تلقائيًا إلى `manual_model`/`api_method` (nullable) |
| `type` | **نعم** | `manual` (اختيار يدوي) أو `api` (تلقائي بالفلترة) |
| `variant` | لا | `horizontal` (سلايدر) \| `vertical` (شبكة) \| `square` (مربعات). افتراضي `horizontal` |
| `background_color` | لا | لون خلفية القسم |
| `background_card_color` | لا | لون خلفية الكارد |
| `item_ids` | مطلوب إذا `type=manual` | العناصر المختارة يدويًا |
| `filters` | لا | فلاتر إذا `type=api` |
| `page_id` | **ممنوع** | لا تُرسل — القسم يُنشأ مستقلًا ويُربط بالصفحة من داخلها |

### أنواع المحتوى (`content_type`)

| القيمة | يدوي (`manual`) | تلقائي (`api`) | مصدر الاختيار في الداشبورد |
|--------|-----------------|----------------|----------------------------|
| `banner` | بانرات محددة | — | `GET /api/admin/banners` (صورة **عرضية**) |
| `product` | منتجات محددة | منتجات بالفلترة | `GET /api/admin/products` |
| `shop` | متاجر محددة | متاجر بالفلترة | `GET /api/admin/shops?is_restaurant=0` |
| `restaurant` | مطاعم محددة | مطاعم بالفلترة | `GET /api/admin/shops?is_restaurant=1` |
| `brand` | ماركات | ماركات | `GET /api/admin/brands` |
| `category` | فئات | فئات | `GET /api/admin/categories` |
| `recipe` | وصفات | وصفات | `GET /api/admin/recipes` |
| `basket` | سلات | سلات | `GET /api/admin/baskets` |

### أشكال العرض (`variant`)

| القيمة | الشكل | الاستخدام النموذجي |
|--------|-------|--------------------|
| `horizontal` | شريط أفقي (سلايدر) — كاردات تتحرك يمين/يسار | منتجات رائجة، متاجر قريبة، عروض الأسبوع |
| `vertical` | شبكة أو قائمة رأسية | منتجات الفئة، نتائج بحث |
| `square` | شبكة مربّعات | فئات دائرية، ماركات |

### مثال — قسم منتجات (سلايدر أفقي)

```json
{
  "name": { "ar": "عروض الأسبوع", "en": "Weekly deals" },
  "content_type": "product",
  "type": "manual",
  "item_ids": [
    { "item_id": 10, "order": 0 },
    { "item_id": 22, "order": 1 }
  ],
  "variant": "horizontal",
  "background_color": "#F5F5F5",
  "background_card_color": "#FFFFFF"
}
```

### مثال — قسم مطاعم تلقائي

```json
{
  "name": { "ar": "مطاعم قريبة", "en": "Nearby restaurants" },
  "content_type": "restaurant",
  "type": "api",
  "filters": { "type": "most_popular" },
  "variant": "horizontal",
  "background_color": "#FFF8F0",
  "background_card_color": "#FFFFFF"
}
```

### مثال — قسم بانرات

```json
{
  "name": { "ar": "إعلانات رئيسية", "en": "Main ads" },
  "content_type": "banner",
  "type": "manual",
  "item_ids": [
    { "item_id": 5, "order": 0 },
    { "item_id": 8, "order": 1 }
  ],
  "variant": "horizontal",
  "background_color": "#EEEEEE",
  "background_card_color": "#FFFFFF"
}
```

- اختر البانرات من `GET /api/admin/banners` — **لا تنشئ بانرًا جديدًا هنا**.
- اعرض الصورة بنسبة أفقية (wide) في شبكة الاختيار.

---

## 2) إدارة الأقسام (CRUD)

| Method | Endpoint | الوصف |
|--------|----------|--------|
| GET | `/api/admin/sections` | قائمة كل الأقسام (بحث + فلترة) |
| POST | `/api/admin/sections` | إنشاء قسم |
| GET | `/api/admin/sections/{id}` | تفاصيل + عناصر |
| PUT/PATCH | `/api/admin/sections/{id}` | تعديل |
| DELETE | `/api/admin/sections/{id}` | حذف |

**Query params للقائمة:** `search`, `content_type`, `type`, `is_active`, `page`, `per_page`

**استجابة القائمة** (كل عنصر):

```json
{
  "id": 12,
  "name": { "ar": "...", "en": "..." },
  "content_type": "product",
  "type": "manual",
  "variant": "horizontal",
  "background_color": "#F5F5F5",
  "background_card_color": "#FFFFFF",
  "pages_count": 3
}
```

---

## 3) إضافة قسم لصفحة — من داخل الصفحة

عند فتح صفحة (`GET /api/admin/pages/{id}`) → زر **"إضافة قسم"**:

### أ) جلب كل الأقسام للاختيار

**`GET /api/admin/pages/{pageId}/sliders`**

Query: `search`, `content_type`, `type`, `page`, `per_page`

يرجّع **كل الأقسام** الموجودة (ليس فقط المرتبطة بالصفحة).

### ب) ربط قسم موجود

**`POST /api/admin/pages/{pageId}/sections`**

```json
{
  "section_id": 12
}
```

- ينسخ ألوان و`variant` من القسم تلقائيًا.
- يمكن تجاوزها: `"background_color"`, `"background_card_color"`, `"variant"`, `"order"`.

### ج) إنشاء وربط بنداء واحد (اختياري)

```json
{
  "type": "manual",
  "content_type": "product",
  "name": { "ar": "...", "en": "..." },
  "item_ids": [ ... ],
  "variant": "horizontal",
  "background_color": "#F5F5F5",
  "background_card_color": "#FFFFFF"
}
```

---

## 4) تصميم الواجهة المقترح

### شاشة "الأقسام"

- قائمة الأقسام + زر "إنشاء قسم".
- النموذج:
  1. الاسم (ar/en)
  2. نوع المحتوى (منتجات / مطاعم / متاجر / بانرات / فئات / ماركات / وصفات / سلل)
  3. شكل العرض (سلايدر أفقي / شبكة رأسية / مربعات)
  4. يدوي أو تلقائي
  5. اختيار العناصر (مع بحث وفلترة) أو ضبط الفلاتر
  6. لون الخلفية + لون الكارد
  7. **بدون** اختيار صفحة

### داخل الصفحة — "إضافة قسم"

1. افتح قائمة من `GET /pages/{id}/sliders` (بحث + فلترة حسب `content_type`).
2. الأدمن يختار قسمًا → `POST /pages/{id}/sections` مع `section_id`.
3. أو زر "إنشاء قسم جديد" يفتح نفس نموذج الأقسام ثم يعود ويختاره.

---

## 5) ملاحظات

- نفس القسم يمكن إضافته لعدة صفحات (`pages_count` يوضح ذلك).
- تعديل القسم ينعكس على **كل الصفحات** التي تستخدمه.
- حذف القسم يحذف روابطه من الصفحات (via cascade على `page_sections`).
- بعد التحديث شغّل: `php artisan migrate` (يضيف `variant`/الألوان على جدول `sections`).
