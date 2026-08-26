# Flutter — Circular Categories (Root + Subcategories)

> **⚠️ Merged into [`FRONTEND_FLUTTER_COMPLETE.md`](./FRONTEND_FLUTTER_COMPLETE.md)**

A product can now be saved on **any level** of the category tree (parent or child). The user app must:

1. Show **every level** with the same circular layout (not roots only).
2. Show products for the current category even when it has children.

> User routes are under `/api/user`.

---

## 1) What changed

| Before | After |
|---|---|
| Products were usually on leaf categories only | A product can sit on a root or any subcategory |
| Circles for roots, list/chips for children | **The same circle widget at every level** |
| Opening a parent showed no direct products | `GET /products?category_id={id}` returns that category **+ all descendants** |

```text
Food  ← circle
 └─ Grains  ← same circle widget
     └─ Rice  ← same circle widget
         └─ Short rice  ← circle, then products only if it has no children
```

---

## 1.1) API changes (backend)

No new endpoints and no breaking removals. Only added response fields + one filter behavior change.

### `GET /api/user/categories` (and `?parent_id=`)

Added on each item **and** on each `children[]` entry:

| Field | Type | Note |
|---|---|---|
| `parent_id` | int / null | `null` = root |
| `children_count` | int | number of active children |
| `has_children` | bool | `children_count > 0` |
| `children[].icon` | string | full URL — **new**, children now carry their own icon |

`children[]` entries also now include `parent_id`, `order`, `is_restaurant`, `children_count`, `has_children` (same card shape as a top-level item).

### `GET /api/user/categories?parent_id={id}` (children)

Returns the children of the given parent. Same card shape as roots (with `icon`, `has_children`, `children_count`).

> **ملاحظة:** لا يوجد endpoint `GET /api/user/categories/{id}` لفئة واحدة. للحصول على أبناء فئة استخدم `?parent_id=`. بيانات الفئة نفسها (الاسم والأيقونة) تأتي من الكارد الذي ضغطته في القائمة.

### `GET /api/user/products?category_id={id}` — behavior change

| Before | After |
|---|---|
| Returned products of leaf descendants (and the id itself only if it was a leaf) | Returns products of the id **plus every descendant at any depth** |

So a product saved on a parent now appears when browsing that parent. No parameter change — same request, wider result.

### `GET /api/admin/categories` + `GET /api/admin/categories/{id}`

Added `has_children` (bool). `children_count` was already present on the list.

### Not changed

- Request params for all category/product endpoints
- Attributes endpoint `GET /api/user/categories/{id}/attributes` (still returns root attributes)
- Product card fields, price/brand/shop filters

---

## 2) Required UI

Reuse the same circle screen at every level (drill-down):

```text
┌─────────────────────────────────┐
│  ← Food                         │
│                                 │
│   (○)     (○)     (○)          │
│ Grains  Veg    Fruits           │
│                                 │
│   (○)     (○)                   │
│  Meat   Dairy                   │
│                                 │
│  ── Products in this category ──│
│  [card] [card] [card]           │
└─────────────────────────────────┘
```

- Top: children of the current category as **circles** (round icon + name below).
- Bottom: products for the current `category_id` (includes descendants).
- If there are no children: hide the circle row and show products only.

Do not use ListTiles/chips for subcategories and circles for roots. **One widget** for every category at any depth.

---

## 3) APIs

### 3.1 Root categories (home)

```http
GET /api/user/categories
```

Without `parent_id` this returns **roots only** (`parent_id = null`).

### 3.2 Subcategories of any parent

```http
GET /api/user/categories?parent_id={categoryId}
```

Same item shape. Call this when the user taps a circle with `has_children: true`.

### Item shape (circle card)

```json
{
  "id": 5,
  "name": "Food",
  "icon": "http://localhost:8000/storage/categories/food.png",
  "parent_id": null,
  "order": 1,
  "is_restaurant": false,
  "children_count": 3,
  "has_children": true,
  "children": [
    {
      "id": 12,
      "name": "Grains & legumes",
      "icon": "http://localhost:8000/storage/categories/grains.png",
      "parent_id": 5,
      "order": 1,
      "is_restaurant": false,
      "children_count": 2,
      "has_children": true
    }
  ]
}
```

| Field | Flutter use |
|---|---|
| `icon` | Circle image — full URL |
| `name` | Label under the circle |
| `has_children` | If `true` → open the same circle screen for children |
| `children_count` | Same meaning |
| `children` | One nested level (optional). For deeper levels use `?parent_id=` |
| `is_restaurant` | Restaurant screen behavior if needed |

`children[].icon` is now returned on children, so the same circle widget works for them.

### 3.3 Products

```http
GET /api/user/products?category_id={id}
```

Returns products in **this category + every descendant at any depth**.

- A product saved on "Food" shows when browsing Food. It does **not** show when browsing "Grains".
- A product saved on "Short rice" shows on Short rice, Rice, Grains, and Food.

### 3.4 Attributes (filters)

Full guide: `FRONTEND_FLUTTER_CATEGORY_ATTRIBUTE_INHERITANCE.md`.

```http
GET /api/user/categories/{categoryId}/attributes
```

Any id in the tree returns the **root** attributes **and values**. Show filter chips as soon as the user opens a root (or any child). Do not wait for a leaf. Do not refetch when drilling down the same tree.

---

## 4) Navigation flow

```text
Home
  GET /categories
  → circular grid of roots

Tap a category with has_children = true
  → push CategoriesCirclePage(parentId: id, title: name)
  GET /categories?parent_id={id}     ← child circles
  GET /products?category_id={id}     ← products at this level + descendants

Tap a category with has_children = false
  → products screen only
  GET /products?category_id={id}
```

Alternative: always use the same screen. If `has_children == false`, the grid is empty and products still show.

Do not stop at the root. Every child is a circle, including levels 5–6.

---

## 5) Flutter example

### Model

```dart
class CategoryCircle {
  final int id;
  final String name;
  final String? icon;
  final int? parentId;
  final bool hasChildren;
  final bool isRestaurant;

  CategoryCircle({
    required this.id,
    required this.name,
    this.icon,
    this.parentId,
    required this.hasChildren,
    required this.isRestaurant,
  });

  factory CategoryCircle.fromJson(Map<String, dynamic> json) {
    return CategoryCircle(
      id: json['id'],
      name: json['name'] is Map ? (json['name']['ar'] ?? json['name']['en']) : json['name'],
      icon: json['icon'],
      parentId: json['parent_id'],
      hasChildren: json['has_children'] == true || (json['children_count'] ?? 0) > 0,
      isRestaurant: json['is_restaurant'] == true,
    );
  }
}
```

### Fetch the current level

```dart
Future<List<CategoryCircle>> fetchCategories({int? parentId}) async {
  final path = parentId == null
      ? '/api/user/categories'
      : '/api/user/categories?parent_id=$parentId';
  final res = await api.get(path);
  final items = res.data['data']['items'] ?? res.data['data'];
  return (items as List).map((e) => CategoryCircle.fromJson(e)).toList();
}
```

If the list already includes `children`, you can render that next level without a second request. For anything deeper than one child, use `parent_id`.

### Circle widget — same widget at every level

```dart
class CategoryCircleTile extends StatelessWidget {
  final CategoryCircle category;
  final VoidCallback onTap;

  const CategoryCircleTile({required this.category, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Column(
        children: [
          CircleAvatar(
            radius: 36,
            backgroundImage: category.icon != null
                ? NetworkImage(category.icon!)
                : null,
            child: category.icon == null ? const Icon(Icons.category) : null,
          ),
          const SizedBox(height: 8),
          Text(
            category.name,
            textAlign: TextAlign.center,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }
}
```

### Circle grid

```dart
GridView.builder(
  shrinkWrap: true,
  physics: const NeverScrollableScrollPhysics(),
  itemCount: categories.length,
  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
    crossAxisCount: 4,
    mainAxisSpacing: 12,
    crossAxisSpacing: 12,
    childAspectRatio: 0.75,
  ),
  itemBuilder: (context, index) {
    final category = categories[index];
    return CategoryCircleTile(
      category: category,
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => CategoryBrowsePage(category: category),
          ),
        );
      },
    );
  },
);
```

### Level screen (circles + products)

```dart
class CategoryBrowsePage extends StatelessWidget {
  final CategoryCircle category;
  const CategoryBrowsePage({required this.category});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(category.name)),
      body: ListView(
        children: [
          if (category.hasChildren)
            FutureBuilder(
              future: fetchCategories(parentId: category.id),
              builder: (context, snap) {
                if (!snap.hasData) return const SizedBox.shrink();
                return Padding(
                  padding: const EdgeInsets.all(16),
                  child: GridView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: snap.data!.length,
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 4,
                      childAspectRatio: 0.75,
                    ),
                    itemBuilder: (_, i) {
                      final child = snap.data![i];
                      return CategoryCircleTile(
                        category: child,
                        onTap: () => Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => CategoryBrowsePage(category: child),
                          ),
                        ),
                      );
                    },
                  ),
                );
              },
            ),
          ProductGrid(categoryId: category.id),
        ],
      ),
    );
  }
}
```

`ProductGrid` calls:

```http
GET /api/user/products?category_id={category.id}
```

---

## 6) Do / don't

- **Do:** tapping a parent opens child circles + that parent's products.
- **Do:** tapping a leaf opens products only.
- **Don't:** hide circles after level 1.
- **Don't:** skip products because `has_children == true`.
- **Don't:** assume a product exists only on the last child.

---

## 7) UI checklist

- [ ] Home: root circles from `GET /categories`
- [ ] Same `CategoryCircleTile` for subcategories
- [ ] `has_children` → same screen with `?parent_id=`
- [ ] Every circle uses `icon` (children now include `icon`)
- [ ] Products for the current `category_id` always under the circles
- [ ] Leaf with no children → products only
- [ ] Attributes from `/categories/{id}/attributes` at any level (see `FRONTEND_FLUTTER_CATEGORY_ATTRIBUTE_INHERITANCE.md`)
- [ ] `is_restaurant` if the restaurant screen is different

### Do not change

- Price / brand / shop filters
- Product card layout
- Variant price/quantity (dashboard work)

---

## 8) Notes

- Depth is up to 6 levels (1 root + 5 nested). The same page repeats.
- `GET /categories` without `parent_id` = roots only. Do not expect the full tree in one request.
- `children` in the response is one level. Keep drilling with `parent_id`.
- Dashboard: `FRONTEND_DASHBOARD_PRODUCT_CATEGORY_ANY_LEVEL.md`
