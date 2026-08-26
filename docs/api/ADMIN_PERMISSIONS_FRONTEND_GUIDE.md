# دليل الصلاحيات (Permissions) — واجهة Admin للـ Front End

هذا الملف موجّه **لمطوّر الواجهة الأمامية** يشرح التغييرات المتعلقة بالصلاحيات على **مسارات الـ Admin API**، وكيفية استخدامها في الواجهة (قوائم، أزرار، معالجة أخطاء الشبكة).

**المسار الأساسي للـ API:** ` /api/admin/...`  
(يُضاف له نطاق السيرفر، مثل `https://api.example.com`.)

---

## 1. من أين تأتي الصلاحيات؟

بعد **تسجيل الدخول** (`POST /api/admin/auth/login`)، الاستجابة تحتوي عادةً على:

- `data.user` (أو حسب شكل الـ wrapper عندكم): بيانات الأدمن.
- **`permissions`**: مصفوفة **نصوص** بأسماء الصلاحيات كما هي مخزّنة في الـ backend (Spatie)، مثل `"product.view"`، `"order.update"`.
- **`roles`**: أسماء الأدوار، مثل `"admin"` أو `"employee"`.

نفس الحقل **`permissions`** يُعاد في **`GET /api/admin/auth/profile`** (مع تحميل العلاقات المناسبة).

**استخدام مقترح في الـ FE:**

- خزّن `permissions` في الـ state (مثلاً بعد Login أو عند تحميل Profile).
- لإظهار رابط أو زر: تحقق من وجود اسم الصلاحية المطلوبة في المصفوفة.
- الأسماء **حسّاسة لحالة الأحرف** وتُطابق الـ backend حرفيًا (مثلاً `shop.view` وليس `Shop.View`).

مثال منطقي (Pseudo-code):

```ts
function can(permissions: string[], required: string | string[]): boolean {
  const list = Array.isArray(required) ? required : [required];
  return list.some((p) => permissions.includes(p));
}
```

---

## 2. المصادقة (Token)

- الطلبات المحمية تحتاج **Bearer Token** (Sanctum) للأدمن.
- الهيدر المعتاد:  
  `Authorization: Bearer {token}`  
- بدون توكن أو توكن منتهٍ / غير صالح: غالبًا **401**.

---

## 3. أخطاء الشبكة المتعلقة بالصلاحيات

| الحالة | المعنى العملي للـ FE |
|--------|----------------------|
| **401** | غير مصادق: لا يوجد توكن، أو التوكن غير صالح. → إعادة توجيه لصفحة تسجيل الدخول أو تحديث التوكن. |
| **403** | مصادق لكن **لا تملك الصلاحية** لهذا الإجراء. → إخفاء العنصر من الواجهة إن أمكن، أو عرض رسالة “ليس لديك صلاحية”. |

لا تعتمد فقط على إخفاء الواجهة: أي استدعاء API قد يرجع 403 إذا تغيّت صلاحيات المستخدم لاحقًا.

---

## 4. نمط أسماء الصلاحيات (CRUD)

لكثير من الموارد، الاسم يتبع:

`{مورد}.{view|create|update|delete}`

| طلب HTTP | اللاحقة |
|----------|---------|
| GET | `view` |
| POST | `create` |
| PUT / PATCH | `update` |
| DELETE | `delete` |

**أمثلة:**

- `GET /api/admin/products` → غالبًا يحتاج `product.view`
- `POST /api/admin/products` → `product.create`
- `PATCH /api/admin/products/5` → `product.update`
- `DELETE /api/admin/products/5` → `product.delete`

**استثناء:** مسار المتاجر يستخدم في الـ backend مفتاح الصلاحية **`shop.*`** (وليس `shops.*`)؛ اسم الصلاحية في الـ API يبقى كما في قائمة `permissions` القادمة من السيرفر (مثل `shop.view`).

---

## 5. صلاحيات صريحة (ليست CRUD عام)

هذه مسارات تستخدم أسماء صلاحيات **ثابتة** (لا تخمّنها من الـ HTTP فقط):

| المنطقة في الواجهة | صلاحية تقريبية للوصول |
|--------------------|-------------------------|
| سجل النشاط Activity logs | `activitylog.view` |
| الإحصائيات (كل `/statistics/...`) | `statistics.view` |
| التقارير والتصدير (كل `/reports/...`) | `reports.view` |
| إعدادات عامة (Settings) — قراءة | `setting.view` |
| إعدادات عامة — تعديل | `setting.update` |
| إعدادات النظام (System settings) — حسب العملية | `systemsetting.view` / `systemsetting.update` / `systemsetting.delete` |
| نقاط المستخدمين (`/user-points/...`) | `pointwallet.view` |
| محاسبة الموردين (`/vendor-accounting/...`) | `vendoraccounting.view` |
| قائمة أسماء الصلاحيات (للأدوار) `GET /permissions` | `role.view` |
| موافقة/رفض منتج | `product.update` |
| رفع هدايا دفعة `POST /gifts/bulk` | `gift.create` |
| طلبات التوصيل Orders | عرض: `order.view` — تعديل/تعيين: `order.update` |
| طلبات الخدمة Service orders | عرض: `serviceorder.view` — تعديل: `serviceorder.update` |

---

## 6. جدول مرجعي سريع — موارد CRUD شائعة

استخدم الجدول لربط **شاشة** في الـ Admin بصلاحيات **عرض/إضافة/تعديل/حذف** (حسب الأزرار عندكم).

| موضوع الواجهة | بادئة الصلاحية (مثال) |
|----------------|------------------------|
| منتجات | `product` |
| متاجر (Shops) | `shop` |
| موردون | `vendor` |
| طلبات (Orders API) | `order` |
| مستخدمون نهائيون | `user` |
| أدوار وصلاحيات | `role` |
| أدمن النظام | `admin` |
| سائقون | `driver` |
| كوبونات | `coupon` |
| أقسام / أقسام صفحات | `section` / `pagesection` |
| إشعارات لوحة الأدمن | `notification` |
| مستندات قانونية | `legaldocument` |
| سلة / جداول / اشتراكات… | `basket`, `schedule`, `package`, `subscription`, … |

القائمة الكاملة لأسماء الموارد تطابق ما يُزرع في قاعدة البيانات؛ المرجع التقني التفصيلي: `docs/ADMIN_ROUTE_PERMISSIONS.md`.

---

## 7. سلوك خاص يهم الـ FE

1. **`POST /api/admin/toggle-status`**  
   يبقى يتطلب تسجيل دخول أدمن، و**قد لا** يكون مربوطًا بصلاحية Spatie منفصلة في التعريف الحالي. تعامل معه كـ “يحتاج أدمن مسجّل”؛ إن رجع 403 راجع مع الفريق.

2. **مسارات Auth الفرعية** (`/auth/logout`, `/auth/profile`, …)  
   تحتاج `auth:admin` وليس بالضرورة صلاحية Spatie إضافية في نفس ملف المسارات.

3. **هذا الدليل خاص بـ Admin فقط**  
   مسارات **المستخدم** و**السائق** (`user` / `driver` APIs) لها مصادقة وقواعد منفصلة؛ لا تخلط صلاحيات `admin` معها.

---

## 8. checklist سريع للتكامل

- [ ] بعد Login، خزّن `user.permissions` (أو المسار الفعلي في الـ response عندكم).
- [ ] عند 403 على زر معيّن: أخفِ العنصر في الجلسات القادمة إن كان نفس المستخدم.
- [ ] اربط كل قسم رئيسي في القائمة الجانبية بصلاحية `view` المناسبة.
- [ ] أزرار الإنشاء/الحفظ/الحذف تربط بـ `create` / `update` / `delete` حسب الجدول أعلاه.
- [ ] لا تُعرّف أسماء صلاحيات جديدة في الـ FE؛ استخدم دائمًا ما يعيده الـ API في `permissions`.

---

*للتفاصيل التقنية (middleware، ملفات Laravel، seeders) راجع `docs/ADMIN_ROUTE_PERMISSIONS.md`.*
