# Badges in the User API

There is no standalone `/badges` route for public users. Instead badges are embedded inside other resources (products, vendors, baskets, shops, recipes, etc.) so you always consume them as part of those objects.

## Common badge shape

Every badge object returned to users follows the same `Badge\OneResource` structure:

```json
{
  "id": 123,
  "name": {
    "en": "New",
    "ar": "جديد"
  },
  "color": "success",
  "type": "image",
  "image": "https://example.com/storage/badges/abc.png",
  "position": "top"
}
```

- `type` is inferred on the server (`text`, `image`, or `gif`) based on whether a badge image was uploaded when it was created or last updated (`app/Services/Admin/BadgeService.php`).  
- `image` is generated via the model’s `getImageUrlAttribute()` and may be `null` for text-only badges.  
- `position` is only present when the badge comes from a polymorphic pivot (e.g., a product with `top_badges`/`bottom_badges`). Otherwise it will be `null`.

## Where badges appear for users

The following user-facing resources include badge lists (all using `Badge\OneResource`):

- **Products**: `Product\OneResource` and `Product\AllResource` expose `top_badges` and `bottom_badges` arrays on both detail and collection responses.  
- **Baskets**: `Basket\OneResource` and `Basket\AllResource`, plus the analogous scheduled basket resources, expose badges grouped by `pivot.position`.  
- **Stores/Vendors**: `Store\OneResource`, `Store\AllResource`, `Vendor\OneResource`, and `Vendor\AllResource` list badges for each entity.  
- **Recipes**: `Recipe\AllResource`, `Recipe\OneResource`, and `Recipe\AdminOneResource` (users get only the public ones) provide `top_badges`/`bottom_badges`.  
- **Other contexts**: Admin-only badge lists (e.g., `Admin\Basket\OneResource`) follow the same shape when reused in user flows.

Whenever you fetch one of those entities from the user API routes (`routes/api/user.php`, `routes/api/vendor.php`, etc.), inspect the `top_badges`/`bottom_badges` arrays for badge metadata. Use the `image` URL and `type` to determine how to render each badge.
