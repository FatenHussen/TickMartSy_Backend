## Popup Campaigns (User-facing)

### API Endpoints
- `GET /api/popups/active`: returns the highest-priority active campaign matching the request context.  
- `POST /api/popups/{id}/track-view` & `POST /api/popups/{id}/track-click`: log impressions and interactions in `popup_campaign_events`.

### Request Inputs
- `page_slug` (preferred): used by `PopupCampaignSelector` to fetch the related `Page` model and match `show_on_pages`.  
- `page_type`, `current_url`: optional fallbacks for type-based targeting.  
- `visitorType` is inferred server-side via session to classify new vs returning visitors without extra client logic.

### Response Structure
- Wrapped in `App\Http\Resources\User\PopupCampaignResource`.  
- Includes content (`headline`, `description`), buttons, CTA metadata (`type`, `value`), media info, form configuration, trigger/frequency settings, and audience details.

### Frontend Behavior
- Blade component `resources/views/components/popup-campaign.blade.php` fetches `/api/popups/active` and renders modal/slide-in/fullscreen experiences.  
- The component respects triggers (on load, delay, scroll, exit intent), throttling (`show_every`, `max_impressions` via `localStorage`), and fires track-view/track-click when shown or interacted with.  
- Forms are dynamically built from `form_fields`, while CTA actions include navigation, coupon copy, or form submission hooks.

### Configuration Tips
- Set `window.popupCampaignOptions = { pageType: 'product', currentUrl: '/products/42', page_slug: 'product' };` before including the component for per-page accuracy.  
- Media paths should be accessible (e.g., `/storage/popups/...`).  
- Ensure the repeated view/click endpoints are protected by CSRF tokens if needed (the Blade component already includes `credentials: 'include'`).
