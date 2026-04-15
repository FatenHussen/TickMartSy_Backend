## PopupCampaign Admin API

### Base URL
- جميع endpoints هنا تبدأ بـ: `/api/admin/popup-campaigns`
- تتطلب مصادقة Admin: `auth:admin`

### Endpoints

#### 1) List popup campaigns
- `GET /api/admin/popup-campaigns`

**Query Params (اختيارية):**
- `search` للبحث داخل: `title`, `headline`, `description`
- `sort_field` مثل: `priority`, `status`, `created_at`, `updated_at`
- `sort_order` قيمة: `asc` أو `desc`
- `page`, `per_page` للـ pagination

#### 2) Create popup campaign
- `POST /api/admin/popup-campaigns`

#### 3) Show popup campaign
- `GET /api/admin/popup-campaigns/{id}`

#### 4) Update popup campaign
- `PUT /api/admin/popup-campaigns/{id}`

#### 5) Delete popup campaign
- `DELETE /api/admin/popup-campaigns/{id}`

---

### Create / Update Request Body
> حقول الترجمة يجب أن تكون بصيغة `ar/en` كـ object.

```json
{
  "title": {
    "ar": "عرض الربيع",
    "en": "Spring Offer"
  },
  "slug": "spring-offer-popup",
  "type": "modal",
  "status": "active",
  "priority": 90,
  "headline": {
    "ar": "خصم 20%",
    "en": "20% Discount"
  },
  "subheadline": {
    "ar": "لفترة محدودة",
    "en": "Limited Time"
  },
  "description": {
    "ar": "استفد من الخصم اليوم.",
    "en": "Get your discount today."
  },
  "button_text": "Shop Now",
  "button_url": "https://example.com/sale",
  "secondary_button_text": "Learn More",
  "media_type": "image",
  "media_path": "/storage/popups/spring.jpg",
  "form_enabled": false,
  "form_fields": ["name", "email"],
  "show_on_pages": ["home", "category"],
  "audience_type": "all_visitors",
  "trigger_type": "delay",
  "trigger_value": 5,
  "show_every": 60,
  "max_impressions": 3
}
```

### Validation Rules (مختصر)
- `title.en`, `title.ar`: required string
- `headline.en`, `headline.ar`: required string
- `subheadline.*`, `description.*`: nullable
- `slug`: required + unique
- `type`: `modal | slide_in | fullscreen`
- `status`: `draft | active | paused | archived`
- `button_text`: required string
- `button_url`: nullable valid URL
- `media_type`: `image | video | gif`
- `media_path`: required string
- `form_enabled`: boolean
- `form_fields`: nullable array of strings
- `show_on_pages`: nullable array (slugs موجودة في جدول `pages`)
- `audience_type`: `all_visitors | guests_only | logged_in_only | new_visitors | returning_visitors`
- `trigger_type`: `on_load | delay | scroll | exit_intent`
- `trigger_value`: nullable integer >= 0
- `show_every`, `max_impressions`: required integer >= 0

---

### Response Fields (Admin Resource)
- `id`, `title`, `slug`, `type`, `status`, `priority`
- `headline`, `subheadline`, `description`
- `button_text`, `button_url`, `secondary_button_text`
- `media_type`, `media_path`
- `form_enabled`, `form_fields`
- `show_on_pages`, `audience_type`
- `trigger_type`, `trigger_value`
- `show_every`, `max_impressions`
- `created_at`, `updated_at`

### Notes
- تم إلغاء الحقول القديمة: `cta_type` و `cta_value`
- استخدم فقط `button_url` لتحديد وجهة الزر الأساسي
