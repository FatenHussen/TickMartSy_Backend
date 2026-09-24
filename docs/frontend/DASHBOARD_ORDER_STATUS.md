# الداشبورد — حالة الطلب (`status`)

> **أرسلوا هذا الملف لفريق الداشبورد فقط.**  
> **آخر تحديث:** 25 أيلول 2026  
> Base: `/api/admin` + Admin token  
> **الباك جاهز بعد `git pull`**  
> ويب: [`WEB_ORDER_STATUS.md`](./WEB_ORDER_STATUS.md)  
> Flutter: [`FLUTTER_ORDER_STATUS.md`](./FLUTTER_ORDER_STATUS.md)

**المشكلة:** تغيير الحالة من «قيد التحضير» إلى «خرج للتسليم» كان يظهر `pending` أو يرجع.  
**السبب الشائع:** إرسال `out_for_delivery` بدل `out_delivery`، أو عرض fallback على `pending` لما المفتاح غير معروف.

---

## الفهرس

1. [القيم الصحيحة](#1-القيم-الصحيحة)
2. [تغيير الحالة](#2-تغيير-الحالة)
3. [الانتقالات المسموحة](#3-الانتقالات-المسموحة)
4. [الرد](#4-الرد)
5. [غلط vs صح](#5-غلط-vs-صح)
6. [Checklist](#6-checklist)

---

## 1) القيم الصحيحة

| `status` (API) | العرض (عربي) | ملاحظات |
|----------------|--------------|---------|
| `pending` | قيد الانتظار | |
| `waiting_approval` | بانتظار موافقة الزبون | طلب سريع بعد التحويل |
| `preparing` | قيد التحضير | |
| `out_delivery` | خرج للتوصيل | **مو** `out_for_delivery` |
| `delivered` | تم التوصيل | |
| `cancelled` | ملغي | |
| `cancelled_by_admin` | ملغي من الإدارة | يحتاج `rejection_reason` |
| `rejected_by_delivery` | مرفوض من الدليفري | |
| `faild_deliver` | فشل التوصيل | كتابة الباك كما هي |
| `returned_by_user` | مرتجع من قبل المستخدم | |

في الـ Select / dropdown: **`value` = المفتاح الإنجليزي أعلاه** · الـ label عربي للعرض فقط.

---

## 2) تغيير الحالة

```http
PATCH /api/admin/orders/{orderId}/change-status
Content-Type: application/json
Authorization: Bearer {admin_token}
```

```json
{
  "status": "out_delivery"
}
```

إلغاء من الإدارة:

```json
{
  "status": "cancelled_by_admin",
  "rejection_reason": "سبب الرفض"
}
```

تغيير حالة عنصر واحد:

```http
PATCH /api/admin/orders/items/{itemId}/change-status
```

```json
{ "status": "out_delivery" }
```

قيم العنصر المسموحة: `pending` · `preparing` · `out_delivery` · `delivered`

---

## 3) الانتقالات المسموحة

| من | إلى |
|----|-----|
| `pending` | `preparing` · `cancelled` · `cancelled_by_admin` |
| `waiting_approval` | `preparing` · `cancelled` · `cancelled_by_admin` |
| `preparing` | `out_delivery` · `cancelled` · `cancelled_by_admin` |
| `out_delivery` | `delivered` · `cancelled` · `cancelled_by_admin` |
| `delivered` | ❌ لا تغيير |

`preparing` → `pending` **مرفوض** من الباك.

---

## 4) الرد

```json
{
  "status": true,
  "message": "تم تحديث حالة الطلب بنجاح",
  "data": {
    "id": 12,
    "order_code": "ORD-...",
    "status": "out_delivery",
    "status_label": "خرج للتوصيل",
    "...": "..."
  }
}
```

| حقل | معنى |
|-----|------|
| الجذر `status` | نجاح الطلب (`true`/`false`) — **مو** حالة الطلب |
| `data.status` | حالة الطلب (`out_delivery` …) |
| `data.status_label` | نص عربي جاهز للعرض |

بعد التحديث: خذوا الحالة من **`data.status`** أو **`data.status_label`** فقط.

الباك يقبل أيضاً `out_for_delivery` ويحوّله لـ `out_delivery` — لكن **فضّلوا المفتاح الصحيح** في الكود.

---

## 5) غلط vs صح

| غلط | صح |
|-----|-----|
| `value: "out_for_delivery"` | `value: "out_delivery"` |
| `value: "خرج للتسليم"` | `value: "out_delivery"` + label عربي |
| قراءة `response.status` بعد التغيير | قراءة `response.data.status` |
| fallback غير معروف → `pending` | fallback → `data.status` كما هو أو `status_label` |
| من `preparing` ترجعوا لـ `pending` في الـ UI | ابقوا على الحالة السابقة عند خطأ 400/422 |

---

## 6) Checklist

- [ ] Select الحالات يستخدم `out_delivery` (مو `out_for_delivery`)
- [ ] بعد `change-status` تقرأون `data.status` / `data.status_label`
- [ ] ما في mapping يحوّل حالة غير معروفة لـ `pending`
- [ ] عند 400/422 ترجع الواجهة للحالة **السابقة** (مو `pending` افتراضي)
- [ ] `cancelled_by_admin` يرسل `rejection_reason`
