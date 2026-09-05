# الداشبoard — إنشاء منتج: صفات جزئية + حذف صور

> **الجمهور:** فريق الداشبoard  
> **تاريخ:** 31 آب 2026  
> **الباك:** جاهز — التعديل في **UI فقط**

---

## 1) المشكلة: الفئة فيها 3 صفات وأنا مجبرة أختار الثلاثة

### الوضع الذي تصفينه

```
الفئة: جينز
الصفات: لون + مقاس + تصميم  (3 dropdowns)

❌ الواجهة الحالية: لا يمكن «إضافة متغيّr» إلا إذا ملأت الثلاثة
✅ المطلوب: أختار اللي بدي — لون فقط، أو لون+مقاس، أو الثلاثة
```

### ماذا يقبل الباك؟

في `StoreRequest` / `UpdateRequest`:

```php
'variants.*.attributes_values_ids' => 'nullable|array',
'variants.*.attributes_values_ids.*' => 'required|integer|exists:attribute_values,id',
```

| ما ترسلينه | النتيجة |
|------------|---------|
| `[5]` — لون فقط | ✅ مقبول |
| `[5, 12]` — لون + مقاس | ✅ مقبول |
| `[5, 12, 20]` — الثلاثة | ✅ مقبول |
| `[]` أو بدون الحقل | ✅ مقبول (متغيّr بدون صفات — نادر) |

**الباك لا يتحقق** أنك اخترت كل صفات الفئة. يمنع فقط **تكرار نفس المجموعة** إذا أرسلتها مرتين.

### ❌ خطأ شائع في الفرونت (سبب المشكلة)

```js
// غلط — يمنع الإضافة إذا dropdown فاضي
const allFilled = categoryAttributes.every(a => selections[a.id]);
if (!allFilled) {
  toast('اختر كل الصفات');
  return;
}

// غلط — required على كل select
<Select required ... />
```

### ✅ السلوك الصحيح

```js
function onAddVariant(selections) {
  // فقط القيم المختارة — الباقي يتجاهل
  const ids = selections
    .map(s => s.valueId)
    .filter(id => id != null && id !== '');

  // اختياري: على الأقل صفة واحدة (UX) — أو اسمح [] إذا بدكم
  if (ids.length === 0) {
    toast('اختر صفة واحدة على الأقل (أو أضف متغيّr بدون صفات)');
    return;
  }

  if (isDuplicateVariant(localVariants, ids)) {
    toast('هذا المتغيّr موجود — غيّري قيمة واحدة على الأقل');
    return;
  }

  addLocalVariant({ attributes_values_ids: ids, /* ... */ });
}
```

### أمثلة مسموحة

| إضافة | attributes_values_ids | كارد يعرض |
|-------|----------------------|-----------|
| أخضر فقط | `[3]` | ● green |
| أخضر + L | `[3, 12]` | ● green · L |
| أخضر + L + تعبان | `[3, 12, 20]` | ● green · L · تعبان |
| L + تعبان (بدون لون) | `[12, 20]` | ● L · تعبان |

### UI مقترح

```
┌─ إضافة متغيّr ─────────────────────────────┐
│  لون      ▼  [ اختياري — — ]               │
│  مقاس     ▼  [ L            ]               │
│  تصميم    ▼  [ —            ]               │
│                                             │
│  [ + إضافة متغيّr ]   ← يشتغل بمقاس فقط    │
└─────────────────────────────────────────────┘
```

- **لا `required`** على أي dropdown.
- Placeholder: «اختياري» أو «—».
- Label **بدون** نجمة `*`.
- زر «إضافة» يفعّل إذا **صفة واحدة على الأقل** مختارة (أو حسب قراركم: حتى بدون صفات).

### Payload

```text
# لون + مقاس فقط — بدون تصميم
variants[0][attributes_values_ids][0]=3
variants[0][attributes_values_ids][1]=12
# لا ترسلي index 2 — الباك لا يتوقع 3 قيم
```

---

## 2) المشكلة: تاب الصور — ما في حذف إذا رفعت صورة غلط

### الوضع

في **إنشاء منتج** (`/products/create`) الصور بعد الرفع تكون في **state محلي** (File + preview) — **لم تُرفع للسيرفر بعد**.

لذلك **حذف الصورة = إزالة من state** — لا يحتاج API قبل «إنشاء المنتج».

### ❌ خطأ

- عرض preview بدون زر حذف
- أو الاعتماد على `DELETE /media/{id}` قبل الحفظ (ما في id بعد)

### ✅ إنشاء منتج — state محلي

```js
const [localMedia, setLocalMedia] = useState([]);

function onPickImages(files) {
  const next = [...files].map(file => ({
    _localKey: crypto.randomUUID(),
    file,
    previewUrl: URL.createObjectURL(file),
  }));
  setLocalMedia(prev => [...prev, ...next]);
}

function removeLocalImage(localKey) {
  setLocalMedia(prev => {
    const item = prev.find(m => m._localKey === localKey);
    if (item?.previewUrl) URL.revokeObjectURL(item.previewUrl);
    return prev.filter(m => m._localKey !== localKey);
  });
}
```

### UI مقترح

```
┌─ صور المنتج ──────────────────────────────┐
│  [thumb1 ×]  [thumb2 ×]  [thumb3 ×]       │
│  [ + رفع صور ]                            │
└───────────────────────────────────────────┘
     ↑
  زر × على كل صورة — يستدعي removeLocalImage
```

```jsx
{localMedia.map(m => (
  <div key={m._localKey} className="relative">
    <img src={m.previewUrl} alt="" />
    <button
      type="button"
      aria-label="حذف الصورة"
      onClick={() => removeLocalImage(m._localKey)}
    >
      ×
    </button>
  </div>
))}
```

### عند «إنشاء المنتج»

```text
POST /api/admin/products
Content-Type: multipart/form-data

media[0]=<file1>
media[1]=<file2>
```

- ترسلي **فقط** ما بقي في `localMedia[]` بعد الحذف.

---

## 3) تعديل منتج — حذف صور محفوظة

بعد الحفظ، الصور لها `id` من الباك.

```text
PUT /api/admin/products/{id}

existing_media_ids[]=5
existing_media_ids[]=8
media[]= (صورة جديدة اختيارية)
```

| السينario | ماذا ترسلين |
|-----------|-------------|
| حذف صورة 11 من 10,11,12 | `existing_media_ids`: 10 و 12 فقط |
| حذف الكل | `existing_media_ids[]` فارغ أو `[]` |
| إضافة بدون حذف | كل ids القديمة + `media[]` جديدة |
| لا تغيير على الصور | **لا ترسلي** `existing_media_ids` ولا `media` |

> المرجع الكامل: [`api/PRODUCT_IMAGES_UPDATE_GUIDE.md`](../api/PRODUCT_IMAGES_UPDATE_GUIDE.md)

### صور المتغيّr (edit)

```text
variants[0][existing_images_ids][]=15
variants[0][images][]=<file>
```

---

## 4) Checklist للمطور

### صفات المتغيّr

- [ ] احذفي `required` من dropdowns الصفات
- [ ] احذفي «اختر كل الصفات» / «املأ الثلاثة»
- [ ] `attributes_values_ids` = **فقط** القيم المختارة (filter null)
- [ ] duplicate = نفس مجموعة ids بالضبط
- [ ] placeholder «اختياري» على dropdowns الفارغة

### صور المنتج

- [ ] **create:** زر × على كل preview → `removeLocalImage`
- [ ] **create:** `URL.revokeObjectURL` عند الحذف
- [ ] **edit:** زر × → يزيل id من `existing_media_ids` + يخفي من UI
- [ ] **edit:** رفع جديد → يضاف لـ `media[]`

---

## 5) ملخص

| المشكلة | السبب | الحل |
|---------|--------|------|
| إلزام 3 صفات | validation في الداشبoard | اختيار جزئي — الباك يقبل |
| لا حذف صورة عند الإنشاء | UI ناقص | زر × على state محلي |
| لا حذف صورة عند التعديل | UI ناقص | `existing_media_ids` + زر × |

**باگ مرتبط (5 أيلول):** توست «الكمية موجبة» رغم كمية المتغيّر معبّاة — [`DASHBOARD_PRODUCT_QUANTITY_VALIDATION.md`](./DASHBOARD_PRODUCT_QUANTITY_VALIDATION.md)

**الباك جاهز — عدّلوا الداشبoard فقط.**
