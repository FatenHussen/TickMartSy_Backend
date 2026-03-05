# 📱 User Features API Documentation
## توثيق APIs المفضلة والشكاوي والباقات

---

## 📋 جدول المحتويات

1. [المفضلة (Favorites)](#favorites-apis)
2. [الشكاوي (Complaints)](#complaints-apis)
3. [الباقات والاشتراكات (Packages & Subscriptions)](#packages-subscriptions-apis)

---

## ⭐ Favorites APIs - المفضلة

### Base URL
```
/api/user/favorites
```

### Authentication
جميع الـ APIs تحتاج Authentication Token:
```
Authorization: Bearer {token}
```

---

## 1️⃣ إضافة/إزالة من المفضلة (Toggle Favorite)

### Endpoint
```http
POST /api/user/favorites/toggle
```

### الغرض
إضافة عنصر للمفضلة أو إزالته (Toggle). إذا كان موجود يتم حذفه، وإذا لم يكن موجود يتم إضافته.

### Request Body
```json
{
  "type": "product",
  "id": 25
}
```

### أنواع العناصر المدعومة (type)
- `product` - منتج
- `recipe` - وصفة
- `brand` - علامة تجارية
- `shop` - متجر
- `vendor` - بائع
- `basket` - سلة

### Response - إضافة للمفضلة
```json
{
  "success": true,
  "data": {
    "message": "Added to favorites",
    "is_favorite": true
  }
}
```

### Response - إزالة من المفضلة
```json
{
  "success": true,
  "data": {
    "message": "Removed from favorites",
    "is_favorite": false
  }
}
```

---

## 2️⃣ عرض قائمة المفضلة (List Favorites)

### Endpoint
```http
GET /api/user/favorites
```

### Query Parameters
```
?type=product              // نوع العنصر (إجباري)
&shop_id=5                 // تصفية حسب المتجر (اختياري)
&category_id=10            // تصفية حسب الفئة (اختياري)
&page=1                    // رقم الصفحة
&per_page=15               // عدد العناصر
```

### مثال 1: عرض المنتجات المفضلة
```http
GET /api/user/favorites?type=product
```

### Response
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type": "product",
      "product": {
        "id": 25,
        "name": "Fresh Milk",
        "price": 5.50,
        "image": "https://...",
        "is_favorite": true
      },
      "created_at": "2026-03-05 10:00:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 15
  }
}
```

### مثال 2: عرض الوصفات المفضلة
```http
GET /api/user/favorites?type=recipe
```

### مثال 3: عرض المتاجر المفضلة
```http
GET /api/user/favorites?type=shop
```

### مثال 4: تصفية المنتجات المفضلة حسب المتجر
```http
GET /api/user/favorites?type=product&shop_id=5
```

---

## 📝 Complaints APIs - الشكاوي

### Base URL
```
/api/user/complaints
```

### Authentication
جميع الـ APIs تحتاج Authentication Token

---

## 1️⃣ إنشاء شكوى جديدة (Create Complaint)

### Endpoint
```http
POST /api/user/complaints/store
```

### Request Body (Multipart Form Data)
```json
{
  "order_id": 150,
  "type": "product",
  "message": "المنتج وصل تالف ولا يطابق الوصف",
  "images": [File, File]
}
```

### الحقول المطلوبة

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| order_id | integer | ✅ Yes | معرف الطلب |
| type | string | ✅ Yes | نوع الشكوى |
| message | string | ✅ Yes | نص الشكوى (5 أحرف على الأقل) |
| images | array | ❌ No | صور الشكوى (حد أقصى 5 صور) |

### أنواع الشكاوي (type)
- `product` - شكوى على المنتج
- `order` - شكوى على الطلب
- `driver` - شكوى على السائق
- `merchant` - شكوى على التاجر/المتجر

### مثال Request بالصور (cURL)
```bash
curl -X POST https://api.example.com/api/user/complaints/store \
  -H "Authorization: Bearer {token}" \
  -F "order_id=150" \
  -F "type=product" \
  -F "message=المنتج وصل تالف" \
  -F "images[]=@/path/to/image1.jpg" \
  -F "images[]=@/path/to/image2.jpg"
```

### Response - نجاح
```json
{
  "success": true,
  "message": "Complaint created successfully"
}
```

### Response - خطأ (Validation)
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "message": ["The message field must be at least 5 characters."],
    "order_id": ["The selected order id is invalid."]
  }
}
```

---

## 2️⃣ عرض قائمة الشكاوي (List Complaints)

### Endpoint
```http
GET /api/user/complaints
```

### Query Parameters
```
?status=new                // تصفية حسب الحالة
&order_id=150              // تصفية حسب الطلب
&from=2026-03-01           // من تاريخ
&to=2026-03-31             // إلى تاريخ
&page=1
&per_page=15
```

### حالات الشكوى (status)
- `new` - جديدة (لم يتم الرد عليها)
- `resolved` - تم حلها
- `rejected` - مرفوضة

### Response
```json
{
  "success": true,
  "data": [
    {
      "id": 45,
      "order_id": 150,
      "type": "product",
      "message": "المنتج وصل تالف ولا يطابق الوصف",
      "status": "new",
      "admin_response": null,
      "images": [
        "https://example.com/storage/complaints/image1.jpg",
        "https://example.com/storage/complaints/image2.jpg"
      ],
      "user": {
        "id": 10,
        "name": "Ahmed Ali",
        "phone": "+966501234567"
      },
      "created_at": "2026-03-05 14:30:00"
    },
    {
      "id": 44,
      "order_id": 148,
      "type": "driver",
      "message": "السائق تأخر كثيراً في التوصيل",
      "status": "resolved",
      "admin_response": "تم التواصل مع السائق وتم حل المشكلة",
      "images": [],
      "user": {
        "id": 10,
        "name": "Ahmed Ali",
        "phone": "+966501234567"
      },
      "created_at": "2026-03-03 10:15:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 8
  }
}
```

---

## 3️⃣ عرض الطلبات المتاحة للشكوى (Available Orders)

### Endpoint
```http
GET /api/user/complaints/orders
```

### الغرض
يعرض قائمة الطلبات التي يمكن للمستخدم تقديم شكوى عليها (الطلبات المكتملة أو الملغاة).

### Response
```json
{
  "success": true,
  "data": [
    {
      "id": 150,
      "order_code": "ORD-260305-ABCD150",
      "status": "delivered",
      "total": 45.95,
      "created_at": "2026-03-05 14:30:00"
    },
    {
      "id": 148,
      "order_code": "ORD-260303-WXYZ148",
      "status": "cancelled",
      "total": 32.50,
      "created_at": "2026-03-03 10:15:00"
    }
  ]
}
```

---

## 📦 Packages & Subscriptions APIs - الباقات والاشتراكات

### Base URL
```
/api/user/packages
/api/user/subscription
```

---

## 1️⃣ عرض الباقات المتاحة (List Packages)

### Endpoint
```http
GET /api/user/packages
```

### Authentication
❌ لا يحتاج Authentication (Public API)

### الغرض
يعرض جميع الباقات المتاحة للاشتراك مع تفاصيل كل باقة.

### Response
```json
{
  "data": [
    {
      "id": 1,
      "name": "الباقة الفضية",
      "price": 99.00,
      "price_formatted": "99.00 SAR",
      "duration_days": 30,
      "monthly_orders_limit": 10,
      "free_delivery_count": 5,
      "discount_percentage": 10,
      "points_bonus": 50,
      "is_active": true
    },
    {
      "id": 2,
      "name": "الباقة الذهبية",
      "price": 199.00,
      "price_formatted": "199.00 SAR",
      "duration_days": 30,
      "monthly_orders_limit": 20,
      "free_delivery_count": 15,
      "discount_percentage": 15,
      "points_bonus": 150,
      "is_active": true
    }
  ]
}
```

### شرح حقول الباقة

| Field | Description |
|-------|-------------|
| id | معرف الباقة |
| name | اسم الباقة |
| price | سعر الباقة |
| duration_days | مدة الباقة بالأيام (عادة 30 يوم) |
| monthly_orders_limit | عدد الطلبات المسموح بها شهرياً |
| free_delivery_count | عدد مرات التوصيل المجاني |
| discount_percentage | نسبة الخصم على الطلبات (%) |
| points_bonus | نقاط إضافية عند الاشتراك |
| is_active | هل الباقة متاحة للاشتراك |

---

## 2️⃣ الاشتراك في باقة (Subscribe to Package)

### Endpoint
```http
POST /api/user/subscribe
```

### Authentication
✅ يحتاج Authentication Token

### Request Body
```json
{
  "package_id": 1
}
```

### Response - نجاح
```json
{
  "success": true,
  "message": "Subscription created successfully"
}
```

### Response - خطأ (لديه اشتراك نشط)
```json
{
  "success": false,
  "message": "You already have an active subscription"
}
```

---

## 3️⃣ عرض اشتراكي الحالي (My Subscription)

### Endpoint
```http
GET /api/user/my-subscription
```

### Authentication
✅ يحتاج Authentication Token

### Response - لديه اشتراك نشط
```json
{
  "success": true,
  "data": {
    "id": 25,
    "package": {
      "id": 1,
      "name": "الباقة الفضية",
      "price": 99.00,
      "price_formatted": "99.00 SAR",
      "duration_days": 30,
      "monthly_orders_limit": 10,
      "free_delivery_count": 5,
      "discount_percentage": 10,
      "points_bonus": 50,
      "is_active": true
    },
    "status": "active",
    "start_date": "2026-03-01",
    "end_date": "2026-03-31",
    "remaining_orders": 7,
    "remaining_free_deliveries": 3
  }
}
```

### Response - لا يوجد اشتراك
```json
{
  "success": false,
  "message": "No active subscription",
  "code": 404
}
```

---

## 4️⃣ تجديد الاشتراك (Renew Subscription)

### Endpoint
```http
POST /api/user/renew
```

### Authentication
✅ يحتاج Authentication Token

### الغرض
تجديد الاشتراك بنفس الباقة أو باقة جديدة.

### Request Body
```json
{
  "package_id": 2
}
```

### Response
```json
{
  "success": true,
  "message": "Subscription renewed successfully"
}
```

---

## 5️⃣ عرض مزايا الباقة المتبقية (Subscription Benefits)

### Endpoint
```http
GET /api/user/subscription/benefits
```

### Authentication
✅ يحتاج Authentication Token

### الغرض
يعرض المزايا المتبقية من الباقة (الخصومات والتوصيل المجاني).

### Response - لديه اشتراك نشط
```json
{
  "success": true,
  "data": {
    "has_subscription": true,
    "remaining_discounts": 7,
    "remaining_free_deliveries": 3
  },
  "message": "تم جلب مزايا الباقة بنجاح"
}
```

### Response - لا يوجد اشتراك
```json
{
  "success": true,
  "data": {
    "has_subscription": false,
    "remaining_discounts": 0,
    "remaining_free_deliveries": 0
  },
  "message": "لا يوجد اشتراك نشط"
}
```

---

## 📊 أمثلة كاملة (Complete Examples)

### مثال 1: إضافة منتج للمفضلة
```javascript
// Request
POST /api/user/favorites/toggle
Headers: {
  "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGc...",
  "Content-Type": "application/json"
}
Body: {
  "type": "product",
  "id": 25
}

// Response
{
  "success": true,
  "data": {
    "message": "Added to favorites",
    "is_favorite": true
  }
}
```

### مثال 2: تقديم شكوى على طلب
```javascript
// Request
POST /api/user/complaints/store
Headers: {
  "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGc...",
  "Content-Type": "multipart/form-data"
}
Body (FormData): {
  "order_id": 150,
  "type": "product",
  "message": "المنتج وصل تالف ولا يطابق الوصف المكتوب",
  "images": [File1, File2]
}

// Response
{
  "success": true,
  "message": "Complaint created successfully"
}
```

### مثال 3: الاشتراك في باقة
```javascript
// Step 1: عرض الباقات المتاحة
GET /api/user/packages

// Response
{
  "data": [
    {
      "id": 1,
      "name": "الباقة الفضية",
      "price": 99.00,
      "monthly_orders_limit": 10,
      "discount_percentage": 10
    }
  ]
}

// Step 2: الاشتراك في الباقة
POST /api/user/subscribe
Headers: {
  "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGc...",
  "Content-Type": "application/json"
}
Body: {
  "package_id": 1
}

// Response
{
  "success": true,
  "message": "Subscription created successfully"
}
```

### مثال 4: استخدام مزايا الباقة في الطلب
```javascript
// Step 1: التحقق من المزايا المتبقية
GET /api/user/subscription/benefits
Headers: {
  "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
}

// Response
{
  "success": true,
  "data": {
    "has_subscription": true,
    "remaining_discounts": 7,
    "remaining_free_deliveries": 3
  }
}

// Step 2: إنشاء طلب مع استخدام مزايا الباقة
POST /api/user/orders
Headers: {
  "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGc...",
  "Content-Type": "application/json"
}
Body: {
  "address_id": 1,
  "payment_method_id": 2,
  "is_instant_delivery": true,
  "items": [
    {
      "shop_product_variant_id": 10,
      "quantity": 2
    }
  ],
  "use_subscription_discount": true,
  "use_subscription_free_delivery": true
}

// Response
{
  "success": true,
  "data": {
    "id": 150,
    "order_code": "ORD-260305-ABCD150",
    "subtotal": 50.00,
    "subscription_discount": 5.00,
    "subscription_free_delivery": true,
    "delivery_price": 0.00,
    "total": 45.00
  }
}
```

---

## 🔄 سيناريوهات الاستخدام (Use Cases)

### سيناريو 1: المستخدم يريد حفظ منتجات مفضلة
1. المستخدم يتصفح المنتجات
2. يضغط على أيقونة القلب ❤️
3. يتم استدعاء `POST /api/user/favorites/toggle` مع `type=product` و `id=25`
4. يتم إضافة المنتج للمفضلة
5. لعرض المفضلة: `GET /api/user/favorites?type=product`

### سيناريو 2: المستخدم يريد تقديم شكوى
1. المستخدم يذهب لصفحة الطلبات
2. يختار طلب معين ويضغط "تقديم شكوى"
3. يتم استدعاء `GET /api/user/complaints/orders` لعرض الطلبات المتاحة
4. المستخدم يختار نوع الشكوى ويكتب الرسالة ويرفع صور
5. يتم استدعاء `POST /api/user/complaints/store`
6. يمكن متابعة الشكوى من `GET /api/user/complaints`

### سيناريو 3: المستخدم يريد الاشتراك في باقة
1. المستخدم يذهب لصفحة الباقات
2. يتم استدعاء `GET /api/user/packages` لعرض الباقات
3. المستخدم يختار باقة ويضغط "اشترك الآن"
4. يتم استدعاء `POST /api/user/subscribe` مع `package_id`
5. يتم تفعيل الاشتراك
6. عند إنشاء طلب جديد، يمكن استخدام `use_subscription_discount` و `use_subscription_free_delivery`

### سيناريو 4: التحقق من المزايا المتبقية
1. قبل إنشاء طلب، يتم استدعاء `GET /api/user/subscription/benefits`
2. إذا كان `has_subscription: true` و `remaining_discounts > 0`
3. يتم عرض خيار "استخدام خصم الباقة" للمستخدم
4. إذا كان `remaining_free_deliveries > 0`
5. يتم عرض خيار "استخدام توصيل مجاني من الباقة"

---

## ⚠️ ملاحظات مهمة (Important Notes)

### المفضلة (Favorites)
- ✅ Toggle API يضيف أو يحذف تلقائياً (لا حاجة لـ API منفصل للحذف)
- ✅ يمكن إضافة أي نوع من العناصر (منتج، وصفة، متجر، إلخ)
- ✅ التصفية حسب المتجر أو الفئة متاحة للمنتجات فقط
- ⚠️ الـ `type` parameter إجباري في List API

### الشكاوي (Complaints)
- ✅ يمكن رفع حتى 5 صور مع الشكوى
- ✅ الصور يجب أن تكون بصيغة: jpg, jpeg, png
- ✅ الحد الأدنى لنص الشكوى: 5 أحرف
- ⚠️ يمكن تقديم شكوى فقط على الطلبات المكتملة أو الملغاة
- ⚠️ لا يمكن تعديل أو حذف الشكوى بعد إرسالها (من جهة المستخدم)
- ℹ️ الرد على الشكوى يتم من لوحة الإدارة فقط

### الباقات (Packages)
- ✅ يمكن الاشتراك في باقة واحدة فقط في نفس الوقت
- ✅ عند انتهاء الباقة، يمكن التجديد بنفس الباقة أو باقة أخرى
- ✅ المزايا تُستهلك تلقائياً عند إنشاء الطلبات
- ⚠️ لا يمكن استخدام خصم الباقة مع كوبون عادي في نفس الطلب
- ⚠️ لا يمكن استخدام توصيل مجاني من الباقة مع توصيل مجاني من النقاط
- ℹ️ النقاط الإضافية (points_bonus) تُضاف فوراً عند الاشتراك

---

## 🔐 Error Codes - أكواد الأخطاء

| Code | Message | Description |
|------|---------|-------------|
| 401 | Unauthorized | المستخدم غير مسجل دخول |
| 404 | Not Found | العنصر المطلوب غير موجود |
| 422 | Validation Error | خطأ في البيانات المدخلة |
| 400 | Bad Request | طلب غير صحيح |
| 409 | Conflict | تعارض (مثل: لديك اشتراك نشط بالفعل) |

---

## 📱 Integration Tips - نصائح التكامل

### للفرونت إند (Frontend)

#### 1. المفضلة
```javascript
// دالة Toggle للمفضلة
async function toggleFavorite(type, id) {
  const response = await fetch('/api/user/favorites/toggle', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ type, id })
  });
  
  const data = await response.json();
  
  // تحديث الأيقونة في الواجهة
  if (data.data.is_favorite) {
    // عرض قلب ممتلئ ❤️
  } else {
    // عرض قلب فارغ 🤍
  }
}
```

#### 2. الشكاوي
```javascript
// دالة إرسال شكوى مع صور
async function submitComplaint(orderId, type, message, images) {
  const formData = new FormData();
  formData.append('order_id', orderId);
  formData.append('type', type);
  formData.append('message', message);
  
  // إضافة الصور
  images.forEach((image, index) => {
    formData.append(`images[${index}]`, image);
  });
  
  const response = await fetch('/api/user/complaints/store', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    },
    body: formData
  });
  
  return await response.json();
}
```

#### 3. الباقات
```javascript
// دالة التحقق من المزايا قبل الطلب
async function checkSubscriptionBenefits() {
  const response = await fetch('/api/user/subscription/benefits', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  
  const data = await response.json();
  
  if (data.data.has_subscription) {
    // عرض خيارات استخدام مزايا الباقة
    if (data.data.remaining_discounts > 0) {
      // عرض checkbox "استخدام خصم الباقة"
    }
    if (data.data.remaining_free_deliveries > 0) {
      // عرض checkbox "استخدام توصيل مجاني"
    }
  }
}
```

---

## 🎯 Checklist للفرونت إند

### صفحة المفضلة
- [ ] زر Toggle للإضافة/الإزالة من المفضلة
- [ ] عرض قائمة المفضلة مع Pagination
- [ ] تصفية حسب النوع (منتجات، وصفات، متاجر)
- [ ] تصفية حسب المتجر أو الفئة (للمنتجات)
- [ ] رسالة "لا توجد عناصر مفضلة" عند القائمة فارغة

### صفحة الشكاوي
- [ ] نموذج تقديم شكوى جديدة
- [ ] اختيار الطلب من قائمة الطلبات المتاحة
- [ ] اختيار نوع الشكوى (منتج، طلب، سائق، تاجر)
- [ ] حقل نص الشكوى (5 أحرف على الأقل)
- [ ] رفع صور (حد أقصى 5 صور)
- [ ] عرض قائمة الشكاوي السابقة
- [ ] تصفية حسب الحالة (جديدة، محلولة، مرفوضة)
- [ ] عرض رد الإدارة على الشكوى

### صفحة الباقات
- [ ] عرض جميع الباقات المتاحة
- [ ] عرض تفاصيل كل باقة (السعر، المدة، المزايا)
- [ ] زر الاشتراك في الباقة
- [ ] عرض الاشتراك الحالي (إن وجد)
- [ ] عرض المزايا المتبقية
- [ ] زر تجديد الاشتراك
- [ ] عرض تاريخ انتهاء الاشتراك

### صفحة إنشاء الطلب
- [ ] التحقق من مزايا الباقة المتبقية
- [ ] عرض خيار "استخدام خصم الباقة" (إذا متاح)
- [ ] عرض خيار "استخدام توصيل مجاني من الباقة" (إذا متاح)
- [ ] منع استخدام أكثر من مصدر خصم واحد
- [ ] منع استخدام أكثر من مصدر توصيل مجاني واحد

---

## 📞 Support

للمزيد من المعلومات أو الدعم الفني، يرجى التواصل مع فريق التطوير.

---

**آخر تحديث:** 5 مارس 2026
