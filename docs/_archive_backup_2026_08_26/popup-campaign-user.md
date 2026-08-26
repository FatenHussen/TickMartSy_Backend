# Popup Campaign — واجهة المستخدم (User API)

اختيار الحملة: `app/Services/PopupCampaignSelector.php` — الكنترولر: `app/Http/Controllers/User/PopupCampaignController.php`.

**Base URL (افتراضي Laravel `/api`):** المسارات أدناه بدون بادئة `user` (معرفة في `routes/api.php`).

---

## المصادقة

- `GET /api/popups/active` ومسارات التتبع **عامة** (لا تتطلب `auth:user` في المسارات الحالية).

---

## 1) جلب البوب‑أب النشط

### `GET /api/popups/active`

يعيد **أول حملة** مطابقة لـ: الحالة النشطة، الجمهور، **صفحات مربوطة من جدول `pages`** (عبر pivot)، **ربط اختياري بمنتجات/متاجر/وصفات/سلال**، والأولوية.

### Query parameters (اختياري)

| المعامل | الوصف |
|---------|--------|
| `page_type` | نوع الصفحة المنطقي، مثل `home`, `products`, `product_details`. الافتراضي: `home`. يُقارَن مع `slug` الصفحات المربوطة بالحملة (بعد تطبيع: أحرف صغيرة، مسافات → `_`). |
| `current_url` | مسار أو URL للسياق؛ إن لم يُرسل يُستخدم طلب HTTP الحالي. |
| `product_id` | رقم منتج؛ يُستخدم عندما تكون الحملة مربوطة بمنتجات (pivot). |
| `shop_id` | رقم متجر (`shops`). |
| `recipe_id` | رقم وصفة. |
| `basket_id` | رقم سلة. |

**سلوك الربط بالكيانات:** إذا وُجدت روابط في `popup_campaign_attachables`، يجب أن يطابق الطلب أحد المعرفات أعلاه حتى تُعتبر الحملة مطابقة. إذا لم يوجد أي ربط، تُطبَّق باقي القواعد فقط (صفحات + جمهور).

### استجابة ناجحة — يوجد بوب‑أب

`200 OK` — جسم الاستجابة من `PopupCampaignResource` (مثال تقريبي):

```json
{
  "data": {
    "id": 1,
    "title": { "ar": "…", "en": "…" },
    "slug": "spring-offer-popup",
    "type": "modal",
    "status": "active",
    "priority": 90,
    "content": {
      "headline": { "ar": "…", "en": "…" },
      "subheadline": { "ar": "…", "en": "…" },
      "description": { "ar": "…", "en": "…" }
    },
    "buttons": {
      "primary": "Shop Now",
      "secondary": null,
      "url": "https://example.com/sale"
    },
    "media": { "type": "image", "path": "https://…" },
    "form": { "enabled": false, "fields": null },
    "display": {
      "pages": ["home", "products"],
      "audience_type": "all_visitors"
    },
    "trigger": { "type": "delay", "value": 5 },
    "frequency": {
      "show_every": 0,
      "max_impressions": 1
    },
    "scoped_to_entities": false,
    "products": [],
    "shops": [],
    "recipes": [],
    "baskets": []
  }
}
```

- **`display.pages`:** مصفوفة **`slug`** من جدول `pages` (لا يوجد عمود JSON على الحملة).
- **`frequency`:** القيم تُضبط من السيرفر (سياسة التكرار الافتراضية).
- **`scoped_to_entities`:** `true` إذا وُجدت روابط في `popup_campaign_attachables`.
- **`products` / `shops` / `recipes` / `baskets`:** مصفوفات بصيغة `AllResource` لكل نموذج عند وجود ربط.

### استجابة — لا يوجد بوب‑أب

`200 OK`

```json
{ "data": null }
```

> ملاحظة: إذا كان المشروع يلفّ الاستجابة بـ `sendResponse`، قد يظهر الهيكل `status`, `message`, `data` بدل الجذر `data` فقط — راجع `App\Http\Controllers\Controller`.

---

## 2) تتبع عرض البوب‑أب

### `POST /api/popups/{popupCampaign}/track-view`

- **`popupCampaign`:** معرف الحملة (route model binding).
- **Body (اختياري):** `page_type`, `current_url`, `referrer` — تُحفظ في `payload` للحدث.

**استجابة:** `204 No Content`

---

## 3) تتبع ضغطة البوب‑أب

### `POST /api/popups/{popupCampaign}/track-click`

نفس فكرة `track-view` مع نوع حدث `click`.

**استجابة:** `204 No Content`

---

## أحداث قاعدة البيانات

- `view` — عند `track-view`
- `click` — عند `track-click`

---

## الواجهة الأمامية (تكرار الظهور)

يُنصح بتطبيق حد التكرار في المتصفح باستخدام `frequency.max_impressions` و`frequency.show_every` مع مفاتيح `localStorage` حسب `slug` (انظر `resources/views/components/popup-campaign.blade.php` إن وُجد).
