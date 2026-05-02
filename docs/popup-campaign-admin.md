# Popup Campaign — لوحة التحكم (Admin API)

**Base URL:** `/api/admin/popup-campaigns`  
**المصادقة:** `auth:admin` + صلاحيات `crud.permission:popupcampaign` (حسب `routes/api/admin.php`).

الكنترولر: `app/Http\Controllers\Admin\PopupCampaignController.php`  
الطلبات: `app\Http\Requests\Admin\PopupCampaignRequest.php`  
السيرفس: `app\Services\Admin\PopupCampaignService.php`

---

## Endpoints (REST)

| الطريقة | المسار | الوصف |
|---------|--------|--------|
| `GET` | `/api/admin/popup-campaigns` | قائمة + ترقيم (`page`, `per_page`)، بحث، ترتيب |
| `POST` | `/api/admin/popup-campaigns` | إنشاء |
| `GET` | `/api/admin/popup-campaigns/{id}` | تفاصيل |
| `PUT`/`PATCH` | `/api/admin/popup-campaigns/{id}` | تحديث |
| `DELETE` | `/api/admin/popup-campaigns/{id}` | حذف |

**Query (للقائمة):** `search`, `sort_field`, `sort_order`, `page`, `per_page` — انظر `BaseCRUDController`.

---

## جسم الطلب — إنشاء / تحديث

- الحقول المترجمة: `title`, `headline`, `subheadline`, `description` كـ objects `ar` / `en`.
- **`media_path`:** عند الإنشاء مطلوب كملف رفع (أو حسب القواعد)؛ عند التحديث يمكن إرسال ملف جديد.

### حقول أساسية

| الحقل | ملاحظات |
|--------|---------|
| `slug` | فريد |
| `type` | `modal`, `slide_in`, `fullscreen` |
| `status` | `draft`, `active`, `paused`, `archived` |
| `priority` | عدد صحيح ≥ 0 |
| `button_text`, `button_url`, `secondary_button_text` | نصوص وروابط |
| `media_type` | `image`, `video`, `gif` |
| `form_enabled`, `form_fields` | نموذج اختياري |
| **`show_on_pages`** | مصفوفة **`slug`** موجودة في جدول **`pages`** — تُحفظ عبر pivot **`page_popup_campaign`** (ليس JSON على `popup_campaigns`). |
| `audience_type` | `all_visitors`, `guests_only`, `logged_in_only`, `new_visitors`, `returning_visitors` |
| `trigger_type` | `on_load`, `delay`, `scroll`, `exit_intent` |
| `trigger_value` | عدد صحيح اختياري (مثلاً ثواني للـ delay أو نسبة للـ scroll) |

### حقول ممنوعة في الطلب (يُضبطها السيرفر)

- **`show_every`**, **`max_impressions`:** `prohibited` — تُعيَّن تلقائياً إلى قيم الموديل الافتراضية عند الحفظ.

### ربط كيانات (اختياري)

| الحقل | الوصف |
|--------|--------|
| `product_ids` | مصفوفة أرقام؛ `exists` على `products` (مع استبعاد المحذوف ناعماً حيث ينطبق). |
| `shop_ids` | `shops` |
| `recipe_ids` | `recipes` |
| `basket_ids` | `baskets` |

- إذا **حُذف** المفتاح من طلب التحديث لا تُعاد مزامنة تلك العلاقة.
- إذا **أُرسلت** مصفوفة فارغة `[]` تُفرَّغ الروابط.

### مثال JSON (مختصر)

```json
{
  "title": { "ar": "…", "en": "…" },
  "slug": "spring-offer-popup",
  "type": "modal",
  "status": "active",
  "priority": 90,
  "headline": { "ar": "…", "en": "…" },
  "subheadline": { "ar": null, "en": null },
  "description": { "ar": "…", "en": "…" },
  "button_text": "Shop Now",
  "button_url": "https://example.com/sale",
  "secondary_button_text": null,
  "media_type": "image",
  "form_enabled": false,
  "form_fields": null,
  "show_on_pages": ["home", "products"],
  "audience_type": "all_visitors",
  "trigger_type": "delay",
  "trigger_value": 5,
  "product_ids": [1, 2],
  "shop_ids": []
}
```

---

## استجابة الموارد (Admin)

- **`PopupCampaignResource`** (عنصر واحد): يتضمن `show_on_pages` كقائمة **`slug`** من العلاقة `pages`، و`product_ids` … عند تحميل العلاقات.
- **`PopupCampaignCollection`** (قائمة مختصرة): نفس فكرة `show_on_pages` كـ slugs.

---

## مراجع الكود

- الموديل: `app\Models\PopupCampaign.php` (علاقات `pages`, `products`, …)
- الهجرة: `database/migrations/2026_05_03_100000_create_page_popup_campaign_table_and_drop_show_on_pages_json.php`
