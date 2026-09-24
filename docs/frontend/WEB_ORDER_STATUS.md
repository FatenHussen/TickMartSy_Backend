# الويب — عرض حالة الطلب (`status`)

> **أرسلوا هذا الملف لفريق الويب فقط.**  
> **آخر تحديث:** 25 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> داشبورد: [`DASHBOARD_ORDER_STATUS.md`](./DASHBOARD_ORDER_STATUS.md)  
> Flutter: [`FLUTTER_ORDER_STATUS.md`](./FLUTTER_ORDER_STATUS.md)

**المشكلة:** بعد ما الأدمن يغيّر الطلب لـ «خرج للتوصيل»، الموقع كان يظهر `pending` / قيد الانتظار.  
**السبب:** المفتاح الصحيح `out_delivery` — إذا الـ map ما فيو هالمفتاح، الـ fallback يطلع `pending`.

---

## الفهرس

1. [القيم](#1-القيم)
2. [مصدر البيانات](#2-مصدر-البيانات)
3. [العرض](#3-العرض)
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

**مو موجود:** `out_for_delivery` — لا تستخدموه في الـ map.

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
    }
  }
}
```

| حقل | استخدام |
|-----|---------|
| الجذر `status` | نجاح الـ API فقط (`true`) |
| `data.status` | مفتاح الحالة للـ stepper / الألوان / الفلاتر |
| `data.status_label` | نص جاهز للعرض (مفضّل) |

عناصر الطلب: `items[].status` = `item_status` بنفس المفاتيح.

---

## 3) العرض

**مفضّل:**

```ts
const label = order.status_label ?? statusMap[order.status] ?? order.status;
```

**خريطة كاملة (إذا ما استخدمتوا `status_label`):**

```ts
const statusMap: Record<string, string> = {
  pending: "قيد الانتظار",
  waiting_approval: "بانتظار موافقة الزبون",
  preparing: "قيد التحضير",
  out_delivery: "خرج للتوصيل", // ← ضروري
  delivered: "تم التوصيل",
  cancelled: "ملغي",
  cancelled_by_admin: "ملغي من الإدارة",
  rejected_by_delivery: "مرفوض من الدليفري",
  faild_deliver: "فشل التوصيل",
  returned_by_user: "مرتجع من المستخدم",
};
```

تتبع الطلب / stepper النموذجي:

`pending` → `preparing` → `out_delivery` → `delivered`

كرت التتبع على الهوم يقرأ من `GET /api/user/orders/active` — نفس الحقول.

---

## 4) غلط vs صح

| غلط | صح |
|-----|-----|
| `out_for_delivery` في الـ map | `out_delivery` |
| `?? "pending"` لأي مفتاح ناقص | `?? order.status` أو `status_label` |
| قراءة `response.status` كحالة طلب | `response.data.status` |
| إخفاء الخطوة «خرج للتوصيل» | خطوة مستقلة بعد التحضير |

---

## 5) Checklist

- [ ] الـ map فيه `out_delivery` → «خرج للتوصيل»
- [ ] ما في fallback يحوّل أي شيء غير معروف لـ `pending`
- [ ] كرت التتبع / صفحة الطلب تستخدم `data.status` أو `status_label`
- [ ] بعد تحديث الأدمن للحالة، الريفريش يظهر «خرج للتوصيل» مو «قيد الانتظار»
