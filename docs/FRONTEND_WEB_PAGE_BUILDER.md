# عرض الصفحات وصفحات الفئات (Web)

هذا المستند لفريق الويب. الباك إند يقدّم "صفحات" مبنية من **أقسام** (sections). كل قسم له:

- **`layout`**: طريقة عرض القسم (`slider` / `list` / `grid`)
- **`variant`**: شكل الكارد داخل القسم (`horizontal` / `vertical` / `square`)
- محتوى العناصر

التفاصيل: **`FRONTEND_SECTION_LAYOUT_AND_CARD.md`**.

صفحات الفئات = صفحة لكل فئة وتُستهلك بنفس المُعرّض.

---

## 1) المصادر (Endpoints)

| الغرض | Endpoint |
|-------|----------|
| أقسام صفحة عامة (رئيسية/عروض...) | `GET /api/user/sections?page_slug={slug}` (كما هو حاليًا) |
| صفحة فئة (بأي مستوى) | `GET /api/user/categories/{categoryId}/page` |

### استجابة صفحة الفئة

```json
{
  "status": true,
  "data": {
    "category": { "id": 1, "name": { "ar": "...", "en": "..." }, "children": [ ... ] },
    "sections": [ /* مصفوفة أقسام */ ]
  }
}
```

`sections` هنا بنفس شكل أقسام أي صفحة → استخدم **نفس مكوّن عرض الأقسام** في كل مكان.

---

## 2) شكل القسم (Section)

```json
{
  "id": 10,
  "name": { "ar": "سلايدر رئيسي", "en": "Main slider" },
  "type": "manual",
  "content_type": "banner",
  "manual_model": "banner",
  "api_method": null,
  "position": "before",
  "order": 1,
  "layout": "slider",
  "variant": "vertical",
  "display_type_id": 1,
  "is_default": false,
  "background_color": "#F7F7F7",
  "background_card_color": "#FFFFFF",
  "end_date": null,               // تاريخ انتهاء (flash sale فقط)
  "discount": null,               // خصم (flash sale فقط)
  "discount_type": null,          // نوع الخصم (flash sale فقط)
  "see_more": { "page_slug": "products", "params": { "category_id": 5 } },
  "show_when": {},                // شروط عرض — فاضي = يُعرض دائمًا
  "action": { "page_slug": "product_details" },
  "items": [ /* عناصر القسم */ ]
}
```

### قواعد العرض: `layout` ثم `variant`

1. **`layout`** يحدد تخطيط القسم:

| `layout` | العرض |
|----------|--------|
| `slider` | سلايدر أفقي (تمرير يمين/يسار) — الافتراضي |
| `list` | قائمة عمودية |
| `grid` | شبكة |

2. **`variant`** يحدد شكل كل كارد داخل التخطيط:

| `variant` | شكل الكارد |
|-----------|------------|
| `horizontal` | كارد أفقي عريض (الافتراضي) |
| `vertical` | كارد رأسي ضيق |
| `square` | كارد مربّع (مناسب للفئات) |

> إذا غاب `layout` في بيانات قديمة: اعتبروه `slider`.  
> `display_type_id` لنوع المحتوى فقط — ليس للتخطيط ولا لشكل الكارد.

### تمييز نوع القسم

| `type` | `manual_model` | `content_type` | `api_method` |
|--------|----------------|----------------|--------------|
| `manual` | مطلوب | مطلوب | `null` |
| `api` | `null` | مطلوب | مطلوب |

### فلاتر URL

- **`manual`**: `items` ثابتة — لا تتأثر بفلاتر URL.
- **`api`**: `items` ديناميكية — `merge(section.filters, url_query)` (URL override).

فلاتر `products`: `category_id`, `brand_id`, `shop_id`, `country_id`, `country`, `name`, `price_min`, `price_max`, `on_sale`, `in_stock_only`, `is_free_delivery`, `is_instant_delivery`, `attribute_values[]`, `type`, `search`, `sort_by` أو `sortField`+`sortOrder`.

> قائمة المنتجات المستقلة (`GET /api/user/products`) وجدول ما يعمل فعلياً: **`FRONTEND_WEB_FILTERS.md`**. ملاحظة: `country_id` يُقرأ هنا من query القسم، لكنه **غير مقبول** على FilterRequest الخاص بـ `/products` — هناك استخدم `country` كنص.

`display_type_id`: `1=banner`, `2=product`, `3=shop`, `4=basket`, `5=schedule-basket`, `6=brand`, `7=recipe`, `8=category`.

---

## 3) البانرات (صور عرضية / سلايدر إعلاني)

قسم البانرات يأتي كـ `type: "manual"` وعناصره بانرات:

```json
{
  "type": "manual",
  "layout": "slider",
  "variant": "horizontal",
  "items": [
    { "id": 1, "link": "https://...", "order": 0, "item": { "id": 12, "title": {...}, "image": "https://.../banner1.jpg" } }
  ]
}
```

- الصورة في `item.image` **عرضية (wide)** — اعرضها بنسبة أفقية (≈ 16:6 / 3:1) بعرض كامل الحاوية.
- عنصر واحد = **بانر إعلاني عريض ثابت**.
- عدة عناصر = **سلايدر أفقي** (auto-play اختياري + أسهم/نقاط تنقّل).
- عند الضغط: افتح `item.link` (رابط داخلي أو خارجي).

---

## 4) الأنواع الأخرى للعناصر

`items[].item` يحوي حقولًا موحّدة (`id`, `title`, `image`, `price`, `discount`, ...) حسب المحتوى:

- منتجات → كارد منتج (سعر/خصم/إضافة للسلة).
- متاجر/مطاعم → كارد متجر.
- فئات → كارد فئة (اضغط → `GET /categories/{id}/page`).
- ماركات/وصفات/سلات → كروت مناسبة.

### "عرض المزيد" والنقر

- `see_more` موجود → أظهر زر "عرض الكل" ووجّه إلى `see_more.page_slug` مع `see_more.params`.
- `action.page_slug` → مسار صفحة التفاصيل عند الضغط على عنصر.

---

## 5) ملاحظات

- تجاهل أي قسم `items` فارغة (لا تعرض عنوانًا بلا محتوى).
- استخدم `background_color` كخلفية للقسم و`background_card_color` كخلفية للكارد.
- صفحات الفئات بالتفصيل: **`FRONTEND_WEB_CATEGORY_PAGES.md`**
