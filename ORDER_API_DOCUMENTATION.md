# 📦 Order API Documentation - توثيق APIs الطلبات

## 📋 جدول المحتويات

1. [APIs الطلبات (Orders)](#orders-apis)
2. [APIs النقاط (Points)](#points-apis)
3. [APIs الاشتراكات (Subscriptions)](#subscriptions-apis)
4. [حالات الطلب (Order Status)](#order-status)
5. [أمثلة كاملة](#complete-examples)

---

## 🛒 Orders APIs

### Base URL
```
/api/user/orders
```

### Authentication
جميع الـ APIs تحتاج Authentication Token في الـ Header:
```
Authorization: Bearer {token}
```

---

## 1️⃣ إنشاء طلب جديد (Create Order)

### Endpoint
```http
POST /api/user/orders
```

### Request Body - الحقول الأساسية

```json
{
  "address_id": 1,
  "payment_method_id": 2,
  "is_instant_delivery": true,
  "items": [
    {
      "shop_product_variant_id": 10,
      "quantity": 2
    },
    {
      "shop_product_variant_id": 15,
      "quantity": 3
    }
  ]
}
```

### Request Body - الحقول الاختيارية

#### 1. نوع السلة (Cart Type)
```json
{
  "cart_type": "default",  // Options: default, recipe, admin_cart, schedule_admin_cart
  "recipe_id": 5,  // مطلوب إذا cart_type = recipe
  "admin_basket_id": 10,  // مطلوب إذا cart_type = admin_cart
  "admin_schedule_basket_id": 8  // مطلوب إذا cart_type = schedule_admin_cart
}
```

#### 2. الكوبونات والخصومات
```json
{
  "coupon": "SUMMER2024"  // كود الكوبون
}
```

#### 3. التسويق بالعمولة
```json
{
  "affiliate_id": "AFF123456"  // معرف المسوق
}
```

#### 4. استخدام النقاط (Point Exchanges)
```json
{
  "point_coupon_exchange_id": 25,  // استبدال نقاط للحصول على خصم
  "point_free_delivery_exchange_id": 30  // استبدال نقاط للتوصيل المجاني
}
```

#### 5. استخدام مزايا الباقة (Subscription Benefits)
```json
{
  "use_subscription_discount": true,  // استخدام خصم الباقة
  "use_subscription_free_delivery": true  // استخدام توصيل مجاني من الباقة
}
```

### ⚠️ قواعد مهمة (Validation Rules)

1. **مصدر خصم واحد فقط**: يمكن استخدام واحد فقط من:
   - `coupon` (كوبون عادي)
   - `point_coupon_exchange_id` (خصم من النقاط)
   - `use_subscription_discount` (خصم من الباقة)

2. **مصدر توصيل مجاني واحد فقط**: يمكن استخدام واحد فقط من:
   - `point_free_delivery_exchange_id` (توصيل مجاني من النقاط)
   - `use_subscription_free_delivery` (توصيل مجاني من الباقة)

### Response - نجاح الطلب

```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "id": 150,
    "order_code": "ORD-260305-ABCD150",
    "status": "pending",
    "cart_type": "default",
    "is_instant_delivery": true,
    "delivery_price": 5.00,
    "subtotal": 45.50,
    "total": 40.95,
    "total_with_delivery": 45.95,
    "total_quantity": 5,
    "basket_discount": 0,
    "coupon_discount": 4.55,
    "coupon_discount_from_points": 0,
    "free_delivery_from_points": false,
    "subscription_discount": 0,
    "subscription_free_delivery": false,
    "used_coupon_exchange_id": null,
    "used_free_delivery_exchange_id": null,
    "created_at": "2026-03-05 14:30:00",
    "timestamps": {
      "pending_at": "2026-03-05 14:30:00",
      "preparing_at": null,
      "out_delivery_at": null,
      "delivered_at": null
    },
    "user": { ... },
    "driver": null,
    "user_address": { ... },
    "payment_method": {
      "id": 2,
      "name": "Cash on Delivery"
    },
    "items": [
      {
        "id": 301,
        "shop_product_variant_id": 10,
        "quantity": 2,
        "price": 15.00,
        "total": 30.00,
        "product": { ... }
      }
    ]
  }
}
```

---

## 2️⃣ معاينة الطلب قبل الإنشاء (Preview Order)

### Endpoint
```http
POST /api/user/orders/preview
```

### الغرض
يتيح للمستخدم معاينة تفاصيل الطلب (السعر النهائي، الخصومات، التوصيل) قبل تأكيد الطلب.

### Request Body
نفس الـ Body المستخدم في إنشاء الطلب.

### Response
```json
{
  "success": true,
  "data": {
    "subtotal": 45.50,
    "delivery_price": 5.00,
    "basket_discount": 0,
    "coupon_discount": 4.55,
    "subscription_discount": 0,
    "total": 40.95,
    "total_with_delivery": 45.95,
    "applied_discounts": {
      "coupon": "SUMMER2024",
      "coupon_percentage": 10,
      "subscription_used": false,
      "points_used": false
    }
  }
}
```

---

## 3️⃣ معاينة الكوبون (Coupon Preview)

### Endpoint
```http
POST /api/user/orders/coupon-preview
```

### Request Body
```json
{
  "coupon": "SUMMER2024",
  "subtotal": 100.00
}
```

### Response
```json
{
  "success": true,
  "data": {
    "coupon_valid": true,
    "discount_amount": 10.00,
    "discount_percentage": 10,
    "final_total": 90.00
  }
}
```

---

## 4️⃣ عرض جميع الطلبات (List Orders)

### Endpoint
```http
GET /api/user/orders
```

### Query Parameters
```
?status=pending          // تصفية حسب الحالة
&page=1                  // رقم الصفحة
&per_page=15             // عدد العناصر في الصفحة
```

### حالات الطلب المتاحة للتصفية
- `pending` - قيد الانتظار
- `preparing` - قيد التحضير
- `out_delivery` - خرج للتوصيل
- `delivered` - تم التوصيل
- `cancelled` - ملغي

### Response
```json
{
  "success": true,
  "data": [
    {
      "id": 150,
      "order_code": "ORD-260305-ABCD150",
      "status": "pending",
      "total": 45.95,
      "created_at": "2026-03-05 14:30:00",
      ...
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 25
  }
}
```

---

## 5️⃣ عرض تفاصيل طلب واحد (Show Order)

### Endpoint
```http
GET /api/user/orders/{id}
```

### Response
نفس الـ Response الخاص بإنشاء الطلب مع جميع التفاصيل.

---

## 6️⃣ الطلب النشط الحالي (Active Order)

### Endpoint
```http
GET /api/user/orders/active
```

### الغرض
يعرض الطلب النشط الحالي للمستخدم (الطلب الذي لم يتم توصيله أو إلغاؤه بعد).

### Response
```json
{
  "success": true,
  "data": {
    "id": 150,
    "order_code": "ORD-260305-ABCD150",
    "status": "preparing",
    ...
  }
}
```

---

## 7️⃣ إلغاء الطلب (Cancel Order)

### Endpoint
```http
POST /api/user/orders/{orderId}/cancel
```

### Response
```json
{
  "success": true,
  "message": "Order cancelled successfully"
}
```

---

## 📊 Order Status - حالات الطلب

| Status | Arabic | English | Description |
|--------|--------|---------|-------------|
| `pending` | قيد الانتظار | Pending | الطلب تم إنشاؤه وبانتظار المعالجة |
| `preparing` | قيد التحضير | Preparing | المتجر يحضر الطلب |
| `out_delivery` | خرج للتوصيل | Out for delivery | السائق في الطريق للتوصيل |
| `delivered` | تم التوصيل | Delivered | تم توصيل الطلب بنجاح |
| `cancelled` | ملغي | Cancelled | تم إلغاء الطلب |

---

## 💰 Points APIs - APIs النقاط

### Base URL
```
/api/user/points
```

---

## 1️⃣ ملخص النقاط (Points Summary)

### Endpoint
```http
GET /api/user/points/summary
```

### Response
```json
{
  "success": true,
  "data": {
    "total_points": 1250,
    "pending_points": 150,
    "earned_points": 1400,
    "redeemed_points": 300,
    "expired_points": 0
  }
}
```

---

## 2️⃣ سجل المعاملات (Transactions History)

### Endpoint
```http
GET /api/user/points/transactions
```

### Query Parameters
```
?status=earned           // تصفية حسب الحالة
&page=1
&per_page=15
```

### Response
```json
{
  "success": true,
  "data": [
    {
      "id": 50,
      "points": 100,
      "status": "earned",
      "reason": "Order completed",
      "reference_type": "Order",
      "reference_id": 150,
      "expires_at": "2027-03-05",
      "created_at": "2026-03-05 14:30:00"
    }
  ]
}
```

---

## 3️⃣ إحصائيات النقاط (Points Statistics)

### Endpoint
```http
GET /api/user/points/statistics
```

### Response
```json
{
  "success": true,
  "data": {
    "transactions_count": {
      "pending": 5,
      "earned": 20,
      "expired": 2,
      "redeemed": 8
    },
    "status_types": {
      "pending": "Pending transactions (not yet confirmed)",
      "earned": "Earned points (added to balance)",
      "expired": "Expired points (removed from balance)",
      "redeemed": "Redeemed points (spent on rewards)"
    }
  }
}
```

---

## 🎁 Point Exchange APIs - استبدال النقاط

### Base URL
```
/api/user/points/exchange
```

---

## 1️⃣ الخيارات المتاحة للاستبدال (Exchange Options)

### Endpoint
```http
GET /api/user/points/exchange/options
```

### الغرض
يعرض جميع خيارات الاستبدال المتاحة (كوبونات، توصيل مجاني، هدايا) مع النقاط المطلوبة.

### Response
```json
{
  "success": true,
  "data": {
    "user_points": 1250,
    "coupons": [
      {
        "id": 5,
        "code": "POINTS10",
        "discount_percentage": 10,
        "points_required": 500,
        "can_exchange": true
      }
    ],
    "free_delivery": {
      "points_required": 300,
      "can_exchange": true,
      "zones": ["Zone A", "Zone B"]
    },
    "gifts": [
      {
        "id": 10,
        "name": "Gift Card $50",
        "points_required": 1000,
        "available_quantity": 5,
        "can_exchange": true,
        "image": "https://..."
      }
    ]
  }
}
```

---
