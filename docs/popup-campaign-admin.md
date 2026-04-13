## Popup Campaigns (Admin)

### Overview
- Admins manage popup campaigns via the `/api/admin/popup-campaigns` resource.  
- Each entry uses `PopupCampaignService`, `PopupCampaignRequest`, and JSON resources for consistent responses.

### CRUD Operations
- `GET /api/admin/popup-campaigns` returns paginated results with sorting and search (fields: title, headline, description).  
- `POST`/`PUT` validate payloads through `PopupCampaignRequest`, leveraging enum constants (type, status, CTA, media, audience, trigger) and JSON casts (`form_fields`, `show_on_pages`).  
- `DELETE` removes the campaign (with cascade for already registered events).

### Key Fields
- `show_on_pages`: accepts arrays containing page identifiers (`home`, `product`, `cart`, `page_slug`, `custom_url`, `exclude_pages`). The selector normalizes these to decide visibility.  
- `audience_type`: controls guests vs logged-in vs visitors (new/returning).  
- `trigger_type` + `trigger_value`: determines when the frontend should show (on load, delay seconds, scroll percentage, exit intent).  
- `frequency`: `show_every` in minutes and `max_impressions` limit how often each device sees the popup.

### Media & Forms
- `media_type` + `media_path` support images, videos, GIFs. Admins upload the asset and record the storage path.  
- `form_enabled` toggles a dynamic form. `form_fields` accepts named inputs (e.g., `email`, `phone`, `custom_text`).

### Seeder Examples
- `PopupCampaignSeeder` ships with 15+ realistic records spanning all statuses, audience types, CTAs, triggers, and page targets. Use seeds when testing admin behavior.
