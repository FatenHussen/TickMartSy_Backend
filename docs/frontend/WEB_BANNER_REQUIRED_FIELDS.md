# الويب — بنر: كل الحقول متوفرة للعرض

> **أرسلوا هذا الملف لفريق الويب فقط.**  
> **آخر تحديث:** 25 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> داشبورد: [`DASHBOARD_BANNER_REQUIRED_FIELDS.md`](./DASHBOARD_BANNER_REQUIRED_FIELDS.md)  
> Flutter: [`FLUTTER_BANNER_REQUIRED_FIELDS.md`](./FLUTTER_BANNER_REQUIRED_FIELDS.md)

حقول البنر من الأدمن **اختيارية** (ما عدا الصورة عند الإنشاء).  
على الموقع: اعرضوا العنوان والوصف ونص الزر والرابط إن وُجدت — مو الصورة وحدها. Fallback فاضي للحقول الناقصة.

ما في endpoint جديد: نفس أقسام الصفحة.

**ترتيب الصفحة:** البنر أول محتوى تحت الـ Nav، **قبل** كرت تتبع الطلب — [`WEB_HOME_BANNER_BEFORE_TRACK.md`](./WEB_HOME_BANNER_BEFORE_TRACK.md)

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [مصدر البيانات](#2-مصدر-البيانات)
3. [شكل العنصر](#3-شكل-العنصر)
4. [العرض](#4-العرض)
5. [غلط vs صح](#5-غلط-vs-صح)
6. [Checklist](#6-checklist)

---

## 1) القاعدة

| الحقل | أين | العرض |
|--------|-----|--------|
| الصورة | `item.image` | ✅ أساسية · نسبة ≈ 16:6 |
| العنوان | `item.title` | ✅ اعرضوه |
| الوصف | `item.desc` | ✅ اعرضوه |
| نص الزر | `item.button_text` | ✅ على الزر / الـ CTA |
| الرابط | `items[].link` | ✅ الضغط يفتح الرابط |

أي بنر قد ينقصه عنوان أو وصف أو زر أو رابط — استخدموا fallback فاضي مو تخفوا الكارد.

---

## 2) مصدر البيانات

```http
GET /api/user/sections?page_slug=home
GET /api/user/categories/{id}/page
```

قسم بنر: `manual_model: "banner"` أو `display_type_id` الخاص بالبنر · غالباً `layout: slider` + `variant: horizontal`.

- عنصر واحد = إعلان ثابت  
- عدة عناصر = سلايدر / carousel  
- تجاهلوا قسماً `items` فارغة

---

## 3) شكل العنصر

```json
{
  "id": 1,
  "link": "https://example.com/offers",
  "order": 0,
  "item": {
    "id": 12,
    "title": "عرض الصيف",
    "desc": "خصم على الإلكترونيات",
    "button_text": "تسوق الآن",
    "image": "https://…/storage/banner/….jpg",
    "price": null,
    "discount": null
  }
}
```

- `title` / `desc` / `button_text` = **String** حسب `Accept-Language` (مو `{ ar, en }` داخل الأقسام).
- `link` على **مستوى العنصر** (`items[i].link`) — مو داخل `item` فقط.
- الصورة = `item.image` (URL كامل).

---

## 4) العرض

```js
const banner = section.items[i];
const title = banner.item?.title ?? '';
const desc = banner.item?.desc ?? '';
const cta = banner.item?.button_text ?? '';
const image = banner.item?.image;
const href = banner.link;

// صورة عرضية
// Aspect ratio ≈ 16 / 6

// onClick / <a href={href}>
```

- لا تعتمدوا على الصورة وحدها إذا التصميم فيه عنوان/زر.
- لا تفترضوا `link` فاضي على بنرات جديدة.
- استخدموا `background_color` للقسم و `background_card_color` للكارد إن وُجدت.

---

## 5) غلط vs صح

**غلط**

```js
// تجاهل title / desc / button_text
<img src={item.image} />

// قراءة link من المكان الغلط فقط
item.link  // غالباً undefined — الصحيح: items[i].link
```

**صح**

```js
<a href={banner.link}>
  <img src={banner.item.image} alt={banner.item.title} />
  <h3>{banner.item.title}</h3>
  <p>{banner.item.desc}</p>
  <button type="button">{banner.item.button_text}</button>
</a>
```

---

## 6) Checklist

- [ ] قراءة `title` · `desc` · `button_text` من `item`
- [ ] الضغط يفتح `items[i].link`
- [ ] نسبة صورة ≈ 16:6
- [ ] عنصر واحد ثابت · عدة = سلايدر
- [ ] قسم `items` فارغ → لا تعرضوه
- [ ] fallback لنصوص فاضية على بنرات قديمة
