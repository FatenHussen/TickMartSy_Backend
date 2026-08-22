# صفحات الفئات (Flutter)

> **⚠️ مدمج في [`FRONTEND_FLUTTER_COMPLETE.md`](./FRONTEND_FLUTTER_COMPLETE.md)**

كل فئة (رئيسية أو فرعية بأي عمق) لها **صفحة محتوى** مبنية من **أقسام**. endpoint واحد لجميع المستويات.

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
    "sections": [ /* أقسام */ ]
  }
}
```

---

## شاشة الفئة

1. نادِ `categories/{categoryId}/page`
2. AppBar: `category.name`
3. `ListView` / `CustomScrollView` يبني كل عنصر في `sections` عبر **مُعرّض الأقسام الموحّد** (نفس الصفحة الرئيسية)

---

## التنقّل

| من | إلى |
|----|-----|
| كارد فئة في قسم `categories` | `CategoryPageScreen(categoryId: item.id)` |
| كارد منتج | `ProductDetailScreen` |
| كارد متجر/مطعم | `ShopDetailScreen` |
| `see_more` | شاشة القائمة مع `see_more.params` |

---

## الأقسام

- **افتراضي:** فرعية + منتجات (يضبطها الأدmin)
- **اختياري:** سلاiderات/بانرات/... يضيفها الأدmin

لا تفترض عددًا ثابتًا — رتّب حسب `order`. لا تعرض قسمًا `items` فارغة.

### عرض الأقسام — `layout` ثم `variant`

> **مهم:** لا تربط `variant` بالتخطيط. التفاصيل: `FRONTEND_FLUTTER_SECTIONS_TODAY.md`

| `layout` | ويدجت القسم |
|----------|-------------|
| `slider` | `ListView` أفقي / carousel — **افتراضي** |
| `list` | `ListView` / `Column` عمودي |
| `grid` | `GridView` |

| `variant` | شكل الكارد داخل التخطيط |
|-----------|-------------------------|
| `horizontal` | كارد عرضي (بانرات) |
| `vertical` | كارد رأسي (منتجات) |
| `square` | كارد مربع (فئات) |

```dart
final layout = section.layout ?? 'slider';
final cardVariant = section.variant ?? 'horizontal';
```

تفاصيل كاملة: `FRONTEND_FLUTTER_PAGE_BUILDER.md` · `FRONTEND_FLUTTER_SECTIONS_COMPLETE.md`

---

## State

```dart
// مثال
final response = await api.get('/user/categories/$categoryId/page');
final category = response.data['category'];
final sections = response.data['sections'] as List;
```

- `AutomaticKeepAliveClientMixin` أو كاش per `categoryId` لتحسين الأداء
- `RefreshIndicator` → إعادة نداء الـ endpoint

---

## Breadcrumb (اختياري)

عند التنقّل للفئات الفرعية، احتفظ بمسار `[rootId, ..., currentId]` واعرض breadcrumb في AppBar.
