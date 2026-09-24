# الداشبورد — كل حقول البنر مطلوبة

> **أرسلوا هذا الملف لفريق الداشبورد فقط.**  
> **آخر تحديث:** 25 أيلول 2026  
> Base: `/api/admin` + Admin token  
> **الباك جاهز بعد `git pull`**  
> ويب: [`WEB_BANNER_REQUIRED_FIELDS.md`](./WEB_BANNER_REQUIRED_FIELDS.md)  
> Flutter: [`FLUTTER_BANNER_REQUIRED_FIELDS.md`](./FLUTTER_BANNER_REQUIRED_FIELDS.md)

حقول البنر اللي كانت **اختيارية** صارت **إلزامية**.  
بدونها الإنشاء/التعديل يرجع **422**.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [الحقول](#2-الحقول)
3. [Endpoints](#3-endpoints)
4. [Payload](#4-payload)
5. [الفورم](#5-الفورم)
6. [غلط vs صح](#6-غلط-vs-صح)
7. [Checklist](#7-checklist)

---

## 1) القاعدة

| الحقل | قبل | بعد |
|--------|-----|-----|
| `title.ar` / `title.en` | اختياري | **مطلوب** |
| `description.ar` / `description.en` | اختياري | **مطلوب** |
| `button_text.ar` / `button_text.en` | اختياري | **مطلوب** |
| `link` | اختياري | **مطلوب** (URL صالح) |
| `expires_at` | مطلوب عند الإنشاء | **مطلوب** أيضاً عند التعديل (تاريخ بعد الآن) |
| `image` | مطلوب عند الإنشاء | مطلوب عند الإنشاء · **اختياري** عند التعديل إذا الصورة ما تغيّرت |
| `is_active` | اختياري | يبقى اختياري |

لا ترسلوا فورم ناقص. كل اللغات `ar` و `en` للحقول النصية.

---

## 2) الحقول

| الحقل | نوع | ملاحظات |
|--------|-----|---------|
| `title` | object `{ ar, en }` | نص العنوان |
| `description` | object `{ ar, en }` | الوصف |
| `button_text` | object `{ ar, en }` | نص زر البنر |
| `image` | file | jpeg/png/jpg/gif/webp/mp4/mov/avi/webm · max 8MB |
| `link` | string url | يفتح عند الضغط |
| `expires_at` | datetime | بعد `now` — بعد الانتهاء البنر يُحذف تلقائياً |
| `is_active` | boolean | اختياري |

---

## 3) Endpoints

```http
POST   /api/admin/banners
PUT    /api/admin/banners/{id}
PATCH  /api/admin/banners/{id}
GET    /api/admin/banners
GET    /api/admin/banners/{id}
```

قسم البانرات في الصفحة يبقى اختيار من القائمة الموجودة (`GET /api/admin/banners`) — هذا التغيير على **إنشاء/تعديل البنر نفسه**.

---

## 4) Payload

`Content-Type: multipart/form-data`

```text
title[ar]=عرض الصيف
title[en]=Summer Sale
description[ar]=خصم على الإلكترونيات
description[en]=Discount on electronics
button_text[ar]=تسوق الآن
button_text[en]=Shop now
link=https://example.com/offers
expires_at=2026-12-31 23:59:00
image=<file>
is_active=1
```

عند التعديل: نفس الحقول كلها مطلوبة ما عدا `image` إذا ما رفعتم صورة جديدة.

---

## 5) الفورم

- كل الحقول أعلاه = `required` في الـ UI (ما عدا `image` عند التعديل و`is_active`).
- لا تخلّوا `title` / `description` / `button_text` / `link` / `expires_at` فارغة أو مخفية كاختيارية.
- صورة عرضية ≈ **16:6**.
- بانر واحد في القسم = إعلان ثابت · عدة بانرات = سلايدر.

---

## 6) غلط vs صح

**غلط**

```text
title[ar]=عرض
# بدون title[en] → 422

link=   # فاضي → 422
expires_at=   # فاضي عند التعديل → 422
button_text غير مرسل → 422
```

**صح**

```text
title[ar]=...
title[en]=...
description[ar]=...
description[en]=...
button_text[ar]=...
button_text[en]=...
link=https://...
expires_at=2026-12-31 23:59:00
image=<file>   # إنشاء دائماً · تعديل إذا تغيّرت
```

---

## 7) Checklist

- [ ] فورم إنشاء: كل الحقول مطلوبة + صورة
- [ ] فورم تعديل: نفس الحقول مطلوبة · الصورة اختيارية إذا ما تغيّرت
- [ ] `ar` و `en` لكل من title / description / button_text
- [ ] `link` = URL صالح
- [ ] `expires_at` بعد الآن
- [ ] معالجة 422 وعرض أخطاء الحقول
