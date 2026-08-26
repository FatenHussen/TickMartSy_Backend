# Flutter — Dynamic Navigation Menu (Top Bar)

> **⚠️ Merged into [`FRONTEND_FLUTTER_COMPLETE.md`](./FRONTEND_FLUTTER_COMPLETE.md)** — send that single file to the Flutter team.

The top navigation bar (Main Categories, Brands, All shops, My baskets, Points & rewards, Help & support, Subscription packages...) is **no longer hardcoded**. It is fully managed from the dashboard and must be fetched from the API and rendered dynamically.

> New public user route: `GET /api/user/nav-menu` (no token required).

---

## 1) What changed / what to fix

| Before | After |
|--------|-------|
| Menu entries hardcoded in the app | Fetched from `GET /api/user/nav-menu` |
| Fixed order / labels | Admin controls title, order, visibility |
| Manual navigation per link | Each item returns a ready `target` per `type` |

**Required fix:** remove the static list and drive the bar from the API + a navigation map keyed by `type`.

---

## 2) Request

```http
GET /api/user/nav-menu
Accept-Language: ar   // or en — controls the label language
```

Returns **active items only**, already sorted by `order` ascending. No client-side sorting needed.

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

| `type` | Value | Flutter destination |
|--------|-------|---------------------|
| `route` | `target.route_key` | Fixed in-app screen (map below) |
| `category` | `target.category_id` | Category page → `GET /api/user/categories/{id}/page` |
| `brand` | `target.brand_id` | Brand screen |
| `page` | `target.page_id` / `target.slug` | Page Builder screen |
| `url` | `target.url` | Open in browser / in-app webview |

### `route_key` → app screens

Known, fixed values — define this map once:

```
home | categories | brands | shops | baskets | points | help | subscriptions
```

> If a future `route_key` is not in your map, ignore that item safely (don't crash).

---

## 4) Dart models

```dart
class NavMenuItem {
  final int id;
  final String title;
  final String type; // route | category | brand | page | url
  final String? icon;
  final int order;
  final bool openInNewTab;
  final Map<String, dynamic> target;

  NavMenuItem({
    required this.id,
    required this.title,
    required this.type,
    required this.icon,
    required this.order,
    required this.openInNewTab,
    required this.target,
  });

  factory NavMenuItem.fromJson(Map<String, dynamic> json) => NavMenuItem(
        id: json['id'] as int,
        title: json['title'] as String,
        type: json['type'] as String,
        icon: json['icon'] as String?,
        order: json['order'] as int,
        openInNewTab: json['open_in_new_tab'] as bool? ?? false,
        target: (json['target'] as Map?)?.cast<String, dynamic>() ?? {},
      );
}

Future<List<NavMenuItem>> fetchNavMenu() async {
  final res = await dio.get('/api/user/nav-menu');
  final data = (res.data['data'] as List);
  return data.map((e) => NavMenuItem.fromJson(e)).toList();
}
```

---

## 5) Navigation handler

```dart
void openNavItem(BuildContext context, NavMenuItem item) {
  switch (item.type) {
    case 'route':
      final key = item.target['route_key'] as String?;
      switch (key) {
        case 'home':          context.go('/'); break;
        case 'categories':    context.go('/categories'); break;
        case 'brands':        context.go('/brands'); break;
        case 'shops':         context.go('/shops'); break;
        case 'baskets':       context.go('/my-baskets'); break;
        case 'points':        context.go('/points'); break;
        case 'help':          context.go('/help'); break;
        case 'subscriptions': context.go('/subscription-packages'); break;
        default: break; // unknown key → ignore safely
      }
      break;

    case 'category':
      context.push('/category/${item.target['category_id']}');
      break;

    case 'brand':
      context.push('/brand/${item.target['brand_id']}');
      break;

    case 'page':
      context.push('/page/${item.target['slug'] ?? item.target['page_id']}');
      break;

    case 'url':
      final url = item.target['url'] as String?;
      if (url != null) launchUrl(Uri.parse(url), mode: LaunchMode.externalApplication);
      break;
  }
}
```

---

## 6) Notes

- **Language:** send the correct `Accept-Language`; the `title` comes back in that language only. Refetch when the user switches language.
- **Caching:** safe to cache briefly, but invalidate on language change.
- **Empty state:** if `data` is empty, hide the bar instead of showing an empty strip.
- **Icons:** `icon` is a full URL when present; otherwise render text-only.

---

## 7) Checklist

- [ ] Remove the hardcoded menu list
- [ ] Fetch `GET /api/user/nav-menu` and render items in returned order
- [ ] `route_key` → screen map implemented
- [ ] Navigation driven by `type` + `target`
- [ ] `url` items open externally (respect `open_in_new_tab` where relevant)
- [ ] Safe fallback for unknown `route_key`
- [ ] Refetch on language switch
