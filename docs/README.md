# Tikmool Docs

هيكل نظيف — أرسل لكل فريق **ملف واحد** فيه **كل** التعديلات.

## للفرونت (نسخ نهائية شاملة)

| الفريق | الملف | يشمل |
|--------|--------|------|
| داشبورد | [`frontend/dashboard.md`](./frontend/dashboard.md) | Page Builder · أقسام · صفحات فئات · Nav · منتج/متغيّرات · حذف بتأكيد · أسعار · بلد منشأ/مبيع · قناة بيع |
| ويب | [`frontend/web.md`](./frontend/web.md) | Nav · أقسام · فئات · منتج · تسجيل · فلاتر · طلب سريع · نص تحميل · أسعار |
| Flutter | [`frontend/flutter.md`](./frontend/flutter.md) | Nav · أقسام layout/variant · فئات دائرية · فلاتر · منتج/سلة · تسجيل · أسعار |

## طلبات مخصصة (Custom Orders)

| الفريق | الملف |
|--------|--------|
| داشبورد | [`custom-orders/dashboard.md`](./custom-orders/dashboard.md) |
| ويب | [`custom-orders/web.md`](./custom-orders/web.md) |
| Flutter | [`custom-orders/flutter.md`](./custom-orders/flutter.md) |

## مرجع API

ملفات المرجع التفصيلي في [`api/`](./api/).

### Base URLs

- Admin: `/api/admin` + Bearer admin token  
- User: `/api/user`  
- Vendor: `/api/vendor`

### شكل الرد

```json
{ "success": true, "message": "...", "data": {} }
```

---

> أرشيف الملفات القديمة: `_archive_backup_2026_08_26/` — احذفوه بعد التأكد.
