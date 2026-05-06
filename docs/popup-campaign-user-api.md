# Popup Campaign User API

This document explains the user-facing popup campaign endpoints handled by `PopupCampaignController`.

Base path: `/api`

## Endpoints

### 1) Get active popup campaign

- **Method:** `GET`
- **URL:** `/api/popups/active`
- **Auth:** Public (no auth middleware on this route)

#### Query params

- `page_type` (optional, string): current page slug. Defaults to `home`.
- `product_id` (optional, integer): used for campaigns linked to a product.
- `shop_id` (optional, integer): used for campaigns linked to a shop.
- `recipe_id` (optional, integer): used for campaigns linked to a recipe.
- `basket_id` (optional, integer): used for campaigns linked to a basket.

#### Selection rules (high level)

The API returns the first active campaign by priority that matches:

1. Audience (`all`, `guests`, `logged-in`, `new`, `returning`).
2. Page (if campaign has page targeting, `page_type` must match).
3. Entity context (if campaign is linked to entities, one matching `*_id` must be provided).

If no campaign matches, response is still successful with:

```json
{
  "data": null
}
```

#### Success response (200)

```json
{
  "data": {
    "id": 12,
    "title": {
      "en": "Mega Offer"
    },
    "slug": "mega-offer",
    "type": "modal",
    "status": "active",
    "priority": 100,
    "content": {
      "headline": {
        "en": "Save More"
      },
      "subheadline": {
        "en": "Today only"
      },
      "description": {
        "en": "Special discount for you"
      }
    },
    "buttons": {
      "primary": "Shop Now",
      "secondary": "Later",
      "url": "https://example.com/offers"
    },
    "media": {
      "type": "image",
      "path": "https://your-domain/storage/popup-campaigns/banner.jpg"
    },
    "form": {
      "enabled": false,
      "fields": []
    },
    "display": {
      "pages": [
        "home",
        "product_details"
      ],
      "audience_type": "all_visitors"
    },
    "trigger": {
      "type": "delay",
      "value": 5
    },
    "frequency": {
      "show_every": 0,
      "max_impressions": 1
    },
    "scoped_to_entities": true,
    "products": [],
    "shops": [],
    "recipes": [],
    "baskets": []
  }
}
```

---

### 2) Track popup view

- **Method:** `POST`
- **URL:** `/api/popups/{popupCampaign}/track-view`
- **Auth:** Public

#### Path param

- `popupCampaign` (required): popup campaign ID (Laravel route model binding).

#### Request body (JSON, optional fields)

```json
{
  "page_type": "home",
  "current_url": "https://app.example.com/home",
  "referrer": "https://google.com"
}
```

Only these keys are stored in event payload: `page_type`, `current_url`, `referrer`.

#### Success response

- Status: `204 No Content`
- Body: empty

---

### 3) Track popup click

- **Method:** `POST`
- **URL:** `/api/popups/{popupCampaign}/track-click`
- **Auth:** Public

#### Path param

- `popupCampaign` (required): popup campaign ID.

#### Request body (JSON, optional fields)

```json
{
  "page_type": "home",
  "current_url": "https://app.example.com/home",
  "referrer": "https://google.com"
}
```

Only these keys are stored in event payload: `page_type`, `current_url`, `referrer`.

#### Success response

- Status: `204 No Content`
- Body: empty

## Notes

- `page_type` is normalized internally (trimmed, lowercased, spaces replaced with `_`).
- New vs returning visitor detection uses session (`popup_campaign_first_visit`).
- `track-view` and `track-click` currently do not validate campaign status, so events can be created for any existing campaign ID.
