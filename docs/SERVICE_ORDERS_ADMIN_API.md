# Service Order – Admin APIs

These endpoints let an authenticated admin monitor and manage every service order.

## List and detail

### 1. List all service orders
- **Method**: `GET /api/admin/service-orders`
- **Auth**: `auth:admin`
- **Filters**: `status`, `shop_id`, `vendor_service_id`, `user_id`
- **Response**: paginated `ServiceOrder\AllResource` collection (same layout as the user list result).

### 2. Show one order
- **Method**: `GET /api/admin/service-orders/{id}/get_one`
- **Auth**: `auth:admin`
- **Response**: `ServiceOrder\OneResource` with the same payload as the user detail endpoint.

## Status transitions

### 3. Change service order status
- **Method**: `PATCH /api/admin/service-orders/{orderId}/change-status`
- **Auth**: `auth:admin`
- **Body**:
  ```json
  {
    "status": "completed"
  }
  ```
- **Rules**: Only transitions from non-final statuses are allowed. Once the status becomes `canceled`, `rejected`, or `completed`, it cannot be updated again. The controller validates the value via `ServiceOrderStatus` and throws `custom.service_orders.cannot_change_final_status` when the order is already closed.
- **Response**: The updated `ServiceOrder\OneResource`.

## Supporting pieces

- `App\Services\Admin\ServiceOrderService` exposes the filtering logic and guards the status transition inside a transaction.
- Localization keys for success/error messages can be found under `resources/lang/{en,ar,fre}/custom.php` in the `service_orders` group (e.g., `status_updated_successfully`, `cannot_change_final_status`).
