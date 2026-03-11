# مثال عملي كامل | Complete Practical Example

## 🎯 السيناريو | Scenario

مستخدم يشتري منتج بـ 100 دولار، يكسب 100 نقطة، ثم يستبدلها بكوبون خصم، وبعدها يتلقى جائزة من الإدارة.

---

## 📝 الخطوة 1: المستخدم يشتري منتج | User Purchases Product

### Backend - الكود

```php
<?php
// app/Services/OrderService.php

namespace App\Services\User;

use App\Services\PointService;

class OrderService
{
    public function __construct(private PointService $pointService)
    {}

    public function createOrder($userId, $items, $totalAmount)
    {
        // إنشاء الطلب
        $order = Order::create([
            'user_id' => $userId,
            'total_amount' => $totalAmount,
            'status' => 'pending'
        ]);

        // منح النقاط (1 نقطة لكل 1 دولار)
        $points = (int) $totalAmount;
        
        $this->pointService->awardPoints(
            userId: $userId,
            points: $points,
            source: 'purchase',
            referenceType: 'order',
            referenceId: $order->id,
            reason: "شراء منتج - الطلب #{$order->id}"
        );

        // إرسال إشعار
        $user = User::find($userId);
        $user->notify(new PointsEarnedNotification(
            points: $points,
            source: 'purchase'
        ));

        return $order;
    }
}
```

### Frontend - الكود

```javascript
// JavaScript - عند إكمال الشراء
async function completeOrder(items) {
  try {
    const response = await fetch('/api/user/orders', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        items: items,
        total_amount: calculateTotal(items)
      })
    });

    const data = await response.json();
    
    if (data.success) {
      // عرض رسالة النجاح
      showNotification('تم الشراء بنجاح! تم إضافة نقاط إلى حسابك');
      
      // تحديث رصيد النقاط
      updatePointsBalance();
      
      // الانتقال لصفحة الشكر
      navigateTo('/order-success');
    }
  } catch (error) {
    console.error('Error:', error);
  }
}
```

### النتيجة | Result

```
✅ تم إنشاء الطلب
✅ تم منح 100 نقطة
✅ تم إرسال إشعار
✅ تم تحديث المحفظة
```

---

## 💰 الخطوة 2: المستخدم يستبدل النقاط | User Redeems Points

### Backend - الكود

```php
<?php
// app/Http/Controllers/User/Point/ExchangeController.php

public function exchangeForCoupon(ExchangeForCouponRequest $request)
{
    $userId = auth('user')->id();
    $points = $request->points; // 100 نقطة

    // الخطوة 1: التحقق من الرصيد
    $wallet = PointWallet::where('user_id', $userId)->first();
    
    if (!$wallet || $wallet->balance < $points) {
        return $this->sendError(
            message: 'رصيد النقاط غير كافي',
            code: 400
        );
    }

    // الخطوة 2: خصم النقاط
    $transaction = $this->service->redeemPoints(
        $userId,
        $points,
        'استبدال بكوبون'
    );

    if (!$transaction) {
        return $this->sendError(
            message: 'فشل الاستبدال',
            code: 400
        );
    }

    // الخطوة 3: إنشاء الكوبون
    $discountAmount = $points / 10; // 100 نقطة = 10 دولار
    
    $coupon = Coupon::create([
        'code' => 'COUPON-' . strtoupper(uniqid()),
        'discount_amount' => $discountAmount,
        'discount_type' => 'fixed',
        'user_id' => $userId,
        'expires_at' => now()->addMonths(3),
        'is_active' => true,
    ]);

    // الخطوة 4: تسجيل الاستبدال
    PointExchange::create([
        'user_id' => $userId,
        'transaction_id' => $transaction->id,
        'exchange_type' => 'coupon',
        'exchange_data' => [
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
            'discount_amount' => $discountAmount,
            'expires_at' => $coupon->expires_at->toDateString(),
        ],
        'status' => 'completed',
    ]);

    // الخطوة 5: إرسال إشعار
    $user = User::find($userId);
    $user->notify(new PointsRedeemedNotification(
        points: $points,
        couponCode: $coupon->code,
        discountAmount: $discountAmount
    ));

    return $this->sendResponse(
        message: 'تم الاستبدال بنجاح',
        data: [
            'coupon_code' => $coupon->code,
            'discount_amount' => $discountAmount,
            'expires_at' => $coupon->expires_at->toDateString(),
            'new_balance' => $wallet->balance - $points,
        ]
    );
}
```

### Frontend - الكود

```jsx
import React, { useState } from 'react';

export function ExchangePointsFlow() {
  const [step, setStep] = useState('select'); // select, confirm, success
  const [points, setPoints] = useState('');
  const [coupon, setCoupon] = useState(null);
  const [loading, setLoading] = useState(false);

  const handleExchange = async () => {
    try {
      setLoading(true);

      const response = await fetch('/api/user/points/exchange/coupon', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ points: parseInt(points) })
      });

      const data = await response.json();

      if (data.success) {
        setCoupon(data.data);
        setStep('success');
        
        // تحديث النقاط
        updatePointsBalance();
      } else {
        alert(`خطأ: ${data.message}`);
      }
    } catch (error) {
      console.error('Error:', error);
      alert('حدث خطأ');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="exchange-flow">
      {step === 'select' && (
        <div className="step-select">
          <h2>استبدال النقاط</h2>
          
          <div className="form-group">
            <label>عدد النقاط</label>
            <input
              type="number"
              value={points}
              onChange={(e) => setPoints(e.target.value)}
              placeholder="أدخل عدد النقاط (الحد الأدنى 100)"
              min="100"
            />
            <small>100 نقطة = 10 دولار</small>
          </div>

          <button
            onClick={handleExchange}
            disabled={!points || parseInt(points) < 100 || loading}
            className="btn-primary"
          >
            {loading ? 'جاري المعالجة...' : 'استبدال'}
          </button>
        </div>
      )}

      {step === 'success' && coupon && (
        <div className="step-success">
          <div className="success-icon">✅</div>
          <h2>تم الاستبدال بنجاح!</h2>
          
          <div className="coupon-card">
            <div className="coupon-code">{coupon.coupon_code}</div>
            <div className="coupon-value">
              خصم {coupon.discount_amount} دولار
            </div>
            <div className="coupon-expiry">
              ينتهي في: {coupon.expires_at}
            </div>
          </div>

          <p className="info">
            يمكنك استخدام هذا الكوبون في طلبك القادم
          </p>

          <button
            onClick={() => navigateTo('/products')}
            className="btn-primary"
          >
            متابعة التسوق
          </button>
        </div>
      )}
    </div>
  );
}
```

### النتيجة | Result

```
✅ تم التحقق من الرصيد (100 نقطة متاحة)
✅ تم خصم 100 نقطة من المحفظة
✅ تم إنشاء كوبون بقيمة 10 دولار
✅ تم تسجيل الاستبدال
✅ تم إرسال إشعار
✅ الرصيد الجديد: 0 نقطة
```

---

## 🎁 الخطوة 3: الإدارة ترسل جائزة | Admin Sends Gift

### Backend - الكود (Admin Panel)

```php
<?php
// app/Http/Controllers/Admin/GiftController.php

public function sendGiftToUser(Request $request)
{
    $validated = $request->validate([
        'user_id' => 'required|exists:users,id',
        'gift_id' => 'required|exists:gifts,id',
        'message' => 'nullable|string',
    ]);

    // إنشاء جائزة للمستخدم
    $userGift = UserGift::create([
        'gift_id' => $validated['gift_id'],
        'user_id' => $validated['user_id'],
        'status' => 'pending',
        'admin_notes' => $validated['message'] ?? null,
    ]);

    // إرسال إشعار للمستخدم
    $user = User::find($validated['user_id']);
    $gift = Gift::find($validated['gift_id']);
    
    $user->notify(new GiftReceivedNotification($userGift, $gift));

    // تسجيل النشاط
    activity()
        ->causedBy(auth('admin')->user())
        ->performedOn($userGift)
        ->log("تم إرسال جائزة '{$gift->name}' للمستخدم {$user->name}");

    return response()->json([
        'success' => true,
        'message' => 'تم إرسال الجائزة بنجاح',
        'data' => $userGift
    ]);
}
```

### Frontend - الكود (User App)

```javascript
// عند استقبال الإشعار
function handleGiftNotification(notification) {
  // عرض إشعار
  showNotification({
    title: 'جائزة جديدة!',
    message: `تلقيت جائزة: ${notification.data.gift_name}`,
    icon: 'gift',
    action: () => navigateTo('/gifts')
  });

  // تحديث قائمة الجوائز
  refreshGiftsList();
}
```

### النتيجة | Result

```
✅ تم إنشاء جائزة جديدة
✅ تم إرسال إشعار للمستخدم
✅ الحالة: معلقة (Pending)
```

---

## ✅ الخطوة 4: المستخدم يقبل الجائزة | User Accepts Gift

### Backend - الكود

```php
<?php
// app/Http/Controllers/User/UserGift/UserGiftController.php

public function accept($id)
{
    $userId = auth('user')->id();
    
    $gift = UserGift::where('id', $id)
        ->where('user_id', $userId)
        ->first();

    if (!$gift) {
        return $this->sendError(
            message: 'الجائزة غير موجودة',
            code: 404
        );
    }

    if (!$gift->isPending()) {
        return $this->sendError(
            message: 'لا يمكن قبول هذه الجائزة',
            code: 400
        );
    }

    // تحديث الحالة
    $gift->accept();

    // إرسال إشعار للإدارة
    Admin::first()->notify(new GiftAcceptedNotification($gift));

    return $this->sendResponse(
        message: 'تم قبول الجائزة بنجاح'
    );
}
```

### Frontend - الكود

```jsx
export function GiftCard({ gift, onAccept }) {
  const [loading, setLoading] = useState(false);

  const handleAccept = async () => {
    try {
      setLoading(true);

      const response = await fetch(`/api/user/user-gifts/${gift.id}/accept`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      });

      const data = await response.json();

      if (data.success) {
        showNotification('تم قبول الجائزة! يرجى تحديد عنوان التسليم');
        onAccept(gift.id);
      }
    } catch (error) {
      console.error('Error:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="gift-card">
      <img src={gift.gift.image} alt={gift.gift.name} />
      <h3>{gift.gift.name}</h3>
      <p>{gift.gift.description}</p>
      
      <button
        onClick={handleAccept}
        disabled={loading}
        className="btn-primary"
      >
        {loading ? 'جاري المعالجة...' : 'قبول الجائزة'}
      </button>
    </div>
  );
}
```

### النتيجة | Result

```
✅ تم قبول الجائزة
✅ تم تحديث الحالة إلى "مقبولة"
✅ تم إرسال إشعار للإدارة
```

---

## 📍 الخطوة 5: المستخدم يحدد العنوان | User Selects Address

### Backend - الكود

```php
<?php
public function updateAddress($id, UpdateAddressRequest $request)
{
    $userId = auth('user')->id();
    
    $gift = UserGift::where('id', $id)
        ->where('user_id', $userId)
        ->first();

    if (!$gift || !$gift->isAddressPending()) {
        return $this->sendError(
            message: 'لا يمكن تحديث العنوان',
            code: 400
        );
    }

    // التحقق من أن العنوان ينتمي للمستخدم
    $address = UserAddress::where('id', $request->address_id)
        ->where('user_id', $userId)
        ->first();

    if (!$address) {
        return $this->sendError(
            message: 'العنوان غير موجود',
            code: 404
        );
    }

    // تحديث الجائزة
    $gift->update([
        'address_id' => $request->address_id,
        'status' => 'address_pending'
    ]);

    // إرسال إشعار للإدارة
    Admin::first()->notify(new GiftAddressSelectedNotification($gift, $address));

    return $this->sendResponse(
        message: 'تم تحديد العنوان بنجاح',
        data: [
            'gift_id' => $gift->id,
            'address' => [
                'full_name' => $address->full_name,
                'phone' => $address->phone,
                'city' => $address->city,
                'area' => $address->area,
                'street' => $address->street,
            ]
        ]
    );
}
```

### Frontend - الكود

```jsx
export function AddressSelectionFlow({ giftId, onComplete }) {
  const [addresses, setAddresses] = useState([]);
  const [selectedAddress, setSelectedAddress] = useState(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    fetchAddresses();
  }, []);

  const fetchAddresses = async () => {
    const response = await fetch('/api/user/addresses', {
      headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
    });
    const data = await response.json();
    setAddresses(data.data);
  };

  const handleSubmit = async () => {
    try {
      setLoading(true);

      const response = await fetch(`/api/user/user-gifts/${giftId}/address`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ address_id: selectedAddress })
      });

      const data = await response.json();

      if (data.success) {
        showNotification('تم تحديد العنوان! سيتم الشحن قريباً');
        onComplete();
      }
    } catch (error) {
      console.error('Error:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="address-selection">
      <h2>تحديد عنوان التسليم</h2>

      <div className="address-list">
        {addresses.map(addr => (
          <label key={addr.id} className="address-option">
            <input
              type="radio"
              name="address"
              value={addr.id}
              onChange={(e) => setSelectedAddress(parseInt(e.target.value))}
            />
            <div className="address-info">
              <p className="name">{addr.full_name}</p>
              <p className="phone">{addr.phone}</p>
              <p className="location">
                {addr.street}, {addr.area}, {addr.city}
              </p>
            </div>
          </label>
        ))}
      </div>

      <button
        onClick={handleSubmit}
        disabled={!selectedAddress || loading}
        className="btn-primary"
      >
        {loading ? 'جاري المعالجة...' : 'تأكيد العنوان'}
      </button>
    </div>
  );
}
```

### النتيجة | Result

```
✅ تم تحديد العنوان
✅ تم تحديث الحالة إلى "في انتظار الشحن"
✅ تم إرسال إشعار للإدارة
✅ جاهز للشحن
```

---

## 📊 الملخص الكامل | Complete Summary

### المستخدم

```
الحالة الأولية:
- الرصيد: 0 نقطة
- الجوائز: 0

بعد الشراء:
- الرصيد: 100 نقطة
- الجوائز: 0

بعد الاستبدال:
- الرصيد: 0 نقطة
- الجوائز: 0
- الكوبونات: 1 (قيمة 10 دولار)

بعد استقبال الجائزة:
- الرصيد: 0 نقطة
- الجوائز: 1 (معلقة)
- الكوبونات: 1

بعد قبول الجائزة وتحديد العنوان:
- الرصيد: 0 نقطة
- الجوائز: 1 (في انتظار الشحن)
- الكوبونات: 1
```

### قاعدة البيانات

```
point_wallets:
- user_id: 1
- balance: 0
- last_earned_at: 2024-03-11 10:00:00

point_transactions:
1. type: purchase, points: 100, status: earned
2. type: redemption, points: -100, status: redeemed

point_exchanges:
1. exchange_type: coupon, status: completed

user_gifts:
1. gift_id: 123, status: address_pending, address_id: 456
```

---

## 🔔 الإشعارات المرسلة | Notifications Sent

```
1. ✅ PointsEarnedNotification
   - "تم إضافة 100 نقطة إلى حسابك"

2. ✅ PointsRedeemedNotification
   - "تم استبدال 100 نقطة بكوبون COUPON-ABC123"

3. ✅ GiftReceivedNotification
   - "تلقيت جائزة جديدة: منتج مميز"

4. ✅ GiftAcceptedNotification (للإدارة)
   - "المستخدم قبل الجائزة"

5. ✅ GiftAddressSelectedNotification (للإدارة)
   - "تم تحديد عنوان التسليم"
```

---

## 🧪 اختبار الـ API | API Testing

```bash
# 1. الحصول على ملخص النقاط
curl -X GET http://localhost:8000/api/user/points/summary \
  -H "Authorization: Bearer TOKEN"

# الرد:
# {
#   "success": true,
#   "data": {
#     "current_balance": 0,
#     "total_earned": 100,
#     "total_redeemed": 100,
#     "total_expired": 0,
#     "pending_points": 0
#   }
# }

# 2. الحصول على الجوائز
curl -X GET http://localhost:8000/api/user/user-gifts \
  -H "Authorization: Bearer TOKEN"

# الرد:
# {
#   "success": true,
#   "data": {
#     "gifts": [
#       {
#         "id": 1,
#         "gift": { "id": 123, "name": "منتج مميز" },
#         "status": "address_pending",
#         "address": { "id": 456, "full_name": "أحمد محمد" }
#       }
#     ]
#   }
# }
```

---

