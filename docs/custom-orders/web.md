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

## قسم الطلب السريع (صفحات مختارة + الهيدر)

نفس مصدر الموبايل:

```http
GET /api/user/settings
Accept-Language: ar
```

### شكل `data.quick_order`

```json
{
  "is_enabled": true,
  "page_ids": [1],
  "page_slugs": ["home"],
  "background_image": "https://.../storage/settings/quick-order-bg.jpg",
  "background_color": "#FFE8D6",
  "card_background_color": "#FFFFFF",
  "card_variant": "horizontal",
  "badge": "طلب عاجل",
  "title": "تحتاجه الآن؟",
  "subtitle": "اكتب ما تريده مثل قائمة السوق...",
  "cta": "اطلب الآن",
  "steps": [
    { "number": 1, "icon": "edit", "title": "اكتبه", "description": "قائمتك، بكلماتك" },
    { "number": 2, "icon": "price", "title": "نسعّره", "description": "أسعار واضحة قبل الدفع" },
    { "number": 3, "icon": "delivery", "title": "نوصّل", "description": "للباب بسرعة" }
  ],
  "action": {
    "page_slug": "custom_order_request",
    "route": "/api/user/custom-order-requests"
  }
}
```

| حقل | استخدام |
|-----|---------|
| `is_enabled` | إن `false`: أخفِ زر الهيدر **وقسم** الطلب السريع بالكامل |
| `page_ids` / `page_slugs` | اعرض القسم فقط على هذه الصفحات (افتراضي: `home`) |
| `background_image` | صورة خلفية القسم (URL) — `background-size: cover` |
| `background_color` | لون احتياطي تحت/بدل الصورة |
| `card_background_color` | خلفية كل كارد خطوة |
| `card_variant` | `horizontal` \| `vertical` \| `square` |
| `badge` / `title` / `subtitle` / `cta` | نصوص الواجهة (حسب `Accept-Language`) |
| `steps[]` | خطوات القسم |
| `action.page_slug` | افتح صفحة/مودال إنشاء الطلب السريع |

### إظهار / إخفاء

```jsx
const { data } = await api.get('/user/settings');
const qo = data.quick_order;
const showSection = qo?.is_enabled && qo.page_slugs?.includes(currentPageSlug);

// زر الهيدر عام عند التفعيل؛ القسم حسب الصفحة:
{qo?.is_enabled && <QuickOrderHeaderButton label={qo.badge} />}
{showSection && <QuickOrderSection config={qo} />}
```

- زر الهيدر ينقل إلى `/custom-orders/new` (أو يفتح Modal الإنشاء).
- لا تُظهر القسم إن `is_enabled === false` أو الصفحة الحالية ليست ضمن `page_slugs`.

### خلفية القسم (CSS)

```jsx
<section
  className="quick-order"
  style={{
    backgroundColor: qo.background_color,
    backgroundImage: qo.background_image
      ? `url(${qo.background_image})`
      : undefined,
    backgroundSize: 'cover',
    backgroundPosition: 'center',
  }}
>
  {/* badge + title + subtitle + steps + CTA */}
</section>
```

### شكل الكارد (`card_variant`)

| قيمة | تخطيط |
|------|--------|
| `horizontal` | أيقونة يسار + نص يمين (افتراضي) |
| `vertical` | أيقونة فوق + نص تحت |
| `square` | كارد مربّع (مناسب لـ CSS grid) |

```jsx
<div
  className={`step-card step-card--${qo.card_variant}`}
  style={{ backgroundColor: qo.card_background_color }}
>
  <span className="step-num">{step.number}</span>
  <StepIcon name={step.icon} />
  <div>
    <strong>{step.title}</strong>
    <p>{step.description}</p>
  </div>
</div>
```

أيقونات `steps[].icon`: `edit` | `price` | `delivery` — اربطها بـ SVG/Icon محلي؛ قيمة غير معروفة → أيقونة افتراضية.

### ريسبونسيف

| Breakpoint | تخطيط |
|------------|--------|
| `≥ 768px` | صف: نص تعريفي \| 3 كروت \| زر CTA |
| `< 768px` | عمود: عنوان → كروت عمودياً (أو سكرول أفقي) → CTA بعرض كامل |

```css
.quick-order__body {
  display: flex;
  align-items: center;
  gap: 1rem;
}
.quick-order__steps {
  display: flex;
  gap: 0.75rem;
  flex: 1;
}
.quick-order__cta {
  white-space: nowrap;
}

@media (max-width: 767px) {
  .quick-order__body {
    flex-direction: column;
    align-items: stretch;
  }
  .quick-order__steps {
    flex-direction: column;
  }
  .quick-order__cta {
    width: 100%;
  }
}
```

- padding أفقي ≥ 16px، `border-radius` للكروت ≈ 16px.
- لا تثبّت عرض الكروت ببكسل ضيق؛ استخدم `flex` / `minmax`.

### إنشاء الطلب من الويب

بعد الضغط على CTA → صفحة أو Modal: textarea (≥ 10) + صور (حتى 5) + عنوان + وقت متوقع + طريقة دفع → `POST /custom-order-requests`.

---

## Checklist ويب

- [ ] جلب `GET /settings` وعرض `quick_order` إن `is_enabled` والصفحة ضمن `page_slugs`
- [ ] زر هيدر عام + قسم حسب الصفحات المختارة
- [ ] خلفية صورة أو لون
- [ ] كروت بـ `card_background_color` + `card_variant`
- [ ] ريسبونسيف موبايل / ديسكتوب
- [ ] فلو الإنشاء / الموافقة / الإلغاء كما أعلاه

---

## توصيات UX إضافية

1. التحقق من طول النص ≥ 10 في الواجهة **والاعتماد على السيرفر**.
2. معاينة الصور قبل الرفع (حد 5، كل صورة ≤ 5MB).
3. بعد الموافقة وجّه المستخدم لصفحة تتبع الطلب النظامي `GET /api/user/orders/{order_id}`.
