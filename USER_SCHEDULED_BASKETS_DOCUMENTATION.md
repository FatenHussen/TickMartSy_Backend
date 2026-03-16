# User Scheduled Baskets API Documentation
# توثيق واجهات برمجة التطبيقات للسلل المجدولة للمستخدم

## Table of Contents | جدول المحتويات

1. [Overview | نظرة عامة](#overview)
2. [List Scheduled Baskets | عرض السلل المجدولة](#list-scheduled-baskets)
3. [Get Scheduled Basket Details | تفاصيل السلة المجدولة](#get-scheduled-basket-details)
4. [Create Scheduled Basket | إنشاء سلة مجدولة](#create-scheduled-basket)
5. [Update Scheduled Basket | تعديل السلة المجدولة](#update-scheduled-basket)
6. [Delete Scheduled Basket | حذف السلة المجدولة](#delete-scheduled-basket)
7. [Pause Scheduled Basket | إيقاف السلة المجدولة](#pause-scheduled-basket)
8. [Resume Scheduled Basket | استئناف السلة المجدولة](#resume-scheduled-basket)
9. [Pause Admin Basket Subscription | إيقاف اشتراك السلة من الأدمن](#pause-admin-basket-subscription)
10. [Resume Admin Basket Subscription | استئناف اشتراك السلة من الأدمن](#resume-admin-basket-subscription)

---

## Overview | نظرة عامة

**Description:**
The User Scheduled Baskets API allows users to manage their recurring basket subscriptions. Users can create, update, pause, resume, and delete scheduled baskets that will be automatically processed based on a schedule.

**الوصف:**
واجهة برمجة التطبيقات للسلل المجدولة تسمح للمستخدمين بإدارة اشتراكاتهم في السلل المتكررة. يمكن للمستخدمين إنشاء وتعديل وإيقاف واستئناف وحذف السلل المجدولة التي سيتم معالجتها تلقائياً بناءً على جدول زمني.

**Base URL:** `/api/user`

**Authentication:** Required (Bearer Token)  
**المصادقة:** مطلوبة (رمز Bearer)

---

## List Scheduled Baskets | عرض السلل المجدولة

### Endpoint | نقطة النهاية
```
GET /api/user/scheduled-baskets
```

### Route Name | اسم المسار
```php
user.scheduled-baskets.index
```

### Controller | المتحكم
```php
App\Http\Controllers\User\Basket\UserBasketScheduleController@index
```

### Service | الخدمة
```php
App\Services\User\UserBasketScheduleService@getAll()
```

### Model | النموذج
```php
App\Models\UserBasketSchedule
```

### Request Parameters | معاملات الطلب

| Parameter | Type | Required | Description (EN) | الوصف (AR) |
|-----------|------|----------|------------------|------------|
| page | integer | No | Page number for pagination | رقم الصفحة للترقيم |
| per_page | integer | No | Items per page (default: 15) | عدد العناصر في الصفحة |
| search | string | No | Search by name or ID | البحث بالاسم أو المعرف |
| sort_by | string | No | Sort field (id, created_at) | حقل الترتيب |
| sort_order | string | No | Sort order (asc, desc) | اتجاه الترتيب |

### Response Resource | مورد الاستجابة
```php
App\Http\Resources\UserBasketSchedule\AllResource
```

### Response Example | مثال الاستجابة

```json
{
  "status": true,
  "message": "تم جلب البيانات بنجاح",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Weekly Groceries",
        "image": "https://example.com/storage/categories/groceries.jpg",
        "num_varieties": 5,
        "is_paused": false,
        "original_price": 150.00,
        "original_price_converted": 40.50,
        "discount_value": 10,
        "discount_type": "percent",
        "discount_amount": 15.00,
        "discount_amount_converted": 4.05,
        "final_price": 135.00,
        "final_price_converted": 36.45,
        "next_run_date": "2026-03-24"
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 1
    }
  }
}
```

### Notes | ملاحظات
- Only returns active scheduled baskets for the authenticated user
- يعرض فقط السلل المجدولة النشطة للمستخدم المصادق عليه
- Includes currency conversion based on user's preferred currency
- يتضمن تحويل العملة بناءً على عملة المستخدم المفضلة

---

## Get Scheduled Basket Details | تفاصيل السلة المجدولة

### Endpoint | نقطة النهاية
```
GET /api/user/scheduled-baskets/{id}
```

### Route Name | اسم المسار
```php
user.scheduled-baskets.show
```

### Controller | المتحكم
```php
App\Http\Controllers\User\Basket\UserBasketScheduleController@show
```

### Service | الخدمة
```php
App\Services\User\UserBasketScheduleService@getOne($id)
```

### URL Parameters | معاملات الرابط

| Parameter | Type | Required | Description (EN) | الوصف (AR) |
|-----------|------|----------|------------------|------------|
| id | integer | Yes | Scheduled basket ID | معرف السلة المجدولة |

### Response Resource | مورد الاستجابة
```php
App\Http\Resources\UserBasketSchedule\OneResource
```

### Relations Loaded | العلاقات المحملة
- `schedule` - Schedule details | تفاصيل الجدول الزمني
- `items.product` - Product details for each item | تفاصيل المنتج لكل عنصر
- `items.variant` - Variant details for each item | تفاصيل المتغير لكل عنصر

### Response Example | مثال الاستجابة

```json
{
  "status": true,
  "message": "تم جلب البيانات بنجاح",
  "data": {
    "id": 1,
    "name": "Weekly Groceries",
    "is_active": true,
    "is_paused": false,
    "paused_at": null,
    "start_date": "2026-03-17",
    "next_run_date": "2026-03-24",
    "schedule": {
      "id": 1,
      "name": "Weekly",
      "interval_days": 7,
      "discount_type": "percent",
      "discount_value": 10
    },
    "items": [
      {
        "id": 1,
        "product_id": 10,
        "shop_product_variant_id": 25,
        "quantity": 2,
        "price": 50.00,
        "product": {
          "id": 10,
          "name": "Fresh Milk",
          "image": "https://example.com/storage/products/milk.jpg"
        },
        "variant": {
          "id": 25,
          "attributes": "1L"
        }
      }
    ]
  }
}
```

---

## Create Scheduled Basket | إنشاء سلة مجدولة

### Endpoint | نقطة النهاية
```
POST /api/user/scheduled-baskets
```

### Route Name | اسم المسار
```php
user.scheduled-baskets.store
```

### Controller | المتحكم
```php
App\Http\Controllers\User\Basket\UserBasketScheduleController@store
```

### Service | الخدمة
```php
App\Services\User\UserBasketScheduleService@create($data)
```

### Request Class | فئة الطلب
```php
App\Http\Requests\User\BasketSchedule\StoreRequest
```

### Request Body | جسم الطلب

| Field | Type | Required | Validation | Description (EN) | الوصف (AR) |
|-------|------|----------|------------|------------------|------------|
| name | string | Yes | max:255 | Basket name | اسم السلة |
| schedule_id | integer | Yes | exists:schedules,id | Schedule ID | معرف الجدول الزمني |
| start_date | date | Yes | after_or_equal:today | Start date | تاريخ البدء |
| is_active | boolean | No | - | Active status (default: true) | حالة النشاط |
| items | array | Yes | min:1 | Array of basket items | مصفوفة عناصر السلة |
| items.*.product_id | integer | Yes | exists:products,id | Product ID | معرف المنتج |
| items.*.shop_product_variant_id | integer | No | exists:shop_product_variants,id | Shop variant ID | معرف متغير المتجر |
| items.*.quantity | integer | Yes | min:1 | Quantity | الكمية |

### Request Example | مثال الطلب

```json
{
  "name": "Weekly Groceries",
  "schedule_id": 1,
  "start_date": "2026-03-17",
  "is_active": true,
  "items": [
    {
      "product_id": 10,
      "shop_product_variant_id": 25,
      "quantity": 2
    },
    {
      "product_id": 15,
      "shop_product_variant_id": 30,
      "quantity": 1
    }
  ]
}
```

### Response Example | مثال الاستجابة

```json
{
  "status": true,
  "message": "تم إنشاء السلة المجدولة بنجاح",
  "data": {
    "id": 1,
    "name": "Weekly Groceries",
    "is_active": true,
    "is_paused": false,
    "start_date": "2026-03-17",
    "next_run_date": "2026-03-24",
    "schedule": {
      "id": 1,
      "name": "Weekly",
      "interval_days": 7
    },
    "items": [...]
  }
}
```

### Database Transaction | معاملة قاعدة البيانات
- Creates the scheduled basket record | ينشئ سجل السلة المجدولة
- Creates all basket items in a single transaction | ينشئ جميع عناصر السلة في معاملة واحدة
- Automatically sets user_id from authenticated user | يضبط user_id تلقائياً من المستخدم المصادق عليه

---

## Update Scheduled Basket | تعديل السلة المجدولة

### Endpoint | نقطة النهاية
```
PUT /api/user/scheduled-baskets/{id}
```

### Route Name | اسم المسار
```php
user.scheduled-baskets.update
```

### Controller | المتحكم
```php
App\Http\Controllers\User\Basket\UserBasketScheduleController@update
```

### Service | الخدمة
```php
App\Services\User\UserBasketScheduleService@update($id, $data)
```

### Request Class | فئة الطلب
```php
App\Http\Requests\User\BasketSchedule\UpdateRequest
```

### URL Parameters | معاملات الرابط

| Parameter | Type | Required | Description (EN) | الوصف (AR) |
|-----------|------|----------|------------------|------------|
| id | integer | Yes | Scheduled basket ID | معرف السلة المجدولة |

### Request Body | جسم الطلب

| Field | Type | Required | Validation | Description (EN) | الوصف (AR) |
|-------|------|----------|------------|------------------|------------|
| name | string | No | max:255 | Basket name | اسم السلة |
| schedule_id | integer | No | exists:schedules,id | Schedule ID | معرف الجدول الزمني |
| start_date | date | No | after_or_equal:today | Start date | تاريخ البدء |
| is_active | boolean | No | - | Active status | حالة النشاط |
| items | array | No | min:1 | Array of basket items | مصفوفة عناصر السلة |
| items.*.id | integer | No | - | Item ID (for update) | معرف العنصر (للتحديث) |
| items.*.product_id | integer | No | exists:products,id | Product ID | معرف المنتج |
| items.*.shop_product_variant_id | integer | No | exists:shop_product_variants,id | Shop variant ID | معرف متغير المتجر |
| items.*.quantity | integer | No | min:1 | Quantity | الكمية |

### Request Example | مثال الطلب

```json
{
  "name": "Updated Weekly Groceries",
  "items": [
    {
      "id": 1,
      "quantity": 3
    },
    {
      "product_id": 20,
      "shop_product_variant_id": 40,
      "quantity": 2
    }
  ]
}
```

### Update Logic | منطق التحديث

**Items Update Strategy:**
- If item has `id` and exists: Update the item | إذا كان للعنصر `id` وموجود: يحدث العنصر
- If item has no `id`: Create new item | إذا لم يكن للعنصر `id`: ينشئ عنصر جديد
- Items not included in request: Deleted | العناصر غير المضمنة في الطلب: تحذف

**استراتيجية تحديث العناصر:**
- العناصر الموجودة في الطلب مع معرف: يتم تحديثها
- العناصر الجديدة بدون معرف: يتم إنشاؤها
- العناصر القديمة غير الموجودة في الطلب: يتم حذفها

### Response Example | مثال الاستجابة

```json
{
  "status": true,
  "message": "تم تحديث السلة المجدولة بنجاح",
  "data": {
    "id": 1,
    "name": "Updated Weekly Groceries",
    "is_active": true,
    "items": [...]
  }
}
```

---

## Delete Scheduled Basket | حذف السلة المجدولة

### Endpoint | نقطة النهاية
```
DELETE /api/user/scheduled-baskets/{id}
```

### Route Name | اسم المسار
```php
user.scheduled-baskets.destroy
```

### Controller | المتحكم
```php
App\Http\Controllers\User\Basket\UserBasketScheduleController@destroy
```

### Service | الخدمة
```php
App\Services\User\UserBasketScheduleService@delete($id)
```

### URL Parameters | معاملات الرابط

| Parameter | Type | Required | Description (EN) | الوصف (AR) |
|-----------|------|----------|------------------|------------|
| id | integer | Yes | Scheduled basket ID | معرف السلة المجدولة |

### Response Example | مثال الاستجابة

```json
{
  "status": true,
  "message": "تم حذف السلة المجدولة بنجاح",
  "data": true
}
```

### Notes | ملاحظات
- Soft deletes the basket and all its items | يحذف السلة وجميع عناصرها حذفاً ناعماً
- Only the owner can delete their scheduled basket | يمكن للمالك فقط حذف سلته المجدولة

---

## Pause Scheduled Basket | إيقاف السلة المجدولة

### Endpoint | نقطة النهاية
```
POST /api/user/scheduled-baskets/{id}/pause
```

### Route Name | اسم المسار
```php
user.scheduled-baskets.pause
```

### Controller | المتحكم
```php
App\Http\Controllers\User\Basket\UserBasketScheduleController@pause
```

### Service | الخدمة
```php
App\Services\User\UserBasketScheduleService@pause($id)
```

### URL Parameters | معاملات الرابط

| Parameter | Type | Required | Description (EN) | الوصف (AR) |
|-----------|------|----------|------------------|------------|
| id | integer | Yes | Scheduled basket ID | معرف السلة المجدولة |

### Description | الوصف

**English:**
Pauses a scheduled basket by setting the `paused_at` timestamp. When paused, the basket will not be processed automatically until it is resumed.

**العربية:**
يوقف السلة المجدولة عن طريق تعيين الطابع الزمني `paused_at`. عند الإيقاف، لن تتم معالجة السلة تلقائياً حتى يتم استئنافها.

### Response Example | مثال الاستجابة

```json
{
  "status": true,
  "message": "تم إيقاف السلة المجدولة بنجاح",
  "data": {
    "id": 1,
    "name": "Weekly Groceries",
    "is_active": true,
    "is_paused": true,
    "paused_at": "2026-03-17 10:30:00",
    "start_date": "2026-03-17",
    "next_run_date": "2026-03-24",
    "schedule": {...},
    "items": [...]
  }
}
```

### Database Changes | التغييرات في قاعدة البيانات
- Sets `paused_at` to current timestamp | يضبط `paused_at` على الطابع الزمني الحالي
- Basket remains active but won't be processed | تبقى السلة نشطة لكن لن تتم معالجتها

---

## Resume Scheduled Basket | استئناف السلة المجدولة

### Endpoint | نقطة النهاية
```
POST /api/user/scheduled-baskets/{id}/resume
```

### Route Name | اسم المسار
```php
user.scheduled-baskets.resume
```

### Controller | المتحكم
```php
App\Http\Controllers\User\Basket\UserBasketScheduleController@resume
```

### Service | الخدمة
```php
App\Services\User\UserBasketScheduleService@resume($id)
```

### URL Parameters | معاملات الرابط

| Parameter | Type | Required | Description (EN) | الوصف (AR) |
|-----------|------|----------|------------------|------------|
| id | integer | Yes | Scheduled basket ID | معرف السلة المجدولة |

### Description | الوصف

**English:**
Resumes a paused scheduled basket by clearing the `paused_at` timestamp. The basket will resume automatic processing according to its schedule.

**العربية:**
يستأنف السلة المجدولة الموقوفة عن طريق مسح الطابع الزمني `paused_at`. ستستأنف السلة المعالجة التلقائية وفقاً لجدولها الزمني.

### Response Example | مثال الاستجابة

```json
{
  "status": true,
  "message": "تم استئناف السلة المجدولة بنجاح",
  "data": {
    "id": 1,
    "name": "Weekly Groceries",
    "is_active": true,
    "is_paused": false,
    "paused_at": null,
    "start_date": "2026-03-17",
    "next_run_date": "2026-03-24",
    "schedule": {...},
    "items": [...]
  }
}
```

### Database Changes | التغييرات في قاعدة البيانات
- Sets `paused_at` to null | يضبط `paused_at` على null
- Basket resumes automatic processing | تستأنف السلة المعالجة التلقائية

---

## Database Schema | مخطط قاعدة البيانات

### Table: `user_basket_schedules`

| Column | Type | Nullable | Description (EN) | الوصف (AR) |
|--------|------|----------|------------------|------------|
| id | bigint | No | Primary key | المفتاح الأساسي |
| user_id | bigint | No | User ID (FK) | معرف المستخدم |
| schedule_id | bigint | No | Schedule ID (FK) | معرف الجدول الزمني |
| name | string | No | Basket name | اسم السلة |
| is_active | boolean | No | Active status | حالة النشاط |
| start_date | date | No | Start date | تاريخ البدء |
| next_run_date | date | Yes | Next run date (computed) | تاريخ التشغيل التالي |
| paused_at | timestamp | Yes | Pause timestamp | طابع زمني للإيقاف |
| created_at | timestamp | No | Creation timestamp | طابع زمني للإنشاء |
| updated_at | timestamp | No | Update timestamp | طابع زمني للتحديث |

### Table: `user_basket_schedule_items`

| Column | Type | Nullable | Description (EN) | الوصف (AR) |
|--------|------|----------|------------------|------------|
| id | bigint | No | Primary key | المفتاح الأساسي |
| user_basket_schedule_id | bigint | No | Basket schedule ID (FK) | معرف السلة المجدولة |
| product_id | bigint | No | Product ID (FK) | معرف المنتج |
| shop_product_variant_id | bigint | Yes | Shop variant ID (FK) | معرف متغير المتجر |
| quantity | integer | No | Quantity | الكمية |
| price | decimal | No | Price at time of creation | السعر وقت الإنشاء |
| created_at | timestamp | No | Creation timestamp | طابع زمني للإنشاء |
| updated_at | timestamp | No | Update timestamp | طابع زمني للتحديث |

---

## Error Responses | استجابات الأخطاء

### 404 Not Found | غير موجود

```json
{
  "status": false,
  "message": "السلة المجدولة غير موجودة",
  "data": []
}
```

### 422 Validation Error | خطأ في التحقق

```json
{
  "status": false,
  "message": "خطأ في البيانات المدخلة",
  "data": {
    "name": ["حقل الاسم مطلوب"],
    "items": ["يجب أن يحتوي على عنصر واحد على الأقل"]
  }
}
```

### 401 Unauthorized | غير مصرح

```json
{
  "status": false,
  "message": "غير مصرح بالوصول",
  "data": []
}
```

---

## Related Models | النماذج ذات الصلة

### UserBasketSchedule
```php
App\Models\UserBasketSchedule
```

**Relations:**
- `user()` - BelongsTo User
- `schedule()` - BelongsTo Schedule
- `items()` - HasMany UserBasketScheduleItem

### UserBasketScheduleItem
```php
App\Models\UserBasketScheduleItem
```

**Relations:**
- `userBasketSchedule()` - BelongsTo UserBasketSchedule
- `product()` - BelongsTo Product
- `variant()` - BelongsTo ShopProductVariant

### Schedule
```php
App\Models\Schedule
```

**Fields:**
- `name` - Schedule name | اسم الجدول
- `interval_days` - Days between runs | الأيام بين التشغيلات
- `discount_type` - Discount type (percent/fixed) | نوع الخصم
- `discount_value` - Discount value | قيمة الخصم

---

## Business Logic | المنطق التجاري

### Next Run Date Calculation | حساب تاريخ التشغيل التالي

The `next_run_date` is automatically calculated based on:
- `start_date` - The initial start date
- `schedule.interval_days` - Number of days between runs
- Current date

**Formula:**
```
next_run_date = start_date + (cycles * interval_days)
where cycles = ceil((today - start_date) / interval_days)
```

**الصيغة:**
```
تاريخ_التشغيل_التالي = تاريخ_البدء + (الدورات * أيام_الفاصل)
حيث الدورات = ceil((اليوم - تاريخ_البدء) / أيام_الفاصل)
```

### Discount Calculation | حساب الخصم

Discount is applied from the schedule:
- **Percentage:** `discount_amount = total_price * (discount_value / 100)`
- **Fixed:** `discount_amount = min(discount_value, total_price)`

**حساب الخصم:**
- **نسبة مئوية:** `مبلغ_الخصم = السعر_الإجمالي * (قيمة_الخصم / 100)`
- **ثابت:** `مبلغ_الخصم = min(قيمة_الخصم، السعر_الإجمالي)`

---

## Notes | ملاحظات

1. **Authentication Required:** All endpoints require user authentication
   **المصادقة مطلوبة:** جميع نقاط النهاية تتطلب مصادقة المستخدم

2. **User Isolation:** Users can only access their own scheduled baskets
   **عزل المستخدم:** يمكن للمستخدمين الوصول فقط إلى سلالهم المجدولة

3. **Currency Conversion:** Prices are automatically converted based on user's currency preference
   **تحويل العملة:** يتم تحويل الأسعار تلقائياً بناءً على تفضيل عملة المستخدم

4. **Soft Deletes:** Deleted baskets are soft-deleted and can be restored
   **الحذف الناعم:** السلال المحذوفة يتم حذفها بشكل ناعم ويمكن استعادتها

5. **Transaction Safety:** Create and update operations use database transactions
   **أمان المعاملات:** عمليات الإنشاء والتحديث تستخدم معاملات قاعدة البيانات

---

## Pause Admin Basket Subscription | إيقاف اشتراك السلة من الأدمن

### Endpoint | نقطة النهاية
```
POST /api/user/my-baskets/{basket}/pause-subscription
```

### Route Name | اسم المسار
```php
user.my-baskets.pause-subscription
```

### Controller | المتحكم
```php
App\Http\Controllers\User\MyBasket\MyBasketController@pauseSubscription
```

### Service | الخدمة
```php
App\Services\User\MyBasketService@pauseSubscriptionBasket($userId, $basketId)
```

### Description | الوصف

**English:**
Pauses a user's subscription to an admin-created scheduled basket. This is different from user-created scheduled baskets. When a user subscribes to an admin basket with a schedule, this endpoint allows them to pause that subscription.

**العربية:**
يوقف اشتراك المستخدم في سلة مجدولة أنشأها الأدمن. هذا يختلف عن السلل المجدولة التي ينشئها المستخدم. عندما يشترك المستخدم في سلة من الأدمن مع جدول زمني، تسمح هذه النقطة بإيقاف هذا الاشتراك.

### URL Parameters | معاملات الرابط

| Parameter | Type | Required | Description (EN) | الوصف (AR) |
|-----------|------|----------|------------------|------------|
| basket | integer | Yes | Admin basket ID | معرف السلة من الأدمن |

### How It Works | كيف يعمل

**English:**
1. Finds the order record where:
   - `user_id` matches authenticated user
   - `basket_id` matches the provided basket ID
   - `cart_type` is `schedule_admin_cart`
2. Sets the `pause_at` timestamp to current time
3. The subscription will not be processed until resumed

**العربية:**
1. يبحث عن سجل الطلب حيث:
   - `user_id` يطابق المستخدم المصادق عليه
   - `basket_id` يطابق معرف السلة المقدم
   - `cart_type` هو `schedule_admin_cart`
2. يضبط الطابع الزمني `pause_at` على الوقت الحالي
3. لن تتم معالجة الاشتراك حتى يتم استئنافه

### Database Changes | التغييرات في قاعدة البيانات

**Table:** `orders`

Updates the order record:
```sql
UPDATE orders 
SET pause_at = NOW() 
WHERE basket_id = ? 
  AND user_id = ? 
  AND cart_type = 'schedule_admin_cart'
```

### Response Example | مثال الاستجابة

```json
{
  "message": "Basket paused successfully"
}
```

### Response (Arabic) | الاستجابة (بالعربية)

```json
{
  "message": "تم إيقاف السلة بنجاح"
}
```

### Notes | ملاحظات

**English:**
- This endpoint is specifically for admin-created scheduled baskets
- The basket itself is not modified, only the user's subscription order
- Multiple users can subscribe to the same admin basket independently
- Each user can pause/resume their own subscription

**العربية:**
- هذه النقطة مخصصة للسلل المجدولة التي أنشأها الأدمن
- السلة نفسها لا يتم تعديلها، فقط طلب اشتراك المستخدم
- يمكن لعدة مستخدمين الاشتراك في نفس سلة الأدمن بشكل مستقل
- كل مستخدم يمكنه إيقاف/استئناف اشتراكه الخاص

---

## Resume Admin Basket Subscription | استئناف اشتراك السلة من الأدمن

### Endpoint | نقطة النهاية
```
POST /api/user/my-baskets/{basket}/resume-subscription
```

### Route Name | اسم المسار
```php
user.my-baskets.resume-subscription
```

### Controller | المتحكم
```php
App\Http\Controllers\User\MyBasket\MyBasketController@resumeSubscription
```

### Service | الخدمة
```php
App\Services\User\MyBasketService@resumeSubscriptionBasket($userId, $basketId)
```

### Description | الوصف

**English:**
Resumes a paused subscription to an admin-created scheduled basket. This clears the pause timestamp and allows the subscription to be processed again according to its schedule.

**العربية:**
يستأنف اشتراكاً موقوفاً في سلة مجدولة أنشأها الأدمن. هذا يمسح الطابع الزمني للإيقاف ويسمح بمعالجة الاشتراك مرة أخرى وفقاً لجدوله الزمني.

### URL Parameters | معاملات الرابط

| Parameter | Type | Required | Description (EN) | الوصف (AR) |
|-----------|------|----------|------------------|------------|
| basket | integer | Yes | Admin basket ID | معرف السلة من الأدمن |

### How It Works | كيف يعمل

**English:**
1. Finds the order record where:
   - `user_id` matches authenticated user
   - `basket_id` matches the provided basket ID
   - `cart_type` is `schedule_admin_cart`
2. Sets the `pause_at` timestamp to null
3. The subscription will resume automatic processing

**العربية:**
1. يبحث عن سجل الطلب حيث:
   - `user_id` يطابق المستخدم المصادق عليه
   - `basket_id` يطابق معرف السلة المقدم
   - `cart_type` هو `schedule_admin_cart`
2. يضبط الطابع الزمني `pause_at` على null
3. سيستأنف الاشتراك المعالجة التلقائية

### Database Changes | التغييرات في قاعدة البيانات

**Table:** `orders`

Updates the order record:
```sql
UPDATE orders 
SET pause_at = NULL 
WHERE basket_id = ? 
  AND user_id = ? 
  AND cart_type = 'schedule_admin_cart'
```

### Response Example | مثال الاستجابة

```json
{
  "message": "Basket resumed successfully"
}
```

### Response (Arabic) | الاستجابة (بالعربية)

```json
{
  "message": "تم استئناف السلة بنجاح"
}
```

### Notes | ملاحظات

**English:**
- This endpoint is specifically for admin-created scheduled baskets
- The basket itself is not modified, only the user's subscription order
- Resuming will allow the next scheduled delivery to proceed
- The schedule timing is based on the basket's schedule configuration

**العربية:**
- هذه النقطة مخصصة للسلل المجدولة التي أنشأها الأدمن
- السلة نفسها لا يتم تعديلها، فقط طلب اشتراك المستخدم
- الاستئناف سيسمح بالمتابعة للتسليم المجدول التالي
- توقيت الجدول يعتمد على إعدادات جدول السلة

---

## Comparison: User vs Admin Scheduled Baskets | المقارنة: السلل المجدولة للمستخدم مقابل الأدمن

### User-Created Scheduled Baskets | السلل المجدولة التي ينشئها المستخدم

**Model:** `UserBasketSchedule`  
**Table:** `user_basket_schedules`  
**Pause Field:** `paused_at` (on the basket schedule itself)  
**Endpoints:**
- `POST /api/user/scheduled-baskets/{id}/pause`
- `POST /api/user/scheduled-baskets/{id}/resume`

**Characteristics:**
- User creates and owns the basket
- User can fully customize items and schedule
- Pause affects the basket schedule directly
- Only the owner can access and modify

**الخصائص:**
- المستخدم ينشئ ويملك السلة
- المستخدم يمكنه تخصيص العناصر والجدول بالكامل
- الإيقاف يؤثر على جدول السلة مباشرة
- المالك فقط يمكنه الوصول والتعديل

---

### Admin-Created Scheduled Baskets | السلل المجدولة التي ينشئها الأدمن

**Model:** `Basket` (with `is_schedule = true`)  
**Table:** `baskets`  
**Subscription Model:** `Order` (with `cart_type = schedule_admin_cart`)  
**Pause Field:** `pause_at` (on the user's order/subscription)  
**Endpoints:**
- `POST /api/user/my-baskets/{basket}/pause-subscription`
- `POST /api/user/my-baskets/{basket}/resume-subscription`

**Characteristics:**
- Admin creates the basket template
- Multiple users can subscribe to the same basket
- Each user selects a schedule from available options
- Pause affects only the user's subscription, not the basket
- Users cannot modify basket items, only subscribe/unsubscribe

**الخصائص:**
- الأدمن ينشئ قالب السلة
- عدة مستخدمين يمكنهم الاشتراك في نفس السلة
- كل مستخدم يختار جدولاً من الخيارات المتاحة
- الإيقاف يؤثر فقط على اشتراك المستخدم، وليس السلة
- المستخدمون لا يمكنهم تعديل عناصر السلة، فقط الاشتراك/إلغاء الاشتراك

---

## Related Enums | التعدادات ذات الصلة

### CartType Enum

```php
App\Enums\CartType
```

**Values:**
- `ADMIN_CART` - Regular admin basket | سلة عادية من الأدمن
- `SCHEDULE_ADMIN_CART` - Scheduled admin basket subscription | اشتراك سلة مجدولة من الأدمن
- `USER_CART` - User's custom cart | سلة مخصصة للمستخدم

---

## Support | الدعم

For technical support or questions, please contact the development team.
للدعم الفني أو الأسئلة، يرجى الاتصال بفريق التطوير.

