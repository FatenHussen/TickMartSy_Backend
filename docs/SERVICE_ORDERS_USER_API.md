# Service Order - User APIs

Authenticated users can create and view their own service orders via `App\Http\Controllers\User\ServiceOrder\ServiceOrderController`.

Each service order currently includes:
- References: `user_id`, `shop_id`, `vendor_service_id`, `shop_vendor_service_id`
- Pricing: `price`, `price_unit`
- Schedule: `date`, `time`
- Other: `notes`, `status`

Allowed `status` values: `pending`, `canceled`, `rejected`, `completed`.

## Endpoints

### 1) List my service orders
- **Method**: `GET /api/user/service-orders`
- **Auth**: `auth:user`
- **Behavior**: Always scoped to current authenticated user (`user_id` is applied server-side).
- **Query params**:
  - `status` (optional): one of service order statuses.
  - `search` (optional): searches in `id`, `notes`.
  - `sort_field` (optional): one of `id`, `created_at`, `date` (default from base behavior is `id`).
  - `sort_order` (optional): typically `asc` or `desc`.
  - `page`, `per_page` (optional): pagination controls.
- **Response**: paginated collection of `ServiceOrder\AllResource`.

`AllResource` item shape:
- `id`
- `status`
- `price`
- `price_unit`
- `shop` => `{ id, name, lat, lng }`
- `vendor_service` => `{ id, name }`
- `created_at`
- `date` (`Y-m-d`)
- `time` (formatted by `formattedOrderTime()`)
- `notes`
- `user` => `{ id, name, phone }`

### 2) Retrieve a single order
- **Method**: `GET /api/user/service-orders/{id}`
- **Auth**: `auth:user`
- **Response**: `ServiceOrder\OneResource`

`OneResource` includes:
- `id`
- `status`
- `price`
- `price_unit`
- `notes`
- `date` (`Y-m-d`)
- `time` (formatted by `formattedOrderTime()`)
- `created_at`
- `shop` => `{ id, name, lat, lng }`
- `vendor_service` => `{ id, name, description }`
- `shop_vendor_service` => `{ id, extra_details, duration_minutes, schedule }`
- `user` => `{ id, name, phone }`

### 3) Create a service order
- **Method**: `POST /api/user/service-orders`
- **Auth**: `auth:user`
- **Payload**:
  ```json
  {
    "shop_id": 12,
    "vendor_service_id": 5,
    "date": "2026-04-27",
    "time": "14:30",
    "notes": "Please arrive after noon."
  }
  ```
- **Validation**:
  - `shop_id`: required, must exist in `shops`.
  - `vendor_service_id`: required, must exist in `vendor_services`.
  - `date`: required, valid date, `after_or_equal:today`.
  - `time`: required, 24h format `HH:mm` or `HH:mm:ss`.
  - `notes`: optional string.
- **Business rule**:
  - The (`shop_id`, `vendor_service_id`) pair must exist in active `shop_vendor_services` (`is_active = true`).
  - If not found: throws custom 404 (`custom.service_orders.service_not_available`).
- **Behavior on success**:
  - Creates order with `status = pending`.
  - Copies `price` and `price_unit` from matched `shop_vendor_service`.
  - Stores submitted `date`, `time`, and optional `notes`.
  - Returns created `OneResource`.

## Notes
- User routes expose only `index`, `show`, and `store` (no update/delete).
- Resource fields reflect:
  - `App\Http\Resources\ServiceOrder\AllResource`
  - `App\Http\Resources\ServiceOrder\OneResource`
- Request rules reflect:
  - `App\Http\Requests\User\ServiceOrder\FilterRequest`
  - `App\Http\Requests\User\ServiceOrder\StoreRequest`
