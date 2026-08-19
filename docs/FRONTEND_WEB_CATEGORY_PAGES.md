# صفحات الفئات (Web)

كل فئة (رئيسية أو فرعية) لها **صفحة محتوى خاصة** تُبنى من **أقسام** (sections). استخدم endpoint واحد لأي مستوى في شجرة الفئات.

---

## Endpoint

**`GET /api/user/categories/{categoryId}/page`**

```json
{
  "status": true,
  "data": {
    "category": {
      "id": 12,
      "name": { "ar": "إلكترونيات", "en": "Electronics" },
      "children": [ /* فئات فرعية */ ]
    },
    "sections": [ /* أقسام مرتّبة */ ]
  }
}
```

---

## التنقّل

| الحدث | الإجراء |
|-------|---------|
| فتح فئة من القائمة/الكارد | `GET /categories/{id}/page` |
| فتح فئة فرعية من قسم `categories` | نفس الـ endpoint بـ `id` الفرعية |
| فتح منتج من قسم `products` | صفحة تفاصيل المنتج (`product_details`) |
| فتح متجر/مطعم | صفحة المتجر |

> **لا تفترض** عددًا ثابتًا للأقسام — الأدمن يضيف/يحذف/يرتّب من الداشبoard.

---

## الأقسام الافتراضية + المخصّصة

قد تحتوي الصفحة على:

1. **الأقسام الفرعية** (`manual_model: category`, `is_default: true`) — أبناء الفئة الحالية
2. **المنتجات** (`manual_model: product`, `is_default: true`) — منتجات الفئة وكل التابعين لها
3. **أقسام يضيفها الأدmin** (`is_default: false`) — سلاiderات، بانرات، متاجر، ...

### تمييز الأقسام المولّدة تلقائياً

| الحقل | القيمة |
|-------|--------|
| `is_default` | `true` فقط للقسمين المولّدين؛ `false` لأي قسم أضافه الأدمن |
| `content_type` | `category` \| `product` \| … — على كل قسم |
| `manual_model` | مطلوب لـ `type=manual`، `null` لـ `type=api` |
| `api_method` | مطلوب لـ `type=api` (`categories`, `products`, …)، `null` لـ manual |
| `display_type_id` | رقم ثابت بين البيئات (مثلاً `category=8`, `product=2`, `banner=1`) |

**الويب:** اسقط الأقسام حيث `is_default === true` واعرض البديل المخصّص (شريط التصفح + شبكة المنتجات). اعرض كل قسم حيث `is_default === false` — حتى لو تكرر `manual_model` مع قسم مولّد.

اعرض `sections` بالترتيب (`order`) و`position` (`before` / `after`) كما يصل من API.

---

## عرض الأقسام

استخدم **نفس مكوّن الأقسام** لصفحة الفئة وللصفحة الرئيسية — انظر `FRONTEND_WEB_PAGE_BUILDER.md`:

- `variant: horizontal` → سلاider
- `variant: vertical` → شبكة/قائمة
- `variant: square` → مربّعات (فئات)
- تجاهل قسمًا `items` فارغة

---

## الهيدر

- عنوان الصفحة = `category.name`
- يمكن عرض `category.children` كـ tabs أو breadcrumb للتنقّل بين الفئات الفرعية

---

## الكاش

- خزّن الاستجابة لكل `categoryId`
- أعد التحميل عند العودة للشاشة أو pull-to-refresh إن وُجد
