# 🎁 Active Benefits API Documentation
## توثيق API المزايا النشطة

---

## 📋 نظرة عامة

هذا الـ API يرجع جميع المزايا النشطة للمستخدم (استبدالات النقاط + مزايا الباقة) بشكل منظم ومقسم لقسمين:
1. **الكوبونات والخصومات** (Coupons)
2. **التوصيل المجاني** (Free Deliveries)

---

## 🔗 Endpoint

```http
GET /api/user/active-benefits
```

### Authentication
✅ يحتاج Authentication Token

```
Authorization: Bearer {token}
```

---

## 📤 Response Structure

### Success Response (200)

```json
{
  "success": true,
  "data": {
    "coupons": [
      {
        "key": "point_coupon_exchange_id",
        "value": 25,
        "title": "خصم من النقاط",
        "discount_amount": 50,
        "points_used": 500,
        "expired_at": "2026-04-05"
      },
      {
        "key": "use_subscription_discount",
        "value": true,
        "title": "خصم من الباقة",
        "discount_percentage": 10,
        "remaining_orders": 7,
        "expired_at": "2026-03-31"
      }
    ],
    "free_deliveries": [
      {
        "key": "point_free_delivery_exchange_id",
        "value": 30,
        "title": "توصيل مجاني من النقاط",
        "points_used": 300,
        "expired_at": "2026-04-05"
      },
      {
        "key": "use_subscription_free_delivery",
        "value": true,
        "title": "توصيل مجاني من الباقة",
        "remaining_count": 3,
        "expired_at": "2026-03-31"
      }
    ],
    "has_benefits": true
  }
}
```

### Response When No Benefits (200)

```json
{
  "success": true,
  "data": {
    "coupons": [],
    "free_deliveries": [],
    "has_benefits": false
  }
}
```

---

## 📊 Response Fields Explanation

### Coupons Array

كل عنصر في مصفوفة `coupons` يحتوي على:

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| key | string | نوع الخصم | `point_coupon_exchange_id` أو `use_subscription_discount` |
| value | integer/boolean | القيمة (ID أو true) | `25` أو `true` |
| title | string | عنوان الخصم | "خصم من النقاط" أو "خصم من الباقة" |
| discount_amount | float | قيمة الخصم (للنقاط) | `50` |
| discount_percentage | integer | نسبة الخصم (للباقة) | `10` |
| points_used | integer | النقاط المستخدمة | `500` |
| remaining_orders | integer | الطلبات المتبقية (للباقة) | `7` |
| expired_at | string | تاريخ الانتهاء | `"2026-04-05"` |

### Free Deliveries Array

كل عنصر في مصفوفة `free_deliveries` يحتوي على:

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| key | string | نوع التوصيل المجاني | `point_free_delivery_exchange_id` أو `use_subscription_free_delivery` |
| value | integer/boolean | القيمة (ID أو true) | `30` أو `true` |
| title | string | عنوان التوصيل | "توصيل مجاني من النقاط" أو "توصيل مجاني من الباقة" |
| points_used | integer | النقاط المستخدمة | `300` |
| remaining_count | integer | العدد المتبقي (للباقة) | `3` |
| expired_at | string | تاريخ الانتهاء | `"2026-04-05"` |

---

## 🎯 Use Cases - حالات الاستخدام

### 1. عرض المزايا المتاحة للمستخدم

```javascript
// Fetch active benefits
const response = await fetch('/api/user/active-benefits', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const data = await response.json();

// Display coupons
data.data.coupons.forEach(coupon => {
  console.log(`${coupon.title}: ${coupon.key} = ${coupon.value}`);
});

// Display free deliveries
data.data.free_deliveries.forEach(delivery => {
  console.log(`${delivery.title}: ${delivery.key} = ${delivery.value}`);
});
```

### 2. استخدام المزايا عند إنشاء طلب

```javascript
// Get first available coupon
const firstCoupon = data.data.coupons[0];

// Get first available free delivery
const firstFreeDelivery = data.data.free_deliveries[0];

// Create order with benefits
const orderData = {
  address_id: 1,
  payment_method_id: 2,
  items: [...],
  // Add coupon
  [firstCoupon.key]: firstCoupon.value,
  // Add free delivery
  [firstFreeDelivery.key]: firstFreeDelivery.value
};

await createOrder(orderData);
```

### 3. عرض UI للمستخدم

```jsx
function BenefitsSelector({ benefits }) {
  const [selectedCoupon, setSelectedCoupon] = useState(null);
  const [selectedFreeDelivery, setSelectedFreeDelivery] = useState(null);

  return (
    <div>
      {/* Coupons Section */}
      <h3>الخصومات المتاحة</h3>
      {benefits.coupons.map((coupon, index) => (
        <div key={index} onClick={() => setSelectedCoupon(coupon)}>
          <input 
            type="radio" 
            name="coupon" 
            checked={selectedCoupon?.key === coupon.key}
          />
          <label>
            {coupon.title}
            {coupon.discount_amount && ` - خصم ${coupon.discount_amount}`}
            {coupon.discount_percentage && ` - خصم ${coupon.discount_percentage}%`}
            <small>ينتهي في: {coupon.expired_at}</small>
          </label>
        </div>
      ))}

      {/* Free Deliveries Section */}
      <h3>التوصيل المجاني المتاح</h3>
      {benefits.free_deliveries.map((delivery, index) => (
        <div key={index} onClick={() => setSelectedFreeDelivery(delivery)}>
          <input 
            type="radio" 
            name="free_delivery" 
            checked={selectedFreeDelivery?.key === delivery.key}
          />
          <label>
            {delivery.title}
            {delivery.remaining_count && ` - متبقي ${delivery.remaining_count}`}
            <small>ينتهي في: {delivery.expired_at}</small>
          </label>
        </div>
      ))}
    </div>
  );
}
```

---

## 📝 Important Notes - ملاحظات مهمة

### ترتيب العناصر

1. **الكوبونات**: يتم عرضها حسب تاريخ الإنشاء (الأحدث أولاً)
2. **التوصيل المجاني من النقاط**: يتم عرضها حسب تاريخ الانتهاء (الأقرب للانتهاء أولاً)
3. **مزايا الباقة**: تظهر دائماً في النهاية

### القيود

- ✅ يمكن استخدام خصم واحد فقط في الطلب
- ✅ يمكن استخدام توصيل مجاني واحد فقط في الطلب
- ⚠️ لا يمكن الجمع بين خصم من النقاط وخصم من الباقة
- ⚠️ لا يمكن الجمع بين توصيل مجاني من النقاط وتوصيل مجاني من الباقة

### تحديث البيانات

- يتم تحديث البيانات تلقائياً عند:
  - استخدام استبدال في طلب
  - انتهاء صلاحية استبدال
  - انتهاء الباقة
  - استهلاك جميع مزايا الباقة

---

## 🔄 Integration Flow - سير العمل

```
1. المستخدم يفتح صفحة إنشاء الطلب
   ↓
2. استدعاء GET /api/user/active-benefits
   ↓
3. عرض المزايا المتاحة للمستخدم
   ↓
4. المستخدم يختار خصم وتوصيل مجاني
   ↓
5. إرسال الطلب مع المزايا المختارة
   ↓
6. النظام يتحقق ويطبق المزايا
   ↓
7. تحديث حالة الاستبدالات والباقة
```

---

## 🧪 Testing Examples

### cURL Example

```bash
curl -X GET "https://api.example.com/api/user/active-benefits" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
```

### Postman Example

```
GET /api/user/active-benefits
Headers:
  Authorization: Bearer {token}
```

### Expected Response

```json
{
  "success": true,
  "data": {
    "coupons": [
      {
        "key": "point_coupon_exchange_id",
        "value": 25,
        "title": "خصم من النقاط",
        "discount_amount": 50,
        "points_used": 500,
        "expired_at": "2026-04-05"
      }
    ],
    "free_deliveries": [
      {
        "key": "point_free_delivery_exchange_id",
        "value": 30,
        "title": "توصيل مجاني من النقاط",
        "points_used": 300,
        "expired_at": "2026-04-05"
      }
    ],
    "has_benefits": true
  }
}
```

---

## ✅ Summary - الخلاصة

هذا الـ API يوفر:
- ✅ جميع الاستبدالات النشطة من النقاط
- ✅ جميع مزايا الباقة النشطة
- ✅ تنظيم واضح (كوبونات + توصيل مجاني)
- ✅ معلومات كاملة لكل ميزة
- ✅ سهولة الاستخدام في الفرونت إند

الـ API جاهز للاستخدام! 🎉
