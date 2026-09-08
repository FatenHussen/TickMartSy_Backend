# ويب + Flutter — Nav: مفتاح `schedules` (جدولة)

> **تاريخ:** 8 أيلول 2026  
> **الباك:** جاهز بعد `git pull` + `php artisan db:seed --class=NavMenuSeeder`  
> `GET /api/user/nav-menu` قد يرجّع `type=route` + `target.route_key=schedules`

---

## ماذا تغيّر؟

عنصر جديد في الشريط: **جدولة** / **Schedules**.

بدون خريطة عندكم العنصر **يُتجاهل** (fallback الحالي). لازم تضيفوا المفتاح.

| `route_key` | ويب | Flutter |
|-------------|-----|---------|
| `schedules` | `/schedules` | شاشة قائمة فئات الجدولة — نفس `GET /api/user/schedules` |

```js
const ROUTE_MAP = {
  // ... الموجود
  schedules: "/schedules",
};
```

ضغط العنصر → قائمة الجداول (`GET /api/user/schedules`). كرت فئة → `/schedules/{id}` كما هو.

---

## Checklist

- [ ] أضيفوا `schedules` لخريطة `route_key` (ويب + Flutter)
- [ ] لا تكسروا الـ fallback لباقي المفاتيح
- [ ] الباك: `php artisan db:seed --class=NavMenuSeeder` (آمن — `updateOrCreate`)
