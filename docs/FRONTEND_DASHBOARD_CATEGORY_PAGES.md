# صفحات الفئات — إدارة كاملة (الداشبورد)

كل **فئة** (رئيسية أو فرعية بأي مستوى) = **صفحة**. الأدمن يتحكم بالأقسام داخلها (إضافة / تعديل / حذف / ترتيب)، بينما **حذف الصفحة نفسها** يتم فقط بحذف الفئة.

---

## القاعدتان الأساسيتان

| # | ماذا | كيف |
|---|------|-----|
| 1 | **إدارة الأقسام** داخل صفحة الفئة | من Page Builder — نفس صفحة المحتوى العادية |
| 2 | **حذف صفحة الفئة** | من شاشة **الفئات** — `DELETE /api/admin/categories/{id}` |

> لا يمكن حذف أو تعديل عنوان/slug صفحة فئة من `DELETE/PUT /api/admin/pages/{id}` — الباك إند يرفض ذلك (`422`).

---

## 1) الوصول لصفحة الفئة

### من قائمة الصفحات

`GET /api/admin/pages?type=category`

```json
{
  "id": 45,
  "title": "إلكترونيات",
  "slug": "category-12",
  "is_category_page": true,
  "category_id": 12,
  "can_delete_page": false,
  "can_edit_metadata": false,
  "sections_count": 4
}
```

### من قائمة الفئات

`GET /api/admin/categories` — كل فئة ترجع `page_id`:

```json
{
  "id": 12,
  "name": "إلكترونيات",
  "page_id": 45,
  "page_is_auto": true
}
```

**UX مقترح:** زر **"بناء الصفحة"** → `GET /api/admin/pages/{page_id}`

---

## 2) إدارة الأقسام (إضافة / تعديل / حذف)

افتح الصفحة: `GET /api/admin/pages/{page_id}`

```json
{
  "id": 45,
  "title": "إلكترونيات",
  "is_category_page": true,
  "category_id": 12,
  "can_delete_page": false,
  "can_edit_metadata": false,
  "delete_page_via": "DELETE /api/admin/categories/12",
  "sections": [
    {
      "id": 101,
      "section_id": 55,
      "content_type": "banner",
      "order": 1,
      "variant": "horizontal",
      "background_color": "#F5F5F5"
    }
  ]
}
```

### إضافة قسم

1. `GET /api/admin/pages/{page_id}/sliders` — اختيار سلاider من المكتبة
2. `POST /api/admin/pages/{page_id}/sections`

```json
{ "section_id": 12 }
```

أو إنشاء مباشر — انظر `FRONTEND_DASHBOARD_SLIDERS.md`.

### تعديل قسم

`PUT /api/admin/page-sections/{pageSectionId}`

```json
{
  "variant": "horizontal",
  "background_color": "#EEEEEE",
  "background_card_color": "#FFFFFF",
  "order": 2
}
```

> تعديل القسم **لا يحذف السلاider من المكتبة** — يغيّر ظهوره في هذه الصفحة فقط (الترتيب، الألوان، العنوان على مستوى الصفحة).

### حذف قسم من صفحة الفئة

`DELETE /api/admin/page-sections/{pageSectionId}`

- يزيل القسم **من هذه الصفحة فقط**
- الفئة وصفحتها **تبقيان**
- إذا كان القسم مرتبطًا بصفحات أخرى، يبقى السلاider في المكتبة

### ترتيب الأقسام

`POST /api/admin/page-sections/pages/{page_id}/reorder`

```json
{
  "sections": [
    { "id": 101, "order": 1, "position": "after" },
    { "id": 102, "order": 2, "position": "after" }
  ]
}
```

### معاينة

`GET /api/admin/page-sections/pages/{page_id}/preview`

---

## 3) حذف صفحة الفئة (عبر حذف الفئة)

صفحة الفئة **جزء من الفئة** — لا تُحذف من شاشة الصفحات.

| الإجراء | Endpoint | النتيجة |
|---------|----------|---------|
| حذف قسم | `DELETE /page-sections/{id}` | القسم يُزال من الصفحة |
| حذف صفحة فئة | `DELETE /pages/{id}` | **مرفوض 422** |
| حذف الفئة | `DELETE /categories/{id}` | تُحذف الفئة + صفحتها + روابط الأقسام |

**UX مقترح:**
- في Page Builder لصفحة فئة: **لا** تظهر زر "حذف الصفحة"
- في شاشة الفئات: زر "حذف الفئة" مع تحذير: *"سيتم حذف صفحة الفئة وأقسامها أيضًا"*
- استخدم `can_delete_page` و `delete_page_via` من API لإظهار/إخفاء الأزرار

---

## 4) دورة حياة صفحة الفئة

```
إنشاء فئة  →  تُنشأ صفحتها تلقائيًا + قسمان افتراضيان (فرعية + منتجات)
تعديل اسم الفئة  →  يتحدّث عنوان الصفحة
إضافة/تعديل/حذف أقسام  →  من Page Builder
حذف الفئة  →  تُحذف الصفحة وأقسامها
```

القسمان الافتراضيان (يُنشآن تلقائيًا):
1. **الأقسام الفرعية** — `api_method: categories`, `filters.parent_id`
2. **المنتجات** — `api_method: products`, `filters.category_id` (شجرة الفئة)

يمكن حذفهما أو تعديلهما مثل أي قسم آخر.

---

## 5) تصميم الشاشة (ملخّص)

### تبويب "صفحات الفئات" (`type=category`)

- قائمة كل صفحات الفئات
- **بدون** زر "إنشاء صفحة" أو "حذف صفحة"
- زر "فتح / بناء" → Page Builder

### داخل Page Builder (صفحة فئة = صفحة عادية للأقسام)

| زر | Endpoint |
|----|----------|
| إضافة قسم | `POST /pages/{id}/sections` |
| تعديل قسم | `PUT /page-sections/{id}` |
| حذف قسم | `DELETE /page-sections/{id}` |
| ترتيب | `POST /page-sections/pages/{id}/reorder` |
| معاينة | `GET /page-sections/pages/{id}/preview` |

---

## 6) أخطاء متوقعة

| HTTP | السبب |
|------|--------|
| `422` | `DELETE /pages/{id}` على صفحة فئة |
| `422` | `PUT /pages/{id}` لتعديل title/slug صفحة فئة |

رسالة مثال:
```json
{
  "message": "Category pages cannot be deleted directly. Delete the category from Categories to remove its page."
}
```

---

## 7) مراجع

- Page Builder عام: `FRONTEND_DASHBOARD_PAGE_BUILDER.md`
- مكتبة السلاiderات: `FRONTEND_DASHBOARD_SLIDERS.md`
- Web: `FRONTEND_WEB_CATEGORY_PAGES.md`
- Flutter: `FRONTEND_FLUTTER_CATEGORY_PAGES.md`
