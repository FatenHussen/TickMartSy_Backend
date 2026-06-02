# Admin Product Variants - Update and Delete

Base URL: /api/admin
Auth: Bearer <admin_token>

## Product Variants

### Update

Endpoint:
- PUT /product-variants/{product_variant}
- PATCH /product-variants/{product_variant}

Permissions:
- crud.permission:productvariant

Request body (multipart/form-data when sending images):
- name: object (optional)
  - name.ar: string | null
  - name.en: string | null
- sku: string | null (optional, unique in product_variants)
- model: string | null (optional)
- barcode: string | null (optional)
- attributes_values_ids: array<int> (optional)
- is_trend: boolean (optional)
- is_active: boolean (optional)
- images: array<file> (optional, each image max 5MB)
- existing_images_ids: array<int> (optional, keep only these image IDs)

Example request fields (send as form fields; files in images[]):

```json
{
  "name": {
    "en": "Large",
    "ar": "كبير"
  },
  "sku": "PV-1001-L",
  "model": "L-2024",
  "barcode": "1234567890123",
  "attributes_values_ids": [12, 34],
  "is_trend": true,
  "is_active": true,
  "existing_images_ids": [91, 92]
}
```

Success response (200):

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "id": 10,
    "product_id": 4,
    "name": {
      "ar": "كبير",
      "en": "Large"
    },
    "sku": "PV-1001-L",
    "model": "L-2024",
    "barcode": "1234567890123",
    "product": {
      "id": 4,
      "product_number": "PR-0004",
      "name": {
        "ar": "منتج",
        "en": "Product"
      },
      "description": {
        "ar": "وصف",
        "en": "Description"
      },
      "price": 100,
      "discount": 10,
      "country": "KW",
      "image": "https://cdn.example.com/products/4/main.jpg",
      "images": [
        "https://cdn.example.com/products/4/1.jpg",
        "https://cdn.example.com/products/4/2.jpg"
      ],
      "category": {
        "id": 3,
        "name": {
          "ar": "تصنيف",
          "en": "Category"
        }
      },
      "brand": {
        "id": 2,
        "name": {
          "ar": "ماركة",
          "en": "Brand"
        },
        "logo": "https://cdn.example.com/brands/2/logo.png"
      }
    },
    "attributes": [
      {
        "id": 12,
        "name": "Red",
        "display_name": "Red (#FF0000)",
        "hex": "#FF0000",
        "color": {
          "id": 8,
          "name": "Red",
          "hex": "#FF0000"
        },
        "category_attribute": {
          "id": 5,
          "name": "Color",
          "type": "color"
        }
      },
      {
        "id": 34,
        "name": "XL",
        "display_name": "XL",
        "hex": null,
        "color": null,
        "category_attribute": {
          "id": 7,
          "name": "Size",
          "type": "text"
        }
      }
    ],
    "attributes_values_ids": [12, 34],
    "is_trend": true,
    "is_active": true,
    "shop_variants": [
      {
        "id": 55,
        "shop": {
          "id": 9,
          "name": "Shop A",
          "logo": "https://cdn.example.com/shops/9/logo.png",
          "address": "Street 1",
          "is_restaurant": false,
          "city_id": 1
        },
        "price": 120,
        "discount": 5,
        "price_after_discount": 114,
        "cost_price": 90,
        "quantity": 15,
        "sku": "SPV-55",
        "barcode": "998877665544"
      }
    ],
    "created_at": "2026-05-11 10:05:00",
    "updated_at": "2026-05-11 11:20:00"
  }
}
```

Validation error (422):

```json
{
  "status": false,
  "message": "Validation Error",
  "errors": {
    "sku": ["The sku has already been taken."]
  }
}
```

### Delete

Endpoint:
- DELETE /product-variants/{product_variant}

Permissions:
- crud.permission:productvariant

Success response (200):

```json
{
  "status": true,
  "message": "Success",
  "data": true
}
```

Active orders error (422):

```json
{
  "message": "Cannot delete product variant with active orders."
}
```

## Shop Product Variants

### Update

Endpoint:
- PUT /shop-product-variants/{shop_product_variant}
- PATCH /shop-product-variants/{shop_product_variant}

Permissions:
- crud.permission:shopproductvariant

Request body (application/json):
- price: number (optional, min 0)
- cost_price: number (optional, min 0)
- quantity: integer (optional, min 0)

Example request:

```json
{
  "price": 120,
  "cost_price": 90,
  "quantity": 12
}
```

Success response (200):

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "id": 55,
    "product_variant_id": 10,
    "variant": {
      "id": 10,
      "name": {
        "ar": "كبير",
        "en": "Large"
      },
      "sku": "PV-1001-L",
      "model": "L-2024",
      "barcode": "1234567890123"
    },
    "shop_id": 9,
    "is_restaurant": false,
    "city_id": 1,
    "variant_image": "https://cdn.example.com/variants/10/1.jpg",
    "product": {
      "id": 4,
      "product_number": "PR-0004",
      "name": {
        "ar": "منتج",
        "en": "Product"
      },
      "description": {
        "ar": "وصف",
        "en": "Description"
      },
      "price": 100,
      "discount": 10,
      "country": "KW",
      "image": "https://cdn.example.com/products/4/main.jpg",
      "images": [
        "https://cdn.example.com/products/4/1.jpg",
        "https://cdn.example.com/products/4/2.jpg"
      ],
      "category": {
        "id": 3,
        "name": {
          "ar": "تصنيف",
          "en": "Category"
        }
      },
      "brand": {
        "id": 2,
        "name": {
          "ar": "ماركة",
          "en": "Brand"
        },
        "logo": "https://cdn.example.com/brands/2/logo.png"
      }
    },
    "attributes": [
      {
        "id": 12,
        "name": "Red",
        "display_name": "Red (#FF0000)",
        "hex": "#FF0000",
        "color": {
          "id": 8,
          "name": "Red",
          "hex": "#FF0000"
        },
        "category_attribute": {
          "id": 5,
          "name": "Color",
          "type": "color"
        }
      }
    ],
    "attributes_values_ids": [12, 34],
    "is_trend": true,
    "shop": {
      "id": 9,
      "name": "Shop A",
      "logo": "https://cdn.example.com/shops/9/logo.png",
      "address": "Street 1",
      "email": "shop@example.com",
      "mobile": "+96500000000",
      "is_restaurant": false,
      "city_id": 1
    },
    "price": 120,
    "discount": 5,
    "price_after_discount": 114,
    "cost_price": 90,
    "quantity": 12,
    "sku": "SPV-55",
    "barcode": "998877665544",
    "created_at": "2026-05-11 10:05:00",
    "updated_at": "2026-05-11 11:20:00"
  }
}
```

Validation error (422):

```json
{
  "status": false,
  "message": "Validation Error",
  "errors": {
    "price": ["The price must be at least 0."]
  }
}
```

### Delete

Endpoint:
- DELETE /shop-product-variants/{shop_product_variant}

Permissions:
- crud.permission:shopproductvariant

Success response (200):

```json
{
  "status": true,
  "message": "Success",
  "data": true
}
```

Active orders error (422):

```json
{
  "message": "Cannot delete shop product variant with active orders."
}
```
