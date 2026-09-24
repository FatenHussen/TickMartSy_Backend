# Flutter — عرض حالة الطلب (`status`)

> **أرسلوا هذا الملف لفريق Flutter فقط.**  
> **آخر تحديث:** 25 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> داشبورد: [`DASHBOARD_ORDER_STATUS.md`](./DASHBOARD_ORDER_STATUS.md)  
> ويب: [`WEB_ORDER_STATUS.md`](./WEB_ORDER_STATUS.md)

**المشكلة:** بعد تحويل الطلب لـ «خرج للتوصيل»، التطبيق كان يظهر `pending` / قيد الانتظار.  
**السبب:** المفتاح الصحيح `out_delivery` — إذا الـ enum/map ما فيو، الـ default يطلع `pending`.

---

## الفهرس

1. [القيم](#1-القيم)
2. [مصدر البيانات](#2-مصدر-البيانات)
3. [الموديل والعرض](#3-الموديل-والعرض)
4. [غلط vs صح](#4-غلط-vs-صح)
5. [Checklist](#5-checklist)

---

## 1) القيم

| `status` | عربي | إنجليزي |
|----------|------|---------|
| `pending` | قيد الانتظار | Pending |
| `waiting_approval` | بانتظار موافقة الزبون | Waiting approval |
| `preparing` | قيد التحضير | Preparing |
| `out_delivery` | خرج للتوصيل | Out for delivery |
| `delivered` | تم التوصيل | Delivered |
| `cancelled` | ملغي | Cancelled |
| `cancelled_by_admin` | ملغي من الإدارة | Cancelled by admin |
| `rejected_by_delivery` | مرفوض من الدليفري | Rejected by delivery |
| `faild_deliver` | فشل التوصيل | Failed delivery |
| `returned_by_user` | مرتجع من المستخدم | Returned by user |

**مو موجود:** `out_for_delivery`.

---

## 2) مصدر البيانات

```http
GET /api/user/orders
GET /api/user/orders/{id}
GET /api/user/orders/active
```

```json
{
  "status": true,
  "data": {
    "id": 12,
    "status": "out_delivery",
    "status_label": "خرج للتوصيل",
    "timestamps": {
      "pending_at": "...",
      "preparing_at": "...",
      "out_delivery_at": "...",
      "delivered_at": null
    },
    "items": [
      { "id": 1, "status": "out_delivery" }
    ]
  }
}
```

| حقل | استخدام |
|-----|---------|
| الجذر `status` | نجاح الـ API (`bool`) — **مو** حالة الطلب |
| `data.status` | مفتاح للـ enum / stepper |
| `data.status_label` | نص جاهز للـ Text — مفضّل |
| `items[].status` | حالة العنصر (نفس المفاتيح) |

---

## 3) الموديل والعرض

```dart
class Order {
  final String status;
  final String? statusLabel;
  // ...
}

String displayStatus(Order order) {
  return order.statusLabel
      ?? statusLabels[order.status]
      ?? order.status; // لا ترجعوا 'pending' كافتراضي
}

const statusLabels = {
  'pending': 'قيد الانتظار',
  'waiting_approval': 'بانتظار موافقة الزبون',
  'preparing': 'قيد التحضير',
  'out_delivery': 'خرج للتوصيل', // ← ضروري
  'delivered': 'تم التوصيل',
  'cancelled': 'ملغي',
  'cancelled_by_admin': 'ملغي من الإدارة',
  'rejected_by_delivery': 'مرفوض من الدليفري',
  'faild_deliver': 'فشل التوصيل',
  'returned_by_user': 'مرتجع من المستخدم',
};
```

إذا عندكم `enum OrderStatus`:

```dart
enum OrderStatus {
  pending,
  waitingApproval,
  preparing,
  outDelivery, // json: out_delivery
  delivered,
  cancelled,
  cancelledByAdmin,
  rejectedByDelivery,
  faildDeliver,
  returnedByUser,
  unknown, // ← لأي قيمة جديدة بدل pending
}
```

Step tracker:

`pending` → `preparing` → `out_delivery` → `delivered`

`GET /orders/active` لنفس الحقول في كرت التتبع.

---

## 4) غلط vs صح

| غلط | صح |
|-----|-----|
| `outForDelivery` / `out_for_delivery` في الـ JSON | `out_delivery` |
| `default: OrderStatus.pending` عند parse فاشل | `unknown` أو النص الخام |
| `response['status']` كحالة طلب | `response['data']['status']` |
| تجاهل `status_label` | اعرضوه مباشرة إن وُجد |

---

## 5) Checklist

- [ ] الـ model/enum فيه `out_delivery`
- [ ] ما في default يحوّل قيمة غير معروفة لـ `pending`
- [ ] الشاشات تعرض `status_label` أو map كامل
- [ ] بعد تحديث الأدمن، الريفريش يظهر «خرج للتوصيل»
