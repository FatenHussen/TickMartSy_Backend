# User Subscriptions API - Admin Panel

## نظرة عامة

اشتراكات المستخدمين (User Subscriptions) تمثل اشتراكات المستخدمين في الباقات المختلفة التي توفر مزايا مثل:
- خصومات على الطلبات
- توصيل مجاني
- نقاط إضافية
- حد أقصى للطلبات الشهرية

---

## Base URL
```
/api/admin/subscriptions
```

## Authentication
```
Authorization: Bearer {admin_token}
```

---

## 1. List User Subscriptions
**GET** `/api/admin/subscriptions`

### Query Parameters
- `page` (int): Page number
- `per_page` (int): Items per page (default: 10, max: 100)
- `user_id` (int): Filter by user ID
- `package_id` (int): Filter by package ID
- `status` (string): Filter by status (active, expired, cancelled)
- `start_date_from` (date): Filter subscriptions starting from this date
- `start_date_to` (date): Filter subscriptions starting until this date
- `search` (string): Search in subscription data
- `sortField` (string): id, start_date, end_date, created_at, status
- `sortOrder` (string): asc, desc

### Response
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "user": {
          "id": 10,
          "name": "أحمد محمد",
          "email": "ahmad@example.com"
        },
        "package": {
          "id": 2,
          "name": "الباقة الذهبية",
          "price": 50.00
        },
        "status": "active",
        "start_date": "2024-01-01",
        "end_date": "2024-02-01",
        "remaining_orders": 15,
        "remaining_free_deliveries": 5,
        "is_active": true,
        "created_at": "2024-01-01 10:00:00"
      },
      {
        "id": 2,
        "user": {
          "id": 15,
          "name": "فاطمة علي",
          "email": "fatima@example.com"
        },
        "package": {
          "id": 1,
          "name": "الباقة الفضية",
          "price": 30.00
        },
        "status": "expired",
        "start_date": "2023-12-01",
        "end_date": "2024-01-01",
        "remaining_orders": 0,
        "remaining_free_deliveries": 0,
        "is_active": false,
        "created_at": "2023-12-01 12:00:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 10,
      "per_page": 10,
      "total": 100
    }
  }
}
```

---

## 2. Get Single Subscription
**GET** `/api/admin/subscriptions/{id}`

### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user": {
      "id": 10,
      "name": "أحمد محمد",
      "email": "ahmad@example.com",
      "phone": "+962791234567"
    },
    "package": {
      "id": 2,
      "name": "الباقة الذهبية",
      "price": 50.00,
      "duration_days": 30,
      "monthly_orders_limit": 20,
      "free_delivery_count": 10,
      "discount_percentage": 15,
      "points_bonus": 500
    },
    "status": "active",
    "start_date": "2024-01-01",
    "end_date": "2024-02-01",
    "remaining_orders": 15,
    "remaining_free_deliveries": 5,
    "is_active": true,
    "days_remaining": 15,
    "created_at": "2024-01-01 10:00:00",
    "updated_at": "2024-01-15 14:30:00"
  }
}
```

---

## Field Descriptions

### Subscription Fields

#### id
معرف الاشتراك الفريد

#### user
معلومات المستخدم المشترك:
- `id`: معرف المستخدم
- `name`: اسم المستخدم
- `email`: البريد الإلكتروني
- `phone`: رقم الهاتف (في OneResource فقط)

#### package
معلومات الباقة المشترك بها:
- `id`: معرف الباقة
- `name`: اسم الباقة
- `price`: سعر الباقة
- `duration_days`: مدة الباقة بالأيام
- `monthly_orders_limit`: الحد الأقصى للطلبات الشهرية
- `free_delivery_count`: عدد التوصيلات المجانية
- `discount_percentage`: نسبة الخصم على الطلبات
- `points_bonus`: نقاط إضافية عند الاشتراك

#### status
حالة الاشتراك:
- `active`: نشط
- `expired`: منتهي
- `cancelled`: ملغي

#### start_date
تاريخ بداية الاشتراك

#### end_date
تاريخ نهاية الاشتراك

#### remaining_orders
عدد الطلبات المتبقية في الاشتراك
- `null`: غير محدود
- `0`: لا يوجد طلبات متبقية
- `> 0`: عدد الطلبات المتبقية

#### remaining_free_deliveries
عدد التوصيلات المجانية المتبقية

#### is_active
هل الاشتراك نشط حالياً؟
- يتحقق من:
  - `status === 'active'`
  - التاريخ الحالي بين `start_date` و `end_date`

#### days_remaining
عدد الأيام المتبقية حتى انتهاء الاشتراك
- `null`: إذا لم يكن هناك تاريخ انتهاء
- `< 0`: الاشتراك منتهي
- `>= 0`: عدد الأيام المتبقية

---

## Filter Examples

### Example 1: Get Active Subscriptions
```
GET /api/admin/subscriptions?status=active&sortField=end_date&sortOrder=asc
```

### Example 2: Get User's Subscriptions
```
GET /api/admin/subscriptions?user_id=10&sortField=created_at&sortOrder=desc
```

### Example 3: Get Subscriptions by Package
```
GET /api/admin/subscriptions?package_id=2&status=active
```

### Example 4: Get Subscriptions by Date Range
```
GET /api/admin/subscriptions?start_date_from=2024-01-01&start_date_to=2024-01-31
```

### Example 5: Get Expired Subscriptions
```
GET /api/admin/subscriptions?status=expired&sortField=end_date&sortOrder=desc
```

---

## Use Cases

### 1. عرض جميع الاشتراكات النشطة
```
GET /api/admin/subscriptions?status=active&per_page=50
```

### 2. البحث عن اشتراكات مستخدم معين
```
GET /api/admin/subscriptions?user_id=10
```

### 3. عرض الاشتراكات التي ستنتهي قريباً
```
GET /api/admin/subscriptions?status=active&sortField=end_date&sortOrder=asc&per_page=20
```

### 4. عرض اشتراكات باقة معينة
```
GET /api/admin/subscriptions?package_id=2&sortField=created_at&sortOrder=desc
```

### 5. تصفية الاشتراكات حسب فترة زمنية
```
GET /api/admin/subscriptions?start_date_from=2024-01-01&start_date_to=2024-01-31&status=active
```

---

## Subscription Status Flow

```
┌─────────────┐
│   active    │ ← الاشتراك نشط ويمكن استخدامه
└──────┬──────┘
       │
       ├─────────────────────────────────┐
       │                                 │
       ▼                                 ▼
┌─────────────┐                   ┌─────────────┐
│   expired   │                   │  cancelled  │
│ (انتهى تلقائياً)                │ (ألغاه المستخدم)
└─────────────┘                   └─────────────┘
```

### Status Transitions:
1. **active → expired**: عندما يصل `end_date` أو `remaining_orders = 0`
2. **active → cancelled**: عندما يلغي المستخدم الاشتراك

---

## Package Benefits

### الباقة الفضية (مثال)
```json
{
  "name": "الباقة الفضية",
  "price": 30.00,
  "duration_days": 30,
  "monthly_orders_limit": 10,
  "free_delivery_count": 5,
  "discount_percentage": 10,
  "points_bonus": 300
}
```

**المزايا:**
- مدة الاشتراك: 30 يوم
- حد أقصى للطلبات: 10 طلبات شهرياً
- توصيل مجاني: 5 مرات
- خصم: 10% على جميع الطلبات
- نقاط إضافية: 300 نقطة عند الاشتراك

### الباقة الذهبية (مثال)
```json
{
  "name": "الباقة الذهبية",
  "price": 50.00,
  "duration_days": 30,
  "monthly_orders_limit": 20,
  "free_delivery_count": 10,
  "discount_percentage": 15,
  "points_bonus": 500
}
```

**المزايا:**
- مدة الاشتراك: 30 يوم
- حد أقصى للطلبات: 20 طلب شهرياً
- توصيل مجاني: 10 مرات
- خصم: 15% على جميع الطلبات
- نقاط إضافية: 500 نقطة عند الاشتراك

---

## Subscription Lifecycle

### 1. إنشاء الاشتراك
عندما يشترك المستخدم في باقة:
```json
{
  "user_id": 10,
  "package_id": 2,
  "start_date": "2024-01-01",
  "end_date": "2024-02-01",
  "status": "active",
  "remaining_orders": 20,
  "remaining_free_deliveries": 10
}
```

### 2. استخدام الاشتراك
عند كل طلب:
- `remaining_orders` ينقص بمقدار 1
- `remaining_free_deliveries` ينقص بمقدار 1 (إذا استخدم توصيل مجاني)

### 3. انتهاء الاشتراك
عندما:
- `end_date` يصل
- أو `remaining_orders = 0`

يتم تحديث:
```json
{
  "status": "expired",
  "is_active": false
}
```

---

## Statistics & Reports

### إحصائيات مفيدة يمكن استخراجها:

1. **عدد الاشتراكات النشطة**
```
GET /api/admin/subscriptions?status=active
```

2. **إجمالي الإيرادات من الاشتراكات**
```
GET /api/admin/subscriptions
// ثم حساب مجموع package.price
```

3. **أكثر الباقات شعبية**
```
GET /api/admin/subscriptions?package_id=X
// تكرار لكل باقة
```

4. **الاشتراكات التي ستنتهي خلال 7 أيام**
```
GET /api/admin/subscriptions?status=active&sortField=end_date&sortOrder=asc
// ثم فلترة days_remaining <= 7
```

5. **معدل تجديد الاشتراكات**
```
// مقارنة عدد الاشتراكات الجديدة مع المنتهية
```

---

## Error Responses

### 404 Not Found
```json
{
  "success": false,
  "message": "Subscription not found"
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "user_id": ["The selected user id is invalid."],
    "package_id": ["The selected package id is invalid."],
    "status": ["The selected status is invalid."]
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

---

## Notes

### ملاحظات مهمة:

1. **Read-Only API**: هذا الـ API للعرض فقط (index, show). إنشاء وتحديث الاشتراكات يتم من خلال User API.

2. **Automatic Expiration**: الاشتراكات تنتهي تلقائياً عند الوصول لـ `end_date` أو عندما `remaining_orders = 0`.

3. **Unlimited Orders**: إذا كان `remaining_orders = null`، فهذا يعني طلبات غير محدودة.

4. **Free Deliveries**: `remaining_free_deliveries` ينقص فقط عند استخدام التوصيل المجاني.

5. **Package Benefits**: المزايا (discount, points_bonus) تطبق تلقائياً على الطلبات أثناء فترة الاشتراك النشط.

6. **Status Check**: استخدم `is_active` للتحقق من صلاحية الاشتراك، لا تعتمد على `status` فقط.

