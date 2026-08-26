# عرض الصفحات وصفحات الفئات (Flutter)

> **⚠️ مدمج في [`FRONTEND_FLUTTER_COMPLETE.md`](./FRONTEND_FLUTTER_COMPLETE.md)**

هذا المستند لفريق تطبيق الموبايل. الصفحات مبنية من **أقسام** (sections)، وكل فئة (بأي مستوى) صار لها **صفحتها الخاصة** تُستهلك بنفس مُعرّض الأقسام.

---

## 1) المصادر (Endpoints)

| الغرض | Endpoint |
|-------|----------|
| أقسام الصفحة الرئيسية / صفحات عامة | `GET /api/user/sections?page_slug={slug}` |
| صفحة فئة (بأي مستوى) | `GET /api/user/categories/{categoryId}/page` |

### استجابة صفحة الفئة

```json
{
  "status": true,
  "data": {
    "category": { "id": 1, "name": { "ar": "...", "en": "..." }, "children": [ ... ] },
    "sections": [ /* أقسام */ ]
  }
}
```

عند فتح شاشة فئة: نادِ `categories/{id}/page`، اعرض `category` في الهيدر، وابنِ `sections` عبر مُعرّض الأقسام الموحّد.

---

## 2) نموذج القسم (Section)

```json
{
  "id": 10,
  "name": { "ar": "...", "en": "..." },
  "type": "manual",              // manual | api
  "layout": "slider",            // slider | list | grid  → طريقة عرض القسم
  "variant": "vertical",         // horizontal | vertical | square → شكل الكارد
  "background_color": "#F7F7F7",
  "background_card_color": "#FFFFFF",
  "display_type_id": 3,
  "see_more": { "page_slug": "products", "params": { "category_id": 5 } },
  "action": { "page_slug": "product_details" },
  "items": [ ... ]
}
```

### `layout` ثم `variant`

1. **`layout`** → ويدجت القسم:
   - `slider` → `ListView(scrollDirection: Axis.horizontal)`
   - `list` → قائمة عمودية
   - `grid` → `GridView`
2. **`variant`** → شكل كارد العنصر داخل التخطيط (`horizontal` / `vertical` / `square`).

إذا غاب `layout`: اعتبروه `slider`. التفاصيل: `FRONTEND_SECTION_LAYOUT_AND_CARD.md`.

استخدم `background_color` لخلفية القسم و`background_card_color` لخلفية الكارد. `display_type_id` لنوع المحتوى فقط.

---

## 3) البانرات (Wide / Slider)

قسم البانرات `type: "manual"` وعناصره بانرات:

```json
{
  "type": "manual",
  "variant": "horizontal",
  "items": [
    { "id": 1, "link": "https://...", "order": 0, "item": { "id": 12, "image": "https://.../banner.jpg", "title": {...} } }
  ]
}
```

- `item.image` **صورة عرضية** — استخدم `AspectRatio` أفقي (≈ 16/6) بعرض كامل.
- عنصر واحد → بانر إعلاني ثابت.
- عدة عناصر → `PageView`/carousel أفقي (نقاط + auto-scroll اختياري).
- الضغط → افتح `item.link` (deep link داخلي أو رابط خارجي).

---

## 4) بقية العناصر

`items[].item` حقول موحّدة (`id`, `title`, `image`, `price`, `discount`, ...):

- منتج → كارد منتج (سعر/خصم/زر إضافة).
- متجر/مطعم → كارد متجر.
- فئة → كارد فئة، الضغط يفتح `categories/{id}/page` (تنقّل متداخل لأي عمق).
- ماركة/وصفة/سلة → كارد مناسب.

### التنقّل

- `see_more` موجود → زر "عرض الكل" يفتح `see_more.page_slug` مع `see_more.params`.
- `action.page_slug` → شاشة تفاصيل العنصر عند الضغط.

---

## 5) ملاحظات تنفيذية

- لا تعرض قسمًا `items` فارغ.
- احترم ترتيب `sections` كما يصل من الـ API (الأدمن يرتّبها).
- صفحة الفئة تبدأ بقسمَي "الفئات الفرعية" و"المنتجات"، وقد يضيف الأدمن فوقها أقسامًا إضافية (سلايدرات/بانرات/شبكات...) — لا تفترض عددًا ثابتًا للأقسام.
- خزّن كاش الاستجابة لكل `categoryId` لتحسين الأداء وأعد التحقق عند السحب للتحديث.
- صفحات الفئات بالتفصيل: **`FRONTEND_FLUTTER_CATEGORY_PAGES.md`**
