# Notification APIs

This document describes the notification endpoints used by the mobile/web frontend.

**Base URL**

`/api`

**Authentication**

All endpoints require `auth:user,admin,driver` middleware.
Use a Bearer token header:

`Authorization: Bearer {token}`

Note: The `GET /notifications` endpoint uses `auth('user')` in code, so it should be called with a **user token**.

**Response Envelope (Success)**

```json
{
  "status": true,
  "message": "Success",
  "data": {}
}
```

**Error Response (BaseException)**

```json
{
  "status": false,
  "message": "Error message",
  "errors": []
}
```

**Validation Errors**

Laravel validation errors return `422` with an `errors` object.

---

## 1) List Notifications

**GET** `/api/notifications`

**Query Params**

- `read` (optional, boolean):  
  - `true` or `1` to return only read notifications  
  - `false` or `0` to return only unread notifications
- `is_fixed` (optional, any value): when present, returns only unread fixed admin notifications  
  - This applies: `data->data->type = admin` AND `data->data->is_fixed = 1` AND `read_at IS NULL`
- `page` (optional, integer): Laravel pagination page number

**Response (data: paginated collection)**

`NotificationResource` fields:

```json
{
  "id": "uuid",
  "title": "string|null",
  "body": "string|null",
  "type": "string|null",
  "is_fixed": 0,
  "read": false,
  "created_at": "2 hours ago"
}
```

The response is a Laravel paginated resource collection wrapped by `sendResponse`. Example:

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "data": [
      {
        "id": "2b2d2b7b-6f61-4a9a-8d8a-0b0f0b0f0b0f",
        "title": "Order Update",
        "body": "Your order has been delivered",
        "type": "user",
        "is_fixed": 0,
        "read": true,
        "created_at": "3 minutes ago"
      }
    ],
    "links": {
      "first": "http://example.com/api/notifications?page=1",
      "last": "http://example.com/api/notifications?page=5",
      "prev": null,
      "next": "http://example.com/api/notifications?page=2"
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 5,
      "path": "http://example.com/api/notifications",
      "per_page": 20,
      "to": 20,
      "total": 95
    }
  }
}
```

---

## 2) Mark Notification As Read

**POST** `/api/notifications/mark-as-read`

**Body (JSON)**

- `notification_id` (required, uuid)

Example:

```json
{
  "notification_id": "2b2d2b7b-6f61-4a9a-8d8a-0b0f0b0f0b0f"
}
```

**Behavior**

- Finds the notification by `id` within the authenticated user's notifications.
- If not found, returns `404`.
- If already read, no change is made.

**Response**

```json
{
  "status": true,
  "message": "Success",
  "data": []
}
```

---

## 4) Notification Data Payloads (By Recipient)

**Where the `type` lives**

Database notifications are stored as:

```json
{
  "title": "...",
  "body": "...",
  "data": { "type": "...", "...": "..." }
}
```

So in the API response, `NotificationResource` reads:

- `type` from `data.data.type`
- `is_fixed` from `data.data.is_fixed`

FCM push notifications use the same `data` object directly under `message.data`.

---

### Driver

- `type: instant`  
Keys: `order_id`, `status`  
Source: `SendOrderNotifications` (instant delivery orders)

- `type: order`  
Keys: `order_id`, `status`  
Source: `HandleOrderStatusNotifications` (new order -> pending)

- `type: admin`  
Keys: none  
Source: `SendBulkNotificationJob` (admin broadcast)

---

### User

- `type: order`  
Keys: `order_id`, `status`  
Source: `HandleOrderStatusNotifications`

- `type: order_item`  
Keys: `order_id`, `item_id`  
Source: `HandleOrderItemStatusNotifications`

- `type: affiliate_order`  
Keys: `order_id`  
Source: `HandleOrderStatusNotifications` (affiliate user)

- `type: points_earned`  
Keys: `points`, `reason`, optional `order_id`, optional `breakdown`, optional `review_id`  
Source: `AwardPointsListener` / `UserService`

- `type: points_redeemed`  
Keys: `exchange_type`, `points_used`, optional `discount_amount`, optional `gift_name`  
Source: `PointExchangeService`

- `type: basket_reminder`  
Keys: `basket_id`  
Source: `SendScheduledBasketReminderJob`

- `type: subscription_reminder`  
Keys: `order_id`  
Source: `SendScheduledBasketReminderJob`

- `type: complaint`  
Keys: none  
Source: `ComplaintService`

- `type: coupon`  
Keys: none  
Source: `CouponService` (affiliate coupon assigned)

- `type: markter`  
Keys: none  
Source: `Admin\\UserService` (affiliate approval)

- `type: admin`  
Keys: optional `is_fixed`  
Source: `SendBulkNotificationJob`

- `type: user_gift` (FCM only, not stored in DB)  
Keys: `user_gift_id`, `gift_id`, `status`  
Source: `Admin\\UserGiftService`

---

### Admin

- `type: instant` or `type: scheduled`  
Keys: `order_id`, `status`  
Source: `SendOrderNotifications` (order created)

- `type: order`  
Keys: `order_id`, `status`  
Source: `HandleOrderStatusNotifications`

- `type: order_item`  
Keys: `order_id`, `item_id`  
Source: `HandleOrderItemStatusNotifications`

- `type: driver`  
Keys: `order_id`  
Source: `DriverAcceptOrderNotifications`

- `type: low_stock`  
Keys: `variant_id`, `quantity`  
Source: `NotifyAdminsLowStock`

**Not Found Error (404)**

```json
{
  "status": false,
  "message": "Not Found",
  "errors": []
}
```

---

## 3) Mark All Notifications As Read

**POST** `/api/notifications/mark-all-as-read`

**Body**

No body required.

**Behavior**

- Marks all unread notifications for the authenticated user as read.

**Response**

```json
{
  "status": true,
  "message": "Success",
  "data": []
}
```
