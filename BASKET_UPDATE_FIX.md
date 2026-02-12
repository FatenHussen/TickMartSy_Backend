# Basket Update Issue - SOLVED

## Problem
The update request was receiving empty data because Laravel doesn't parse nested array form-data properly with PUT/PATCH methods.

## Solution

### Option 1: Use JSON (Recommended for requests without file upload)

**Method:** PUT  
**URL:** `http://127.0.0.1:8000/api/admin/baskets/5`  
**Headers:**
- Content-Type: application/json
- Accept: application/json
- Authorization: Bearer {token}

**Body (raw JSON):**
```json
{
    "category_id": 1,
    "name": {
        "en": "Breakfast Basket",
        "ar": "سلة الإفطار"
    },
    "discount_type": "percentage",
    "discount": 10,
    "items": [
        {
            "shop_product_variant_id": 2,
            "quantity": 2
        },
        {
            "shop_product_variant_id": 3,
            "quantity": 1
        }
    ],
    "offer_ends_at": "01-01-2030"
}
```

### Option 2: Use POST with _method override (For file uploads)

**Method:** POST  
**URL:** `http://127.0.0.1:8000/api/admin/baskets/5`  
**Headers:**
- Accept: application/json
- Authorization: Bearer {token}

**Body (form-data):**
```
_method: PUT
category_id: 1
name[en]: Breakfast Basket
name[ar]: سلة الإفطار
discount_type: percentage
discount: 10
items[0][shop_product_variant_id]: 2
items[0][quantity]: 2
items[1][shop_product_variant_id]: 3
items[1][quantity]: 1
offer_ends_at: 01-01-2030
image: [file]
```

## Why This Happens

Laravel's request parsing works differently for different HTTP methods:
- **POST requests**: PHP automatically parses form-data into arrays
- **PUT/PATCH requests**: PHP doesn't parse form-data, so Laravel receives raw input

When using PUT/PATCH with form-data, Laravel can't parse nested arrays like `items[0][shop_product_variant_id]`.

## Recommendation

1. **For updates without images**: Use JSON (Option 1)
2. **For updates with images**: Use POST with `_method=PUT` (Option 2)
3. **For create**: Use POST with form-data (works fine)
