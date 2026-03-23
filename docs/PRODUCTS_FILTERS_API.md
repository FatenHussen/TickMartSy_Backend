# Product Filters API Documentation

## Overview
This document provides comprehensive information about filtering products in the user-facing API. The product filtering system supports various criteria including categories, price ranges, attributes, availability, and sorting options.

## Base Endpoint
```
GET /api/user/products
```

## Authentication
- Required: Yes (Bearer Token)
- Header: `Authorization: Bearer {token}`

---

## Available Filters

### 1. Category Filter
Filter products by category. Automatically includes all subcategories.

**Parameter:** `category_id`  
**Type:** Integer  
**Example:**
```
GET /api/user/products?category_id=5
```

**Response:** Returns products from category 5 and all its subcategories.

---

### 2. Shop Filter
Filter products available in a specific shop.

**Parameter:** `shop_id`  
**Type:** Integer  
**Example:**
```
GET /api/user/products?shop_id=3
```

---

### 3. Brand Filter
Filter products by brand.

**Parameter:** `brand_id`  
**Type:** Integer  
**Example:**
```
GET /api/user/products?brand_id=7
```

---

### 4. Price Range Filter
Filter products within a specific price range. Prices are automatically converted from user's currency to USD.

**Parameters:**
- `price_min` (optional): Minimum price
- `price_max` (optional): Maximum price

**Type:** Numeric  
**Examples:**
```
GET /api/user/products?price_min=100
GET /api/user/products?price_max=500
GET /api/user/products?price_min=100&price_max=500
```

---

### 5. Search Filter
Search products by name, description, or country.

**Parameter:** `search`  
**Type:** String  
**Max Length:** 255  
**Example:**
```
GET /api/user/products?search=rice
```

**Note:** Search is locale-aware and searches in the current language.

---

### 6. Country Filter
Filter products by country of origin.

**Parameter:** `country`  
**Type:** String  
**Max Length:** 100  
**Example:**
```
GET /api/user/products?country=Syria
```

---

### 7. Free Delivery Filter
Filter products from shops that offer free delivery.

**Parameter:** `is_free_delivery`  
**Type:** Boolean (true/false, 1/0)  
**Example:**
```
GET /api/user/products?is_free_delivery=true
```

---

### 8. On Sale Filter
Filter products that have active discounts.

**Parameter:** `on_sale`  
**Type:** Boolean (true/false, 1/0)  
**Example:**
```
GET /api/user/products?on_sale=true
```

---

### 9. In Stock Filter
Filter products that are currently available in stock.

**Parameter:** `in_stock_only`  
**Type:** Boolean (true/false, 1/0)  
**Example:**
```
GET /api/user/products?in_stock_only=true
```

---

### 10. Attribute Values Filter
Filter products by specific attributes (color, size, material, etc.).

**Parameter:** `attribute_values`  
**Type:** Array or Comma-separated string  
**Examples:**

Array format:
```
GET /api/user/products?attribute_values[]=1&attribute_values[]=5
```

Comma-separated format:
```
GET /api/user/products?attribute_values=1,5,8
```

**Use Case:** Filter products that have red color (attribute_value_id=1) OR large size (attribute_value_id=5).

---

## Product Types

The `type` parameter provides predefined filtering and sorting combinations.

**Parameter:** `type`  
**Available Values:**
- `new` - Newest products (sorted by creation date)
- `trend` - Trending products (sorted by sales count)
- `most_popular` - Same as trend
- `top_rated` - Highest rated products
- `offers` - Products with discounts (sorted by discount percentage)
- `recommended` - Recommended for user (based on order history)
- `for_you` - Personalized products
- `search_based` - Search-based filtering (requires `search` parameter)

**Examples:**
```
GET /api/user/products?type=new
GET /api/user/products?type=trend
GET /api/user/products?type=top_rated
GET /api/user/products?type=offers
```

---

## Sorting Options

**Parameter:** `sort_by`  
**Available Values:**
- `price_desc` - Price: High to Low
- `price_asc` - Price: Low to High
- `newest` - Newest First
- `oldest` - Oldest First
- `rating_desc` - Highest Rated
- `rating_asc` - Lowest Rated

**Examples:**
```
GET /api/user/products?sort_by=price_desc
GET /api/user/products?sort_by=newest
GET /api/user/products?sort_by=rating_desc
```

---

## Pagination

All product listing endpoints support pagination.

**Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15)

**Example:**
```
GET /api/user/products?page=2&per_page=20
```

---

## Combined Filter Examples

### Example 1: Products in Category with Price Range
```
GET /api/user/products?category_id=5&price_min=100&price_max=500
```

### Example 2: On Sale Products with Free Delivery
```
GET /api/user/products?on_sale=true&is_free_delivery=true
```

### Example 3: Search with Filters
```
GET /api/user/products?search=rice&category_id=3&in_stock_only=true
```

### Example 4: Brand Products Sorted by Price
```
GET /api/user/products?brand_id=7&sort_by=price_asc
```

### Example 5: Trending Products in Specific Shop
```
GET /api/user/products?type=trend&shop_id=3
```

### Example 6: Products with Specific Attributes
```
GET /api/user/products?attribute_values=1,5&in_stock_only=true
```

### Example 7: Top Rated Products on Sale
```
GET /api/user/products?type=top_rated&on_sale=true&price_max=1000
```

### Example 8: Complete Filter Combination
```
GET /api/user/products?category_id=5&brand_id=7&price_min=100&price_max=500&on_sale=true&in_stock_only=true&is_free_delivery=true&sort_by=price_asc&page=1&per_page=20
```

---

## Response Structure

### Success Response (200 OK)

```json
{
  "status": true,
  "message": "Products retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Premium White Rice",
        "description": "High quality long grain rice",
        "price": 250.00,
        "original_price": 300.00,
        "discount": 16.67,
        "country": "Syria",
        "rating": 4.5,
        "rating_count": 120,
        "is_favorite": true,
        "approval_status": "approved",
        "category": {
          "id": 5,
          "name": "Rice & Grains"
        },
        "brand": {
          "id": 7,
          "name": "Premium Foods"
        },
        "media": [
          {
            "id": 1,
            "path": "products/rice-001.jpg",
            "order": 1
          }
        ],
        "icons": [
          {
            "id": 1,
            "name": "Organic",
            "image": "icons/organic.png"
          }
        ],
        "badges": [
          {
            "id": 1,
            "name": "Best Seller",
            "position": "top"
          }
        ],
        "variants": [
          {
            "id": 1,
            "attributes_values_ids": [1, 5],
            "shop_variants": [
              {
                "id": 1,
                "shop_id": 3,
                "quantity": 50,
                "price": 250.00,
                "shop": {
                  "id": 3,
                  "name": "Downtown Store",
                  "is_free_delivery": true
                }
              }
            ]
          }
        ],
        "extra_details": [
          {
            "id": 1,
            "key": "Weight",
            "value": "1kg",
            "price": null
          }
        ],
        "created_at": "2026-03-20T10:00:00.000000Z",
        "updated_at": "2026-03-23T08:30:00.000000Z"
      }
    ],
    "current_page": 1,
    "per_page": 15,
    "total": 45,
    "last_page": 3,
    "from": 1,
    "to": 15
  }
}
```

### Error Response (422 Unprocessable Entity)

```json
{
  "status": false,
  "message": "Validation error",
  "errors": {
    "category_id": ["The selected category id is invalid."],
    "price_min": ["The price min must be a number."]
  }
}
```

---

## Filter Validation Rules

| Parameter | Type | Required | Validation |
|-----------|------|----------|------------|
| category_id | Integer | No | Must exist in categories table |
| shop_id | Integer | No | Must exist in shops table |
| brand_id | Integer | No | Must exist in brands table |
| price_min | Numeric | No | Must be >= 0 |
| price_max | Numeric | No | Must be >= 0 |
| country | String | No | Max 100 characters |
| search | String | No | Max 255 characters |
| type | String | No | Must be one of: new, trend, top_rated, offers, recommended, for_you, search_based, most_popular |
| sort_by | String | No | Must be one of: price_desc, price_asc, newest, oldest, rating_desc, rating_asc |
| is_free_delivery | Boolean | No | true/false or 1/0 |
| on_sale | Boolean | No | true/false or 1/0 |
| in_stock_only | Boolean | No | true/false or 1/0 |
| attribute_values | Array/String | No | Array of integers or comma-separated string |
| page | Integer | No | Must be >= 1 |
| per_page | Integer | No | Must be between 1-100 |

---

## Best Practices

### 1. Performance Optimization
- Use specific filters to reduce result set size
- Combine `category_id` with other filters for faster queries
- Use pagination to avoid loading too many products at once

### 2. User Experience
- Show filter counts before applying (e.g., "50 products found")
- Allow users to clear individual filters
- Save user's filter preferences for future sessions

### 3. Mobile Optimization
- Use smaller `per_page` values on mobile (10-15 items)
- Implement infinite scroll with pagination
- Cache filter results for better performance

### 4. Search Best Practices
- Combine `search` with `type=search_based` for better results
- Use `search` with category filters to narrow results
- Implement search suggestions based on popular searches

---

## Common Use Cases

### Use Case 1: Category Page
Display all products in a category with sorting options.
```
GET /api/user/products?category_id=5&sort_by=newest&per_page=20
```

### Use Case 2: Shop Page
Display all products available in a specific shop.
```
GET /api/user/products?shop_id=3&in_stock_only=true
```

### Use Case 3: Deals Page
Display all products with active discounts.
```
GET /api/user/products?type=offers&on_sale=true
```

### Use Case 4: Search Results
Display search results with filters.
```
GET /api/user/products?search=rice&in_stock_only=true&sort_by=price_asc
```

### Use Case 5: Brand Page
Display all products from a specific brand.
```
GET /api/user/products?brand_id=7&sort_by=top_rated
```

### Use Case 6: Free Delivery Products
Display products with free delivery option.
```
GET /api/user/products?is_free_delivery=true&in_stock_only=true
```

---

## Notes

1. **Currency Conversion**: All prices are automatically converted to the user's preferred currency.

2. **Favorites**: The `is_favorite` field is only included for authenticated users.

3. **Category Hierarchy**: When filtering by `category_id`, all subcategories are automatically included.

4. **Approval Status**: Only approved products are returned in user-facing APIs.

5. **Locale Support**: All translatable fields (name, description, country) are returned in the user's current locale.

6. **Attribute Filtering**: The `attribute_values` filter uses OR logic - products matching ANY of the specified attributes are returned.

7. **Stock Availability**: The `in_stock_only` filter checks across all shop variants of a product.

---

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 401 | Unauthorized (invalid or missing token) |
| 422 | Validation error (invalid filter parameters) |
| 500 | Server error |

---

## Support

For additional support or questions about the Product Filters API, please contact the development team.
