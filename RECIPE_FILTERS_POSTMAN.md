# Recipe Filters - Postman Examples

## Endpoint
```
GET /api/user/recipes
```

## Available Filters

### 1. Basic Filters

#### Search by Name/Description
```
GET /api/user/recipes?search=كبسة
```
**Description:** Search in recipe name and description (supports Arabic and English)

#### Filter by Discount Range
```
GET /api/user/recipes?discount_min=10&discount_max=50
```
**Description:** Returns recipes with discount between 10% and 50%

#### Filter by Serves
```
GET /api/user/recipes?serves=4
```
**Description:** Returns recipes that serve a specific number of people

#### Filter by Prepare Time
```
GET /api/user/recipes?prepare_time=30 minutes
```
**Description:** Returns recipes with specific preparation time

---

### 2. Combined Filters

#### Search + Discount
```
GET /api/user/recipes?search=rice&discount_min=20
```

#### Serves + Prepare Time
```
GET /api/user/recipes?serves=4&prepare_time=30 minutes
```

#### All Filters Combined
```
GET /api/user/recipes?search=chicken&discount_min=10&discount_max=30&serves=4&prepare_time=45 minutes
```

---

### 3. Pagination & Sorting

#### With Pagination
```
GET /api/user/recipes?page=1&per_page=20
```

#### With Sorting
```
GET /api/user/recipes?sort_field=discount&sort_order=desc
```

**Available sort fields:**
- `id`
- `discount`
- `rating`
- `orders_count`
- `created_at`

**Sort orders:**
- `asc` (ascending)
- `desc` (descending)

#### Combined: Filters + Pagination + Sorting
```
GET /api/user/recipes?search=rice&discount_min=20&sort_field=rating&sort_order=desc&page=1&per_page=10
```

---

## Postman Collection Format

### Request 1: Get All Recipes
```json
{
  "name": "Get All Recipes",
  "request": {
    "method": "GET",
    "header": [],
    "url": {
      "raw": "{{base_url}}/api/user/recipes",
      "host": ["{{base_url}}"],
      "path": ["api", "user", "recipes"]
    }
  }
}
```

### Request 2: Search Recipes
```json
{
  "name": "Search Recipes",
  "request": {
    "method": "GET",
    "header": [],
    "url": {
      "raw": "{{base_url}}/api/user/recipes?search=كبسة",
      "host": ["{{base_url}}"],
      "path": ["api", "user", "recipes"],
      "query": [
        {
          "key": "search",
          "value": "كبسة"
        }
      ]
    }
  }
}
```

### Request 3: Filter by Discount
```json
{
  "name": "Filter by Discount",
  "request": {
    "method": "GET",
    "header": [],
    "url": {
      "raw": "{{base_url}}/api/user/recipes?discount_min=10&discount_max=50",
      "host": ["{{base_url}}"],
      "path": ["api", "user", "recipes"],
      "query": [
        {
          "key": "discount_min",
          "value": "10"
        },
        {
          "key": "discount_max",
          "value": "50"
        }
      ]
    }
  }
}
```

### Request 4: Filter by Serves
```json
{
  "name": "Filter by Serves",
  "request": {
    "method": "GET",
    "header": [],
    "url": {
      "raw": "{{base_url}}/api/user/recipes?serves=4",
      "host": ["{{base_url}}"],
      "path": ["api", "user", "recipes"],
      "query": [
        {
          "key": "serves",
          "value": "4"
        }
      ]
    }
  }
}
```

### Request 5: Filter by Prepare Time
```json
{
  "name": "Filter by Prepare Time",
  "request": {
    "method": "GET",
    "header": [],
    "url": {
      "raw": "{{base_url}}/api/user/recipes?prepare_time=30 minutes",
      "host": ["{{base_url}}"],
      "path": ["api", "user", "recipes"],
      "query": [
        {
          "key": "prepare_time",
          "value": "30 minutes"
        }
      ]
    }
  }
}
```

### Request 6: Sort by Rating
```json
{
  "name": "Sort by Rating",
  "request": {
    "method": "GET",
    "header": [],
    "url": {
      "raw": "{{base_url}}/api/user/recipes?sort_field=rating&sort_order=desc",
      "host": ["{{base_url}}"],
      "path": ["api", "user", "recipes"],
      "query": [
        {
          "key": "sort_field",
          "value": "rating"
        },
        {
          "key": "sort_order",
          "value": "desc"
        }
      ]
    }
  }
}
```

### Request 7: Sort by Discount
```json
{
  "name": "Sort by Discount",
  "request": {
    "method": "GET",
    "header": [],
    "url": {
      "raw": "{{base_url}}/api/user/recipes?sort_field=discount&sort_order=desc",
      "host": ["{{base_url}}"],
      "path": ["api", "user", "recipes"],
      "query": [
        {
          "key": "sort_field",
          "value": "discount"
        },
        {
          "key": "sort_order",
          "value": "desc"
        }
      ]
    }
  }
}
```

### Request 8: Combined Filters
```json
{
  "name": "Combined Filters",
  "request": {
    "method": "GET",
    "header": [],
    "url": {
      "raw": "{{base_url}}/api/user/recipes?search=rice&discount_min=20&serves=4&sort_field=rating&sort_order=desc&page=1&per_page=10",
      "host": ["{{base_url}}"],
      "path": ["api", "user", "recipes"],
      "query": [
        {
          "key": "search",
          "value": "rice"
        },
        {
          "key": "discount_min",
          "value": "20"
        },
        {
          "key": "serves",
          "value": "4"
        },
        {
          "key": "sort_field",
          "value": "rating"
        },
        {
          "key": "sort_order",
          "value": "desc"
        },
        {
          "key": "page",
          "value": "1"
        },
        {
          "key": "per_page",
          "value": "10"
        }
      ]
    }
  }
}
```

### Request 9: Get Recipe by ID
```json
{
  "name": "Get Recipe by ID",
  "request": {
    "method": "GET",
    "header": [],
    "url": {
      "raw": "{{base_url}}/api/user/recipes/1",
      "host": ["{{base_url}}"],
      "path": ["api", "user", "recipes", "1"]
    }
  }
}
```

---

## Filter Parameters Summary

| Parameter | Type | Required | Description | Example |
|-----------|------|----------|-------------|---------|
| `search` | string | No | Search in name/description | `كبسة`, `rice` |
| `discount_min` | numeric | No | Minimum discount percentage | `10` |
| `discount_max` | numeric | No | Maximum discount percentage | `50` |
| `serves` | string | No | Number of servings | `4`, `6-8` |
| `prepare_time` | string | No | Preparation time | `30 minutes`, `1 hour` |
| `sort_field` | string | No | Field to sort by | `id`, `discount`, `rating`, `orders_count`, `created_at` |
| `sort_order` | string | No | Sort direction | `asc`, `desc` |
| `page` | integer | No | Page number | `1` |
| `per_page` | integer | No | Items per page | `10`, `20` |

---

## Expected Response Format

```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": {
          "ar": "كبسة رز",
          "en": "Kabsa Rice"
        },
        "description": {
          "ar": "كبسة رز بالدجاج",
          "en": "Rice Kabsa with Chicken"
        },
        "image": "https://example.com/storage/recipes/kabsa.jpg",
        "video_url": "https://youtube.com/watch?v=...",
        "discount": 15,
        "discount_type": "percentage",
        "delivery_price": 5000,
        "rating": 4.5,
        "orders_count": 120,
        "serves": "4",
        "prepare_time": "45 minutes",
        "total_items_price": 50000,
        "total_after_discount": 42500,
        "items": [
          {
            "id": 1,
            "quantity": 1,
            "shop_product_variant": {
              "id": 1,
              "price": 25000,
              "product_variant": {
                "product": {
                  "name": {
                    "ar": "دجاج",
                    "en": "Chicken"
                  }
                }
              },
              "shop": {
                "name": {
                  "ar": "متجر اللحوم",
                  "en": "Meat Shop"
                }
              }
            }
          }
        ],
        "steps": [
          {
            "step_number": 1,
            "description": {
              "ar": "اغسل الرز جيداً",
              "en": "Wash the rice well"
            }
          }
        ]
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 3,
      "per_page": 10,
      "total": 25
    }
  }
}
```

---

## Testing Tips

1. **Test each filter individually first** to ensure it works
2. **Then combine filters** to test complex scenarios
3. **Test edge cases:**
   - Empty search results
   - discount_min > discount_max
   - Invalid serves or prepare_time values
4. **Test sorting** with different fields and orders
5. **Test pagination** with different per_page values

---

## Common Issues & Solutions

### Issue 1: No results returned
**Solution:** Check if recipes exist in the database with the specified filters

### Issue 2: Search returns nothing
**Solution:** Check the locale setting and ensure recipes have translations

### Issue 3: Discount filter not working
**Solution:** Ensure discount values are stored as percentages (0-100)

### Issue 4: Sorting not working
**Solution:** Ensure the sort_field value is one of the allowed fields

---

## Recipe Data Structure

### Recipe Fields
- `name`: Translatable (ar, en)
- `description`: Translatable (ar, en)
- `image`: Recipe image URL
- `video_url`: Optional cooking video
- `discount`: Percentage discount (0-100)
- `discount_type`: 'fixed' or 'percentage'
- `delivery_price`: Delivery cost in smallest currency unit
- `rating`: Average rating (0-5)
- `orders_count`: Number of times ordered
- `serves`: Number of servings (e.g., "4", "6-8")
- `prepare_time`: Preparation time (e.g., "30 minutes", "1 hour")

### Recipe Items
Each recipe contains multiple items (ingredients) with:
- `shop_product_variant`: The product variant from a specific shop
- `quantity`: Amount needed for the recipe

### Recipe Steps
Cooking instructions ordered by `step_number`
