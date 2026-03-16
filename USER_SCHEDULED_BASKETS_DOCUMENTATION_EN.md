# User Scheduled Baskets API Documentation

## Table of Contents

1. [Overview](#overview)
2. [List Scheduled Baskets](#list-scheduled-baskets)
3. [Get Scheduled Basket Details](#get-scheduled-basket-details)
4. [Create Scheduled Basket](#create-scheduled-basket)
5. [Update Scheduled Basket](#update-scheduled-basket)
6. [Delete Scheduled Basket](#delete-scheduled-basket)
7. [Pause Scheduled Basket](#pause-scheduled-basket)
8. [Resume Scheduled Basket](#resume-scheduled-basket)
9. [Pause Admin Basket Subscription](#pause-admin-basket-subscription)
10. [Resume Admin Basket Subscription](#resume-admin-basket-subscription)

---

## Overview

**Description:**
The User Scheduled Baskets API allows users to manage their recurring basket subscriptions. Users can create, update, pause, resume, and delete scheduled baskets that will be automatically processed based on a schedule.

**Base URL:** `/api/user`

**Authentication:** Required (Bearer Token)

---

## List Scheduled Baskets

### Endpoint
```
GET /api/user/scheduled-baskets
```

### Route Name
```php
user.scheduled-baskets.index
```

### Controller
```php
App\Http\Controllers\User\Basket\UserBasketScheduleController@index
```

### Service
```php
App\Services\User\UserBasketScheduleService@getAll()
```

### Model
```php
App\Models\UserBasketSchedule
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| page | integer | No | Page number for pagination |
| per_page | integer | No | Items per page (default: 15) |
| search | string | No | Search by name or ID |
| sort_by | string | No | Sort field (id, created_at) |
| sort_order | string | No | Sort order (asc, desc) |

### Response Resource
```php
App\Http\Resources\UserBasketSchedule\AllResource
```

### Response Example

```json
{
  "status": true,
  "message": "Data retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Weekly Groceries",
        "image": "https://example.com/storage/categories/groceries.jpg",
        "num_varieties": 5,
        "is_paused": false,
        "original_price": 150.00,
        "original_price_converted": 40.50,
        "discount_value": 10,
        "discount_type": "percent",
        "discount_amount": 15.00,
        "discount_amount_converted": 4.05,
        "final_price": 135.00,
        "final_price_converted": 36.45,
        "next_run_date": "2026-03-24"
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 1
    }
  }
}
```

### Notes
- Only returns active scheduled baskets for the authenticated user
- Includes currency conversion based on user's preferred currency

---

## Get Scheduled Basket Details

### Endpoint
```
GET /api/user/scheduled-baskets/{id}
```

### Route Name
```php
user.scheduled-baskets.show
```

### Controller
```php
App\Http\Controllers\User\Basket\UserBasketScheduleController@show
```

### Service
```php
App\Services\User\UserBasketScheduleService@getOne($id)
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Scheduled basket ID |

### Response Resource
```php
App\Http\Resources\UserBasketSchedule\OneResource
```

### Relations Loaded
- `schedule` - Schedule details
- `items.product` - Product details for each item
- `items.variant` - Variant details for each item

### Response Example

```json
{
  "status": true,
  "message": "Data retrieved successfully",
  "data": {
    "id": 1,
    "name": "Weekly Groceries",
    "is_active": true,
    "is_paused": false,
    "paused_at": null,
    "start_date": "2026-03-17",
    "next_run_date": "2026-03-24",
    "schedule": {
      "id": 1,
      "name": "Weekly",
      "interval_days": 7,
      "discount_type": "percent",
      "discount_value": 10
    },
    "items": [
      {
        "id": 1,
        "product_id": 10,
        "shop_product_variant_id": 25,
        "quantity": 2,
        "price": 50.00,
        "product": {
          "id": 10,
          "name": "Fresh Milk",
          "image": "https://example.com/storage/products/milk.jpg"
        },
        "variant": {
          "id": 25,
          "attributes": "1L"
        }
      }
    ]
  }
}
```

---

## Create Scheduled Basket

### Endpoint
```
POST /api/user/scheduled-baskets
```

### Route Name
```php
user.scheduled-baskets.store
```

### Controller
```php
App\Http\Controllers\User\Basket\UserBasketScheduleController@store
```

### Service
```php
App\Services\User\UserBasketScheduleService@create($data)
```

### Request Class
```php
App\Http\Requests\User\BasketSchedule\StoreRequest
```

### Request Body

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| name | string | Yes | max:255 | Basket name |
| schedule_id | integer | Yes | exists:schedules,id | Schedule ID |
| start_date | date | Yes | after_or_equal:today | Start date |
| is_active | boolean | No | - | Active status (default: true) |
| items | array | Yes | min:1 | Array of basket items |
| items.*.product_id | integer | Yes | exists:products,id | Product ID |
| items.*.shop_product_variant_id | integer | No | exists:shop_product_variants,id | Shop variant ID |
| items.*.quantity | integer | Yes | min:1 | Quantity |

### Request Example

```json
{
  "name": "Weekly Groceries",
  "schedule_id": 1,
  "start_date": "2026-03-17",
  "is_active": true,
  "items": [
    {
      "product_id": 10,
      "shop_product_variant_id": 25,
      "quantity": 2
    },
    {
      "product_id": 15,
      "shop_product_variant_id": 30,
      "quantity": 1
    }
  ]
}
```

### Response Example

```json
{
  "status": true,
  "message": "Scheduled basket created successfully",
  "data": {
    "id": 1,
    "name": "Weekly Groceries",
    "is_active": true,
    "is_paused": false,
    "start_date": "2026-03-17",
    "next_run_date": "2026-03-24",
    "schedule": {
      "id": 1,
      "name": "Weekly",
      "interval_days": 7
    },
    "items": [...]
  }
}
```

### Database Transaction
- Creates the scheduled basket record
- Creates all basket items in a single transaction
- Automatically sets user_id from authenticated user

---

## Update Scheduled Basket

### Endpoint
```
PUT /api/user/scheduled-baskets/{id}
```

### Route Name
```php
user.scheduled-baskets.update
```

### Controller
```php
App\Http\Controllers\User\Basket\UserBasketScheduleController@update
```

### Service
```php
App\Services\User\UserBasketScheduleService@update($id, $data)
```

### Request Class
```php
App\Http\Requests\User\BasketSchedule\UpdateRequest
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Scheduled basket ID |

### Request Body

| Field | Type | Required | Validation | Description |
|-------|------|----------|------------|-------------|
| name | string | No | max:255 | Basket name |
| schedule_id | integer | No | exists:schedules,id | Schedule ID |
| start_date | date | No | after_or_equal:today | Start date |
| is_active | boolean | No | - | Active status |
| items | array | No | min:1 | Array of basket items |
| items.*.id | integer | No | - | Item ID (for update) |
| items.*.product_id | integer | No | exists:products,id | Product ID |
| items.*.shop_product_variant_id | integer | No | exists:shop_product_variants,id | Shop variant ID |
| items.*.quantity | integer | No | min:1 | Quantity |

### Request Example

```json
{
  "name": "Updated Weekly Groceries",
  "items": [
    {
      "id": 1,
      "quantity": 3
    },
    {
      "product_id": 20,
      "shop_product_variant_id": 40,
      "quantity": 2
    }
  ]
}
```

### Update Logic

**Items Update Strategy:**
- If item has `id` and exists: Update the item
- If item has no `id`: Create new item
- Items not included in request: Deleted

### Response Example

```json
{
  "status": true,
  "message": "Scheduled basket updated successfully",
  "data": {
    "id": 1,
    "name": "Updated Weekly Groceries",
    "is_active": true,
    "items": [...]
  }
}
```

---

## Delete Scheduled Basket

### Endpoint
```
DELETE /api/user/scheduled-baskets/{id}
```

### Route Name
```php
user.scheduled-baskets.destroy
```

### Controller
```php
App\Http\Controllers\User\Basket\UserBasketScheduleController@destroy
```

### Service
```php
App\Services\User\UserBasketScheduleService@delete($id)
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Scheduled basket ID |

### Response Example

```json
{
  "status": true,
  "message": "Scheduled basket deleted successfully",
  "data": true
}
```

### Notes
- Soft deletes the basket and all its items
- Only the owner can delete their scheduled basket

---

## Pause Scheduled Basket

### Endpoint
```
POST /api/user/scheduled-baskets/{id}/pause
```

### Route Name
```php
user.scheduled-baskets.pause
```

### Controller
```php
App\Http\Controllers\User\Basket\UserBasketScheduleController@pause
```

### Service
```php
App\Services\User\UserBasketScheduleService@pause($id)
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Scheduled basket ID |

### Description

Pauses a scheduled basket by setting the `paused_at` timestamp. When paused, the basket will not be processed automatically until it is resumed.

### Response Example

```json
{
  "status": true,
  "message": "Scheduled basket paused successfully",
  "data": {
    "id": 1,
    "name": "Weekly Groceries",
    "is_active": true,
    "is_paused": true,
    "paused_at": "2026-03-17 10:30:00",
    "start_date": "2026-03-17",
    "next_run_date": "2026-03-24",
    "schedule": {...},
    "items": [...]
  }
}
```

### Database Changes
- Sets `paused_at` to current timestamp
- Basket remains active but won't be processed

---

## Resume Scheduled Basket

### Endpoint
```
POST /api/user/scheduled-baskets/{id}/resume
```

### Route Name
```php
user.scheduled-baskets.resume
```

### Controller
```php
App\Http\Controllers\User\Basket\UserBasketScheduleController@resume
```

### Service
```php
App\Services\User\UserBasketScheduleService@resume($id)
```

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Scheduled basket ID |

### Description

Resumes a paused scheduled basket by clearing the `paused_at` timestamp. The basket will resume automatic processing according to its schedule.

### Response Example

```json
{
  "status": true,
  "message": "Scheduled basket resumed successfully",
  "data": {
    "id": 1,
    "name": "Weekly Groceries",
    "is_active": true,
    "is_paused": false,
    "paused_at": null,
    "start_date": "2026-03-17",
    "next_run_date": "2026-03-24",
    "schedule": {...},
    "items": [...]
  }
}
```

### Database Changes
- Sets `paused_at` to null
- Basket resumes automatic processing

---

## Pause Admin Basket Subscription

### Endpoint
```
POST /api/user/my-baskets/{basket}/pause-subscription
```

### Route Name
```php
user.my-baskets.pause-subscription
```

### Controller
```php
App\Http\Controllers\User\MyBasket\MyBasketController@pauseSubscription
```

### Service
```php
App\Services\User\MyBasketService@pauseSubscriptionBasket($userId, $basketId)
```

### Description

Pauses a user's subscription to an admin-created scheduled basket. This is different from user-created scheduled baskets. When a user subscribes to an admin basket with a schedule, this endpoint allows them to pause that subscription.

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| basket | integer | Yes | Admin basket ID |

### How It Works

1. Finds the order record where:
   - `user_id` matches authenticated user
   - `basket_id` matches the provided basket ID
   - `cart_type` is `schedule_admin_cart`
2. Sets the `pause_at` timestamp to current time
3. The subscription will not be processed until resumed

### Database Changes

**Table:** `orders`

Updates the order record:
```sql
UPDATE orders 
SET pause_at = NOW() 
WHERE basket_id = ? 
  AND user_id = ? 
  AND cart_type = 'schedule_admin_cart'
```

### Response Example

```json
{
  "message": "Basket paused successfully"
}
```

### Notes

- This endpoint is specifically for admin-created scheduled baskets
- The basket itself is not modified, only the user's subscription order
- Multiple users can subscribe to the same admin basket independently
- Each user can pause/resume their own subscription

---

## Resume Admin Basket Subscription

### Endpoint
```
POST /api/user/my-baskets/{basket}/resume-subscription
```

### Route Name
```php
user.my-baskets.resume-subscription
```

### Controller
```php
App\Http\Controllers\User\MyBasket\MyBasketController@resumeSubscription
```

### Service
```php
App\Services\User\MyBasketService@resumeSubscriptionBasket($userId, $basketId)
```

### Description

Resumes a paused subscription to an admin-created scheduled basket. This clears the pause timestamp and allows the subscription to be processed again according to its schedule.

### URL Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| basket | integer | Yes | Admin basket ID |

### How It Works

1. Finds the order record where:
   - `user_id` matches authenticated user
   - `basket_id` matches the provided basket ID
   - `cart_type` is `schedule_admin_cart`
2. Sets the `pause_at` timestamp to null
3. The subscription will resume automatic processing

### Database Changes

**Table:** `orders`

Updates the order record:
```sql
UPDATE orders 
SET pause_at = NULL 
WHERE basket_id = ? 
  AND user_id = ? 
  AND cart_type = 'schedule_admin_cart'
```

### Response Example

```json
{
  "message": "Basket resumed successfully"
}
```

### Notes

- This endpoint is specifically for admin-created scheduled baskets
- The basket itself is not modified, only the user's subscription order
- Resuming will allow the next scheduled delivery to proceed
- The schedule timing is based on the basket's schedule configuration

---

## Comparison: User vs Admin Scheduled Baskets

### User-Created Scheduled Baskets

**Model:** `UserBasketSchedule`  
**Table:** `user_basket_schedules`  
**Pause Field:** `paused_at` (on the basket schedule itself)  
**Endpoints:**
- `POST /api/user/scheduled-baskets/{id}/pause`
- `POST /api/user/scheduled-baskets/{id}/resume`

**Characteristics:**
- User creates and owns the basket
- User can fully customize items and schedule
- Pause affects the basket schedule directly
- Only the owner can access and modify

---

### Admin-Created Scheduled Baskets

**Model:** `Basket` (with `is_schedule = true`)  
**Table:** `baskets`  
**Subscription Model:** `Order` (with `cart_type = schedule_admin_cart`)  
**Pause Field:** `pause_at` (on the user's order/subscription)  
**Endpoints:**
- `POST /api/user/my-baskets/{basket}/pause-subscription`
- `POST /api/user/my-baskets/{basket}/resume-subscription`

**Characteristics:**
- Admin creates the basket template
- Multiple users can subscribe to the same basket
- Each user selects a schedule from available options
- Pause affects only the user's subscription, not the basket
- Users cannot modify basket items, only subscribe/unsubscribe

---

## Database Schema

### Table: `user_basket_schedules`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | bigint | No | Primary key |
| user_id | bigint | No | User ID (FK) |
| schedule_id | bigint | No | Schedule ID (FK) |
| name | string | No | Basket name |
| is_active | boolean | No | Active status |
| start_date | date | No | Start date |
| next_run_date | date | Yes | Next run date (computed) |
| paused_at | timestamp | Yes | Pause timestamp |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Update timestamp |

### Table: `user_basket_schedule_items`

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | bigint | No | Primary key |
| user_basket_schedule_id | bigint | No | Basket schedule ID (FK) |
| product_id | bigint | No | Product ID (FK) |
| shop_product_variant_id | bigint | Yes | Shop variant ID (FK) |
| quantity | integer | No | Quantity |
| price | decimal | No | Price at time of creation |
| created_at | timestamp | No | Creation timestamp |
| updated_at | timestamp | No | Update timestamp |

---

## Error Responses

### 404 Not Found

```json
{
  "status": false,
  "message": "Scheduled basket not found",
  "data": []
}
```

### 422 Validation Error

```json
{
  "status": false,
  "message": "Validation error",
  "data": {
    "name": ["The name field is required"],
    "items": ["The items field must have at least 1 item"]
  }
}
```

### 401 Unauthorized

```json
{
  "status": false,
  "message": "Unauthorized",
  "data": []
}
```

---

## Related Models

### UserBasketSchedule
```php
App\Models\UserBasketSchedule
```

**Relations:**
- `user()` - BelongsTo User
- `schedule()` - BelongsTo Schedule
- `items()` - HasMany UserBasketScheduleItem

### UserBasketScheduleItem
```php
App\Models\UserBasketScheduleItem
```

**Relations:**
- `userBasketSchedule()` - BelongsTo UserBasketSchedule
- `product()` - BelongsTo Product
- `variant()` - BelongsTo ShopProductVariant

### Schedule
```php
App\Models\Schedule
```

**Fields:**
- `name` - Schedule name
- `interval_days` - Days between runs
- `discount_type` - Discount type (percent/fixed)
- `discount_value` - Discount value

---

## Business Logic

### Next Run Date Calculation

The `next_run_date` is automatically calculated based on:
- `start_date` - The initial start date
- `schedule.interval_days` - Number of days between runs
- Current date

**Formula:**
```
next_run_date = start_date + (cycles * interval_days)
where cycles = ceil((today - start_date) / interval_days)
```

### Discount Calculation

Discount is applied from the schedule:
- **Percentage:** `discount_amount = total_price * (discount_value / 100)`
- **Fixed:** `discount_amount = min(discount_value, total_price)`

---

## Related Enums

### CartType Enum

```php
App\Enums\CartType
```

**Values:**
- `ADMIN_CART` - Regular admin basket
- `SCHEDULE_ADMIN_CART` - Scheduled admin basket subscription
- `USER_CART` - User's custom cart

---

## Notes

1. **Authentication Required:** All endpoints require user authentication

2. **User Isolation:** Users can only access their own scheduled baskets

3. **Currency Conversion:** Prices are automatically converted based on user's currency preference

4. **Soft Deletes:** Deleted baskets are soft-deleted and can be restored

5. **Transaction Safety:** Create and update operations use database transactions

---

## Support

For technical support or questions, please contact the development team.
