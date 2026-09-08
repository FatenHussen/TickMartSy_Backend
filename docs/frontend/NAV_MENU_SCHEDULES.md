# الداشبورد — إضافة «جدولة» للناف بار

> **تاريخ:** 8 أيلول 2026  
> **الباك:** جاهز — **لا سيدر تلقائي**. الأدمن يضيف العنصر من شريط التنقّل.

جدولة **ليست صفحة** من Page Builder. لذلك ما بتظهر ضمن قائمة الصفحات.

---

## من الداشبورد

1. شريط التنقّل → إضافة عنصر
2. النوع: **شاشة ثابتة** (`type=route`) — **مو** صفحة (`page`)
3. الشاشة: **جدولة** (`route_key=schedules`)
4. العنوان: `{ "ar": "جدولة", "en": "Schedules" }`

```http
POST /api/admin/nav-menu-items
Content-Type: multipart/form-data

title[ar]=جدولة
title[en]=Schedules
type=route
route_key=schedules
is_active=1
```

قائمة الشاشات الثابتة (فيها جدولة):

```http
GET /api/admin/nav-menu-items/route-keys
```

```json
{
  "data": [
    { "key": "baskets", "label": { "ar": "سلالي", "en": "My baskets" } },
    { "key": "schedules", "label": { "ar": "جدولة", "en": "Schedules" } }
  ]
}
```

إذا الدروب داون عندكم **مكتوب بالكود** بدون `schedules` — أضيفوه، أو عبّوا القائمة من الـ endpoint فوق.

---

## ويب + Flutter

إذا وصل `route_key=schedules` وبدون خريطة، العنصر يُتجاهل.

| `route_key` | ويب | Flutter |
|-------------|-----|---------|
| `schedules` | `/schedules` | شاشة `GET /api/user/schedules` |

```js
schedules: "/schedules",
```
