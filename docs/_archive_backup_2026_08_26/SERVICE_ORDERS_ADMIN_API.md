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

## Area-aware access control

Admins are scoped to one or more `areas`, so every area-aware model in the admin API should only return rows that match the areas assigned to the authenticated admin (super-admins continue to see everything). We rely on:

### Data structure

- `areas` already exists (id, json `name`, city, etc.).
- A new `admin_area` pivot links `admins` to `areas` and is maintained whenever you onboard a new admin or change their coverage.
- Models that have a direct `area_id` column (e.g., `shops.area_id`, `user_addresses.area_id`) naturally belong to an area. Models that derive their area through another relation (e.g., `orders` via `address.area_id`) need explicit joins or `whereHas` in the admin layer when you filter by area.

### Policies & query scoping

- Continue to check permissions (e.g., `view service orders`) via your current role/permission package.
- Restrict rows to the authenticated admin’s areas in services or controllers (e.g., using `Admin::areaIds()` and the `admin_area` pivot), rather than relying on a model global scope.
- Seed `admin_area` and sync it with roles so you never leave an admin with permissions but no area coverage.

### Best practices

1. Cache `areaIds` on login (e.g., `session()->put('admin_area_ids', $admin->areaIds())`) so each request doesn’t re-query the pivot.
2. Combine explicit query filters with policy checks so both the “who” (roles/permissions) and the “what” (areas) are enforced.
