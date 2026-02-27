# API للتحقق من إمكانية تقييم المنتج
## Can Rate Product API Documentation

---

## نظرة عامة

API يسمح لليوزر بالتحقق إذا كان يستطيع تقييم منتج معين أو لا. الشرط الأساسي هو أن يكون اليوزر قد طلب أي variant من هذا المنتج في طلب سابق وتم توصيله بنجاح.

### الشروط
- ✅ اليوزر لازم يكون طلب أي variant من المنتج في طلب سابق
- ✅ الطلب لازم يكون مكتمل (status = DELIVERED)
- ✅ اليوزر ما يكون قيّم المنتج مسبقاً

---

## API Endpoint

### Check if User Can Rate Product
**الاستخدام**: التحقق من إمكانية تقييم منتج معين

**Endpoint**: `GET /api/user/ratings/can-rate`

**Authentication**: Required (Bearer Token)

**Parameters**:
- `product_id` (required): معرف المنتج

---

## Request Example

### cURL
```bash
curl -X GET "http://localhost:8000/api/user/ratings/can-rate?product_id=45" \
  -H "Authorization: Bearer YOUR_USER_TOKEN" \
  -H "Accept: application/json"
```

### JavaScript (Axios)
```javascript
const response = await axios.get('/api/user/ratings/can-rate', {
  params: {
    product_id: 45
  },
  headers: {
    'Authorization': 'Bearer YOUR_USER_TOKEN',
    'Accept': 'application/json'
  }
});
```

### PHP (Laravel HTTP Client)
```php
$response = Http::withToken($userToken)
    ->get('/api/user/ratings/can-rate', [
        'product_id' => 45
    ]);
```

---

## Response Examples

### 1. يستطيع التقييم (Can Rate)
**Status Code**: 200

```json
{
    "status": true,
    "message": "You can rate this product",
    "data": {
        "can_rate": true,
        "product_id": 45
    }
}
```

**الحالة**: اليوزر طلب أي variant من المنتج سابقاً وتم توصيله ولم يقيّمه بعد.

---

### 2. لم يطلب المنتج (Not Purchased)
**Status Code**: 200

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

**الحالة**: اليوزر لم يطلب أي variant من هذا المنتج أبداً، أو طلبه لكن الطلب لم يكتمل بعد.

---

### 3. قيّم المنتج مسبقاً (Already Rated)
**Status Code**: 200

```json
{
    "status": true,
    "message": "You have already rated this product",
    "data": {
        "can_rate": false,
        "reason": "You have already rated this product",
        "reason_ar": "لقد قمت بتقييم هذا المنتج مسبقاً"
    }
}
```

**الحالة**: اليوزر قيّم هذا المنتج مسبقاً.

---

### 4. Validation Error
**Status Code**: 422

```json
{
    "message": "The product id field is required.",
    "errors": {
        "product_id": [
            "The product id field is required."
        ]
    }
}
```

**الحالة**: لم يتم إرسال `product_id`.

---

### 5. Not Found
**Status Code**: 200

```json
{
    "status": true,
    "message": "Product not found",
    "data": {
        "can_rate": false,
        "reason": "Product not found",
        "reason_ar": "المنتج غير موجود"
    }
}
```

**الحالة**: الـ `product_id` غير موجود في قاعدة البيانات.

---

### 6. Unauthenticated
**Status Code**: 401

```json
{
    "message": "Unauthenticated."
}
```

**الحالة**: لم يتم إرسال توكن المصادقة أو التوكن غير صحيح.

---

## حالات الاستخدام

### 1. قبل عرض نموذج التقييم
```javascript
// تحقق أولاً إذا كان اليوزر يستطيع التقييم
const checkCanRate = async (productId) => {
  try {
    const response = await axios.get('/api/user/ratings/can-rate', {
      params: { product_id: productId }
    });

    if (response.data.data.can_rate) {
      // اعرض نموذج التقييم
      showRatingForm(response.data.data.product_id);
    } else {
      // اعرض رسالة توضيحية
      showMessage(response.data.data.reason_ar);
    }
  } catch (error) {
    console.error('Error checking rating permission:', error);
  }
};
```

### 2. في صفحة المنتج
```javascript
// اعرض زر التقييم فقط إذا كان اليوزر يستطيع التقييم
const ProductPage = ({ productId }) => {
  const [canRate, setCanRate] = useState(null);

  useEffect(() => {
    checkRatingPermission(productId);
  }, [productId]);

  const checkRatingPermission = async (id) => {
    const response = await axios.get('/api/user/ratings/can-rate', {
      params: { product_id: id }
    });
    setCanRate(response.data.data.can_rate);
  };

  return (
    <div>
      {canRate === true && (
        <button onClick={openRatingForm}>قيّم المنتج</button>
      )}
      {canRate === false && (
        <p>يجب عليك شراء المنتج أولاً لتتمكن من تقييمه</p>
      )}
    </div>
  );
};
```

### 3. في قائمة الطلبات السابقة
```javascript
// اعرض زر التقييم بجانب كل منتج
const OrderHistory = ({ orders }) => {
  return orders.map(order => (
    <div key={order.id}>
      {order.items.map(item => (
        <div key={item.id}>
          <p>{item.product_name}</p>
          <RateButton productId={item.product_id} />
        </div>
      ))}
    </div>
  ));
};

const RateButton = ({ productId }) => {
  const [canRate, setCanRate] = useState(false);

  useEffect(() => {
    checkIfCanRate(productId);
  }, [productId]);

  const checkIfCanRate = async (id) => {
    const response = await axios.get('/api/user/ratings/can-rate', {
      params: { product_id: id }
    });
    setCanRate(response.data.data.can_rate);
  };

  if (!canRate) return null;

  return <button onClick={() => openRatingForm(productId)}>قيّم</button>;
};
```

---

## الملفات المُنشأة/المُعدّلة

### Services
- `app/Services/User/RatingService.php` - تم إضافة method `canRateProduct()`

### Controllers
- `app/Http/Controllers/User/Rating/RatingController.php` - تم إضافة method `canRate()`

### Routes
- `routes/api/user.php` - تم إضافة route `GET /api/user/ratings/can-rate`

---

## الآلية الداخلية

### 1. التحقق من الشراء
```php
// يتحقق إذا كان اليوزر طلب أي variant من هذا المنتج في طلب مكتمل
// ShopProductVariant -> ProductVariant -> Product
$hasOrdered = OrderItem::whereHas('order', function ($query) use ($userId) {
    $query->where('user_id', $userId)
          ->where('status', OrderStatus::DELIVERED->value);
})
->whereHas('shopProductVariant.productVariant', function ($query) use ($productId) {
    $query->where('product_id', $productId);
})
->exists();
```

### 2. التحقق من التقييم السابق
```php
// يتحقق إذا كان اليوزر قيّم المنتج مسبقاً
$alreadyRated = Rating::where('user_id', $userId)
    ->where('rateable_type', Product::class)
    ->where('rateable_id', $productId)
    ->exists();
```

---

## ملاحظات مهمة

### 1. التقييم على مستوى المنتج
- التقييم يكون للـ **Product** وليس للـ **Variant**
- إذا اليوزر قيّم المنتج مرة، ما يقدر يقيّمه مرة ثانية حتى لو اشترى variant مختلف
- الـ API يتحقق من شراء **أي variant** من المنتج

### 2. البنية الهرمية
```
Product (المنتج الأساسي)
  └── ProductVariant (نسخة من المنتج بمواصفات معينة)
      └── ShopProductVariant (النسخة في متجر معين بسعر وكمية)
          └── OrderItem (العنصر في الطلب)
```

### 3. حالة الطلب
- فقط الطلبات المكتملة (DELIVERED) تُحسب
- الطلبات الملغاة أو المعلقة لا تُحسب

### 3. الأداء
- الاستعلامات محسّنة باستخدام `whereHas` و `exists()`
- لا يتم جلب بيانات غير ضرورية
- العلاقات المتداخلة: `shopProductVariant.productVariant`

---

## الأخطاء الشائعة

### 1. "Unauthenticated"
**السبب**: توكن اليوزر غير صحيح أو منتهي
**الحل**: تسجيل دخول جديد والحصول على توكن جديد

### 2. "The product id field is required"
**السبب**: لم يتم إرسال `product_id`
**الحل**: تأكد من إرسال الـ parameter

### 3. "Product not found"
**السبب**: الـ product_id غير موجود في قاعدة البيانات
**الحل**: تحقق من صحة الـ ID

### 4. "You must purchase this product before rating it"
**السبب**: اليوزر لم يطلب المنتج أو الطلب لم يكتمل
**الحل**: اطلب المنتج أولاً وانتظر حتى يتم توصيله

### 5. "You have already rated this product"
**السبب**: اليوزر قيّم المنتج مسبقاً
**الحل**: يمكن تعديل التقييم السابق بدلاً من إنشاء تقييم جديد

---

## التطويرات المستقبلية

### 1. السماح بتقييمات متعددة
```php
// يمكن السماح لليوزر بتقييم المنتج أكثر من مرة
// إذا اشتراه أكثر من مرة
```

### 2. تقييم الـ Variant بدلاً من المنتج
```php
// يمكن تغيير النظام ليقيّم الـ variant بدلاً من المنتج
// مفيد إذا كان في فروقات كبيرة بين الـ variants
```

### 3. إضافة معلومات إضافية
```php
// يمكن إرجاع معلومات إضافية مثل:
// - تاريخ الشراء
// - عدد مرات الشراء
// - آخر طلب
```

---

## الاختبار

### 1. اختبار يوزر طلب المنتج
```bash
# يوزر طلب أي variant من المنتج وتم توصيله
GET /api/user/ratings/can-rate?product_id=45
# Expected: can_rate = true
```

### 2. اختبار يوزر لم يطلب المنتج
```bash
# يوزر لم يطلب أي variant من المنتج أبداً
GET /api/user/ratings/can-rate?product_id=999
# Expected: can_rate = false, reason = "You must purchase..."
```

### 3. اختبار يوزر قيّم المنتج مسبقاً
```bash
# يوزر طلب المنتج وقيّمه مسبقاً
GET /api/user/ratings/can-rate?product_id=45
# Expected: can_rate = false, reason = "You have already rated..."
```

---

## الخلاصة

API بسيط وفعّال للتحقق من إمكانية تقييم المنتج:
- ✅ يتحقق من الشراء السابق
- ✅ يتحقق من حالة الطلب (DELIVERED)
- ✅ يتحقق من التقييم السابق
- ✅ يرجع رسائل واضحة بالعربي والإنجليزي
- ✅ محمي ومحسّن للأداء

**جاهز للاستخدام مباشرة!** 🎉

---

**آخر تحديث**: 27 فبراير 2026
**الإصدار**: 1.0
