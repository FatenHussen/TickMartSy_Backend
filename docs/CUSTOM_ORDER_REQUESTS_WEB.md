# Custom Order Requests — Web (User Website)

نفس فلو تطبيق الموبايل للطلب السريع على موقع المستخدم.

**Base:** `/api/user`  
**Auth:** Bearer (`auth:user`)  
**إنشاء مع صور:** `multipart/form-data`

---

## Endpoints

| Method | Path | وصف |
|--------|------|-----|
| `POST` | `/custom-order-requests` | إنشاء طلب (نص ≥ 10 + عنوان + …) |
| `GET` | `/custom-order-requests` | قائمة (فلتر `status`) |
| `GET` | `/custom-order-requests/{id}` | تفاصيل + `order` بعد التسعير |
| `POST` | `/custom-order-requests/{id}/approve` | موافقة الزبون |
| `POST` | `/custom-order-requests/{id}/cancel` | إلغاء الزبون |

---

## إنشاء الطلب (مثال fetch)

```js
const form = new FormData();
form.append('description', description); // min 10 chars
form.append('address_id', String(addressId));
if (paymentMethodId) form.append('payment_method_id', String(paymentMethodId));
if (expectedAt) form.append('expected_at', expectedAt); // ISO
files.forEach((file) => form.append('images[]', file));

await api.post('/user/custom-order-requests', form, {
  headers: { 'Content-Type': 'multipart/form-data' },
});
```

لا يوجد تسعير عند الإرسال. الحالة الابتدائية: `pending_pricing`.

---

## حالات الواجهة

```
pending_pricing  →  waiting_approval  →  approved
        \                 /
         → cancelled / cancelled_by_admin
```

| Status | صفحة التفاصيل |
|--------|----------------|
| `pending_pricing` | نص الطلب + الصور + «قيد مراجعة الإدارة» + زر إلغاء |
| `waiting_approval` | جدول بنود الطلب النظامي + مجموع + تنبيه تفاوت إن وُجد + موافقة/إلغاء |
| `approved` | رابط/ملخص الطلب النظامي (`order_id`) وحالته `preparing` فما فوق |
| `cancelled*` | رسالة نهائية + `rejection_reason` إن وُجد |

استخدم `data.actions.can_approve` و `can_cancel` لإظهار الأزرار.

---

## عرض التسعير على الويب

من `response.data.order`:

- `items[]`: `product_name`, `quantity`, أسعار العملة المزدوجة إن وُجدت، `is_external`, `invoice_image`
- `total`, `delivery_price`, `subtotal`
- `has_external_items`, `price_variance_type`, `price_variance_value`
- `approximate_total` اختياري (نادراً؛ التفاصيل هي المصدر)

تنبيه البنود الخارجية (مثال React):

```jsx
{order.has_external_items && (
  <Alert>
    الأسعار النهائية للبنود الخارجية قد تختلف بحدود{' '}
    {order.price_variance_type === 'percent'
      ? `±${order.price_variance_value}%`
      : `±${order.price_variance_value}`}{' '}
    حسب التوفر في السوق.
  </Alert>
)}
```

---

## الدفع

- `payment_method_id` عند الإنشاء = **نية دفع فقط**.
- لا يُعتبر الطلب مدفوعاً إلكترونياً قبل الموافقة (`is_paid` يبقى `false` حتى `approve`).
- بعد الموافقة: إن كانت الطريقة غير نقدية قد يُعلَّم `is_paid` حسب منطق البوابة الحالي؛ النقد عند الاستلام يبقى حتى التسليم.

---

## إشعارات الويب

إن كان لديكم Web Push / polling للصندوق:

- `type: custom_order_request` + `status: waiting_approval` → افتح `/custom-orders/{id}` مع CTA موافقة.
- بعد الموافقة تتبع إشعارات الطلب العادي `type: order`.

---

## توصيات UX

1. زر واضح في الهيدر: «طلب سريع».
2. التحقق من طول النص ≥ 10 في الواجهة **والاعتماد على السيرفر**.
3. معاينة الصور قبل الرفع (حد 5).
4. بعد الموافقة وجّه المستخدم لصفحة تتبع الطلب النظامي `GET /api/user/orders/{order_id}`.
