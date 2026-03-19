# Schedules API - Admin Panel

## Base URL
```
/api/admin/schedules
```

## Authentication
All endpoints require admin authentication:
```
Authorization: Bearer {admin_token}
```

## Overview
Schedules تمثل جداول التوصيل المتكررة (مثل: كل 3 أيام، كل أسبوع، إلخ) مع إمكانية تطبيق خصومات على الاشتراكات.

---

## 1. List Schedules
**GET** `/api/admin/schedules`

### Query Parameters
- `page` (int): Page number
- `per_page` (int): Items per page (default: 10)
- `search` (string): Search in name
- `is_active` (boolean): Filter by active status (true/false)
- `discount_type` (string): Filter by discount type (percentage, fixed)
- `sort_field` (string): id, name, interval_days, discount_value, created_at
- `sort_order` (string): asc, desc

### Response
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": "كل 3 أيام",
        "interval_days": 3,
        "is_active": true,
        "discount_type": "percentage",
        "discount_value": 10.00,
        "created_at": "2024-01-01 12:00:00"
      },
      {
        "id": 2,
        "name": "أسبوعي",
        "interval_days": 7,
        "is_active": true,
        "discount_type": "fixed",
        "discount_value": 5.00,
        "created_at": "2024-01-02 12:00:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 10,
      "total": 50
    }
  }
}
```

---

## 2. Get Single Schedule
**GET** `/api/admin/schedules/{id}`

### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": {
      "en": "Every 3 Days",
      "ar": "كل 3 أيام"
    },
    "interval_days": 3,
    "is_active": true,
    "discount_type": "percentage",
    "discount_value": 10.00,
    "created_at": "2024-01-01T12:00:00.000000Z",
    "updated_at": "2024-01-01T12:00:00.000000Z"
  }
}
```

---

## 3. Create Schedule
**POST** `/api/admin/schedules`

### Request Body
```json
{
  "name": {
    "en": "Every 3 Days",
    "ar": "كل 3 أيام"
  },
  "interval_days": 3,
  "is_active": true,
  "discount_type": "percentage",
  "discount_value": 10
}
```

### Validation Rules
- `name[en]`: required|string|max:255
- `name[ar]`: required|string|max:255
- `interval_days`: required|integer|min:1
- `is_active`: nullable|boolean (default: true)
- `discount_type`: nullable|in:percentage,fixed
- `discount_value`: nullable|numeric|min:0

### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": {
      "en": "Every 3 Days",
      "ar": "كل 3 أيام"
    },
    "interval_days": 3,
    "is_active": true,
    "discount_type": "percentage",
    "discount_value": 10.00,
    "created_at": "2024-01-01T12:00:00.000000Z",
    "updated_at": "2024-01-01T12:00:00.000000Z"
  },
  "message": "تم إنشاء الجدول بنجاح"
}
```

---

## 4. Update Schedule
**PUT/PATCH** `/api/admin/schedules/{id}`

### Request Body
Same as Create Schedule, but all fields are optional.

```json
{
  "name": {
    "en": "Every 5 Days",
    "ar": "كل 5 أيام"
  },
  "interval_days": 5,
  "is_active": false,
  "discount_type": "fixed",
  "discount_value": 15
}
```

### Validation Rules
- `name[en]`: nullable|string|max:255
- `name[ar]`: nullable|string|max:255
- `interval_days`: nullable|integer|min:1
- `is_active`: nullable|boolean
- `discount_type`: nullable|in:percentage,fixed
- `discount_value`: nullable|numeric|min:0

### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": {
      "en": "Every 5 Days",
      "ar": "كل 5 أيام"
    },
    "interval_days": 5,
    "is_active": false,
    "discount_type": "fixed",
    "discount_value": 15.00,
    "created_at": "2024-01-01T12:00:00.000000Z",
    "updated_at": "2024-01-02T14:30:00.000000Z"
  },
  "message": "تم تحديث الجدول بنجاح"
}
```

---

## 5. Delete Schedule
**DELETE** `/api/admin/schedules/{id}`

### Response
```json
{
  "success": true,
  "message": "تم حذف الجدول بنجاح"
}
```

---

## Field Descriptions

### interval_days
عدد الأيام بين كل توصيل. مثال:
- `3` = كل 3 أيام
- `7` = أسبوعي
- `14` = كل أسبوعين
- `30` = شهري

### discount_type
نوع الخصم المطبق على الاشتراك:
- `percentage`: خصم بالنسبة المئوية (مثال: 10%)
- `fixed`: خصم بقيمة ثابتة (مثال: 5 دينار)
- `null`: بدون خصم

### discount_value
قيمة الخصم:
- إذا كان `discount_type = percentage`: القيمة تمثل النسبة المئوية (مثال: 10 = 10%)
- إذا كان `discount_type = fixed`: القيمة تمثل المبلغ الثابت (مثال: 5 = 5 دينار)
- يجب أن تكون `null` إذا لم يكن هناك خصم

### is_active
حالة الجدول:
- `true`: نشط ويمكن للمستخدمين الاشتراك به
- `false`: غير نشط ولا يظهر للمستخدمين

---

## Examples

### Example 1: Weekly Schedule with 15% Discount
```json
{
  "name": {
    "en": "Weekly Delivery",
    "ar": "توصيل أسبوعي"
  },
  "interval_days": 7,
  "is_active": true,
  "discount_type": "percentage",
  "discount_value": 15
}
```

### Example 2: Every 3 Days with 10 Fixed Discount
```json
{
  "name": {
    "en": "Every 3 Days",
    "ar": "كل 3 أيام"
  },
  "interval_days": 3,
  "is_active": true,
  "discount_type": "fixed",
  "discount_value": 10
}
```

### Example 3: Monthly Schedule without Discount
```json
{
  "name": {
    "en": "Monthly Delivery",
    "ar": "توصيل شهري"
  },
  "interval_days": 30,
  "is_active": true,
  "discount_type": null,
  "discount_value": null
}
```

---

## Error Responses

### 404 Not Found
```json
{
  "success": false,
  "message": "Schedule not found"
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "interval_days": ["عدد الأيام مطلوب"],
    "name.ar": ["الاسم بالعربي مطلوب"]
  }
}
```

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

