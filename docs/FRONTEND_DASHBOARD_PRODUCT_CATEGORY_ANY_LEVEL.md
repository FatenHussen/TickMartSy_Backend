# Dashboard Frontend — إسناد المنتج لأي مستوى فئة

تغيير فورم المنتج: **ما في إجبار توصل لآخر ابن**. المنتج ينحفظ على آخر فئة اختارها المستخدم (رئيسية، أب، أو ورقة).

> كل المسارات تحت `/api/admin` وتتطلب Admin token.  
> الصفات ما زالت من الجذر — راجع `FRONTEND_DASHBOARD_CATEGORY_ATTRIBUTE_INHERITANCE.md`.

---

## 1) شنو تغيّر بالواجهة

| قبل | بعد |
|---|---|
| لازم تختار لآخر فئة فرعية (ورقة) قبل الحفظ | المستوى 1 يكفي. 2–6 اختيارية |
| `has_children === true` يعني لازم تختار ابن | `has_children` بس يظهر الـ select التالي **كخيار** |
| `category_id` = الورقة دائماً | `category_id` = آخر مستوى اختاره المستخدم |

الـ API كان يقبل أي `category_id`. المطلوب من الداشبورد: **لا تمنع Submit** إذا الفئة المختارة إلها أبناء.

```text
أكل                          → category_id = أكل
أكل > حبوب                   → category_id = حبوب
أكل > حبوب > رز > رز قصير    → category_id = رز قصير
```

---

## 2) فورم المنتج — قواعد الاختيار

1. المستوى 1 (الرئيسية) **مطلوب**.
2. إذا للفئة أبناء → أظهر select المستوى التالي، وسمّه **اختياري**.
3. المستخدم يقدر يوقف بأي مستوى ويحفظ.
4. `category_id` المرسل = آخر قيمة مختارة (أعمق select مش فاضي).
5. إذا غيّر مستوى أعلى → امسح المستويات الأعمق تحتها.

لا تعمل validation مثل:

```js
// غلط — لا تستخدم
if (selected.has_children) {
  return 'يجب اختيار فئة فرعية';
}
if (children_count > 0 && !nextLevelId) {
  disableSubmit();
}
```

الحفظ مسموح بمجرد وجود `category_id` (level 1 يكفي).

---

## 3) حقول الفئة من الـ API

`GET /api/admin/categories`

| الحقل | المعنى | استخدام بالفورم |
|---|---|---|
| `id` | معرف الفئة | قيمة الـ select |
| `parent_id` | `null` = رئيسية | فلترة المستوى 1 |
| `is_root` | `true` إذا رئيسية | نفس `parent_id === null` |
| `children_count` | عدد الأبناء | عرض الـ select التالي إذا `> 0` |
| `has_children` | `true` إذا إلها أبناء | **مو شرط** للحفظ |

```json
{
  "id": 5,
  "name": "أكل",
  "parent_id": null,
  "is_root": true,
  "children_count": 3,
  "has_children": true
}
```

هالفئة إلها أبناء، ومع ذلك تقدر تحفظ المنتج عليها: `category_id=5`.

---

## 4) إنشاء / تعديل منتج

نفس الـ endpoints:

- `POST /api/admin/products`
- `PUT /api/admin/products/{id}`

```text
category_id=5
```

أو أي id أعمق اختاره المستخدم.

`category_id` → `required|exists:categories,id`  
ما في شرط أنه ورقة.

---

## 5) الصفات (ما تغيّر منطقها)

بعد اختيار **المستوى 1** فوراً:

```http
GET /api/admin/category-attributes?category_id={rootCategoryId}
```

- الصفات تظهر من الجذر حتى لو المنتج محفوظ على أب أو ورقة.
- النزول للمستويات 2–6 **لا** يعيد طلب الصفات.
- غيّر الرئيسية فقط → أعد الطلب وامسح قيم التنويعات القديمة.

---

## 6) مثال React

```jsx
const [levels, setLevels] = useState([null, null, null, null, null, null]);
const [attributes, setAttributes] = useState([]);

const selectedCategoryId = [...levels].reverse().find(Boolean) ?? null;
const rootCategoryId = levels[0];

useEffect(() => {
  if (!rootCategoryId) {
    setAttributes([]);
    return;
  }

  api.get('/api/admin/category-attributes', {
    params: { category_id: rootCategoryId },
  }).then((res) => setAttributes(res.data.data ?? res.data));
}, [rootCategoryId]);

function onSelectLevel(levelIndex, categoryId) {
  setLevels((prev) => {
    const next = [...prev];
    next[levelIndex] = categoryId;
    for (let i = levelIndex + 1; i < 6; i++) next[i] = null;
    return next;
  });
}

function canSubmit() {
  return Boolean(selectedCategoryId); // level 1 يكفي
}

// الحفظ
// category_id: selectedCategoryId
```

عرض المستوى التالي:

```js
const showNextLevel = (category) => category?.has_children === true;
```

placeholder للمستويات 2–6: `اختياري`.

---

## 7) UI Checklist

- [ ] المستوى 1 مطلوب فقط
- [ ] المستويات 2–6 اختيارية + placeholder «اختياري»
- [ ] لا تمنع الحفظ إذا `has_children === true`
- [ ] `category_id` = آخر فئة مختارة (مو الورقة)
- [ ] الـ select التالي يظهر فقط إذا `has_children` / `children_count > 0`
- [ ] الصفات تُجلب عند المستوى 1، مو عند الورقة
- [ ] تغيير مستوى أعلى يمسح الأعمق تحته

### لا تغيّر

- سعر/كمية التنويعة — `FRONTEND_DASHBOARD_VARIANT_PRICE_QUANTITY.md`
- إسناد الصفات للجذر فقط — `FRONTEND_DASHBOARD_CATEGORY_ATTRIBUTE_INHERITANCE.md`
- `category_details` مربوطة بالفئة الحرفية المختارة

---

## 8) ملاحظات

- الشجرة: 1 رئيسية + حتى 5 فرعية (6 مستويات بالفورم).
- منتج على الأب يظهر للمستخدم عند تصفح الأب. ما يظهر إذا تصفحت ابنه.
- منتج على الابن يظهر عند تصفح الابن وعند تصفح أي أب فوقه.
- تطبيق Flutter (دوائر لكل المستويات): `FRONTEND_FLUTTER_CATEGORIES_CIRCULAR.md`
