# Flutter — تكبير صورة المنتج (pinch / lightbox)

> **أرسلوا هذا الملف لفريق Flutter فقط.**  
> **آخر تحديث:** 21 أيلول 2026  
> Base: `/api/user` — **ما في endpoint جديد**  
> **الباك جاهز — التعديل UI فقط**  
> صور المتغيّر: [`PRODUCT_VARIANT_IMAGES_WEB_DASHBOARD.md`](./PRODUCT_VARIANT_IMAGES_WEB_DASHBOARD.md)  
> ويب: [`WEB_PRODUCT_IMAGE_ZOOM.md`](./WEB_PRODUCT_IMAGE_ZOOM.md)

على شاشة المنتج: الضغط على الصورة → تفتح بملء الشاشة ويقدر المستخدم **يكبّر ويدقّق بالتفاصيل** (pinch-to-zoom + pan).

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [مصدر الصور](#2-مصدر-الصور)
3. [السلوك](#3-السلوك)
4. [تنفيذ مقترح](#4-تنفيذ-مقترح)
5. [غلط vs صح](#5-غلط-vs-صح)
6. [Checklist](#6-checklist)

---

## 1) القاعدة

| المكان | المطلوب |
|--------|---------|
| معرض صفحة المنتج (الصورة الكبيرة + المصغّرات) | ✅ ضغط → عارض كامل الشاشة |
| العارض | ✅ pinch zoom + سحب (pan) + تمرير بين الصور |
| كروت القائمة / منتجات مشابهة | ❌ مش مطلوب (اختياري لاحقاً) |
| API جديد | ❌ لا |

الهدف: المستخدم يدقّق بالخياطة / القماش / اللون — دائرة رمادية أو صورة ثابتة بدون تكبير **ما تكفي**.

---

## 2) مصدر الصور

نفس معرض شاشة المنتج الحالي — **لا طلب إضافي**:

```http
GET /api/user/products/{id}
```

| الحالة | المصفوفة |
|--------|----------|
| متغيّر فيه صور خاصة | `selected.images[].path` عندما `has_variant_images == true` |
| وإلا | `product.images[].path` |
| fallback | `thumbnail` إن المعرض فاضي |

عند تبديل لون/مقاس: حدّثوا قائمة العارض بنفس منطق المعرض.

---

## 3) السلوك

1. المستخدم يضغط الصورة الرئيسية أو أي مصغّرة في المعرض  
2. يفتح **fullscreen overlay / route** فوق شاشة المنتج  
3. عرض الصورة الحالية + إمكانية السحب لليمين/اليسار لباقي صور المعرض  
4. **Pinch-to-zoom** (إصبعان) + **pan** بعد التكبير  
5. زر إغلاق (X) أو السحب للأسفل أو زر الرجوع → يرجع لشاشة المنتج بنفس الـ scroll  
6. خلفية داكنة شبه شفافة؛ الـ AppBar/السلة ما تغطي الصورة أثناء المعاينة

حد أدنى للتكبير: ×1 · حد أقصى مقترح: ×4 أو ×5.

---

## 4) تنفيذ مقترح

حزم شائعة: `photo_view` أو `InteractiveViewer` + `PageView`.

```dart
GestureDetector(
  onTap: () => openProductGallery(
    images: galleryPaths, // List<String> من path
    initialIndex: currentIndex,
  ),
  child: Hero(
    tag: 'product-image-$currentIndex',
    child: Image.network(galleryPaths[currentIndex], fit: BoxFit.cover),
  ),
);
```

داخل العارض:

```dart
PageView.builder(
  itemCount: images.length,
  controller: PageController(initialPage: initialIndex),
  itemBuilder: (_, i) => PhotoView(
    imageProvider: NetworkImage(images[i]),
    minScale: PhotoViewComputedScale.contained,
    maxScale: PhotoViewComputedScale.covered * 4,
  ),
);
```

- `Hero` اختياري لانتقال أنعم  
- أظهروا مؤشر صفحة `1 / N` إن في أكثر من صورة  
- عطلوا scroll شاشة المنتج تحت الـ overlay أثناء الفتح

---

## 5) غلط vs صح

**غلط**

```dart
// صورة ثابتة بدون onTap
Image.network(url);
// أو Dialog يعرض الصورة بنفس الحجم بدون zoom
```

**صح**

```dart
onTap → fullscreen gallery + pinch zoom + pan بين صور المعرض الحالي
```

---

## 6) Checklist

- [ ] ضغط الصورة الرئيسية يفتح عارض ملء الشاشة
- [ ] ضغط المصغّرة يفتح على نفس الفهرس
- [ ] pinch-to-zoom + pan يشتغلون
- [ ] تمرير بين صور المعرض (إن > 1)
- [ ] الإغلاق يرجع لشاشة المنتج بدون إعادة تحميل
- [ ] قائمة الصور تتبع المتغيّر المختار (`has_variant_images`)
- [ ] روابط مكسورة / فاضي → ما يكسر الشاشة

**الباك جاهز — التطبيق يتبع هذا الملف.**
