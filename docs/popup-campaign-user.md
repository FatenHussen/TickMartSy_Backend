## Popup Campaign User API

توثيق APIs الخاصة بالـ user في الكنترولر:
`app/Http/Controllers/User/PopupCampaignController.php`

## Base URL

`/api`

## Endpoints

- `GET /api/popups/active`
- `POST /api/popups/{popupCampaign}/track-view`
- `POST /api/popups/{popupCampaign}/track-click`

## 1) Get Active Popup

### Endpoint

`GET /api/popups/active`

### Description

يرجع أول حملة Popup فعالة ومطابقة للسياق الحالي (نوع الزائر + الصفحة الحالية + أولوية الحملة).

### Query Params (اختيارية)

- `page_type` (string): مثال `home`, `product`, `cart`. لو مش موجود، القيمة الافتراضية `home`.
- `current_url` (string): رابط الصفحة الحالية. لو مش موجود، يتم استخدام رابط الطلب الحالي تلقائيا.

### Success Response (عند وجود حملة مطابقة)

Status: `200 OK`

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
      "fields": [
        "name",
        "email"
      ]
    },
    "display": {
      "pages": [
        "home",
        "category"
      ],
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

### Success Response (عند عدم وجود حملة مطابقة)

Status: `200 OK`

```json
{
  "data": null
}
```

## 2) Track Popup View

### Endpoint

`POST /api/popups/{popupCampaign}/track-view`

### Path Param

- `popupCampaign` (integer): ID الحملة.

### Request Body (اختياري)

```json
{
  "page_type": "home",
  "current_url": "https://example.com",
  "referrer": "https://google.com"
}
```

### Success Response

Status: `204 No Content`

Body: فارغ.

## 3) Track Popup Click

### Endpoint

`POST /api/popups/{popupCampaign}/track-click`

### Path Param

- `popupCampaign` (integer): ID الحملة.

### Request Body (اختياري)

```json
{
  "page_type": "home",
  "current_url": "https://example.com",
  "referrer": "https://google.com"
}
```

### Success Response

Status: `204 No Content`

Body: فارغ.

## Notes

- endpoints دي حاليا بدون middleware auth في `routes/api.php`، فممكن استخدامها للزائر غير المسجل.
- في `track-view` و `track-click` يتم حفظ فقط الحقول التالية في `payload`: `page_type`, `current_url`, `referrer`.
- قيم event type المحفوظة في قاعدة البيانات:
  - `view` عند `track-view`
  - `click` عند `track-click`
- لو `popupCampaign` غير موجود، Laravel route model binding بيرجع `404 Not Found`.
