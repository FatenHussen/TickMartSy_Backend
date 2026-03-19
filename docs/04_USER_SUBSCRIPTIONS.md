# User Subscriptions API

## Base URL
```
/api/user
```

## 1. Get Available Packages
**GET** `/api/user/packages`

### Response
```json
{
  "data": [
    {
      "id": 1,
      "name": {"en": "Gold Package", "ar": "باقة ذهبية"},
      "price": 99.99,
      "duration_days": 30,
      "max_orders": 10,
      "free_deliveries": 5,
      "discount_percentage": 15,
      "is_active": true
    }
  ]
}
```

## 2. Subscribe to Package
**POST** `/api/user/subscribe`

### Request Body
```json
{
  "package_id": 1
}
```

### Response
```json
{
  "success": true,
  "message": "تم الاشتراك بنجاح"
}
```

## 3. Get My Subscription
**GET** `/api/user/my-subscription`

### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "package": {...},
    "start_date": "2024-01-01",
    "end_date": "2024-01-31",
    "status": "active",
    "remaining_orders": 8,
    "remaining_free_deliveries": 3
  }
}
```


## 4. Renew Subscription
**POST** `/api/user/renew`

### Request Body
```json
{
  "package_id": 1
}
```

## 5. Cancel Subscription
**DELETE** `/api/user/cancel-subscription/{packageId}`

### Response
```json
{
  "success": true,
  "message": "تم إلغاء الاشتراك بنجاح"
}
```

## Subscription Benefits

### Get Active Benefits
**GET** `/api/user/subscription/benefits`

### Response
```json
{
  "success": true,
  "data": {
    "has_subscription": true,
    "discount_percentage": 15,
    "free_deliveries_remaining": 3,
    "orders_remaining": 8,
    "expires_at": "2024-01-31"
  }
}
```
