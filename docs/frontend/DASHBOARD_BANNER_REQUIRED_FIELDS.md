# الداشبورد — حقول البنر اختيارية

> **أرسلوا هذا الملف لفريق الداشبورد فقط.**  
> **آخر تحديث:** 25 أيلول 2026  
> Base: `/api/admin` + Admin token  
> **الباك جاهز بعد `git pull`**  
> ويب: [`WEB_BANNER_REQUIRED_FIELDS.md`](./WEB_BANNER_REQUIRED_FIELDS.md)  
> Flutter: [`FLUTTER_BANNER_REQUIRED_FIELDS.md`](./FLUTTER_BANNER_REQUIRED_FIELDS.md)

**كل حقول البنر اختيارية** ما عدا الصورة عند **الإنشاء**.  
بدون `expires_at` → البنر **دائم** (ما ينحذف تلقائياً).  
مسح الحقول عند التعديل: [`DASHBOARD_BANNER_CLEAR_FIELDS.md`](./DASHBOARD_BANNER_CLEAR_FIELDS.md)

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

| الحقل | الحالة |
|--------|--------|
| `title.ar` / `title.en` | اختياري |
| `description.ar` / `description.en` | اختياري |
| `button_text.ar` / `button_text.en` | اختياري |
| `link` | اختياري (إذا أُرسل → URL صالح) |
| `expires_at` | اختياري · **فارغ / غير مرسل = دائم** |
| `image` | **مطلوب عند الإنشاء** · اختياري عند التعديل إذا الصورة ما تغيّرت |
| `is_active` | اختياري |

لا تخلّوا الحقول `required` في الـ UI (ما عدا الصورة عند الإنشاء).

---

## 2) الحقول

| الحقل | نوع | ملاحظات |
|--------|-----|---------|
| `title` | object `{ ar, en }` | اختياري |
| `description` | object `{ ar, en }` | اختياري |
| `button_text` | object `{ ar, en }` | اختياري |
| `image` | file | jpeg/png/jpg/gif/webp/mp4/mov/avi/webm · max 8MB |
| `link` | string url | يفتح عند الضغط — اختياري |
| `expires_at` | datetime \| null | إذا وُجد → بعد `now` ويُحذف البنر تلقائياً عندها · **null = دائم** |
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

قسم البانرات في الصفحة يبقى اختيار من القائمة الموجودة (`GET /api/admin/banners`).

---

## 4) Payload

`Content-Type: multipart/form-data`

### إنشاء — صورة فقط (باقي الحقول اختيارية)

```text
image=<file>
```

### إنشاء — مع نصوص وتاريخ انتهاء

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

### دائم (بدون انتهاء)

لا ترسلوا `expires_at`، أو أرسلوه فاضي → يُحفظ `null` = دائم.

عند التعديل لمسح تاريخ الانتهاء وإبقاء البنر دائماً: أرسلوا `expires_at=` (فاضي).

---

## 5) الفورم

- كل الحقول = **اختيارية** في الـ UI ما عدا `image` عند الإنشاء.
- لا تعرضوا خطأ «العنوان بالإنكليزية مطلوب» أو مشابه — الباك ما عاد يرفضها.
- `expires_at`: label يوضح «اختياري — فارغ = عرض دائم».
- صورة عرضية ≈ **16:6**.
- بانر واحد في القسم = إعلان ثابت · عدة بانرات = سلايدر.

---

## 6) غلط vs صح

**غلط**

```text
# فورم تعديل: title[en] مطلوب في الـ UI → يمنع الحفظ بدون سبب
expires_at مطلوب في الـ UI → خطأ
```

**صح**

```text
# تعديل بدون عنوان إنجليزي — مقبول
title[ar]=عرض
# بدون title[en] → OK

# بدون expires_at → دائم
image=<file>   # إنشاء فقط مطلوب

# مع انتهاء
expires_at=2026-12-31 23:59:00
```

---

## 7) Checklist

- [ ] فورم إنشاء: الصورة مطلوبة · باقي الحقول اختيارية
- [ ] فورم تعديل: **كل** الحقول اختيارية (بما فيها الصورة إذا ما تغيّرت)
- [ ] لا validation على `title` / `description` / `button_text` / `link` كـ required
- [ ] `expires_at` اختياري · فارغ = دائم
- [ ] إذا وُجد `link` → URL صالح
- [ ] إذا وُجد `expires_at` → تاريخ بعد الآن
- [ ] معالجة 422 فقط للأخطاء الحقيقية (صورة ناقصة عند الإنشاء، URL غلط، تاريخ ماضي)
