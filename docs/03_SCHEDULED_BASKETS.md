# Scheduled Baskets API

## Base URL
```
/api/admin/baskets (Admin)
/api/user/scheduled-baskets (User)
```

## Admin - Scheduled Baskets

### 1. List Scheduled Baskets
**GET** `/api/admin/baskets?is_schedule=1`

### Query Parameters
- `is_schedule` (boolean): Filter scheduled baskets
- `category_id` (int)
- `page`, `per_page`

### Response
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": {"en": "Weekly Basket", "ar": "سلة أسبوعية"},
        "category_id": 5,
        "is_schedule": true,
        "schedules": [
          {
            "id": 1,
            "title": {"en": "Weekly", "ar": "أسبوعي"},
            "number_of_days": 7,
            "discount_type": "percent",
            "discount_value": 10,
            "is_default": true
          }
        ],
        "items": [...]
      }
    ]
  }
}
```


### 2. Create Scheduled Basket
**POST** `/api/admin/baskets`

### Request Body
```json
{
  "name[en]": "Weekly Basket",
  "name[ar]": "سلة أسبوعية",
  "category_id": 5,
  "is_schedule": true,
  "schedules": [
    {
      "title[en]": "Weekly",
      "title[ar]": "أسبوعي",
      "number_of_days": 7,
      "discount_type": "percent",
      "discount_value": 10,
      "is_default": true
    }
  ],
  "items": [
    {
      "shop_product_variant_id": 10,
      "quantity": 2,
      "is_required": true
    }
  ]
}
```

## User - My Scheduled Baskets

### 1. Get My Baskets
**GET** `/api/user/my-baskets?type=subscription`

### Query Parameters
- `type`: all, user-schedule, subscription, custom

### 2. Pause Subscription
**POST** `/api/user/my-baskets/{basketId}/pause-subscription`

### 3. Resume Subscription
**POST** `/api/user/my-baskets/{basketId}/resume-subscription`
