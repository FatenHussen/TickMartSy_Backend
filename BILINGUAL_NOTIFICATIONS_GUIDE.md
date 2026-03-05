# 🌍 Bilingual Notifications Guide
## دليل الإشعارات ثنائية اللغة

---

## 📋 Overview - نظرة عامة

تم تطوير نظام الإشعارات ليدعم اللغتين العربية والإنجليزية تلقائياً. النظام يرسل الإشعار بلغة المستخدم المفضلة، ويوفر كلا النسختين للفرونت إند.

The notification system now supports both Arabic and English automatically. The system sends notifications in the user's preferred language and provides both versions to the frontend.

---

## 🔧 How It Works - كيف يعمل

### Backend Implementation

```php
// Send bilingual notification
$this->notificationService->send(
    recipient: $user,
    title: json_encode([
        'ar' => '🎉 مرحباً بك!',
        'en' => '🎉 Welcome!'
    ]),
    body: json_encode([
        'ar' => "تهانينا! حصلت على 100 نقطة",
        'en' => "Congratulations! You earned 100 points"
    ]),
    data: [
        'type' => 'points_earned',
        'points' => 100
    ]
);
```

### What Happens:

1. **Language Detection**: النظام يحدد لغة المستخدم من `app()->getLocale()` أو يستخدم العربية كافتراضي
2. **Localization**: يختار النص المناسب حسب اللغة
3. **FCM Notification**: يرسل الإشعار بالنص المترجم
4. **Database Storage**: يحفظ الإشعار في قاعدة البيانات
5. **Data Payload**: يضيف كلا النسختين في الـ data للفرونت إند

---

## 📱 Frontend Integration

### Notification Payload Structure

```json
{
  "notification": {
    "title": "🎉 مرحباً بك!",
    "body": "تهانينا! حصلت على 100 نقطة"
  },
  "data": {
    "type": "points_earned",
    "points": 100,
    "title_ar": "🎉 مرحباً بك!",
    "title_en": "🎉 Welcome!",
    "body_ar": "تهانينا! حصلت على 100 نقطة",
    "body_en": "Congratulations! You earned 100 points"
  }
}
```

### React/React Native Example

```javascript
// Handle FCM notification
messaging.onMessage((payload) => {
  const { data } = payload;
  
  // Get user's preferred language
  const userLang = i18n.language; // 'ar' or 'en'
  
  // Use appropriate language
  const title = userLang === 'ar' ? data.title_ar : data.title_en;
  const body = userLang === 'ar' ? data.body_ar : data.body_en;
  
  // Show notification
  showNotification(title, body, data);
});
```

### Flutter Example

```dart
FirebaseMessaging.onMessage.listen((RemoteMessage message) {
  final data = message.data;
  
  // Get user's preferred language
  String userLang = Localizations.localeOf(context).languageCode;
  
  // Use appropriate language
  String title = userLang == 'ar' ? data['title_ar'] : data['title_en'];
  String body = userLang == 'ar' ? data['body_ar'] : data['body_en'];
  
  // Show notification
  showNotification(title, body);
});
```

---

## 📊 All Notification Types - جميع أنواع الإشعارات

### 1. Registration Bonus - مكافأة التسجيل

```json
{
  "title_ar": "🎉 مرحباً بك!",
  "title_en": "🎉 Welcome!",
  "body_ar": "تهانينا! حصلت على 100 نقطة كمكافأة تسجيل",
  "body_en": "Congratulations! You earned 100 points as registration bonus"
}
```

### 2. Order Delivered - توصيل الطلب

```json
{
  "title_ar": "🎉 حصلت على نقاط!",
  "title_en": "🎉 You earned points!",
  "body_ar": "تم توصيل طلبك بنجاح! حصلت على 150 نقطة\n\n🎁 مكافأة أول طلب: 50 نقطة\n✅ إتمام الطلب: 100 نقطة",
  "body_en": "Your order delivered successfully! You earned 150 points\n\n🎁 First order bonus: 50 points\n✅ Order completion: 100 points"
}
```

### 3. Product Review - تقييم المنتج

```json
{
  "title_ar": "⭐ شكراً على تقييمك!",
  "title_en": "⭐ Thanks for your review!",
  "body_ar": "حصلت على 10 نقطة مقابل تقييم المنتج",
  "body_en": "You earned 10 points for reviewing the product"
}
```

### 4. Coupon Redemption - استبدال بكوبون

```json
{
  "title_ar": "🎟️ تم استبدال النقاط!",
  "title_en": "🎟️ Points redeemed!",
  "body_ar": "تم استبدال 500 نقطة بكوبون خصم بقيمة 50",
  "body_en": "Redeemed 500 points for a discount coupon worth 50"
}
```

### 5. Free Delivery - توصيل مجاني

```json
{
  "title_ar": "🚚 توصيل مجاني!",
  "title_en": "🚚 Free delivery!",
  "body_ar": "تم استبدال 300 نقطة بتوصيل مجاني لطلبك القادم",
  "body_en": "Redeemed 300 points for free delivery on your next order"
}
```

### 6. Gift Redemption - استبدال بهدية

```json
{
  "title_ar": "🎁 تم طلب الهدية!",
  "title_en": "🎁 Gift requested!",
  "body_ar": "تم استبدال 1000 نقطة بـ Gift Card $50. سيتم التواصل معك قريباً",
  "body_en": "Redeemed 1000 points for Gift Card $50. We'll contact you soon"
}
```

---

## 🎨 UI/UX Best Practices

### 1. Language Switching

```javascript
// Allow users to switch language
function switchLanguage(lang) {
  i18n.changeLanguage(lang);
  localStorage.setItem('userLang', lang);
  
  // Refresh notifications with new language
  refreshNotifications();
}
```

### 2. Notification Display

```javascript
function showNotification(title, body, data) {
  // Use appropriate RTL/LTR based on language
  const isRTL = i18n.language === 'ar';
  
  toast.success(body, {
    title: title,
    direction: isRTL ? 'rtl' : 'ltr',
    icon: getIconForType(data.type)
  });
}
```

### 3. Notification List

```jsx
function NotificationItem({ notification }) {
  const { t, i18n } = useTranslation();
  const lang = i18n.language;
  
  return (
    <div className={lang === 'ar' ? 'rtl' : 'ltr'}>
      <h3>{notification.data[`title_${lang}`]}</h3>
      <p>{notification.data[`body_${lang}`]}</p>
    </div>
  );
}
```

---

## ⚙️ Configuration

### Set Default Language

```php
// In config/app.php
'locale' => 'ar',
'fallback_locale' => 'en',
```

### Detect User Language

```php
// From request header
$lang = request()->header('Accept-Language', 'ar');
app()->setLocale($lang);

// From user preference (if you add lang column to users table)
if ($user->lang) {
    app()->setLocale($user->lang);
}
```

---

## 🔍 Testing

### Test Notification in Both Languages

```php
// Test Arabic
app()->setLocale('ar');
$service->send($user, $title, $body, $data);

// Test English
app()->setLocale('en');
$service->send($user, $title, $body, $data);
```

---

## ✅ Summary - الخلاصة

- ✅ All notifications support Arabic and English
- ✅ Automatic language detection
- ✅ Both versions sent to frontend
- ✅ Easy to add more languages
- ✅ Consistent format across all notification types

جميع الإشعارات تدعم العربية والإنجليزية تلقائياً! 🎉
