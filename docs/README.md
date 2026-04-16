# Tikmool API Documentation

## Overview
Complete API documentation for Tikmool e-commerce platform.

## Documentation Files

### 1. Products API (`01_PRODUCTS_API.md`)
- CRUD operations for products
- Product listing with filters
- Image upload
- Product variants

### 2. Categories & Attributes (`02_CATEGORIES_ATTRIBUTES.md`)
- Category management
- Category attributes
- Attribute values
- Category hierarchy

### 3. Scheduled Baskets (`03_SCHEDULED_BASKETS.md`)
- Admin scheduled baskets management
- User subscription baskets
- Pause/Resume subscriptions
- Basket schedules

### 4. User Subscriptions (`04_USER_SUBSCRIPTIONS.md`)
- Package management
- Subscribe/Renew/Cancel
- Subscription benefits
- Usage tracking

### 5. Reports & Exports (`05_REPORTS_EXPORTS.md`)
- Sales reports
- Product movement
- Vendor performance
- Driver performance
- Excel exports

### 6. React Integration (`06_REACT_INTEGRATION.md`)
- API client setup
- Service layer examples
- React component examples
- Error handling

### 7. Admin Colors API (`ADMIN_COLORS_API.md`)
- Colors CRUD for admin
- Request validation rules
- Request/response examples

### 8. Admin Driver Coverage APIs (`ADMIN_DRIVER_COVERAGE_API.md`)
- Endpoint لتصفية طلبات pending/preparing حسب تغطية السائق (coverage)
- شكل الرد المخصص للـ dropdown (id/value)
- كيف تربطيه بالـ frontend (toggle/driver select)

### 9. Admin Driver Wallet Transactions API (`ADMIN_DRIVER_WALLET_TRANSACTIONS_API.md`)
- List/Show لمعاملات محفظة السائق من لوحة الإدارة
- الفلاتر (type, driver_id, order_id, range dates, range amount)
- أمثلة Request/Response جاهزة للربط مع الفرونت


## Quick Start

### Authentication
All admin endpoints require Bearer token:
```
Authorization: Bearer {your_admin_token}
```

### Base URLs
- Admin: `https://your-api.com/api/admin`
- User: `https://your-api.com/api/user`
- Vendor: `https://your-api.com/api/vendor`

### Response Format
All responses follow this structure:
```json
{
  "success": true,
  "message": "Success message",
  "data": {...}
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": {...}
}
```

## Common Query Parameters
- `page` (int): Page number (default: 1)
- `per_page` (int): Items per page (default: 10)
- `search` (string): Search term
- `sort_field` (string): Field to sort by
- `sort_order` (string): asc or desc

## File Uploads
Use `multipart/form-data` for file uploads:
```javascript
const formData = new FormData();
formData.append('image', file);
formData.append('name[en]', 'Name');
```

## Pagination
All list endpoints return pagination info:
```json
{
  "pagination": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 10,
    "total": 100
  }
}
```
