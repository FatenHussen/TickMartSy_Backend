# الويب — تكبير صورة المنتج (lightbox / zoom)

> **أرسلوا هذا الملف لفريق الويب فقط.**  
> **آخر تحديث:** 21 أيلول 2026  
> Base: `/api/user` — **ما في endpoint جديد**  
> **الباك جاهز — التعديل UI فقط**  
> Flutter: [`FLUTTER_PRODUCT_IMAGE_ZOOM.md`](./FLUTTER_PRODUCT_IMAGE_ZOOM.md)  
> صور المتغيّر: [`PRODUCT_VARIANT_IMAGES_WEB_DASHBOARD.md`](./PRODUCT_VARIANT_IMAGES_WEB_DASHBOARD.md)

على صفحة المنتج: الضغط على الصورة → lightbox بملء الشاشة مع **تكبير وتدقيق بالتفاصيل**.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [مصدر الصور](#2-مصدر-الصور)
3. [السلوك](#3-السلوك)
4. [غلط vs صح](#4-غلط-vs-صح)
5. [Checklist](#5-checklist)

---

## 1) القاعدة

| المكان | المطلوب |
|--------|---------|
| معرض `/product/{id}` | ✅ click → lightbox |
| الـ lightbox | ✅ zoom (عجلة / pinch على الموبايل) + pan + تنقّل بين الصور |
| API جديد | ❌ لا |

---

## 2) مصدر الصور

نفس المعرض الحالي من `GET /api/user/products/{id}`:

- `selected.images[].path` إن `has_variant_images`
- وإلا `product.images[].path`

---

## 3) السلوك

1. click على الصورة الرئيسية أو thumbnail  
2. overlay داكن + صورة كبيرة  
3. zoom in/out + سحب بعد التكبير  
4. أسهم / swipe لباقي الصور  
5. Esc أو زر X أو click على الخلفية → إغلاق

---

## 4) غلط vs صح

**غلط:** صورة ثابتة أو `<a target=_blank>` بدون zoom داخل الصفحة.  
**صح:** lightbox داخل الصفحة مع zoom على صور المعرض الحالي.

---

## 5) Checklist

- [ ] click يفتح lightbox
- [ ] zoom + pan
- [ ] تنقّل بين صور المعرض
- [ ] القائمة تتبع المتغيّر المختار
- [ ] الإغلاق بدون إعادة جلب المنتج

**الباك جاهز — الموقع يتبع هذا الملف.**
