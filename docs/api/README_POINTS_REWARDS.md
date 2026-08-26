# نظام النقاط والجوائز الشامل | Complete Points and Rewards System

## 📚 الملفات المتاحة | Available Documentation

هذا المشروع يحتوي على توثيق شامل لنظام النقاط والجوائز:

### 1. 📖 **COMPLETE_POINTS_AND_REWARDS_IMPLEMENTATION.md**
   **الملف الرئيسي الشامل**
   - بنية قاعدة البيانات (SQL)
   - المودلز (Models) - 4 مودلز
   - الخدمات (Services) - 2 خدمة
   - الكونترولرز (Controllers) - 3 كونترولرز
   - الراوتس (Routes)
   - الموارد (Resources)
   - الطلبات (Requests)
   - الإشعارات (Notifications)
   - الاختبارات (Tests)
   - الإعدادات (Configuration)

### 2. 🎨 **FRONTEND_POINTS_REWARDS_INTEGRATION.md**
   **دليل تكامل الفرونت إند**
   - جميع نقاط النهاية (API Endpoints)
   - أمثلة عملية بـ React
   - أمثلة بـ JavaScript/Axios
   - مكونات الواجهة (UI Components)
   - CSS Styles
   - Responsive Design

### 3. 🚀 **QUICK_START_GUIDE.md**
   **دليل البدء السريع**
   - خطوات البدء خطوة بخطوة
   - الاستخدام الأساسي
   - التكامل مع الفرونت إند
   - استكشاف الأخطاء

### 4. 💡 **PRACTICAL_EXAMPLE_COMPLETE.md**
   **مثال عملي كامل**
   - سيناريو واقعي من البداية للنهاية
   - كود Backend كامل
   - كود Frontend كامل
   - النتائج المتوقعة
   - الإشعارات المرسلة

### 5. 📋 **README_POINTS_REWARDS.md** (هذا الملف)
   **الفهرس والملخص**

---

## 🎯 نظرة عامة | Overview

### نظام النقاط | Points System

**التعريف:**
- نقاط يكسبها المستخدم من خلال الشراء والأنشطة
- يمكن استبدالها بخصومات أو هدايا أو توصيل مجاني
- تنتهي صلاحيتها بعد سنة من الحصول عليها

**الميزات:**
- ✅ كسب النقاط تلقائياً من الشراء
- ✅ استبدال النقاط بسهولة
- ✅ تتبع سجل المعاملات
- ✅ إشعارات فورية
- ✅ انتهاء صلاحية تلقائي

### نظام الجوائز | Rewards System

**التعريف:**
- هدايا يرسلها الإدارة للمستخدمين
- يمكن أن تكون منتجات أو قسائم أو نقاط
- يحدد المستخدم عنوان التسليم عند استقبالها

**الميزات:**
- ✅ إرسال جوائز من الإدارة
- ✅ قبول أو رفض الجوائز
- ✅ تحديد عنوان التسليم
- ✅ تتبع حالة الجائزة
- ✅ إشعارات للمستخدم والإدارة

---

## 🗄️ بنية قاعدة البيانات | Database Structure

### الجداول الرئيسية | Main Tables

```
point_wallets
├── user_id (FK)
├── balance
├── expire_at
└── last_earned_at

point_transactions
├── user_id (FK)
├── wallet_id (FK)
├── source (purchase, review, referral, etc.)
├── points
├── status (pending, earned, expired, redeemed)
├── expires_at
└── reason

point_exchanges
├── user_id (FK)
├── transaction_id (FK)
├── exchange_type (coupon, free_delivery, gift)
├── exchange_data (JSON)
└── status

user_gifts
├── gift_id (FK)
├── user_id (FK)
├── address_id (FK)
├── status (pending, accepted, address_pending, shipped, delivered)
└── delivered_at
```

---

## 🔄 تدفق العمليات | Process Flows

### تدفق كسب النقاط | Points Earning Flow

```
المستخدم يشتري منتج
    ↓
تفعيل حدث الشراء
    ↓
حساب النقاط (1 نقطة = 1 دولار)
    ↓
إنشاء معاملة نقاط
    ↓
إضافة النقاط للمحفظة
    ↓
إرسال إشعار
    ↓
تحديث الرصيد
```

### تدفق استبدال النقاط | Points Redemption Flow

```
المستخدم يختار الاستبدال
    ↓
التحقق من الرصيد
    ↓
خصم النقاط
    ↓
إنشاء معاملة استبدال
    ↓
إنشاء الكوبون/الهدية
    ↓
إرسال إشعار
    ↓
إكمال العملية
```

### تدفق استقبال الجائزة | Gift Reception Flow

```
الإدارة تنشئ جائزة
    ↓
إرسال إشعار للمستخدم
    ↓
المستخدم يقبل الجائزة
    ↓
تحديد عنوان التسليم
    ↓
إرسال إشعار للإدارة
    ↓
الشحن والتسليم
    ↓
تحديث الحالة
```

---

## 📊 المودلز | Models

### 1. PointWallet
- تخزين رصيد النقاط للمستخدم
- تتبع آخر وقت تم كسب نقاط فيه

### 2. PointTransaction
- تسجيل كل معاملة نقاط
- تتبع مصدر النقاط والحالة

### 3. PointExchange
- تسجيل عمليات الاستبدال
- تخزين بيانات الاستبدال (JSON)

### 4. UserGift
- تسجيل الجوائز المرسلة للمستخدمين
- تتبع حالة الجائزة والعنوان

---

## 🔧 الخدمات | Services

### PointService
```php
- getOrCreateWallet()
- awardPoints()
- redeemPoints()
- getUserPointsSummary()
- expireOldPoints()
- getTransactionsCountByStatus()
```

### UserGiftService
```php
- getUserGifts()
- acceptGift()
- rejectGift()
- setDeliveryAddress()
```

---

## 🎮 الكونترولرز | Controllers

### PointController
```
GET  /api/user/points/summary
GET  /api/user/points/transactions
POST /api/user/points/redeem
GET  /api/user/points/statistics
```

### UserGiftController
```
GET  /api/user/user-gifts
GET  /api/user/user-gifts/{id}
POST /api/user/user-gifts/{id}/accept
POST /api/user/user-gifts/{id}/reject
PUT  /api/user/user-gifts/{id}/address
```

### ExchangeController
```
GET  /api/user/points/exchange/options
POST /api/user/points/exchange/coupon
POST /api/user/points/exchange/gift
GET  /api/user/points/exchange/history
GET  /api/user/points/exchange/active
```

---

## 📱 نقاط النهاية | API Endpoints

### النقاط | Points

| الطريقة | Method | المسار | Path | الوصف | Description |
|--------|--------|--------|------|--------|-------------|
| GET | GET | `/api/user/points/summary` | الحصول على ملخص | Get summary |
| GET | GET | `/api/user/points/transactions` | سجل المعاملات | Transactions |
| POST | POST | `/api/user/points/redeem` | استبدال | Redeem |
| GET | GET | `/api/user/points/statistics` | الإحصائيات | Statistics |

### الجوائز | Gifts

| الطريقة | Method | المسار | Path | الوصف | Description |
|--------|--------|--------|------|--------|-------------|
| GET | GET | `/api/user/user-gifts` | قائمة الجوائز | List gifts |
| GET | GET | `/api/user/user-gifts/{id}` | تفاصيل | Details |
| POST | POST | `/api/user/user-gifts/{id}/accept` | قبول | Accept |
| POST | POST | `/api/user/user-gifts/{id}/reject` | رفض | Reject |
| PUT | PUT | `/api/user/user-gifts/{id}/address` | العنوان | Address |

### الاستبدال | Exchange

| الطريقة | Method | المسار | Path | الوصف | Description |
|--------|--------|--------|------|--------|-------------|
| GET | GET | `/api/user/points/exchange/options` | الخيارات | Options |
| POST | POST | `/api/user/points/exchange/coupon` | كوبون | Coupon |
| POST | POST | `/api/user/points/exchange/gift` | هدية | Gift |
| GET | GET | `/api/user/points/exchange/history` | السجل | History |

---

## 🔔 الإشعارات | Notifications

### PointsEarnedNotification
```
"تم إضافة 100 نقطة إلى حسابك"
```

### PointsRedeemedNotification
```
"تم استبدال 100 نقطة بكوبون COUPON-ABC123"
```

### GiftReceivedNotification
```
"تلقيت جائزة جديدة: منتج مميز"
```

### GiftAcceptedNotification
```
"المستخدم قبل الجائزة"
```

---

## 🧪 الاختبارات | Tests

```php
// اختبار منح النقاط
test_can_award_points()

// اختبار استبدال النقاط
test_can_redeem_points()

// اختبار عدم الاستبدال إذا كان الرصيد غير كافي
test_cannot_redeem_more_than_balance()

// اختبار الحصول على ملخص النقاط
test_can_get_points_summary()
```

---

## 🚀 البدء السريع | Quick Start

### 1. إنشاء الجداول
```bash
php artisan migrate
```

### 2. إنشاء المودلز والخدمات والكونترولرز
انسخ الملفات من الملف الشامل

### 3. إضافة الراوتس
أضف الراوتس من الملف الشامل

### 4. اختبار الـ API
```bash
curl -X GET http://localhost:8000/api/user/points/summary \
  -H "Authorization: Bearer TOKEN"
```

---

## 📊 الإحصائيات | Statistics

### معدلات الاستبدال | Exchange Rates

```
100 نقطة = 10 دولار (كوبون)
200 نقطة = توصيل مجاني
500 نقطة = هدية
```

### صلاحية النقاط | Points Validity

```
مدة الصلاحية: 1 سنة
تاريخ الانتهاء: تاريخ الحصول + 365 يوم
```

---

## 🔐 الأمان | Security

- ✅ التحقق من الرصيد قبل الاستبدال
- ✅ تشفير بيانات العنوان
- ✅ تسجيل جميع المعاملات
- ✅ التحقق من صلاحية النقاط
- ✅ منع الاستبدال المتكرر

---

## 📋 قائمة التحقق | Checklist

- [ ] قراءة الملف الشامل
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
**الحل:** تأكد من أن المستخدم لديه رصيد كافي

### المشكلة: "الجائزة غير موجودة"
**الحل:** تأكد من معرف الجائزة

### المشكلة: "العنوان غير موجود"
**الحل:** تأكد من معرف العنوان

---

## 📞 الدعم | Support

للمزيد من المعلومات:
- اقرأ الملف الشامل: `COMPLETE_POINTS_AND_REWARDS_IMPLEMENTATION.md`
- اقرأ دليل الفرونت إند: `FRONTEND_POINTS_REWARDS_INTEGRATION.md`
- اقرأ المثال العملي: `PRACTICAL_EXAMPLE_COMPLETE.md`
- اقرأ دليل البدء السريع: `QUICK_START_GUIDE.md`

---

## 📈 الخطوات التالية | Next Steps

1. **تخصيص القواعد**: عدّل قواعد النقاط
2. **إضافة خيارات**: أضف خيارات استبدال جديدة
3. **تحسين الواجهة**: صمم واجهة جميلة
4. **الاختبار الشامل**: اختبر جميع الحالات
5. **النشر**: انشر التطبيق

---

## 📊 الملخص | Summary

| العنصر | الكمية | الملف |
|--------|--------|--------|
| الجداول | 4 | COMPLETE_POINTS_AND_REWARDS_IMPLEMENTATION.md |
| المودلز | 4 | COMPLETE_POINTS_AND_REWARDS_IMPLEMENTATION.md |
| الخدمات | 2 | COMPLETE_POINTS_AND_REWARDS_IMPLEMENTATION.md |
| الكونترولرز | 3 | COMPLETE_POINTS_AND_REWARDS_IMPLEMENTATION.md |
| الراوتس | 15+ | COMPLETE_POINTS_AND_REWARDS_IMPLEMENTATION.md |
| الموارد | 3 | COMPLETE_POINTS_AND_REWARDS_IMPLEMENTATION.md |
| الطلبات | 4 | COMPLETE_POINTS_AND_REWARDS_IMPLEMENTATION.md |
| الإشعارات | 2 | COMPLETE_POINTS_AND_REWARDS_IMPLEMENTATION.md |
| أمثلة React | 3 | FRONTEND_POINTS_REWARDS_INTEGRATION.md |
| أمثلة JavaScript | 3 | PRACTICAL_EXAMPLE_COMPLETE.md |

---

## 🎓 الموارد التعليمية | Learning Resources

- Laravel Documentation: https://laravel.com/docs
- React Documentation: https://react.dev
- API Design Best Practices: https://restfulapi.net

---

**آخر تحديث | Last Updated:** 2024-03-11
**الإصدار | Version:** 1.0.0
**الحالة | Status:** ✅ جاهز للاستخدام | Ready to Use

---

## 📝 الملاحظات | Notes

- جميع الأمثلة مكتوبة بلغتين: العربية والإنجليزية
- جميع الكود متوافق مع Laravel 10+
- جميع الكود متوافق مع PHP 8.1+
- جميع الأمثلة اختبرت وتعمل بشكل صحيح

---

