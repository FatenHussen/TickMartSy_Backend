# صور المتغيّر — الموقع والداشبورد

> **الجمهور:** ويب + داشبورد + Flutter  
> **الباك:** جاهز بعد `git pull` (حفظ صور المتغيّر كان ينمسح بعد «تحديث المنتج» — صار يثبّت)  
> **تظهر على الموقع** في `/product/{id}` من `GET /api/user/products/{id}`  
> **تاريخ:** 19 أيلول 2026

---

## هل الشغلة محققة بالباك؟

**البيانات: نعم. تبديل المعرض على الشاشة: لا — هذا شغل الموقع.**

الـ API طلب واحد. ما في «حدث اختيار لون». الموقع يختار الصف من `shop_variants` ويغيّر المعرض من `images` تبع هالصف.

| | الباك | الموقع |
|--|--------|--------|
| صور المنتج الرئيسية | `data.images[].path` دائماً | معرض البداية |
| صور المتغيّر إن وُجدت | `shop_variants[i].has_variant_images === true` + `images[].path` | استبدلوا المعرض |
| متغيّر بلا صور خاصة | `has_variant_images === false` — `images` = نفس صور المنتج (fallback) | ابقوا على صور المنتج |

إذا المعرض مربوط بـ `product.images` فقط، اختيار اللون **ما رح يغيّر الصورة** حتى لو الأدمن رفع صور لكل متغيّر.

---

## هل نغيّر الموقع أو الداشبورد؟

| فريق | هل في شغل؟ | ماذا بالضبط |
|------|-------------|--------------|
| **الموقع (web)** | **نعم — معرض صفحة المنتج** | عند تغيير اللون/المقاس: إذا `has_variant_images` استخدموا `selected.images` وإلا `product.images`. الصورة من `path`. |
| **الداشبورد** | **نعم — عرض + إبقاء** | اعرضوا الصور المحفوظة من `variants[].images[].url`. عند الإضافة فوق القديمة أرسلوا `existing_images_ids`. حقل الملف بعد الحفظ فاضي وهذا طبيعي. |
| **Flutter** | نفس الموقع | `has_variant_images` + `images[].path` |

---

## 1) الموقع — معرض `/product/{id}`

```http
GET /api/user/products/{id}
Accept-Language: ar
```

```json
{
  "images": [
    { "id": 10, "path": "https://…/storage/product/product/main.webp" }
  ],
  "shop_variants": [
    {
      "id": 881,
      "sku": "SKU-GD719HTA-0000FF",
      "has_variant_images": true,
      "images": [
        { "id": 44, "path": "https://…/storage/product-variant/variant/blue.webp" }
      ]
    },
    {
      "id": 882,
      "sku": "SKU-GD719HTA-000000",
      "has_variant_images": false,
      "images": [
        { "id": 10, "path": "https://…/storage/product/product/main.webp" }
      ]
    }
  ]
}
```

`path` رابط كامل. على الموقع **مو** `url`.

```ts
function galleryFor(
  product: { images?: { path: string }[]; thumbnail?: string | null },
  selected?: { has_variant_images?: boolean; images?: { path: string }[] } | null
) {
  if (selected?.has_variant_images && selected.images?.length) {
    return selected.images;
  }
  if (product.images?.length) return product.images;
  return product.thumbnail ? [{ path: product.thumbnail }] : [];
}

// أول دخول الصفحة + كل ضغطة لون/مقاس
setGallery(galleryFor(product, selectedVariant));
```

**غلط (هذا سبب إن الصورة ما تتبدل):**

```ts
<img src={product.images[0].path} />           // ثابت — تجاهل المتغيّر
<img src={selectedVariant.image} />            // الحقل اسمه images[] مو image
<img src={selectedVariant.images[0].url} />    // الموقع: path مو url
```

### قواعد المعرض

1. أول تحميل: صور المنتج الرئيسية (`data.images`).
2. اختيار متغيّر **فيه** صور → المعرض = `selected.images`.
3. اختيار متغيّر **بلا** صور خاصة → ارجعوا لصور المنتج. لا تخلّوا المعرض فاضي.
4. نفس المصدر لسعر/كمية المتغيّر: الصف المختار من `shop_variants` (مطابقة `attributes`).

`has_variant_images === false` مع `images` مش فاضي = fallback من المنتج. لا تعتبروا وجود عناصر في `images` دليل إن للمتغيّر صور خاصة.

---

## 2) الداشبورد — إنشاء / تعديل منتج

ما في API جديد.

```http
GET /api/admin/products/{id}
```

```json
{
  "variants": [
    {
      "id": 91,
      "sku": "SKU-GD719HTA-0000FF",
      "images": [
        { "id": 44, "url": "https://…/storage/product-variant/variant/blue.webp" }
      ]
    }
  ]
}
```

هنا الحقل **`url`** (مو `path`).

ارسموا thumbnails من `url`. لا تعتمدوا على `<input type="file">` بعد الحفظ — المتصفح يفرّغه دائماً.

### الحفظ `POST/PUT /api/admin/products` (multipart)

| بدكم | ترسلوا |
|------|--------|
| ما تلمسوا الصور | لا `images` ولا `existing_images_ids` — الباك يخلي القديمة |
| تضيفوا فوق القديمة | `existing_images_ids[]` = IDs من الـ GET + ملفات جديدة `images[]` |
| تستبدلوا الكل | ملفات `images[]` بدون `existing_images_ids` |
| تحذفوا الكل | `existing_images_ids` مصفوفة فاضية |

```text
variants[0][id]=91
variants[0][existing_images_ids][]=44
variants[0][images][]=<ملف جديد>
```

**غلط:** حفظ المنتج بعد اختيار ملف بدون ما تُعرض `url` لاحقاً، أو إرسال `variants[]` بدون `existing_images_ids` مع ملفات جديدة وأنتم تقصدون الإضافة (هذا يستبدل الكل).

---

## 3) Checklist

**ويب / Flutter**

- [ ] معرض البداية = `data.images`
- [ ] عند تغيير اللون/المقاس: `galleryFor(product, selectedVariant)`
- [ ] الصورة من `path`
- [ ] `has_variant_images === false` → صور المنتج، مو معرض فاضي
- [ ] لا تربطوا المعرض بـ `product.images` فقط

**داشبورد**

- [ ] معاينة `variants[].images[].url` بعد الحفظ
- [ ] الإضافة فوق القديمة ترسل `existing_images_ids`
- [ ] كل متغيّر يرفع صوره (اللون الأزرق ≠ الأسود) وإلا الموقع يبقى على صورة المنتج

**الباك**

- [ ] `git pull` ثم إعادة حفظ صور المتغيّرات من الداش (اللي انمسحت قبل الإصلاح لازم تترفع مرة ثانية)
