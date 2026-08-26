# Tikmool Docs

هيكل نظيف — أرسل لكل فريق **ملف واحد** فقط.

## للفرونت — النسخ النهائية (26 آب 2026)

أرسل لكل فريق **ملفه فقط**:

| الفريق | الملف |
|--------|--------|
| داشبورد | [`frontend/dashboard.md`](./frontend/dashboard.md) |
| ويب | [`frontend/web.md`](./frontend/web.md) |
| Flutter | [`frontend/flutter.md`](./frontend/flutter.md) |

محتوى الداشبورد النهائي يشمل: بلدان المبيع · أسعار USD/SYP · بلد المنشأ · حذف الفئات · قناة البيع.

## طلبات مخصصة (Custom Orders)

| الفريق | الملف |
|--------|--------|
| داشبورد | [`custom-orders/dashboard.md`](./custom-orders/dashboard.md) |
| ويب | [`custom-orders/web.md`](./custom-orders/web.md) |
| Flutter | [`custom-orders/flutter.md`](./custom-orders/flutter.md) |

## مرجع API

ملفات المرجع التفصيلي في [`api/`](./api/) (منتجات، طلبات، أقسام، صلاحيات، …).

### Base URLs

- Admin: `/api/admin` + Bearer admin token  
- User: `/api/user`  
- Vendor: `/api/vendor`

### شكل الرد

```json
{ "success": true, "message": "...", "data": {} }
```

---

> نسخة احتياطية من الملفات القديمة (قبل التنظيف): `_archive_backup_2026_08_26/`  
> احذفوها بعد التأكد أن الملفات الجديدة كافية.
