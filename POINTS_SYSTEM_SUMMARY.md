# ملخص نظام النقاط - Points System Summary

## قواعد اكتساب النقاط المتوفرة

### 1. إنشاء حساب (user_registration)
- **القيمة الافتراضية**: 100 نقطة
- **النوع**: ثابت (fixed)
- **الشرط**: يُمنح مرة واحدة فقط عند إنشاء الحساب
- **الموقع**: `AwardPointsListener::handleUserRegistered()`

### 2. أول طلب (first_order)
- **القيمة الافتراضية**: 50 نقطة
- **النوع**: ثابت (fixed)
- **الشرط**: يُمنح مرة واحدة فقط عند إتمام أول طلب
- **الموقع**: `AwardPointsListener::handleOrderStatusChanged()`

### 3. إتمام طلب (order_completion)
- **القيمة الافتراضية**: 100 نقطة
- **النوع**: ثابت (fixed)
- **الشرط**: يُمنح عند إتمام كل طلب (عندما يصبح الطلب delivered)
- **الموقع**: `AwardPointsListener::handleOrderStatusChanged()`

### 4. تقييم منتج (product_review)
- **القيمة الافتراضية**: 10 نقاط
- **النوع**: ثابت (fixed)
- **الشرط**: يُمنح عند تقييم أي منتج
- **الموقع**: `AwardPointsListener::handleReviewSubmitted()`

### 5. قيمة الشراء (purchase_amount_threshold)
- **القيمة الافتراضية**: 200 نقطة
- **النوع**: ثابت (fixed)
- **الحد الأدنى**: 100 (min_order_amount)
- **الشرط**: يُمنح عندما تصل قيمة الطلب إلى 100 أو أكثر
- **الموقع**: `AwardPointsListener::handleOrderStatusChanged()`

### 6. نقاط الباقة (subscription_package)
- **القيمة**: حسب الباقة (points_bonus في جدول packages)
- **النوع**: ثابت (fixed)
- **الشرط**: يُمنح فوراً عند الاشتراك في باقة
- **الموقع**: `SubscriptionService::subscribe()`

## API الإدارة

### قراءة جميع القواعد
```
GET /api/admin/point-rules
```

### قراءة قاعدة واحدة
```
GET /api/admin/point-rules/{id}
```

### تحديث قاعدة (تعديل القيمة فقط)
```
PUT /api/admin/point-rules/{id}
Body:
{
    "value": 150,
    "min_order_amount": 50.00,
    "is_active": true
}
```

### إنشاء قاعدة جديدة
```
POST /api/admin/point-rules
Body:
{
    "code": "special_event",
    "title": "Special Event Bonus",
    "type": "fixed",
    "value": 300,
    "min_order_amount": null,
    "expires_after_days": 365,
    "is_active": true
}
```

### حذف قاعدة
```
DELETE /api/admin/point-rules/{id}
```

## الملفات المعدلة

1. **database/seeders/PointRuleSeeder.php**
   - تم تحديث القواعد لتشمل جميع الحالات المطلوبة
   - تم استخدام `updateOrCreate` لتجنب التكرار

2. **routes/api/admin.php**
   - تم تفعيل `Route::apiResource('point-rules', PointRuleController::class)`

3. **app/Listeners/AwardPointsListener.php**
   - تم إضافة منح نقاط `purchase_amount_threshold`

4. **app/Services/User/SubscriptionService.php**
   - تم تحسين منح نقاط الباقة باستخدام `PointService`
   - يتم تسجيل النقاط في جدول transactions

5. **app/Http/Requests/Admin/Point/UpdatePointRuleRequest.php** (جديد)
   - Request خاص بالتحديث يسمح فقط بتعديل القيمة

6. **app/Http/Resources/Admin/PointRule/PointRuleResource.php** (جديد)
   - Resource للـ API responses

7. **resources/lang/ar/custom.php** و **resources/lang/en/custom.php**
   - تم إضافة ترجمات لقواعد النقاط

## كيفية التشغيل

1. تشغيل الـ seeder:
```bash
php artisan db:seed --class=PointRuleSeeder
```

2. التحقق من الـ routes:
```bash
php artisan route:list --path=point-rules
```

## ملاحظات مهمة

- جميع قيم النقاط هي أرقام ثابتة (100، 200، إلخ) وليست نسب مئوية
- يمكن للإدارة تعديل قيمة النقاط لأي قاعدة عبر API
- نقاط الباقة (points_bonus) موجودة في جدول packages ويتم منحها عند الاشتراك
- جميع القواعد تدعم `min_order_amount` لتحديد الحد الأدنى لقيمة الطلب
- النقاط تُمنح فقط عندما يصبح الطلب `delivered`
