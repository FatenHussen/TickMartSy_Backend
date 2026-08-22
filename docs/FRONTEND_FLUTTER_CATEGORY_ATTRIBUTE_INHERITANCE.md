# Flutter — Category attributes (end-user app)

> **⚠️ Merged into [`FRONTEND_FLUTTER_COMPLETE.md`](./FRONTEND_FLUTTER_COMPLETE.md)**

Category attributes (color, size, weight, …) live on the **root category only**. Every subcategory down to 5 levels **inherits the same names and the same values**.

This file is for the **customer Flutter app** (`/api/user`). Dashboard: `FRONTEND_DASHBOARD_CATEGORY_ATTRIBUTE_INHERITANCE.md`. Category circles: `FRONTEND_FLUTTER_CATEGORIES_CIRCULAR.md`.

> User routes are under `/api/user`.

---

## API changes (user)

نفس الـ URL. تغيّر **السلوك + حقول جديدة**. ما في endpoint جديد.

### `GET /api/user/categories/{categoryId}/attributes`

| | قبل | بعد |
|---|---|---|
| المطابقة | `category_id` حرفي فقط | أي id بالشجرة → صفات **الجذر** + قيمها |
| فئة فرعية بدون صفات خاصة | `data: []` | صفات الرئيسية (وزن، لون…) |
| حقول الصفة | `id`, `name`, `type`, `values` | نفسهم + `category_id` + `root_category_id` |

**قبل**

```json
{
  "id": 10,
  "name": { "ar": "الوزن", "en": "Weight" },
  "type": "square",
  "values": [{ "id": 31, "name": { "ar": "1 كيلو", "en": "1kg" } }]
}
```

**بعد**

```json
{
  "id": 10,
  "category_id": 5,
  "root_category_id": 5,
  "name": { "ar": "الوزن", "en": "Weight" },
  "type": "square",
  "values": [{ "id": 31, "name": { "ar": "1 كيلو", "en": "1kg" } }]
}
```

`category_id` و `root_category_id` = فئة الجذر اللي الصفة محفوظة عليها. الحقول القديمة ما انمسحت — أضفوا الحقلين بالـ model.

### ما تغيّر (نفس الـ contract)

| Endpoint | ملاحظة |
|---|---|
| `GET /api/user/categories` | جذور فقط إذا ما في `parent_id` |
| `GET /api/user/categories?parent_id={id}` | أبناء المستوى الحالي |
| `GET /api/user/products?category_id={id}` | منتجات هالفئة + كل الأحفاد |
| `GET /api/user/products?attribute_values=31,40` | فلتر القيم — نفس الباراميتر |
| `GET /api/user/products/{id}` | `attributes_map` و `shop_variants[].attributes` بدون تغيير |

ما في breaking rename. التطبيق القديم اللي يقرأ `id/name/type/values` يضل يشتغل، بس لازم **ما يعتبر القائمة الفاضية على فرعية = ما في فلاتر** — هلق الفرعية ترجع فلاتر الجذر.

---

## 1) Do you need to change the app?

**Yes, if** filters only appear on a leaf category, or you call attributes with the current id and treat an empty list as “this category has no filters”.

**No extra work** on the product-detail variant picker: `GET /products/{id}` still returns `attributes_map` / `shop_variants[].attributes` from that product’s own variants.

| Screen | Change |
|---|---|
| Category browse filters (chips: Color, Weight, …) | Load attributes as soon as the user opens **any** category, including the root |
| Drill-down Food → Grains → Rice | **Do not** refetch attributes; they are the same |
| Product detail (pick Red / 1kg) | Unchanged |
| Cart / checkout | Unchanged |

```text
Food (root)  ← attributes defined here: Weight, Quality
 └─ Grains
     └─ Rice
         └─ Short rice   ← same Weight + Quality values
```

Opening Food, Grains, or Short rice must show the **same filter chips**.

---

## 2) API

### 2.1 Filters for the current category

```http
GET /api/user/categories/{categoryId}/attributes
```

Pass **the category on screen** (root or child). The backend walks up to the root and returns that root’s attributes **and values**.

```json
{
  "success": true,
  "data": [
    {
      "id": 10,
      "category_id": 5,
      "root_category_id": 5,
      "name": { "ar": "الوزن", "en": "Weight" },
      "type": "square",
      "values": [
        { "id": 31, "name": { "ar": "1 كيلو", "en": "1kg" } },
        { "id": 32, "name": { "ar": "5 كيلو", "en": "5kg" } }
      ]
    },
    {
      "id": 11,
      "category_id": 5,
      "root_category_id": 5,
      "name": { "ar": "الجودة", "en": "Quality" },
      "type": "circle",
      "values": [
        { "id": 40, "name": { "ar": "فاخر", "en": "Premium" } }
      ]
    }
  ]
}
```

| Field | Flutter use |
|---|---|
| `name` | Filter section title (localized map or string) |
| `values[].id` | Sent as `attribute_values` |
| `values[].name` | Chip label |
| `type` | `square` / `circle` / `color` — chip UI |
| `root_category_id` | Cache key. Same root → skip a second request |

If the list is empty, the **root** has no attributes. Do not wait for a child category.

### 2.2 Product list with selected values

```http
GET /api/user/products?category_id={id}&attribute_values[]=31&attribute_values[]=40
```

Or comma-separated:

```http
GET /api/user/products?category_id={id}&attribute_values=31,40
```

Keep `category_id` as the **current circle**. Products in this category **and all descendants** are returned (see the circular-categories doc). Attribute filters still apply on top.

### 2.3 Product detail (no attribute-API change)

```http
GET /api/user/products/{id}
```

Use this for the variant UI, not the category attributes endpoint:

- `attributes_map` — groups available on **this product**
- `shop_variants[].attributes` — `{ attribute, value, type }` per SKU

The user picks values that exist on that product. Those values originally come from the root, but you do not need the category endpoint here.

---

## 3) UI flow

```text
Home — tap Food (root)
  GET /categories/{foodId}/attributes     ← show filter chips immediately
  GET /products?category_id={foodId}

Tap Grains (child)
  Do NOT call /attributes again
    (root_category_id is still Food)
  GET /products?category_id={grainsId}
  Keep the same chips; keep selected value ids if you want

Tap Short rice
  Same chips, same values
  GET /products?category_id={shortRiceId}
```

Wrong:

```text
Open Food → hide filters because “not a leaf”
Open Short rice → call /attributes → empty → “no filters”
  (happened when attributes were stored only on the parent)
```

---

## 4) Flutter example

### Model

```dart
class CategoryAttributeValue {
  final int id;
  final String name;

  CategoryAttributeValue({required this.id, required this.name});

  factory CategoryAttributeValue.fromJson(Map<String, dynamic> json) {
    return CategoryAttributeValue(
      id: json['id'],
      name: _localized(json['name']),
    );
  }
}

class CategoryAttribute {
  final int id;
  final int rootCategoryId;
  final String name;
  final String type; // square | circle | color
  final List<CategoryAttributeValue> values;

  CategoryAttribute({
    required this.id,
    required this.rootCategoryId,
    required this.name,
    required this.type,
    required this.values,
  });

  factory CategoryAttribute.fromJson(Map<String, dynamic> json) {
    return CategoryAttribute(
      id: json['id'],
      rootCategoryId: json['root_category_id'] ?? json['category_id'],
      name: _localized(json['name']),
      type: json['type'] ?? 'square',
      values: (json['values'] as List? ?? [])
          .map((e) => CategoryAttributeValue.fromJson(e))
          .toList(),
    );
  }
}

String _localized(dynamic value) {
  if (value is Map) {
    return value['ar'] ?? value['en'] ?? '';
  }
  return value?.toString() ?? '';
}
```

### Fetch once per root

```dart
int? _cachedRootId;
List<CategoryAttribute> _cachedAttributes = [];

Future<List<CategoryAttribute>> fetchAttributes(int categoryId) async {
  final res = await api.get('/api/user/categories/$categoryId/attributes');
  final list = (res.data['data'] as List)
      .map((e) => CategoryAttribute.fromJson(e))
      .toList();

  if (list.isNotEmpty) {
    _cachedRootId = list.first.rootCategoryId;
    _cachedAttributes = list;
  }
  return list;
}

Future<List<CategoryAttribute>> attributesForCategory(int categoryId) async {
  if (_cachedRootId != null && _cachedAttributes.isNotEmpty) {
    // Optional: still fetch once when landing on a new tree.
    // If you already loaded this root while drilling down, reuse.
  }
  return fetchAttributes(categoryId);
}
```

When pushing a child category page, pass the already-loaded attributes (or `rootCategoryId`) so the child screen does not refetch.

```dart
Navigator.push(
  context,
  MaterialPageRoute(
    builder: (_) => CategoryBrowsePage(
      category: child,
      inheritedAttributes: attributes, // same list
    ),
  ),
);
```

### Filter chips + products

```dart
// selected value ids
final selected = <int>{};

void applyFilters() {
  final ids = selected.join(',');
  api.get('/api/user/products', queryParameters: {
    'category_id': category.id,
    if (ids.isNotEmpty) 'attribute_values': ids,
  });
}
```

Render one section per attribute (`name`), chips from `values`. For `type == color`, use a color swatch if the value is a hex / color name.

---

## 5) What not to do

- Do not require a leaf category before showing filters.
- Do not expect different Weight/Color lists on Rice vs Short rice.
- Do not copy attributes onto subcategories on the client.
- Do not send `category_id` of a child when **creating** attributes (dashboard only). The user app is read-only here.
- Do not rebuild the product-detail picker from the category endpoint; use `attributes_map`.

---

## 6) UI checklist

- [ ] `GET /categories/{id}/attributes` when a category screen opens (root included)
- [ ] Show **name + value chips** even if `has_children == true`
- [ ] Reuse the same list when drilling to children (`root_category_id`)
- [ ] Refetch only when the user opens a **different root** (e.g. Food → Fashion)
- [ ] Product grid: `category_id` = current circle + optional `attribute_values`
- [ ] Product detail: still `attributes_map` / `shop_variants`
- [ ] Empty attributes = this tree has no filters, not an error

### Do not change

- Circle layout — `FRONTEND_FLUTTER_CATEGORIES_CIRCULAR.md`
- Variant price / quantity on the card
- Cart line items

---

## 7) Notes

- Tree depth: 1 root + up to 5 children. Attributes always come from the root.
- `name` / `values[].name` may be a localized map `{ ar, en }` or a string, depending on locale.
- `type`: `square`, `circle`, `color`.
- Dashboard assigns attributes on the root only. The user app only **reads** them.
