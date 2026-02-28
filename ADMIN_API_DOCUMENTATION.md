# Admin API Documentation

## Base URL
```
/api/admin
```

## Authentication
All endpoints (except login) require Bearer token authentication:
```
Authorization: Bearer {admin_token}
```

## Standard Response Format

### Success Response
```json
{
    "status": true,
    "message": "Success",
    "data": {}
}
```

### Error Response
```json
{
    "status": "error",
    "message": "Error message",
    "errors": {}
}
```

## Standard Query Parameters (for index endpoints)
- `search` (string): Search term
- `sort_field` (string): Field to sort by (default: 'id')
- `sort_order` (string): 'asc' or 'desc' (default: 'desc')
- `page` (integer): Page number (default: 1)
- `per_page` (integer): Items per page (default: 10)

---


## Category Management

### List Categories
**GET** `/admin/categories`

### Get Category
**GET** `/admin/categories/{id}`

### Create Category
**POST** `/admin/categories`

**Request Body:**
```json
{
    "name": "Category Name",
    "description": "Description",
    "image": "file",
    "parent_id": null,
    "is_active": true
}
```

### Update Category
**PUT** `/admin/categories/{id}`

### Delete Category
**DELETE** `/admin/categories/{id}`

---

## Brand Management

### List Brands
**GET** `/admin/brands`

### Get Brand
**GET** `/admin/brands/{id}`

### Create Brand
**POST** `/admin/brands`

**Request Body:**
```json
{
    "name": "Brand Name",
    "logo": "file",
    "is_active": true
}
```

### Update Brand
**PUT** `/admin/brands/{id}`

### Delete Brand
**DELETE** `/admin/brands/{id}`

---

## Category Attributes Management

### List Category Attributes
**GET** `/admin/category-attributes`

### Get Category Attribute
**GET** `/admin/category-attributes/{id}`

### Create Category Attribute
**POST** `/admin/category-attributes`

### Update Category Attribute
**PUT** `/admin/category-attributes/{id}`

### Delete Category Attribute
**DELETE** `/admin/category-attributes/{id}`

---

## Category Details Management

### List Category Details
**GET** `/admin/category-details`

### Get Category Detail
**GET** `/admin/category-details/{id}`

### Create Category Detail
**POST** `/admin/category-details`

### Update Category Detail
**PUT** `/admin/category-details/{id}`

### Delete Category Detail
**DELETE** `/admin/category-details/{id}`

---

## Product Management

### List Products
**GET** `/admin/products`

### Get Product
**GET** `/admin/products/{id}`

### Create Product
**POST** `/admin/products`

**Request Body:**
```json
{
    "name": "Product Name",
    "description": "Description",
    "category_id": 1,
    "brand_id": 1,
    "images": ["file1", "file2"],
    "is_active": true
}
```

### Update Product
**PUT** `/admin/products/{id}`

### Delete Product
**DELETE** `/admin/products/{id}`

---

## Product Variant Management

### List Product Variants
**GET** `/admin/product-variants`

### Get Product Variant
**GET** `/admin/product-variants/{id}`

### Create Product Variant
**POST** `/admin/product-variants`

### Update Product Variant
**PUT** `/admin/product-variants/{id}`

### Delete Product Variant
**DELETE** `/admin/product-variants/{id}`

---

## Shop Product Variant Management

### List Shop Product Variants
**GET** `/admin/shop-product-variants`

### Get Shop Product Variant
**GET** `/admin/shop-product-variants/{id}`

### Create Shop Product Variant
**POST** `/admin/shop-product-variants`

### Update Shop Product Variant
**PUT** `/admin/shop-product-variants/{id}`

### Delete Shop Product Variant
**DELETE** `/admin/shop-product-variants/{id}`

---

## Governorate Management

### List Governorates
**GET** `/admin/governorates`

### Get Governorate
**GET** `/admin/governorates/{id}`

### Create Governorate
**POST** `/admin/governorates`

**Request Body:**
```json
{
    "name": "Governorate Name",
    "is_active": true
}
```

### Update Governorate
**PUT** `/admin/governorates/{id}`

### Delete Governorate
**DELETE** `/admin/governorates/{id}`

---

## City Management

### List Cities
**GET** `/admin/cities`

### Get City
**GET** `/admin/cities/{id}`

### Create City
**POST** `/admin/cities`

**Request Body:**
```json
{
    "name": "City Name",
    "governorate_id": 1,
    "is_active": true
}
```

### Update City
**PUT** `/admin/cities/{id}`

### Delete City
**DELETE** `/admin/cities/{id}`

---

## Area Management

### List Areas
**GET** `/admin/areas`

### Get Area
**GET** `/admin/areas/{id}`

### Create Area
**POST** `/admin/areas`

**Request Body:**
```json
{
    "name": "Area Name",
    "city_id": 1,
    "delivery_fee": 5.00,
    "is_active": true
}
```

### Update Area
**PUT** `/admin/areas/{id}`

### Delete Area
**DELETE** `/admin/areas/{id}`

---

## Basket Management

### List Baskets
**GET** `/admin/baskets`

### Get Basket
**GET** `/admin/baskets/{id}`

### Create Basket
**POST** `/admin/baskets`

**Request Body:**
```json
{
    "name": "Basket Name",
    "description": "Description",
    "price": 50.00,
    "discount_price": 45.00,
    "is_active": true,
    "products": [
        {
            "product_id": 1,
            "quantity": 2
        }
    ]
}
```

### Update Basket
**PUT** `/admin/baskets/{id}`

### Delete Basket
**DELETE** `/admin/baskets/{id}`

---

## Scheduled Basket Management

### List Scheduled Baskets
**GET** `/admin/scheduled-baskets`

### Get Scheduled Basket
**GET** `/admin/scheduled-baskets/{id}`

### Create Scheduled Basket
**POST** `/admin/scheduled-baskets`

### Update Scheduled Basket
**PUT** `/admin/scheduled-baskets/{id}`

### Delete Scheduled Basket
**DELETE** `/admin/scheduled-baskets/{id}`

---

## Package Management

### List Packages
**GET** `/admin/packages`

### Get Package
**GET** `/admin/packages/{id}`

### Create Package
**POST** `/admin/packages`

**Request Body:**
```json
{
    "name": "Package Name",
    "description": "Description",
    "price": 100.00,
    "duration_days": 30,
    "features": ["feature1", "feature2"],
    "is_active": true
}
```

### Update Package
**PUT** `/admin/packages/{id}`

### Delete Package
**DELETE** `/admin/packages/{id}`

---

## Subscription Management

### List Subscriptions
**GET** `/admin/subscriptions`

### Get Subscription
**GET** `/admin/subscriptions/{id}`

### Create Subscription
**POST** `/admin/subscriptions`

### Update Subscription
**PUT** `/admin/subscriptions/{id}`

### Delete Subscription
**DELETE** `/admin/subscriptions/{id}`

---

## Gift Management

### List Gifts
**GET** `/admin/gifts`

### Get Gift
**GET** `/admin/gifts/{id}`

### Create Gift
**POST** `/admin/gifts`

**Request Body:**
```json
{
    "name": "Gift Name",
    "description": "Description",
    "points_required": 100,
    "image": "file",
    "stock": 50,
    "is_active": true
}
```

### Update Gift
**PUT** `/admin/gifts/{id}`

### Delete Gift
**DELETE** `/admin/gifts/{id}`

---

## User Gift Management

### List User Gifts
**GET** `/admin/user-gifts`

### Get User Gift
**GET** `/admin/user-gifts/{id}`

### Create User Gift
**POST** `/admin/user-gifts`

### Update User Gift
**PUT** `/admin/user-gifts/{id}`

### Delete User Gift
**DELETE** `/admin/user-gifts/{id}`

---

## Point Exchange Management

### List Point Exchanges
**GET** `/admin/point-exchanges`

### Get Point Exchange
**GET** `/admin/point-exchanges/{id}`

### Update Point Exchange
**PUT** `/admin/point-exchanges/{id}`

---

## Vendor Package Management

### List Vendor Packages
**GET** `/admin/vendor-packages`

### Get Vendor Package
**GET** `/admin/vendor-packages/{id}`

### Create Vendor Package
**POST** `/admin/vendor-packages`

**Request Body:**
```json
{
    "name": "Vendor Package Name",
    "description": "Description",
    "price": 200.00,
    "duration_days": 30,
    "max_products": 100,
    "max_shops": 5,
    "features": ["feature1", "feature2"],
    "is_active": true
}
```

### Update Vendor Package
**PUT** `/admin/vendor-packages/{id}`

### Delete Vendor Package
**DELETE** `/admin/vendor-packages/{id}`

---

## Vendor Subscription Management

### List Vendor Subscriptions
**GET** `/admin/vendor-subscriptions`

### Get Vendor Subscription
**GET** `/admin/vendor-subscriptions/{id}`

### Create Vendor Subscription
**POST** `/admin/vendor-subscriptions`

### Update Vendor Subscription
**PUT** `/admin/vendor-subscriptions/{id}`

### Delete Vendor Subscription
**DELETE** `/admin/vendor-subscriptions/{id}`

---

## Currency Management

### List Currencies
**GET** `/admin/currencies`

### Get Currency
**GET** `/admin/currencies/{id}`

### Create Currency
**POST** `/admin/currencies`

**Request Body:**
```json
{
    "name": "US Dollar",
    "code": "USD",
    "symbol": "$",
    "exchange_rate": 1.00,
    "is_active": true,
    "is_default": false
}
```

### Update Currency
**PUT** `/admin/currencies/{id}`

### Delete Currency
**DELETE** `/admin/currencies/{id}`

---

## User Points Management

### List User Points
**GET** `/admin/user-points`

**Response:**
```json
{
    "status": true,
    "message": "Success",
    "data": [
        {
            "user_id": 1,
            "user_name": "User Name",
            "total_points": 500,
            "available_points": 450
        }
    ]
}
```

### Get User Points
**GET** `/admin/user-points/{userId}`

### Get User Transactions
**GET** `/admin/user-points/{userId}/transactions`

---

## Seller Registration Management

### List Seller Registrations
**GET** `/admin/seller-registrations`

### Get Seller Registration
**GET** `/admin/seller-registrations/{id}`

### Approve Seller Registration
**POST** `/admin/seller-registrations/{id}/approve`

**Request Body:**
```json
{
    "notes": "Approved"
}
```

### Reject Seller Registration
**POST** `/admin/seller-registrations/{id}/reject`

**Request Body:**
```json
{
    "reason": "Incomplete documents"
}
```

### Delete Seller Registration
**DELETE** `/admin/seller-registrations/{id}`

---

## Vendor User Management

### List Vendor Users
**GET** `/admin/vendor-users`

### Get Vendor User
**GET** `/admin/vendor-users/{id}`

### Create Vendor User
**POST** `/admin/vendor-users`

**Request Body:**
```json
{
    "name": "Vendor User Name",
    "email": "vendor@example.com",
    "password": "password",
    "vendor_id": 1,
    "shop_ids": [1, 2, 3],
    "is_active": true
}
```

### Update Vendor User
**PUT** `/admin/vendor-users/{id}`

### Delete Vendor User
**DELETE** `/admin/vendor-users/{id}`

---

## User Basket Schedule Management (Read-Only)

### List User Basket Schedules
**GET** `/admin/user-basket-schedules`

### Get User Basket Schedule
**GET** `/admin/user-basket-schedules/{id}`

---
## Statistics Endpoints

### Dashboard Statistics
**GET** `/admin/statistics/dashboard`

**Response:**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "total_orders": 1500,
        "total_revenue": 50000.00,
        "total_users": 500,
        "total_drivers": 50,
        "pending_orders": 25,
        "today_orders": 45,
        "today_revenue": 1500.00
    }
}
```

### Get Counts
**GET** `/admin/statistics/counts`

**Response:**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "users": 500,
        "drivers": 50,
        "vendors": 30,
        "shops": 100,
        "products": 1000,
        "orders": 1500
    }
}
```

### Monthly Performance
**GET** `/admin/statistics/monthly-performance`

**Query Parameters:**
- `year` (integer): Year (default: current year)
- `month` (integer): Month (default: current month)

**Response:**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "total_orders": 150,
        "total_revenue": 5000.00,
        "avg_order_value": 33.33,
        "completed_orders": 140,
        "cancelled_orders": 10
    }
}
```

### Orders by Status
**GET** `/admin/statistics/orders-by-status`

**Response:**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "pending": 25,
        "preparing": 30,
        "out_delivery": 15,
        "delivered": 1400,
        "cancelled": 30
    }
}
```

### Top Shops
**GET** `/admin/statistics/top-shops`

**Query Parameters:**
- `limit` (integer): Number of shops (default: 10)
- `date_from` (date): Start date
- `date_to` (date): End date

**Response:**
```json
{
    "status": true,
    "message": "Success",
    "data": [
        {
            "shop_id": 1,
            "shop_name": "Shop Name",
            "total_orders": 200,
            "total_revenue": 10000.00
        }
    ]
}
```

### Revenue Trend
**GET** `/admin/statistics/revenue-trend`

**Query Parameters:**
- `period` (string): 'daily', 'weekly', 'monthly' (default: 'daily')
- `date_from` (date): Start date
- `date_to` (date): End date

### Orders by Hour
**GET** `/admin/statistics/orders-by-hour`

**Query Parameters:**
- `date` (date): Specific date (default: today)

### Orders by Day of Week
**GET** `/admin/statistics/orders-by-day`

**Query Parameters:**
- `date_from` (date): Start date
- `date_to` (date): End date

### Revenue by Payment Method
**GET** `/admin/statistics/revenue-by-payment`

**Query Parameters:**
- `date_from` (date): Start date
- `date_to` (date): End date

### Top Categories by Revenue
**GET** `/admin/statistics/top-categories`

**Query Parameters:**
- `limit` (integer): Number of categories (default: 10)
- `date_from` (date): Start date
- `date_to` (date): End date

### User Growth
**GET** `/admin/statistics/user-growth`

**Query Parameters:**
- `period` (string): 'daily', 'weekly', 'monthly' (default: 'monthly')
- `date_from` (date): Start date
- `date_to` (date): End date

### Order Status Funnel
**GET** `/admin/statistics/order-funnel`

**Query Parameters:**
- `date_from` (date): Start date
- `date_to` (date): End date

### Average Order Value Trend
**GET** `/admin/statistics/avg-order-value-trend`

**Query Parameters:**
- `period` (string): 'daily', 'weekly', 'monthly' (default: 'daily')
- `date_from` (date): Start date
- `date_to` (date): End date

### Driver Performance Comparison
**GET** `/admin/statistics/driver-comparison`

**Query Parameters:**
- `limit` (integer): Number of drivers (default: 10)
- `date_from` (date): Start date
- `date_to` (date): End date

### Product Stock Levels
**GET** `/admin/statistics/stock-levels`

**Query Parameters:**
- `shop_id` (integer): Filter by shop
- `low_stock_threshold` (integer): Threshold for low stock (default: 10)

### Sales Heatmap
**GET** `/admin/statistics/sales-heatmap`

**Query Parameters:**
- `date_from` (date): Start date
- `date_to` (date): End date

---

## Reports Endpoints

### Sales Report
**GET** `/admin/reports/sales`

**Query Parameters:**
- `date_from` (date): Start date (required)
- `date_to` (date): End date (required)
- `shop_id` (integer): Filter by shop
- `category_id` (integer): Filter by category
- `payment_method_id` (integer): Filter by payment method

**Response:**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "summary": {
            "total_orders": 150,
            "total_revenue": 5000.00,
            "total_items_sold": 300,
            "avg_order_value": 33.33
        },
        "orders": []
    }
}
```

### Product Movement Report
**GET** `/admin/reports/product-movement`

**Query Parameters:**
- `date_from` (date): Start date (required)
- `date_to` (date): End date (required)
- `product_id` (integer): Filter by product
- `shop_id` (integer): Filter by shop
- `category_id` (integer): Filter by category

**Response:**
```json
{
    "status": true,
    "message": "Success",
    "data": [
        {
            "product_id": 1,
            "product_name": "Product Name",
            "total_sold": 50,
            "total_revenue": 500.00,
            "avg_price": 10.00
        }
    ]
}
```

### Vendor Performance Report
**GET** `/admin/reports/vendor-performance/{vendorId}`

**Query Parameters:**
- `date_from` (date): Start date (required)
- `date_to` (date): End date (required)

**Response:**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "vendor_id": 1,
        "vendor_name": "Vendor Name",
        "total_orders": 200,
        "total_revenue": 10000.00,
        "total_products": 50,
        "avg_rating": 4.5,
        "shops": []
    }
}
```

### Driver Performance Report
**GET** `/admin/reports/driver-performance/{driverId}`

**Query Parameters:**
- `date_from` (date): Start date (required)
- `date_to` (date): End date (required)

**Response:**
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "driver_id": 1,
        "driver_name": "Driver Name",
        "total_deliveries": 150,
        "completed_deliveries": 145,
        "cancelled_deliveries": 5,
        "total_earnings": 750.00,
        "avg_rating": 4.8,
        "avg_delivery_time": "25 minutes"
    }
}
```

### Sales by Location Report
**GET** `/admin/reports/sales-by-location`

**Query Parameters:**
- `date_from` (date): Start date (required)
- `date_to` (date): End date (required)
- `governorate_id` (integer): Filter by governorate
- `city_id` (integer): Filter by city

### Sales by Category Report
**GET** `/admin/reports/sales-by-category`

**Query Parameters:**
- `date_from` (date): Start date (required)
- `date_to` (date): End date (required)

---

## Export Endpoints

### Export Sales Report
**GET** `/admin/reports/export/sales`

**Query Parameters:**
- Same as Sales Report
- `format` (string): 'excel' or 'pdf' (default: 'excel')

**Response:** File download (Excel or PDF)

### Export Product Movement Report
**GET** `/admin/reports/export/product-movement`

**Query Parameters:**
- Same as Product Movement Report
- `format` (string): 'excel' or 'pdf' (default: 'excel')

**Response:** File download (Excel or PDF)

### Export Vendor Performance Report
**GET** `/admin/reports/export/vendor-performance/{vendorId}`

**Query Parameters:**
- Same as Vendor Performance Report
- `format` (string): 'excel' or 'pdf' (default: 'excel')

**Response:** File download (Excel or PDF)

### Export Driver Performance Report
**GET** `/admin/reports/export/driver-performance/{driverId}`

**Query Parameters:**
- Same as Driver Performance Report
- `format` (string): 'excel' or 'pdf' (default: 'excel')

**Response:** File download (Excel or PDF)

---

## Notes

### Standard CRUD Operations
All resource endpoints follow the same pattern:
- **GET** `/admin/{resource}` - List all (with pagination)
- **GET** `/admin/{resource}/{id}` - Get one
- **POST** `/admin/{resource}` - Create new
- **PUT** `/admin/{resource}/{id}` - Update existing
- **DELETE** `/admin/{resource}/{id}` - Delete

### Pagination Response Format
```json
{
    "status": true,
    "message": "Success",
    "data": {
        "data": [],
        "current_page": 1,
        "last_page": 10,
        "per_page": 10,
        "total": 100
    }
}
```

### Error Codes
- `400` - Bad Request (validation errors)
- `401` - Unauthorized (invalid or missing token)
- `403` - Forbidden (insufficient permissions)
- `404` - Not Found
- `422` - Unprocessable Entity (validation failed)
- `500` - Internal Server Error

### File Upload
For endpoints that accept file uploads, use `multipart/form-data` content type.

### Date Format
All dates should be in `YYYY-MM-DD` format.

### Timestamps
All timestamps are returned in ISO 8601 format: `YYYY-MM-DD HH:MM:SS`
