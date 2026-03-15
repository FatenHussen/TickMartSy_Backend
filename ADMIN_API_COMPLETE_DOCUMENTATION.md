# 📚 Admin API Complete Documentation
# التوثيق الشامل لواجهات برمجة الإدارة

**Version:** 1.0  
**Last Updated:** 2026-03-16  
**Base URL:** `/api/admin`

---

## 📋 Table of Contents | جدول المحتويات

1. [Authentication | المصادقة](#authentication)
2. [Common Patterns | الأنماط المشتركة](#common-patterns)
3. [Products | المنتجات](#products)
4. [Gifts | الهدايا](#gifts)
5. [Baskets | السلل](#baskets)
6. [Reports & Exports | التقارير والتصدير](#reports)
7. [Categories | الفئات](#categories)
8. [Icons | الأيقونات](#icons)
9. [Countries | البلدان](#countries)
10. [Promotion Requests | طلبات العروض](#promotion-requests)
11. [Point Rules | قواعد النقاط](#point-rules)
12. [User Subscriptions | اشتراكات المستخدمين](#user-subscriptions)
13. [Vendor Subscriptions | اشتراكات الموردين](#vendor-subscriptions)
14. [Schedules | الجدولات](#schedules)

---

<a name="authentication"></a>
## 🔐 Authentication | المصادقة

All admin endpoints require Bearer token authentication.

### Login | تسجيل الدخول

```http
POST /api/admin/auth/login
Content-Type: application/json

{
  "email": "admin@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "your_bearer_token_here",
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com"
    }
  }
}
```

### Using the Token | استخدام الرمز

Include the token in all subsequent requests:

```http
Authorization: Bearer your_bearer_token_here
```

---

<a name="common-patterns"></a>
## 📦 Common Patterns | الأنماط المشتركة

### Standard CRUD Operations | عمليات CRUD القياسية

Most resources follow this RESTful pattern:

```
GET    /api/admin/{resource}           - List all (with pagination)
GET    /api/admin/{resource}/{id}      - Get single item
POST   /api/admin/{resource}           - Create new item
PUT    /api/admin/{resource}/{id}      - Update existing item
DELETE /api/admin/{resource}/{id}      - Delete item (soft delete)
```

### Response Structure | بنية الاستجابة

#### Success Response | استجابة النجاح
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {}
}
```

#### Error Response | استجابة الخطأ
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": ["Error description"]
  }
}
```

#### Paginated Response | استجابة مع تصفح
```json
{
  "success": true,
  "data": [],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150,
    "from": 1,
    "to": 15
  },
  "links": {
    "first": "url",
    "last": "url",
    "prev": null,
    "next": "url"
  }
}
```

### Common Query Parameters | معاملات الاستعلام المشتركة

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `page` | integer | Page number | `?page=2` |
| `per_page` | integer | Items per page (default: 15) | `?per_page=20` |
| `search` | string | Search term | `?search=burger` |
| `sort_by` | string | Sort field | `?sort_by=created_at` |
| `sort_order` | string | asc or desc | `?sort_order=desc` |
| `filter[field]` | mixed | Filter by field | `?filter[status]=active` |

### Important Notes | ملاحظات مهمة

1. **Translatable Fields**: Use `{"ar": "value", "en": "value"}` format
2. **File Uploads**: Use `multipart/form-data` encoding
3. **Dates**: ISO 8601 format (`YYYY-MM-DD` or `YYYY-MM-DD HH:MM:SS`)
4. **Soft Deletes**: Enabled on most models
5. **Permissions**: Check user permissions before operations

---

<a name="products"></a>
## 1. Products | المنتجات

### Routes | المسارات

| Method | Endpoint | Description | الوصف |
|--------|----------|-------------|--------|
| GET | `/api/admin/products` | List all products | عرض جميع المنتجات |
| GET | `/api/admin/products/{id}` | Get single product | عرض منتج واحد |
| POST | `/api/admin/products` | Create product | إنشاء منتج جديد |
| PUT | `/api/admin/products/{id}` | Update product | تحديث منتج |
| DELETE | `/api/admin/products/{id}` | Delete product | حذف منتج |
| POST | `/api/admin/products/{id}/approve` | Approve product | الموافقة على منتج |
| POST | `/api/admin/products/{id}/reject` | Reject product | رفض منتج |

### Architecture | البنية

- **Controller:** `App\Http\Controllers\Admin\Product\ProductController`
- **Service:** `App\Services\Admin\ProductService`
- **Model:** `App\Models\Product`
- **Requests:** `StoreRequest`, `UpdateRequest`, `FilterRequest`
- **Resources:** `AllResource`, `OneResource`

### Request Body | بيانات الطلب

```json
{
  "name": {"ar": "همبرغر لحم", "en": "Beef Burger"},
  "description": {"ar": "همبرغر لحم طازج", "en": "Fresh beef burger"},
  "category_id": 1,
  "brand_id": 2,
  "vendor_id": 3,
  "sku": "PROD-001",
  "price": 50.00,
  "cost_price": 35.00,
  "quantity": 100,
  "discount": 10,
  "discount_type": "percentage",
  "is_visible": true
}
```

### Extra Details with Price | التفاصيل الإضافية مع السعر

```json
{
  "extra_details": [
    {
      "detail_key": {"ar": "جبنة إضافية", "en": "Extra Cheese"},
      "detail_value": {"ar": "جبنة موزاريلا", "en": "Mozzarella"},
      "price": 5.00
    },
    {
      "detail_key": {"ar": "بيض مقلي", "en": "Fried Egg"},
      "detail_value": {"ar": "بيضة طازجة", "en": "Fresh Egg"},
      "price": 3.00
    }
  ]
}
```

### Query Parameters | معاملات الاستعلام

- `search` - Search in name, SKU, barcode
- `category_id` - Filter by category
- `vendor_id` - Filter by vendor
- `approval_status` - pending/approved/rejected
- `is_visible` - 0 or 1
- `min_price`, `max_price` - Price range

---

<a name="gifts"></a>
## 2. Gifts | الهدايا

### Routes | المسارات

| Method | Endpoint | Description | الوصف |
|--------|----------|-------------|--------|
| GET | `/api/admin/gifts` | List all gifts | عرض جميع الهدايا |
| GET | `/api/admin/gifts/{id}` | Get single gift | عرض هدية واحدة |
| POST | `/api/admin/gifts` | Create gift | إنشاء هدية |
| PUT | `/api/admin/gifts/{id}` | Update gift | تحديث هدية |
| DELETE | `/api/admin/gifts/{id}` | Delete gift | حذف هدية |

### Architecture | البنية

- **Controller:** `App\Http\Controllers\Admin\Gift\GiftController`
- **Service:** `App\Services\Admin\GiftService`
- **Model:** `App\Models\Gift`

### Request Body | بيانات الطلب

```json
{
  "name": {"ar": "قسيمة خصم 50 ريال", "en": "50 SAR Voucher"},
  "description": {"ar": "قسيمة خصم", "en": "Discount voucher"},
  "points_required": 500,
  "quantity": 100,
  "image": "file",
  "is_active": true,
  "expiry_date": "2024-12-31"
}
```

---

<a name="baskets"></a>
## 3. Baskets | السلل

### Baskets Routes | مسارات السلل

| Method | Endpoint | Description | الوصف |
|--------|----------|-------------|--------|
| GET | `/api/admin/baskets` | List all baskets | عرض جميع السلل |
| GET | `/api/admin/baskets/{id}` | Get single basket | عرض سلة واحدة |
| POST | `/api/admin/baskets` | Create basket | إنشاء سلة |
| PUT | `/api/admin/baskets/{id}` | Update basket | تحديث سلة |
| DELETE | `/api/admin/baskets/{id}` | Delete basket | حذف سلة |

### Scheduled Baskets Routes | مسارات السلل المجدولة

| Method | Endpoint | Description | الوصف |
|--------|----------|-------------|--------|
| GET | `/api/admin/scheduled-baskets` | List scheduled baskets | عرض السلل المجدولة |
| GET | `/api/admin/scheduled-baskets/{id}` | Get single | عرض سلة مجدولة |
| POST | `/api/admin/scheduled-baskets` | Create | إنشاء سلة مجدولة |
| PUT | `/api/admin/scheduled-baskets/{id}` | Update | تحديث |
| DELETE | `/api/admin/scheduled-baskets/{id}` | Delete | حذف |

### User Basket Schedules (Read-Only) | جدولات سلل المستخدمين

| Method | Endpoint | Description | الوصف |
|--------|----------|-------------|--------|
| GET | `/api/admin/user-basket-schedules` | List schedules | عرض الجدولات |
| GET | `/api/admin/user-basket-schedules/{id}` | Get single | عرض جدول واحد |

### Architecture | البنية

- **Baskets:** `BasketController`, `BasketService`, `Basket`
- **Scheduled:** `ScheduledBasketController`, `ScheduledBasketService`
- **User Schedules:** `UserBasketScheduleController`, `UserBasketScheduleService`

---

<a name="reports"></a>
## 4. Reports & Exports | التقارير والتصدير

### Reports Routes | مسارات التقارير

| Method | Endpoint | Description | الوصف |
|--------|----------|-------------|--------|
| GET | `/api/admin/reports/sales` | Sales report | تقرير المبيعات |
| GET | `/api/admin/reports/product-movement` | Product movement | حركة المنتجات |
| GET | `/api/admin/reports/vendor-performance/{id}` | Vendor performance | أداء المورد |
| GET | `/api/admin/reports/driver-performance/{id}` | Driver performance | أداء السائق |
| GET | `/api/admin/reports/sales-by-location` | Sales by location | المبيعات حسب الموقع |
| GET | `/api/admin/reports/sales-by-category` | Sales by category | المبيعات حسب الفئة |

### Export Routes (Excel) | مسارات التصدير

| Method | Endpoint | Description | الوصف |
|--------|----------|-------------|--------|
| GET | `/api/admin/reports/export/sales` | Export sales | تصدير المبيعات |
| GET | `/api/admin/reports/export/product-movement` | Export movement | تصدير الحركة |
| GET | `/api/admin/reports/export/vendor-performance/{id}` | Export vendor | تصدير أداء المورد |
| GET | `/api/admin/reports/export/driver-performance/{id}` | Export driver | تصدير أداء السائق |

### Architecture | البنية

- **Controller:** `App\Http\Controllers\Admin\Reports\ReportsController`
- **Service:** `App\Services\Admin\ReportsService`
- **Exports:** `SalesReportExport`, `ProductMovementExport`, `VendorPerformanceExport`, `DriverPerformanceExport`

### Query Parameters | معاملات الاستعلام

| Parameter | Required | Description |
|-----------|----------|-------------|
| `start_date` | Yes | Start date (YYYY-MM-DD) |
| `end_date` | Yes | End date (YYYY-MM-DD) |
| `vendor_id` | No | Filter by vendor |
| `category_id` | No | Filter by category |
| `shop_id` | No | Filter by shop |

---

<a name="categories"></a>
## 5. Categories | الفئات

### Categories Routes | مسارات الفئات

| Method | Endpoint | Description | الوصف |
|--------|----------|-------------|--------|
| GET | `/api/admin/categories` | List categories | عرض الفئات |
| GET | `/api/admin/categories/{id}` | Get single | عرض فئة واحدة |
| POST | `/api/admin/categories` | Create | إنشاء فئة |
| PUT | `/api/admin/categories/{id}` | Update | تحديث فئة |
| DELETE | `/api/admin/categories/{id}` | Delete | حذف فئة |

### Category Attributes Routes | مسارات خصائص الفئات

| Method | Endpoint | Description | الوصف |
|--------|----------|-------------|--------|
| GET | `/api/admin/category-attributes` | List attributes | عرض الخصائص |
| GET | `/api/admin/category-attributes/{id}` | Get single | عرض خاصية |
| POST | `/api/admin/category-attributes` | Create | إنشاء خاصية |
| PUT | `/api/admin/category-attributes/{id}` | Update | تحديث |
| DELETE | `/api/admin/category-attributes/{id}` | Delete | حذف |

### Category Details Routes | مسارات تفاصيل الفئات

| Method | Endpoint | Description | الوصف |
|--------|----------|-------------|--------|
| GET | `/api/admin/category-details` | List details | عرض التفاصيل |
| GET | `/api/admin/category-details/{id}` | Get single | عرض تفصيل |
| POST | `/api/admin/category-details` | Create | إنشاء تفصيل |
| PUT | `/api/admin/category-details/{id}` | Update | تحديث |
| DELETE | `/api/admin/category-details/{id}` | Delete | حذف |

### Architecture | البنية

- **Categories:** `CategoryController`, `CategoryService`, `Category`
- **Attributes:** `CategoryAttributeController`, `CategoryAttributeService`
- **Details:** `CategoryDetailController`, `CategoryDetailService`

---

<a name="icons"></a>
## 6. Icons | الأيقونات

### Routes | المسارات

| Method | Endpoint | Description | الوصف |
|--------|----------|-------------|--------|
| GET | `/api/admin/icons` | List all icons | عرض جميع الأيقونات |
| GET | `/api/admin/icons/{id}` | Get single icon | عرض أيقونة واحدة |
| POST | `/api/admin/icons` | Create icon | إنشاء أيقونة |
| PUT | `/api/admin/icons/{id}` | Update icon | تحديث أيقونة |
| DELETE | `/api/admin/icons/{id}` | Delete icon | حذف أيقونة |

### Architecture | البنية

- **Controller:** `App\Http\Controllers\Admin\IconController`
- **Service:** `App\Services\Admin\IconService`
- **Model:** `App\Models\Icon`

### Request Body | بيانات الطلب

```json
{
  "name": {"ar": "توصيل سريع", "en": "Fast Delivery"},
  "svg_code": "<svg>...</svg>",
  "category": "product",
  "is_active": true
}
```

---

<a name="countries"></a>
## 7. Countries | البلدان

### Routes | المسارات

| Method | Endpoint | Description | الوصف |
|--------|----------|-------------|--------|
| GET | `/api/admin/countries` | List all countries | عرض جميع البلدان |
| GET | `/api/admin/countries/{id}` | Get single country | عرض بلد واحد |
| POST | `/api/admin/countries` | Create country | إنشاء بلد |
| PUT | `/api/admin/countries/{id}` | Update country | تحديث بلد |
| DELETE | `/api/admin/countries/{id}` | Delete country | حذف بلد |

### Architecture | البنية

- **Controller:** `App\Http\Controllers\Admin\Country\CountryCrudController`
- **Service:** `App\Services\Admin\CountryService`
- **Model:** `App\Models\Country`

### Request Body | بيانات الطلب

```json
{
  "name": {"ar": "سوريا", "en": "Syria"},
  "code": "SY",
  "phone_code": "+963",
  "currency_id": 1,
  "flag": "file",
  "is_active": true
}
```

---

<a name="promotion-requests"></a>
## 8. Promotion Requests | طلبات العروض

### Routes | المسارات

| Method | Endpoint | Description | الوصف |
|--------|----------|-------------|--------|
| GET | `/api/admin/promotion-requests` | List all requests | عرض جميع الطلبات |
| GET | `/api/admin/promotion-requests/{id}` | Get single | عرض طلب واحد |
| POST | `/api/admin/promotion-requests/{id}/approve` | Approve | الموافقة |
| POST | `/api/admin/promotion-requests/{id}/reject` | Reject | الرفض |
| DELETE | `/api/admin/promotion-requests/{id}` | Delete | الحذف |
| GET | `/api/admin/promotion-requests/stats/summary` | Statistics | الإحصائيات |

### Architecture | البنية

- **Controller:** `App\Http\Controllers\Admin\PromotionRequest\PromotionRequestCrudController`
- **Service:** `App\Services\Admin\PromotionRequestService`
- **Model:** `App\Models\PromotionRequest`

### Approve Request | الموافقة

```json
{
  "approved_discount": 15,
  "approved_start_date": "2024-01-01",
  "approved_end_date": "2024-12-31",
  "admin_notes": "Approved with modifications"
}
```

### Reject Request | الرفض

```json
{
  "rejection_reason": "Does not meet criteria"
}
```

---

<a name="point-rules"></a>
## 9. Point Rules | قواعد النقاط

### Point Rules Routes | مسارات قواعد النقاط

| Method | Endpoint | Description | الوصف |
|--------|----------|-------------|--------|
| GET | `/api/admin/point-rules` | List all rules | عرض جميع القواعد |
| GET | `/api/admin/point-rules/{id}` | Get single rule | عرض قاعدة واحدة |
| POST | `/api/admin/point-rules` | Create rule | إنشاء قاعدة |
| PUT | `/api/admin/point-rules/{id}` | Update rule | تحديث قاعدة |
| DELETE | `/api/admin/point-rules/{id}` | Delete rule | حذف قاعدة |

### User Points Routes | مسارات نقاط المستخدمين

| Method | Endpoint | Description | الوصف |
|--------|----------|-------------|--------|
| GET | `/api/admin/user-points` | List user points | عرض نقاط المستخدمين |
| GET | `/api/admin/user-points/{userId}` | Get summary | عرض ملخص |
| GET | `/api/admin/user-points/{userId}/transactions` | Get transactions | عرض المعاملات |

### Architecture | البنية

- **Controller:** `App\Http\Controllers\Admin\PointRuleController`
- **Service:** `App\Services\Admin\PointRuleService`
- **Model:** `App\Models\PointRule`

### Request Body | بيانات الطلب

```json
{
  "name": {"ar": "نقاط إتمام الطلب", "en": "Order Points"},
  "event_type": "order_completed",
  "points": 10,
  "min_order_amount": 50.00,
  "expiry_days": 365,
  "is_active": true
}
```

### Event Types | أنواع الأحداث

- `order_completed` - إتمام الطلب
- `registration` - تسجيل المستخدم
- `referral` - مكافأة الإحالة
- `review` - تقييم المنتج
- `birthday` - مكافأة عيد الميلاد

---

<a name="schedules"></a>
## 13. Schedules | الجدولات

### Routes | المسارات

| Method | Endpoint | Description | الوصف |
|--------|----------|-------------|--------|
| GET | `/api/admin/schedules` | List all schedules | عرض جميع الجدولات |
| GET | `/api/admin/schedules/{id}` | Get single schedule | عرض جدول واحد |
| POST | `/api/admin/schedules` | Create schedule | إنشاء جدول |
| PUT | `/api/admin/schedules/{id}` | Update schedule | تحديث جدول |
| DELETE | `/api/admin/schedules/{id}` | Delete schedule | حذف جدول |

### Architecture | البنية

- **Controller:** `App\Http\Controllers\Admin\Schedule\ScheduleCrudController`
- **Service:** `App\Services\Admin\ScheduleService`
- **Model:** `App\Models\Schedule`

### Request Body | بيانات الطلب

```json
{
  "name": {"ar": "جدول التوصيل الأسبوعي", "en": "Weekly Schedule"},
  "frequency": "weekly",
  "day_of_week": 1,
  "time": "10:00:00",
  "is_active": true
}
```

### Frequency Types | أنواع التكرار

- `daily` - يومياً
- `weekly` - أسبوعياً
- `monthly` - شهرياً
- `custom` - مخصص

---

<a name="vendor-subscriptions"></a>
## 11. User Subscriptions | اشتراكات المستخدمين

### Packages Routes | مسارات الباقات

| Method | Endpoint | Description | الوصف |
|--------|----------|-------------|--------|
| GET | `/api/admin/packages` | List all packages | عرض جميع الباقات |
| GET | `/api/admin/packages/{id}` | Get single package | عرض باقة واحدة |
| POST | `/api/admin/packages` | Create package | إنشاء باقة جديدة |
| PUT | `/api/admin/packages/{id}` | Update package | تحديث باقة |
| DELETE | `/api/admin/packages/{id}` | Delete package | حذف باقة |

### Subscriptions Routes | مسارات الاشتراكات

| Method | Endpoint | Description | الوصف |
|--------|----------|-------------|--------|
| GET | `/api/admin/subscriptions` | List all subscriptions | عرض جميع الاشتراكات |
| GET | `/api/admin/subscriptions/{id}` | Get single subscription | عرض اشتراك واحد |
| POST | `/api/admin/subscriptions` | Create subscription | إنشاء اشتراك جديد |
| PUT | `/api/admin/subscriptions/{id}` | Update subscription | تحديث اشتراك |
| DELETE | `/api/admin/subscriptions/{id}` | Delete subscription | حذف اشتراك |

### Architecture | البنية

#### Packages
- **Controller:** `App\Http\Controllers\Admin\Package\PackageController`
- **Service:** `App\Services\Admin\PackageService`
- **Model:** `App\Models\Package`

#### Subscriptions
- **Controller:** `App\Http\Controllers\Admin\Subscription\SubscriptionController`
- **Service:** `App\Services\Admin\SubscriptionService`
- **Model:** `App\Models\Subscription`

### Request Body | بيانات الطلب

#### Create Package

```json
{
  "name": {
    "ar": "الباقة الذهبية",
    "en": "Gold Package"
  },
  "price": 299.00,
  "duration_days": 30,
  "monthly_orders_limit": 50,
  "free_delivery_count": 10,
  "discount_percentage": 15,
  "points_bonus": 500,
  "is_active": true
}
```

#### Create Subscription

```json
{
  "user_id": 1,
  "package_id": 2,
  "start_date": "2024-01-01",
  "end_date": "2024-01-31",
  "status": "active",
  "remaining_orders": 50,
  "remaining_free_deliveries": 10
}
```

### Response | الاستجابة

#### Package Response

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": {"ar": "الباقة الذهبية", "en": "Gold Package"},
    "price": 299.00,
    "duration_days": 30,
    "monthly_orders_limit": 50,
    "free_delivery_count": 10,
    "discount_percentage": 15,
    "points_bonus": 500,
    "is_active": true,
    "active_subscriptions_count": 25
  }
}
```

#### Subscription Response

```json
{
  "success": true,
  "data": {
    "id": 1,
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "package": {
      "id": 2,
      "name": "Gold Package",
      "price": 299.00
    },
    "start_date": "2024-01-01",
    "end_date": "2024-01-31",
    "status": "active",
    "remaining_orders": 45,
    "remaining_free_deliveries": 8,
    "is_active": true,
    "days_remaining": 15
  }
}
```

### Subscription Status | حالات الاشتراك

| Status | Description | الوصف |
|--------|-------------|--------|
| `active` | Active subscription | اشتراك نشط |
| `expired` | Expired subscription | اشتراك منتهي |
| `cancelled` | Cancelled subscription | اشتراك ملغى |
| `pending` | Pending payment | بانتظار الدفع |

---

## 12. Vendor Subscriptions | اشتراكات الموردين

### Vendor Packages Routes | مسارات باقات الموردين

| Method | Endpoint | Description | الوصف |
|--------|----------|-------------|--------|
| GET | `/api/admin/vendor-packages` | List packages | عرض الباقات |
| GET | `/api/admin/vendor-packages/{id}` | Get single | عرض باقة |
| POST | `/api/admin/vendor-packages` | Create | إنشاء باقة |
| PUT | `/api/admin/vendor-packages/{id}` | Update | تحديث |
| DELETE | `/api/admin/vendor-packages/{id}` | Delete | حذف |

### Vendor Subscriptions Routes | مسارات اشتراكات الموردين

| Method | Endpoint | Description | الوصف |
|--------|----------|-------------|--------|
| GET | `/api/admin/vendor-subscriptions` | List subscriptions | عرض الاشتراكات |
| GET | `/api/admin/vendor-subscriptions/{id}` | Get single | عرض اشتراك |
| POST | `/api/admin/vendor-subscriptions` | Create | إنشاء اشتراك |
| PUT | `/api/admin/vendor-subscriptions/{id}` | Update | تحديث |
| DELETE | `/api/admin/vendor-subscriptions/{id}` | Delete | حذف |

### Architecture | البنية

- **Packages:** `VendorPackageController`, `VendorPackageService`, `VendorPackage`
- **Subscriptions:** `VendorSubscriptionController`, `VendorSubscriptionService`, `VendorSubscription`

### Request Body | بيانات الطلب

#### Create Vendor Package

```json
{
  "name": {
    "ar": "باقة المطاعم الذهبية",
    "en": "Restaurant Gold Package"
  },
  "description": {
    "ar": "باقة مميزة للمطاعم",
    "en": "Premium package for restaurants"
  },
  "price": 500.00,
  "duration_days": 30,
  "max_products": 100,
  "max_shops": 5,
  "features": {
    "ar": ["ميزة 1", "ميزة 2"],
    "en": ["Feature 1", "Feature 2"]
  },
  "is_active": true
}
```

#### Create Vendor Subscription

```json
{
  "vendor_id": 1,
  "vendor_package_id": 2,
  "start_date": "2024-01-01",
  "end_date": "2024-01-31",
  "price": 500.00,
  "payment_method": "card",
  "status": "active",
  "auto_renew": true
}
```

---

## 📁 Project Structure | بنية المشروع

```
app/
├── Http/
│   ├── Controllers/Admin/          # API Controllers
│   ├── Requests/Admin/             # Validation Requests
│   └── Resources/Admin/            # API Response Resources
├── Services/Admin/                 # Business Logic Services
├── Models/                         # Eloquent Models
└── Exports/                        # Excel Export Classes

routes/
└── api/admin.php                   # Admin API Routes

database/
├── migrations/                     # Database Migrations
└── seeders/                        # Database Seeders
```

---

## 🛠️ Development Tools | أدوات التطوير

### Testing with cURL

```bash
# Login
curl -X POST https://api.example.com/api/admin/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Get products
curl -X GET https://api.example.com/api/admin/products \
  -H "Authorization: Bearer {token}"

# Create product
curl -X POST https://api.example.com/api/admin/products \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d @product.json
```

---

## 📞 Support | الدعم

For questions or issues, contact the development team.

---

**© 2026 - All Rights Reserved**

