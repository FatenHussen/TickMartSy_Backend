# Ratings, Packages & Subscriptions System Documentation

## Table of Contents
1. [Ratings System](#ratings-system)
2. [Packages System](#packages-system)
3. [Subscriptions System](#subscriptions-system)
4. [Complete Flow Examples](#complete-flow-examples)

---

## Ratings System

The ratings system allows users to rate various entities in the platform and earn points for their reviews.

### Supported Rating Types

Users can rate the following entities:
- **product** - Products
- **delivery** - Delivery drivers
- **basket** - Admin baskets
- **schedule_basket** - Scheduled baskets
- **shop** - Shops/Stores
- **recipe** - Recipes
- **brand** - Brands

---

### 1. Get All Ratings (Public)

Get all ratings with filters.

**Endpoint:** `GET /api/user/ratings`

**Headers:**
```
Accept: application/json
```

**Query Parameters:**
- `rateable_type` (optional): Filter by type (product, delivery, basket, etc.)
- `rateable_id` (optional): Filter by specific entity ID
- `per_page` (optional): Items per page (default: 15)
- `page` (optional): Page number
- `search` (optional): Search in comments
- `sortField` (optional): Field to sort by (id, rating, created_at)
- `sortOrder` (optional): Sort direction (asc, desc)

**Example Request:**
```
GET /api/user/ratings?rateable_type=product&rateable_id=5&per_page=10
```

**Response:**
```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": [
    {
      "id": 1,
      "user": {
        "id": 2,
        "name": "أحمد محمد",
        "email": "ahmad@example.com"
      },
      "rateable_type": "product",
      "rateable_id": 5,
      "rating": 5,
      "comment": "منتج ممتاز وجودة عالية",
      "image": "https://example.com/storage/ratings/image.jpg",
      "is_verified": true,
      "order_id": 10,
      "created_at": "2026-02-20 14:30:00"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 10,
    "total": 25
  }
}
```

---

### 2. Get My Ratings

Get ratings created by the authenticated user.

**Endpoint:** `GET /api/user/ratings/my_ratings`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Query Parameters:**
- `type` (optional): Filter by type (product, delivery, basket, etc.)
- `rateable_id` (optional): Filter by specific entity ID

**Example Request:**
```
GET /api/user/ratings/my_ratings?type=product
```

**Response:**
```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": [
    {
      "id": 1,
      "rating": 5,
      "comment": "منتج ممتاز",
      "image": "https://example.com/storage/ratings/image.jpg",
      "order_id": 10,
      "created_at": "2026-02-20 14:30:00",
      "rateable": {
        "id": 5,
        "name": "منتج تجريبي",
        "type": "product"
      }
    }
  ]
}
```

---

### 3. Create Rating

Submit a new rating for an entity.

**Endpoint:** `POST /api/user/ratings`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: multipart/form-data
```

**Request Body:**
```json
{
  "type": "product",
  "rateable_id": 5,
  "rating": 5,
  "comment": "منتج ممتاز وجودة عالية",
  "order_id": 10,
  "image": "file"
}
```

**Validation Rules:**
- `type`: required, string, one of: product, delivery, basket, schedule_basket, shop, recipe, brand
- `rateable_id`: required, integer
- `rating`: required, integer, min: 1, max: 5
- `comment`: optional, string
- `order_id`: optional, exists in orders table
- `image`: optional, image file, max: 2MB

**Response:**
```json
{
  "status": true,
  "message": "تم إضافة التقييم بنجاح"
}
```

**Points Reward:**
- User automatically earns points for submitting a review
- Points are awarded based on `product_review` rule
- Points are added to user's wallet immediately

---

### 4. Update Rating

Update an existing rating (only owner can update).

**Endpoint:** `PUT /api/user/ratings/{id}`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: multipart/form-data
```

**Request Body:**
```json
{
  "rating": 4,
  "comment": "تحديث التقييم",
  "image": "file"
}
```

**Validation Rules:**
- `rating`: optional, integer, min: 1, max: 5
- `comment`: optional, string
- `image`: optional, image file, max: 2MB

**Response:**
```json
{
  "status": true,
  "message": "تم تحديث التقييم بنجاح"
}
```

**Authorization:**
- Only the rating owner can update it
- Returns 403 if user is not the owner

---

### 5. Delete Rating

Delete a rating (only owner can delete).

**Endpoint:** `DELETE /api/user/ratings/{id}`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response:**
```json
{
  "status": true,
  "message": "تم حذف التقييم بنجاح"
}
```

**Authorization:**
- Only the rating owner can delete it
- Returns 403 if user is not the owner

---

## Packages System

Packages are subscription plans that users can purchase to get benefits like free deliveries, order limits, and bonus points.

### Package Features

Each package includes:
- **Duration** - Number of days the package is valid
- **Monthly Orders Limit** - Maximum number of orders per month
- **Free Delivery Count** - Number of free deliveries included
- **Points Bonus** - Bonus points awarded upon subscription
- **Price** - Package price

---

### Get Available Packages

Get all active packages available for subscription.

**Endpoint:** `GET /api/user/packages`

**Headers:**
```
Accept: application/json
```

**Response:**
```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": [
    {
      "id": 1,
      "name": {
        "ar": "الباقة الأساسية",
        "en": "Basic Package"
      },
      "description": {
        "ar": "باقة مناسبة للاستخدام الشخصي",
        "en": "Suitable for personal use"
      },
      "price": 99.99,
      "duration_days": 30,
      "monthly_orders_limit": 10,
      "free_delivery_count": 5,
      "points_bonus": 100,
      "features": [
        "10 طلبات شهرياً",
        "5 توصيلات مجانية",
        "100 نقطة مكافأة"
      ],
      "is_active": true,
      "is_popular": false
    },
    {
      "id": 2,
      "name": {
        "ar": "الباقة الذهبية",
        "en": "Gold Package"
      },
      "description": {
        "ar": "باقة مميزة للعائلات",
        "en": "Premium package for families"
      },
      "price": 199.99,
      "duration_days": 30,
      "monthly_orders_limit": 30,
      "free_delivery_count": 15,
      "points_bonus": 300,
      "features": [
        "30 طلب شهرياً",
        "15 توصيل مجاني",
        "300 نقطة مكافأة",
        "أولوية في التوصيل"
      ],
      "is_active": true,
      "is_popular": true
    }
  ]
}
```

---

## Subscriptions System

Users can subscribe to packages to get benefits. The system supports new subscriptions and renewals.

### Subscription Status

- **active** - Currently active subscription
- **expired** - Subscription has ended
- **cancelled** - Subscription was cancelled

### Subscription Types

- **new** - First time subscription
- **renew** - Renewal of existing subscription

---

### 1. Subscribe to Package

Subscribe to a new package or switch to a different package.

**Endpoint:** `POST /api/user/subscribe`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

**Request Body:**
```json
{
  "package_id": 1
}
```

**Validation Rules:**
- `package_id`: required, must exist in packages table

**Response:**
```json
{
  "status": true,
  "message": "تم الاشتراك بنجاح"
}
```

**Behavior:**
- If user has an active subscription, it will be cancelled
- New subscription starts immediately
- Bonus points are added to user's wallet
- Subscription duration starts from now

**Example Flow:**
1. User selects a package
2. User submits subscription request
3. System cancels any active subscription
4. System creates new subscription
5. System adds bonus points to wallet
6. User can now use subscription benefits

---

### 2. Get My Subscription

Get the authenticated user's current subscription details.

**Endpoint:** `GET /api/user/my-subscription`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response (Active Subscription):**
```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": {
    "id": 5,
    "package": {
      "id": 1,
      "name": {
        "ar": "الباقة الأساسية",
        "en": "Basic Package"
      },
      "price": 99.99,
      "duration_days": 30,
      "monthly_orders_limit": 10,
      "free_delivery_count": 5,
      "points_bonus": 100
    },
    "start_date": "2026-02-01",
    "end_date": "2026-03-03",
    "remaining_orders": 7,
    "remaining_free_deliveries": 3,
    "status": "active",
    "type": "new",
    "days_remaining": 7,
    "is_expiring_soon": true,
    "created_at": "2026-02-01 10:00:00"
  }
}
```

**Response (No Subscription):**
```json
{
  "message": "No active subscription"
}
```
Status Code: 404

---

### 3. Renew Subscription

Renew an existing subscription with the same or different package.

**Endpoint:** `POST /api/user/renew`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

**Request Body:**
```json
{
  "package_id": 1
}
```

**Validation Rules:**
- `package_id`: required, must exist in packages table

**Response:**
```json
{
  "status": true,
  "message": "تم تجديد الاشتراك بنجاح"
}
```

**Behavior:**

**Case 1: Renew Same Package**
- If renewing the same package, subscription is extended
- Start date is reset to now
- End date is extended by package duration
- Remaining orders and deliveries are reset to package limits
- Bonus points are added again

**Case 2: Switch to Different Package**
- Current subscription is cancelled
- New subscription is created with new package
- All benefits are reset to new package limits
- Bonus points from new package are added

---

## Complete Flow Examples

### Example 1: Rate a Product After Order

#### Step 1: Complete an Order
```
POST /api/user/orders
{
  "items": [...],
  "address_id": 2
}
```

Order is delivered and completed.

#### Step 2: Submit Rating
```
POST /api/user/ratings
{
  "type": "product",
  "rateable_id": 5,
  "rating": 5,
  "comment": "منتج ممتاز",
  "order_id": 10
}
```

Response:
```json
{
  "status": true,
  "message": "تم إضافة التقييم بنجاح"
}
```

#### Step 3: Check Points Earned
```
GET /api/user/points/summary
```

Response shows increased points balance from review.

---

### Example 2: Subscribe to Package

#### Step 1: View Available Packages
```
GET /api/user/packages
```

Response:
```json
{
  "data": [
    {
      "id": 1,
      "name": {"ar": "الباقة الأساسية"},
      "price": 99.99,
      "monthly_orders_limit": 10,
      "free_delivery_count": 5,
      "points_bonus": 100
    }
  ]
}
```

#### Step 2: Subscribe to Package
```
POST /api/user/subscribe
{
  "package_id": 1
}
```

Response:
```json
{
  "status": true,
  "message": "تم الاشتراك بنجاح"
}
```

#### Step 3: Check Subscription Status
```
GET /api/user/my-subscription
```

Response:
```json
{
  "data": {
    "package": {
      "name": {"ar": "الباقة الأساسية"}
    },
    "start_date": "2026-02-24",
    "end_date": "2026-03-26",
    "remaining_orders": 10,
    "remaining_free_deliveries": 5,
    "status": "active"
  }
}
```

#### Step 4: Check Points Bonus
```
GET /api/user/points/summary
```

Response shows 100 bonus points added to balance.

---

### Example 3: Use Subscription Benefits

#### Step 1: Check Subscription
```
GET /api/user/my-subscription
```

Response:
```json
{
  "data": {
    "remaining_orders": 10,
    "remaining_free_deliveries": 5,
    "status": "active"
  }
}
```

#### Step 2: Create Order with Free Delivery
```
POST /api/user/orders
{
  "items": [...],
  "address_id": 2,
  "use_subscription_delivery": true
}
```

Order is created with free delivery from subscription.

#### Step 3: Check Updated Subscription
```
GET /api/user/my-subscription
```

Response:
```json
{
  "data": {
    "remaining_orders": 9,
    "remaining_free_deliveries": 4,
    "status": "active"
  }
}
```

---

### Example 4: Renew Subscription

#### Step 1: Check Current Subscription
```
GET /api/user/my-subscription
```

Response:
```json
{
  "data": {
    "package": {"id": 1, "name": {"ar": "الباقة الأساسية"}},
    "end_date": "2026-03-01",
    "days_remaining": 5,
    "is_expiring_soon": true,
    "status": "active"
  }
}
```

#### Step 2: Renew with Same Package
```
POST /api/user/renew
{
  "package_id": 1
}
```

Response:
```json
{
  "status": true,
  "message": "تم تجديد الاشتراك بنجاح"
}
```

#### Step 3: Check Renewed Subscription
```
GET /api/user/my-subscription
```

Response:
```json
{
  "data": {
    "start_date": "2026-02-24",
    "end_date": "2026-03-26",
    "remaining_orders": 10,
    "remaining_free_deliveries": 5,
    "status": "active",
    "type": "renew"
  }
}
```

---

### Example 5: Rate Multiple Entities

#### Rate Product
```
POST /api/user/ratings
{
  "type": "product",
  "rateable_id": 5,
  "rating": 5,
  "comment": "منتج ممتاز"
}
```

#### Rate Delivery Driver
```
POST /api/user/ratings
{
  "type": "delivery",
  "rateable_id": 3,
  "rating": 4,
  "comment": "توصيل سريع",
  "order_id": 10
}
```

#### Rate Shop
```
POST /api/user/ratings
{
  "type": "shop",
  "rateable_id": 2,
  "rating": 5,
  "comment": "متجر ممتاز"
}
```

#### View My Ratings
```
GET /api/user/ratings/my_ratings
```

Response shows all ratings created by user.

---

## Important Notes

### Ratings System

1. **Points Reward:**
   - Users earn points for each review submitted
   - Points are awarded immediately upon rating creation
   - Points amount is configured in `product_review` rule

2. **Authorization:**
   - Only authenticated users can create ratings
   - Only rating owner can update or delete their ratings
   - Public can view all ratings

3. **Verification:**
   - All ratings are automatically verified (`is_verified: true`)
   - Admin can manually verify/unverify ratings if needed

4. **Image Upload:**
   - Users can attach one image per rating
   - Maximum file size: 2MB
   - Supported formats: jpg, jpeg, png, gif

### Packages System

1. **Package Status:**
   - Only active packages (`is_active: true`) are shown to users
   - Admin can activate/deactivate packages

2. **Package Features:**
   - Duration is in days (e.g., 30 days = 1 month)
   - Orders limit is per month
   - Free deliveries are counted per subscription period
   - Points bonus is one-time reward upon subscription

3. **Popular Packages:**
   - Packages can be marked as popular (`is_popular: true`)
   - Popular packages are highlighted in UI

### Subscriptions System

1. **Subscription Lifecycle:**
   - Subscription starts immediately upon purchase
   - End date is calculated as: start_date + duration_days
   - Status automatically changes to 'expired' after end_date

2. **Benefits Usage:**
   - Remaining orders decrease with each order
   - Remaining free deliveries decrease when used
   - Benefits reset upon renewal

3. **Switching Packages:**
   - User can switch to different package anytime
   - Current subscription is cancelled
   - New subscription starts immediately
   - No refund for unused benefits

4. **Renewal Behavior:**
   - Renewing same package extends duration
   - Renewing different package creates new subscription
   - Bonus points are awarded on each renewal

5. **Expiration Warning:**
   - `is_expiring_soon: true` when less than 7 days remaining
   - `days_remaining` shows exact days left

---

## Error Handling

### Common Errors:

**Rating Not Found:**
```json
{
  "status": false,
  "message": "التقييم غير موجود"
}
```
Status Code: 404

**Unauthorized to Update/Delete:**
```json
{
  "status": false,
  "message": "غير مصرح لك بهذا الإجراء"
}
```
Status Code: 403

**Invalid Rating Type:**
```json
{
  "status": false,
  "message": "Invalid rateable type"
}
```
Status Code: 422

**Package Not Found:**
```json
{
  "status": false,
  "message": "الباقة غير موجودة"
}
```
Status Code: 404

**No Active Subscription:**
```json
{
  "message": "No active subscription"
}
```
Status Code: 404

---

## Testing with Postman

### Collection Structure:

```
Ratings & Subscriptions
├── Ratings
│   ├── Get All Ratings
│   ├── Get My Ratings
│   ├── Create Rating
│   ├── Update Rating
│   └── Delete Rating
├── Packages
│   └── Get Available Packages
└── Subscriptions
    ├── Subscribe to Package
    ├── Get My Subscription
    └── Renew Subscription
```

### Environment Variables:
```
base_url: http://localhost:8000/api
token: {your_auth_token}
user_id: 2
```

---

## Database Schema Reference

### ratings
- `id`, `user_id`, `rateable_type`, `rateable_id`, `rating`, `comment`, `image`, `is_verified`, `order_id`, `created_at`, `updated_at`

### packages
- `id`, `name`, `description`, `price`, `duration_days`, `monthly_orders_limit`, `free_delivery_count`, `points_bonus`, `features`, `is_active`, `is_popular`, `created_at`, `updated_at`

### subscriptions
- `id`, `user_id`, `package_id`, `start_date`, `end_date`, `remaining_orders`, `remaining_free_deliveries`, `status`, `type`, `created_at`, `updated_at`

---

## Support

For issues or questions about the ratings and subscriptions system, contact the development team.

Last Updated: February 24, 2026

