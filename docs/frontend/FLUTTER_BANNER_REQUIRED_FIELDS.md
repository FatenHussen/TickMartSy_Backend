# Flutter — بنر: كل الحقول متوفرة للعرض

> **أرسلوا هذا الملف لفريق Flutter فقط.**  
> **آخر تحديث:** 25 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> داشبورد: [`DASHBOARD_BANNER_REQUIRED_FIELDS.md`](./DASHBOARD_BANNER_REQUIRED_FIELDS.md)  
> ويب: [`WEB_BANNER_REQUIRED_FIELDS.md`](./WEB_BANNER_REQUIRED_FIELDS.md)

الأدمن صار لازم يعبّي **كل** حقول البنر عند الإنشاء/التعديل.  
بالتطبيق: اعرضوا العنوان والوصف ونص الزر والرابط — مو الصورة وحدها.

ما في endpoint جديد: نفس أقسام الصفحة.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [مصدر البيانات](#2-مصدر-البيانات)
3. [شكل العنصر](#3-شكل-العنصر)
4. [الموديل والعرض](#4-الموديل-والعرض)
5. [غلط vs صح](#5-غلط-vs-صح)
6. [Checklist](#6-checklist)

---

## 1) القاعدة

| الحقل | أين | العرض |
|--------|-----|--------|
| الصورة | `item.image` | ✅ أساسية · `AspectRatio` ≈ 16/6 |
| العنوان | `item.title` | ✅ اعرضوه |
| الوصف | `item.desc` | ✅ اعرضوه |
| نص الزر | `item.button_text` | ✅ على الزر / الـ CTA |
| الرابط | `items[i].link` | ✅ `onTap` → فتح الرابط |

البنرات الجديدة ما عاد تنحفظ ناقصة. بنرات قديمة قد تبقى بلا بعض الحقول — `?? ''` مو إخفاء الكارد.

---

## 2) مصدر البيانات

```http
GET /api/user/sections?page_slug=home
GET /api/user/categories/{id}/page
```

قسم بنر: `manual_model: banner` أو `display_type_id` بنر · غالباً `layout: slider` + `variant: horizontal`.

- عنصر واحد = بنر ثابت  
- عدة = `PageView` / carousel  
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

- `title` / `desc` / `button_text` = **String** حسب `Accept-Language`.
- `link` على مستوى العنصر (`SectionItem.link`) — مو داخل `item` فقط.
- الصورة = `item.image`.

---

## 4) الموديل والعرض

```dart
class BannerItem {
  final String? link;
  final String? title;
  final String? desc;
  final String? buttonText;
  final String? image;

  // من JSON القسم:
  // link        ← items[i].link
  // title       ← items[i].item.title
  // desc        ← items[i].item.desc
  // buttonText  ← items[i].item.button_text
  // image       ← items[i].item.image
}
```

```dart
AspectRatio(
  aspectRatio: 16 / 6,
  child: InkWell(
    onTap: () {
      final url = banner.link;
      if (url == null || url.isEmpty) return;
      // launchUrl / go_router …
    },
    child: Stack(
      fit: StackFit.expand,
      children: [
        Image.network(banner.image ?? '', fit: BoxFit.cover),
        // overlay: title · desc · Text(buttonText)
      ],
    ),
  ),
);
```

- لا تعتمدوا على الصورة وحدها إذا التصميم فيه عنوان/زر.
- لا تفترضوا `link` فاضي على بنرات جديدة.

---

## 5) غلط vs صح

**غلط**

```dart
// صورة فقط
Image.network(item.image);

// link من المكان الغلط
item['item']['link']; // غالباً null
```

**صح**

```dart
final link = sectionItem['link'] as String?;
final title = sectionItem['item']?['title'] as String? ?? '';
final desc = sectionItem['item']?['desc'] as String? ?? '';
final buttonText = sectionItem['item']?['button_text'] as String? ?? '';
final image = sectionItem['item']?['image'] as String?;
```

---

## 6) Checklist

- [ ] قراءة `title` · `desc` · `button_text` من `item`
- [ ] `onTap` يفتح `items[i].link`
- [ ] `AspectRatio` ≈ 16/6
- [ ] عنصر واحد ثابت · عدة = `PageView`
- [ ] قسم `items` فارغ → لا تعرضوه
- [ ] `?? ''` لنصوص فاضية على بنرات قديمة
