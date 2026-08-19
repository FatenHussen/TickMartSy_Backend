# عرض الصفحات وصفحات الفئات (Web)

هذا المستند لفريق الويب. الباك إند يقدّم "صفحات" مبنية من **أقسام** (sections). كل قسم له نوع عرض (`variant`) وشكل كارد ومحتوى. صفحات الفئات صارت **صفحة لكل فئة** (بأي مستوى) وتُستهلك بنفس المُعرّض.

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
  "type": "manual",              // manual | api
  "position": "main",
  "order": 1,
  "variant": "horizontal",       // horizontal | vertical | square
  "background_color": "#F7F7F7",
  "background_card_color": "#FFFFFF",
  "display_type_id": 3,           // شكل الكارد (اختياري)
  "end_date": null,               // تاريخ انتهاء (flash sale فقط)
  "discount": null,               // خصم (flash sale فقط)
  "discount_type": null,          // نوع الخصم (flash sale فقط)
  "see_more": { "page_slug": "products", "params": { "category_id": 5 } },
  "show_when": {},                // شروط عرض — فاضي = يُعرض دائمًا
  "action": { "page_slug": "product_details" },
  "items": [ /* عناصر القسم */ ]
}
```

### قواعد العرض حسب `variant`

- `horizontal` → **سلايدر أفقي** مع تمرير يمين/يسار (هذا هو "السلايدر").
- `vertical` → شبكة/قائمة رأسية.
- `square` → شبكة مربّعات (مناسبة للفئات).

> شكل الكارد يُشتق من `display_type_id` إن رغبت بتوحيد الأشكال؛ وإلا اعتمد `variant`.

---

## 3) البانرات (صور عرضية / سلايدر إعلاني)

قسم البانرات يأتي كـ `type: "manual"` وعناصره بانرات:

```json
{
  "type": "manual",
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
