# Driver Order APIs (قسم الطلبات فقط)

هذا الملف مخصص لشرح واجهات الـ API الخاصة بتطبيق الدرايفر، لقسم الطلبات فقط.

**Base URL**

`/api/driver`

**Authentication**

كل الـ endpoints هنا تتطلب `auth:driver` مع هيدر:

`Authorization: Bearer {driver_token}`

**Response Envelope**

الـ responses الناجحة ترجع الشكل التالي:

```json
{
  "status": true,
  "message": "Success",
  "data": {}
}
```

ملاحظات أخطاء:

- Validation errors ترجع عادة `422` مع حقول `errors`.
- بعض الأخطاء من الـ service ترجع فقط:

```json
{
  "message": "Error message"
}
```

مع كود خطأ مناسب (غالباً `400` أو `403`).

---

## 1) جلب الطلبات حسب الحالة

**GET** `/api/driver/orders`

**Query Params**

- `status` (required): `pending | preparing | out_delivery | delivered`
- `assigned_by` (optional): `admin | driver`

**Response (data: array)**

كل عنصر يمثل طلب بصيغة مختصرة:

```json
{
  "id": 123,
  "order_code": "ORD-0001",
  "status": "pending",
  "cart_type": "basket",
  "is_instant_delivery": true,
  "delivery_price": 5000,
  "total": 25000,
  "subtotal": 20000,
  "total_with_delivery": 25000,
  "total_quantity": 4,
  "basket_discount": 0,
  "coupon_discount": 0,
  "subscription_discount": 0,
  "subscription_free_delivery": false,
  "created_at": "2026-03-10 15:12:00",
  "assigned_by": "driver",
  "affiliate_rate": 0,
  "affiliate_source": null,
  "affiliate_commission": 0,
  "user": {
    "id": 77,
    "name": "User Name",
    "email": "user@example.com",
    "phone": "0999999999",
    "affiliate": {
      "is_affiliate": false,
      "affiliate_approved": false,
      "affiliate_id": null
    },
    "created_at": "2026-03-01 10:00"
  }
}
```

---

## 2) جلب تفاصيل طلب واحد

**GET** `/api/driver/orders/show/{orderId}`

**Response (data: object)**

يرجع تفاصيل كاملة مع تجميع العناصر حسب المحل:

```json
{
  "id": 123,
  "order_code": "ORD-0001",
  "status": "out_delivery",
  "cart_type": "basket",
  "is_instant_delivery": false,
  "delivery_price": 5000,
  "subtotal": 20000,
  "total": 25000,
  "total_quantity": 4,
  "assigned_by": "admin",
  "created_at": "2026-03-10 15:12:00",
  "user": {
    "id": 77,
    "name": "User Name",
    "email": "user@example.com",
    "phone": "0999999999",
    "affiliate": {
      "is_affiliate": false,
      "affiliate_approved": false,
      "affiliate_id": null
    },
    "created_at": "2026-03-01 10:00"
  },
  "driver": {
    "id": 10,
    "name": "Driver Name",
    "phone": "0944444444",
    "status": "available",
    "image": "https://...",
    "rate_per_order": 10,
    "is_active": true,
    "average_rating": 4.8,
    "total_orders": 120,
    "completed_orders": 110,
    "total_earnings": 345000,
    "created_at": "2025-12-01 10:00:00"
  },
  "user_address": {
    "id": 5,
    "label": "Home",
    "street_name": "Street Name",
    "nearest_landmark": "Landmark",
    "building_number": "12",
    "floor_apartment": "3B",
    "contact_phone": "0999999999",
    "lat": 33.5138,
    "lng": 36.2765,
    "is_default": true,
    "area": "Area Name",
    "created_at": "2026-03-01 10:00:00"
  },
  "payment_method": {
    "id": 1,
    "name": "Cash"
  },
  "items": {
    "Shop A": {
      "shop": "Shop A",
      "lat": 33.51,
      "lng": 36.27,
      "items": [
        {
          "id": 9001,
          "product_name": "Product 1",
          "quantity": 2,
          "price": 5000,
          "discount": 0,
          "status": "preparing",
          "variant_attributes": [
            {
              "name": "Size",
              "value": "Large"
            }
          ]
        }
      ]
    }
  }
}
```

**ملاحظة**: حسب الكود الحالي، هذا الـ endpoint لا يتحقق من أن الطلب تابع لنفس الدرايفر.

---

## 3) جلب الطلبات الفورية المتاحة للتعيين (Instant)

**GET** `/api/driver/orders/to-assigned`

**Query Params**

- `status` (optional): `pending | preparing`

**ملاحظة**: قيمة `status` لا تُستخدم حالياً داخل الاستعلام (حسب الكود الحالي).

**Response** نفس شكل قائمة الطلبات في endpoint رقم 1.

---

## 4) قبول طلب فوري

**POST** `/api/driver/orders/accept/{orderId}`

**Behavior**

- يقبل فقط الطلبات الفورية (`is_instant_delivery = true`)
- الحالة يجب أن تكون `pending` أو `preparing`
- إذا تم تعيين الطلب لدرايفر آخر سترجع رسالة خطأ

**Response**

يرجع تفاصيل الطلب بنفس شكل endpoint رقم 2، مع رسالة:

`Order accepted successfully`

---

## 5) تحويل عنصر واحد إلى Out Delivery

**POST** `/api/driver/orders/item-out-delivery/{itemId}`

**Behavior**

- العنصر يجب أن يكون `item_status = preparing`
- الطلب يجب أن يكون تابعاً لهذا الدرايفر

**Response**

يرجع رسالة:

`Item marked as out for delivery`

---

## 6) تحويل الطلب كامل إلى Out Delivery

**POST** `/api/driver/orders/order-out-delivery/{orderId}`

**Behavior**

- الحالة يجب أن تكون `pending` أو `preparing`
- الطلب يجب أن يكون تابعاً لهذا الدرايفر
- يتم تحديث حالة الطلب وكل عناصره إلى `out_delivery`

**Response**

يرجع رسالة:

`Order marked as out for delivery`

---

## 7) تسليم الطلب (Deliver)

**POST** `/api/driver/orders/deliver/{orderId}`

**Behavior**

- الطلب يجب أن يكون `out_delivery`
- الطلب يجب أن يكون تابعاً لهذا الدرايفر
- يتم تحديث حالة الطلب وكل عناصره إلى `delivered`

**Response**

يرجع رسالة:

`Order delivered successfully`

---

## 8) تحديث موقع الدرايفر للطلب الحالي

**POST** `/api/driver/orders/update-location`

**Body (JSON)**

- `order_id` (required, exists)
- `lat` (required, numeric)
- `lng` (required, numeric)

**Behavior**

- الطلب يجب أن يكون تابعاً لهذا الدرايفر
- يجب أن تكون حالة الطلب `out_delivery`

**Response**

يرجع Response عادي بنجاح بدون بيانات مهمة:

```json
{
  "status": true,
  "message": "Success",
  "data": []
}
```

---

## 9) إحصائيات الدرايفر

**GET** `/api/driver/orders/statistics`

**Response (data: object)**

```json
{
  "delivered_orders": 12,
  "rate_percent": 10,
  "total_earnings": 34000
}
```

---

## 10) الطلب الحالي (قيد التوصيل)

**GET** `/api/driver/orders/current`

**Behavior**

- يرجع آخر طلب للدرايفر بحالة `out_delivery`
- إذا لا يوجد يرجع `data: null` مع رسالة `No current order`

**Response**

نفس شكل تفاصيل الطلب في endpoint رقم 2.

