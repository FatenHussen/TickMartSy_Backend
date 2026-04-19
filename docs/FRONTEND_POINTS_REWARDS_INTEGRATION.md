# دليل تكامل الفرونت إند - Frontend Integration Guide

## 🎯 نقاط النهاية الكاملة | Complete API Endpoints

---

## 📊 النقاط | Points Endpoints

### 1. الحصول على ملخص النقاط | Get Points Summary

```
GET /api/user/points/summary
Authorization: Bearer {token}
```

**الرد الناجح | Success Response (200):**
```json
{
  "success": true,
  "message": "تم جلب ملخص النقاط بنجاح",
  "data": {
    "current_balance": 1500,
    "total_earned": 2000,
    "total_redeemed": 500,
    "total_expired": 0,
    "pending_points": 100,
    "last_earned_at": "2024-03-11T10:30:00Z"
  }
}
```

---

### 2. الحصول على سجل المعاملات | Get Transactions History

```
GET /api/user/points/transactions?page=1&limit=20&status=all&sort=created_at
Authorization: Bearer {token}
```

**المعاملات | Query Parameters:**
- `page`: رقم الصفحة (افتراضي: 1)
- `limit`: عدد النتائج (افتراضي: 20)
- `status`: pending, earned, expired, redeemed, all
- `sort`: created_at, points, expires_at

**الرد الناجح | Success Response (200):**
```json
{
  "success": true,
  "data": {
    "transactions": [
      {
        "id": 1,
        "type": "purchase",
        "description": "شراء منتج | Product Purchase",
        "points": 150,
        "date": "2024-03-11T10:30:00Z",
        "status": "earned",
        "status_label": "مكتسبة | Earned",
        "reference": 12345,
        "expiry_date": "2025-03-11",
        "icon": "shopping-bag",
        "is_expired": false
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 5,
      "total_items": 100,
      "per_page": 20
    }
  }
}
```

---


## 🎁 خيارات الاستبدال | Exchange Options

### 1. الحصول على خيارات الاستبدال | Get Exchange Options

```
GET /api/user/points/exchange/options
Authorization: Bearer {token}
```

**الرد الناجح | Success Response (200):**
```json
{
  "success": true,
  "data": {
    "options": [
      {
        "id": "coupon",
        "name": "كوبون خصم | Coupon",
        "description": "احصل على كوبون خصم",
        "icon": "ticket",
        "min_points": 100
      },
      {
        "id": "free_delivery",
        "name": "توصيل مجاني | Free Delivery",
        "description": "احصل على توصيل مجاني",
        "icon": "truck",
        "min_points": 200
      },
      {
        "id": "gift",
        "name": "هدية | Gift",
        "description": "استبدل بهدية",
        "icon": "gift",
        "min_points": 500
      }
    ]
  }
}
```

---

### 2. استبدال بكوبون | Exchange for Coupon

```
POST /api/user/points/exchange/coupon
Authorization: Bearer {token}
Content-Type: application/json

{
  "points": 100
}
```

**الرد الناجح | Success Response (200):**
```json
{
  "success": true,
  "message": "تم الاستبدال بنجاح",
  "data": {
    "discount_amount": 10,
    "expires_at": "2024-06-11T00:00:00Z"
  }
}
```

---

### 3. استبدال بهدية | Exchange for Gift

```
POST /api/user/points/exchange/gift
Authorization: Bearer {token}
Content-Type: application/json

{
  "points": 500,
  "gift_id": 123
}
```

**الرد الناجح | Success Response (200):**
```json
{
  "success": true,
  "message": "تم الاستبدال بنجاح",
  "data": {
    "gift_id": 456,
    "gift_name": "منتج مميز",
    "status": "pending"
  }
}
```

---

### 4. سجل الاستبدالات | Exchange History

```
GET /api/user/points/exchange/history?page=1&limit=20
Authorization: Bearer {token}
```

**الرد الناجح | Success Response (200):**
```json
{
  "success": true,
  "data": {
    "exchanges": [
      {
        "id": 1,
        "exchange_type": "coupon",
        "status": "completed",
        "created_at": "2024-03-11T10:00:00Z",
        "exchange_data": {
          "coupon_id": 123,
          "discount_amount": 10
        }
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 1,
      "total_items": 1,
      "per_page": 20
    }
  }
}
```

---

## 🎀 الجوائز | Rewards Endpoints


### 5. تحديد عنوان التسليم | Select Delivery Address

```
PUT /api/user/user-gifts/{id}/address
Authorization: Bearer {token}
Content-Type: application/json

{
  "address_id": 456
}
```

**الرد الناجح | Success Response (200):**
```json
{
  "success": true,
  "message": "تم تحديث العنوان بنجاح",
  "data": {
    "id": 1,
    "status": "address_pending",
    "address": {
      "id": 456,
      "full_name": "أحمد محمد",
      "phone": "+966501234567",
      "city": "الرياض",
      "area": "النخيل",
      "street": "شارع الملك فهد"
    }
  }
}
```

---

