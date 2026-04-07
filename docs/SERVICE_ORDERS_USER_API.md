# Service Order – User APIs

Service orders let authenticated users request a vendor-provided service from a specific shop. Each record stores the selected `shop_id`, `vendor_service_id`, optional `shop_vendor_service_id`, published `price`, `price_unit`, `notes`, and one of the `ServiceOrderStatus` values (`pending`, `canceled`, `rejected`, `completed`).

## Endpoints

### 1. List my service orders
- **Method**: `GET /api/user/service-orders`
- **Auth**: `auth:user`
- **Query params**: `status` (optional, must match any `ServiceOrderStatus` value)
- **Response**: paginated collection of `ServiceOrder\AllResource` objects. Each item returns `id`, `status`, `price`, `price_unit`, `shop` metadata (`id`, `name`, `lat`, `lng`), `vendor_service` metadata, `notes`, and `created_at`.

### 2. Retrieve a single order
- **Method**: `GET /api/user/service-orders/{id}`
- **Auth**: `auth:user`
- **Response**: `ServiceOrder\OneResource` with detailed `shop`, `vendor_service`, optional `shop_vendor_service`, and `user` data together with `notes`, `status`, pricing, and timestamps.

### 3. Create a service order
- **Method**: `POST /api/user/service-orders`
- **Auth**: `auth:user`
- **Payload**:
  ```json
  {
    "shop_id": 12,
    "vendor_service_id": 5,
    "notes": "Please arrive after noon."
  }
  ```
- **Validation**: `shop_id` and `vendor_service_id` must reference existing active records; the combination must exist in `shop_vendor_services` and must be active.
- **Behavior**: Creates the order with the price/price_unit that the `shop_vendor_service` currently publishes, sets status to `pending`, and returns the freshly loaded `OneResource`.

## Notes
- Use `ServiceOrder\AllResource` for list responses (lean data) and `ServiceOrder\OneResource` for single-item views.
- Any language strings required for user-facing messages live in `resources/lang/{en,ar,fre}/custom.php` under the `service_orders` key (e.g., `service_not_available`).
