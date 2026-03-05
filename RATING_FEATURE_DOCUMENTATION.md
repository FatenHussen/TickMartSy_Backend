# Rating Feature - توثيق كامل

## نظرة عامة
نظام التقييمات يسمح للمستخدمين بتقييم المنتجات، المتاجر، السائقين، السلال، الوصفات، والعلامات التجارية.

---

## 📋 جدول المحتويات
1. [APIs المتاحة](#apis-المتاحة)
2. [أنواع التقييمات](#أنواع-التقييمات)
3. [نظام النقاط](#نظام-النقاط)
4. [قواعد التقييم](#قواعد-التقييم)
5. [أمثلة الاستخدام](#أمثلة-الاستخدام)

---

## APIs المتاحة

### 1. عرض جميع التقييمات (Public)
```http
GET /api/user/ratings
```

**Query Parameters:**
- `rateable_id` (optional): معرف العنصر المراد تقييمه
- `rateable_type` (optional): نوع العنصر (product, shop, delivery, basket, schedule_basket, recipe, brand)
- `rating` (optional): تصفية حسب التقييم (1-5)
- `user_id` (optional): تصفية حسب المستخدم
- `page` (optional): رقم الصفحة
- `per_page` (optional): عدد العناصر في الصفحة

**Response:**
```json
{
  "status": true,
  "message": "Ratings retrieved successfully",
  "data": [
    {
      "id": 1,
      "rating": 5,
      "comment": "منتج ممتاز",
      "image": "https://example.com/rating.jpg",
      "type": "product",
      "is_verified": true,
      "created_at": "2026-03-04",
      "user": {
        "id": 1,
        "name": "أحمد محمد",
        "image": "https://example.com/user.jpg"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

---

### 2. تقييماتي (My Ratings)
```http
GET /api/user/ratings/my_ratings
Authorization: Bearer {token}
```

**Query Parameters:**
- `type` (optional): نوع التقييم (product, brand, shop, delivery, recipe, basket, scheduled_basket)
- `rateable_id` (optional): معرف العنصر

**Response:**
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "rating": 5,
      "comment": "منتج رائع",
      "type": "product",
      "created_at": "2026-03-04 10:30",
      "target": {
        "id": 10,
        "name": "منتج مثال",
        "image": "https://example.com/product.jpg"
      }
    }
  ]
}
```

---

### 3. التحقق من إمكانية التقييم
```http
GET /api/user/ratings/can-rate?product_id=10
Authorization: Bearer {token}
```

**Query Parameters:**
- `product_id` (required): معرف المنتج

**Response (يمكن التقييم):**
```json
{
  "status": true,
  "message": "You can rate this product",
  "data": {
    "can_rate": true,
    "product_id": 10
  }
}
```

**Response (لا يمكن التقييم):**
```json
{
  "status": true,
  "message": "You must purchase this product before rating it",
  "data": {
    "can_rate": false,
    "reason": "You must purchase this product before rating it",
    "reason_ar": "يجب عليك شراء هذا المنتج قبل تقييمه"
  }
}
```

**أسباب عدم القدرة على التقييم:**
1. المنتج غير موجود
2. لم يتم شراء المنتج في طلب مكتمل (delivered)
3. تم تقييم المنتج مسبقاً

---

### 4. إضافة تقييم جديد
```http
POST /api/user/ratings
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Body Parameters:**
- `type` (required): نوع التقييم (product, delivery, basket, schedule_basket, shop, recipe, brand, order)
- `rateable_id` (required): معرف العنصر المراد تقييمه
- `rating` (required): التقييم من 1 إلى 5
- `comment` (optional): تعليق نصي
- `order_id` (optional): معرف الطلب
- `image` (optional): صورة (max: 2MB)

**Example Request:**
```json
{
  "type": "product",
  "rateable_id": 10,
  "rating": 5,
  "comment": "منتج ممتاز وجودة عالية",
  "order_id": 123,
  "image": "file"
}
```

**Response:**
```json
{
  "status": true,
  "message": "Rating created successfully",
  "data": {
    "id": 1,
    "rating": 5,
    "comment": "منتج ممتاز وجودة عالية",
    "type": "product",
    "created_at": "2026-03-04"
  }
}
```

**مكافأة النقاط:**
- يحصل المستخدم على **10 نقاط** عند إضافة تقييم

---

### 5. تحديث تقييم
```http
PUT /api/user/ratings/{id}
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Body Parameters:**
- `rating` (optional): التقييم من 1 إلى 5
- `comment` (optional): تعليق نصي
- `image` (optional): صورة جديدة

**Example Request:**
```json
{
  "rating": 4,
  "comment": "تحديث التقييم"
}
```

**Response:**
```json
{
  "status": true,
  "message": "Rating updated successfully"
}
```

**ملاحظة:** يمكن للمستخدم تحديث تقييمه الخاص فقط

---

### 6. حذف تقييم
```http
DELETE /api/user/ratings/{id}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "status": true,
  "message": "Rating deleted successfully"
}
```

**ملاحظة:** يمكن للمستخدم حذف تقييمه الخاص فقط

---

## أنواع التقييمات

| النوع | القيمة | الوصف |
|------|--------|-------|
| منتج | `product` | تقييم منتج |
| متجر | `shop` | تقييم متجر |
| سائق | `delivery` | تقييم خدمة التوصيل |
| سلة | `basket` | تقييم سلة |
| سلة مجدولة | `schedule_basket` | تقييم سلة مجدولة |
| وصفة | `recipe` | تقييم وصفة |
| علامة تجارية | `brand` | تقييم علامة تجارية |
| طلب | `order` | تقييم تجربة الطلب الكاملة |

---

## نظام النقاط

### مكافأة التقييم
- **القيمة**: 10 نقاط
- **الشرط**: عند إضافة أي تقييم جديد
- **مرة واحدة**: لا (يمكن الحصول على نقاط لكل تقييم)

### كيفية عمل النقاط:
1. المستخدم يضيف تقييم جديد
2. النظام يتحقق من صحة البيانات
3. يتم حفظ التقييم في قاعدة البيانات
4. يتم منح 10 نقاط تلقائياً للمستخدم
5. النقاط تُضاف فوراً لرصيد المستخدم

**الموقع في الكود:**
```php
// app/Services/User/RatingService.php
public function create($data)
{
    // ... create rating
    
    $pointService->awardPoints(
        userId: $data['user_id'],
        ruleCode: 'product_review',
        referenceType: 'rating',
        referenceId: $rating->id
    );
}
```

---

## قواعد التقييم

### قواعد تقييم المنتجات:
1. **يجب شراء المنتج أولاً**: المستخدم يجب أن يكون قد اشترى المنتج في طلب مكتمل (delivered)
2. **تقييم واحد لكل منتج**: لا يمكن تقييم نفس المنتج أكثر من مرة
3. **التحقق التلقائي**: جميع التقييمات يتم التحقق منها تلقائياً (`is_verified = true`)

### التحقق من الشراء:
```php
// يتحقق النظام من:
// 1. وجود طلب للمستخدم
// 2. الطلب يحتوي على المنتج (أي variant من المنتج)
// 3. حالة الطلب = delivered
```

---

## أمثلة الاستخدام

### مثال 1: تقييم منتج بعد الشراء

**الخطوة 1: التحقق من إمكانية التقييم**
```bash
curl -X GET "https://api.example.com/api/user/ratings/can-rate?product_id=10" \
  -H "Authorization: Bearer {token}"
```

**الخطوة 2: إضافة التقييم**
```bash
curl -X POST "https://api.example.com/api/user/ratings" \
  -H "Authorization: Bearer {token}" \
  -F "type=product" \
  -F "rateable_id=10" \
  -F "rating=5" \
  -F "comment=منتج رائع" \
  -F "image=@rating.jpg"
```

**النتيجة:**
- ✅ تم إضافة التقييم
- ✅ حصل المستخدم على 10 نقاط

---

### مثال 2: عرض تقييمات منتج معين

```bash
curl -X GET "https://api.example.com/api/user/ratings?rateable_type=product&rateable_id=10&page=1&per_page=10"
```

**النتيجة:**
- عرض جميع تقييمات المنتج رقم 10
- مع pagination

---

### مثال 3: عرض تقييماتي الشخصية

```bash
curl -X GET "https://api.example.com/api/user/ratings/my_ratings?type=product" \
  -H "Authorization: Bearer {token}"
```

**النتيجة:**
- عرض جميع تقييمات المستخدم للمنتجات
- مع تفاصيل المنتجات المقيّمة

---

### مثال 4: تحديث تقييم

```bash
curl -X PUT "https://api.example.com/api/user/ratings/1" \
  -H "Authorization: Bearer {token}" \
  -F "rating=4" \
  -F "comment=تحديث التقييم بعد الاستخدام"
```

---

## Database Schema

### جدول ratings
```sql
CREATE TABLE ratings (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    rateable_type VARCHAR(255) NOT NULL,
    rateable_id BIGINT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NULL,
    image VARCHAR(255) NULL,
    order_id BIGINT NULL,
    is_verified BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_rateable (rateable_type, rateable_id),
    INDEX idx_user (user_id)
);
```

---

## Validation Rules

### إضافة تقييم:
- `type`: required, string, in:(product,delivery,basket,schedule_basket,shop,recipe,brand,order)
- `rateable_id`: required, integer
- `rating`: required, integer, min:1, max:5
- `comment`: nullable, string
- `order_id`: nullable, exists:orders,id
- `image`: nullable, image, max:2048 (KB)

### تحديث تقييم:
- `rating`: sometimes, integer, min:1, max:5
- `comment`: nullable, string
- `image`: nullable, image, max:2048 (KB)

---

## Error Responses

### 401 Unauthorized
```json
{
  "status": false,
  "message": "Unauthenticated"
}
```

### 403 Forbidden
```json
{
  "status": false,
  "message": "You are not authorized to perform this action"
}
```

### 404 Not Found
```json
{
  "status": false,
  "message": "Rating not found"
}
```

### 422 Validation Error
```json
{
  "status": false,
  "message": "The given data was invalid",
  "errors": {
    "rating": ["The rating field is required."],
    "type": ["The selected type is invalid."]
  }
}
```

---

## ملاحظات مهمة

1. **الصور**: يتم رفع الصور إلى storage وحفظ المسار في قاعدة البيانات
2. **التحقق التلقائي**: جميع التقييمات verified بشكل افتراضي
3. **الأمان**: المستخدم يمكنه فقط تعديل/حذف تقييماته الخاصة
4. **النقاط**: تُمنح فوراً عند إضافة التقييم ولا تُسترجع عند الحذف
5. **Polymorphic Relations**: التقييمات تستخدم polymorphic relations للربط مع أنواع مختلفة

---

## Files Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── User/
│   │       └── Rating/
│   │           └── RatingController.php
│   ├── Requests/
│   │   └── User/
│   │       └── Rating/
│   │           ├── StoreRequest.php
│   │           ├── UpdateRequest.php
│   │           ├── FilterRequest.php
│   │           └── MyRatingsRequest.php
│   └── Resources/
│       └── Rating/
│           ├── AllResource.php
│           ├── OneResource.php
│           └── RatingWithTargetResource.php
├── Models/
│   └── Rating.php
├── Services/
│   └── User/
│       └── RatingService.php
└── Enums/
    └── RateableType.php
```

---

## Testing Examples

### Postman Collection

**Environment Variables:**
- `base_url`: https://api.example.com
- `token`: Bearer token من login

**Test 1: Get All Ratings**
```
GET {{base_url}}/api/user/ratings
```

**Test 2: Create Rating**
```
POST {{base_url}}/api/user/ratings
Headers:
  Authorization: Bearer {{token}}
Body (form-data):
  type: product
  rateable_id: 10
  rating: 5
  comment: منتج ممتاز
```

**Test 3: My Ratings**
```
GET {{base_url}}/api/user/ratings/my_ratings
Headers:
  Authorization: Bearer {{token}}
```

---

تم إنشاء التوثيق بتاريخ: 2026-03-04
