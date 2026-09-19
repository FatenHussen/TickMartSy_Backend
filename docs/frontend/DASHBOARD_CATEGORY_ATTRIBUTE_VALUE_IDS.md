# الداشبورد — قيم الصفات على المنتج (ID يبقى بعد إعادة التسمية)

> **أرسلوا هذا الملف لفريق الداشبورد فقط.**  
> **آخر تحديث:** 20 أيلول 2026  
> Base: `/api/admin` + Admin token  
> **الباك جاهز بعد `git pull`**  
> ويب: [`WEB_CATEGORY_ATTRIBUTE_VALUE_IDS.md`](./WEB_CATEGORY_ATTRIBUTE_VALUE_IDS.md)  
> Flutter: [`FLUTTER_CATEGORY_ATTRIBUTE_VALUE_IDS.md`](./FLUTTER_CATEGORY_ATTRIBUTE_VALUE_IDS.md)

المنتج مربوط **بـ ID القيمة** (`attributes_values_ids`)، مو بالاسم.  
قبل تغيير الاسم القيم تكون على المنتج. بعد التغيير لازم تبقى **نفس الـ IDs** ويتبدّل النص فقط.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [تعديل قيم الصفة — أرسلوا `id`](#2-تعديل-قيم-الصفة--أرسلوا-id)
3. [GET المنتج — اللون والقياس صفّين](#3-get-المنتج--اللون-والقياس-صفّين)
4. [فورم المتغيّر — select لكل صفة](#4-فورم-المتغيّر--select-لكل-صفة)
5. [الحفظ — لا تمسحوا القياس](#5-الحفظ--لا-تمسحوا-القياس)
6. [غلط vs صح](#6-غلط-vs-صح)
7. [Checklist](#7-checklist)

---

## 1) القاعدة

| عملية | الباك | المنتج |
|--------|--------|--------|
| إعادة تسمية قيمة **مع `id`** | نفس الصف، نفس الـ ID | يبقى مربوط — الاسم الجديد |
| إعادة تسمية **بدون `id`** | تحديث حسب الترتيب | يبقى مربوط إذا الترتيب نفسه |
| حفظ المنتج بلون فقط | **يبقي** القياس المحفوظ | ما ينمسح |
| حذف قيمة من قائمة الصفة | تُحذف وتُشال من المتغيّرات | المنتج يبقى |

`أحمر XS` في dropdown **واحد** = غلط واجهة. الباك يرجع قيمتين.

---

## 2) تعديل قيم الصفة — أرسلوا `id`

```http
GET /api/admin/category-attributes/{id}
GET /api/admin/category-attributes?category_id={rootCategoryId}
```

```json
{
  "id": 11,
  "name": { "ar": "القياس", "en": "Size" },
  "type": "square",
  "values": [
    { "id": 31, "name": { "ar": "صغير", "en": "Small" } },
    { "id": 32, "name": { "ar": "كبير", "en": "Large" } }
  ]
}
```

```http
PUT /api/admin/category-attributes/{id}
```

```text
type=square
name[ar]=القياس
name[en]=Size
values[0][id]=31
values[0][name][ar]=XS
values[0][name][en]=XS
values[1][id]=32
values[1][name][ar]=XL
values[1][name][en]=XL
```

| بدكم | ترسلوا |
|------|--------|
| إعادة تسمية | كل القيم + `id` + الاسم الجديد |
| إضافة | القديمة مع `id` + صف جديد **بدون** `id` |
| حذف قيمة | لا ترسلوا الصف المحذوف |
| ما تلمسوا القيم | **لا** مفتاح `values` |

---

## 3) GET المنتج — اللون والقياس صفّين

**قبل** تغيير الاسم و**بعده** نفس الشكل. افتحوا المنتج وتأكدوا من `attributes[]`:

```http
GET /api/admin/products/{id}
```

```json
{
  "variants": [
    {
      "id": 91,
      "attributes_values_ids": [40, 31],
      "attributes": [
        {
          "id": 40,
          "value": "أحمر",
          "type": "color",
          "category_attribute_id": 10
        },
        {
          "id": 31,
          "value": "XS",
          "type": "square",
          "category_attribute_id": 11
        }
      ]
    }
  ]
}
```

- `40` = أحمر · `31` = XS (أو «صغير» قبل إعادة التسمية)
- بعد «صغير → XS»: **نفس** `id: 31` — يتغيّر `value` فقط
- إذا `attributes` فيها صفّين والقياس غايب من الشاشة: الفورم ما رسم select ثاني

---

## 4) فورم المتغيّر — select لكل صفة

```
لون     [ أحمر ▼ ]
قياس    [ XS    ▼ ]
```

كل صفة من `GET /category-attributes?category_id=` = select. القيمة = `values[].id`.

```ts
const selectedIdFor = (variant, attributeId: number) =>
  variant.attributes.find((a) => a.category_attribute_id === attributeId)?.id
  ?? variant.attributes_values_ids.find((id) =>
      attribute.values.some((v) => v.id === id)
    )
  ?? null;

<Select
  value={selectedIdFor(variant, attribute.id)}
  options={attribute.values.map((v) => ({
    value: v.id,
    label: v.name.ar ?? v.name,
  }))}
/>
```

**ممنوع:**

```
صف المتغيّر:  [ أحمر XS ▼ ]
```

```ts
variant.attributes.map((a) => a.value).join(" ")
allAttributes.flatMap((a) => a.values)
attrs.find((a) => a.name === "القياس")
```

---

## 5) الحفظ — لا تمسحوا القياس

```text
variants[0][id]=91
variants[0][attributes_values_ids][]=40
variants[0][attributes_values_ids][]=31
```

أرسلوا **كل** IDs المختارة (لون + قياس + …).

إذا انرسل اللون فقط، الباك **يبقي** القياس القديم. الأفضل ترسّلوا الاثنين حتى الطلب واضح.

لا ترسلوا النص `أحمر XS`.

---

## 6) غلط vs صح

**غلط — اختفاء القيم:**

```ts
body.values = names.map((name) => ({ name: { ar: name, en: name } }));
form.append("variants[0][attributes_values_ids][]", colorId); // بلا قياس
```

**صح:**

```ts
body.values = rows.map((row) => ({
  ...(row.id ? { id: row.id } : {}),
  name: { ar: row.nameAr, en: row.nameEn },
}));

(v.attributes_values_ids ?? []).forEach((id, j) => {
  form.append(`variants[${i}][attributes_values_ids][${j}]`, String(id));
});
```

---

## 7) Checklist

- [ ] تعديل الصفة: `values[i][id]` من الـ GET
- [ ] قيمة جديدة فقط بدون `id`
- [ ] مفتاح الصف = `id` مو الاسم
- [ ] قبل إعادة التسمية: `GET product` فيه `attributes` للون **والقياس**
- [ ] بعد إعادة التسمية: نفس الـ IDs + الاسم الجديد
- [ ] فورم المنتج: **select لكل صفة** — ممنوع `أحمر XS`
- [ ] الحفظ يرسل كل `attributes_values_ids`
- [ ] المنتجات المقطوعة قبل الإصلاح: إعادة تعيين يدوي
