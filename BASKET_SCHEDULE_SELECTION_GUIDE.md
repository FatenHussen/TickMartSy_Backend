# 📅 Basket Schedule Selection Guide
## دليل اختيار جدولة السلة

---

## 📋 نظرة عامة

تم تعديل النظام ليسمح للمستخدم باختيار جدولة محددة للسلة المجدولة، وعلى أساسها يتم تطبيق الخصم.

---

## 🔄 التغييرات

### قبل التعديل ❌
- النظام كان ياخد أول جدولة نشطة تلقائياً
- المستخدم ما كان يقدر يختار الجدولة

### بعد التعديل ✅
- المستخدم يختار الجدولة اللي يريدها
- يتم تطبيق خصم الجدولة المختارة
- إذا ما اختار جدولة، يتم تطبيق خصم السلة العادي فقط

---

## 📝 Request Body

### إنشاء طلب مع سلة مجدولة

```json
{
  "address_id": 1,
  "payment_method_id": 2,
  "is_instant_delivery": false,
  "cart_type": "schedule_admin_cart",
  "admin_schedule_basket_id": 5,
  "basket_schedule_id": 10,
  "items": [
    {
      "shop_product_variant_id": 15,
      "quantity": 2
    }
  ]
}
```

### الحقول المطلوبة

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| cart_type | string | ✅ Yes | يجب أن يكون `schedule_admin_cart` |
| admin_schedule_basket_id | integer | ✅ Yes | معرف السلة المجدولة |
| basket_schedule_id | integer | ❌ No | معرف الجدولة المختارة |

---

## 🎯 سيناريوهات الاستخدام

### 1️⃣ المستخدم يختار جدولة محددة

```json
{
  "cart_type": "schedule_admin_cart",
  "admin_schedule_basket_id": 5,
  "basket_schedule_id": 10
}
```

**النتيجة:**
- ✅ يتم تطبيق خصم الجدولة رقم 10
- ✅ إذا الجدولة فيها خصم 15%، يتم تطبيقه
- ✅ إذا الجدولة ما فيها خصم، يتم تطبيق خصم السلة العادي

---

### 2️⃣ المستخدم ما يختار جدولة

```json
{
  "cart_type": "schedule_admin_cart",
  "admin_schedule_basket_id": 5
}
```

**النتيجة:**
- ✅ يتم تطبيق خصم السلة العادي فقط
- ❌ لا يتم تطبيق أي خصم جدولة

---

### 3️⃣ المستخدم يختار جدولة غير موجودة أو غير نشطة

```json
{
  "cart_type": "schedule_admin_cart",
  "admin_schedule_basket_id": 5,
  "basket_schedule_id": 999
}
```

**النتيجة:**
- ✅ يتم تطبيق خصم السلة العادي (Fallback)
- ⚠️ لا يتم رفض الطلب

---

## 📊 مثال كامل

### Step 1: عرض السلة المجدولة مع جدولاتها

```http
GET /api/user/baskets/5
```

**Response:**
```json
{
  "id": 5,
  "name": "سلة الخضار الأسبوعية",
  "discount": 5,
  "delivery_price": 10,
  "schedules": [
    {
      "id": 10,
      "day_of_week": "monday",
      "time": "09:00",
      "discount_value": 15,
      "is_active": true
    },
    {
      "id": 11,
      "day_of_week": "wednesday",
      "time": "14:00",
      "discount_value": 10,
      "is_active": true
    }
  ]
}
```

### Step 2: المستخدم يختار الجدولة

```javascript
const selectedSchedule = {
  id: 10,
  discount_value: 15
};
```

### Step 3: إنشاء الطلب

```http
POST /api/user/orders
```

**Request Body:**
```json
{
  "address_id": 1,
  "payment_method_id": 2,
  "is_instant_delivery": false,
  "cart_type": "schedule_admin_cart",
  "admin_schedule_basket_id": 5,
  "basket_schedule_id": 10,
  "items": [
    {
      "shop_product_variant_id": 15,
      "quantity": 2
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 150,
    "subtotal": 100.00,
    "basket_discount": 15,
    "total": 85.00,
    "delivery_price": 10.00,
    "total_with_delivery": 95.00
  }
}
```

---

## 🎨 UI/UX للفرونت إند

### عرض الجدولات للمستخدم

```jsx
function ScheduleSelector({ basket }) {
  const [selectedSchedule, setSelectedSchedule] = useState(null);

  return (
    <div>
      <h3>اختر موعد التوصيل</h3>
      
      {basket.schedules.map(schedule => (
        <div 
          key={schedule.id}
          onClick={() => setSelectedSchedule(schedule)}
          className={selectedSchedule?.id === schedule.id ? 'selected' : ''}
        >
          <input 
            type="radio" 
            name="schedule"
            checked={selectedSchedule?.id === schedule.id}
          />
          <label>
            <strong>{schedule.day_of_week} - {schedule.time}</strong>
            {schedule.discount_value > 0 && (
              <span className="discount-badge">
                خصم {schedule.discount_value}%
              </span>
            )}
          </label>
        </div>
      ))}
      
      {!selectedSchedule && (
        <div className="info">
          💡 إذا لم تختر موعد، سيتم تطبيق خصم السلة العادي ({basket.discount}%)
        </div>
      )}
    </div>
  );
}
```

### إنشاء الطلب

```javascript
async function createOrder(basket, selectedSchedule, items) {
  const orderData = {
    address_id: userAddress.id,
    payment_method_id: paymentMethod.id,
    is_instant_delivery: false,
    cart_type: 'schedule_admin_cart',
    admin_schedule_basket_id: basket.id,
    items: items
  };

  // Add schedule if selected
  if (selectedSchedule) {
    orderData.basket_schedule_id = selectedSchedule.id;
  }

  const response = await fetch('/api/user/orders', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(orderData)
  });

  return await response.json();
}
```

---

## ⚠️ ملاحظات مهمة

1. **الجدولة يجب أن تكون نشطة**: `is_active = true`
2. **الجدولة يجب أن تنتمي للسلة المختارة**
3. **إذا الجدولة ما فيها خصم**: يتم استخدام خصم السلة العادي
4. **الحقل اختياري**: `basket_schedule_id` مش إجباري

---

## ✅ الخلاصة

الآن المستخدم يقدر:
- ✅ يشوف جميع الجدولات المتاحة للسلة
- ✅ يختار الجدولة اللي يريدها
- ✅ يحصل على خصم الجدولة المختارة
- ✅ يطلب بدون اختيار جدولة (خصم السلة العادي)

النظام جاهز! 🎉
