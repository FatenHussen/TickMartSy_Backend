# User Basket Schedules - Admin View API

## نظرة عامة
هذه الـ API تسمح للأدمن بعرض ومراقبة سلال المستخدمين المجدولة (Read-Only).

## الفرق بين السلال
- **Baskets (Admin)**: السلال التي ينشئها الأدمن (قوالب)
- **User Basket Schedules**: السلال الشخصية للمستخدمين (اشتراكات فعلية)

---

## Endpoints

### 1. List All User Basket Schedules
**GET** `/api/admin/user-basket-schedules`

عرض جميع سلال المستخدمين مع إمكانية التصفية والبحث.

**Headers:**
```
Authorization: Bearer {admin_token}
Accept: application/json
```

**Query Parameters:**
- `page` (optional): رقم الصفحة (default: 1)
- `per_page` (optional): عدد العناصر في الصفحة (default: 10)
- `search` (optional): البحث في اسم السلة
- `sort_field` (optional): حقل الترتيب (id, name, user_id, is_active, start_date, next_run_date, created_at)
- `sort_order` (optional): اتجاه الترتيب (asc, desc)
- `user_id` (optional): تصفية حسب المستخدم
- `category_id` (optional): تصفية حسب الفئة
- `schedule_id` (optional): تصفية حسب الجدولة
- `is_active` (optional): تصفية حسب الحالة (true/false)
- `start_date_from` (optional): تصفية من تاريخ البداية
- `start_date_to` (optional): تصفية إلى تاريخ البداية

**Example Request:**
```
GET /api/admin/user-basket-schedules?user_id=5&is_active=true&per_page=20
```

**Response:**
```json
{
    "status": true,
    "message": "تمت العملية بنجاح.",
    "data": {
        "items": [
            {
                "id": 1,
                "user": {
                    "id": 5,
                    "name": "أحمد محمد",
                    "email": "ahmed@example.com",
                    "phone": "+966501234567"
                },
                "name": "سلتي الأسبوعية",
                "category": {
                    "id": 2,
                    "name": "خضروات"
                },
                "schedule": {
                    "id": 1,
                    "name": "أسبوعي",
                    "interval_days": 7
                },
                "is_active": true,
                "start_date": "2026-02-01",
                "next_run_date": "2026-02-15",
                "items_count": 5,
                "total_price": 250.00,
                "discount_amount": 25.00,
                "final_price": 225.00,
                "created_at": "2026-02-01 10:00:00",
                "updated_at": "2026-02-10 15:30:00"
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 5,
            "per_page": 10,
            "total": 48
        }
    }
}
```

---

### 2. Show Single User Basket Schedule
**GET** `/api/admin/user-basket-schedules/{id}`

عرض تفاصيل سلة مستخدم محددة مع جميع المنتجات.

**Response:**
```json
{
    "status": true,
    "message": "تمت العملية بنجاح.",
    "data": {
        "id": 1,
        "user": {
            "id": 5,
            "name": "أحمد محمد",
            "email": "ahmed@example.com",
            "phone": "+966501234567",
            "image": "http://..."
        },
        "name": "سلتي الأسبوعية",
        "category": {
            "id": 2,
            "name": "خضروات",
            "image": "http://..."
        },
        "schedule": {
            "id": 1,
            "name": "أسبوعي",
            "interval_days": 7,
            "discount_type": "percentage",
            "discount_value": 10,
            "is_active": true
        },
        "is_active": true,
        "start_date": "2026-02-01",
        "next_run_date": "2026-02-15",
        "items": [
            {
                "id": 10,
                "product": {
                    "id": 25,
                    "name": "طماطم",
                    "image": "http://..."
                },
                "variant": {
                    "id": 50,
                    "shop_id": 3,
                    "price": 15.00,
                    "quantity": 100
                },
                "quantity": 2,
                "unit_price": 15.00,
                "subtotal": 30.00,
                "created_at": "2026-02-01 10:00:00",
                "updated_at": "2026-02-01 10:00:00"
            },
            {
                "id": 11,
                "product": {
                    "id": 26,
                    "name": "خيار",
                    "image": "http://..."
                },
                "variant": {
                    "id": 51,
                    "shop_id": 3,
                    "price": 12.00,
                    "quantity": 80
                },
                "quantity": 3,
                "unit_price": 12.00,
                "subtotal": 36.00,
                "created_at": "2026-02-01 10:00:00",
                "updated_at": "2026-02-01 10:00:00"
            }
        ],
        "pricing": {
            "items_count": 5,
            "total_price": 250.00,
            "discount_type": "percentage",
            "discount_value": 10,
            "discount_amount": 25.00,
            "final_price": 225.00
        },
        "created_at": "2026-02-01 10:00:00",
        "updated_at": "2026-02-10 15:30:00"
    }
}
```

---

### 3. Get Statistics
**GET** `/api/admin/user-basket-schedules/statistics`

الحصول على إحصائيات عامة عن سلال المستخدمين.

**Response:**
```json
{
    "status": true,
    "message": "تمت العملية بنجاح.",
    "data": {
        "total": 150,
        "active": 120,
        "inactive": 30
    }
}
```

---

### 4. Get User Basket Schedules by User
**GET** `/api/admin/user-basket-schedules/by-user/{userId}`

عرض جميع سلال مستخدم محدد.

**Example:**
```
GET /api/admin/user-basket-schedules/by-user/5
```

**Response:**
```json
{
    "status": true,
    "message": "تمت العملية بنجاح.",
    "data": [
        {
            "id": 1,
            "user": {...},
            "name": "سلتي الأسبوعية",
            ...
        },
        {
            "id": 2,
            "user": {...},
            "name": "سلة الفواكه",
            ...
        }
    ]
}
```

---

### 5. Get User Basket Schedules by Schedule
**GET** `/api/admin/user-basket-schedules/by-schedule/{scheduleId}`

عرض جميع سلال المستخدمين التي تستخدم جدولة محددة.

**Example:**
```
GET /api/admin/user-basket-schedules/by-schedule/1
```

**Response:**
```json
{
    "status": true,
    "message": "تمت العملية بنجاح.",
    "data": [
        {
            "id": 1,
            "user": {...},
            "schedule": {
                "id": 1,
                "name": "أسبوعي",
                "interval_days": 7
            },
            ...
        }
    ]
}
```

---

## Use Cases

### 1. مراقبة اشتراكات المستخدمين
```
GET /api/admin/user-basket-schedules?is_active=true&sort_field=created_at&sort_order=desc
```

### 2. البحث عن سلال مستخدم محدد
```
GET /api/admin/user-basket-schedules?user_id=5
```

### 3. عرض السلال حسب الفئة
```
GET /api/admin/user-basket-schedules?category_id=2
```

### 4. تصفية السلال حسب تاريخ البداية
```
GET /api/admin/user-basket-schedules?start_date_from=2026-02-01&start_date_to=2026-02-28
```

### 5. عرض السلال غير النشطة
```
GET /api/admin/user-basket-schedules?is_active=false
```

---

## Data Structure

### UserBasketSchedule
```
- id: معرف السلة
- user_id: معرف المستخدم
- category_id: معرف الفئة
- schedule_id: معرف الجدولة
- name: اسم السلة (اختياري)
- is_active: حالة السلة (نشط/غير نشط)
- start_date: تاريخ بداية الاشتراك
- next_run_date: تاريخ التوصيل القادم (محسوب تلقائياً)
```

### UserBasketScheduleItem
```
- id: معرف العنصر
- user_basket_schedule_id: معرف السلة
- product_id: معرف المنتج
- shop_product_variant_id: معرف المنتج في المتجر
- quantity: الكمية
- price: السعر (محسوب من shop_product_variant)
```

### Schedule
```
- id: معرف الجدولة
- name: اسم الجدولة (متعدد اللغات)
- interval_days: عدد الأيام بين كل توصيل
- discount_type: نوع الخصم (percentage/fixed)
- discount_value: قيمة الخصم
- is_active: حالة الجدولة
```

---

## ملاحظات مهمة

1. **Read-Only**: هذه الـ API للعرض فقط، لا يمكن للأدمن تعديل أو حذف سلال المستخدمين
2. **next_run_date**: يُحسب تلقائياً بناءً على `start_date` و `interval_days`
3. **Pricing**: يُحسب تلقائياً من أسعار المنتجات والخصومات
4. **Filtering**: يمكن الجمع بين عدة فلاتر في نفس الطلب

---

## الملفات المنشأة

```
app/Services/Admin/UserBasketScheduleService.php
app/Http/Controllers/Admin/UserBasketSchedule/UserBasketScheduleController.php
app/Http/Requests/Admin/UserBasketSchedule/FilterRequest.php
app/Http/Resources/Admin/UserBasketSchedule/AllResource.php
app/Http/Resources/Admin/UserBasketSchedule/OneResource.php
app/Http/Resources/Admin/UserBasketSchedule/ItemResource.php
```

---

## Routes Summary

```php
GET  /api/admin/user-basket-schedules                    - List all
GET  /api/admin/user-basket-schedules/statistics         - Statistics
GET  /api/admin/user-basket-schedules/by-user/{userId}   - By user
GET  /api/admin/user-basket-schedules/by-schedule/{id}   - By schedule
GET  /api/admin/user-basket-schedules/{id}               - Show one
```

جميع الـ routes محمية بـ `auth:admin` middleware.
