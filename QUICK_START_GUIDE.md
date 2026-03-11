# دليل البدء السريع | Quick Start Guide

## 📚 الملفات المتاحة | Available Files

### 1. **COMPLETE_POINTS_AND_REWARDS_IMPLEMENTATION.md**
   - الملف الشامل الذي يحتوي على:
     - بنية قاعدة البيانات (SQL)
     - المودلز (Models)
     - الخدمات (Services)
     - الكونترولرز (Controllers)
     - الراوتس (Routes)
     - الموارد (Resources)
     - الطلبات (Requests)
     - الإشعارات (Notifications)
     - أمثلة الاستخدام
     - الاختبارات

### 2. **FRONTEND_POINTS_REWARDS_INTEGRATION.md**
   - دليل تكامل الفرونت إند يحتوي على:
     - جميع نقاط النهاية (API Endpoints)
     - أمثلة عملية بـ React
     - أمثلة بـ JavaScript/Axios
     - CSS Styles
     - Responsive Design

### 3. **QUICK_START_GUIDE.md** (هذا الملف)
   - دليل البدء السريع

---

## 🚀 خطوات البدء | Getting Started

### الخطوة 1: إنشاء قاعدة البيانات | Create Database

```bash
# تشغيل الـ Migration
php artisan migrate

# أو إنشاء الجداول يدويًا من الـ SQL في الملف الشامل
```

---

### الخطوة 2: إنشاء المودلز | Create Models

انسخ المودلز من الملف الشامل:
- `PointWallet.php`
- `PointTransaction.php`
- `PointExchange.php`
- `UserGift.php`

إلى المجلد: `app/Models/`

---

### الخطوة 3: إنشاء الخدمات | Create Services

انسخ الخدمات من الملف الشامل:
- `PointService.php`
- `UserGiftService.php`

إلى المجلد: `app/Services/`

---

### الخطوة 4: إنشاء الكونترولرز | Create Controllers

انسخ الكونترولرز من الملف الشامل:
- `PointController.php`
- `UserGiftController.php`
- `ExchangeController.php`

إلى المجلد: `app/Http/Controllers/User/`

---

### الخطوة 5: إضافة الراوتس | Add Routes

أضف الراوتس من الملف الشامل إلى:
`routes/api/user.php`

---

### الخطوة 6: إنشاء الموارد | Create Resources

انسخ الموارد من الملف الشامل:
- `PointSummaryResource.php`
- `PointTransactionResource.php`
- `UserGiftResource.php`

إلى المجلد: `app/Http/Resources/`

---

### الخطوة 7: إنشاء الطلبات | Create Requests

انسخ الطلبات من الملف الشامل:
- `RedeemPointsRequest.php`
- `ExchangeForCouponRequest.php`
- `ExchangeForGiftRequest.php`
- `UpdateAddressRequest.php`

إلى المجلد: `app/Http/Requests/User/`

---

### الخطوة 8: إنشاء الإشعارات | Create Notifications

انسخ الإشعارات من الملف الشامل:
- `PointsEarnedNotification.php`
- `GiftReceivedNotification.php`

إلى المجلد: `app/Notifications/`

---

## 🧪 الاختبار | Testing

### اختبار الـ API

```bash
# اختبار الحصول على ملخص النقاط
curl -X GET http://localhost:8000/api/user/points/summary \
  -H "Authorization: Bearer YOUR_TOKEN"

# اختبار استبدال النقاط
curl -X POST http://localhost:8000/api/user/points/redeem \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"points": 100, "reason": "استبدال بكوبون"}'

# اختبار الحصول على الجوائز
curl -X GET http://localhost:8000/api/user/user-gifts \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 📊 الاستخدام الأساسي | Basic Usage

### 1. منح نقاط للمستخدم | Award Points

```php
$pointService = app(PointService::class);

$transaction = $pointService->awardPoints(
    userId: 1,
    points: 100,
    source: 'purchase',
    reason: 'شراء منتج'
);
```

---

### 2. استبدال النقاط | Redeem Points

```php
$transaction = $pointService->redeemPoints(
    userId: 1,
    points: 50,
    reason: 'استبدال بكوبون'
);
```

---

### 3. الحصول على ملخص النقاط | Get Points Summary

```php
$summary = $pointService->getUserPointsSummary(userId: 1);

echo $summary['current_balance']; // 50
echo $summary['total_earned'];    // 100
```

---

### 4. إرسال جائزة للمستخدم | Send Gift to User

```php
$userGift = UserGift::create([
    'gift_id' => 123,
    'user_id' => 1,
    'status' => 'pending'
]);

// إرسال إشعار
$user->notify(new GiftReceivedNotification($userGift));
```

---

### 5. قبول الجائزة | Accept Gift

```php
$userGift = UserGift::find(1);
$userGift->accept();
```

---

### 6. تحديد عنوان التسليم | Set Delivery Address

```php
$userGift = UserGift::find(1);
$userGift->setAddress(addressId: 456);
```

---

## 🔌 التكامل مع الفرونت إند | Frontend Integration

### استخدام Axios

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('token')}`
  }
});

// الحصول على النقاط
api.get('/user/points/summary').then(res => {
  console.log(res.data.data);
});

// استبدال النقاط
api.post('/user/points/redeem', {
  points: 100,
  reason: 'استبدال بكوبون'
}).then(res => {
  console.log('تم الاستبدال بنجاح');
});
```

---

## 📋 قائمة التحقق | Checklist

- [ ] إنشاء الجداول
- [ ] إنشاء المودلز
- [ ] إنشاء الخدمات
- [ ] إنشاء الكونترولرز
- [ ] إضافة الراوتس
- [ ] إنشاء الموارد
- [ ] إنشاء الطلبات
- [ ] إنشاء الإشعارات
- [ ] اختبار الـ API
- [ ] تطوير الفرونت إند
- [ ] اختبار التكامل
- [ ] النشر

---

## 🐛 استكشاف الأخطاء | Troubleshooting

### المشكلة: "رصيد النقاط غير كافي"
**الحل:** تأكد من أن المستخدم لديه رصيد كافي قبل الاستبدال

### المشكلة: "الجائزة غير موجودة"
**الحل:** تأكد من أن معرف الجائزة صحيح وأن الجائزة موجودة في قاعدة البيانات

### المشكلة: "العنوان غير موجود"
**الحل:** تأكد من أن معرف العنوان صحيح وأنه ينتمي للمستخدم

---

## 📞 الدعم | Support

للمزيد من المعلومات، راجع:
- `COMPLETE_POINTS_AND_REWARDS_IMPLEMENTATION.md` - الملف الشامل
- `FRONTEND_POINTS_REWARDS_INTEGRATION.md` - دليل الفرونت إند

---

## 🎯 الخطوات التالية | Next Steps

1. **تخصيص القواعد**: عدّل قواعد النقاط حسب احتياجات مشروعك
2. **إضافة المزيد من الخيارات**: أضف خيارات استبدال جديدة
3. **تحسين الواجهة**: صمم واجهة مستخدم جميلة
4. **الاختبار الشامل**: اختبر جميع الحالات
5. **النشر**: انشر التطبيق

---

**آخر تحديث | Last Updated:** 2024-03-11
**الإصدار | Version:** 1.0.0

