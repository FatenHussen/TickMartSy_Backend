# Dashboard Frontend — وراثة متغيرات الفئة من الرئيسية

الصفات (متغيرات الفئة: لون، مقاس، وزن…) تُسند **للفئة الرئيسية فقط**. الفئات الفرعية حتى 5 مستويات **ترث نفس الصفات ونفس القيم**. ما في إسناد ولا نسخ على الفرعية.

> كل مسارات الأدمن تحت `/api/admin` وتتطلب Admin token.

---

## 1) شنو تغيّر

| قبل | بعد |
|---|---|
| صفة مربوطة بأي فئة (رئيسية أو فرعية) | صفة مربوطة **بالجذر فقط** (`parent_id = null`) |
| جلب الصفات بـ `category_id` الحرفي | تمرير أي فئة بالشجرة → الـ API يطلع للجذر ويرجع صفاته |
| فورم المنتج ينتظر الفئة الورقة حتى تظهر الصفات | بمجرد اختيار **المستوى 1** تظهر الصفات + القيم |
| قيم مختلفة ممكن تتعرّف على كل مستوى | القيم واحدة: المعرّفة على الرئيسية |

المنتج ينحفظ على **أي مستوى تختاره** (رئيسية، أب، أو ورقة). ما في إجبار توصل لآخر ابن. الصفات تجي بالميراث من الجذر.

```text
أكل (رئيسية)  ←  الوزن / الجودة تُنشأ هنا
 └─ حبوب وبقوليات
     └─ رز
         └─ رز قصير     ← يرث الوزن + الجودة (الاسم والقيم)
```

---

## 2) شاشة إدارة الصفات (Category Attributes)

### إنشاء / تعديل

`POST /api/admin/category-attributes`  
`PUT /api/admin/category-attributes/{id}`

- `category_id` **لازم** يكون فئة رئيسية.
- إذا انرسلت فئة فرعية → **422** مع:

```text
يمكن إسناد المتغيرات (الصفات) للفئة الرئيسية فقط. الفئات الفرعية ترث نفس الصفات والقيم.
```

- القيم (`values`) تتعرّف مع الصفة على الجذر. الفرعية ما إلها فورم قيم لحاله.

**Select الفئة بهالصفحة:** اعرض فقط الفئات الرئيسية.

```js
const rootCategories = categories.filter(
  (c) => c.is_root === true || c.parent_id === null
);
```

حقل جديد على قائمة/تفاصيل الفئة:

| الحقل | المعنى |
|---|---|
| `is_root` | `true` إذا `parent_id === null` |
| `parent_id` | `null` = رئيسية |
| `has_children` | `true` إذا للفئة أبناء — يظهر الـ select التالي كخيار، **مو شرط** الوصول للورقة |

لا تعرض فورم «إضافة صفة» جوّا صفحة فئة فرعية.

### جلب الصفات

`GET /api/admin/category-attributes?category_id={id}`

`category_id` يقدر يكون رئيسية **أو** فرعية. النتيجة دائماً صفات الجذر (الاسم + القيم).

```http
GET /api/admin/category-attributes?category_id=12
```

إذا `12` = رز قصير، والجذر = أكل، ترجع صفات «أكل».

شكل العنصر (حقول مضافة):

```json
{
  "id": 10,
  "category_id": 5,
  "root_category_id": 5,
  "name": "الوزن",
  "category": "أكل",
  "type": "square",
  "values": [
    { "id": 31, "name": "1 كيلو" },
    { "id": 32, "name": "5 كيلو" }
  ],
  "is_active": true
}
```

`category_id` و `root_category_id` نفس القيمة: الصفة محفوظة على الجذر.

---

## 3) فورم المنتج — أهم تعديل بالواجهة

اختيار الفئة لأي مستوى (مو الورقة فقط): `FRONTEND_DASHBOARD_PRODUCT_CATEGORY_ANY_LEVEL.md`.

### تدفّق الاختيار

1. المستخدم يختار **الفئة الرئيسية** (level 1).
2. **فوراً** اطلب الصفات:
   `GET /api/admin/category-attributes?category_id={rootCategoryId}`
3. اعرض الصفات **والقيم** بتاب المتغيرات. ما تستنى اختيار الفرعية.
4. المستويات 2–6 **اختيارية**. إذا وقفت عند المستوى 1 → المنتج للأب/الرئيسية. إذا نزلت للمستوى 3 ووقفت → المنتج لهالمستوى. **لا تجبر المستخدم يوصل لآخر ابن** حتى لو `has_children === true`.
5. المستويات 2–6 ما تغيّر قائمة الصفات. بس يحدّدوا `category_id` المحفوظ على المنتج.
6. لكل تنويعة: المستخدم **يختار قيمة من القائمة**، ما يكتب قيمة جديدة.

`category_id` عند الحفظ = **آخر فئة اختارها المستخدم**، مو بالضرورة الورقة:

```text
أكل (level 1)                    → category_id = أكل
أكل > حبوب (level 2 ثم توقف)     → category_id = حبوب
أكل > حبوب > رز > رز قصير        → category_id = رز قصير
```

لا تمنع Submit لأن الفئة المختارة إلها أبناء. `has_children` بس ليظهر الـ select التالي كخيار، مو كشرط.

```js
// بعد اختيار المستوى 1
const { data } = await api.get('/api/admin/category-attributes', {
  params: { category_id: selectedRootCategoryId },
});

// attributes[].name  → تسميات الحقول (وزن، لون…)
// attributes[].values → خيارات الـ select
```

إذا المستخدم غيّر الرئيسية: أعد جلب الصفات وامسح اختيارات القيم القديمة على التنويعات.

إذا غيّر فرعية فقط: **لا** تعيد الطلب.

### إسناد القيم على التنويعة

نفس الشكل السابق. القيم هي IDs من صفة الجذر:

```text
variants[0][attributes_values_ids][]=31
variants[0][attributes_values_ids][]=40
```

`31` = «1 كيلو» و `40` = «فاخر» من صفات الفئة الرئيسية، حتى لو المنتج محفوظ على «رز قصير».

---

## 4) تطبيق المستخدم (Flutter)

دليل التطبيق النهائي: `FRONTEND_FLUTTER_CATEGORY_ATTRIBUTE_INHERITANCE.md`.

`GET /api/user/categories/{categoryId}/attributes`

نفس الوراثة: أي id بالشجرة يرجع صفات الجذر + القيم.

حقول مضافة على كل صفة:

- `category_id`
- `root_category_id`

فلاتر المنتجات: `attribute_values` (ids القيم). شكل التنويعة على صفحة المنتج من `attributes_map` — ما تغيّر.

عند `GET /api/user/products?category_id={id}` تظهر منتجات **هالفئة نفسها + كل أبنائها بأي مستوى** (مو الأوراق فقط). منتج محفوظ على الأب يظهر عند تصفح الأب، وما يظهر إذا تصفحت ابنه.

---

## 5) UI Checklist

### إدارة الصفات

- [ ] Select الفئة = الرئيسية فقط (`is_root` / `parent_id === null`)
- [ ] إخفاء «إضافة صفة» على الفئات الفرعية
- [ ] رسالة 422 إذا انرسلت فرعية
- [ ] فورم القيم يبقى على نفس شاشة الصفة (مو على الفرعية)

### فورم المنتج

- [ ] طلب الصفات عند اختيار المستوى 1، مو عند الورقة
- [ ] عرض الاسم + select القيم فوراً
- [ ] عدم إعادة الطلب عند النزول للمستويات 2–6
- [ ] إعادة الطلب فقط إذا تغيّرت الرئيسية
- [ ] `category_id` المرسل عند الحفظ = آخر فئة اختارها المستخدم (رئيسية / أب / ورقة)
- [ ] المستويات 2–6 اختيارية — لا تمنع الحفظ إذا بقي ابن ظاهر وغير مختار
- [ ] `has_children` / `children_count` يظهرون الـ select التالي فقط، مو شرط الوصول للورقة

### لا تغيّر

- سعر/كمية التنويعة (`variants.*.price` / `quantity`) — شغل منفصل
- `category_details` (مواصفات المنتج) — ما زالت مربوطة بالفئة الحرفية

---

## 6) مثال React مختصر

```jsx
const [rootCategoryId, setRootCategoryId] = useState(null);
const [selectedCategoryId, setSelectedCategoryId] = useState(null);
const [attributes, setAttributes] = useState([]);

useEffect(() => {
  if (!rootCategoryId) {
    setAttributes([]);
    return;
  }

  api.get('/api/admin/category-attributes', {
    params: { category_id: rootCategoryId },
  }).then((res) => setAttributes(res.data.data ?? res.data));
}, [rootCategoryId]);

function onSelectLevel(level, categoryId) {
  if (level === 1) {
    setRootCategoryId(categoryId);
  }
  // آخر اختيار — حتى لو للفئة أبناء ولم يُختر ابن
  setSelectedCategoryId(categoryId);
}

// يمكن الحفظ بمجرد وجود selectedCategoryId (level 1 يكفي)
// category_id: selectedCategoryId
// variants[].attributes_values_ids: القيم المختارة من attributes[].values
```

---

## 7) ملاحظات

- الشجرة: 1 رئيسية + حتى 5 فرعية (6 مستويات بالفورم).
- ما تنسخوا الصفات لكل فرعية. مصدر واحد على الجذر.
- بيانات قديمة كانت على فرعية تننقل للجذر بمايجريشن البكند. إذا ظهرت صفات مكررة بنفس الاسم على جذر واحد، احذف التكرار من شاشة الصفات.
