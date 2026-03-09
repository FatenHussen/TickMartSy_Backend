# دليل الفرونت اند الشامل - Frontend Complete Testing Flow

## 📋 جدول المحتويات
1. [التسجيل والدخول](#1-registration-and-login)
2. [حذف الحساب (إلغاء التفعيل)](#2-delete-account)
3. [حذف التعليق خلال 24 ساعة](#3-delete-rating)
4. [صور المنتج](#4-product-images)
5. [صور الكاتيجوري](#5-category-images)
6. [النقاط النشطة](#6-active-points)
7. [إدارة الأيقونات](#7-icons-management)
8. [البادجات](#8-badges)
9. [المنتجات المباعة معاً](#9-bought-with-products)
10. [إعادة الطلب (Reorder)](#10-reorder)

---

## 1. Registration and Login

### 1.1 التسجيل بالهاتف
**Endpoint:** `POST /api/user/auth/register`

**Request Body:**
```json
{
    "phone": "0501234567",
    "password": "password123",
    "name": "أحمد محمد",
    "city_id": 1,
    "governorate_id": 1
}
```

**Response:**
```json
{
    "success": true,
    "message": "تم التسجيل بنجاح. يرجى التحقق من رقم الهاتف"
}
```

**ملاحظات:**
- يتم إرسال OTP للهاتف تلقائياً
- الحساب يُنشأ مع `is_active = true`
- يجب التحقق من الهاتف قبل تسجيل الدخول

---

### 1.2 التحقق من OTP
**Endpoint:** `POST /api/user/auth/verify-otp`

**Request Body:**
```json
{
    "phone": "0501234567",
    "code": "12345"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "أحمد محمد",
        "phone": "0501234567",
        "phone_verified_at": "2026-03-08T16:00:00.000000Z",
        "token": "1|abc123...",
        "currency": {
            "id": 1,
            "code": "USD",
            "symbol": "$"
        }
    }
}
```

**ملاحظات:**
- عند التحقق الأول، يحصل المستخدم على نقاط التسجيل (100 نقطة)
- يتم إرسال إشعار بالنقاط المكتسبة

---

### 1.3 تسجيل الدخول
**Endpoint:** `POST /api/user/auth/login`

**Request Body:**
```json
{
    "phone": "0501234567",
    "password": "password123"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "أحمد محمد",
        "phone": "0501234567",
        "token": "2|xyz789...",
        "currency": {
            "id": 1,
            "code": "USD",
            "symbol": "$"
        }
    }
}
```

**حالات الخطأ:**
```json
// الحساب غير مسجل
{
    "success": false,
    "message": "الحساب غير مسجل"
}

// كلمة المرور خاطئة
{
    "success": false,
    "message": "بيانات الدخول غير صحيحة"
}

// الحساب غير مفعل (is_active = false)
{
    "success": false,
    "message": "الحساب غير نشط"
}

// الهاتف غير محقق
{
    "success": false,
    "message": "يرجى التحقق من رقم الهاتف أولاً"
}
```

---

## 2. Delete Account (إلغاء التفعيل)

### 2.1 حذف الحساب
**Endpoint:** `POST /api/user/auth/profile/delete-account`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "تم إلغاء تفعيل الحساب بنجاح"
}
```

**ملاحظات:**
- لا يتم حذف الحساب فعلياً من قاعدة البيانات
- يتم تعيين `is_active = false`
- المستخدم لن يستطيع تسجيل الدخول بعد ذلك
- البيانات تبقى محفوظة (الطلبات، النقاط، إلخ)

---

### 2.2 التحقق من is_active

**في Login:**
```php
// الكود يتحقق من is_active = true
$user = User::where('phone', $phone)->where('is_active', true)->first();
```

**في Register:**
```php
// يتم التحقق من عدم وجود حساب نشط بنفس الرقم
if (User::where('phone', $phone)->where('is_active', true)->exists()) {
    throw new AccountAlreadyExistsException();
}
```

---

## 3. Delete Rating (حذف التعليق خلال 24 ساعة)

### ⚠️ الوضع الحالي
**لا يوجد endpoint لحذف التقييم حالياً**

### ✅ الحل المطلوب
يجب إضافة endpoint جديد:

**Endpoint:** `DELETE /api/user/ratings/{id}`

**Logic المطلوب:**
```php
// التحقق من أن التقييم للمستخدم الحالي
// التحقق من أن التقييم تم إنشاؤه خلال آخر 24 ساعة
if ($rating->created_at->diffInHours(now()) > 24) {
    throw new Exception('لا يمكن حذف التقييم بعد مرور 24 ساعة');
}
```

---

## 4. Product Images

### 4.1 صور المنتج في التفاصيل
**Endpoint:** `GET /api/user/products/{id}`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "منتج تجريبي",
        "images": [
            {
                "id": 1,
                "url": "http://localhost:8000/storage/products/image1.jpg",
                "order": 1
            },
            {
                "id": 2,
                "url": "http://localhost:8000/storage/products/image2.jpg",
                "order": 2
            }
        ]
    }
}
```

**ملاحظات:**
- الصور مرتبة حسب `order`
- كل صورة لها `id` و `url` و `order`

---

### 4.2 صور الـ Variants
**في نفس الـ Response:**
```json
{
    "shop_variants": [
        {
            "id": 1,
            "price": 100,
            "quantity": 50,
            "images": [
                {
                    "id": 3,
                    "url": "http://localhost:8000/storage/variants/variant1.jpg",
                    "order": 1
                }
            ]
        }
    ]
}
```

---

## 5. Category Images

### 5.1 صور الكاتيجوري
**Endpoint:** `GET /api/user/categories`

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "فواكه",
            "icon": "http://localhost:8000/storage/categories/fruits.png",
            "children": [
                {
                    "id": 2,
                    "name": "فواكه طازجة",
                    "icon": "http://localhost:8000/storage/categories/fresh-fruits.png"
                }
            ]
        }
    ]
}
```

**ملاحظات:**
- الحقل اسمه `icon` وليس `image`
- يرجع URL كامل جاهز للاستخدام

---

## 6. Active Points

### 6.1 ملخص النقاط
**Endpoint:** `GET /api/user/points/summary`

**Response:**
```json
{
    "success": true,
    "data": {
        "points": 450,
        "value": {
            "point_value": "1 pt = 0.01 $",
            "estimated_value": "4.50 $",
            "currency_code": "USD",
            "currency_symbol": "$"
        },
        "next_reward": "Next reward at 1,000 pts",
        "expiry": "2027-03-08",
        "earning_rules": [
            {
                "id": 1,
                "code": "user_registration",
                "title": "مكافأة إنشاء حساب",
                "type": "fixed",
                "value": 100,
                "min_order_amount": null,
                "expires_after_days": 365
            },
            {
                "id": 2,
                "code": "first_order",
                "title": "مكافأة أول طلب",
                "type": "fixed",
                "value": 50,
                "min_order_amount": null,
                "expires_after_days": 365
            }
        ]
    }
}
```

**ملاحظات:**
- `earning_rules` يرجع كل قواعد النقاط النشطة
- التايتل يرجع حسب لغة التطبيق (ar/en)

---

### 6.2 الفوائد النشطة (Active Benefits)
**Endpoint:** `GET /api/user/active-benefits`

**Response:**
```json
{
    "success": true,
    "data": {
        "coupons": [
            {
                "key": "point_coupon_exchange_id",
                "value": 5,
                "expired_at": "2026-04-08",
                "title": "خصم من النقاط"
            },
            {
                "key": "use_subscription_discount",
                "value": true,
                "expired_at": "2026-05-08",
                "title": "خصم من الباقة"
            }
        ],
        "free_deliveries": [
            {
                "key": "point_free_delivery_exchange_id",
                "value": 3,
                "expired_at": "2026-03-15",
                "title": "توصيل مجاني من النقاط"
            },
            {
                "key": "use_subscription_free_delivery",
                "value": true,
                "expired_at": "2026-05-08",
                "title": "توصيل مجاني من الباقة"
            }
        ]
    }
}
```

---

### 6.3 ملخص الملف الشخصي
**Endpoint:** `GET /api/user/profile-summary`

**Response:**
```json
{
    "success": true,
    "data": {
        "points": 450,
        "gifts_count": 3,
        "subscription_name": "الباقة الذهبية"
    }
}
```

**ملاحظات:**
- API بسيط للاستخدام في الـ header
- `subscription_name` يرجع `null` إذا لم يكن مشترك

---

## 7. Icons Management

### 7.1 Admin - إدارة الأيقونات

#### Get All Icons
**Endpoint:** `GET /api/admin/icons`

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": {
                "ar": "جديد",
                "en": "New"
            },
            "image": "http://localhost:8000/storage/icons/new.png",
            "description": {
                "ar": "منتج جديد",
                "en": "New product"
            },
            "is_active": true
        }
    ]
}
```

#### Create Icon
**Endpoint:** `POST /api/admin/icons`

**Request (multipart/form-data):**
```
name[ar]: "جديد"
name[en]: "New"
image: [file]
description[ar]: "منتج جديد"
description[en]: "New product"
is_active: true
```

#### Update Icon
**Endpoint:** `PUT /api/admin/icons/{id}`

**Request (multipart/form-data):**
```
name[ar]: "جديد - محدث"
name[en]: "New - Updated"
image: [file] (optional)
description[ar]: "منتج جديد محدث"
description[en]: "Updated new product"
is_active: false
```

---

### 7.2 Admin - ربط الأيقونات بالمنتجات

**في Product Create/Update:**
```json
{
    "name": {
        "ar": "منتج تجريبي",
        "en": "Test Product"
    },
    "category_id": 1,
    "price": 100,
    "icon_ids": [1, 2, 3]
}
```

---

### 7.3 User - عرض الأيقونات

**Endpoint:** `GET /api/user/products/{id}`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "منتج تجريبي",
        "icons": [
            {
                "id": 1,
                "name": "جديد",
                "image": "http://localhost:8000/storage/icons/new.png",
                "description": "منتج جديد"
            },
            {
                "id": 2,
                "name": "عرض خاص",
                "image": "http://localhost:8000/storage/icons/special-offer.png",
                "description": "عرض لفترة محدودة"
            }
        ]
    }
}
```

**ملاحظات:**
- الاسم والوصف يرجعوا حسب لغة التطبيق
- الأيقونات ترجع فقط إذا كانت مرتبطة بالمنتج

---

## 8. Badges

### 8.1 البادجات في المنتج
**Endpoint:** `GET /api/user/products/{id}`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "منتج تجريبي",
        "top_badges": [
            {
                "id": 1,
                "name": "خصم 50%",
                "image": "http://localhost:8000/storage/badges/discount.png",
                "position": "top"
            }
        ],
        "bottom_badges": [
            {
                "id": 2,
                "name": "توصيل مجاني",
                "image": "http://localhost:8000/storage/badges/free-delivery.png",
                "position": "bottom"
            }
        ]
    }
}
```

**ملاحظات:**
- البادجات مقسمة لـ `top_badges` و `bottom_badges`
- كل badge له `position` (top/bottom)

---

### 8.2 البادجات في قائمة المنتجات
**Endpoint:** `GET /api/user/products`

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "منتج تجريبي",
            "image": "http://localhost:8000/storage/products/product1.jpg",
            "top_badges": [
                {
                    "id": 1,
                    "name": "خصم 50%",
                    "image": "http://localhost:8000/storage/badges/discount.png"
                }
            ],
            "bottom_badges": []
        }
    ]
}
```

---

## 9. Bought With Products (يباع مع هذا المنتج)

### 9.1 المنتجات المباعة معاً
**Endpoint:** `GET /api/user/products/{id}`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "منتج تجريبي",
        "bought_with": [
            {
                "id": 2,
                "name": "منتج مرتبط 1",
                "image": "http://localhost:8000/storage/products/product2.jpg",
                "price": {
                    "original": 50,
                    "converted": 50,
                    "currency": "USD",
                    "symbol": "$"
                },
                "price_after_discount": {
                    "original": 40,
                    "converted": 40,
                    "currency": "USD",
                    "symbol": "$"
                }
            },
            {
                "id": 3,
                "name": "منتج مرتبط 2",
                "image": "http://localhost:8000/storage/products/product3.jpg",
                "price": {
                    "original": 30,
                    "converted": 30,
                    "currency": "USD",
                    "symbol": "$"
                }
            }
        ]
    }
}
```

**ملاحظات:**
- `bought_with` يرجع array من المنتجات
- كل منتج يرجع بنفس structure قائمة المنتجات
- يشمل الصورة، السعر، الخصم، إلخ

---

## 🧪 Frontend Testing Checklist

### Registration & Login
- [ ] التسجيل بالهاتف يعمل
- [ ] OTP يصل للهاتف
- [ ] التحقق من OTP يعمل
- [ ] نقاط التسجيل تُضاف (100 نقطة)
- [ ] إشعار النقاط يظهر
- [ ] تسجيل الدخول يعمل
- [ ] رسالة خطأ للحساب غير المسجل
- [ ] رسالة خطأ لكلمة المرور الخاطئة
- [ ] رسالة خطأ للحساب غير المفعل

### Delete Account
- [ ] حذف الحساب يعمل
- [ ] `is_active` يتغير لـ `false`
- [ ] لا يمكن تسجيل الدخول بعد الحذف
- [ ] البيانات تبقى محفوظة

### Product Images
- [ ] صور المنتج تظهر في التفاصيل
- [ ] صور الـ variants تظهر
- [ ] الصور مرتبة حسب `order`

### Category Images
- [ ] صور الكاتيجوري تظهر
- [ ] الحقل `icon` موجود

### Active Points
- [ ] ملخص النقاط يظهر
- [ ] قواعد النقاط ترجع
- [ ] الفوائد النشطة ترجع
- [ ] ملخص الملف الشخصي يعمل

### Icons
- [ ] الأيقونات تظهر مع المنتج
- [ ] الاسم والوصف بالعربي والانكليزي
- [ ] Admin يقدر يضيف أيقونات
- [ ] Admin يقدر يربط أيقونات بالمنتجات

### Badges
- [ ] البادجات تظهر (top/bottom)
- [ ] البادجات في قائمة المنتجات
- [ ] البادجات في تفاصيل المنتج

### Bought With
- [ ] المنتجات المباعة معاً تظهر
- [ ] كل منتج له صورة وسعر
- [ ] الخصومات تظهر

---

## 📝 Notes للفرونت اند

1. **Authentication:**
   - احفظ الـ token في localStorage
   - أضف الـ token في كل request: `Authorization: Bearer {token}`

2. **Language:**
   - أرسل header: `Accept-Language: ar` أو `en`
   - الترجمات ترجع تلقائياً حسب اللغة

3. **Currency:**
   - الأسعار ترجع محولة حسب عملة المستخدم
   - استخدم `converted` للعرض

4. **Images:**
   - كل الصور ترجع URL كامل جاهز للاستخدام
   - لا تحتاج إضافة base URL

5. **Errors:**
   - كل الأخطاء ترجع بنفس الـ structure:
   ```json
   {
       "success": false,
       "message": "رسالة الخطأ"
   }
   ```


---

## 10. Reorder (إعادة الطلب)

### 10.1 إعادة طلب سابق
**Endpoint:** `POST /api/user/orders/reorder/{orderId}`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "تم إعادة الطلب بنجاح",
    "data": {
        "id": 25,
        "order_code": "ORD-25",
        "status": "pending",
        "total": 150.50,
        "subtotal": 130.00,
        "delivery_price": 20.50,
        "basket_discount": 0,
        "coupon_discount": 0,
        "total_quantity": 3,
        "cart_type": "normal",
        "is_instant_delivery": false,
        "user_address": {
            "id": 1,
            "address": "شارع الملك فهد، الرياض",
            "city": "الرياض",
            "governorate": "الرياض"
        },
        "payment_method": {
            "id": 1,
            "name": "الدفع عند الاستلام"
        },
        "items": [
            {
                "id": 50,
                "product_name": "تفاح أحمر",
                "variant_attributes": "حجم: كبير",
                "quantity": 2,
                "price": 50.00,
                "discount": 0,
                "item_status": "pending"
            },
            {
                "id": 51,
                "product_name": "موز",
                "variant_attributes": "حجم: متوسط",
                "quantity": 1,
                "price": 30.00,
                "discount": 0,
                "item_status": "pending"
            }
        ],
        "created_at": "2026-03-08T18:00:00.000000Z",
        "pending_at": "2026-03-08T18:00:00.000000Z"
    }
}
```

---

### 10.2 كيف يعمل Reorder

**الخطوات:**
1. يتم جلب الطلب الأصلي مع كل تفاصيله
2. يتم التحقق من أن الطلب يخص المستخدم الحالي
3. يتم إنشاء طلب جديد بنفس البيانات:
   - نفس العنوان
   - نفس طريقة الدفع
   - نفس المنتجات والكميات
   - نفس الأسعار
   - نفس الخصومات (إن وجدت)
   - نفس الكوبونات (إن وجدت)
   - نفس الباقة (إن وجدت)

**ما يتم إعادة تعيينه:**
- الحالة: `pending`
- رقم الطلب: جديد
- التواريخ: جديدة
- السائق: `null`
- حالة العناصر: `pending`

---

### 10.3 حالات الخطأ

```json
// الطلب غير موجود
{
    "success": false,
    "message": "Order not found"
}

// الطلب لا يخص المستخدم
{
    "success": false,
    "message": "Order not found"
}
```

---

### 10.4 استخدام Reorder في الفرونت اند

**مثال 1: زر إعادة الطلب في تفاصيل الطلب**
```javascript
async function reorderOrder(orderId) {
    try {
        const response = await fetch(`/api/user/orders/reorder/${orderId}`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // عرض رسالة نجاح
            showSuccessMessage('تم إعادة الطلب بنجاح');
            
            // الانتقال لصفحة الطلب الجديد
            window.location.href = `/orders/${data.data.id}`;
        }
    } catch (error) {
        showErrorMessage('حدث خطأ أثناء إعادة الطلب');
    }
}
```

**مثال 2: زر إعادة الطلب في قائمة الطلبات**
```html
<div class="order-card">
    <h3>طلب #ORD-15</h3>
    <p>الحالة: مكتمل</p>
    <p>الإجمالي: 150.50 ريال</p>
    <button onclick="reorderOrder(15)" class="reorder-btn">
        🔄 إعادة الطلب
    </button>
</div>
```

**مثال 3: تأكيد قبل إعادة الطلب**
```javascript
function confirmReorder(orderId, orderTotal) {
    if (confirm(`هل تريد إعادة طلب بقيمة ${orderTotal} ريال؟`)) {
        reorderOrder(orderId);
    }
}
```

---

### 10.5 ملاحظات مهمة

1. **الأسعار:**
   - يتم نسخ الأسعار من الطلب الأصلي
   - قد تكون الأسعار الحالية مختلفة
   - يُفضل عرض تنبيه للمستخدم

2. **التوفر:**
   - لا يتم التحقق من توفر المنتجات
   - قد تكون بعض المنتجات غير متوفرة
   - يُفضل التحقق بعد إنشاء الطلب

3. **الخصومات:**
   - يتم نسخ الخصومات من الطلب الأصلي
   - قد لا تكون صالحة الآن
   - الكوبونات قد تكون منتهية

4. **الباقة:**
   - إذا كان الطلب الأصلي يستخدم باقة
   - يتم نسخ بيانات الباقة
   - يجب التحقق من صلاحية الباقة

5. **النقاط:**
   - إذا كان الطلب الأصلي يستخدم نقاط
   - يتم نسخ بيانات النقاط
   - يجب التحقق من رصيد النقاط الحالي

---

### 10.6 UI/UX Recommendations

**متى يظهر زر إعادة الطلب:**
- في تفاصيل الطلب المكتمل
- في قائمة الطلبات السابقة
- في صفحة "طلباتي"

**تصميم الزر:**
```css
.reorder-btn {
    background: #4CAF50;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
}

.reorder-btn:hover {
    background: #45a049;
}
```

**رسائل التأكيد:**
- "هل تريد إعادة هذا الطلب؟"
- "سيتم إنشاء طلب جديد بنفس المنتجات"
- "قد تختلف الأسعار عن الطلب الأصلي"

---

## 🧪 Frontend Testing Checklist (Updated)

### Reorder
- [ ] زر إعادة الطلب يظهر في تفاصيل الطلب
- [ ] زر إعادة الطلب يظهر في قائمة الطلبات
- [ ] إعادة الطلب تعمل بنجاح
- [ ] يتم إنشاء طلب جديد برقم جديد
- [ ] المنتجات تُنسخ بنفس الكميات
- [ ] الأسعار تُنسخ من الطلب الأصلي
- [ ] العنوان يُنسخ من الطلب الأصلي
- [ ] طريقة الدفع تُنسخ من الطلب الأصلي
- [ ] رسالة نجاح تظهر بعد إعادة الطلب
- [ ] الانتقال للطلب الجديد يعمل
- [ ] رسالة خطأ تظهر للطلب غير الموجود
- [ ] رسالة خطأ تظهر لطلب مستخدم آخر

---

## 📱 Quick Reference للفرونت اند

### Reorder Flow
```
1. User clicks "إعادة الطلب" button
2. Show confirmation dialog (optional)
3. POST /api/user/orders/reorder/{orderId}
4. Show success message
5. Redirect to new order page
```

### Error Handling
```javascript
try {
    const response = await reorderOrder(orderId);
    if (response.success) {
        showSuccess('تم إعادة الطلب بنجاح');
        navigateToOrder(response.data.id);
    }
} catch (error) {
    if (error.status === 404) {
        showError('الطلب غير موجود');
    } else {
        showError('حدث خطأ أثناء إعادة الطلب');
    }
}
```

