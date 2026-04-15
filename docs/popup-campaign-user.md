## PopupCampaign User API

### Endpoints
- `GET /api/popups/active`
- `POST /api/popups/{popupCampaign}/track-view`
- `POST /api/popups/{popupCampaign}/track-click`

> هذه endpoints متاحة للمستخدم النهائي لتحديد الحملة المناسبة وتسجيل الـ analytics events.

---

### 1) Get Active Popup
- `GET /api/popups/active`

**Query Params (اختيارية):**
- `page_type` مثل: `home`, `product`, `cart`
- `current_url` رابط الصفحة الحالي

**Response عند عدم وجود حملة مطابقة:**
```json
{
  "data": null
}
```

**Response عند وجود حملة مطابقة:**
```json
{
  "data": {
    "id": 1,
    "title": {
      "ar": "عرض الربيع",
      "en": "Spring Offer"
    },
    "slug": "spring-offer-popup",
    "type": "modal",
    "status": "active",
    "priority": 90,
    "content": {
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
      }
    },
    "buttons": {
      "primary": "Shop Now",
      "secondary": "Learn More",
      "url": "https://example.com/sale"
    },
    "media": {
      "type": "image",
      "path": "https://your-domain.com/storage/popups/spring.jpg"
    },
    "form": {
      "enabled": false,
      "fields": ["name", "email"]
    },
    "display": {
      "pages": ["home", "category"],
      "audience_type": "all_visitors"
    },
    "trigger": {
      "type": "delay",
      "value": 5
    },
    "frequency": {
      "show_every": 60,
      "max_impressions": 3
    }
  }
}
```

---

### 2) Track Popup View
- `POST /api/popups/{popupCampaign}/track-view`

**Body (اختياري):**
```json
{
  "page_type": "home",
  "current_url": "https://example.com",
  "referrer": "https://google.com"
}
```

**Response:**
- `204 No Content`

---

### 3) Track Popup Click
- `POST /api/popups/{popupCampaign}/track-click`

**Body (اختياري):**
```json
{
  "page_type": "home",
  "current_url": "https://example.com",
  "referrer": "https://google.com"
}
```

**Response:**
- `204 No Content`

---

### Notes
- لا يوجد validation صارم لهذه endpoints على body، ويتم حفظ القيم المتاحة داخل `payload`.
- تم إلغاء الحقول القديمة `cta_type` و`cta_value`.
- رابط الإجراء في الـ response هو `buttons.url`.
