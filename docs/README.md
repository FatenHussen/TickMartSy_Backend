# Tikmool Docs

هيكل نظيف — أرسل لكل فريق **ملف واحد** فيه **كل** التعديلات.

**آخر التعديلات والتحديثات (8 أيلول 2026 — كل شيء):** [`LATEST_UPDATES.md`](./LATEST_UPDATES.md)

**نظرة شاملة من أول استنساخ حتى الوضع الحالي (بدون تواريخ):** [`FULL_PROJECT_OVERVIEW.md`](./FULL_PROJECT_OVERVIEW.md)

**تقرير شخصي للإدارة — شو اشتغلتِ (ويب + داشبورد، بدون تواريخ):** [`تقرير_عملي_ويب_وداشبورد.txt`](./تقرير_عملي_ويب_وداشبورد.txt)

## للفرونت (نسخ نهائية شاملة)

| الفريق | الملف | يشمل |
|--------|--------|------|
| داشبورد | [`frontend/dashboard.md`](./frontend/dashboard.md) | Page Builder · أقسام · Nav · منتج · **حقول السعر/كمية/خصم** · **طلب سريع** · استيراد Excel · **الضمان** · **سلة مخصصة** · … |
| ويب | [`frontend/WEB_LATEST.md`](./frontend/WEB_LATEST.md) | **آخر نسخة 5 أيلول مساءً** — Nav · أقسام · منتج · ضمان · كمية · متغيّرات · فلاتر · طلب سريع · **سلة مخصصة** (`image` / `images` / بادجز) |
| Flutter | [`frontend/FLUTTER_LATEST.md`](./frontend/FLUTTER_LATEST.md) | **آخر نسخة 5 أيلول مساءً** — نفس المحاور للتطبيق · **سلة مخصصة** |

## سلة مخصصة (أرسلوا ملف الفريق)

| الفريق | الملف |
|--------|--------|
| داشبورد | [`frontend/DASHBOARD_CUSTOM_BASKET.md`](./frontend/DASHBOARD_CUSTOM_BASKET.md) |
| ويب | [`frontend/WEB_CUSTOM_BASKET.md`](./frontend/WEB_CUSTOM_BASKET.md) |
| Flutter | [`frontend/FLUTTER_CUSTOM_BASKET.md`](./frontend/FLUTTER_CUSTOM_BASKET.md) |
| ملخص العقد | [`frontend/CUSTOM_BASKET_FLOW.md`](./frontend/CUSTOM_BASKET_FLOW.md) |

الملفات التفصيلية الأطول (نفس المحتوى + أمثلة قديمة): [`web.md`](./frontend/web.md) · [`flutter.md`](./frontend/flutter.md)

## إنشاء منتج — UX

| الموضوع | الملف |
|---------|--------|
| صفات جزئية + حذف صور | [`frontend/DASHBOARD_PRODUCT_CREATE_UX.md`](./frontend/DASHBOARD_PRODUCT_CREATE_UX.md) |
| سعر · خصم · كمية · باركود · SKU (Flutter Web) | [`frontend/DASHBOARD_PRODUCT_PRICING_FIELDS.md`](./frontend/DASHBOARD_PRODUCT_PRICING_FIELDS.md) |
| باگ كمية المنتج (توست «موجبة») | [`frontend/DASHBOARD_PRODUCT_QUANTITY_VALIDATION.md`](./frontend/DASHBOARD_PRODUCT_QUANTITY_VALIDATION.md) |
| ضمان المنتج (دروب داون + قسم مستقل) | [`frontend/DASHBOARD_PRODUCT_WARRANTY.md`](./frontend/DASHBOARD_PRODUCT_WARRANTY.md) |

## إظهار / إخفاء أقسام الصفحة (Eye toggle)

| الفريق | الملف |
|--------|--------|
| داشبورد | [`page-sections/dashboard.md`](./page-sections/dashboard.md) — أيقونة عين · `POST /toggle-status` · UI checklist |
| ويب | [`page-sections/web.md`](./page-sections/web.md) — لا تغيير UI · الباك يستبعد الأقسام المخفية |

## طلبات مخصصة (Custom Orders)

التفصيل الكامل للطلب السريع (فلو الأدمن + ويب + Flutter + إعدادات القسم):

| الفريق | الملف |
|--------|--------|
| داشبورد | [`custom-orders/dashboard.md`](./custom-orders/dashboard.md) — convert/cancel **+ Settings: تفعيل + صفحات الظهور + شكل القسم** |
| ويب | [`custom-orders/web.md`](./custom-orders/web.md) — قسم حسب `page_slugs` + فلو الإنشاء · **آخر تحديث 2026-08-26** |
| Flutter | [`custom-orders/flutter.md`](./custom-orders/flutter.md) — قسم حسب `page_slugs` + فلو الإنشاء · **آخر تحديث 2026-08-26** |

### تحديث مهم (26 آب 2026) — صفحات ظهور الطلب السريع

- المحتوى والشكل يبقى من **Settings** (ليس Page Builder).
- مفتاح جديد: `quick_order_page_ids` — الأدمن يختار صفحات الظهور.
- العميل يقرأ `data.quick_order.page_ids` + `page_slugs` من `GET /api/user/settings`.
- الافتراضي = صفحة `home` فقط.

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
