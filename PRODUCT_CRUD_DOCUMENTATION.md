# Product CRUD API Documentation - React/Vite Frontend Guide

## Base Information
- **Base URL**: `/api/admin/products`
- **Authentication**: Required - Bearer Token in header
- **Content-Type**: `multipart/form-data` (for create/update with images)

---

## Table of Contents
1. [List Products](#1-list-products)
2. [Get Single Product](#2-get-single-product)
3. [Create Product](#3-create-product)
4. [Update Product](#4-update-product)
5. [Delete Product](#5-delete-product)
6. [Dependencies & Related APIs](#6-dependencies--related-apis)
7. [React/Vite Implementation Examples](#7-reactvite-implementation-examples)

---

## 1. List Products

### Endpoint
```
GET /api/admin/products
```

### Query Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `page` | integer | No | Page number (default: 1) |
| `per_page` | integer | No | Items per page (default: 10) |
| `search` | string | No | Search in name, description, sku, barcode |
| `sort_field` | string | No | Field to sort by (id, price, created_at, etc.) |
| `sort_order` | string | No | 'asc' or 'desc' (default: 'desc') |
| `shop_id` | integer | No | Filter by shop |

### Response Example
```json
{
  "status": true,
  "message": "Success",
  "data": {
    "data": [
      {
        "id": 1,
        "category_id": "Electronics",
        "brand_id": "Samsung",
        "name": "Product Name",
        "description": "Short description",
        "full_description": "Full description",
        "country": "USA",
        "sku": "SKU123",
        "model": "MODEL-X",
        "price": 100,
        "price_after_discount": 90,
        "quantity": 50,
        "barcode": "123456789",
        "time_prepare": "00:30:00",
        "bought_with": [2, 3],
        "is_instant_delivery": true,
        "image": "https://example.com/storage/products/image.jpg",
        "images": [
          "https://example.com/storage/products/image1.jpg",
          "https://example.com/storage/products/image2.jpg"
        ],
        "created_at": "2024-01-01T00:00:00.000000Z"
      }
    ],
    "current_page": 1,
    "last_page": 5,
    "per_page": 10,
    "total": 50
  }
}
```

---

## 2. Get Single Product

### Endpoint
```
GET /api/admin/products/{id}
```

### Response Example
```json
{
  "status": true,
  "message": "Success",
  "data": {
    "id": 1,
    "name": {
      "en": "Product Name",
      "ar": "اسم المنتج"
    },
    "description": {
      "en": "Description",
      "ar": "الوصف"
    },
    "full_description": {
      "en": "Full description",
      "ar": "الوصف الكامل"
    },
    "country": {
      "en": "USA",
      "ar": "أمريكا"
    },
    "price": 100,
    "price_after_discount": 90,
    "quantity": 50,
    "sku": "SKU123",
    "model": "MODEL-X",
    "barcode": "123456789",
    "time_prepare": "00:30",
    "bought_with": [2, 3, 4],
    "is_instant_delivery": true,
    "category": {
      "id": 1,
      "name": "Electronics"
    },
    "brand": {
      "id": 1,
      "name": "Samsung"
    },
    "variants": [
      {
        "id": 1,
        "attributes": [
          {
            "attribute": "Color",
            "value": "Red",
            "type": "color"
          },
          {
            "attribute": "Size",
            "value": "Large",
            "type": "text"
          }
        ],
        "shops": [
          {
            "shop_id": 1,
            "shop_name": "Main Shop",
            "price": 100,
            "quantity": 20
          }
        ],
        "images": [
          {
            "id": 1,
            "url": "https://example.com/storage/variants/image1.jpg"
          }
        ]
      }
    ],
    "category_details": [
      {
        "id": 1,
        "name": "Warranty",
        "value": {
          "en": "2 years",
          "ar": "سنتان"
        }
      }
    ],
    "extra_details": [
      {
        "id": 1,
        "key": {
          "en": "Weight",
          "ar": "الوزن"
        },
        "value": {
          "en": "500g",
          "ar": "500 جرام"
        }
      }
    ],
    "images": [
      {
        "id": 1,
        "url": "https://example.com/storage/products/image1.jpg"
      },
      {
        "id": 2,
        "url": "https://example.com/storage/products/image2.jpg"
      }
    ]
  }
}
```

---

## 3. Create Product

### Endpoint
```
POST /api/admin/products
```

### Content-Type
```
multipart/form-data
```

### Request Body Structure

#### Basic Fields (Required)
```javascript
{
  // Category (Required) - Get from /api/admin/categories
  "category_id": 1,
  
  // Price (Required)
  "price": 100,
  
  // Translatable Fields (for each active language)
  "name": {
    "en": "Product Name",
    "ar": "اسم المنتج"
  },
  "description": {
    "en": "Short description",
    "ar": "وصف قصير"
  },
  "full_description": {
    "en": "Full detailed description",
    "ar": "وصف كامل مفصل"
  },
  "country": {
    "en": "USA",
    "ar": "أمريكا"
  }
}
```

#### Optional Basic Fields
```javascript
{
  "brand_id": 1,                    // Get from /api/admin/brands
  "sku": "SKU123",                  // Unique
  "model": "MODEL-X",               // Unique
  "price_after_discount": 90,
  "quantity": 50,
  "barcode": "123456789",
  "time_prepare": "00:30",          // Format: HH:MM
  "bought_with": [2, 3, 4],         // Array of product IDs
  "is_instant_delivery": true
}
```

#### Product Images (Optional)
```javascript
{
  "images": [File, File, File]      // Array of image files
}
```

#### Variants (Optional but Important)
```javascript
{
  "variants": [
    {
      // Array of attribute value IDs
      // Get from /api/admin/category-attributes (based on category)
      "attributes_values_ids": [1, 5, 8],
      
      // Variant images (optional)
      "images": [File, File]
    },
    {
      "attributes_values_ids": [2, 6, 9],
      "images": [File]
    }
  ]
}
```

#### Shop Variants (Optional - Link variants to shops with price/quantity)
```javascript
{
  "shop_variants": [
    {
      "shop_id": 1,              // Get from /api/admin/shops
      "variant_index": 0,        // Index of variant in variants array (0-based)
      "price": 100,              // Shop-specific price (optional)
      "quantity": 20             // Shop-specific quantity (optional)
    },
    {
      "shop_id": 2,
      "variant_index": 0,
      "price": 95,
      "quantity": 15
    },
    {
      "shop_id": 1,
      "variant_index": 1,
      "price": 110,
      "quantity": 10
    }
  ]
}
```

#### Category Details (Optional - Category-specific fields)
```javascript
{
  "category_details": [
    {
      "category_detail_id": 1,   // Get from /api/admin/category-details
      "detail_value": {
        "en": "2 years warranty",
        "ar": "ضمان سنتين"
      }
    },
    {
      "category_detail_id": 2,
      "detail_value": {
        "en": "LED",
        "ar": "ليد"
      }
    }
  ]
}
```

#### Extra Details (Optional - Custom key-value pairs)
```javascript
{
  "extra_details": [
    {
      "detail_key": {
        "en": "Weight",
        "ar": "الوزن"
      },
      "detail_value": {
        "en": "500g",
        "ar": "500 جرام"
      }
    },
    {
      "detail_key": {
        "en": "Dimensions",
        "ar": "الأبعاد"
      },
      "detail_value": {
        "en": "10x20x5 cm",
        "ar": "10×20×5 سم"
      }
    }
  ]
}
```

### Complete FormData Example (JavaScript)
```javascript
const formData = new FormData();

// Basic fields
formData.append('category_id', 1);
formData.append('brand_id', 1);
formData.append('price', 100);
formData.append('price_after_discount', 90);
formData.append('quantity', 50);
formData.append('sku', 'SKU123');
formData.append('model', 'MODEL-X');
formData.append('barcode', '123456789');
formData.append('time_prepare', '00:30');
formData.append('is_instant_delivery', true);

// Translatable fields
formData.append('name[en]', 'Product Name');
formData.append('name[ar]', 'اسم المنتج');
formData.append('description[en]', 'Description');
formData.append('description[ar]', 'الوصف');
formData.append('full_description[en]', 'Full description');
formData.append('full_description[ar]', 'الوصف الكامل');
formData.append('country[en]', 'USA');
formData.append('country[ar]', 'أمريكا');

// Bought with products
formData.append('bought_with[0]', 2);
formData.append('bought_with[1]', 3);

// Product images
productImages.forEach((file, index) => {
  formData.append(`images[${index}]`, file);
});

// Variants
formData.append('variants[0][attributes_values_ids][0]', 1);
formData.append('variants[0][attributes_values_ids][1]', 5);
variantImages[0].forEach((file, index) => {
  formData.append(`variants[0][images][${index}]`, file);
});

formData.append('variants[1][attributes_values_ids][0]', 2);
formData.append('variants[1][attributes_values_ids][1]', 6);

// Shop variants
formData.append('shop_variants[0][shop_id]', 1);
formData.append('shop_variants[0][variant_index]', 0);
formData.append('shop_variants[0][price]', 100);
formData.append('shop_variants[0][quantity]', 20);

formData.append('shop_variants[1][shop_id]', 2);
formData.append('shop_variants[1][variant_index]', 0);
formData.append('shop_variants[1][price]', 95);
formData.append('shop_variants[1][quantity]', 15);

// Category details
formData.append('category_details[0][category_detail_id]', 1);
formData.append('category_details[0][detail_value][en]', '2 years');
formData.append('category_details[0][detail_value][ar]', 'سنتان');

// Extra details
formData.append('extra_details[0][detail_key][en]', 'Weight');
formData.append('extra_details[0][detail_key][ar]', 'الوزن');
formData.append('extra_details[0][detail_value][en]', '500g');
formData.append('extra_details[0][detail_value][ar]', '500 جرام');
```

### Success Response
```json
{
  "status": true,
  "message": "Success",
  "data": {
    // Same structure as Get Single Product response
  }
}
```

---

## 4. Update Product

### Endpoint
```
PUT /api/admin/products/{id}
```

### Important Notes
- All fields are optional in update
- Same structure as Create, but you can send only the fields you want to update
- For variants, category_details, and extra_details: sending new data will replace all existing data
- To keep existing images, don't send the images field

### Additional Fields for Update
```javascript
{
  // For updating existing variants
  "variants": [
    {
      "id": 1,                           // Existing variant ID (optional)
      "attributes_values_ids": [1, 5],
      "images": [File]                   // New images (optional)
    }
  ],
  
  // For updating existing category details
  "category_details": [
    {
      "id": 1,                           // Existing detail ID (optional)
      "category_detail_id": 1,
      "detail_value": {
        "en": "Updated value",
        "ar": "قيمة محدثة"
      }
    }
  ],
  
  // For updating existing extra details
  "extra_details": [
    {
      "id": 1,                           // Existing detail ID (optional)
      "detail_key": {
        "en": "Updated key",
        "ar": "مفتاح محدث"
      },
      "detail_value": {
        "en": "Updated value",
        "ar": "قيمة محدثة"
      }
    }
  ]
}
```

### Partial Update Example (Only update price and name)
```javascript
const formData = new FormData();
formData.append('price', 120);
formData.append('name[en]', 'Updated Product Name');
formData.append('name[ar]', 'اسم المنتج المحدث');
```

---

## 5. Delete Product

### Endpoint
```
DELETE /api/admin/products/{id}
```

### Response
```json
{
  "status": true,
  "message": "Success",
  "data": null
}
```

---

## 6. Dependencies & Related APIs

### Required APIs to Call Before Creating/Updating Product

#### 1. Get Active Languages
**Endpoint**: `GET /api/admin/languages`
**Purpose**: Get list of active languages for translatable fields
**Filter**: `is_active=true`

```javascript
// Response
{
  "data": [
    { "id": 1, "code": "en", "name": "English" },
    { "id": 2, "code": "ar", "name": "Arabic" }
  ]
}
```

**Usage**: Use language codes for all translatable fields (name, description, etc.)

---

#### 2. Get Categories
**Endpoint**: `GET /api/admin/categories`
**Purpose**: Get list of categories for category_id field

```javascript
// Response
{
  "data": [
    { "id": 1, "name": "Electronics", "parent_id": null },
    { "id": 2, "name": "Clothing", "parent_id": null }
  ]
}
```

**Usage**: Required field - select category_id

---

#### 3. Get Brands
**Endpoint**: `GET /api/admin/brands`
**Purpose**: Get list of brands for brand_id field

```javascript
// Response
{
  "data": [
    { "id": 1, "name": "Samsung" },
    { "id": 2, "name": "Apple" }
  ]
}
```

**Usage**: Optional field - select brand_id

---

#### 4. Get Category Attributes (for Variants)
**Endpoint**: `GET /api/admin/category-attributes?category_id={category_id}`
**Purpose**: Get attributes and their values for selected category

```javascript
// Response
{
  "data": [
    {
      "id": 1,
      "name": "Color",
      "type": "color",
      "values": [
        { "id": 1, "name": "Red", "value": "#FF0000" },
        { "id": 2, "name": "Blue", "value": "#0000FF" }
      ]
    },
    {
      "id": 2,
      "name": "Size",
      "type": "text",
      "values": [
        { "id": 5, "name": "Small" },
        { "id": 6, "name": "Large" }
      ]
    }
  ]
}
```

**Usage**: 
- Call this after selecting category
- Use value IDs in `variants[].attributes_values_ids`
- Create variant combinations (e.g., Red+Small, Red+Large, Blue+Small, Blue+Large)

---

#### 5. Get Category Details
**Endpoint**: `GET /api/admin/category-details?category_id={category_id}`
**Purpose**: Get category-specific detail fields

```javascript
// Response
{
  "data": [
    { "id": 1, "name": "Warranty", "category_id": 1 },
    { "id": 2, "name": "Screen Type", "category_id": 1 }
  ]
}
```

**Usage**: Optional - use IDs in `category_details[].category_detail_id`

---

#### 6. Get Shops
**Endpoint**: `GET /api/admin/shops`
**Purpose**: Get list of shops for shop_variants

```javascript
// Response
{
  "data": [
    { "id": 1, "name": "Main Shop", "is_active": true },
    { "id": 2, "name": "Branch Shop", "is_active": true }
  ]
}
```

**Usage**: Use shop IDs in `shop_variants[].shop_id`

---

#### 7. Get Products (for bought_with)
**Endpoint**: `GET /api/admin/products`
**Purpose**: Get list of products for "bought with" suggestions

```javascript
// Response
{
  "data": [
    { "id": 2, "name": "Related Product 1" },
    { "id": 3, "name": "Related Product 2" }
  ]
}
```

**Usage**: Use product IDs in `bought_with` array

---

### API Call Flow for Creating Product

```
1. GET /api/admin/languages
   ↓ (Get language codes: en, ar)
   
2. GET /api/admin/categories
   ↓ (User selects category_id: 1)
   
3. GET /api/admin/brands (optional)
   ↓ (User selects brand_id: 1)
   
4. GET /api/admin/category-attributes?category_id=1
   ↓ (Get attributes and values for variants)
   
5. GET /api/admin/category-details?category_id=1 (optional)
   ↓ (Get category-specific fields)
   
6. GET /api/admin/shops (optional)
   ↓ (Get shops for shop_variants)
   
7. GET /api/admin/products (optional)
   ↓ (Get products for bought_with)
   
8. POST /api/admin/products
   ↓ (Create product 
egoryDetails, setCategoryDetails] = useState([]);
  
  const [formData, setFormData] = useState({
    category_id: '',
    brand_id: '',
    price: '',
    name: {},
    description: {},
    variants: [],
    shop_variants: [],
    category_details: [],
    extra_details: [],
    images: []
  });

  // Load initial data
  useEffect(() => {
    loadLanguages();
    loadCategories();
    loadBrands();
    loadShops();
    
    if (productId) {
      loadProduct(productId);
    }
  }, [productId]);

  // Load languages
  const loadLanguages = async () => {
    const response = await axios.get('/api/admin/languages');
    setLanguages(response.data.data);
    
    // Initialize translatable fields
    const langObj = {};
    response.data.data.forEach(lang => {
      langObj[lang.code] = '';
    });
    setFormData(prev => ({
      ...prev,
      name: langObj,
      description: langObj,
      full_description: langObj,
      country: langObj
    }));
  };

  // Load categories
  const loadCategories = async () => {
    const response = await axios.get('/api/admin/categories');
    setCategories(response.data.data);
  };

  // Load brands
  const loadBrands = async () => {
    const response = await axios.get('/api/admin/brands');
    setBrands(response.data.data);
  };

  // Load shops
  const loadShops = async () => {
    const response = await axios.get('/api/admin/shops');
    setShops(response.data.data);
  };

  // Load category attributes when category changes
  const handleCategoryChange = async (categoryId) => {
    setFormData(prev => ({ ...prev, category_id: categoryId }));
    
    const response = await axios.get(
      `/api/admin/category-attributes?category_id=${categoryId}`
    );
    setCategoryAttributes(response.data.data);
    
    const detailsResponse = await axios.get(
      `/api/admin/category-details?category_id=${categoryId}`
    );
    setCategoryDetails(detailsResponse.data.data);
  };

  // Load existing product
  const loadProduct = async (id) => {
    const response = await axios.get(`/api/admin/products/${id}`);
    const product = response.data.data;
    
    setFormData({
      category_id: product.category.id,
      brand_id: product.brand?.id || '',
      price: product.price,
      price_after_discount: product.price_after_discount,
      quantity: product.quantity,
      sku: product.sku,
      model: product.model,
      barcode: product.barcode,
      time_prepare: product.time_prepare,
      bought_with: product.bought_with,
      is_instant_delivery: product.is_instant_delivery,
      name: product.name,
      description: product.description,
      full_description: product.full_description,
      country: product.country,
      variants: product.variants,
      category_details: product.category_details,
      extra_details: product.extra_details
    });
    
    // Load category-specific data
    handleCategoryChange(product.category.id);
  };

  // Handle form submission
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    const formDataToSend = new FormData();
    
    // Basic fields
    formDataToSend.append('category_id', formData.category_id);
    if (formData.brand_id) {
      formDataToSend.append('brand_id', formData.brand_id);
    }
    formDataToSend.append('price', formData.price);
    
    // Translatable fields
    languages.forEach(lang => {
      if (formData.name[lang.code]) {
        formDataToSend.append(`name[${lang.code}]`, formData.name[lang.code]);
      }
      if (formData.description[lang.code]) {
        formDataToSend.append(`description[${lang.code}]`, formData.description[lang.code]);
      }
    });
    
    // Variants
    formData.variants.forEach((variant, vIndex) => {
      variant.attributes_values_ids.forEach((attrId, aIndex) => {
        formDataToSend.append(
          `variants[${vIndex}][attributes_values_ids][${aIndex}]`,
          attrId
        );
      });
      
      // Variant images
      if (variant.images) {
        variant.images.forEach((file, iIndex) => {
          formDataToSend.append(`variants[${vIndex}][images][${iIndex}]`, file);
        });
      }
    });
    
    // Shop variants
    formData.shop_variants.forEach((sv, index) => {
      formDataToSend.append(`shop_variants[${index}][shop_id]`, sv.shop_id);
      formDataToSend.append(`shop_variants[${index}][variant_index]`, sv.variant_index);
      formDataToSend.append(`shop_variants[${index}][price]`, sv.price);
      formDataToSend.append(`shop_variants[${inde
d, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });
      }
      
      alert('Product saved successfully!');
    } catch (error) {
      console.error('Error saving product:', error);
      alert('Error saving product');
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      {/* Form fields here */}
    </form>
  );
};

export default ProductForm;
```

---

### 7.2 Variant Builder Component

```jsx
const VariantBuilder = ({ 
  categoryAttributes, 
  variants, 
  setVariants 
}) => {
  // Generate all possible combinations
  const generateVariants = () => {
    if (categoryAttributes.length === 0) return;
    
    const combinations = [];
    const generate = (current, depth) => {
      if (depth === categoryAttributes.length) {
        combinations.push([...current]);
        return;
      }
      
      const attr = categoryAttributes[depth];
      attr.values.forEach(value => {
        current.push(value.id);
        generate(current, depth + 1);
        current.pop();
      });
    };
    
    generate([], 0);
    
    const newVariants = combinations.map(combo => ({
      attributes_values_ids: combo,
      images: []
    }));
    
    setVariants(newVariants);
  };

  return (
    <div>
      <button type="button" onClick={generateVariants}>
        Generate All Variants
      </button>
      
      {variants.map((variant, index) => (
        <div key={index}>
          <h4>Variant {index + 1}</h4>
          <div>
            {variant.attributes_values_ids.map(attrId => {
              // Find attribute value name
              const attrValue = categoryAttributes
                .flatMap(attr => attr.values)
                .find(v => v.id === attrId);
              return <span key={attrId}>{attrValue?.name} </span>;
            })}
          </div>
          
          <input
            type="file"
            multiple
            accept="image/*"
            onChange={(e) => {
              const newVariants = [...variants];
              newVariants[index].images = Array.from(e.target.files);
              setVariants(newVariants);
            }}
          />
        </div>
      ))}
    </div>
  );
};
```

---

### 7.3 Shop Variant Manager Component

```jsx
const ShopVariantManager = ({ 
  variants, 
  shops, 
  shopVariants, 
  setShopVariants 
}) => {
  const addShopVariant = (variantIndex, shopId) => {
    setShopVariants(prev => [
      ...prev,
      {
        shop_id: shopId,
        variant_index: variantIndex,
        price: '',
        quantity: ''
      }
    ]);
  };

  return (
    <div>
      <h3>Shop Variants</h3>
      {variants.map((variant, vIndex) => (
        <div key={vIndex}>
          <h4>Variant {vIndex + 1}</h4>
          
          {shops.map(shop => {
            const existing = shopVariants.find(
              sv => sv.variant_index === vIndex && sv.shop_id === shop.id
            );
            
            return (
              <div key={shop.id}>
                <label>
                  <input
                    type="checkbox"
                    checked={!!existing}
                    onChange={(e) => {
                      if (e.target.checked) {
                        addShopVariant(vIndex, shop.id);
                      } else {
                        setShopVariants(prev => 
                          prev.filter(sv => 
                            !(sv.variant_index === vIndex && sv.shop_id === shop.id)
                          )
                        );
                      }
                    }}
                  />
                  {shop.name}
                </label>
                
                {existing && (
                  <>
                    <input
                      type="number"
                      placeholder="Price"
                      value={existing.price}
                      onChange={(e) => {
                        setShopVariants(prev => prev.map(sv =>
                          sv.variant_index === vIndex && sv.shop_id === shop.id
                            ? { ...sv, price: e.target.value }
                            : sv
                        ));
                      }}
                    />
                    <input
                      type="number"
                      placeholder="Quantity"
                      value={existing.quantity}
                      onChange={(e) => {
                        setShopVariants(prev => prev.map(sv =>
                          sv.variant_index === vIndex && sv.shop_id === shop.id
                            ? { ...sv, quantity: e.target.value }
                            : sv
                        ));
                      }}
                    />
                  </>
                )}
              </div>
            );
          })}
        </div>
      ))}
    </div>
  );
};
```

---

### 7.4 Axios Configuration

```javascript
// api/axios.js
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://your-api-domain.com/api',
  headers: {
    'Accept': 'application/json',
  }
});

// Add token to requests
api.interceptors.request.use(config => {
  const token = localStorage.getItem('admin_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;
```

---

## 8. Common Pitfalls & Tips

### ❌ Common Mistakes

1. **Forgetting to set Content-Type for FormData**
   ```javascript
   // ❌ Wrong
   axios.post('/api/admin/products', formData);
   
   // ✅ Correct
   axios.post('/api/admin/products', formData, {
     headers: { 'Content-Type': 'multipart/form-data' }
   });
   ```

2. **Not using proper array notation in FormData**
   ```javascript
   // ❌ Wrong
   formData.append('variants', JSON.stringify(variants));
   
   // ✅ Correct
   variants.forEach((variant, index) => {
     variant.attributes_values_ids.forEach((id, i) => {
       formData.append(`variants[${index}][attributes_values_ids][${i}]`, id);
     });
   });
   ```

3. **Forgetting variant_index in shop_variants**
   ```javascript
   // ❌ Wrong - Missing variant_index
   { shop_id: 1, price: 100 }
   
   // ✅ Correct
   { shop_id: 1, variant_index: 0, price: 100 }
   ```

### ✅ Best Practices

1. **Load category-specific data dynamically**
   - Load attributes and details when category changes
   - Clear variants when category changes

2. **Validate before submit**
   - Check required fields
   - Validate image file types and sizes
   - Ensure at least one variant if using variants

3. **Handle errors properly**
   - Show validation errors from API
   - Handle network errors
   - Provide user feedback

4. **Optimize image uploads**
   - Compress images before upload
   - Show image previews
   - Allow image removal

---

## 9. Testing Checklist

- [ ] Create product with only required fields
- [ ] Create product with all optional fields
- [ ] Create product with variants
- [ ] Create product with shop variants
- [ ] Create product with category details
- [ ] Create product with extra details
- [ ] Create product with multiple images
- [ ] Update product (partial update)
- [ ] Update product (full update)
- [ ] Delete product
- [ ] List products with pagination
- [ ] Search products
- [ ] Filter products by shop
- [ ] Sort products

---

## 10. Support & Questions

If you encounter any issues:
1. Check the validation errors in the API response
2. Verify all required dependencies are loaded
3. Ensure FormData is properly formatted
4. Check browser console for errors
5. Verify authentication token is valid

Happy coding! 🚀
