# نظام الموافقة على طلبات البائعين - دليل شامل

## نظرة عامة

هذا النظام يسمح للأدمن بإدارة طلبات تسجيل البائعين، الموافقة عليها أو رفضها، وإنشاء حسابات البائعين تلقائياً مع إرسال بيانات الدخول عبر البريد الإلكتروني.

## البنية الهيكلية

### الملفات المنشأة

```
app/
├── Http/
│   ├── Controllers/Admin/SellerRegistration/
│   │   └── SellerRegistrationCrudController.php       # Controller للعمليات CRUD + Approve/Reject
│   ├── Requests/Admin/SellerRegistration/
│   │   └── ApproveRequest.php                         # Validation للموافقة
│   └── Resources/SellerRegistration/
│       ├── AllResource.php                            # Resource للقائمة
│       └── OneResource.php                            # Resource للعنصر الواحد
├── Services/Admin/
│   └── SellerRegistrationService.php                  # Business Logic
├── Models/
│   └── SellerRegistration.php                         # Model موجود مسبقاً
├── Mail/
│   └── VendorCredentialsMail.php                      # Email Template
└── Filament/Resources/SellerRegistrations/
    ├── SellerRegistrationResource.php                 # Filament Admin Panel
    └── SellerRegistrationResource/Pages/
        ├── ListSellerRegistrations.php
        ├── ViewSellerRegistration.php
        ├── EditSellerRegistration.php
        └── CreateSellerRegistration.php

resources/views/emails/
└── vendor-credentials.blade.php                       # Email View

routes/api/
└── admin.php                                          # Routes
```

---

## الوظائف الرئيسية

### 1. عرض جميع الطلبات
**GET** `/api/admin/seller-registrations`

يعرض قائمة بجميع طلبات التسجيل مع pagination

### 2. عرض طلب واحد
**GET** `/api/admin/seller-registrations/{id}`

يعرض تفاصيل طلب معين

### 3. الموافقة على الطلب
**POST** `/api/admin/seller-registrations/{id}/approve`

عند الموافقة، النظام يقوم بـ:
1. إنشاء Vendor جديد
2. إنشاء Shop أول للـ Vendor
3. إنشاء VendorUser مع كلمة مرور عشوائية
4. ربط VendorUser بالـ Shop
5. إرسال بريد إلكتروني يحتوي على:
   - البريد الإلكتروني
   - كلمة المرور
   - رابط تسجيل الدخول
6. تحديث حالة الطلب إلى 'approved'

**Request Body (اختياري):**
```json
{
  "commission_rate": 10.5,
  "contract_duration_months": 12
}
```

### 4. رفض الطلب
**POST** `/api/admin/seller-registrations/{id}/reject`

يقوم بتحديث حالة الطلب إلى 'rejected'

### 5. حذف الطلب
**DELETE** `/api/admin/seller-registrations/{id}`

يحذف الطلب من قاعدة البيانات

---

## Filament Admin Panel

يمكن للأدمن إدارة الطلبات من خلال لوحة Filament:

**الرابط:** `/admin/seller-registrations`

**المميزات:**
- عرض جميع الطلبات مع فلاتر حسب الحالة
- عرض/تعديل تفاصيل الطلب
- زر "Approve" للموافقة (يظهر فقط للطلبات pending)
- زر "Reject" للرفض (يظهر فقط للطلبات pending)
- Status badges ملونة (pending/approved/rejected)
- إرسال البريد الإلكتروني تلقائياً عند الموافقة

---

## سير العمل (Workflow)

```
1. المستخدم يملأ فورم التسجيل كبائع
   ↓
2. يتم حفظ البيانات في جدول seller_registrations بحالة 'pending'
   ↓
3. الأدمن يراجع الطلب في Filament Panel أو عبر API
   ↓
4. الأدمن يوافق على الطلب
   ↓
5. النظام ينشئ:
   - Vendor
   - Shop (أول محل للبائع)
   - VendorUser (حساب المستخدم)
   ↓
6. النظام يربط VendorUser بالـ Shop
   ↓
7. النظام يرسل بريد إلكتروني للبائع يحتوي على:
   - البريد الإلكتروني
   - كلمة المرور المؤقتة
   - رابط تسجيل الدخول
   - تعليمات الأمان
   ↓
8. البائع يستلم البريد ويسجل دخول
   ↓
9. البائع يغير كلمة المرور في أول تسجيل دخول
```

---

## البريد الإلكتروني

### محتوى البريد:
- رسالة ترحيب
- بيانات الدخول (Email + Password)
- رابط تسجيل الدخول
- تعليمات الأمان:
  - تغيير كلمة المرور فوراً
  - عدم مشاركة البيانات
  - استخدام كلمة مرور قوية

### Template:
الملف: `resources/views/emails/vendor-credentials.blade.php`

---

## حالات الطلب (Status)

| الحالة | الوصف |
|--------|-------|
| `pending` | الطلب بانتظار مراجعة الأدمن |
| `approved` | تمت الموافقة وإنشاء الحساب |
| `rejected` | تم رفض الطلب |

---

## الأمان (Security)

1. جميع الـ endpoints تتطلب مصادقة Admin
2. كلمات المرور يتم تشفيرها قبل الحفظ
3. يتم توليد كلمات مرور عشوائية آمنة (12 حرف)
4. البريد الإلكتروني يحتوي على تعليمات أمان
5. يجب على البائع تغيير كلمة المرور عند أول تسجيل دخول

---

## الاختبار

### 1. اختبار API

```bash
# عرض جميع الطلبات
curl -X GET "http://your-domain/api/admin/seller-registrations" \
  -H "Authorization: Bearer {admin_token}"

# الموافقة على طلب
curl -X POST "http://your-domain/api/admin/seller-registrations/1/approve" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "commission_rate": 10,
    "contract_duration_months": 12
  }'

# رفض طلب
curl -X POST "http://your-domain/api/admin/seller-registrations/1/reject" \
  -H "Authorization: Bearer {admin_token}"
```

### 2. اختبار Filament Panel

1. سجل دخول كـ Admin
2. اذهب إلى `/admin/seller-registrations`
3. اختر طلب بحالة 'pending'
4. اضغط على "Approve"
5. تحقق من:
   - تم إنشاء Vendor
   - تم إنشاء Shop
   - تم إنشاء VendorUser
   - تم إرسال البريد الإلكتروني

---

## معالجة الأخطاء

### الأخطاء المحتملة:

1. **Registration is not pending**
   - يحدث عند محاولة الموافقة/الرفض لطلب ليس بحالة pending

2. **Email sending failed**
   - تحقق من إعدادات SMTP في `.env`

3. **Database transaction failed**
   - تحقق من العلاقات بين الجداول
   - تحقق من الحقول المطلوبة

---

## الإعدادات المطلوبة

### 1. إعدادات البريد الإلكتروني (.env)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 2. رابط تسجيل الدخول للبائعين

في ملف `config/app.php` أو `.env`:
```env
APP_URL=https://yourdomain.com
```

---

## التخصيص

### 1. تخصيص البريد الإلكتروني

عدّل الملف: `resources/views/emails/vendor-credentials.blade.php`

### 2. تخصيص نسبة العمولة الافتراضية

في `SellerRegistrationService.php`:
```php
'commission_rate' => $data['commission_rate'] ?? 10, // غيّر القيمة الافتراضية
```

### 3. تخصيص مدة العقد الافتراضية

في `SellerRegistrationService.php`:
```php
'contract_duration_months' => $data['contract_duration_months'] ?? 12, // غيّر القيمة
```

---

## الدعم والمساعدة

للمزيد من المعلومات، راجع:
- `SELLER_REGISTRATION_API_DOCUMENTATION.md` - توثيق API كامل
- `app/Services/Admin/SellerRegistrationService.php` - Business Logic
- `app/Filament/Resources/SellerRegistrations/SellerRegistrationResource.php` - Filament Resource

---

## الملاحظات المهمة

1. ✅ النظام يتبع نفس البنية الموجودة في المشروع (Controllers/Services/Requests/Resources)
2. ✅ يستخدم BaseService للعمليات CRUD
3. ✅ يستخدم BaseCRUDController للـ Controller
4. ✅ Resources منفصلة (AllResource/OneResource)
5. ✅ Validation منفصل في Request classes
6. ✅ Business Logic في Service
7. ✅ يدعم Filament Admin Panel
8. ✅ يرسل بريد إلكتروني تلقائياً
9. ✅ Transaction آمن لضمان تكامل البيانات
10. ✅ معالجة أخطاء شاملة
