# Flutter — Product detail (`shop_variants` + `country`)

## What broke

On the storefront, product detail crashed when the API returned:

```json
{
  "shop_variants": [],
  "country": { "id": 9, "name": { "ar": "تركيا", "en": "Turkey" }, "code": "+90" }
}
```

The request itself was fine (`GET /api/user/products/{id}` → **200**). The app (and the web) crashed when code assumed `shop_variants[0]` always exists, or when `country` was treated as a nested object.

The backend is fixed. Flutter still needs defensive parsing so the screen never crashes, and so add-to-cart is disabled correctly when the product is not linked to a shop.

> User routes are under `/api/user`. Same endpoints — behavior + field shapes changed. No new URL.

---

## 1) API changes

```http
GET /api/user/products/{id}
```

| Field | Before | After |
|---|---|---|
| `shop_variants` | Could be `[]` | **Always at least one item** |
| `country` | Full object `{ id, name: {ar,en}, code, ... }` | **Localized string** (`"Turkey"` / `"تركيا"`) or `null` |
| `shop_variants[].shop_id` | Always present | May be **`null`** (no shop link) |
| `shop_variants[].id` | Always present | May be **`null`** (no `shop_product_variant`) |

Same `country` change applies to the product list:

```http
GET /api/user/products
```

`attributes_map`, `available_shops`, `category_details`, and `extra_details` can still be empty arrays. That is valid.

---

## 2) `shop_variants` shapes

### Linked to a shop (normal)

```json
{
  "shop_variants": [
    {
      "id": 44,
      "variant_id": 44,
      "sku": "JEANS-RED",
      "model": "4280",
      "barcode": "",
      "attributes": [
        { "attribute": "Color", "value": "Light blue", "type": "color" }
      ],
      "price": 25,
      "currency": "USD",
      "currency_symbol": "$",
      "price_formatted": "$ 25",
      "discount": 0,
      "price_after_discount": 25,
      "quantity": 12,
      "shop_id": 1,
      "is_restaurant": false,
      "city_id": 3,
      "images": [{ "id": 373, "path": "https://.../variant.webp" }]
    }
  ]
}
```

### Fallback (no variant / no shop link)

```json
{
  "shop_variants": [
    {
      "id": null,
      "variant_id": null,
      "sku": "LIG-8188-BASE",
      "model": "4280",
      "barcode": "",
      "attributes": [],
      "price": 20,
      "currency": "USD",
      "currency_symbol": "$",
      "price_formatted": "$ 20",
      "discount": 0,
      "price_after_discount": 20,
      "quantity": 100,
      "shop_id": null,
      "is_restaurant": false,
      "city_id": null,
      "images": [{ "id": 373, "path": "https://.../product.webp" }]
    }
  ]
}
```

Use this for display (name, price, images, description). **Do not** allow add-to-cart when `shop_id` or `id` is `null` — cart needs a real `shop_product_variant_id`.

---

## 3) What Flutter must change

### 3.1 Models / parsing

Update the product detail model so:

- `country` is `String?` (not a nested `Country` object).
- `shopVariants` is `List<ShopVariant>` and is never assumed non-empty without a guard.
- `ShopVariant.id` and `ShopVariant.shopId` are **nullable** (`int?`).

```dart
class ProductDetail {
  final String? country;
  final List<ShopVariant> shopVariants;
  // ...
}

class ShopVariant {
  final int? id;          // shop_product_variant id — nullable
  final int? variantId;
  final int? shopId;      // nullable
  final num price;
  final int quantity;
  final List<VariantAttribute> attributes;
  final List<ProductImage> images;
  // ...
}
```

If you used code generation (`json_serializable` / `freezed`), regenerate after changing the types. Old parsers that expect `country: { name: ... }` will throw on every product.

### 3.2 Never index `shop_variants[0]` blindly

```dart
// Bad — crashes when empty (legacy) or when you forget null checks
final variant = product.shopVariants[0];
final price = variant.price;

// Good
final variants = product.shopVariants;
final selected = variants.isNotEmpty ? variants.first : null;
final price = selected?.price ?? product.price;
```

Prefer selecting by attributes when `attributes_map` is not empty; otherwise use the first item.

### 3.3 Gate add-to-cart on shop link + stock

```dart
bool canAddToCart(ShopVariant? v) =>
    v?.shopId != null &&
    v?.id != null &&
    (v?.quantity ?? 0) > 0;
```

When `canAddToCart` is false:

- Keep the product page fully visible (title, gallery, price, description).
- Disable the add-to-cart button.
- Show a short message such as **"Currently unavailable"** / **"Not available in your area"**.
- Do **not** throw, show a red error screen, or navigate away.

### 3.4 `country` is a string

```dart
// Bad (breaks after this change)
product.country?.name?['en']
product.country.name.ar

// Good
product.country // "Turkey" or "تركيا" or null
```

Hide the origin row when `country` is null or empty.

### 3.5 Empty `attributes_map` is normal

```dart
final map = product.attributesMap; // List

if (map.isEmpty) {
  // Single SKU — no color/size picker
} else {
  // Show chips / pickers from attributes_map
}
```

Do not treat an empty map as an error.

### 3.6 Images fallback

```dart
List<String> galleryUrls(ProductDetail p, ShopVariant? v) {
  final fromVariant = v?.images.map((e) => e.path).whereType<String>().toList() ?? [];
  if (fromVariant.isNotEmpty) return fromVariant;

  final fromProduct = p.images.map((e) => e.path).whereType<String>().toList();
  if (fromProduct.isNotEmpty) return fromProduct;

  if (p.thumbnail != null && p.thumbnail!.isNotEmpty) return [p.thumbnail!];
  return [];
}
```

### 3.7 Error UI

Wrap product detail loading/rendering so a parse or null-safety miss shows a retry screen, not a red Flutter error:

```dart
FutureBuilder<ProductDetail>(
  future: repo.fetchProduct(id),
  builder: (context, snap) {
    if (snap.hasError) return ProductErrorView(onRetry: () => setState(() {}));
    if (!snap.hasData) return const ProductSkeleton();
    return ProductBody(product: snap.data!);
  },
);
```

---

## 4) Nullable / empty fields cheat sheet

| Field | Empty value | App behavior |
|---|---|---|
| `shop_variants[].shop_id` | `null` | Disable cart |
| `shop_variants[].id` | `null` | Disable cart |
| `shop_variants[].attributes` | `[]` | Hide attribute chips |
| `shop_variants[].images` | `[]` | Fall back to product images / thumbnail |
| `attributes_map` | `[]` | Hide variant picker |
| `available_shops` | `[]` | Hide shop list |
| `category_details` / `extra_details` | `[]` | Hide those tabs/sections |
| `country` | `null` | Hide origin row |
| `description` | `""` | Prefer `full_description` |

---

## 5) Minimal Dart flow

```dart
Future<void> openProduct(int id) async {
  final product = await api.getProduct(id);
  final variants = product.shopVariants;
  ShopVariant? selected = variants.isNotEmpty ? variants.first : null;

  // If attributes_map is not empty, let the user pick; then resolve selected.

  final canBuy = canAddToCart(selected);

  // Render:
  // - product.name
  // - product.country (String?)
  // - galleryUrls(product, selected)
  // - selected?.priceAfterDiscount ?? product.priceAfterDiscount
  // - Add to cart button enabled only when canBuy
  // - If !canBuy → "Currently unavailable"
}
```

Add-to-cart body should keep using the shop product variant id:

```dart
await api.addToCart(shopProductVariantId: selected!.id!);
```

Never call this when `selected.id` is null.

---

## 6) Product list (`GET /api/user/products`)

Only the `country` field shape changed for cards:

| Before | After |
|---|---|
| `country` object | `country` string or `null` |

`shop_product_variant_id` on list cards can still be `null`. If the list has a quick “add” action, disable it when that id is null — same rule as detail.

---

## 7) Checklist

- [ ] `country` parsed as `String?` on detail **and** list models
- [ ] `ShopVariant.id` / `shopId` are `int?`
- [ ] No blind `shopVariants[0]` access
- [ ] Add-to-cart disabled when `shop_id` or `id` is null
- [ ] Empty `attributes_map` hides the picker (not an error)
- [ ] Image fallback: variant → product images → thumbnail
- [ ] Product screen still renders when cart is unavailable
- [ ] Error/retry UI instead of uncaught exceptions
- [ ] Manually test products that have no shop link (e.g. id `23`, `24` on staging)

---

## 8) Important note

The backend fallback **prevents a crash** and keeps the page usable for browsing. It does **not** make the product purchasable. Purchasing requires the dashboard to save at least one variant **and** link it to a shop (`shop_variants` with a real `shop_id`).

Related docs:

- Web: `FRONTEND_WEB_PRODUCT_DETAIL_SHOP_VARIANTS.md`
- Dashboard: `FRONTEND_DASHBOARD_PRODUCT_VARIANTS_SAVE.md`
- Variant price/qty source of truth: `FRONTEND_DASHBOARD_VARIANT_PRICE_QUANTITY.md`
