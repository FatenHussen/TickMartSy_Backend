# Subscription Benefits API - توثيق

## نظرة عامة
هذا الـ API يعرض المزايا المتاحة من الباقة النشطة للمستخدم.

---

## API Endpoint

### الحصول على مزايا الباقة
```http
GET /api/user/subscription/benefits
Authorization: Bearer {token}
```

---

## Response Examples

### 1. المستخدم لديه باقة نشطة ✅

```json
{
  "status": true,
  "message": "تم جلب مزايا الباقة بنجاح",
  "data": {
    "has_subscription": true,
    "subscription": {
      "id": 1,
      "package_name": "الباقة الذهبية",
      "start_date": "2026-03-01",
      "end_date": "2026-04-01",
      "days_remaining": 28
    },
    "benefits": {
      "discount": {
        "available": true,
        "percentage": 10,
        "remaining_uses": 15,
        "description": "خصم 10% على الطلبات"
      },
      "free_delivery": {
        "available": true,
        "remaining_count": 5,
        "description": "توصيل مجاني"
      },
      "points_bonus": {
        "value": 250,
        "description": "نقاط مكافأة إضافية عند كل طلب"
      }
    }
  }
}
```

### شرح الـ Response:

#### `has_subscription`
- **true**: المستخدم لديه باقة نشطة
- **false**: لا يوجد باقة نشطة

#### `subscription`
معلومات الاشتراك:
- `id`: معرف الاشتراك
- `package_name`: اسم الباقة
- `start_date`: تاريخ بداية الاشتراك
- `end_date`: تاريخ انتهاء الاشتراك
- `days_remaining`: الأيام المتبقية (سالب = منتهي)

#### `benefits.discount`
ميزة الخصم:
- `available`: هل الخصم متاح؟
  - `true`: يوجد طلبات متبقية والخصم > 0
  - `false`: لا يوجد طلبات متبقية أو الخصم = 0
- `percentage`: نسبة الخصم (مثال: 10 = 10%)
- `remaining_uses`: عدد الطلبات المتبقية
- `description`: وصف الميزة

#### `benefits.free_delivery`
ميزة التوصيل المجاني:
- `available`: هل التوصيل المجاني متاح؟
  - `true`: يوجد توصيلات مجانية متبقية
  - `false`: لا يوجد توصيلات مجانية متبقية
- `remaining_count`: عدد التوصيلات المجانية المتبقية
- `description`: وصف الميزة

#### `benefits.points_bonus`
نقاط المكافأة:
- `value`: عدد النقاط التي حصل عليها عند الاشتراك
- `description`: وصف الميزة

---

### 2. المستخدم ليس لديه باقة نشطة ❌

```json
{
  "status": true,
  "message": "لا يوجد اشتراك نشط",
  "data": {
    "has_subscription": false,
    "message": "لا يوجد اشتراك نشط"
  }
}
```

---

### 3. المستخدم غير مسجل دخول 🚫

```json
{
  "status": false,
  "message": "المستخدم غير مسجل دخول",
  "data": [],
  "code": 401
}
```

---

## حالات الاستخدام

### حالة 1: عرض المزايا في الصفحة الرئيسية
```javascript
// Frontend Code Example
async function checkSubscriptionBenefits() {
  const response = await fetch('/api/user/subscription/benefits', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  
  const data = await response.json();
  
  if (data.data.has_subscription) {
    // عرض badge أو banner بالمزايا المتاحة
    showBenefitsBadge(data.data.benefits);
  } else {
    // عرض دعوة للاشتراك
    showSubscribePrompt();
  }
}
```

### حالة 2: التحقق قبل الطلب
```javascript
// قبل إنشاء الطلب، تحقق من المزايا المتاحة
async function beforeCreateOrder() {
  const benefits = await getSubscriptionBenefits();
  
  if (benefits.discount.available) {
    // تطبيق الخصم تلقائياً
    applyDiscount(benefits.discount.percentage);
  }
  
  if (benefits.free_delivery.available) {
    // عرض خيار التوصيل المجاني
    showFreeDeliveryOption();
  }
}
```

### حالة 3: عرض العداد التنازلي
```javascript
// عرض الأيام المتبقية في الباقة
function showRemainingDays(daysRemaining) {
  if (daysRemaining > 0) {
    return `باقي ${daysRemaining} يوم على انتهاء الباقة`;
  } else if (daysRemaining === 0) {
    return 'الباقة تنتهي اليوم!';
  } else {
    return 'الباقة منتهية';
  }
}
```

---

## Logic Details

### متى يكون الخصم متاح؟
```php
$discount_available = 
    $subscription->hasRemainingOrders() && 
    $package->discount_percentage > 0;
```

**الشروط:**
1. يوجد طلبات متبقية (`remaining_orders > 0`)
2. نسبة الخصم أكبر من 0

### متى يكون التوصيل المجاني متاح؟
```php
$free_delivery_available = 
    $subscription->hasRemainingFreeDeliveries();
```

**الشرط:**
- يوجد توصيلات مجانية متبقية (`remaining_free_deliveries > 0`)

### متى تكون الباقة نشطة؟
```php
$is_active = 
    $subscription->status === 'active' && 
    $subscription->end_date >= now();
```

**الشروط:**
1. حالة الاشتراك = `active`
2. تاريخ الانتهاء لم يمضي بعد

---

## أمثلة عملية

### مثال 1: باقة جديدة (كل المزايا متاحة)
```json
{
  "has_subscription": true,
  "benefits": {
    "discount": {
      "available": true,
      "percentage": 20,
      "remaining_uses": 50
    },
    "free_delivery": {
      "available": true,
      "remaining_count": 50
    },
    "points_bonus": {
      "value": 500
    }
  }
}
```

### مثال 2: باقة مستخدمة جزئياً
```json
{
  "has_subscription": true,
  "benefits": {
    "discount": {
      "available": true,
      "percentage": 10,
      "remaining_uses": 5
    },
    "free_delivery": {
      "available": false,
      "remaining_count": 0
    },
    "points_bonus": {
      "value": 250
    }
  }
}
```

### مثال 3: باقة استُهلكت كل الطلبات
```json
{
  "has_subscription": true,
  "benefits": {
    "discount": {
      "available": false,
      "percentage": 10,
      "remaining_uses": 0
    },
    "free_delivery": {
      "available": false,
      "remaining_count": 0
    },
    "points_bonus": {
      "value": 250
    }
  }
}
```

---

## Integration with Order Creation

عند إنشاء طلب، يتم استخدام هذه المزايا تلقائياً:

```php
// في OrderService::create()

// 1. التحقق من الباقة النشطة
$subscription = $user->subscription;

if ($subscription && $subscription->isActive()) {
    
    // 2. تطبيق الخصم
    if ($subscription->hasRemainingOrders()) {
        $discount = ($total * $package->discount_percentage) / 100;
        $subscription->decrement('remaining_orders');
    }
    
    // 3. تطبيق التوصيل المجاني
    if ($subscription->hasRemainingFreeDeliveries()) {
        $deliveryPrice = 0;
        $subscription->decrement('remaining_free_deliveries');
    }
}
```

---

## Related APIs

### 1. الاشتراك في باقة
```http
POST /api/user/subscribe
Body: { "package_id": 1 }
```

### 2. عرض باقتي الحالية
```http
GET /api/user/my-subscription
```

### 3. تجديد الباقة
```http
POST /api/user/renew
Body: { "package_id": 1 }
```

### 4. عرض جميع الباقات المتاحة
```http
GET /api/user/packages
```

---

## Testing with Postman

### Request
```
GET {{base_url}}/api/user/subscription/benefits
Headers:
  Authorization: Bearer {{token}}
```

### Expected Response (Success)
```json
{
  "status": true,
  "message": "تم جلب مزايا الباقة بنجاح",
  "data": {
    "has_subscription": true,
    "subscription": { ... },
    "benefits": { ... }
  }
}
```

---

## Notes

1. **النقاط (points_bonus)**: تُمنح مرة واحدة عند الاشتراك، وليس مع كل طلب
2. **الخصم**: يُطبق تلقائياً على كل طلب حتى نفاد `remaining_orders`
3. **التوصيل المجاني**: يُطبق تلقائياً حتى نفاد `remaining_free_deliveries`
4. **انتهاء الباقة**: عند انتهاء التاريخ، تصبح الباقة غير نشطة حتى لو بقيت مزايا

---

## File Location
```
app/Http/Controllers/User/Subscription/SubscriptionController.php
Method: benefits()
```

---

تم إنشاء التوثيق بتاريخ: 2026-03-04
