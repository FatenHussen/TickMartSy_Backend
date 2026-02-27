# User Gifts API Documentation

## Overview
نظام إرسال الهدايا من الأدمن للمستخدمين مع إمكانية تتبع الحالة وتحديد عنوان التوصيل.

---

## Admin Endpoints

### 1. Create User Gift (إنشاء هدية لمستخدم)
**Endpoint:** `POST /api/admin/user-gifts`

**Headers:**
```
Authorization: Bearer {admin_token}
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "gift_id": 1,
  "user_id": 5,
  "address_id": null,
  "status": "pending",
  "admin_notes": "هدية تقديرية للعميل المميز"
}
```

**Response (201):**
```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": {
    "id": 1,
    "gift": {
      "id": 1,
      "name": {
        "ar": "سماعات لاسلكية (اللون: أسود) - متجر الإلكترونيات",
        "en": "Wireless Headphones (Color: Black) - Electronics Store"
      },
      "description": {
        "ar": "سماعات بلوتوث عالية الجودة",
        "en": "High quality bluetooth headphones"
      },
      "image": "http://127.0.0.1:8000/storage/gift/image.png",
      "points_required": 500
    },
    "user": {
      "id": 5,
      "name": "أحمد محمد",
      "phone": "+963912345678",
      "email": "ahmad@example.com"
    },
    "address": null,
    "status": "pending",
    "admin_notes": "هدية تقديرية للعميل المميز",
    "user_notes": null,
    "delivered_at": null,
    "created_at": "2026-02-27 18:30:00",
    "updated_at": "2026-02-27 18:30:00"
  }
}
```

**Notes:**
- يتم إرسال إشعار FCM للمستخدم تلقائياً
- `address_id` اختياري - المستخدم يمكنه تحديده لاحقاً
- `status` اختياري - القيمة الافتراضية `pending`

---

### 2. Get All User Gifts (عرض كل الهدايا)
**Endpoint:** `GET /api/admin/user-gifts`

**Headers:**
```
Authorization: Bearer {admin_token}
Accept: application/json
```

**Query Parameters:**
- `page` (optional): رقم الصفحة
- `per_page` (optional): عدد العناصر في الصفحة
- `status` (optional): فلترة حسب الحالة (pending, processing, shipped, delivered, cancelled)
- `user_id` (optional): فلترة حسب المستخدم
- `gift_id` (optional): فلترة حسب الهدية

**Example:** `GET /api/admin/user-gifts?status=pending&per_page=20`

**Response (200):**
```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": {
    "data": [
      {
        "id": 1,
        "gift": {
          "id": 1,
          "name": "سماعات لاسلكية",
          "image": "http://127.0.0.1:8000/storage/gift/image.png"
        },
        "user": {
          "id": 5,
          "name": "أحمد محمد",
          "phone": "+963912345678"
        },
        "address": null,
        "status": "pending",
        "created_at": "2026-02-27 18:30:00"
      }
    ],
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 1
  }
}
```

---

### 3. Get Single User Gift (عرض هدية واحدة)
**Endpoint:** `GET /api/admin/user-gifts/{id}`

**Headers:**
```
Authorization: Bearer {admin_token}
Accept: application/json
```

**Response (200):**
```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": {
    "id": 1,
    "gift": {
      "id": 1,
      "name": {
        "ar": "سماعات لاسلكية",
        "en": "Wireless Headphones"
      },
      "description": {
        "ar": "سماعات بلوتوث عالية الجودة",
        "en": "High quality bluetooth headphones"
      },
      "image": "http://127.0.0.1:8000/storage/gift/image.png",
      "points_required": 500
    },
    "user": {
      "id": 5,
      "name": "أحمد محمد",
      "phone": "+963912345678",
      "email": "ahmad@example.com"
    },
    "address": {
      "id": 3,
      "full_address": "شارع الثورة، بناء 15، الطابق 3",
      "city": "دمشق",
      "area": "المزة"
    },
    "status": "processing",
    "admin_notes": "هدية تقديرية للعميل المميز",
    "user_notes": "يرجى التوصيل بعد الساعة 5 مساءً",
    "delivered_at": null,
    "created_at": "2026-02-27 18:30:00",
    "updated_at": "2026-02-27 19:00:00"
  }
}
```

---

### 4. Update User Gift (تحديث الهدية)
**Endpoint:** `PUT /api/admin/user-gifts/{id}`

**Headers:**
```
Authorization: Bearer {admin_token}
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "status": "shipped",
  "admin_notes": "تم الشحن مع شركة أرامكس - رقم التتبع: 123456"
}
```

**Response (200):**
```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": {
    "id": 1,
    "gift": {...},
    "user": {...},
    "address": {...},
    "status": "shipped",
    "admin_notes": "تم الشحن مع شركة أرامكس - رقم التتبع: 123456",
    "user_notes": "يرجى التوصيل بعد الساعة 5 مساءً",
    "delivered_at": null,
    "created_at": "2026-02-27 18:30:00",
    "updated_at": "2026-02-27 20:00:00"
  }
}
```

**Notes:**
- عند تغيير `status` يتم إرسال إشعار للمستخدم تلقائياً
- يمكن تحديث: `gift_id`, `user_id`, `address_id`, `status`, `admin_notes`

---

### 5. Delete User Gift (حذف الهدية)
**Endpoint:** `DELETE /api/admin/user-gifts/{id}`

**Headers:**
```
Authorization: Bearer {admin_token}
Accept: application/json
```

**Response (200):**
```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": true
}
```

---

## User Endpoints

### 1. Get My Gifts (عرض هداياي)
**Endpoint:** `GET /api/user-gifts`

**Headers:**
```
Authorization: Bearer {user_token}
Accept: application/json
```

**Query Parameters:**
- `page` (optional): رقم الصفحة
- `per_page` (optional): عدد العناصر في الصفحة
- `status` (optional): فلترة حسب الحالة

**Example:** `GET /api/user-gifts?status=pending`

**Response (200):**
```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": {
    "data": [
      {
        "id": 1,
        "gift": {
          "id": 1,
          "name": "سماعات لاسلكية",
          "image": "http://127.0.0.1:8000/storage/gift/image.png"
        },
        "address": null,
        "status": "pending",
        "status_label": "قيد الانتظار",
        "created_at": "2026-02-27 18:30:00"
      }
    ],
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 1
  }
}
```

---

### 2. Get Single Gift (عرض هدية واحدة)
**Endpoint:** `GET /api/user-gifts/{id}`

**Headers:**
```
Authorization: Bearer {user_token}
Accept: application/json
```

**Response (200):**
```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": {
    "id": 1,
    "gift": {
      "id": 1,
      "name": "سماعات لاسلكية",
      "description": "سماعات بلوتوث عالية الجودة",
      "image": "http://127.0.0.1:8000/storage/gift/image.png"
    },
    "address": {
      "id": 3,
      "full_address": "شارع الثورة، بناء 15، الطابق 3",
      "city": "دمشق",
      "area": "المزة"
    },
    "status": "processing",
    "status_label": "قيد المعالجة",
    "user_notes": "يرجى التوصيل بعد الساعة 5 مساءً",
    "delivered_at": null,
    "created_at": "2026-02-27 18:30:00",
    "updated_at": "2026-02-27 19:00:00"
  }
}
```

---

### 3. Update Delivery Address (تحديد عنوان التوصيل)
**Endpoint:** `PUT /api/user-gifts/{id}/address`

**Headers:**
```
Authorization: Bearer {user_token}
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "address_id": 3,
  "user_notes": "يرجى التوصيل بعد الساعة 5 مساءً"
}
```

**Response (200):**
```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": {
    "id": 1,
    "gift": {...},
    "address": {
      "id": 3,
      "full_address": "شارع الثورة، بناء 15، الطابق 3",
      "city": "دمشق",
      "area": "المزة"
    },
    "status": "pending",
    "status_label": "قيد الانتظار",
    "user_notes": "يرجى التوصيل بعد الساعة 5 مساءً",
    "delivered_at": null,
    "created_at": "2026-02-27 18:30:00",
    "updated_at": "2026-02-27 18:45:00"
  }
}
```

**Notes:**
- يمكن تحديث العنوان فقط عندما تكون الحالة `pending`
- `user_notes` اختياري

---

## Status Values (حالات الهدية)

| Status | Arabic | English | Description |
|--------|--------|---------|-------------|
| `pending` | قيد الانتظار | Pending | في انتظار تحديد العنوان من المستخدم |
| `processing` | قيد المعالجة | Processing | جاري تجهيز الهدية للشحن |
| `shipped` | تم الشحن | Shipped | تم شحن الهدية |
| `delivered` | تم التسليم | Delivered | تم تسليم الهدية للمستخدم |
| `cancelled` | ملغي | Cancelled | تم إلغاء الهدية |

---

## FCM Notifications

### عند إنشاء هدية جديدة:
```json
{
  "title": "هدية جديدة!",
  "body": "لقد حصلت على هدية جديدة! يرجى تحديد عنوان التوصيل.",
  "data": {
    "type": "user_gift",
    "user_gift_id": 1,
    "gift_id": 1,
    "status": "pending"
  }
}
```

### عند تغيير الحالة:
```json
{
  "title": "تحديث حالة الهدية",
  "body": "حالة هديتك الآن: تم الشحن",
  "data": {
    "type": "user_gift",
    "user_gift_id": 1,
    "gift_id": 1,
    "status": "shipped"
  }
}
```

---

## Error Responses

### 404 Not Found
```json
{
  "status": false,
  "message": "الهدية غير موجودة"
}
```

### 422 Validation Error
```json
{
  "status": false,
  "message": "خطأ في البيانات المدخلة",
  "errors": {
    "gift_id": ["الهدية مطلوبة"],
    "user_id": ["المستخدم مطلوب"]
  }
}
```

### 403 Forbidden (User trying to update after processing)
```json
{
  "status": false,
  "message": "لا يمكن تحديث العنوان بعد بدء معالجة الهدية"
}
```

---

## Workflow Example

1. **Admin creates gift for user:**
   ```
   POST /api/admin/user-gifts
   {
     "gift_id": 1,
     "user_id": 5,
     "status": "pending"
   }
   ```
   → User receives FCM notification

2. **User sets delivery address:**
   ```
   PUT /api/user-gifts/1/address
   {
     "address_id": 3,
     "user_notes": "يرجى التوصيل بعد الساعة 5 مساءً"
   }
   ```

3. **Admin updates status to processing:**
   ```
   PUT /api/admin/user-gifts/1
   {
     "status": "processing",
     "admin_notes": "جاري تجهيز الهدية"
   }
   ```
   → User receives FCM notification

4. **Admin updates status to shipped:**
   ```
   PUT /api/admin/user-gifts/1
   {
     "status": "shipped",
     "admin_notes": "رقم التتبع: 123456"
   }
   ```
   → User receives FCM notification

5. **Admin marks as delivered:**
   ```
   PUT /api/admin/user-gifts/1
   {
     "status": "delivered"
   }
   ```
   → User receives FCM notification
