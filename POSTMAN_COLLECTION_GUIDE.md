# دليل استخدام Postman Collection لنظام العملات

## 📦 الملفات المطلوبة

1. `Currency_API.postman_collection.json` - مجموعة الـ APIs
2. `Currency_API.postman_environment.json` - متغيرات البيئة

---

## 🚀 خطوات الاستيراد

### 1. استيراد الـ Collection

1. افتح Postman
2. اضغط على **Import** في الزاوية العلوية اليسرى
3. اسحب ملف `Currency_API.postman_collection.json` أو اضغط **Choose Files**
4. اضغط **Import**

### 2. استيراد الـ Environment

1. اضغط على أيقونة **Environments** في الشريط الجانبي
2. اضغط **Import**
3. اختر ملف `Currency_API.postman_environment.json`
4. اضغط **Import**

### 3. تفعيل الـ Environment

1. من القائمة المنسدلة في الأعلى، اختر **Currency API - Local**
2. الآن البيئة مفعلة

---

## ⚙️ إعداد المتغيرات

### تحديث الـ Base URL (إذا لزم الأمر)

```
base_url: http://localhost:8000
```

إذا كان السيرفر على بورت مختلف أو دومين مختلف، عدل القيمة.

### إضافة الـ Tokens

#### للحصول على Admin Token:

```http
POST {{base_url}}/api/admin/auth/login
Content-Type: application/json

{
    "email": "admin@admin.com",
    "password": "password"
}
```

انسخ الـ `token` من الـ Response وضعه في:
- Environments → Currency API - Local → `admin_token`

#### للحصول على User Token:

```http
POST {{base_url}}/api/user/auth/login
Content-Type: application/json

{
    "phone": "1234567890",
    "password": "password"
}
```

انسخ الـ `token` من الـ Response وضعه في:
- Environments → Currency API - Local → `user_token`

---

## 📋 قائمة الـ APIs

### User APIs (لا تحتاج Authentication إلا المحدد)

#### 1. Get All Active Currencies
```
GET /api/user/currencies
```
- عام (لا يحتاج token)
- يرجع كل العملات النشطة

#### 2. Get User Currency ✅ Auth Required
```
GET /api/user/currencies/my-currency
Authorization: Bearer {{user_token}}
```
- يرجع العملة المفضلة للمستخدم الحالي

#### 3. Update User Currency ✅ Auth Required
```
PATCH /api/user/currencies/update-currency
Authorization: Bearer {{user_token}}
Content-Type: application/json

{
    "currency_id": 2
}
```
- تحديث عملة المستخدم

---

### Admin APIs (كلها تحتاج Admin Token)

#### 1. Get All Currencies
```
GET /api/admin/currencies?page=1&per_page=10
Authorization: Bearer {{admin_token}}
```
- جلب كل العملات مع pagination
- يمكن البحث: `?search=USD`

#### 2. Get Currency By ID
```
GET /api/admin/currencies/1
Authorization: Bearer {{admin_token}}
```
- جلب تفاصيل عملة واحدة

#### 3. Create Currency
```
POST /api/admin/currencies
Authorization: Bearer {{admin_token}}
Content-Type: application/json

{
    "code": "EUR",
    "name": {
        "en": "Euro",
        "ar": "يورو"
    },
    "symbol": "€",
    "exchange_rate": 0.92,
    "is_default": false,
    "is_active": true
}
```

#### 4. Update Currency
```
PUT /api/admin/currencies/2
Authorization: Bearer {{admin_token}}
Content-Type: application/json

{
    "exchange_rate": 13500.00
}
```
- يمكن تحديث حقل واحد أو كل الحقول

#### 5. Toggle Currency Status
```
PATCH /api/admin/currencies/2/toggle-status
Authorization: Bearer {{admin_token}}
```
- تفعيل/تعطيل العملة
- لا يمكن تعطيل العملة الافتراضية

#### 6. Delete Currency
```
DELETE /api/admin/currencies/6
Authorization: Bearer {{admin_token}}
```
- حذف عملة

---

## 🧪 سيناريوهات الاختبار

### السيناريو 1: إنشاء عملة جديدة

1. استخدم **Create Currency** مع بيانات اليورو
2. تحقق من الـ Response أن العملة تم إنشاؤها
3. استخدم **Get All Currencies** للتأكد من ظهورها

### السيناريو 2: تحديث سعر الصرف

1. استخدم **Get Currency By ID** لجلب العملة
2. استخدم **Update Currency** لتحديث `exchange_rate`
3. تحقق من التحديث باستخدام **Get Currency By ID** مرة أخرى

### السيناريو 3: تغيير عملة المستخدم

1. استخدم **Get All Active Currencies** لرؤية العملات المتاحة
2. استخدم **Update User Currency** لتغيير العملة
3. استخدم **Get User Currency** للتأكد من التغيير

### السيناريو 4: تعطيل عملة

1. استخدم **Toggle Currency Status** لتعطيل عملة
2. استخدم **Get All Active Currencies** (User API) للتأكد أنها لا تظهر
3. استخدم **Toggle Currency Status** مرة أخرى لإعادة تفعيلها

---

## 🌍 تغيير اللغة

في كل Request، يمكنك تغيير اللغة عبر Header:

```
Accept-Language: ar
```

أو

```
Accept-Language: en
```

---

## 📊 أمثلة على الـ Responses

### Success Response
```json
{
    "status": true,
    "message": "تم جلب العملات بنجاح",
    "data": [
        {
            "id": 1,
            "code": "USD",
            "name": "دولار أمريكي",
            "symbol": "$",
            "is_default": true
        }
    ]
}
```

### Error Response
```json
{
    "status": false,
    "message": "لا يمكن تعطيل العملة الافتراضية",
    "data": null
}
```

### Validation Error
```json
{
    "status": false,
    "message": "البيانات المدخلة غير صحيحة",
    "errors": {
        "code": [
            "The code field is required."
        ],
        "exchange_rate": [
            "The exchange rate must be at least 0.000001."
        ]
    }
}
```

---

## 🔧 نصائح الاستخدام

1. **احفظ الـ Tokens**: بعد تسجيل الدخول، احفظ الـ tokens في الـ Environment
2. **استخدم Variables**: استخدم `{{base_url}}` بدلاً من كتابة الرابط كاملاً
3. **اختبر بالترتيب**: ابدأ بـ GET ثم POST ثم PUT ثم DELETE
4. **راقب الـ Console**: في حالة الأخطاء، افتح Postman Console (View → Show Postman Console)
5. **استخدم Tests**: يمكنك إضافة Tests تلقائية للتحقق من الـ Responses

---

## 🐛 حل المشاكل الشائعة

### 401 Unauthorized
- تأكد من أن الـ Token صحيح ومحدث
- تأكد من أن الـ Token في الـ Environment متغير

### 404 Not Found
- تأكد من الـ base_url صحيح
- تأكد من أن السيرفر شغال: `php artisan serve`

### 422 Validation Error
- راجع الـ Request Body
- تأكد من أن كل الحقول المطلوبة موجودة

### 500 Server Error
- راجع الـ Laravel logs: `storage/logs/laravel.log`
- تأكد من أن الـ Database متصل

---

## 📝 ملاحظات إضافية

- كل الأسعار في الداتابيز مخزنة بالدولار
- التحويل يتم تلقائياً عند العرض
- العملة الافتراضية لا يمكن تعطيلها أو حذفها
- عند تعيين عملة جديدة كافتراضية، يتم إلغاء الافتراضية من العملات الأخرى تلقائياً

---

## 🎯 الخطوات التالية

بعد اختبار الـ APIs، يمكنك:

1. دمج نظام العملات مع Products API
2. إضافة التحويل التلقائي في Order API
3. إضافة Currency Selector في الـ Frontend
4. إضافة تقارير بالعملات المختلفة

---

تم إنشاء هذا الدليل لمساعدتك في اختبار نظام العملات بسهولة! 🚀
