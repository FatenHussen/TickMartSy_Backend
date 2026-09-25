# Flutter — بنر بعد مسح الحقول من الداشبورد

> **أرسلوا هذا الملف لفريق Flutter فقط.**  
> **آخر تحديث:** 25 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> داشبورد: [`DASHBOARD_BANNER_CLEAR_FIELDS.md`](./DASHBOARD_BANNER_CLEAR_FIELDS.md)  
> ويب: [`WEB_BANNER_CLEAR_FIELDS.md`](./WEB_BANNER_CLEAR_FIELDS.md)  
> عرض الحقول: [`FLUTTER_BANNER_REQUIRED_FIELDS.md`](./FLUTTER_BANNER_REQUIRED_FIELDS.md)

الأدمن يقدر يمسح عنوان البنر ووصفه ونص الزر والرابط، والباك يحفظهم `null`.  
الصورة تبقى. النص الممسوح يوصل `null` أو `""` — لا تعرضوه ولا تحتفظوا بالنص السابق في الموديل.

ما في endpoint جديد: نفس أقسام الصفحة.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [شكل العنصر](#2-شكل-العنصر)
3. [الموديل والعرض](#3-الموديل-والعرض)
4. [غلط vs صح](#4-غلط-vs-صح)
5. [Checklist](#5-checklist)

---

## 1) القاعدة

| الحقل | إذا فاضي / `null` |
|--------|-------------------|
| `item.image` | يبقى — الكارد يضل ظاهر |
| `item.title` | لا `Text` للعنوان |
| `item.desc` | لا وصف |
| `item.button_text` | لا زر |
| `items[i].link` | `onTap` لا يفتح شيئاً |

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

## 3) الموديل والعرض

```dart
String textOf(dynamic value) {
  if (value is! String) return '';
  return value.trim();
}

final title = textOf(sectionItem['item']?['title']);
final desc = textOf(sectionItem['item']?['desc']);
final buttonText = textOf(sectionItem['item']?['button_text']);
final link = textOf(sectionItem['link']);
final image = sectionItem['item']?['image'] as String?;
```

```dart
AspectRatio(
  aspectRatio: 16 / 6,
  child: InkWell(
    onTap: link.isEmpty ? null : () { /* launchUrl */ },
    child: Stack(
      fit: StackFit.expand,
      children: [
        Image.network(image ?? '', fit: BoxFit.cover),
        if (title.isNotEmpty) Text(title),
        if (desc.isNotEmpty) Text(desc),
        if (buttonText.isNotEmpty) Text(buttonText),
      ],
    ),
  ),
);
```

استبدلوا كائن البنر من الرد الأخير. `?? previous` يرجّع النص الممسوح.

---

## 4) غلط vs صح

**غلط**

```dart
final title = item['title'] as String? ?? previousTitle;
onTap: () => launchUrl(Uri.parse(item['link'] ?? previousLink));
```

**صح**

```dart
final title = textOf(item['title']);
if (title.isNotEmpty) Text(title);
if (link.isEmpty) return;
```

---

## 5) Checklist

- [ ] `null` و `""` ما ينرسموا كعنوان أو وصف أو زر
- [ ] `link` فاضي → `onTap: null`
- [ ] الصورة تبقى
- [ ] إعادة جلب الأقسام تستبدل الموديل القديم
- [ ] `AspectRatio` ≈ 16/6 مثل [`FLUTTER_BANNER_REQUIRED_FIELDS.md`](./FLUTTER_BANNER_REQUIRED_FIELDS.md)
