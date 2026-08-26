# Driver App API - Frontend Reference

This document consolidates the driver-facing API for the mobile app UI.

Authentication: `auth:driver` (Bearer token) unless noted.

## Auth

- `POST /driver/auth/login`
- `POST /driver/auth/send-password`
- `POST /driver/auth/verify-password`
- `GET /driver/auth/logout`
- `POST /driver/auth/store-token`
- `GET /driver/auth/notifications`
- `DELETE /driver/auth/delete-account`
- `POST /driver/auth/reset-password` (requires `abilities:reset-password`)

## Driver profile and coverage

Endpoint:

- `GET /driver/profile`

Includes:

- `cities`: list of assigned cities
- `areas`: list of areas derived from assigned cities
- `shops`: list of assigned shops
- `vendors`: list of assigned vendors

## Driver status

- `POST /driver/update-status`

## Orders

### Orders list (by status)

- `GET /driver/orders`

Required query params:

- `status`: one of
  - `pending`
  - `preparing`
  - `out_delivery`
  - `delivered`
  - `cancelled`
  - `cancelled_by_admin`
  - `rejected_by_delivery`
  - `faild_deliver`
  - `returned_by_user`

Optional query params:

- `assigned_by`: `admin` | `driver`

### Assigned orders (directly assigned)

- `GET /driver/orders/assigned`
- Optional: `status` (same values as above)

### Available nearby orders

- `GET /driver/orders/to-assigned`

### Order details

- `GET /driver/orders/show/{orderId}`

### Order actions

- `POST /driver/orders/accept/{orderId}`
- `POST /driver/orders/reject/{orderId}`
- `POST /driver/orders/item-out-delivery/{itemId}`
- `POST /driver/orders/order-out-delivery/{orderId}`
- `POST /driver/orders/shop-out-delivery/{orderId}`
- `POST /driver/orders/deliver/{orderId}`
- `POST /driver/orders/faild-deliver/{orderId}`
- `POST /driver/orders/returned-by-user/{orderId}`
- `POST /driver/orders/start-to-outdelivery/{orderId}`

### Current active order

- `GET /driver/orders/current`

### Driver location update

- `POST /driver/orders/update-location`
- `POST /driver/driver/update-location`

## Driver statistics (earnings/performance)

- `GET /driver/orders/statistics`

Query parameters:

- `period` (optional): `day` | `month` | `custom`
- `date` (optional, for `day`): `YYYY-MM-DD`
- `month` (optional, for `month`): `1-12`
- `year` (optional, for `month`): `YYYY`
- `start_date` (optional, for `custom`): `YYYY-MM-DD`
- `end_date` (optional, for `custom`): `YYYY-MM-DD`

If `period` is not provided, the API defaults to `day` using today.

Response fields (top-level):

- `average_rating`
- `rate_percent`
- `total_orders`
- `total_delivered`
- `today_delivered`
- `total_earnings`
- `today_earnings`
- `average_delivery_time_minutes`
- `cancellation_rate_percent`

Filtered fields:

- `filtered.period`
- `filtered.start`
- `filtered.end`
- `filtered.delivered_orders`
- `filtered.earnings`
- `filtered.average_delivery_time_minutes`

Examples:

- `GET /driver/orders/statistics?period=day&date=2026-05-07`
- `GET /driver/orders/statistics?period=month&month=5&year=2026`
- `GET /driver/orders/statistics?period=custom&start_date=2026-05-01&end_date=2026-05-07`

## Driver legal documents (policies)

- `GET /driver/legal-documents`
- `GET /driver/legal-documents/{key}`

Notes:

- Only documents with `key` containing `driver` are returned.

Example:

- `GET /driver/legal-documents/privacy_policy_driver`

## Driver contact methods (support numbers/links)

- `GET /driver/contact-methods`
- `GET /driver/contact-methods?type=number|email|url|whts`
- `GET /driver/contact-methods?key=support_driver_whatsapp`

Notes:

- Only contact methods with `key` containing `driver` are returned.

## Share and external messaging actions

The driver app should provide clear buttons/links that open external messaging apps for:

- Contact support/admin (WhatsApp, Telegram)
- Message the customer (pre-filled text or share tracking link)
- Share the app (invite another driver)

Support/admin contact URLs or numbers should come from contact methods.
