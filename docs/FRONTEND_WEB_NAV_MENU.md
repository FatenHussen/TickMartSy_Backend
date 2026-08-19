# Frontend Web — Dynamic Navigation Menu (Top Bar)

The top navigation bar (Main Categories, Brands, All shops, My baskets, Points & rewards, Help & support, Subscription packages...) is **no longer hardcoded**. It is fully managed from the dashboard, and the web app must fetch it from the API and render it dynamically.

> New public user route: `GET /api/user/nav-menu` (no token required).

---

## 1) What changed / what to fix

| Before | After |
|--------|-------|
| Menu entries hardcoded in the code (array/constants) | Fetched from `GET /api/user/nav-menu` |
| Fixed order / labels | Admin controls title, order, and visibility |
| Manual navigation per link | Each item returns a ready `target` per `type` |

**Required fix:** remove the static menu list and replace it with an API fetch + a navigation map keyed by `type`.

---

## 2) Request

```http
GET /api/user/nav-menu
Accept-Language: ar   // or en — controls the label language
```

Returns **active items only**, already sorted by `order` ascending (ready to render as-is, no extra sorting needed).

### Response shape

```json
{
  "status": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "title": "Main Categories",
      "type": "route",
      "icon": null,
      "order": 1,
      "open_in_new_tab": false,
      "target": { "route_key": "categories" }
    },
    {
      "id": 8,
      "title": "Electronics",
      "type": "category",
      "icon": "https://.../storage/icons/x.png",
      "order": 2,
      "open_in_new_tab": false,
      "target": { "category_id": 12, "name": "Electronics" }
    },
    {
      "id": 9,
      "title": "Ramadan offers",
      "type": "page",
      "target": { "page_id": 5, "slug": "ramadan-offers" }
    },
    {
      "id": 10,
      "title": "Our blog",
      "type": "url",
      "open_in_new_tab": true,
      "target": { "url": "https://example.com/blog" }
    }
  ]
}
```

- `title` is a single localized string (not a `{ar,en}` map).
- `icon` can be `null` — render text-only in that case.

---

## 3) Navigation by `type`

Map each type to the matching web destination:

| `type` | Value | Suggested web destination |
|--------|-------|---------------------------|
| `route` | `target.route_key` | Fixed internal path (see map below) |
| `category` | `target.category_id` | Category page: `/categories/{id}` (consumes `GET /api/user/categories/{id}/page`) |
| `brand` | `target.brand_id` | Brand page: `/brands/{id}` |
| `page` | `target.slug` or `target.page_id` | Page Builder page: `/pages/{slug}` |
| `url` | `target.url` | External link (respect `open_in_new_tab`) |

### `route_key` → web routes map

Known, fixed values — define this once in the app:

```js
const ROUTE_MAP = {
  home:          "/",
  categories:    "/categories",
  brands:        "/brands",
  shops:         "/shops",
  baskets:       "/my-baskets",
  points:        "/points",
  help:          "/help",
  subscriptions: "/subscription-packages",
};
```

> If the backend adds a new `route_key` later and it's not in the map, ignore the item safely (fallback) instead of crashing.

---

## 4) React example

```jsx
import { useEffect, useState } from "react";

const ROUTE_MAP = {
  home: "/", categories: "/categories", brands: "/brands", shops: "/shops",
  baskets: "/my-baskets", points: "/points", help: "/help",
  subscriptions: "/subscription-packages",
};

function hrefFor(item) {
  switch (item.type) {
    case "route":    return ROUTE_MAP[item.target.route_key] ?? null;
    case "category": return `/categories/${item.target.category_id}`;
    case "brand":    return `/brands/${item.target.brand_id}`;
    case "page":     return `/pages/${item.target.slug ?? item.target.page_id}`;
    case "url":      return item.target.url;
    default:         return null;
  }
}

export function TopNavBar() {
  const [items, setItems] = useState([]);

  useEffect(() => {
    api.get("/api/user/nav-menu").then((res) => setItems(res.data.data));
  }, []);

  return (
    <nav className="top-nav">
      {items.map((item) => {
        const href = hrefFor(item);
        if (!href) return null; // unknown route_key → ignore safely

        const isExternal = item.type === "url";
        return (
          <a
            key={item.id}
            href={href}
            target={item.open_in_new_tab ? "_blank" : undefined}
            rel={isExternal && item.open_in_new_tab ? "noopener noreferrer" : undefined}
          >
            {item.icon && <img src={item.icon} alt="" className="nav-icon" />}
            <span>{item.title}</span>
          </a>
        );
      })}
    </nav>
  );
}
```

---

## 5) Notes

- **Caching:** you may cache the result (e.g. 5 minutes) since it rarely changes, but refetch when the language changes (because `title` is language-dependent).
- **Language:** send the correct `Accept-Language`; the label comes back in the requested language only.
- **External links:** use `rel="noopener noreferrer"` with `target="_blank"`.
- **Empty state:** if `data` is empty, hide the bar instead of showing an empty strip.

---

## 6) Checklist

- [ ] Remove the hardcoded menu list from the code
- [ ] Fetch `GET /api/user/nav-menu` and render items in the returned order
- [ ] `route_key` → web routes map defined
- [ ] Navigation driven by `type` + `target`
- [ ] `open_in_new_tab` applied to external links
- [ ] Safe fallback for any unknown `route_key`
- [ ] Refetch on language switch
