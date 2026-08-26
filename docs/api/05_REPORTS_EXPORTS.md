# Reports & Exports API

## Base URL
```
/api/admin/reports
```

## 1. Sales Report
**GET** `/api/admin/reports/sales`

### Query Parameters
- `from_date` (date): YYYY-MM-DD
- `to_date` (date): YYYY-MM-DD
- `shop_id` (int): Filter by shop
- `category_id` (int): Filter by category

### Response
```json
{
  "success": true,
  "data": {
    "total_sales": 15000.00,
    "total_orders": 150,
    "average_order_value": 100.00,
    "daily_sales": [
      {"date": "2024-01-01", "sales": 500.00, "orders": 5},
      {"date": "2024-01-02", "sales": 750.00, "orders": 8}
    ]
  }
}
```

## 2. Export Sales Report
**GET** `/api/admin/reports/sales/export`

### Query Parameters
Same as Sales Report

### Response
Returns Excel file download

## 3. Product Movement Report
**GET** `/api/admin/reports/product-movement`

### Query Parameters
- `from_date`, `to_date`
- `product_id` (int)
- `shop_id` (int)


### Response
```json
{
  "success": true,
  "data": {
    "products": [
      {
        "product_id": 1,
        "product_name": "Product A",
        "total_sold": 100,
        "total_revenue": 5000.00,
        "stock_remaining": 50
      }
    ]
  }
}
```

## 4. Vendor Performance Report
**GET** `/api/admin/reports/vendor-performance`

### Query Parameters
- `from_date`, `to_date`
- `vendor_id` (int)

### Response
```json
{
  "success": true,
  "data": {
    "vendors": [
      {
        "vendor_id": 1,
        "vendor_name": "Vendor A",
        "total_orders": 50,
        "total_revenue": 10000.00,
        "average_rating": 4.5,
        "completion_rate": 95
      }
    ]
  }
}
```

## 5. Driver Performance Report
**GET** `/api/admin/reports/driver-performance`

### Export Available
**GET** `/api/admin/reports/driver-performance/export`
