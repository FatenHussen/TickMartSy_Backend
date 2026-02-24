# Points & Exchange System Documentation

## Table of Contents
1. [System Overview](#system-overview)
2. [Points APIs](#points-apis)
3. [Exchange APIs](#exchange-apis)
4. [Order Integration](#order-integration)
5. [Complete Flow Examples](#complete-flow-examples)

---

## System Overview

The points system allows users to:
- Earn points through various activities (registration, orders, reviews)
- Redeem points for rewards (coupons, free delivery, gifts)
- Use redeemed rewards when creating orders

### Key Concepts

- **Point Wallet**: Each user has a wallet that stores their point balance
- **Point Transactions**: Record of all point earnings and redemptions
- **Point Rules**: Define how many points are awarded for each activity
- **Point Exchanges**: Record of rewards redeemed by users
- **Point Events**: Track one-time events (like first order)

---

## Points APIs

### 1. Get Points Summary

Get user's current point balance and statistics.

**Endpoint:** `GET /api/user/points/summary`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response:**
```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": {
    "balance": 1500,
    "total_earned": 2000,
    "total_redeemed": 500,
    "pending_points": 0,
    "wallet_created_at": "2026-01-15 10:30:00"
  }
}
```

---

### 2. Get Transaction History

Get detailed history of all point transactions.

**Endpoint:** `GET /api/user/points/transactions`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**
- `type` (optional): Filter by type (`earned`, `redeemed`)
- `per_page` (optional): Items per page (default: 15)
- `page` (optional): Page number

**Example Request:**
```
GET /api/user/points/transactions?type=earned&per_page=10&page=1
```

**Response:**
```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": {
    "transactions": [
      {
        "id": 1,
        "type": "earned",
        "amount": 100,
        "description": "نقاط أول طلب",
        "reference_type": "order",
        "reference_id": 5,
        "status": "completed",
        "created_at": "2026-02-20 14:30:00"
      },
      {
        "id": 2,
        "type": "redeemed",
        "amount": -50,
        "description": "استبدال بكوبون خصم",
        "reference_type": "coupon_exchange",
        "reference_id": null,
        "status": "completed",
        "created_at": "2026-02-21 10:15:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 3,
      "per_page": 10,
      "total": 25
    }
  }
}
```

---

## Exchange APIs

### 1. Get Available Exchange Options

Check what rewards the user can redeem with their current points.

**Endpoint:** `GET /api/user/points/exchange/options`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response:**
```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": {
    "available": true,
    "current_balance": 1500,
    "options": {
      "coupon": {
        "enabled": true,
        "min_points": 50,
        "max_points": 1500,
        "discount_rate": 0.01,
        "description": "استبدال النقاط بكوبون خصم"
      },
      "free_delivery": {
        "enabled": true,
        "points_cost": 100,
        "description": "توصيل مجاني للطلب القادم"
      },
      "gifts": {
        "enabled": true,
        "available_gifts": [
          {
            "id": 1,
            "name": "سماعات بلوتوث",
            "description": "سماعات لاسلكية عالية الجودة",
            "image": "https://example.com/headphones.jpg",
            "points_required": 500,
            "stock_quantity": 10,
            "category": "electronics"
          }
        ]
      }
    }
  }
}
```

**Note:** If user doesn't have minimum points:
```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": {
    "available": false,
    "message": "تحتاج إلى 50 نقطة على الأقل للاستبدال",
    "current_balance": 30,
    "min_required": 50
  }
}
```

---

### 2. Exchange Points for Coupon

Redeem points to get a discount coupon.

**Endpoint:** `POST /api/user/points/exchange/coupon`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

**Request Body:**
```json
{
  "points": 100
}
```

**Validation Rules:**
- `points`: required, integer, min: 50, max: 5000

**Response:**
```json
{
  "status": true,
  "message": "تم استبدال النقاط بنجاح",
  "data": {
    "exchange_id": 5,
    "points_used": 100,
    "discount_amount": 1.00,
    "expires_at": "2026-03-24",
    "new_balance": 1400
  }
}
```

**How Discount is Calculated:**
- Discount Amount = Points × Discount Rate
- Example: 100 points × 0.01 = $1.00 discount

---

### 3. Exchange Points for Free Delivery

Redeem points to get free delivery on next order.

**Endpoint:** `POST /api/user/points/exchange/free-delivery`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

**Request Body:**
```json
{}
```

**Response:**
```json
{
  "status": true,
  "message": "تم استبدال النقاط بنجاح",
  "data": {
    "exchange_id": 6,
    "points_used": 100,
    "expires_at": "2026-03-24",
    "usage_count": 1,
    "new_balance": 1300
  }
}
```

---

### 4. Exchange Points for Gift

Redeem points to get a physical gift.

**Endpoint:** `POST /api/user/points/exchange/gift`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

**Request Body:**
```json
{
  "gift_id": 1,
  "delivery_address": {
    "street": "شارع الملك فهد",
    "city": "الرياض",
    "postal_code": "12345"
  }
}
```

**Validation Rules:**
- `gift_id`: required, integer, exists in gifts table
- `delivery_address`: optional, object

**Response:**
```json
{
  "status": true,
  "message": "تم استبدال النقاط بنجاح",
  "data": {
    "exchange_id": 7,
    "gift_name": "سماعات بلوتوث",
    "points_used": 500,
    "status": "pending",
    "delivery_address": {
      "street": "شارع الملك فهد",
      "city": "الرياض",
      "postal_code": "12345"
    },
    "new_balance": 800
  }
}
```

**Note:** Gift exchanges require admin approval for delivery.

---

### 5. Get Exchange History

Get history of all exchanges (coupons, free delivery, gifts).

**Endpoint:** `GET /api/user/points/exchange/history`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**
- `per_page` (optional): Items per page (default: 15)
- `page` (optional): Page number

**Response:**
```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": {
    "exchanges": [
      {
        "id": 5,
        "exchange_type": "coupon",
        "status": "completed",
        "exchange_data": {
          "discount_amount": 1.00,
          "points_used": 100,
          "expires_at": "2026-03-24"
        },
        "created_at": "2026-02-24 10:00:00"
      },
      {
        "id": 6,
        "exchange_type": "free_delivery",
        "status": "used",
        "exchange_data": {
          "points_used": 100,
          "expires_at": "2026-03-24",
          "usage_count": 1
        },
        "created_at": "2026-02-24 11:00:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 2,
      "per_page": 15,
      "total": 20
    }
  }
}
```

---

### 6. Get Active Exchanges

Get currently usable exchanges (not expired, not used).

**Endpoint:** `GET /api/user/points/exchange/active`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response:**
```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": {
    "active_exchanges": {
      "coupons": [
        {
          "id": 2,
          "exchange_type": "coupon",
          "status": "completed",
          "exchange_data": {
            "discount_amount": 0.24,
            "points_used": 24,
            "expires_at": "2026-03-26"
          },
          "created_at": "2026-02-24 13:40:52"
        }
      ],
      "free_deliveries": [
        {
          "id": 3,
          "exchange_type": "free_delivery",
          "status": "completed",
          "exchange_data": {
            "delivery_zones": ["all"],
            "points_used": 100,
            "expires_at": "2026-03-26",
            "usage_count": 1
          },
          "created_at": "2026-02-24 13:40:52"
        }
      ]
    },
    "summary": {
      "total_coupons": 1,
      "total_free_deliveries": 1
    }
  }
}
```

---

## Order Integration

### How to Use Point Exchanges in Orders

When creating an order, you can apply redeemed coupons or free delivery from your active exchanges.

**Endpoint:** `POST /api/user/orders`

**Request Body with Point Exchanges:**
```json
{
  "cart_type": "default",
  "address_id": 2,
  "is_instant_delivery": true,
  "items": [
    {
      "shop_product_variant_id": 10,
      "quantity": 2
    }
  ],
  "point_coupon_exchange_id": 2,
  "point_free_delivery_exchange_id": 3
}
```

**New Fields:**
- `point_coupon_exchange_id` (optional): ID of the coupon exchange to apply
- `point_free_delivery_exchange_id` (optional): ID of the free delivery exchange to apply

**Response:**
```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": {
    "id": 15,
    "order_code": "ORD-260224-WJCC15",
    "status": "pending",
    "cart_type": "default",
    "is_instant_delivery": true,
    "delivery_price": 0,
    "subtotal": 264,
    "total": 210.72,
    "total_with_delivery": 210.72,
    "total_quantity": 2,
    "basket_discount": 0,
    "coupon_discount": 0,
    "coupon_discount_from_points": 0.24,
    "free_delivery_from_points": true,
    "used_coupon_exchange_id": 2,
    "used_free_delivery_exchange_id": 3,
    "created_at": "2026-02-24 13:58:11",
    "user": {
      "id": 2,
      "name": "حمزة فواز",
      "email": "hamza@gmail.com",
      "phone": "0993359825"
    },
    "items": [
      {
        "id": 32,
        "product_name": "منتج تجريبي",
        "quantity": 2,
        "price": 132,
        "discount": 20,
        "status": "pending"
      }
    ]
  }
}
```

**Important Response Fields:**
- `coupon_discount_from_points`: Discount amount applied from point exchange
- `free_delivery_from_points`: Whether free delivery was applied (true/false)
- `used_coupon_exchange_id`: ID of the coupon exchange used (null if not used)
- `used_free_delivery_exchange_id`: ID of the free delivery exchange used (null if not used)
- `delivery_price`: Will be 0 if free delivery was applied

---

## Complete Flow Examples

### Example 1: Earn and Use Points for Discount

#### Step 1: Check Current Balance
```
GET /api/user/points/summary
```

Response:
```json
{
  "data": {
    "balance": 150
  }
}
```

#### Step 2: Exchange Points for Coupon
```
POST /api/user/points/exchange/coupon
{
  "points": 100
}
```

Response:
```json
{
  "data": {
    "exchange_id": 5,
    "discount_amount": 1.00,
    "expires_at": "2026-03-24"
  }
}
```

#### Step 3: Get Active Exchanges
```
GET /api/user/points/exchange/active
```

Response:
```json
{
  "data": {
    "active_exchanges": {
      "coupons": [
        {
          "id": 5,
          "exchange_data": {
            "discount_amount": 1.00
          }
        }
      ]
    }
  }
}
```

#### Step 4: Create Order with Coupon
```
POST /api/user/orders
{
  "cart_type": "default",
  "address_id": 2,
  "items": [
    {
      "shop_product_variant_id": 10,
      "quantity": 1
    }
  ],
  "point_coupon_exchange_id": 5
}
```

Response:
```json
{
  "data": {
    "subtotal": 100,
    "coupon_discount_from_points": 1.00,
    "total": 99.00,
    "used_coupon_exchange_id": 5
  }
}
```

---

### Example 2: Use Free Delivery

#### Step 1: Exchange Points for Free Delivery
```
POST /api/user/points/exchange/free-delivery
```

Response:
```json
{
  "data": {
    "exchange_id": 6,
    "points_used": 100,
    "expires_at": "2026-03-24"
  }
}
```

#### Step 2: Create Order with Free Delivery
```
POST /api/user/orders
{
  "cart_type": "default",
  "address_id": 2,
  "items": [
    {
      "shop_product_variant_id": 10,
      "quantity": 1
    }
  ],
  "point_free_delivery_exchange_id": 6
}
```

Response:
```json
{
  "data": {
    "subtotal": 100,
    "delivery_price": 0,
    "free_delivery_from_points": true,
    "total": 100.00,
    "used_free_delivery_exchange_id": 6
  }
}
```

---

### Example 3: Use Both Coupon and Free Delivery

#### Step 1: Get Active Exchanges
```
GET /api/user/points/exchange/active
```

Response:
```json
{
  "data": {
    "active_exchanges": {
      "coupons": [
        {
          "id": 2,
          "exchange_data": {
            "discount_amount": 0.24
          }
        }
      ],
      "free_deliveries": [
        {
          "id": 3
        }
      ]
    }
  }
}
```

#### Step 2: Create Order with Both
```
POST /api/user/orders
{
  "cart_type": "default",
  "address_id": 2,
  "items": [
    {
      "shop_product_variant_id": 10,
      "quantity": 2
    }
  ],
  "point_coupon_exchange_id": 2,
  "point_free_delivery_exchange_id": 3
}
```

Response:
```json
{
  "data": {
    "subtotal": 264,
    "coupon_discount_from_points": 0.24,
    "delivery_price": 0,
    "free_delivery_from_points": true,
    "total": 210.72,
    "used_coupon_exchange_id": 2,
    "used_free_delivery_exchange_id": 3
  }
}
```

---

## Points Earning Rules

Users automatically earn points for these activities:

### 1. User Registration
- **Points Awarded:** 50 points
- **When:** Upon successful registration
- **One-time:** Yes

### 2. First Order
- **Points Awarded:** Based on order total (configurable)
- **When:** When first order status becomes "delivered"
- **One-time:** Yes

### 3. Order Completion
- **Points Awarded:** Based on order total (configurable)
- **When:** When order status becomes "delivered"
- **One-time:** No (awarded for every completed order)

### 4. Product Review
- **Points Awarded:** Fixed amount (configurable)
- **When:** Upon submitting a product review
- **One-time:** No (per product)

---

## Exchange Settings

Current system settings (configurable by admin):

```json
{
  "min_points": 50,
  "max_points": 5000,
  "coupon_enabled": true,
  "coupon_discount_rate": 0.01,
  "free_delivery_enabled": true,
  "free_delivery_points": 100,
  "gifts_enabled": true
}
```

---

## Important Notes

1. **Exchange Expiration:**
   - Coupons and free delivery expire after 30 days
   - Check `expires_at` field before using

2. **Exchange Status:**
   - `completed`: Ready to use
   - `used`: Already used in an order
   - `pending`: Waiting for admin approval (gifts only)

3. **Order Priority:**
   - Regular coupons are applied before point coupons
   - If regular coupon is used, basket discount is cancelled
   - Point exchanges are independent of regular coupons

4. **Validation:**
   - System validates exchange ownership (user_id)
   - System validates exchange type (coupon/free_delivery)
   - System validates exchange status (must be 'completed')
   - System validates expiration date

5. **After Use:**
   - Used exchanges are marked as 'used'
   - They won't appear in active exchanges anymore
   - They remain in exchange history

---

## Error Handling

### Common Errors:

**Insufficient Points:**
```json
{
  "status": false,
  "message": "رصيد النقاط غير كافٍ"
}
```

**Invalid Exchange ID:**
```json
{
  "status": false,
  "message": "الاستبدال غير موجود أو غير صالح"
}
```

**Expired Exchange:**
```json
{
  "status": false,
  "message": "انتهت صلاحية الاستبدال"
}
```

**Already Used:**
```json
{
  "status": false,
  "message": "تم استخدام هذا الاستبدال مسبقاً"
}
```

---

## Testing with Postman

### Collection Structure:

```
Points System
├── Points
│   ├── Get Summary
│   ├── Get Transactions
│   └── Get Statistics
├── Exchange
│   ├── Get Options
│   ├── Exchange for Coupon
│   ├── Exchange for Free Delivery
│   ├── Exchange for Gift
│   ├── Get History
│   └── Get Active Exchanges
└── Orders
    └── Create Order with Exchanges
```

### Environment Variables:
```
base_url: http://localhost:8000/api
token: {your_auth_token}
user_id: 2
```

---

## Database Schema Reference

### point_wallets
- `id`, `user_id`, `balance`, `created_at`, `updated_at`

### point_transactions
- `id`, `wallet_id`, `type`, `amount`, `description`, `reference_type`, `reference_id`, `status`, `created_at`

### point_exchanges
- `id`, `user_id`, `transaction_id`, `exchange_type`, `exchange_data`, `status`, `created_at`

### orders (point-related fields)
- `used_coupon_exchange_id`
- `used_free_delivery_exchange_id`
- `coupon_discount_from_points`
- `free_delivery_from_points`

---

## Support

For issues or questions about the points system, contact the development team.

Last Updated: February 24, 2026

