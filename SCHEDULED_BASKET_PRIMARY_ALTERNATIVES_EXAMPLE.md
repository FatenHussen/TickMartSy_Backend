# Scheduled Basket - Primary + Alternatives Example

## Overview
Scheduled baskets now support BOTH:
- **Primary variant** (`shop_product_variant_id`) - Required, used for price calculation
- **Alternative variants** (`shop_product_variant_ids`) - Optional array of alternatives

## Form-Data Example for Create/Update

### Example 1: With Primary and Alternatives

```
category_id: 1
name[en]: Weekly Vegetables
name[ar]: خضروات أسبوعية
discount_type: percentage
discount: 15
delivery_price: 25
offer_ends_at: 31-12-2026

schedule[title][en]: Weekly Delivery
schedule[title][ar]: توصيل أسبوعي
schedule[number_of_days]: 7
schedule[discount_type]: percentage
schedule[discount_value]: 5
schedule[is_active]: 1

items[0][shop_product_variant_id]: 1
items[0][shop_product_variant_ids][0]: 10
items[0][shop_product_variant_ids][1]: 11
items[0][shop_product_variant_ids][2]: 12
items[0][quantity]: 2
items[0][is_required]: 1
items[0][is_extra]: 0
items[0][min_quantity]: 1
items[0][max_quantity]: 10

items[1][shop_product_variant_id]: 2
items[1][shop_product_variant_ids][0]: 15
items[1][shop_product_variant_ids][1]: 16
items[1][quantity]: 1
items[1][is_required]: 0
items[1][is_extra]: 1
items[1][min_quantity]: 1
items[1][max_quantity]: 5

image: [file upload]
```

### Example 2: Primary Only (No Alternatives)

```
category_id: 1
name[en]: Breakfast Basket
name[ar]: سلة الإفطار
discount_type: percentage
discount: 10
delivery_price: 20

schedule[title][en]: Weekly Breakfast
schedule[title][ar]: إفطار أسبوعي
schedule[number_of_days]: 7
schedule[discount_type]: percentage
schedule[discount_value]: 5
schedule[is_active]: 1

items[0][shop_product_variant_id]: 1
items[0][quantity]: 2
items[0][is_required]: 1
items[0][is_extra]: 0
items[0][min_quantity]: 1
items[0][max_quantity]: 10

items[1][shop_product_variant_id]: 5
items[1][quantity]: 1
items[1][is_required]: 0
items[1][is_extra]: 1
items[1][min_quantity]: 1
items[1][max_quantity]: 5

image: [file upload]
```

## JSON Example (for requests without file upload)

```json
{
  "category_id": 1,
  "name": {
    "en": "Weekly Vegetables",
    "ar": "خضروات أسبوعية"
  },
  "discount_type": "percentage",
  "discount": 15,
  "delivery_price": 25,
  "offer_ends_at": "31-12-2026",
  "schedule": {
    "title": {
      "en": "Weekly Delivery",
      "ar": "توصيل أسبوعي"
    },
    "number_of_days": 7,
    "discount_type": "percentage",
    "discount_value": 5,
    "is_active": true
  },
  "items": [
    {
      "shop_product_variant_id": 1,
      "shop_product_variant_ids": [10, 11, 12],
      "quantity": 2,
      "is_required": true,
      "is_extra": false,
      "min_quantity": 1,
      "max_quantity": 10
    },
    {
      "shop_product_variant_id": 2,
      "shop_product_variant_ids": [15, 16],
      "quantity": 1,
      "is_required": false,
      "is_extra": true,
      "min_quantity": 1,
      "max_quantity": 5
    }
  ]
}
```

## Response Structure

```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": {
    "id": 8,
    "category": {
      "id": 1,
      "name": "إلكترونيات"
    },
    "name": "خضروات أسبوعية",
    "image": "http://127.0.0.1:8000/storage/baskets/xxx.jpg",
    "num_varieties": 1,
    "offer_ends_at": "2026-12-31",
    "original_price": 454,
    "discount": "15.00",
    "discount_type": "percentage",
    "discount_amount": 68.1,
    "final_price": 385.9,
    "delivery_price": 25,
    "is_schedule": true,
    "items": [
      {
        "id": 21,
        "product_id": 1,
        "variant_id": 1,
        "shop_product_variant_id": 1,
        "primary_variant": {
          "id": 1,
          "shop_id": 1,
          "shop_name": "Shop Name",
          "product_name": "Product Name",
          "price": 227,
          "quantity": 100
        },
        "alternatives": [
          {
            "product_id": 2,
            "shop_product_variant_id": 10,
            "name": "Alternative Product 1",
            "image_url": "http://...",
            "price": 200
          },
          {
            "product_id": 3,
            "shop_product_variant_id": 11,
            "name": "Alternative Product 2",
            "image_url": "http://...",
            "price": 210
          }
        ],
        "alternative_variants": [
          {
            "id": 10,
            "shop_id": 1,
            "shop_name": "Shop Name",
            "product_name": "Alternative 1",
            "price": 200,
            "quantity": 50
          },
          {
            "id": 11,
            "shop_id": 1,
            "shop_name": "Shop Name",
            "product_name": "Alternative 2",
            "price": 210,
            "quantity": 30
          }
        ],
        "product": {
          "id": 1,
          "name": "منتج تجريبي",
          "image": "http://..."
        },
        "variant": ["#fc0303", "وسط"],
        "quantity": 2,
        "unit_price": 227,
        "subtotal": 454,
        "is_required": true,
        "is_extra": false,
        "min_quantity": 1,
        "max_quantity": 10
      }
    ],
    "schedules": [
      {
        "id": 9,
        "title": "توصيل أسبوعي",
        "number_of_days": 7,
        "discount_type": "percentage",
        "discount_value": "5.00",
        "is_active": true
      }
    ]
  }
}
```

## Key Changes

1. **Primary Variant (Required)**
   - Field: `shop_product_variant_id`
   - Used for price calculation
   - Must exist in database

2. **Alternative Variants (Optional)**
   - Field: `shop_product_variant_ids` (array)
   - Can be empty or contain multiple IDs
   - Not used for price calculation

3. **Price Calculation**
   - Only the primary variant price is used
   - Formula: `unit_price = primary_variant.price`
   - Subtotal: `unit_price * quantity`

4. **Response Fields**
   - `shop_product_variant_id`: Primary variant ID
   - `primary_variant`: Full details of primary variant
   - `alternatives`: Simplified list for user selection
   - `alternative_variants`: Full details of all alternatives

