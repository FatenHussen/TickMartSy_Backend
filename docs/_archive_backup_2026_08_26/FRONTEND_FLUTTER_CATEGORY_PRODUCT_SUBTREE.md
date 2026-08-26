# Flutter — Products of a Category Subtree

> **⚠️ مدمج في [`FRONTEND_FLUTTER_COMPLETE.md`](./FRONTEND_FLUTTER_COMPLETE.md)**

عند فتح أي فئة، User API يعيد منتجات الفئة نفسها مباشرة مع منتجات كل الفروع تحتها
بأي عمق.

لا يوجد endpoint جديد ولا تغيير في موديل استجابة المنتج.

---

## 1) الطلب

```http
GET /api/user/products?category_id={categoryId}&page=1&per_page=10
```

مثال:

```text
Fashion
├── منتج مربوط مباشرة بـ Fashion
└── Clothing
    ├── منتج مربوط بـ Clothing
    └── Jeans
        └── منتج مربوط بـ Jeans
```

فتح `Fashion` يعرض المنتجات الثلاثة.

فتح `Clothing` يعرض منتجي `Clothing` و`Jeans` فقط.

فتح `Jeans` يعرض منتجات `Jeans` فقط.

---

## 2) المطلوب في التطبيق

- مرّر id الفئة المفتوحة في `category_id`.
- نفّذ طلباً واحداً فقط للمنتجات؛ الـ Backend يجمع منتجات الفروع.
- لا تشترط `has_children == false` حتى تجلب المنتجات.
- اعرض المنتجات أسفل دوائر/قائمة الأبناء حتى لو كانت الفئة الحالية لها أبناء.
- عند الانتقال لفئة أخرى: امسح القائمة القديمة، أعد الصفحة إلى `1`، ثم نفّذ الطلب الجديد.
- استخدم pagination القادمة من الخادم كما هي.

### لا تعِد فلترة الاستجابة حسب الفئة المباشرة

هذا خطأ:

```dart
// خطأ: يحذف منتجات الفئات الفرعية عند تصفح الأب
final visible = products
    .where((product) => product.categoryId == selectedCategoryId)
    .toList();
```

المنتج الراجع قد يكون مربوطاً بابن أو حفيد للفئة الحالية، وقد قام الـ Backend بالتحقق
من أنه ضمن الشجرة مسبقاً.

---

## 3) Repository example

```dart
Future<PaginatedProducts> getCategoryProducts({
  required int categoryId,
  int page = 1,
  int perPage = 10,
  Map<String, dynamic> filters = const {},
}) async {
  final response = await dio.get(
    '/api/user/products',
    queryParameters: {
      'category_id': categoryId,
      'page': page,
      'per_page': perPage,
      ...filters,
    },
  );

  return PaginatedProducts.fromJson(response.data['data']);
}
```

لا تعمل requests منفصلة لكل child category ولا تجمع الصفحات محلياً؛ ذلك يسبب تكرار
المنتجات وpagination غير صحيحة.

---

## 4) State flow

```dart
Future<void> openCategory(int categoryId) async {
  state = state.copyWith(
    selectedCategoryId: categoryId,
    products: const [],
    currentPage: 1,
    hasMore: true,
    isLoading: true,
  );

  final result = await repository.getCategoryProducts(
    categoryId: categoryId,
    page: 1,
  );

  state = state.copyWith(
    products: result.data,
    currentPage: result.pagination.currentPage,
    hasMore:
        result.pagination.currentPage < result.pagination.lastPage,
    isLoading: false,
  );
}
```

عند load more استخدم نفس `selectedCategoryId` مع الصفحة التالية.

---

## 5) Empty state

أظهر «لا توجد منتجات في هذه الفئة» فقط عندما:

```dart
!isLoading && products.isEmpty && pagination.total == 0
```

لا تعرض empty state أثناء الانتقال بين الفئات أو قبل اكتمال أول طلب.

---

## 6) Checklist

- [ ] جلب المنتجات لكل مستوى، وليس للـ leaf فقط
- [ ] طلب واحد باستخدام `category_id` للفئة الحالية
- [ ] عدم فلترة المنتجات محلياً بمطابقة الفئة
- [ ] عرض منتجات الأب المباشرة مع منتجات فروعه
- [ ] تصفير القائمة والصفحة عند تغيير الفئة
- [ ] الحفاظ على `category_id` نفسه أثناء load more
- [ ] الاعتماد على pagination القادمة من الـ API

---

## 7) Dashboard

لا يوجد تعديل مطلوب في الداشبورد.

يستمر بإرسال:

```text
category_id = آخر مستوى اختاره المستخدم
```

سواء كانت الفئة رئيسية أو فرعية. تجميع المنتجات عند التصفح يتم في User API.
