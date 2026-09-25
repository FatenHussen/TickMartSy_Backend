# الويب — بنر بعد مسح الحقول من الداشبورد

> **أرسلوا هذا الملف لفريق الويب فقط.**  
> **آخر تحديث:** 25 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> داشبورد: [`DASHBOARD_BANNER_CLEAR_FIELDS.md`](./DASHBOARD_BANNER_CLEAR_FIELDS.md)  
> Flutter: [`FLUTTER_BANNER_CLEAR_FIELDS.md`](./FLUTTER_BANNER_CLEAR_FIELDS.md)  
> عرض الحقول: [`WEB_BANNER_REQUIRED_FIELDS.md`](./WEB_BANNER_REQUIRED_FIELDS.md)

الأدمن يقدر يمسح عنوان البنر ووصفه ونص الزر والرابط.  
الصورة تبقى. النص الممسوح يوصل `null` أو `""` — لا تعرضوه ولا تحتفظوا بالنص السابق من الكاش.

ما في endpoint جديد: نفس أقسام الصفحة.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [شكل العنصر](#2-شكل-العنصر)
3. [العرض](#3-العرض)
4. [غلط vs صح](#4-غلط-vs-صح)
5. [Checklist](#5-checklist)

---

## 1) القاعدة

| الحقل | إذا فاضي / `null` |
|--------|-------------------|
| `item.image` | يبقى — الكارد يضل ظاهر |
| `item.title` | لا عنوان |
| `item.desc` | لا وصف |
| `item.button_text` | لا زر |
| `items[i].link` | الكارد مو قابل للضغط |

النص حسب `Accept-Language` = **String أو null**. مو `{ ar, en }` داخل الأقسام.

---

## 2) شكل العنصر

بنر بعد مسح كل النصوص من الداشبورد:

```json
{
  "id": 1,
  "link": null,
  "order": 0,
  "item": {
    "id": 12,
    "title": null,
    "desc": null,
    "button_text": null,
    "image": "https://…/storage/banner/….jpg",
    "price": null,
    "discount": null
  }
}
```

قد يوصل `""` بدل `null`. الاتنين = فاضي.

---

## 3) العرض

```js
const text = (value) => (typeof value === 'string' ? value.trim() : '');

const banner = section.items[i];
const title = text(banner.item?.title);
const desc = text(banner.item?.desc);
const cta = text(banner.item?.button_text);
const href = text(banner.link);
const image = banner.item?.image;

// الصورة دائماً
// title / desc / الزر: فقط إذا text() مو فاضي
// <a> أو onClick فقط إذا href مو فاضي
```

- حدّثوا القسم من الرد الجديد. لا تدمجوا مع بنر قديم في الذاكرة.
- `title || previousTitle` يرجّع النص اللي انمسح.

---

## 4) غلط vs صح

**غلط**

```js
<h3>{banner.item.title || cachedTitle}</h3>
<a href={banner.link || '#'}>
```

**صح**

```js
{title ? <h3>{title}</h3> : null}
{desc ? <p>{desc}</p> : null}
{cta ? <span>{cta}</span> : null}
{href ? <a href={href}><img src={image} alt={title} /></a> : <img src={image} alt="" />}
```

---

## 5) Checklist

- [ ] `null` و `""` ما ينعرضوا كعنوان أو وصف أو زر
- [ ] `link` فاضي → بدون انتقال
- [ ] الصورة تبقى
- [ ] إعادة جلب الأقسام تستبدل النص القديم، ما تدمجه
- [ ] نسبة الصورة ≈ 16:6 مثل [`WEB_BANNER_REQUIRED_FIELDS.md`](./WEB_BANNER_REQUIRED_FIELDS.md)
