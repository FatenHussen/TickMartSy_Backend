# Driver Earnings & Performance - Frontend Notes

This document describes the driver-facing earnings/performance and orders filters from the API.

## Base route

All routes are under:

- `GET /driver/orders/statistics`
- `GET /driver/orders`

Authentication: `auth:driver` (Bearer token).

## Driver statistics (earnings/performance)

Endpoint:

- `GET /driver/orders/statistics`

### Query parameters

- `period` (optional): `day` | `month` | `custom`
- `date` (optional, for `day`): `YYYY-MM-DD`
- `month` (optional, for `month`): `1-12`
- `year` (optional, for `month`): `YYYY`
- `start_date` (optional, for `custom`): `YYYY-MM-DD`
- `end_date` (optional, for `custom`): `YYYY-MM-DD`

If `period` is not provided, the API defaults to `day` using today.

### Response fields

Top-level (existing):

- `average_rating`
- `rate_percent`
- `total_orders`
- `total_delivered`
- `today_delivered`
- `total_earnings`
- `today_earnings`
- `average_delivery_time_minutes`
- `cancellation_rate_percent`

Filtered (new):

- `filtered.period` (echoes `day`, `month`, or `custom`)
- `filtered.start` (start date, `YYYY-MM-DD`)
- `filtered.end` (end date, `YYYY-MM-DD`)
- `filtered.delivered_orders`
- `filtered.earnings`
- `filtered.average_delivery_time_minutes`

### Examples

Daily (specific date):

`GET /driver/orders/statistics?period=day&date=2026-05-07`

Monthly:

`GET /driver/orders/statistics?period=month&month=5&year=2026`

Custom range:

`GET /driver/orders/statistics?period=custom&start_date=2026-05-01&end_date=2026-05-07`

## Driver orders list (by status)

Endpoint:

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

### Examples

Failed delivery:

`GET /driver/orders?status=faild_deliver`

Cancelled by admin:

`GET /driver/orders?status=cancelled_by_admin`

Returned by user:

`GET /driver/orders?status=returned_by_user`

## Notes for UI

- The performance screen should show the average delivery time from `filtered.average_delivery_time_minutes` for the selected period.
- The orders screen should offer filters/tabs for all statuses listed above, including failed delivery and cancelled statuses.
- If you need a failure reason, confirm the API field used for it (not specified in this doc).
