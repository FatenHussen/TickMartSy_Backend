# آخر التعديلات والتحديثات — Tikmool Backend

> **تاريخ التحديث:** 5 أيلول 2026  
> **النطاق:** كل التغييرات من منتصف آب 2026 حتى اليوم — باك + داشبورد + ويب + Flutter  
> **الحالة:** الباك جاهز — التنفيذ المتبقي في الواجهات (UI)

---

## أين أبدأ؟

| الفريق | الملف الرئيسي | ملاحظة |
|--------|---------------|--------|
| **داشبورد** | [`frontend/dashboard.md`](./frontend/dashboard.md) | كل تعديلات الأدمن في ملف واحد |
| **ويب** | [`frontend/web.md`](./frontend/web.md) | Nav · أقسام · فئات · منتج · فلاتر · طلب سريع |
| **Flutter** | [`frontend/flutter.md`](./frontend/flutter.md) | نفس المحاور للتطبيق |
| **متغيّرات — داشبورد** | [`frontend/product-variants-dashboard.md`](./frontend/product-variants-dashboard.md) | **آخر تحديث 30 آب** |
| **إنشاء منتج — صفات جزئية + حذف صور** | [`frontend/DASHBOARD_PRODUCT_CREATE_UX.md`](./frontend/DASHBOARD_PRODUCT_CREATE_UX.md) | **31 آب** |
| **باگ كمية المنتج (توست موجبة)** | [`frontend/DASHBOARD_PRODUCT_QUANTITY_VALIDATION.md`](./frontend/DASHBOARD_PRODUCT_QUANTITY_VALIDATION.md) | **5 أيلول** |
| **ضمان المنتج — دروب داون + قسم مستقل** | [`frontend/DASHBOARD_PRODUCT_WARRANTY.md`](./frontend/DASHBOARD_PRODUCT_WARRANTY.md) | **5 أيلول** |
| **متغيّرات — ويب + Flutter** | [`frontend/product-variants-storefront-update.md`](./frontend/product-variants-storefront-update.md) | **آخر تحديث 30 آب** |
| **متغيّرات — ويب (تفصيل)** | [`frontend/product-variants-web.md`](./frontend/product-variants-web.md) | |
| **متغيّرات — ويب (دليل شامل + مقارنة tikmool-website)** | [`frontend/WEB_VARIANTS_COMPLETE.md`](./frontend/WEB_VARIANTS_COMPLETE.md) | **31 آب** — API حقيقي · types · gaps · سلة |
| **متغيّرات — Flutter (تفصيل)** | [`frontend/product-variants-flutter.md`](./frontend/product-variants-flutter.md) | |
| **طلب سريع** | [`custom-orders/`](./custom-orders/) | dashboard · web · flutter |
| **إظهار/إخفاء أقسام** | [`page-sections/`](./page-sections/) | Eye toggle |
| **مرجع API كامل** | [`api/`](./api/) | Postman · صلاحيات · endpoints |
| **نظرة شاملة على المشروع** | [`FULL_PROJECT_OVERVIEW.md`](./FULL_PROJECT_OVERVIEW.md) | بدون تواريخ — ماذا يوجد وكيف يعمل |

---

## ملخص تنفيذي — أهم 10 تغييرات

| # | التغيير | من يتأثر |
|---|---------|----------|
| 1 | **متغيّرات المنتج** — سعر + كمية + **خصم** على `product_variants` (مو shop) | الكل |
| 2 | **إنشاء منتج** — المتغيّرات في **state محلي** ثم `POST /products` دفعة واحدة | داشبورد |
| 3 | **Single select** لكل صفة — لا multi-select للمقاسات | داشبورد |
| 4 | **حذف اسم المتغيّر** — الهوية = صفات + SKU | الكل |
| 5 | **Page Builder موحّد** — صفحات + أقسام + preview + reorder | داشبورد + ويب + Flutter |
| 6 | **Eye toggle** — إخفاء قسم بدون حذف (`is_active`) | داشبورد + ويب + Flutter |
| 7 | **Nav Menu ديناميكي** — `GET /api/user/nav-menu` | ويب + Flutter |
| 8 | **الطلب السريع** — إعدادات + `quick_order_page_ids` + convert/cancel | الكل |
| 9 | **استيراد Excel** — قالب SPBS + upsert | داشبورد |
| 10 | **قناة البيع** `sale_channel` — platform أو shop | داشبورد |

---

## آخر تحديث (5 أيلول 2026) — ضمان المنتج + بدون متغيّر افتراضي

الضمان في إنشاء المنتج **دروب داون** من قسم **الضمانات** (`/products/warranties`) — مو حقل أشهر. لا كارد «المتغير رقم 1» تلقائي.

> الدليل: [`DASHBOARD_PRODUCT_WARRANTY.md`](./frontend/DASHBOARD_PRODUCT_WARRANTY.md)

```bash
php artisan migrate
php artisan db:seed --class=AdminRolePermissionSeeder
```

---

## آخر تحديث (5 أيلول 2026) — باگ كمية المنتج في الداشبورد

توست «يجب أن تكون الكمية موجبة» عند إنشاء منتج بمتغيّرات **باگ غير مقصود** — الباك يقبل كمية فاضية. Zod كان يطلب `quantity` المنتج المخفي.

> الدليل: [`DASHBOARD_PRODUCT_QUANTITY_VALIDATION.md`](./frontend/DASHBOARD_PRODUCT_QUANTITY_VALIDATION.md)

---

## آخر تحديث (30–31 آب 2026) — متغيّرات المنتج

### الباك — ما تغيّر

| البند | التفاصيل |
|-------|----------|
| Migration | `2026_08_30_120000_add_discount_to_product_variants_table` |
| حقول جديدة | `discount` (nullable int) · `discount_type` (default `none`) |
| السعر والكمية | على **`product_variants`** — أُزيل `price` من `shop_product_variants` |
| الخصم | per variant — أولوية على خصم المنتج / flash sale |
| API Admin | `variants[].discount` + `variants[].discount_type` في POST/PUT |
| API User | `shop_variants[].discount_value` · `discount_type` · `price_currencies` |
| اسم المتغيّر | **غير موجود** — لا ترسل `name.ar` / `name.en` |

```bash
php artisan migrate   # يضيف discount على product_variants
```

### الداشبورد — مطلوب في UI

| البند | الصح | الخطأ الشائع |
|-------|------|--------------|
| **إنشاء منتج** | متغيّرات في `localVariants[]` → `POST /products` مرة واحدة | toast «احفظ المنتج أولاً» |
| **نموذج الإضافة** | dropdown **single** لكل صفة (لون · مقاس · تصميم) | multi-select مقاسات → 4 كروت دفعة |
| **كل «إضافة»** | متغيّر **واحد** = كارد واحد | `map` على المقاسات |
| **تكرار اللون** | مسموح — يُمنع فقط نفس `attributes_values_ids` | فلترة ألوان مستخدمة من dropdown |
| **حقول الكارد** | SKU · $ · ل.س · خصم · كمية · باركود — **كلها اختيارية** | required على السعر/الكمية |
| **SKU** | **إنجليزي فقط** — `SKU-27T4376` | `PROD-أخضر-XL` |
| **تحديث** (edit) | `PUT product-variants/{id}` أو `PUT products/{id}` | — |

> الدليل الكامل: [`product-variants-dashboard.md`](./frontend/product-variants-dashboard.md)

### الويب + Flutter — مطلوب في UI

| البند | التفاصيل |
|-------|----------|
| Endpoint | `GET /api/user/products/{id}?lat=&lng=` |
| اختيار المتغيّر | من **`shop_variants`** — لا Cartesian كامل |
| السعر | `price_currencies.USD` / `price_currencies.SYP` |
| الخصم | `discount_value` + `discount_type` على المتغيّر |
| الكمية | حقل واحد `quantity` — لا `stock` منفصل |
| `shop_variants` | عنصر واحد على الأقل؛ `id`/`shop_id` ممكن `null` (منصة) |
| `attributes` | `{ attribute, value, type }` — مو `category_attribute` |
| `delivery_time` | على **مستوى المنتج** — ليس per variant |
| `country` | **string** في صفحة المنتج |

> الدليل المشترك: [`product-variants-storefront-update.md`](./frontend/product-variants-storefront-update.md)

---

## الجدول الزمني — آب 2026

### 17–19 آب — Page Builder + Nav + فئات

| الميزة | الوصف |
|--------|--------|
| **Page Builder موحّد** | CRUD صفحات · إضافة قسم بـ `section_id` أو إنشاء مباشر · reorder · preview |
| **صفحات فئات تلقائية** | كل فئة → صفحة CMS مرتبطة (`category_id` على `pages`) |
| **Nav Menu** | CRUD + reorder من الداشبoard · `GET /api/user/nav-menu` للعملاء |
| **layout + variant** | `slider` \| `list` \| `grid` + `horizontal` \| `vertical` \| `square` |
| **صفات الفئة** | نُقلت للفئات الجذر · وراثة للفرعية |

**Endpoints رئيسية:**

```http
GET  /api/admin/pages
POST /api/admin/pages/{id}/sections
GET  /api/user/pages/{slug}
GET  /api/user/nav-menu
GET  /api/user/categories/{id}/page
```

---

### 20–22 آب — منتج · متغيّر · فلاتر

| الميزة | الوصف |
|--------|--------|
| **سعر/كمية على variant** | `product_variants.price` · `quantity` — لا على ربط المحل |
| **Soft delete variants** | حذف متغيّر مع تأكيد · 409 عند ارتباط |
| **منتج لأي مستوى فئة** | `category_id` على أي عمق |
| **Boolean query params** | `is_active=1` أو `is_active=true` — الباك يقبل الاثنين |
| **فلاتر منتجات** | تحسينات User API |

---

### 25–26 آب — طلب سريع · قناة بيع · Excel

| الميزة | الوصف |
|--------|--------|
| **Custom Order Requests** | جدول + Admin convert/cancel · User create/list |
| **Quick Order Settings** | `quick_order_enabled` · خلفية · ألوان · خطوات · شكل كارد |
| **`quick_order_page_ids`** | الأدمن يختار صفحات الظهور — افتراضي `home` |
| **`sale_channel`** | `platform` (موقع) أو `shop` (ربط بمتجر) |
| **حذف فئات** | soft delete · `category_id` nullable على المنتج |
| **استيراد Excel** | `GET import-template` · `POST import` · upsert barcode/sku |

**Endpoints طلب سريع:**

```http
GET  /api/admin/custom-order-requests
POST /api/admin/custom-order-requests/{id}/convert
POST /api/admin/custom-order-requests/{id}/cancel
POST /api/user/custom-order-requests
GET  /api/user/settings          → data.quick_order
PUT  /api/admin/settings/quick_order_page_ids
```

---

### 27–28 آب — Eye toggle · أقسام

| الميزة | الوصف |
|--------|--------|
| **Eye toggle** | `is_active` على `page_sections` — إخفاء بدون DELETE |
| **Toggle API** | `POST /api/admin/toggle-status` `{ type: "page_section", id, is_active }` |
| **Preview** | يعرض الأقسام **النشطة فقط** |
| **Soft delete categories** | migration `2026_08_27_143700` |
| **Runtime filters** | فلاتر ديناميكية على أقسام الصفحة |

> الدليل: [`page-sections/dashboard.md`](./page-sections/dashboard.md) · [`page-sections/web.md`](./page-sections/web.md)

---

### 30–31 آب — خصم المتغيّر + توثيق نهائي

| الميزة | الوصف |
|--------|--------|
| **خصم per variant** | `discount` + `discount_type` على `product_variants` |
| **Validation** | كل حقول المتغيّر nullable في Admin |
| **Resources** | `ShopVariantResource` · `ProductVariant/OneResource` محدّثة |
| **Docs** | 7 ملفات frontend محدّثة (dashboard · web · flutter · variants × 4) |

---

## Migrations — آب 2026 (ترتيب التشغيل)

```bash
php artisan migrate   # آمن — هيكل فقط، لا يمسح داتا
```

| Migration | الغرض |
|-----------|--------|
| `2026_08_16_100000_add_price_to_product_variants_table` | سعر على المتغيّر |
| `2026_08_16_100001_drop_price_from_shop_product_variants_table` | إزالة سعر من ربط المحل |
| `2026_08_16_100002_move_category_attributes_to_root_categories` | صفات على الجذر |
| `2026_08_17_120000_add_soft_deletes_to_product_variants_table` | soft delete variants |
| `2026_08_17_120000_create_nav_menu_items_table` | Nav Menu |
| `2026_08_17_130000_add_category_id_to_pages_table` | صفحات فئات |
| `2026_08_17_180000_add_slider_defaults_to_sections_table` | defaults للسلايدر |
| `2026_08_19_190000_add_is_default_to_page_sections_table` | قسم افتراضي |
| `2026_08_20_120000_add_layout_to_sections_and_page_sections` | layout + variant |
| `2026_08_25_120000_create_custom_order_requests_table` | طلبات مخصصة |
| `2026_08_25_120100_add_custom_order_fields_to_orders_and_items` | ربط بالطلب |
| `2026_08_26_103600_add_quick_order_settings` | إعدادات طلب سريع |
| `2026_08_26_104500_make_product_category_id_nullable_for_category_delete` | حذف فئات |
| `2026_08_26_111600_add_sale_channel_to_products_table` | قناة البيع |
| `2026_08_26_131500_add_quick_order_page_ids_setting` | صفحات ظهور الطلب السريع |
| `2026_08_27_143700_add_soft_deletes_to_categories_table` | soft delete فئات |
| `2026_08_30_120000_add_discount_to_product_variants_table` | **خصم المتغيّر** |

### Seeders (اختياري — ناقص فقط)

```bash
php artisan db:seed --class=RolePermissionSeeder   # صلاحيات CustomOrderRequest
php artisan db:seed --class=CountrySeeder            # دول منشأ
php artisan db:seed --class=SaleCountrySeeder        # بلدان مبيع
```

**لا تشغّل:** `migrate:fresh` · `migrate:refresh` · `db:seed` (كامل)

---

## Base URLs وشكل الرد

| الطرف | Base | Token |
|-------|------|-------|
| Admin | `/api/admin` | Bearer admin |
| User | `/api/user` | Bearer user (اختياري لبعض المسارات) |
| Vendor | `/api/vendor` | Bearer vendor |
| Driver | `/api/driver` | Bearer driver |

```json
{ "success": true, "message": "...", "data": {} }
```

---

## Checklist — حسب الفريق

### داشبoard

- [ ] Page Builder: صفحات + أقسام + reorder + preview + eye toggle
- [ ] Nav Menu CRUD + reorder
- [ ] منتج: أي مستوى فئة · sale_channel · بلد منشأ · بلدان مبيع
- [ ] متغيّرات: single select · state محلي عند الإنشاء · خصم per variant
- [ ] حذف متغيّر/صفة/فئة مع confirm + 409
- [ ] استيراد Excel: قالب + رفع + ملخص
- [ ] طلب سريع: إعدادات + multi-select صفحات + convert/cancel
- [ ] `is_active=1` أو `true` في فلاتر القوائم

### ويب

- [ ] Nav من API · Page Builder · صفحات فئات
- [ ] منتج: shop_variants picker · price_currencies · discount per variant
- [ ] فلاتر · تسجيل بدون إيميل · أسعار USD/SYP
- [ ] طلب سريع حسب `page_slugs` من settings
- [ ] أقسام مخفية لا تُعرض (الباك يستبعدها)

### Flutter

- [ ] نفس محاور الويب (Nav · صفحات · فئات · منتج · فلاتر)
- [ ] shop_variants · attributes_map · quantity · discount
- [ ] طلب سريع حسب الصفحة
- [ ] Circular categories · layout/variant للأقسام

---

## محذوف / لا تعتمد عليه

| قديم | البديل |
|------|--------|
| `variant.name` (ar/en) | `attributes` + `sku` |
| `shop_variants[].price` | `product_variants.price` → User API |
| multi-select مقاسات | single لكل صفة |
| toast «احفظ المنتج أولاً» | state محلي + POST واحد |
| DELETE قسم لإخفائه | `is_active` toggle |
| Nav ثابت بالكود | `GET /nav-menu` |
| `price_usd` / `price_syp` flat | `price_currencies.USD` / `.SYP` |
| مستودعاتي / خارجي (sale) | `platform` \| `shop` فقط |

---

## Commits حديثة (مرجع)

| التاريخ | Commit | المحتوى |
|---------|--------|---------|
| 30 آب | `c0f04c1` | خصم variant + ProductService + docs |
| 28 آب | `875480e` | Eye toggle + page sections + tests |
| 26–27 آب | `3895759`… | Quick order · sale_channel · filters |
| 19 آب | `01b020b` | Page Builder موحّد |
| 17 آب | `b721154` | fix product id filter |

---

## روابط سريعة إضافية

- [`README.md`](./README.md) — فهرس الوثائق
- [`api/ADMIN_PRODUCTS_COMPLETE_DOCUMENTATION.md`](./api/ADMIN_PRODUCTS_COMPLETE_DOCUMENTATION.md) — منتجات Admin
- [`api/ADMIN_PRODUCT_VARIANTS_UPDATE_DELETE.md`](./api/ADMIN_PRODUCT_VARIANTS_UPDATE_DELETE.md) — متغيّرات CRUD
- [`api/TOGGLE_STATUS_API.md`](./api/TOGGLE_STATUS_API.md) — toggle عام
- [`api/PRODUCTS_FILTERS_API.md`](./api/PRODUCTS_FILTERS_API.md) — فلاتر
- [`custom-orders/dashboard.md`](./custom-orders/dashboard.md) — طلب سريع Admin

---

> **ملاحظة:** أرشيف الملفات القديمة في `_archive_backup_2026_08_26/` — للمرجع فقط. استخدم الملفات في `frontend/` و `api/` و `custom-orders/` و `page-sections/`.

**الباك جاهز — التعديل المتبقي في واجهات الداشبoard / الويب / Flutter.**
