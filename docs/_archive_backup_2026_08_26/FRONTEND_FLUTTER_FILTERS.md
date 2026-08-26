# Flutter — فلاتر المنتجات (آخر تحديث)

> **⚠️ مدمج في [`FRONTEND_FLUTTER_COMPLETE.md`](./FRONTEND_FLUTTER_COMPLETE.md)** — ارسلوا الملف الموحّد لفريق Flutter.

> **نفس الـ API للويب.** المصدر من الكود الحالي — لا تعتمد `PRODUCTS_FILTERS_API.md` (قديم).

مراجع Flutter:

- `FRONTEND_FLUTTER_CATEGORY_ATTRIBUTE_INHERITANCE.md` — chips الصفات + كاش
- `FRONTEND_FLUTTER_CATEGORY_PRODUCT_SUBTREE.md` — `category_id` + شجرة الفئة
- `FRONTEND_FLUTTER_PRODUCT_DETAIL_SHOP_VARIANTS.md` — `country` نص + `attributes_map`

---

## 1) Endpoints

| الغرض | Endpoint |
|-------|----------|
| قائمة منتجات + كل الفلاتر | `GET /api/user/products` |
| صفات الفلاتر (chips) | `GET /api/user/categories/{categoryId}/attributes` |
| ماركات (dropdown) | `GET /api/user/brands` |
| متاجر (dropdown) | `GET /api/user/shops` |
| بلدان (عرض) | `GET /api/user/countries` |

```http
GET /api/user/products
Accept-Language: ar
```

- **بدون توكن** (عام). التوكن اختياري — يفعّل `is_favorite` فقط.
- أرسل فقط المفاتيح المفعّلة. **لا** ترسل `null` أو `""`.

---

## 2) باراميترات `GET /api/user/products`

| Param | النوع | مثال | السلوك |
|-------|--------|------|---------|
| `category_id` | int | `12` | الفئة **+ كل الأحفاد**. **لا تفلتر محلياً** |
| `brand_id` | int | `7` | ماركة |
| `shop_id` | int | `3` | متوفر في هذا الفرع |
| `price_min` / `price_max` | number | `100` | بعملة المستخدم |
| `search` | string | `أرز` | اسم + وصف (حسب اللغة) |
| `country` | string | `تركيا` | نص — **مو** `country_id` |
| `is_free_delivery` | bool | `true` | توصيل مجاني من الفرع |
| `is_instant_delivery` | bool | `true` | توصيل فوري |
| `on_sale` | bool | `true` | `discount > 0` |
| `in_stock_only` | bool | `true` | كمية المتغيّر `> 0` |
| `attribute_values` | `"31,40"` أو list | — | **OR**: أي قيمة تكفي |
| `type` | enum | `trend` | قائمة جاهزة (§4) |
| `sort_by` | enum | `price_asc` | ترتيب (§5) |
| `page` | int | `1` | افتراضي 1 |
| `per_page` | int | `15` | افتراضي 15 |

### لا تستخدم على `/products`

| Param | السبب |
|-------|--------|
| `name` | استخدم `search` |
| `country_id` | غير موجود — أرسل `country` كنص |
| `sort_by=rating_desc` | استخدم `rating` |

---

## 3) صفات الفئة (Filter Chips)

```http
GET /api/user/categories/{categoryId}/attributes
```

مرّر **الفئة الظاهرة** (جذر أو فرعية) → الباك يرجع صفات **الجذر**.

```json
{
  "data": [
    {
      "id": 10,
      "category_id": 5,
      "root_category_id": 5,
      "name": { "ar": "اللون", "en": "Color" },
      "type": "color",
      "values": [
        { "id": 31, "name": { "ar": "أحمر", "en": "Red" } }
      ]
    }
  ]
}
```

### قواعد Flutter

- اعرض chips فور فتح **أي** فئة (حتى الجذر ولو `has_children == true`)
- **لا** تعيد طلب `/attributes` عند drill-down بنفس الشجرة — كاش بـ `root_category_id`
- أعد الطلب فقط عند فتح **جذر مختلف** (Food → Fashion)
- قائمة فاضية = الجذر بدون صفات (مو خطأ)
- صفحة تفاصيل المنتج: استخدم `attributes_map` — **مو** هذا الـ endpoint

### إرسال القيم

```http
GET /api/user/products?category_id=12&attribute_values=31,40
```

---

## 4) `type` (قوائم جاهزة)

| القيمة | المعنى |
|--------|--------|
| `new` | الأحدث |
| `trend` / `most_popular` | رائج + مبيعات |
| `top_rated` | أعلى تقييم |
| `offers` | عليه خصم |
| `latest_flash_sale` | آخر فلاش سيل — بدون سيل = قائمة فارغة |
| `recommended` / `for_you` | placeholder — لا يفلتر حالياً |
| `search_based` | يحتاج `search` |

---

## 5) `sort_by`

| القيمة | الترتيب |
|--------|---------|
| `price_desc` | السعر تنازلي |
| `price_asc` | السعر تصاعدي |
| `newest` | الأحدث |
| `oldest` | الأقدم |
| `rating` | الأعلى تقييماً |

بدون `type` وبدون `sort_by` → `latest()`.

---

## 6) Dart — Repository

```dart
Future<PaginatedProducts> loadProducts({
  int? categoryId,
  int page = 1,
  int perPage = 20,
  Map<String, dynamic> filters = const {},
}) async {
  final params = <String, dynamic>{
    'page': page,
    'per_page': perPage,
    ..._cleanFilters(filters),
  };
  if (categoryId != null) params['category_id'] = categoryId;

  final res = await dio.get('/api/user/products', queryParameters: params);
  return PaginatedProducts.fromJson(res.data['data']);
}

Map<String, dynamic> _cleanFilters(Map<String, dynamic> raw) {
  return Map.fromEntries(
    raw.entries.where((e) {
      final v = e.value;
      if (v == null || v == '') return false;
      if (v is bool && v == false) return false; // احذف toggle ملغي
      return true;
    }),
  );
}
```

### attribute_values

```dart
final ids = selectedAttributeIds.toList(); // Set<int>
if (ids.isNotEmpty) {
  params['attribute_values'] = ids.join(',');
}
```

---

## 7) Dart — صفات + chips

```dart
int? _cachedRootId;
List<CategoryAttribute> _cachedAttributes = [];

Future<List<CategoryAttribute>> fetchAttributes(int categoryId) async {
  if (_cachedRootId != null && _cachedAttributes.isNotEmpty) {
    // نفس الشجرة — أعد استخدام الكاش
    return _cachedAttributes;
  }
  final res = await dio.get('/api/user/categories/$categoryId/attributes');
  final list = (res.data['data'] as List)
      .map((e) => CategoryAttribute.fromJson(e))
      .toList();
  if (list.isNotEmpty) {
    _cachedRootId = list.first.rootCategoryId;
    _cachedAttributes = list;
  }
  return list;
}

void applyFilters() {
  loadProducts(
    categoryId: currentCategoryId,
    page: 1,
    filters: {
      if (selectedBrandId != null) 'brand_id': selectedBrandId,
      if (priceMin != null) 'price_min': priceMin,
      if (priceMax != null) 'price_max': priceMax,
      if (inStockOnly) 'in_stock_only': true,
      if (onSaleOnly) 'on_sale': true,
      if (selectedAttributeIds.isNotEmpty)
        'attribute_values': selectedAttributeIds.join(','),
      if (sortBy != null) 'sort_by': sortBy,
    },
  );
}
```

---

## 8) State flow

```dart
Future<void> onCategoryChanged(int categoryId) async {
  state = state.copyWith(
    categoryId: categoryId,
    products: [],
    page: 1,
    isLoading: true,
  );

  // صفات: مرة واحدة per root
  final attributes = await fetchAttributes(categoryId);

  final result = await loadProducts(
    categoryId: categoryId,
    page: 1,
    filters: state.activeFilters,
  );

  state = state.copyWith(
    attributes: attributes,
    products: result.data,
    pagination: result.pagination,
    isLoading: false,
  );
}

Future<void> onFilterChanged(Map<String, dynamic> filters) async {
  state = state.copyWith(activeFilters: filters, page: 1);
  await onCategoryChanged(state.categoryId!);
}
```

- أي تغيير فلتر → **`page = 1`**
- load more → نفس `categoryId` + نفس الفلاتر + `page + 1`

---

## 9) Empty state

```dart
final showEmpty = !isLoading && products.isEmpty && pagination.total == 0;
```

لا تعرض empty أثناء الانتقال بين الفئات.

---

## 10) ممنوع

```dart
// خطأ: يحذف منتجات الفروع
products.where((p) => p.categoryId == selectedCategoryId);

// خطأ: فلترة محلية بعد API
products.where((p) => p.price >= minPrice);

// خطأ: طلبات منفصلة لكل child
for (final child in children) {
  await loadProducts(categoryId: child.id);
}
```

- لا تفلتر محلياً (فئة، سعر، ستوك، صفات)
- لا تشترط leaf قبل إظهار الفلاتر
- `country` على البطاقة = **string** أو `null`

---

## 11) أمثلة طلبات

```http
GET /api/user/products?category_id=2&in_stock_only=1&is_free_delivery=1&sort_by=newest&page=1

GET /api/user/products?category_id=5&attribute_values=31,40&price_min=10&price_max=80

GET /api/user/products?on_sale=true&is_instant_delivery=true

GET /api/user/products?category_id=3&search=jeans&in_stock_only=true
```

---

## 12) Checklist Flutter

- [ ] شريط/Sheet فلاتر على صفحة الفئة و«عرض الكل»
- [ ] `GET /categories/{id}/attributes` عند فتح أي فئة
- [ ] كاش `root_category_id` — بدون إعادة طلب داخل الشجرة
- [ ] `attribute_values` + باقي الفلاتر في نفس `GET /products`
- [ ] `category_id` = الفئة الحالية (شجرة من الباك)
- [ ] toggles: `in_stock_only`, `on_sale`, `is_free_delivery`, `is_instant_delivery`
- [ ] سعر: `price_min` / `price_max`
- [ ] ترتيب: `price_asc` | `price_desc` | `newest` | `oldest` | `rating`
- [ ] أي تغيير فلتر → `page = 1`
- [ ] empty من `pagination.total == 0` فقط
- [ ] صفحة المنتج: `attributes_map` — مو `/attributes`

---

## مراجع

| الملف | الموضوع |
|-------|---------|
| `FRONTEND_FLUTTER_FILTERS.md` | **هذا الملف** — كل الفلاتر |
| `FRONTEND_FLUTTER_CATEGORY_ATTRIBUTE_INHERITANCE.md` | chips + كاش تفصيلي |
| `FRONTEND_FLUTTER_CATEGORY_PRODUCT_SUBTREE.md` | شجرة `category_id` |
| `FRONTEND_FLUTTER_CATEGORIES_CIRCULAR.md` | دوائر + منتجات |
| `FRONTEND_FLUTTER_SECTIONS_COMPLETE.md` | §8 فلاتر + عرض الكل |
| `FRONTEND_WEB_FILTERS.md` | نفس الـ API (مرجع Web) |
