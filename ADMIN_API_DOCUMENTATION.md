# Admin API Documentation

## Table of Contents
1. [Authentication](#authentication)
2. [Products Management](#products-management)
3. [Orders Management](#orders-management)
4. [Baskets Management](#baskets-management)
5. [Scheduled Baskets Management](#scheduled-baskets-management)
6. [Shops Management](#shops-management)
7. [Categories Management](#categories-management)
8. [Brands Management](#brands-management)
9. [Packages Management](#packages-management)
10. [Subscriptions Management](#subscriptions-management)
11. [Gifts Management](#gifts-management)
12. [Point Exchanges Management](#point-exchanges-management)
13. [User Points Management](#user-points-management)
14. [Currency Management](#currency-management)
15. [User Basket Schedules (Read-Only)](#user-basket-schedules)
16. [Roles & Permissions](#roles-permissions)
17. [Admins Management](#admins-management)
18. [Drivers Management](#drivers-management)
19. [Users Management](#users-management)
20. [Vendors Management](#vendors-management)
21. [Stores Management](#stores-management)
22. [Governorates, Cities & Areas](#governorates-cities-areas)
23. [Services Management](#services-management)
24. [Sections Management](#sections-management)
25. [Page Sections Management](#page-sections-management)
26. [Banners Management](#banners-management)
27. [Coupons Management](#coupons-management)
28. [Complaints Management](#complaints-management)
29. [Recipes Management](#recipes-management)
30. [Languages Management](#languages-management)
31. [Category Attributes & Details](#category-attributes-details)

---

## Base URL
All admin endpoints are prefixed with: `/api/admin`

## Common Request Parameters
Most list endpoints support these query parameters:
- `search`: Search term (string)
- `sort_field`: Field to sort by (default: 'id')
- `sort_order`: Sort direction 'asc' or 'desc' (default: 'desc')
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 10)


---

## 1. Authentication

### A. Admin Login
**Endpoint:** `POST /api/admin/auth/login`

**Description:** Authenticate admin user and receive access token

**Request Body:**
```json
{
  "email": "admin@example.com",
  "password": "password123"
}
```

**Validation Rules:**
- `email`: required, valid email, must exist in admins table
- `password`: required

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "1|abc123...",
    "admin": {
      "id": 1,
      "name": "Admin Name",
      "email": "admin@example.com"
    }
  }
}
```

### B. Get Admin Profile
**Endpoint:** `GET /api/admin/auth/profile`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Admin Name",
    "email": "admin@example.com",
    "roles": [],
    "permissions": []
  }
}
```

### C. Admin Logout
**Endpoint:** `POST /api/admin/auth/logout`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": null
}
```

### D. Store/Update FCM Token
**Endpoint:** `POST /api/admin/auth/store-token`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "fcm_token": "fcm_device_token_here"
}
```

**Response:**
```json
{
  "success": true,
  "data": null
}
```


---

## 2. Products Management

### A. List Products
**Endpoint:** `GET /api/admin/products`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `search`: Search in product name
- `sort_field`: Field to sort by (default: 'id')
- `sort_order`: 'asc' or 'desc' (default: 'desc')
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 10)
- `shop_id`: Filter by shop ID (optional)

**Response:**
```json
{
  "success": true,
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

### B. Get Single Product
**Endpoint:** `GET /api/admin/products/{id}`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": {"ar": "منتج", "en": "Product"},
    "description": {"ar": "وصف", "en": "Description"},
    "price": 100,
    "category": {...},
    "variants": [...],
    "images": [...]
  }
}
```

### C. Create Product (Detailed)
**Endpoint:** `POST /api/admin/products`

**Headers:** 
- `Authorization: Bearer {token}`
- `Content-Type: multipart/form-data`

**Description:** Create a new product with full details including variants, images, category details, and shop-specific pricing.

---

#### Step-by-Step Integration Guide

**Step 1: Get Active Languages**
```
GET /api/admin/languages
```
You'll need the language codes (ar, en, etc.) for all multilingual fields.

**Step 2: Select Category**
```
GET /api/admin/categories
```
Get the `category_id` for the product.

**Step 3: Get Category Details (Optional)**
```
GET /api/admin/category-details?category_id={categoryId}
```
Get category-specific fields like "Weight", "Size", etc.

**Step 4: Get Category Attributes for Variants (Optional)**
```
GET /api/admin/category-attributes?category_id={categoryId}
```
Get attributes like "Color", "Size" with their values for creating variants.

**Step 5: Get Available Shops**
```
GET /api/admin/shops
```
Get shops where this product will be available with different prices/quantities.

**Step 6: Get Products for "Bought With" (Optional)**
```
GET /api/admin/products
```
Get other products to suggest as "frequently bought together".

---

#### Complete Request Body Structure

```json
{
  // ============ BASIC INFORMATION ============
  "category_id": 1,                    // Required
  "sku": "SKU-12345",                  // Optional, must be unique
  "model": "MODEL-ABC",                // Optional, must be unique
  "barcode": "1234567890123",          // Optional
  "price": 100,                        // Required, in smallest currency unit (cents)
  "price_after_discount": 80,          // Optional, discounted price
  "quantity": 50,                      // Optional, stock quantity
  "time_prepare": "30 minutes",        // Optional, preparation time
  "is_instant_delivery": true,         // Optional, boolean
  
  // ============ MULTILINGUAL FIELDS ============
  "name": {
    "ar": "طماطم طازجة",
    "en": "Fresh Tomatoes"
  },
  "description": {
    "ar": "طماطم طازجة من المزرعة",
    "en": "Fresh farm tomatoes"
  },
  "full_description": {
    "ar": "وصف تفصيلي كامل للمنتج",
    "en": "Complete detailed product description"
  },
  "country": {
    "ar": "مصر",
    "en": "Egypt"
  },
  
  // ============ PRODUCT IMAGES ============
  "images": [file1, file2, file3],     // Array of image files, max 2MB each
  
  // ============ PRODUCT VARIANTS ============
  // Example: Red-Small, Red-Large, Blue-Small, Blue-Large
  "variants": [
    {
      "attributes_values_ids": [1, 3],  // [Color: Red, Size: Small]
      "images": [file1, file2]          // Optional variant-specific images
    },
    {
      "attributes_values_ids": [1, 4],  // [Color: Red, Size: Large]
      "images": [file3]
    },
    {
      "attributes_values_ids": [2, 3],  // [Color: Blue, Size: Small]
      "images": []
    }
  ],
  
  // ============ CATEGORY DETAILS ============
  // Category-specific fields (e.g., Weight, Expiry Date)
  "category_details": [
    {
      "category_detail_id": 1,          // Weight field
      "detail_value": {
        "ar": "1 كيلو",
        "en": "1 KG"
      }
    },
    {
      "category_detail_id": 2,          // Expiry Date field
      "detail_value": {
        "ar": "7 أيام",
        "en": "7 days"
      }
    }
  ],
  
  // ============ EXTRA DETAILS ============
  // Custom key-value pairs for additional info
  "extra_details": [
    {
      "detail_key": {
        "ar": "العلامة التجارية",
        "en": "Brand"
      },
      "detail_value": {
        "ar": "علامة محلية",
        "en": "Local Brand"
      }
    },
    {
      "detail_key": {
        "ar": "بلد المنشأ",
        "en": "Origin Country"
      },
      "detail_value": {
        "ar": "مصر",
        "en": "Egypt"
      }
    }
  ],
  
  // ============ SHOP VARIANTS ============
  // Different price/quantity per shop per variant
  "shop_variants": [
    {
      "shop_id": 1,                     // Shop ID
      "variant_index": 0,               // Index from variants array (0 = first variant)
      "price": 100,                     // Shop-specific price
      "quantity": 50                    // Shop-specific quantity
    },
    {
      "shop_id": 1,
      "variant_index": 1,
      "price": 120,
      "quantity": 30
    },
    {
      "shop_id": 2,                     // Different shop
      "variant_index": 0,
      "price": 95,                      // Different price
      "quantity": 100                   // Different quantity
    }
  ],
  
  // ============ FREQUENTLY BOUGHT TOGETHER ============
  "bought_with": [5, 12, 23]           // Array of product IDs
}
```

---

#### Validation Rules (Complete)

**Basic Fields:**
- `category_id`: **required**, integer, must exist in categories table
- `sku`: optional, string, must be unique across all products
- `model`: optional, string, must be unique across all products
- `price`: **required**, integer, minimum 0
- `price_after_discount`: optional, integer, minimum 0
- `quantity`: optional, integer, minimum 0
- `barcode`: optional, string
- `time_prepare`: optional, string
- `is_instant_delivery`: optional, boolean

**Multilingual Fields** (for each active language):
- `name.{locale}`: optional, string, max 255 characters
- `description.{locale}`: optional, string
- `full_description.{locale}`: optional, string
- `country.{locale}`: optional, string

**Images:**
- `images`: optional, array
- `images.*`: image file (jpeg, png, jpg, gif), max 2048 KB (2MB)

**Variants:**
- `variants`: optional, array
- `variants.*.attributes_values_ids`: optional, array
- `variants.*.attributes_values_ids.*`: **required**, integer, must exist in attribute_values table
- `variants.*.images`: optional, array
- `variants.*.images.*`: optional, image file, max 2048 KB

**Category Details:**
- `category_details`: optional, array
- `category_details.*.category_detail_id`: optional, must exist in category_details table
- `category_details.*.detail_value`: optional, array (multilingual)
- `category_details.*.detail_value.{locale}`: optional, string

**Extra Details:**
- `extra_details`: optional, array
- `extra_details.*.detail_key`: optional, array (multilingual)
- `extra_details.*.detail_key.{locale}`: optional, string
- `extra_details.*.detail_value`: optional, array (multilingual)
- `extra_details.*.detail_value.{locale}`: optional, string

**Shop Variants:**
- `shop_variants`: optional, array
- `shop_variants.*.shop_id`: **required**, must exist in shops table
- `shop_variants.*.variant_index`: **required**, integer, minimum 0
- `shop_variants.*.price`: optional, integer, minimum 0
- `shop_variants.*.quantity`: optional, integer, minimum 0

**Bought With:**
- `bought_with`: optional, array
- `bought_with.*`: optional, integer, must exist in products table

---

#### Important Notes

1. **Vendor ID**: Automatically set from authenticated vendor-user (or defaults to 1)
2. **Multilingual Fields**: Must provide values for all active languages
3. **Variant Index**: Refers to the position in the `variants` array (0-based)
4. **Shop Variants**: If product has 3 variants and 2 shops, you can have up to 6 shop_variants (3 variants × 2 shops)
5. **Images**: Product can have main images + each variant can have its own images
6. **Price Format**: All prices are integers (e.g., 100 = 1.00 in currency)

---

#### Example: Simple Product (No Variants)

```json
{
  "category_id": 1,
  "price": 50,
  "name": {
    "ar": "تفاح أحمر",
    "en": "Red Apple"
  },
  "description": {
    "ar": "تفاح طازج",
    "en": "Fresh apple"
  },
  "images": [file1, file2]
}
```

---

#### Example: Product with Variants

```json
{
  "category_id": 2,
  "price": 100,
  "name": {
    "ar": "قميص قطني",
    "en": "Cotton Shirt"
  },
  "description": {
    "ar": "قميص قطني عالي الجودة",
    "en": "High quality cotton shirt"
  },
  "images": [file1, file2],
  
  // Variants: Red-Small, Red-Large, Blue-Small, Blue-Large
  "variants": [
    {"attributes_values_ids": [1, 3]},  // Red + Small
    {"attributes_values_ids": [1, 4]},  // Red + Large
    {"attributes_values_ids": [2, 3]},  // Blue + Small
    {"attributes_values_ids": [2, 4]}   // Blue + Large
  ],
  
  // Shop 1 sells all variants
  "shop_variants": [
    {"shop_id": 1, "variant_index": 0, "price": 100, "quantity": 10},
    {"shop_id": 1, "variant_index": 1, "price": 120, "quantity": 15},
    {"shop_id": 1, "variant_index": 2, "price": 100, "quantity": 8},
    {"shop_id": 1, "variant_index": 3, "price": 120, "quantity": 12}
  ]
}
```

---

#### Example: Product with Category Details

```json
{
  "category_id": 3,
  "price": 200,
  "name": {
    "ar": "جبنة موتزاريلا",
    "en": "Mozzarella Cheese"
  },
  "images": [file1],
  
  // Category-specific fields
  "category_details": [
    {
      "category_detail_id": 1,  // Weight
      "detail_value": {
        "ar": "500 جرام",
        "en": "500g"
      }
    },
    {
      "category_detail_id": 2,  // Expiry
      "detail_value": {
        "ar": "30 يوم",
        "en": "30 days"
      }
    }
  ]
}
```

---

#### Response (Success)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "category_id": 1,
    "sku": "SKU-12345",
    "model": "MODEL-ABC",
    "price": 100,
    "price_after_discount": 80,
    "quantity": 50,
    "name": {
      "ar": "طماطم طازجة",
      "en": "Fresh Tomatoes"
    },
    "description": {
      "ar": "طماطم طازجة من المزرعة",
      "en": "Fresh farm tomatoes"
    },
    "images": [
      {
        "id": 1,
        "url": "https://example.com/storage/products/image1.jpg"
      }
    ],
    "variants": [
      {
        "id": 1,
        "attributes": [
          {"name": "Color", "value": "Red"},
          {"name": "Size", "value": "Small"}
        ]
      }
    ],
    "shop_variants": [
      {
        "id": 1,
        "shop_id": 1,
        "variant_id": 1,
        "price": 100,
        "quantity": 50
      }
    ],
    "created_at": "2026-02-21T10:00:00Z"
  }
}
```

---

#### Response (Validation Error)

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "category_id": ["The category id field is required."],
    "price": ["The price field is required."],
    "name.ar": ["The name.ar field is required."],
    "variants.0.attributes_values_ids.0": ["The selected variant attribute value is invalid."],
    "shop_variants.0.shop_id": ["The selected shop id is invalid."]
  }
}
```

---

#### Understanding Product Variants & Shop Variants

**Concept Explanation:**

1. **Product Variants** = Different versions of the same product based on attributes
   - Example: T-Shirt with (Red-Small, Red-Large, Blue-Small, Blue-Large)
   - Each combination of attribute values = 1 variant

2. **Shop Variants** = Price and quantity for each variant in each shop
   - Same product variant can have different prices in different shops
   - Each shop can have different stock quantities

**Visual Example:**

```
Product: Cotton T-Shirt
├── Variant 0: Red + Small
│   ├── Shop 1: Price 100, Quantity 10
│   └── Shop 2: Price 95, Quantity 20
├── Variant 1: Red + Large
│   ├── Shop 1: Price 120, Quantity 15
│   └── Shop 2: Price 115, Quantity 25
├── Variant 2: Blue + Small
│   └── Shop 1: Price 100, Quantity 8
└── Variant 3: Blue + Large
    └── Shop 1: Price 120, Quantity 12
```

**How to Build Variants:**

**Step 1:** Get category attributes
```
GET /api/admin/category-attributes?category_id=2
```

**Response:**
```json
{
  "data": {
    "items": [
      {
        "id": 1,
        "name": {"ar": "اللون", "en": "Color"},
        "values": [
          {"id": 1, "value": {"ar": "أحمر", "en": "Red"}},
          {"id": 2, "value": {"ar": "أزرق", "en": "Blue"}}
        ]
      },
      {
        "id": 2,
        "name": {"ar": "المقاس", "en": "Size"},
        "values": [
          {"id": 3, "value": {"ar": "صغير", "en": "Small"}},
          {"id": 4, "value": {"ar": "كبير", "en": "Large"}}
        ]
      }
    ]
  }
}
```

**Step 2:** Create all combinations
```javascript
// Frontend logic to create combinations
const colors = [1, 2];  // Red, Blue
const sizes = [3, 4];   // Small, Large

const variants = [];
colors.forEach(color => {
  sizes.forEach(size => {
    variants.push({
      attributes_values_ids: [color, size]
    });
  });
});

// Result:
// variants = [
//   {attributes_values_ids: [1, 3]},  // Red + Small
//   {attributes_values_ids: [1, 4]},  // Red + Large
//   {attributes_values_ids: [2, 3]},  // Blue + Small
//   {attributes_values_ids: [2, 4]}   // Blue + Large
// ]
```

**Step 3:** Assign prices per shop
```javascript
// For each variant, set price/quantity per shop
const shop_variants = [];
variants.forEach((variant, index) => {
  // Shop 1
  shop_variants.push({
    shop_id: 1,
    variant_index: index,
    price: 100,
    quantity: 10
  });
  
  // Shop 2 (optional)
  shop_variants.push({
    shop_id: 2,
    variant_index: index,
    price: 95,
    quantity: 20
  });
});
```

---

#### Frontend Form Structure Recommendation

```html
<!-- Step 1: Basic Info -->
<form>
  <select name="category_id"><!-- Categories --></select>
  <input name="name[ar]" />
  <input name="name[en]" />
  <input name="price" type="number" />
  <input name="sku" />
  
  <!-- Step 2: Images -->
  <input type="file" name="images[]" multiple />
  
  <!-- Step 3: Variants (if category has attributes) -->
  <div id="variants-section">
    <!-- Dynamically generate based on category attributes -->
    <div class="variant">
      <label>Color:</label>
      <select><!-- Attribute values --></select>
      <label>Size:</label>
      <select><!-- Attribute values --></select>
      <input type="file" name="variants[0][images][]" />
    </div>
  </div>
  
  <!-- Step 4: Shop Variants -->
  <div id="shop-variants-section">
    <table>
      <thead>
        <tr>
          <th>Variant</th>
          <th>Shop</th>
          <th>Price</th>
          <th>Quantity</th>
        </tr>
      </thead>
      <tbody>
        <!-- For each variant × each shop -->
        <tr>
          <td>Red - Small</td>
          <td>Shop 1</td>
          <td><input name="shop_variants[0][price]" /></td>
          <td><input name="shop_variants[0][quantity]" /></td>
          <input type="hidden" name="shop_variants[0][shop_id]" value="1" />
          <input type="hidden" name="shop_variants[0][variant_index]" value="0" />
        </tr>
      </tbody>
    </table>
  </div>
  
  <!-- Step 5: Category Details (if category has details) -->
  <div id="category-details-section">
    <!-- Dynamically generate based on category details -->
    <div class="detail">
      <label>Weight:</label>
      <input name="category_details[0][detail_value][ar]" />
      <input name="category_details[0][detail_value][en]" />
      <input type="hidden" name="category_details[0][category_detail_id]" value="1" />
    </div>
  </div>
  
  <!-- Step 6: Extra Details (optional) -->
  <div id="extra-details-section">
    <button type="button" onclick="addExtraDetail()">Add Detail</button>
    <div class="extra-detail">
      <input name="extra_details[0][detail_key][ar]" placeholder="Key (AR)" />
      <input name="extra_details[0][detail_key][en]" placeholder="Key (EN)" />
      <input name="extra_details[0][detail_value][ar]" placeholder="Value (AR)" />
      <input name="extra_details[0][detail_value][en]" placeholder="Value (EN)" />
    </div>
  </div>
  
  <button type="submit">Create Product</button>
</form>
```

---

### D. Update Product
**Endpoint:** `PUT /api/admin/products/{id}`

**Headers:** `Authorization: Bearer {token}`

**Request Body:** Same structure as Create Product

**Notes:**
- All fields are optional in update (only send fields you want to change)
- To update variants, send the complete variants array
- To update shop_variants, send the complete shop_variants array
- SKU and Model must be unique (excluding current product)

**Response:** Same as Create Product

### E. Delete Product
**Endpoint:** `DELETE /api/admin/products/{id}`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": null
}
```


---

## 3. Orders Management

### A. List Orders
**Endpoint:** `GET /api/admin/orders`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `search`: Search term
- `sort_field`: Field to sort by (default: 'id')
- `sort_order`: 'asc' or 'desc' (default: 'desc')
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 10)
- `status`: Filter by status ('pending', 'preparing', 'out_delivery', 'delivered')

**Response:**
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "order_number": "ORD-001",
        "status": "pending",
        "total": 150,
        "user": {...},
        "items": [...]
      }
    ],
    "pagination": {...}
  }
}
```

### B. Change Order Status
**Endpoint:** `PATCH /api/admin/orders/{orderId}/change-status`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "status": "preparing"
}
```

**Validation Rules:**
- `status`: required, must be one of: 'pending', 'preparing', 'out_delivery', 'delivered'

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "status": "preparing",
    "updated_at": "2026-02-21T10:00:00Z"
  },
  "message": "Order status updated successfully"
}
```

### C. Assign Driver to Order
**Endpoint:** `POST /api/admin/orders/{orderId}/assign-driver`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "driver_id": 5
}
```

**Validation Rules:**
- `driver_id`: required, must exist in drivers table

**Response:**
```json
{
  "success": true,
  "data": null,
  "message": "Order assigned to driver successfully"
}
```

### D. Change Order Item Status
**Endpoint:** `PATCH /api/admin/orders/items/{itemId}/change-status`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "status": "preparing"
}
```

**Validation Rules:**
- `status`: required, must be one of: 'pending', 'preparing', 'out_delivery', 'delivered'

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "status": "preparing"
  },
  "message": "Item status updated successfully"
}
```


---

## 4. Baskets Management

### A. List Baskets
**Endpoint:** `GET /api/admin/baskets`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:** Standard pagination and search parameters

**Response:**
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": {"ar": "سلة", "en": "Basket"},
        "category": {...},
        "discount": 10,
        "discount_type": "percentage",
        "items": [...]
      }
    ],
    "pagination": {...}
  }
}
```

### B. Get Single Basket
**Endpoint:** `GET /api/admin/baskets/{id}`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": {"ar": "سلة", "en": "Basket"},
    "category": {...},
    "discount": 10,
    "discount_type": "percentage",
    "offer_ends_at": "2026-03-01",
    "delivery_price": 20,
    "items": [
      {
        "shop_product_variant_id": 1,
        "quantity": 2,
        "product": {...}
      }
    ]
  }
}
```

### C. Create Basket
**Endpoint:** `POST /api/admin/baskets`

**Headers:** `Authorization: Bearer {token}`, `Content-Type: multipart/form-data`

**Request Body:**
```json
{
  "category_id": 1,
  "name": {
    "ar": "سلة الخضروات",
    "en": "Vegetables Basket"
  },
  "offer_ends_at": "01-03-2026",
  "discount": 10,
  "discount_type": "percentage",
  "delivery_price": 20,
  "image": file,
  "items": [
    {
      "shop_product_variant_id": 1,
      "quantity": 2
    },
    {
      "shop_product_variant_id": 2,
      "quantity": 3
    }
  ]
}
```

**Validation Rules:**
- `category_id`: required, must exist in categories
- `name`: required, array with locale keys
- `name.*`: required, string, max 255
- `offer_ends_at`: optional, date (format: d-m-Y), must be after today
- `discount`: optional, numeric, min 0
- `discount_type`: required, must be 'fixed' or 'percentage'
- `image`: optional, image file (jpeg, png, jpg, gif), max 2MB
- `delivery_price`: optional, numeric, min 0
- `items`: required, array, min 1 item
- `items.*.shop_product_variant_id`: required, must exist in shop_product_variants
- `items.*.quantity`: required, integer, min 1

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": {...},
    "items": [...]
  }
}
```

### D. Update Basket
**Endpoint:** `PUT /api/admin/baskets/{id}`

**Headers:** `Authorization: Bearer {token}`

**Request Body:** Same as Create Basket

**Response:** Same as Create Basket

### E. Delete Basket
**Endpoint:** `DELETE /api/admin/baskets/{id}`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": null
}
```


---

## 5. Scheduled Baskets Management

### A. List Scheduled Baskets
**Endpoint:** `GET /api/admin/scheduled-baskets`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:** Standard pagination and search parameters

**Response:**
```json
{
  "success": true,
  "data": {
    "items": [...],
    "pagination": {...}
  }
}
```

### B. Get Single Scheduled Basket
**Endpoint:** `GET /api/admin/scheduled-baskets/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create Scheduled Basket
**Endpoint:** `POST /api/admin/scheduled-baskets`

**Headers:** `Authorization: Bearer {token}`

### D. Update Scheduled Basket
**Endpoint:** `PUT /api/admin/scheduled-baskets/{id}`

**Headers:** `Authorization: Bearer {token}`

### E. Delete Scheduled Basket
**Endpoint:** `DELETE /api/admin/scheduled-baskets/{id}`

**Headers:** `Authorization: Bearer {token}`

---

## 6. Shops Management

### A. List Shops
**Endpoint:** `GET /api/admin/shops`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:** Standard pagination and search parameters

### B. Get Single Shop
**Endpoint:** `GET /api/admin/shops/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create Shop
**Endpoint:** `POST /api/admin/shops`

**Headers:** `Authorization: Bearer {token}`, `Content-Type: multipart/form-data`

**Request Body:**
```json
{
  "name": {
    "ar": "اسم المتجر",
    "en": "Shop Name"
  },
  "description": {
    "ar": "وصف المتجر",
    "en": "Shop Description"
  },
  "address": {
    "ar": "العنوان",
    "en": "Address"
  },
  "lat": 30.0444,
  "lng": 31.2357,
  "phone": "0123456789",
  "mobile": "0123456789",
  "email": "shop@example.com",
  "working_hours": {
    "saturday": {
      "open": "09:00",
      "close": "18:00"
    },
    "sunday": {
      "open": "09:00",
      "close": "18:00"
    },
    "monday": {
      "closed": true
    }
  },
  "logo": file,
  "cover_images": [file1, file2],
  "is_active": true,
  "area_id": 1,
  "vendor_id": 1,
  "service_ids": [
    {"id": 1},
    {"id": 2}
  ]
}
```

**Validation Rules:**
- `name.ar`: required, string, max 255
- `name.en`: required, string, max 255
- `description.ar`: optional, string
- `description.en`: optional, string
- `address.ar`: required, string
- `address.en`: required, string
- `lat`: required, numeric, between -90 and 90
- `lng`: required, numeric, between -180 and 180
- `phone`: optional, string, max 20
- `mobile`: required, string, max 20
- `email`: optional, valid email, unique in stores table
- `working_hours`: required, array
- `working_hours.*.open`: required without closed, format H:i
- `working_hours.*.close`: required without closed, format H:i
- `working_hours.*.closed`: optional, boolean
- `logo`: optional, image file, max 2MB
- `cover_images.*`: optional, image file, max 2MB
- `is_active`: optional, boolean
- `area_id`: required, must exist in areas
- `vendor_id`: required, must exist in vendors
- `service_ids.*.id`: required, must exist in services

### D. Update Shop
**Endpoint:** `PUT /api/admin/shops/{id}`

**Headers:** `Authorization: Bearer {token}`

**Request Body:** Same as Create Shop

### E. Delete Shop
**Endpoint:** `DELETE /api/admin/shops/{id}`

**Headers:** `Authorization: Bearer {token}`


---

## 7. Categories Management

### A. List Categories
**Endpoint:** `GET /api/admin/categories`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:** Standard pagination and search parameters

### B. Get Single Category
**Endpoint:** `GET /api/admin/categories/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create Category
**Endpoint:** `POST /api/admin/categories`

**Headers:** `Authorization: Bearer {token}`, `Content-Type: multipart/form-data`

**Request Body:**
```json
{
  "name": {
    "ar": "اسم الفئة",
    "en": "Category Name"
  },
  "description": {
    "ar": "وصف الفئة",
    "en": "Category Description"
  },
  "icon": file,
  "parent_id": 1
}
```

**Validation Rules:**
- `name.{locale}`: required, string, max 255
- `description.{locale}`: optional, string
- `icon`: optional, file
- `parent_id`: optional, must exist in categories

### D. Update Category
**Endpoint:** `PUT /api/admin/categories/{id}`

**Headers:** `Authorization: Bearer {token}`

**Request Body:** Same as Create Category

### E. Delete Category
**Endpoint:** `DELETE /api/admin/categories/{id}`

**Headers:** `Authorization: Bearer {token}`

---

## 8. Brands Management

### A. List Brands
**Endpoint:** `GET /api/admin/brands`

**Headers:** `Authorization: Bearer {token}`

### B. Get Single Brand
**Endpoint:** `GET /api/admin/brands/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create Brand
**Endpoint:** `POST /api/admin/brands`

**Headers:** `Authorization: Bearer {token}`

### D. Update Brand
**Endpoint:** `PUT /api/admin/brands/{id}`

**Headers:** `Authorization: Bearer {token}`

### E. Delete Brand
**Endpoint:** `DELETE /api/admin/brands/{id}`

**Headers:** `Authorization: Bearer {token}`

---

## 9. Packages Management

### A. List Packages
**Endpoint:** `GET /api/admin/packages`

**Headers:** `Authorization: Bearer {token}`

### B. Get Single Package
**Endpoint:** `GET /api/admin/packages/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create Package
**Endpoint:** `POST /api/admin/packages`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "name": {
    "ar": "الباقة الذهبية",
    "en": "Gold Package"
  },
  "price": 299.99,
  "duration_days": 30,
  "monthly_orders_limit": 50,
  "free_delivery_count": 10,
  "discount_percentage": 15,
  "points_bonus": 500,
  "is_active": true
}
```

**Validation Rules:**
- `name.ar`: required, string, max 255
- `name.en`: required, string, max 255
- `price`: required, numeric, min 0
- `duration_days`: required, integer, min 1
- `monthly_orders_limit`: optional, integer, min 0
- `free_delivery_count`: required, integer, min 0
- `discount_percentage`: required, numeric, min 0, max 100
- `points_bonus`: required, integer, min 0
- `is_active`: required, boolean

### D. Update Package
**Endpoint:** `PUT /api/admin/packages/{id}`

**Headers:** `Authorization: Bearer {token}`

**Request Body:** Same as Create Package

### E. Delete Package
**Endpoint:** `DELETE /api/admin/packages/{id}`

**Headers:** `Authorization: Bearer {token}`


---

## 10. Subscriptions Management

### A. List Subscriptions
**Endpoint:** `GET /api/admin/subscriptions`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:** Standard pagination and search parameters

### B. Get Single Subscription
**Endpoint:** `GET /api/admin/subscriptions/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create Subscription
**Endpoint:** `POST /api/admin/subscriptions`

**Headers:** `Authorization: Bearer {token}`

### D. Update Subscription
**Endpoint:** `PUT /api/admin/subscriptions/{id}`

**Headers:** `Authorization: Bearer {token}`

### E. Delete Subscription
**Endpoint:** `DELETE /api/admin/subscriptions/{id}`

**Headers:** `Authorization: Bearer {token}`

---

## 11. Gifts Management

### A. List Gifts
**Endpoint:** `GET /api/admin/gifts`

**Headers:** `Authorization: Bearer {token}`

### B. Get Single Gift
**Endpoint:** `GET /api/admin/gifts/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create Gift
**Endpoint:** `POST /api/admin/gifts`

**Headers:** `Authorization: Bearer {token}`, `Content-Type: multipart/form-data`

**Request Body:**
```json
{
  "name": "Gift Card 100",
  "description": "100 SAR Gift Card",
  "image": file,
  "points_required": 1000,
  "stock_quantity": 50,
  "is_active": true,
  "category_id": 1,
  "terms_conditions": "Terms and conditions text"
}
```

**Validation Rules:**
- `name`: required, string, max 255
- `description`: optional, string
- `image`: optional, image file (jpeg, png, jpg, gif), max 2MB
- `points_required`: required, integer, min 1
- `stock_quantity`: optional, integer, min 0
- `is_active`: optional, boolean
- `category_id`: optional, must exist in categories
- `terms_conditions`: optional, string

### D. Update Gift
**Endpoint:** `PUT /api/admin/gifts/{id}`

**Headers:** `Authorization: Bearer {token}`

**Request Body:** Same as Create Gift

### E. Delete Gift
**Endpoint:** `DELETE /api/admin/gifts/{id}`

**Headers:** `Authorization: Bearer {token}`

---

## 12. Point Exchanges Management

### A. List Point Exchanges
**Endpoint:** `GET /api/admin/point-exchanges`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:** Standard pagination and search parameters

**Description:** View all point exchange requests from users

### B. Get Single Point Exchange
**Endpoint:** `GET /api/admin/point-exchanges/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Update Point Exchange Status
**Endpoint:** `PUT /api/admin/point-exchanges/{id}`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "status": "approved"
}
```

**Note:** Only index, show, and update operations are available for point exchanges


---

## 13. User Points Management

### A. List Users with Points
**Endpoint:** `GET /api/admin/user-points`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `search`: Search by user name or email
- `balance_min`: Filter by minimum balance
- `balance_max`: Filter by maximum balance
- `sortField`: Field to sort by (default: 'created_at')
- `sortOrder`: 'asc' or 'desc' (default: 'desc')
- `per_page`: Items per page (default: 20)

**Response:**
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "user_id": 1,
        "user_name": "John Doe",
        "user_email": "john@example.com",
        "balance": 1500,
        "total_earned": 2000,
        "total_redeemed": 500,
        "last_earned_at": "2026-02-20T10:00:00Z"
      }
    ],
    "pagination": {...}
  }
}
```

### B. Get User Point Details
**Endpoint:** `GET /api/admin/user-points/{userId}`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "wallet": {
      "balance": 1500,
      "total_earned": 2000,
      "total_redeemed": 500,
      "last_earned_at": "2026-02-20T10:00:00Z"
    },
    "recent_transactions": [...]
  }
}
```

### C. Get User Point Transactions
**Endpoint:** `GET /api/admin/user-points/{userId}/transactions`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `source`: Filter by transaction source
- `status`: Filter by status ('earned', 'redeemed')
- `date_from`: Filter from date
- `date_to`: Filter to date
- `sortField`: Field to sort by (default: 'created_at')
- `sortOrder`: 'asc' or 'desc' (default: 'desc')
- `per_page`: Items per page (default: 20)

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "items": [
      {
        "id": 1,
        "points": 100,
        "status": "earned",
        "source": "order_completion",
        "reason": "Order #123 completed",
        "created_at": "2026-02-20T10:00:00Z"
      }
    ],
    "pagination": {...}
  }
}
```

### D. Add Points to User
**Endpoint:** `POST /api/admin/user-points/{userId}/add`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "points": 500,
  "reason": "Promotional bonus for loyal customer"
}
```

**Validation Rules:**
- `points`: required, integer, min 1
- `reason`: required, string, max 500

**Response:**
```json
{
  "success": true,
  "message": "Points added successfully",
  "data": {
    "transaction": {
      "id": 1,
      "points": 500,
      "status": "earned",
      "source": "admin_adjustment",
      "reason": "Promotional bonus for loyal customer"
    },
    "new_balance": 2000
  }
}
```

### E. Deduct Points from User
**Endpoint:** `POST /api/admin/user-points/{userId}/deduct`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "points": 200,
  "reason": "Correction for duplicate transaction"
}
```

**Validation Rules:**
- `points`: required, integer, min 1
- `reason`: required, string, max 500

**Response:**
```json
{
  "success": true,
  "message": "Points deducted successfully",
  "data": {
    "transaction": {
      "id": 2,
      "points": -200,
      "status": "redeemed",
      "source": "admin_adjustment",
      "reason": "Correction for duplicate transaction"
    },
    "new_balance": 1800
  }
}
```

**Error Response (Insufficient Balance):**
```json
{
  "success": false,
  "message": "Insufficient points balance"
}
```

### F. Get Point Statistics
**Endpoint:** `GET /api/admin/user-points/statistics`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "total_users_with_points": 150,
    "total_points_in_system": 45000,
    "total_points_earned": 60000,
    "total_points_redeemed": 15000,
    "total_transactions": 500,
    "average_balance_per_user": 300
  }
}
```


---

## 14. Currency Management

### A. List Currencies
**Endpoint:** `GET /api/admin/currencies`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:** Standard pagination and search parameters

**Response:**
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "code": "USD",
        "name": {"ar": "دولار أمريكي", "en": "US Dollar"},
        "symbol": "$",
        "exchange_rate": 1.0,
        "is_default": true,
        "is_active": true
      }
    ],
    "pagination": {...}
  }
}
```

### B. Get Single Currency
**Endpoint:** `GET /api/admin/currencies/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create Currency
**Endpoint:** `POST /api/admin/currencies`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "code": "EUR",
  "name": {
    "en": "Euro",
    "ar": "يورو"
  },
  "symbol": "€",
  "exchange_rate": 0.85,
  "is_default": false,
  "is_active": true
}
```

**Validation Rules:**
- `code`: required, string, exactly 3 characters, unique
- `name.en`: required, string, max 100
- `name.ar`: required, string, max 100
- `symbol`: required, string, max 10
- `exchange_rate`: required, numeric, min 0.000001
- `is_default`: optional, boolean
- `is_active`: optional, boolean

### D. Update Currency
**Endpoint:** `PUT /api/admin/currencies/{id}`

**Headers:** `Authorization: Bearer {token}`

**Request Body:** Same as Create Currency (code must be unique except for current record)

### E. Delete Currency
**Endpoint:** `DELETE /api/admin/currencies/{id}`

**Headers:** `Authorization: Bearer {token}`

### F. Toggle Currency Status
**Endpoint:** `PATCH /api/admin/currencies/{id}/toggle-status`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "is_active": false
  }
}
```

---

## 15. User Basket Schedules (Read-Only)

### A. List User Basket Schedules
**Endpoint:** `GET /api/admin/user-basket-schedules`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:** Standard pagination and search parameters

**Description:** View all basket schedules created by users (read-only access)

**Response:**
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "user": {...},
        "basket": {...},
        "delivery_date": "2026-03-01",
        "status": "active"
      }
    ],
    "pagination": {...}
  }
}
```

### B. Get Single User Basket Schedule
**Endpoint:** `GET /api/admin/user-basket-schedules/{id}`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user": {...},
    "basket": {...},
    "delivery_date": "2026-03-01",
    "delivery_time": "10:00-12:00",
    "status": "active",
    "items": [...]
  }
}
```

**Note:** This resource is read-only. No create, update, or delete operations are available.


---

## 16. Roles & Permissions

### A. List Roles
**Endpoint:** `GET /api/admin/roles`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:** Standard pagination and search parameters

### B. Get Single Role
**Endpoint:** `GET /api/admin/roles/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create Role
**Endpoint:** `POST /api/admin/roles`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "name": "Manager",
  "permissions": [1, 2, 3, 4]
}
```

### D. Update Role
**Endpoint:** `PUT /api/admin/roles/{id}`

**Headers:** `Authorization: Bearer {token}`

### E. Delete Role
**Endpoint:** `DELETE /api/admin/roles/{id}`

**Headers:** `Authorization: Bearer {token}`

### F. List All Permissions
**Endpoint:** `GET /api/admin/permissions`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "view_products",
      "display_name": "View Products"
    },
    {
      "id": 2,
      "name": "create_products",
      "display_name": "Create Products"
    }
  ]
}
```

---

## 17. Admins Management

### A. List Admins
**Endpoint:** `GET /api/admin/admins`

**Headers:** `Authorization: Bearer {token}`

### B. Get Single Admin
**Endpoint:** `GET /api/admin/admins/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create Admin
**Endpoint:** `POST /api/admin/admins`

**Headers:** `Authorization: Bearer {token}`

### D. Update Admin
**Endpoint:** `PUT /api/admin/admins/{id}`

**Headers:** `Authorization: Bearer {token}`

### E. Delete Admin
**Endpoint:** `DELETE /api/admin/admins/{id}`

**Headers:** `Authorization: Bearer {token}`

---

## 18. Drivers Management

### A. List Drivers
**Endpoint:** `GET /api/admin/drivers`

**Headers:** `Authorization: Bearer {token}`

### B. Get Single Driver
**Endpoint:** `GET /api/admin/drivers/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create Driver
**Endpoint:** `POST /api/admin/drivers`

**Headers:** `Authorization: Bearer {token}`

### D. Update Driver
**Endpoint:** `PUT /api/admin/drivers/{id}`

**Headers:** `Authorization: Bearer {token}`

### E. Delete Driver
**Endpoint:** `DELETE /api/admin/drivers/{id}`

**Headers:** `Authorization: Bearer {token}`

---

## 19. Users Management

### A. List Users
**Endpoint:** `GET /api/admin/users`

**Headers:** `Authorization: Bearer {token}`

### B. Get Single User
**Endpoint:** `GET /api/admin/users/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create User
**Endpoint:** `POST /api/admin/users`

**Headers:** `Authorization: Bearer {token}`

### D. Update User
**Endpoint:** `PUT /api/admin/users/{id}`

**Headers:** `Authorization: Bearer {token}`

### E. Delete User
**Endpoint:** `DELETE /api/admin/users/{id}`

**Headers:** `Authorization: Bearer {token}`


---

## 20. Vendors Management

### A. List Vendors
**Endpoint:** `GET /api/admin/vendors`

**Headers:** `Authorization: Bearer {token}`

### B. Get Single Vendor
**Endpoint:** `GET /api/admin/vendors/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create Vendor
**Endpoint:** `POST /api/admin/vendors`

**Headers:** `Authorization: Bearer {token}`

### D. Update Vendor
**Endpoint:** `PUT /api/admin/vendors/{id}`

**Headers:** `Authorization: Bearer {token}`

### E. Delete Vendor
**Endpoint:** `DELETE /api/admin/vendors/{id}`

**Headers:** `Authorization: Bearer {token}`

---

## 21. Stores Management

### A. List Stores
**Endpoint:** `GET /api/admin/stores`

**Headers:** `Authorization: Bearer {token}`

### B. Get Single Store
**Endpoint:** `GET /api/admin/stores/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create Store
**Endpoint:** `POST /api/admin/stores`

**Headers:** `Authorization: Bearer {token}`

### D. Update Store
**Endpoint:** `PUT /api/admin/stores/{id}`

**Headers:** `Authorization: Bearer {token}`

### E. Delete Store
**Endpoint:** `DELETE /api/admin/stores/{id}`

**Headers:** `Authorization: Bearer {token}`

---

## 22. Governorates, Cities & Areas

### Governorates

#### A. List Governorates
**Endpoint:** `GET /api/admin/governorates`

**Headers:** `Authorization: Bearer {token}`

#### B. Get Single Governorate
**Endpoint:** `GET /api/admin/governorates/{id}`

**Headers:** `Authorization: Bearer {token}`

#### C. Create Governorate
**Endpoint:** `POST /api/admin/governorates`

**Headers:** `Authorization: Bearer {token}`

#### D. Update Governorate
**Endpoint:** `PUT /api/admin/governorates/{id}`

**Headers:** `Authorization: Bearer {token}`

#### E. Delete Governorate
**Endpoint:** `DELETE /api/admin/governorates/{id}`

**Headers:** `Authorization: Bearer {token}`

### Cities

#### A. List Cities
**Endpoint:** `GET /api/admin/cities`

**Headers:** `Authorization: Bearer {token}`

#### B. Get Single City
**Endpoint:** `GET /api/admin/cities/{id}`

**Headers:** `Authorization: Bearer {token}`

#### C. Create City
**Endpoint:** `POST /api/admin/cities`

**Headers:** `Authorization: Bearer {token}`

#### D. Update City
**Endpoint:** `PUT /api/admin/cities/{id}`

**Headers:** `Authorization: Bearer {token}`

#### E. Delete City
**Endpoint:** `DELETE /api/admin/cities/{id}`

**Headers:** `Authorization: Bearer {token}`

### Areas

#### A. List Areas
**Endpoint:** `GET /api/admin/areas`

**Headers:** `Authorization: Bearer {token}`

#### B. Get Single Area
**Endpoint:** `GET /api/admin/areas/{id}`

**Headers:** `Authorization: Bearer {token}`

#### C. Create Area
**Endpoint:** `POST /api/admin/areas`

**Headers:** `Authorization: Bearer {token}`

#### D. Update Area
**Endpoint:** `PUT /api/admin/areas/{id}`

**Headers:** `Authorization: Bearer {token}`

#### E. Delete Area
**Endpoint:** `DELETE /api/admin/areas/{id}`

**Headers:** `Authorization: Bearer {token}`


---

## 23. Services Management

### A. List Services
**Endpoint:** `GET /api/admin/services`

**Headers:** `Authorization: Bearer {token}`

### B. Get Single Service
**Endpoint:** `GET /api/admin/services/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create Service
**Endpoint:** `POST /api/admin/services`

**Headers:** `Authorization: Bearer {token}`

### D. Update Service
**Endpoint:** `PUT /api/admin/services/{id}`

**Headers:** `Authorization: Bearer {token}`

### E. Delete Service
**Endpoint:** `DELETE /api/admin/services/{id}`

**Headers:** `Authorization: Bearer {token}`

---

## 24. Sections Management

### A. List Sections
**Endpoint:** `GET /api/admin/sections`

**Headers:** `Authorization: Bearer {token}`

### B. Get Single Section
**Endpoint:** `GET /api/admin/sections/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create Section
**Endpoint:** `POST /api/admin/sections`

**Headers:** `Authorization: Bearer {token}`

### D. Update Section
**Endpoint:** `PUT /api/admin/sections/{id}`

**Headers:** `Authorization: Bearer {token}`

### E. Delete Section
**Endpoint:** `DELETE /api/admin/sections/{id}`

**Headers:** `Authorization: Bearer {token}`

### F. Get Pages
**Endpoint:** `GET /api/admin/sections/pages`

**Description:** Get list of available pages for sections

**Response:**
```json
{
  "success": true,
  "data": [
    {"id": 1, "name": "Home"},
    {"id": 2, "name": "Products"}
  ]
}
```

### G. Get Section Item Types
**Endpoint:** `GET /api/admin/sections/item-types`

**Description:** Get list of available section item types

**Response:**
```json
{
  "success": true,
  "data": [
    {"id": 1, "name": "Product"},
    {"id": 2, "name": "Category"}
  ]
}
```

### H. Get Display Types
**Endpoint:** `GET /api/admin/sections/display-types`

**Description:** Get list of available display types for sections

**Response:**
```json
{
  "success": true,
  "data": [
    {"id": 1, "name": "Grid"},
    {"id": 2, "name": "Slider"}
  ]
}
```

---

## 25. Page Sections Management

### A. List Page Sections
**Endpoint:** `GET /api/admin/page-sections`

**Headers:** `Authorization: Bearer {token}`

### B. Get Single Page Section
**Endpoint:** `GET /api/admin/page-sections/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create Page Section
**Endpoint:** `POST /api/admin/page-sections`

**Headers:** `Authorization: Bearer {token}`

### D. Update Page Section
**Endpoint:** `PUT /api/admin/page-sections/{id}`

**Headers:** `Authorization: Bearer {token}`

### E. Delete Page Section
**Endpoint:** `DELETE /api/admin/page-sections/{id}`

**Headers:** `Authorization: Bearer {token}`

---

## 26. Banners Management

### A. List Banners
**Endpoint:** `GET /api/admin/banners`

**Headers:** `Authorization: Bearer {token}`

### B. Get Single Banner
**Endpoint:** `GET /api/admin/banners/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create Banner
**Endpoint:** `POST /api/admin/banners`

**Headers:** `Authorization: Bearer {token}`, `Content-Type: multipart/form-data`

### D. Update Banner
**Endpoint:** `PUT /api/admin/banners/{id}`

**Headers:** `Authorization: Bearer {token}`

### E. Delete Banner
**Endpoint:** `DELETE /api/admin/banners/{id}`

**Headers:** `Authorization: Bearer {token}`


---

## 27. Coupons Management

### A. List Coupons
**Endpoint:** `GET /api/admin/coupons`

**Headers:** `Authorization: Bearer {token}`

### B. Get Single Coupon
**Endpoint:** `GET /api/admin/coupons/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create Coupon
**Endpoint:** `POST /api/admin/coupons`

**Headers:** `Authorization: Bearer {token}`

### D. Update Coupon
**Endpoint:** `PUT /api/admin/coupons/{id}`

**Headers:** `Authorization: Bearer {token}`

### E. Delete Coupon
**Endpoint:** `DELETE /api/admin/coupons/{id}`

**Headers:** `Authorization: Bearer {token}`

---

## 28. Complaints Management

### A. List Complaints
**Endpoint:** `GET /api/admin/complaints`

**Headers:** `Authorization: Bearer {token}`

### B. Get Single Complaint
**Endpoint:** `GET /api/admin/complaints/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create Complaint
**Endpoint:** `POST /api/admin/complaints`

**Headers:** `Authorization: Bearer {token}`

### D. Update Complaint
**Endpoint:** `PUT /api/admin/complaints/{id}`

**Headers:** `Authorization: Bearer {token}`

### E. Delete Complaint
**Endpoint:** `DELETE /api/admin/complaints/{id}`

**Headers:** `Authorization: Bearer {token}`

---

## 29. Recipes Management

### A. List Recipes
**Endpoint:** `GET /api/admin/recipes`

**Headers:** `Authorization: Bearer {token}`

### B. Get Single Recipe
**Endpoint:** `GET /api/admin/recipes/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create Recipe
**Endpoint:** `POST /api/admin/recipes`

**Headers:** `Authorization: Bearer {token}`

### D. Update Recipe
**Endpoint:** `PUT /api/admin/recipes/{id}`

**Headers:** `Authorization: Bearer {token}`

### E. Delete Recipe
**Endpoint:** `DELETE /api/admin/recipes/{id}`

**Headers:** `Authorization: Bearer {token}`

---

## 30. Languages Management

### A. List Languages
**Endpoint:** `GET /api/admin/languages`

**Headers:** `Authorization: Bearer {token}`

### B. Get Single Language
**Endpoint:** `GET /api/admin/languages/{id}`

**Headers:** `Authorization: Bearer {token}`

### C. Create Language
**Endpoint:** `POST /api/admin/languages`

**Headers:** `Authorization: Bearer {token}`

### D. Update Language
**Endpoint:** `PUT /api/admin/languages/{id}`

**Headers:** `Authorization: Bearer {token}`

### E. Delete Language
**Endpoint:** `DELETE /api/admin/languages/{id}`

**Headers:** `Authorization: Bearer {token}`

---

## 31. Category Attributes & Details

### Category Attributes

#### A. List Category Attributes
**Endpoint:** `GET /api/admin/category-attributes`

**Headers:** `Authorization: Bearer {token}`

#### B. Get Single Category Attribute
**Endpoint:** `GET /api/admin/category-attributes/{id}`

**Headers:** `Authorization: Bearer {token}`

#### C. Create Category Attribute
**Endpoint:** `POST /api/admin/category-attributes`

**Headers:** `Authorization: Bearer {token}`

#### D. Update Category Attribute
**Endpoint:** `PUT /api/admin/category-attributes/{id}`

**Headers:** `Authorization: Bearer {token}`

#### E. Delete Category Attribute
**Endpoint:** `DELETE /api/admin/category-attributes/{id}`

**Headers:** `Authorization: Bearer {token}`

### Category Details

#### A. List Category Details
**Endpoint:** `GET /api/admin/category-details`

**Headers:** `Authorization: Bearer {token}`

#### B. Get Single Category Detail
**Endpoint:** `GET /api/admin/category-details/{id}`

**Headers:** `Authorization: Bearer {token}`

#### C. Create Category Detail
**Endpoint:** `POST /api/admin/category-details`

**Headers:** `Authorization: Bearer {token}`

#### D. Update Category Detail
**Endpoint:** `PUT /api/admin/category-details/{id}`

**Headers:** `Authorization: Bearer {token}`

#### E. Delete Category Detail
**Endpoint:** `DELETE /api/admin/category-details/{id}`

**Headers:** `Authorization: Bearer {token}`


---

## Common Workflows

### Workflow 1: Create a Complete Product (Detailed)

This workflow shows the complete process of creating a product with variants from scratch.

---

#### Scenario: Create "Cotton T-Shirt" with Color and Size variants

**Step 1: Get Active Languages**
```http
GET /api/admin/languages
```

**Response:**
```json
{
  "data": {
    "items": [
      {"id": 1, "code": "ar", "name": "العربية"},
      {"id": 2, "code": "en", "name": "English"}
    ]
  }
}
```
**Action:** Store language codes: `["ar", "en"]`

---

**Step 2: Select Category**
```http
GET /api/admin/categories
```

**Response:**
```json
{
  "data": {
    "items": [
      {"id": 1, "name": {"ar": "خضروات", "en": "Vegetables"}},
      {"id": 2, "name": {"ar": "ملابس", "en": "Clothing"}},
      {"id": 3, "name": {"ar": "إلكترونيات", "en": "Electronics"}}
    ]
  }
}
```
**Action:** User selects "Clothing" → `category_id = 2`

---

**Step 3: Get Category Attributes (for variants)**
```http
GET /api/admin/category-attributes?category_id=2
```

**Response:**
```json
{
  "data": {
    "items": [
      {
        "id": 1,
        "name": {"ar": "اللون", "en": "Color"},
        "values": [
          {"id": 1, "value": {"ar": "أحمر", "en": "Red"}},
          {"id": 2, "value": {"ar": "أزرق", "en": "Blue"}},
          {"id": 3, "value": {"ar": "أخضر", "en": "Green"}}
        ]
      },
      {
        "id": 2,
        "name": {"ar": "المقاس", "en": "Size"},
        "values": [
          {"id": 4, "value": {"ar": "صغير", "en": "Small"}},
          {"id": 5, "value": {"ar": "متوسط", "en": "Medium"}},
          {"id": 6, "value": {"ar": "كبير", "en": "Large"}}
        ]
      }
    ]
  }
}
```

**Action:** User selects:
- Colors: Red (1), Blue (2)
- Sizes: Small (4), Large (6)

**Frontend generates variants:**
```javascript
const selectedColors = [1, 2];  // Red, Blue
const selectedSizes = [4, 6];   // Small, Large

const variants = [];
selectedColors.forEach(color => {
  selectedSizes.forEach(size => {
    variants.push({
      attributes_values_ids: [color, size],
      images: []  // User can upload variant-specific images
    });
  });
});

// Result: 4 variants
// [
//   {attributes_values_ids: [1, 4]},  // Red + Small
//   {attributes_values_ids: [1, 6]},  // Red + Large
//   {attributes_values_ids: [2, 4]},  // Blue + Small
//   {attributes_values_ids: [2, 6]}   // Blue + Large
// ]
```

---

**Step 4: Get Category Details (optional fields)**
```http
GET /api/admin/category-details?category_id=2
```

**Response:**
```json
{
  "data": {
    "items": [
      {
        "id": 1,
        "name": {"ar": "المادة", "en": "Material"},
        "category_id": 2
      },
      {
        "id": 2,
        "name": {"ar": "العناية", "en": "Care Instructions"},
        "category_id": 2
      }
    ]
  }
}
```

**Action:** User fills category details:
```json
[
  {
    "category_detail_id": 1,
    "detail_value": {
      "ar": "قطن 100%",
      "en": "100% Cotton"
    }
  },
  {
    "category_detail_id": 2,
    "detail_value": {
      "ar": "غسيل على 30 درجة",
      "en": "Wash at 30°C"
    }
  }
]
```

---

**Step 5: Get Available Shops**
```http
GET /api/admin/shops
```

**Response:**
```json
{
  "data": {
    "items": [
      {
        "id": 1,
        "name": {"ar": "متجر القاهرة", "en": "Cairo Shop"},
        "is_active": true
      },
      {
        "id": 2,
        "name": {"ar": "متجر الإسكندرية", "en": "Alexandria Shop"},
        "is_active": true
      }
    ]
  }
}
```

**Action:** User selects both shops and sets prices/quantities:

**Frontend generates shop_variants:**
```javascript
const selectedShops = [1, 2];
const shop_variants = [];

variants.forEach((variant, variantIndex) => {
  selectedShops.forEach(shopId => {
    shop_variants.push({
      shop_id: shopId,
      variant_index: variantIndex,
      price: shopId === 1 ? 100 : 95,      // Different prices per shop
      quantity: shopId === 1 ? 50 : 100    // Different quantities per shop
    });
  });
});

// Result: 8 shop_variants (4 variants × 2 shops)
```

---

**Step 6: Get Products for "Bought With" (optional)**
```http
GET /api/admin/products?category_id=2&search=pants
```

**Response:**
```json
{
  "data": {
    "items": [
      {"id": 10, "name": {"ar": "بنطلون جينز", "en": "Jeans Pants"}},
      {"id": 11, "name": {"ar": "حزام جلد", "en": "Leather Belt"}}
    ]
  }
}
```

**Action:** User selects products: `bought_with = [10, 11]`

---

**Step 7: Create the Product**
```http
POST /api/admin/products
Content-Type: multipart/form-data
```

**Complete Request Body:**
```json
{
  "category_id": 2,
  "sku": "TSHIRT-001",
  "price": 100,
  "price_after_discount": 85,
  "quantity": 200,
  "time_prepare": "1 day",
  "is_instant_delivery": false,
  
  "name": {
    "ar": "قميص قطني",
    "en": "Cotton T-Shirt"
  },
  "description": {
    "ar": "قميص قطني مريح للاستخدام اليومي",
    "en": "Comfortable cotton t-shirt for daily use"
  },
  "full_description": {
    "ar": "قميص مصنوع من قطن عالي الجودة، مناسب لجميع الأوقات",
    "en": "Made from high-quality cotton, suitable for all occasions"
  },
  "country": {
    "ar": "مصر",
    "en": "Egypt"
  },
  
  "images": [file1, file2, file3],
  
  "variants": [
    {
      "attributes_values_ids": [1, 4],
      "images": [redSmallFile]
    },
    {
      "attributes_values_ids": [1, 6],
      "images": [redLargeFile]
    },
    {
      "attributes_values_ids": [2, 4],
      "images": [blueSmallFile]
    },
    {
      "attributes_values_ids": [2, 6],
      "images": [blueLargeFile]
    }
  ],
  
  "category_details": [
    {
      "category_detail_id": 1,
      "detail_value": {
        "ar": "قطن 100%",
        "en": "100% Cotton"
      }
    },
    {
      "category_detail_id": 2,
      "detail_value": {
        "ar": "غسيل على 30 درجة",
        "en": "Wash at 30°C"
      }
    }
  ],
  
  "extra_details": [
    {
      "detail_key": {
        "ar": "العلامة التجارية",
        "en": "Brand"
      },
      "detail_value": {
        "ar": "علامة محلية",
        "en": "Local Brand"
      }
    }
  ],
  
  "shop_variants": [
    {"shop_id": 1, "variant_index": 0, "price": 100, "quantity": 50},
    {"shop_id": 1, "variant_index": 1, "price": 120, "quantity": 40},
    {"shop_id": 1, "variant_index": 2, "price": 100, "quantity": 30},
    {"shop_id": 1, "variant_index": 3, "price": 120, "quantity": 35},
    {"shop_id": 2, "variant_index": 0, "price": 95, "quantity": 100},
    {"shop_id": 2, "variant_index": 1, "price": 115, "quantity": 80},
    {"shop_id": 2, "variant_index": 2, "price": 95, "quantity": 60},
    {"shop_id": 2, "variant_index": 3, "price": 115, "quantity": 70}
  ],
  
  "bought_with": [10, 11]
}
```

**Response (Success):**
```json
{
  "success": true,
  "data": {
    "id": 15,
    "category_id": 2,
    "sku": "TSHIRT-001",
    "price": 100,
    "price_after_discount": 85,
    "name": {
      "ar": "قميص قطني",
      "en": "Cotton T-Shirt"
    },
    "images": [
      {"id": 1, "url": "https://example.com/storage/products/1.jpg"},
      {"id": 2, "url": "https://example.com/storage/products/2.jpg"}
    ],
    "variants": [
      {
        "id": 1,
        "attributes": [
          {"name": {"ar": "اللون", "en": "Color"}, "value": {"ar": "أحمر", "en": "Red"}},
          {"name": {"ar": "المقاس", "en": "Size"}, "value": {"ar": "صغير", "en": "Small"}}
        ],
        "images": [
          {"id": 3, "url": "https://example.com/storage/variants/1.jpg"}
        ]
      }
    ],
    "shop_variants": [
      {
        "id": 1,
        "shop_id": 1,
        "shop_name": {"ar": "متجر القاهرة", "en": "Cairo Shop"},
        "variant_id": 1,
        "price": 100,
        "quantity": 50
      }
    ],
    "category_details": [
      {
        "name": {"ar": "المادة", "en": "Material"},
        "value": {"ar": "قطن 100%", "en": "100% Cotton"}
      }
    ],
    "bought_with": [
      {"id": 10, "name": {"ar": "بنطلون جينز", "en": "Jeans Pants"}},
      {"id": 11, "name": {"ar": "حزام جلد", "en": "Leather Belt"}}
    ],
    "created_at": "2026-02-21T10:00:00Z"
  }
}
```

---

#### Summary of Data Flow

```
1. Languages → Get locale codes for multilingual fields
2. Categories → Select category_id
3. Category Attributes → Build variants array
4. Category Details → Fill category-specific fields
5. Shops → Build shop_variants array
6. Products → Select bought_with products
7. Submit → Create product with all data
```

---

### Workflow 2: Manage an Order

**Step 1:** List orders with filters
```
GET /api/admin/orders?status=pending&page=1&per_page=20
```

**Step 2:** View order details
```
GET /api/admin/orders/{orderId}
```

**Step 3:** Change order status
```
PATCH /api/admin/orders/{orderId}/change-status
Body: {"status": "preparing"}
```

**Step 4:** Assign driver to order
```
POST /api/admin/orders/{orderId}/assign-driver
Body: {"driver_id": 5}
```

**Step 5:** Update individual item status (if needed)
```
PATCH /api/admin/orders/items/{itemId}/change-status
Body: {"status": "preparing"}
```

---

### Workflow 3: Create a Basket

**Step 1:** Get available categories
```
GET /api/admin/categories
```

**Step 2:** Get products for the basket
```
GET /api/admin/products?category_id={categoryId}
```

**Step 3:** Get shop product variants
```
GET /api/admin/products/{productId}
```
From the response, extract `shop_variants` with their IDs

**Step 4:** Create the basket
```
POST /api/admin/baskets
```
Send basket data with:
- Category ID
- Name (multilingual)
- Discount details
- Items array with shop_product_variant_id and quantity

---

### Workflow 4: Manage User Points

**Step 1:** View all users with points
```
GET /api/admin/user-points?search=john&balance_min=100
```

**Step 2:** View specific user's point details
```
GET /api/admin/user-points/{userId}
```

**Step 3:** View user's transaction history
```
GET /api/admin/user-points/{userId}/transactions?status=earned
```

**Step 4:** Add points to user (bonus/adjustment)
```
POST /api/admin/user-points/{userId}/add
Body: {
  "points": 500,
  "reason": "Promotional bonus"
}
```

**Step 5:** Deduct points from user (correction)
```
POST /api/admin/user-points/{userId}/deduct
Body: {
  "points": 100,
  "reason": "Correction for error"
}
```

**Step 6:** View overall point statistics
```
GET /api/admin/user-points/statistics
```

---

### Workflow 5: Create a Shop

**Step 1:** Get available areas
```
GET /api/admin/areas
```

**Step 2:** Get available vendors
```
GET /api/admin/vendors
```

**Step 3:** Get available services
```
GET /api/admin/services
```

**Step 4:** Create the shop
```
POST /api/admin/shops
```
Send shop data with:
- Name and description (multilingual)
- Address and coordinates
- Contact information
- Working hours
- Area ID
- Vendor ID
- Service IDs
- Logo and cover images

---

### Workflow 6: Manage Currencies

**Step 1:** List all currencies
```
GET /api/admin/currencies
```

**Step 
"الباقة الذهبية", "en": "Gold Package"},
  "price": 299.99,
  "duration_days": 30,
  "monthly_orders_limit": 50,
  "free_delivery_count": 10,
  "discount_percentage": 15,
  "points_bonus": 500,
  "is_active": true
}
```

**Step 2:** View all packages
```
GET /api/admin/packages
```

**Step 3:** Update package details
```
PUT /api/admin/packages/{id}
```

---

### Workflow 8: Manage Point Exchanges

**Step 1:** View all point exchange requests
```
GET /api/admin/point-exchanges?status=pending
```

**Step 2:** View specific exchange request details
```
GET /api/admin/point-exchanges/{id}
```

**Step 3:** Approve or reject the exchange
```
PUT /api/admin/point-exchanges/{id}
Body: {
  "status": "approved"
}
```

---

## Error Responses

All endpoints follow a consistent error response format:

### Validation Error (422)
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password field is required."]
  }
}
```

### Unauthorized (401)
```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

### Forbidden (403)
```json
{
  "success": false,
  "message": "This action is unauthorized."
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Resource not found."
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "An error occurred while processing your request."
}
```

---

## Notes

1. **Authentication:** All endpoints except login require the `Authorization: Bearer {token}` header
2. **Multilingual Fields:** Fields like `name`, `description` should be sent as objects with locale keys (ar, en)
3. **File Uploads:** Use `multipart/form-data` content type when uploading files
4. **Pagination:** Most list endpoints return paginated results with metadata
5. **Soft Deletes:** Most resources use soft deletes, so deleted items can be restored
6. **Timestamps:** All timestamps are in ISO 8601 format (UTC)
7. **CRUD Pattern:** Most resources follow the standard CRUD pattern (index, show, store, update, destroy)

---

## API Testing Tips

1. Use Postman or similar tools to test the APIs
2. Start with authentication to get your access token
3. Store the token in your environment variables
4. Test list endpoints before create/update operations
5. Check validation rules before sending requests
6. Use proper content types for file uploads
7. Monitor response status codes and error messages

---

**Document Version:** 1.0  
**Last Updated:** February 21, 2026  
**Base URL:** `/api/admin`


---

## Reference Data APIs (Dropdown Lists)

This section explains which API to call to get the list of items when you need to select a value for fields ending with `_id`.

### Field: `category_id`
**API to get list:** `GET /api/admin/categories`
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": {"ar": "خضروات", "en": "Vegetables"}
      }
    ]
  }
}
```
**Used in:** Products, Baskets, Scheduled Baskets, Gifts

---

### Field: `shop_id`
**API to get list:** `GET /api/admin/shops`
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": {"ar": "متجر الخضار", "en": "Vegetable Shop"},
        "is_active": true
      }
    ]
  }
}
```
**Used in:** Products (shop_variants), Orders

---

### Field: `vendor_id`
**API to get list:** `GET /api/admin/vendors`
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": "Vendor Name",
        "email": "vendor@example.com"
      }
    ]
  }
}
```
**Used in:** Shops, Stores

---

### Field: `area_id`
**API to get list:** `GET /api/admin/areas`
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": {"ar": "المعادي", "en": "Maadi"},
        "city": {...}
      }
    ]
  }
}
```
**Used in:** Shops, Stores, User Addresses

---

### Field: `city_id`
**API to get list:** `GET /api/admin/cities`
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": {"ar": "القاهرة", "en": "Cairo"},
        "governorate": {...}
      }
    ]
  }
}
```
**Used in:** Areas

---

### Field: `governorate_id`
**API to get list:** `GET /api/admin/governorates`
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": {"ar": "القاهرة", "en": "Cairo"}
      }
    ]
  }
}
```
**Used in:** Cities

---

### Field: `driver_id`
**API to get list:** `GET /api/admin/drivers`
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": "Driver Name",
        "phone": "0123456789",
        "is_available": true
      }
    ]
  }
}
```
**Used in:** Orders (assign driver)

---

### Field: `service_ids` (array)
**API to get list:** `GET /api/admin/services`
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": {"ar": "توصيل سريع", "en": "Fast Delivery"}
      }
    ]
  }
}
```
**Used in:** Shops

---

### Field: `role_id` or `roles` (array)
**API to get list:** `GET /api/admin/roles`
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": "Manager",
        "permissions": [...]
      }
    ]
  }
}
```
**Used in:** Admins, Users

---

### Field: `permissions` (array)
**API to get list:** `GET /api/admin/permissions`
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "view_products",
      "display_name": "View Products"
    }
  ]
}
```
**Used in:** Roles

---

### Field: `brand_id`
**API to get list:** `GET /api/admin/brands`
```json
{
  "success":
       "id": 10,
        "shop_id": 1,
        "variant_index": 0,
        "price": 100,
        "quantity": 50
      }
    ]
  }
}
```
**Used in:** Baskets (items), Scheduled Baskets (items)

---

### Field: `category_detail_id`
**API to get list:** `GET /api/admin/category-details?category_id={categoryId}`
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": {"ar": "الوزن", "en": "Weight"},
        "category_id": 1
      }
    ]
  }
}
```
**Used in:** Products (category_details)

---

### Field: `category_attribute_id`
**API to get list:** `GET /api/admin/category-attributes?category_id={categoryId}`
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": {"ar": "اللون", "en": "Color"},
        "values": [
          {"id": 1, "value": {"ar": "أحمر", "en": "Red"}},
          {"id": 2, "value": {"ar": "أزرق", "en": "Blue"}}
        ]
      }
    ]
  }
}
```
**Used in:** Products (variants)

---

### Field: `attributes_values_ids` (array)
**Step 1:** Get category attributes
```
GET /api/admin/category-attributes?category_id={categoryId}
```

**Step 2:** From the response, extract the `values` array from each attribute
```json
{
  "id": 1,
  "name": {"ar": "اللون", "en": "Color"},
  "values": [
    {"id": 1, "value": {"ar": "أحمر", "en": "Red"}},
    {"id": 2, "value": {"ar": "أزرق", "en": "Blue"}}
  ]
}
```
**Used in:** Products (variants.attributes_values_ids)

---

### Field: `bought_with` (array of product IDs)
**API to get list:** `GET /api/admin/products`
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": {"ar": "منتج", "en": "Product"}
      }
    ]
  }
}
```
**Used in:** Products (frequently bought together)

---

### Field: `user_id`
**API to get list:** `GET /api/admin/users`
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": "User Name",
        "email": "user@example.com"
      }
    ]
  }
}
```
**Used in:** User Points Management, Orders

---

### Field: `package_id`
**API to get list:** `GET /api/admin/packages`
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": {"ar": "الباقة الذهبية", "en": "Gold Package"},
        "price": 299.99,
        "is_active": true
      }
    ]
  }
}
```
**Used in:** Subscriptions

---

### Field: `gift_id`
**API to get list:** `GET /api/admin/gifts`
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": "Gift Card 100",
        "points_required": 1000,
        "is_active": true
      }
    ]
  }
}
```
**Used in:** Point Exchanges

---

### Field: `basket_id`
**API to get list:** `GET /api/admin/baskets`
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": {"ar": "سلة الخضروات", "en": "Vegetables Basket"},
        "category": {...}
      }
    ]
  }
}
```
**Used in:** User Basket Schedules

---

### Field: `scheduled_basket_id`
**API to get list:** `GET /api/admin/scheduled-baskets`
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": {"ar": "سلة أسبوعية", "en": "Weekly Basket"}
      }
    ]
  }
}
```
**Used in:** User Basket Schedules

---

### Field: `language_code` or `locale`
**API to get list:** `GET /api/admin/languages`
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "code": "ar",
        "name": "العربية",
        "is_active": true
      },
      {
        "id": 2,
        "code": "en",
        "name": "English",
        "is_active": true
      }
    ]
  }
}
```
**Used in:** All multilingual fields

---

### Field: `currency_id` or `currency_code`
**API to get list:** `GET /api/admin/currencies`
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "code": "USD",
        "name": {"ar": "دولار أمريكي", "en": "US Dollar"},
        "symbol": "$",
        "is_active": true
      }
    ]
  }
}
```
**Used in:** Prices, Payments

---

### Field: `section_id`
**API to get list:** `GET /api/admin/sections`
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": {"ar": "قسم", "en": "Section"}
      }
    ]
  }
}
```
**Used in:** Page Sections

---

### Field: `page_id`
**API to get list:** `GET /api/admin/sections/pages`
```json
{
  "success": true,
  "data": [
    {"id": 1, "name": "Home"},
    {"id": 2, "name": "Products"}
  ]
}
```
**Used in:** Sections

---

### Field: `display_type_id`
**API to get list:** `GET /api/admin/sections/display-types`
```json
{
  "success": true,
  "data": [
    {"id": 1, "name": "Grid"},
    {"id": 2, "name": "Slider"}
  ]
}
```
**Used in:** Sections

---

### Field: `item_type_id`
**API to get list:** `GET /api/admin/sections/item-types`
```json
{
  "success": true,
  "data": [
    {"id": 1, "name": "Product"},
    {"id": 2, "name": "Category"}
  ]
}
```
**Used in:** Section Items

---

## Quick Reference Table

| Field Name | API Endpoint | Used In |
|------------|--------------|---------|
| `category_id` | `GET /api/admin/categories` | Products, Baskets, Gifts |
| `shop_id` | `GET /api/admin/shops` | Products, Orders |
| `vendor_id` | `GET /api/admin/vendors` | Shops, Stores |
| `area_id` | `GET /api/admin/areas` | Shops, Addresses |
| `city_id` | `GET /api/admin/cities` | Areas |
| `governorate_id` | `GET /api/admin/governorates` | Cities |
| `driver_id` | `GET /api/admin/drivers` | Orders |
| `service_ids` | `GET /api/admin/services` | Shops |
| `role_id` | `GET /api/admin/roles` | Admins, Users |
| `permissions` | `GET /api/admin/permissions` | Roles |
| `brand_id` | `GET /api/admin/brands` | Products |
| `shop_product_variant_id` | `GET /api/admin/products/{id}` | Baskets |
| `category_detail_id` | `GET /api/admin/category-details` | Products |
| `category_attribute_id` | `GET /api/admin/category-attributes` | Products |
| `user_id` | `GET /api/admin/users` | Points, Orders |
| `package_id` | `GET /api/admin/packages` | Subscriptions |
| `gift_id` | `GET /api/admin/gifts` | Point Exchanges |
| `basket_id` | `GET /api/admin/baskets` | User Schedules |
| `currency_id` | `GET /api/admin/currencies` | Prices |
| `language_code` | `GET /api/admin/languages` | Translations |

---

## Frontend Integration Tips

### 1. Cascading Dropdowns
When dropdowns depend on each other, load them in sequence:

```javascript
// Example: Governorate → City → Area
// Step 1: Load governorates
GET /api/admin/governorates

// Step 2: When user selects governorate, load cities
GET /api/admin/cities?governorate_id={selectedGovernorateId}

// Step 3: When user selects city, load areas
GET /api/admin/areas?city_id={selectedCityId}
```

### 2. Search in Dropdowns
Most list endpoints support search:
```javascript
GET /api/admin/products?search=tomato
GET /api/admin/shops?search=vegetable
```

### 3. Filtering Lists
Use query parameters to filter:
```javascript
GET /api/admin/products?category_id=1
GET /api/admin/shops?area_id=5&is_active=true
```

### 4. Pagination for Large Lists
For large datasets, use pagination:
```javascript
GET /api/admin/products?page=1&per_page=20
```

### 5. Caching Reference Data
Cache static data like categories, languages, and currencies to reduce API calls.

---
