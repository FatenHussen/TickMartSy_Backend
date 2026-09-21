# Flutter — متى تظهر لوحة المسوق؟

> **أرسلوا هذا الملف لفريق Flutter فقط.**  
> **آخر تحديث:** 21 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en` + User Bearer token  
> **الباك جاهز — لا تغيير مطلوب على الباك**  
> ويب: [`WEB_MARKETER_DASHBOARD_GATE.md`](./WEB_MARKETER_DASHBOARD_GATE.md)  
> مرجع مسوّق (API): [`../api/user-affiliate-api-changes.md`](../api/user-affiliate-api-changes.md)

**المشكلة:** اليوزر العادي (غير مترقّى) عم يشوف **لوحة المسوق** (رصيد، طلبات، إنشاء طلب، أرباح) مع بقاء زر «كن مسوقاً».

**المطلوب:** لوحة المسوق تظهر **فقط** بعد ترقية الحساب وموافقة الأدمن. قبلها: زر/شاشة «كن مسوقاً» فقط.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [من وين تقرأوا الحالة](#2-من-وين-تقرأوا-الحالة)
3. [ماذا تعرضوا](#3-ماذا-تعرضوا)
4. [APIs المسوّق](#4-apis-المسوّق)
5. [غلط vs صح](#5-غلط-vs-صح)
6. [Checklist](#6-checklist)

---

## 1) القاعدة

المسوّق المعتمد = الشرطان **معاً**:

```dart
bool isApprovedMarketer(User user) {
  final a = user.affiliate;
  return a != null && a.isAffiliate == true && a.approved == true;
}
```

| الحالة | الواجهة |
|--------|---------|
| غير مسجّل دخول | زر/رابط «كن مسوقاً» → تسجيل/دخول ثم طلب ترقية |
| مسجّل و **مو** مسوّق معتمد | **لا** لوحة مسوق — فقط «كن مسوقاً» / شاشة طلب الترقية |
| مسوّق معتمد | **لوحة المسوق** (رصيد، طلبات، أرباح، …) — **بدون** زر «كن مسوقاً» |

> عمود **ترقية = لا** في الأدمن = الحساب عادي → لازم يبقى على مسار «كن مسوقاً»، مو اللوحة.

---

## 2) من وين تقرأوا الحالة

بعد login / verify / profile، الحقول داخل `data.user.affiliate`:

```json
{
  "user": {
    "id": 12,
    "name": "nebal",
    "affiliate": {
      "is_affiliate": false,
      "approved": false,
      "affiliate_id": null,
      "coupon_id": null,
      "rate": null
    }
  },
  "token": "1|xxxxxxxx"
}
```

| JSON | مقترح في الموديل |
|------|------------------|
| `affiliate.is_affiliate` | `isAffiliate` |
| `affiliate.approved` | `approved` |
| `affiliate.affiliate_id` | `affiliateId` (فقط إذا `approved == true`) |

**لا تعتمدوا** على:

- عنوان التبويب أو الـ route («المسوق والدعم»)
- إحصائيات افتراضية `0 / 0`
- كاش قديم بدون تحديث البروفايل

اعتمدوا **فقط** على `isAffiliate` + `approved`.

حدّثوا الحالة من:

- استجابة login / verify-otp
- `GET /api/user/auth/profile` عند فتح شاشة المسوّق

---

## 3) ماذا تعرضوا

### أ) مستخدم عادي — غير معتمد

اعرضوا واحداً من:

- زر **كن مسوقاً**
- شاشة/bottom sheet طلب الترقية → `POST /api/user/auth/markter-request` (مع التوكن)

**ممنوع** عرض:

- عنوان «لوحة المسوق»
- الرصيد المتاح / إجمالي الطلبات
- «إنشاء طلب جديد» / «شوف الأرباح»
- أي استدعاء لـ `/api/user/markter/*`

### ب) مسوّق معتمد

اعرضوا لوحة المسوق واستدعوا:

| الهدف | Method | Path |
|-------|--------|------|
| إحصائيات (رصيد، طلبات، …) | `GET` | `/api/user/markter/statistics` |
| الملف | `GET` | `/api/user/markter/profile` |
| الطلبات | `GET` | `/api/user/markter/orders` |
| الحركات | `GET` | `/api/user/markter/transactions` |
| … | | باقي مسارات `/api/user/markter/*` |

اخفوا زر **كن مسوقاً**.

---

## 4) APIs المسوّق

كل مسارات `/api/user/markter/*` تتطلب:

- `Authorization: Bearer {token}`
- حساب بـ `is_affiliate = true` و `affiliate_approved = true` و `affiliate_id` موجود

إذا اليوزر عادي → الباك يرجع **403**.

لا تستدعوا هالـ endpoints ثم تخفوا الخطأ؛ **امنعوا الدخول للوحة** من التطبيق أصلاً بشرط القسم 1.

---

## 5) غلط vs صح

**غلط**

```dart
// أي فتح لشاشة المسوّق يعرض اللوحة
class MarketerScreen extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text('لوحة المسوق'),
        Text('الرصيد المتاح: 0'),
        ElevatedButton(onPressed: () {}, child: Text('كن مسوقاً')),
      ],
    );
  }
}
```

**صح**

```dart
class MarketerScreen extends StatelessWidget {
  final User user;
  const MarketerScreen({required this.user});

  @override
  Widget build(BuildContext context) {
    if (!isApprovedMarketer(user)) {
      return const BecomeMarketerView(); // زر / فورم طلب ترقية فقط
    }
    return const MarketerDashboardView(); // رصيد، طلبات، أرباح…
  }
}
```

---

## 6) Checklist

- [ ] لوحة المسوق تظهر فقط إذا `isAffiliate == true` **و** `approved == true`
- [ ] اليوزر العادي يرى «كن مسوقاً» فقط — بلا رصيد/طلبات/أرباح
- [ ] المسوّق المعتمد لا يرى زر «كن مسوقاً»
- [ ] لا استدعاء لـ `/api/user/markter/*` قبل تحقق الشرط
- [ ] عند 403 من markter APIs: اعتبروا الحساب غير معتمد وأرجعوه لمسار «كن مسوقاً»
- [ ] الحالة تُحدَّث من login/profile — مو ثابتة في الواجهة

**الباك جاهز — التطبيق يتبع هذا الملف.**
