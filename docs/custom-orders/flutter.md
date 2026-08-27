# Custom Order Requests — Flutter (User App)

> **آخر تحديث | Last Updated:** 2026-08-26

طلب سريع / اكتب طلبك بنفسك: الزبون يرسل نص + صور اختيارية، الأدمن يسعّر ويحوّل لطلب نظامي، ثم الزبون يوافق أو يلغي.

**Base:** `/api/user`  
**Auth:** `Bearer` token (`auth:user`)  
**Content-Type لإنشاء الطلب مع صور:** `multipart/form-data`

---

## 1) الحالات (على الطلب المخصص)

| `status` | المعنى | أزرار UI |
|----------|--------|----------|
| `pending_pricing` | بانتظار تسعير الأدمن | إلغاء فقط |
| `waiting_approval` | تم التحويل لطلب نظامي | **موافقة** / **إلغاء** |
| `approved` | وافق الزبون → الطلب دخل `preparing` | عرض الطلب |
| `cancelled` | ألغاه الزبون | — |
| `cancelled_by_admin` | ألغته الإدارة | عرض السبب |

`actions.can_approve` / `actions.can_cancel` تأتي من الـ API في `show`.

---

## 2) إنشاء طلب سريع

```http
POST /api/user/custom-order-requests
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

| حقل | مطلوب | ملاحظات |
|-----|--------|---------|
| `description` | نعم | نص ≥ **10** أحرف، أقصى 5000 |
| `address_id` | نعم | من عناوين المستخدم |
| `payment_method_id` | لا | نية دفع (نقد / إلكتروني) — **لا خصم** قبل الموافقة |
| `expected_at` | لا | ISO datetime، ≥ الآن |
| `images[]` | لا | حتى 5 صور: jpg/jpeg/png/webp، كل صورة ≤ 5MB |

```dart
final form = FormData.fromMap({
  'description': text,
  'address_id': addressId,
  if (paymentMethodId != null) 'payment_method_id': paymentMethodId,
  if (expectedAt != null) 'expected_at': expectedAt.toIso8601String(),
  if (images != null)
    'images[]': images.map((f) => MultipartFile.fromFileSync(f.path)).toList(),
});
await dio.post('/api/user/custom-order-requests', data: form);
```

**بعد الإرسال:** الحالة `pending_pricing`. لا يوجد تسعير تلقائي.

---

## 3) قائمة / تفاصيل

```http
GET /api/user/custom-order-requests?status=pending_pricing&page=1
GET /api/user/custom-order-requests/{id}
```

`OneResource` يتضمن:

- `description`, `images` (URLs كاملة), `expected_at`
- `address`, `payment_method`
- `order` عند التحويل (بنود + أسعار + `has_external_items` + تفاوت السعر)
- `actions.can_approve` / `actions.can_cancel`
- `rejection_reason` إن وُجد

---

## 4) موافقة / إلغاء

```http
POST /api/user/custom-order-requests/{id}/approve
POST /api/user/custom-order-requests/{id}/cancel
```

- **Approve** فقط إذا `waiting_approval` → الطلب النظامي يصبح `preparing`.
- **Cancel** مسموح في `pending_pricing` أو `waiting_approval`.
- يمكن أيضاً إلغاء الطلب النظامي عبر `POST /api/user/orders/{orderId}/cancel` إذا حالته `waiting_approval`.

---

## 5) شاشة التأكيد (مهم للـ UX)

عند `waiting_approval` اعرض من `order`:

1. قائمة البنود (`items`) — اسم، كمية، سعر.
2. بنود `is_external: true` بلون/شارة «مصدر خارجي» + `invoice_image` إن وُجدت.
3. المجموع `total` (+ توصيل).
4. إن `has_external_items == true`: تنبيه ثابت:

> الأسعار النهائية للبنود الخارجية قد تزيد أو تنقص بحدود  
> `±{price_variance_value}%` أو `±{price_variance_value}` (حسب `price_variance_type`: `percent` | `fixed`)  
> بناءً على التوفر في السوق.

5. زرّان: موافقة / إلغاء.

**لا تعرض «مجموع تقريبي فقط»** — اعرض تفاصيل البنود دائماً.

---

## 6) الإشعارات (FCM / inbox)

| حدث | `data.type` | مفاتيح إضافية |
|-----|-------------|---------------|
| تم التسعير | `custom_order_request` | `custom_order_request_id`, `order_id`, `status=waiting_approval` |
| إلغاء أدمن | `custom_order_request` | `custom_order_request_id`, `status=cancelled_by_admin` |
| بعد الموافقة (تحضير) | `order` | `order_id`, `status=preparing` |

عند فتح إشعار التسعير → شاشة تفاصيل الطلب المخصص + أزرار الموافقة.

---

## 7) UI قسم الطلب السريع (صفحات مختارة + الهيدر)

الإعدادات تأتي من:

```http
GET /api/user/settings
Accept-Language: ar
```

داخل `data.quick_order`:

| حقل | نوع | استخدام |
|-----|-----|---------|
| `is_enabled` | bool | إن `false`: أخفِ زر الهيدر **وقسم** الطلب السريع بالكامل |
| `page_ids` | int[] | IDs الصفحات التي يظهر عليها القسم |
| `page_slugs` | string[] | نفس الصفحات كـ slug (موصى للمطابقة مع الصفحة الحالية) |
| `background_image` | string\|null | صورة خلفية القسم (URL كامل) — إن وُجدت غطِّ القسم بها |
| `background_color` | string | لون/تدرج احتياطي تحت الصورة أو بدلها (مثلاً `#FFE8D6`) |
| `card_background_color` | string | خلفية كروت الخطوات |
| `card_variant` | string | شكل الكارد: `horizontal` \| `vertical` \| `square` |
| `badge` | string | شارة أعلى القسم (مثلاً «طلب عاجل») |
| `title` | string | العنوان الكبير |
| `subtitle` | string | النص التوضيحي |
| `cta` | string | نص زر «اطلب الآن» |
| `steps[]` | array | `{number, icon, title, description}` — عادة 3 خطوات |
| `action.page_slug` | string | `custom_order_request` — افتح شاشة إنشاء الطلب |

### سلوك الإظهار / الإخفاء

1. اقرأ `quick_order` من الإعدادات (أو من الكاش).
2. إن `is_enabled == false` → لا تعرض زر «طلب سريع / Urgent» في الهيدر ولا القسم بأي صفحة.
3. إن `is_enabled == true` → اعرض زر الهيدر (عام).
4. اعرض **قسم** الطلب السريع فقط إذا `page_slugs` تحتوي slug الصفحة الحالية (أو `page_ids` تحتوي id الصفحة).
5. الافتراضي من الباك = صفحة `home` فقط.

```dart
final showHeader = qo.isEnabled;
final showSection =
    qo.isEnabled && qo.pageSlugs.contains(currentPageSlug);
```

### خلفية القسم

```dart
Decoration decoration;
if (qo.backgroundImage != null) {
  decoration = BoxDecoration(
    image: DecorationImage(
      image: NetworkImage(qo.backgroundImage!),
      fit: BoxFit.cover,
    ),
  );
} else {
  decoration = BoxDecoration(color: Color(hex(qo.backgroundColor)));
}
```

### شكل الكارد (`card_variant`)

| قيمة | تخطيط الكارد |
|------|----------------|
| `horizontal` | أيقونة يسار + نص يمين (افتراضي — كما بالتصميم) |
| `vertical` | أيقونة فوق + نص تحت |
| `square` | كارد مربّع متناسق (مناسب لشبكة) |

طبّق `card_background_color` على خلفية كل كارد خطوة.

### ريسبونسيف (مهم)

| عرض | تخطيط الخطوات + CTA |
|-----|----------------------|
| ≥ 768 (تابلت/ويب) | صف أفقي: نص \| 3 كروت \| زر CTA |
| < 768 (موبايل) | عمود: عنوان → كروت عمودياً أو سكرول أفقي → CTA بعرض كامل أسفل |

- لا تثبت عرض الكروت ببكسل ضيق؛ استخدم `Expanded` / نسب مرنة.
- حافظ على padding أفقي ≥ 16 وعلى `borderRadius` واضح للكروت (≈ 16).
- زر CTA: على الموبايل `width: double.infinity`؛ على الديسكتوب ثابت بعرض مناسب.

### الأيقونات

`steps[].icon` قيم رمزية من السيرفر: `edit` | `price` | `delivery` — اربطها بأيقونات التطبيق (أو Material/SVG محلي). إن لم تُعرف القيمة استخدم أيقونة افتراضية.

---

## 8) أخطاء شائعة

| رسالة / مفتاح | السبب |
|---------------|--------|
| validation `min:10` على description | نص قصير |
| `address_not_found` | عنوان ليس للمستخدم |
| `cannot_approve` / `cannot_cancel` | حالة غير مناسبة |
| `already_priced` (أدمن) | محاولة تحويل مرتين |

---

**آخر تحديث | Last Updated:** 2026-08-26
