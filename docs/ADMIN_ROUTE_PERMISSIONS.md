# صلاحيات مسارات الـ Admin API

هذا المستند يشرح **middleware الصلاحيات** المرتبطة بمسارات `routes/api/admin.php`، وعلاقتها بـ **Spatie Permission** على guard **`admin`**.

---

## 1. المتطلبات الأساسية

| الطبقة | الوصف |
|--------|--------|
| **تسجيل الدخول** | معظم مسارات الـ Admin تحتاج أولاً `auth:admin` (توكن Sanctum للأدمن). |
| **الصلاحيات** | بعد المصادقة، يُفحَص اسم الصلاحية في قاعدة البيانات (جدول `permissions`، `guard_name = admin`). |

بدون توكن أدمن صالح: **401**. مع توكن وبدون الصلاحية: **403** (مع رسالة `__('custom.Unauthorized')` حيث تُعرّف).

---

## 2. أنواع الـ Middleware

### 2.1 `crud.permission:{مورد}`

**الملف:** `app/Http/Middleware/CrudPermissionMiddleware.php`  
**التسجيل:** `bootstrap/app.php` → alias `crud.permission`

يحوّل **HTTP method** إلى لاحقة صلاحية ثم يبني الاسم: **`{مورد}.{إجراء}`**.

| HTTP | لاحقة الصلاحية |
|------|----------------|
| GET | `view` |
| POST | `create` |
| PUT / PATCH | `update` |
| DELETE | `delete` |

**مثال:** `crud.permission:product` على `PATCH /api/admin/products/1` → يتحقق من **`product.update`**.

**Alias اختياري:** إذا مرّرت اسمًا يحتاج تحويلًا (مثل الجمع)، يُقرأ من `config/admin-permissions.php`:

| الممرَّر في الـ middleware | يُفحَص في Spatie كـ |
|-----------------------------|---------------------|
| `shops` | `shop` |

باقي الممرّرات تُستخدم كما هي (مثل `vendor` → `vendor.view`).

---

### 2.2 `admin.permission:{اسم}[|{اسم2}...]`

**الملف:** `app/Http/Middleware/AdminPermissionMiddleware.php`  
**التسجيل:** `bootstrap/app.php` → alias `admin.permission`

يفحص **اسم صلاحية جاهز** كما هو مخزّن في Spatie (بدون ربط تلقائي بالـ HTTP).

- صلاحية واحدة: `admin.permission:statistics.view`
- أكثر من صلاحية (واحدة تكفي): `admin.permission:perm.a|perm.b`

---

## 3. إنشاء الصلاحيات في قاعدة البيانات

- **`database/seeders/AdminRolePermissionSeeder.php`:** يولّد صلاحيات CRUD لعدة موديلات (`{model}.{view|create|update|delete}`) + صلاحيات مخصّصة مثل `statistics.view` و `reports.view`.
- **`database/seeders/RolePermissionSeeder.php`:** يكمّل صلاحيات إضافية (مثل `order`, `complaint`, …) حسب إعداد المشروع.

بعد تعديل الـ seeders شغّل (حسب بيئتك):

```bash
php artisan db:seed --class=AdminRolePermissionSeeder
# و/أو
php artisan db:seed --class=RolePermissionSeeder
```

---

## 4. خريطة المسارات ↔ الصلاحيات (مرجع سريع)

المسار الأساسي للـ API: **`/api/admin/...`** (حسب `routes/api.php`).

### 4.1 `crud.permission` (موارد)

| المسار / المورد في الكود | بادئة الصلاحية في Spatie |
|--------------------------|---------------------------|
| `notification` | `notification.*` |
| `legaldocument` | `legaldocument.*` |
| `basket` | `basket.*` |
| `schedulebasket` | `schedulebasket.*` |
| `schedule` | `schedule.*` |
| `package` | `package.*` |
| `subscription` | `subscription.*` |
| `gift` | `gift.*` |
| `usergift` | `usergift.*` |
| `pointexchange` | `pointexchange.*` |
| `vendorpackage` | `vendorpackage.*` |
| `vendorsubscription` | `vendorsubscription.*` |
| `currency` | `currency.*` |
| `pointrule` | `pointrule.*` |
| `icon` | `icon.*` |
| `quickaction` | `quickaction.*` |
| `store` | `store.*` |
| `language` | `language.*` |
| `category` | `category.*` |
| `brand` | `brand.*` |
| `color` | `color.*` |
| `categoryattribute` | `categoryattribute.*` |
| `categorydetail` | `categorydetail.*` |
| `product` | `product.*` |
| `productvariant` | `productvariant.*` |
| `shopproductvariant` | `shopproductvariant.*` |
| `salecountry` | `salecountry.*` |
| `shops` (alias) | `shop.*` |
| `vendor` | `vendor.*` |
| `role` | `role.*` |
| `banner` | `banner.*` |
| `admin` | `admin.*` |
| `driver` | `driver.*` |
| `governorate` | `governorate.*` |
| `city` | `city.*` |
| `area` | `area.*` |
| `country` | `country.*` |
| `service` | `service.*` |
| `vendorservicetype` | `vendorservicetype.*` |
| `vendorservice` | `vendorservice.*` |
| `shopvendorservice` | `shopvendorservice.*` |
| `section` | `section.*` |
| `pagesection` | `pagesection.*` |
| `coupon` | `coupon.*` |
| `complaint` | `complaint.*` |
| `recipe` | `recipe.*` |
| `faq` | `faq.*` |
| `popupcampaign` | `popupcampaign.*` |
| `badge` | `badge.*` |
| `promotion` | `promotion.*` |
| `flashsale` | `flashsale.*` |
| `affiliatewithdrawrequest` | `affiliatewithdrawrequest.*` |
| `affiliatewallettransaction` | `affiliatewallettransaction.*` (قد يُقيَّد في الـ seeder بصلاحيات معيّنة) |
| `driverwallettransaction` | `driverwallettransaction.*` |
| `vendorwithdrawrequest` | `vendorwithdrawrequest.*` |
| `sellerregistration` | `sellerregistration.*` |
| `promotionrequest` | `promotionrequest.*` |
| `vendoruser` | `vendoruser.*` |
| `user` | `user.*` |

---

### 4.2 `admin.permission` (صلاحيات صريحة)

| السياق | الصلاحية |
|--------|-----------|
| سجل النشاط `GET /activity-logs` | `activitylog.view` |
| رفع هدايا دفعة `POST /gifts/bulk` | `gift.create` |
| نقاط المستخدمين (كل مسارات `user-points/*`) | `pointwallet.view` |
| كل مسارات `statistics/*` | `statistics.view` |
| كل مسارات `reports/*` (تضمين التصدير) | `reports.view` |
| إعدادات الـ Setting `settings/*` | قراءة: `setting.view` — تحديث: `setting.update` |
| إعدادات النظام `system-settings/*` | عرض: `systemsetting.view` — تعديل: `systemsetting.update` — حذف: `systemsetting.delete` |
| جداول المستخدم `user-basket-schedules/*` | عرض: `userbasketschedule.view` — تفعيل/تعطيل: `userbasketschedule.update` |
| مساعدات الأقسام `sections/pages|item-types|display-types` | `section.view` |
| موافقة/رفض منتج `POST products/{id}/approve|reject` | `product.update` |
| متغيرات المنتج حسب منتج `GET products/{product}/variants` | `productvariant.view` |
| قائمة الصلاحيات للأدوار `GET /permissions` | `role.view` |
| محاسبة الموردين `vendor-accounting/*` | `vendoraccounting.view` |
| موافقة/رفض تسجيل بائع | `sellerregistration.update` |
| إحصائيات طلبات الترويج `GET promotion-requests/stats/summary` | `promotionrequest.view` |
| موافقة/رفض طلب ترويج | `promotionrequest.update` |
| مستخدمون (فرعي) `users/markters`، demote/reactivate | `user.view` / `user.update` |
| طلبات التوصيل `orders/*` | قراءة: `order.view` — تعديل/تعيين: `order.update` |
| طلبات الخدمة `service-orders/*` | قراءة: `serviceorder.view` — تعديل: `serviceorder.update` |

---

## 5. ملاحظات مهمة

1. **مسارات بدون فحص صلاحية إضافي:** مثل `POST /toggle-status` تبقى تحت `auth:admin` فقط (لا `admin.permission` / `crud.permission` في التعريف الحالي). راجع `docs/TOGGLE_STATUS_API.md` إن وُجد.
2. **مسارات الـ Auth:** `POST /auth/login` عامة؛ `logout` و`profile` وغيرها تحت `auth:admin` بدون صلاحية Spatie إضافية في نفس الملف.
3. **User / Driver APIs:** مسارات `routes/api/user.php` و `routes/api/driver.php` **لا** تستخدم `admin.permission`؛ المصادقة والصلاحيات هناك منفصلة عن لوحة الأدمن.
4. **ترتيب المسارات:** مسار `GET /promotion-requests/stats/summary` مُعرَّف **قبل** `apiResource('promotion-requests')` حتى لا يُلتقط `stats` كمعرّف.

---

## 6. الملفات ذات الصلة

| الملف | الغرض |
|-------|--------|
| `routes/api/admin.php` | تعريف المسارات وربط الـ middleware |
| `app/Http/Middleware/CrudPermissionMiddleware.php` | `crud.permission` |
| `app/Http/Middleware/AdminPermissionMiddleware.php` | `admin.permission` |
| `config/admin-permissions.php` | alias لمورد الـ CRUD (`shops` → `shop`) |
| `bootstrap/app.php` | تسجيل aliases الـ middleware |
| `database/seeders/AdminRolePermissionSeeder.php` | بذور صلاحيات الأدمن |

---

*آخر تحديث يتوافق مع هيكل المشروع الحالي لملف المسارات والـ middleware.*
