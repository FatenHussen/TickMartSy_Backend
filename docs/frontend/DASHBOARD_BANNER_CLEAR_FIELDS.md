# الداشبورد — مسح حقول البنر عند التعديل

> **أرسلوا هذا الملف لفريق الداشبورد فقط.**  
> **آخر تحديث:** 25 أيلول 2026  
> Base: `/api/admin` + Admin token  
> **الباك جاهز بعد `git pull`**  
> ويب: [`WEB_BANNER_CLEAR_FIELDS.md`](./WEB_BANNER_CLEAR_FIELDS.md)  
> Flutter: [`FLUTTER_BANNER_CLEAR_FIELDS.md`](./FLUTTER_BANNER_CLEAR_FIELDS.md)  
> الحقول الاختيارية: [`DASHBOARD_BANNER_REQUIRED_FIELDS.md`](./DASHBOARD_BANNER_REQUIRED_FIELDS.md)

تعديل البنر ومسح الحقول صار **ينحفظ**.  
الرد بعد المسح يرجع `{ "ar": null, "en": null }` — مو `[]`.  
اعرضوا الفورم من الرد الجديد، مو من القيم اللي كانت قبل الحفظ.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [Endpoints](#2-endpoints)
3. [Payload](#3-payload)
4. [الرد](#4-الرد)
5. [الفورم](#5-الفورم)
6. [غلط vs صح](#6-غلط-vs-صح)
7. [Checklist](#7-checklist)

---

## 1) القاعدة

| الحقل | إذا انرسل فاضي | إذا ما انرسل |
|--------|----------------|--------------|
| `title[ar]` / `title[en]` | تُمسح هاي اللغة | اللغة الثانية تبقى |
| `description[ar]` / `description[en]` | نفس المنطق | نفس المنطق |
| `button_text[ar]` / `button_text[en]` | نفس المنطق | نفس المنطق |
| `link` | `null` | القيمة القديمة تبقى |
| `expires_at` | `null` = بنر **دائم** | تاريخ الانتهاء القديم يبقى |
| `image` | — | الصورة القديمة تبقى |

`title` / `description` / `button_text` إذا انرسلوا `null` (مو object) → **كل** اللغات تُمسح.

---

## 2) Endpoints

```http
POST   /api/admin/banners/{id}     + _method=PATCH
PUT    /api/admin/banners/{id}
PATCH  /api/admin/banners/{id}
GET    /api/admin/banners/{id}
```

`Content-Type: multipart/form-data`  
لا تضبطوا الهيدر يدوياً — خلّوا المتصفح يضيف الـ boundary.

مع ملف أو فورم فيه صورة: أرسلوا **POST** + `_method=PATCH` (أو `PUT`).  
`PATCH`/`PUT` خام مع `multipart` ما بيوصل للباك، بيرجع نجاح والبيانات ما تتغير.

---

## 3) Payload

### مسح كل النصوص والرابط وتاريخ الانتهاء

```text
_method=PATCH
title[ar]=
title[en]=
description[ar]=
description[en]=
button_text[ar]=
button_text[en]=
link=
expires_at=
```

### مسح العربي فقط والإبقاء على الإنجليزي

```text
_method=PATCH
title[ar]=
title[en]=Summer Sale
```

### جعل البنر دائماً

```text
expires_at=
```

---

## 4) الرد

`GET` ورد التعديل نفس الشكل. المفاتيح **دايماً موجودة**:

```json
{
  "title": { "ar": null, "en": null },
  "description": { "ar": null, "en": null },
  "button_text": { "ar": null, "en": null },
  "link": null,
  "expires_at": null,
  "image_url": "https://…/storage/….jpg",
  "is_active": true
}
```

- `null` = الحقل ممسوح. عبّوا الإنبت فاضي.
- لا تتعاملوا مع `[]` كـ «ما في تغيير» — الباك ما عاد يرجّع مصفوفة فاضية.

---

## 5) الفورم

- ألحقوا كل حقل بالـ `FormData` حتى لو فاضي. حذف المفتاح = الإبقاء على القديم.
- بعد `200`: اضبطوا الحالة من `data` في الرد.
- `value ?? previous` خطأ هنا: `null` لازم يفرّغ الحقل.
- `expires_at: null` → تاريخ الانتهاء فاضي، والليبل «فارغ = عرض دائم».
- لا تعيدوا إرسال النص القديم إذا المستخدم مسحه.

---

## 6) غلط vs صح

**غلط**

```js
if (title) formData.append('title[ar]', title.ar);
// مسح الحقل = المفتاح ما انرسل = النص القديم يبقى + توست نجاح

setTitle(data.title?.ar || previousTitle);
// null يصير النص القديم
```

**صح**

```js
formData.append('title[ar]', title.ar ?? '');
formData.append('title[en]', title.en ?? '');
formData.append('description[ar]', description.ar ?? '');
formData.append('description[en]', description.en ?? '');
formData.append('button_text[ar]', buttonText.ar ?? '');
formData.append('button_text[en]', buttonText.en ?? '');
formData.append('link', link ?? '');
formData.append('expires_at', expiresAt ?? '');
formData.append('_method', 'PUT');

setTitle({
  ar: data.title?.ar ?? '',
  en: data.title?.en ?? '',
});
```

---

## 7) Checklist

- [ ] فورم التعديل يرسل المفاتيح الفاضية، ما يحذفها من الـ body
- [ ] `multipart` عبر `POST` + `_method=PATCH`
- [ ] بعد النجاح: `null` يفرّغ الإنبت
- [ ] `title` / `description` / `button_text` تُقرأ كـ `{ ar, en }` وفيها `null`
- [ ] `link` فاضي → `null`
- [ ] `expires_at` فاضي → `null` = دائم
- [ ] الصورة ما تنمسح إذا ما تغيّرت
