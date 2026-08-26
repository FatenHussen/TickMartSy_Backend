# Frontend Web — Products of a Category Subtree

عند تصفح أي فئة، الـ Backend يعيد:

1. المنتجات المرتبطة بالفئة الحالية مباشرة.
2. منتجات جميع الفئات الفرعية تحتها، بأي عمق.

لا يوجد endpoint جديد ولا تغيير في شكل الاستجابة.

---

## 1) الطلب

استخدم نفس الطلب الحالي:

```http
GET /api/user/products?category_id={categoryId}&page=1&per_page=10
```

مثال:

```text
Fashion (2)
├── Product A                 category_id = 2
└── Clothing (7)
    ├── Product B             category_id = 7
    └── Jeans (12)
        └── Product C         category_id = 12
```

عند إرسال:

```http
GET /api/user/products?category_id=2
```

النتيجة تحتوي على `Product A + Product B + Product C`.

عند إرسال:

```http
GET /api/user/products?category_id=7
```

النتيجة تحتوي على `Product B + Product C` فقط، ولا تحتوي على `Product A`.

---

## 2) المطلوب في الويب

- أرسل `category_id` للفئة التي يتصفحها المستخدم، سواء كانت رئيسية أو فرعية.
- اعرض الاستجابة كما ترجع من الـ API.
- لا تجلب منتجات كل ابن بطلب مستقل ولا تدمج النتائج في الواجهة.
- لا تشترط أن تكون الفئة المختارة leaf.
- عند تغيير الفئة، أعد `page` إلى `1`.
- طبّق الـ empty state فقط عندما تكون `data` فارغة و`pagination.total === 0`.

### مهم: ممنوع الفلترة المحلية بالمطابقة المباشرة

لا تستخدم:

```js
// خطأ: يحذف منتجات الفروع من نتيجة الفئة الرئيسية
products.filter((product) => product.category_id === selectedCategoryId);
```

ولا تستخدم اسم الفئة للمطابقة:

```js
// خطأ
products.filter((product) => product.category === selectedCategoryName);
```

الـ Backend طبّق فلتر الشجرة مسبقاً، وقد يكون المنتج الراجع مربوطاً بفئة فرعية.

---

## 3) الفلاتر والترتيب

القائمة الكاملة والمعتمدة: **`FRONTEND_WEB_FILTERS.md`** (آخر تحديث 19 آب 2026).

كل الفلاتر تُطبَّق على نتيجة الشجرة كاملة:

```http
GET /api/user/products
    ?category_id=2
    &in_stock_only=1
    &is_free_delivery=1
    &sort_by=newest
    &page=1
```

الـ pagination أيضاً محسوبة على مجموع منتجات الفئة الحالية وفروعها، لذلك لا تعدّل
`total` أو `last_page` في الواجهة.

---

## 4) مثال React

```js
async function loadCategoryProducts(categoryId, page = 1, filters = {}) {
  const response = await api.get('/api/user/products', {
    params: {
      category_id: categoryId,
      page,
      per_page: 10,
      ...filters,
    },
  });

  return response.data.data;
}
```

استخدم `result.data` للبطاقات و`result.pagination` للصفحات دون فلترة إضافية حسب الفئة.

---

## 5) Checklist

- [ ] الطلب يرسل الفئة الحالية في `category_id`
- [ ] لا يوجد شرط leaf قبل جلب المنتجات
- [ ] لا توجد مطابقة محلية لـ `product.category_id`
- [ ] تغيير الفئة يعيد pagination إلى الصفحة الأولى
- [ ] empty state يعتمد على نتيجة الطلب النهائية
- [ ] الفلاتر تُرسل في نفس الطلب ولا تُطبّق على صفحة واحدة محلياً

---

## 6) Dashboard

لا يوجد تعديل مطلوب في الداشبورد.

يبقى إنشاء/تعديل المنتج يرسل فئة واحدة فقط:

```text
category_id = آخر فئة اختارها المستخدم
```

يمكن أن تكون الفئة رئيسية أو فرعية. توسيع النتائج إلى الفروع مسؤولية User API فقط.
