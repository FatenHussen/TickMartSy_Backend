# Filters Documentation

This document provides comprehensive information about all available filters across different endpoints in the API.

---

## Table of Contents

1. [Products Filters](#products-filters)
2. [Recipes Filters](#recipes-filters)
3. [Brands Filters](#brands-filters)
4. [Categories Filters](#categories-filters)
5. [Shops Filters](#shops-filters)
6. [Orders Filters](#orders-filters)
7. [Favorites Filters](#favorites-filters)
8. [Baskets Filters](#baskets-filters)

---

## Products Filters

**Endpoint:** `GET /api/user/products`

### Available Filters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `category_id` | integer | Filter by category | `category_id=1` |
| `shop_id` | integer | Filter by shop | `shop_id=5` |
| `brand_id` | integer | Filter by brand | `brand_id=3` |
| `country_id` | integer | Filter by country | `country_id=2` |
| `price_min` | numeric | Minimum price | `price_min=10` |
| `price_max` | numeric | Maximum price | `price_max=100` |
| `is_free_delivery` | boolean | Free delivery only | `is_free_delivery=1` |
| `on_sale` | boolean | Products with discount | `on_sale=1` |
| `in_stock_only` | boolean | In stock products only | `in_stock_only=1` |
| `attribute_values` | array | Filter by attribute values | `attribute_values[]=1&attribute_values[]=5` |
| `type` | string | Type filter (new, top_rated, most_popular) | `type=new` |
| `search` | string | Search in name/description | `search=laptop` |

### Example Requests

```
GET /api/user/products?category_id=1&price_min=50&price_max=200
GET /api/user/products?shop_id=5&on_sale=1&in_stock_only=1
GET /api/user/products?type=new&is_free_delivery=1
GET /api/user/products?attribute_values[]=1&attribute_values[]=5&category_id=2
```

---

## Recipes Filters

**Endpoint:** `GET /api/user/recipes`

### Available Filters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `search` | string | Search in name/description | `search=pasta` |
| `discount_min` | numeric | Minimum discount percentage | `discount_min=10` |
| `discount_max` | numeric | Maximum discount percentage | `discount_max=50` |
| `serves_min` | integer | Minimum servings | `serves_min=2` |
| `serves_max` | integer | Maximum servings | `serves_max=6` |
| `prepare_time_min` | integer | Minimum preparation time (minutes) | `prepare_time_min=15` |
| `prepare_time_max` | integer | Maximum preparation time (minutes) | `prepare_time_max=60` |
| `sortField` | string | Sort field (discount, rating, orders_count, created_at) | `sortField=rating` |
| `sortOrder` | string | Sort order (asc, desc) | `sortOrder=desc` |

### Example Requests

```
GET /api/user/recipes?search=chicken&serves_min=4
GET /api/user/recipes?discount_min=20&sortField=discount&sortOrder=desc
GET /api/user/recipes?prepare_time_max=30&serves_max=4
```

---

## Brands Filters

**Endpoint:** `GET /api/user/brands`

### Available Filters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `search` | string | Search by brand name | `search=nike` |
| `type` | string | Type filter (new, top_rated, most_popular) | `type=top_rated` |

### Type Filters Explained

- `new`: Orders by created_at DESC (newest brands first)
- `top_rated`: Orders by average rating DESC
- `most_popular`: Orders by total products sold count DESC

### Example Requests

```
GET /api/user/brands?search=adidas
GET /api/user/brands?type=new
GET /api/user/brands?type=most_popular
```

---

## Categories Filters

**Endpoint:** `GET /api/user/categories`

### Available Filters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `name` | string | Filter by category name | `name=electronics` |
| `parent_id` | integer | Filter by parent category | `parent_id=1` |
| `search` | string | Search in category name | `search=fashion` |
| `shop_id` | integer | Categories with products in shop | `shop_id=5` |
| `type` | string | Type filter (new, most_popular, top_rated) | `type=most_popular` |

### Type Filters Explained

- `new`: Orders by created_at DESC (newest categories first)
- `most_popular`: Orders by total products sold in category DESC
- `top_rated`: Orders by average product rating in category DESC

### Example Requests

```
GET /api/user/categories?search=electronics
GET /api/user/categories?shop_id=5&type=most_popular
GET /api/user/categories?parent_id=1&type=top_rated
GET /api/user/categories?type=new
```

---

## Shops Filters

**Endpoint:** `GET /api/user/shops`

### Available Filters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `governorate_id` | integer | Filter by governorate | `governorate_id=1` |
| `category_id` | integer | Filter by category | `category_id=3` |
| `search` | string | Search by shop name | `search=mall` |

### Example Requests

```
GET /api/user/shops?governorate_id=1&category_id=2
GET /api/user/shops?search=supermarket
GET /api/user/shops?governorate_id=3&search=restaurant
```

---

## Orders Filters

**Endpoint:** `GET /api/user/orders`

### Available Filters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `status` | string | Filter by order status | `status=pending` |

### Available Status Values

- `pending`
- `preparing`
- `out_delivery`
- `delivered`
- `cancelled`

### Example Requests

```
GET /api/user/orders?status=pending
GET /api/user/orders?status=delivered
GET /api/user/orders?status=cancelled
```

---

## Favorites Filters

**Endpoint:** `GET /api/user/favorites`

### Available Filters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `type` | string | Filter by favorite type | `type=product` |
| `shop_id` | integer | Filter by shop (for products) | `shop_id=5` |
| `category_id` | integer | Filter by category (for products/baskets) | `category_id=2` |

### Available Type Values

- `product`
- `recipe`
- `brand`
- `basket`

### Example Requests

```
GET /api/user/favorites?type=product
GET /api/user/favorites?type=product&shop_id=5
GET /api/user/favorites?type=product&category_id=2
GET /api/user/favorites?type=basket&category_id=3
```

---

## Baskets Filters

**Endpoint:** `GET /api/user/baskets`

### Available Filters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `is_schedule` | boolean | Filter scheduled baskets | `is_schedule=1` |
| `category_id` | integer | Filter by category | `category_id=2` |
| `price_min` | numeric | Minimum price | `price_min=50` |
| `price_max` | numeric | Maximum price | `price_max=200` |
| `rating_min` | numeric | Minimum rating (0-5) | `rating_min=4` |
| `items_count_min` | integer | Minimum items count | `items_count_min=5` |
| `items_count_max` | integer | Maximum items count | `items_count_max=20` |
| `type` | string | Type filter (new, best_selling, top_rated) | `type=best_selling` |

### Type Filters Explained

- `new`: Orders by created_at DESC (newest baskets first)
- `best_selling`: Orders by num_sold DESC (most sold baskets first)
- `top_rated`: Orders by rating DESC (highest rated baskets first)

### Example Requests

```
GET /api/user/baskets?is_schedule=1&category_id=2
GET /api/user/baskets?price_min=50&price_max=150&rating_min=4
GET /api/user/baskets?type=best_selling&category_id=3
GET /api/user/baskets?items_count_min=10&items_count_max=20
GET /api/user/baskets?type=new&is_schedule=0
```

---

## Common Parameters

All endpoints support these common parameters:

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `page` | integer | Page number for pagination | `page=2` |
| `per_page` | integer | Items per page | `per_page=20` |
| `search` | string | General search (where applicable) | `search=keyword` |
| `sortField` | string | Field to sort by | `sortField=created_at` |
| `sortOrder` | string | Sort direction (asc/desc) | `sortOrder=desc` |

---

## Response Format

All filtered endpoints return data in this format:

```json
{
  "status": true,
  "message": "Success message",
  "data": {
    "items": [...],
    "pagination": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 10,
      "total": 50
    }
  }
}
```

---

## Notes

1. All boolean filters accept `1`/`0` or `true`/`false`
2. Multiple filters can be combined in a single request
3. Array parameters can be sent as `param[]=value1&param[]=value2` or `param=value1,value2`
4. Numeric filters (price, rating) support decimal values
5. All filters are optional - omit them to get unfiltered results
6. **Search is case-insensitive** - searching for "Nike", "nike", or "NIKE" will return the same results
