# Products API - Admin Panel

## Base URL
```
/api/admin/products
```

## Authentication
All endpoints require admin authentication:
```
Authorization: Bearer {admin_token}
```

## 1. List Products
**GET** `/api/admin/products`

### Query Parameters
- `page` (int): Page number
- `per_page` (int): Items per page (default: 10)
- `search` (string): Search in name, SKU, barcode
- `category_id` (int): Filter by category
- `brand_id` (int): Filter by brand
- `approval_status` (string): pending, approved, rejected
- `sort_field` (string): id, name, price, created_at
- `sort_order` (string): asc, desc

### Response
```json
{
  "success": true,
  "data": {
    "items": [...],
    "pagination": {
      "current_page": 1,
      "last_page": 10,
      "per_page": 10,
      "total": 100
    }
  }
}
```

## 2. Get Single Product
**GET** `/api/admin/products/{id}`

### Response
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": {"en": "Product Name", "ar": "اسم المنتج"},
    "description": {"en": "Short desc", "ar": "وصف قصير"},
    "full_description": {"en": "Full desc", "ar": "وصف كامل"},
    "country": {"en": "USA", "ar": "أمريكا"},
    "price": 100.00,
    "cost_price": 80.00,
    "price_after_discount": 90.00,
    "quantity": 50,
    "unit": "piece",
    "warranty_period": 12,
    "is_visible": true,
    "discount_type": "percentage",
    "sku": "SKU-123",
    "model": "MODEL-123",
    "barcode": "BAR123",
    "time_prepare": "02:00",
    "is_instant_delivery": true,
    "rating": 4.5,
    "rating_breakdown": {...},
    "thumbnail": "https://...",
    "category": {"id": 5, "name": "Electronics"},
    "attributes_map": {...},
    "shop_variants": [...],
    "category_details": [...],
    "extra_details": [...],
    "images": [...],
    "available_shops": [...],
    "is_favorite": false,
    "top_badges": [...],
    "bottom_badges": [...],
    "icons": [...]
  }
}
```

## 3. Create Product
**POST** `/api/admin/products`

### Request Body (multipart/form-data)

#### Basic Fields
```json
{
  "name[en]": "Product Name",
  "name[ar]": "اسم المنتج",
  "description[en]": "Short description",
  "description[ar]": "وصف قصير",
  "full_description[en]": "Full description",
  "full_description[ar]": "وصف كامل",
  "country[en]": "USA",
  "country[ar]": "أمريكا",
  
  "category_id": 5,
  "brand_id": 3,
  "price": 100,
  "cost_price": 80,
  "discount": 10,
  "discount_type": "percentage",
  "quantity": 50,
  "unit": "piece",
  "warranty_period": 12,
  "is_visible": true,
  
  "sku": "SKU-123",
  "model": "MODEL-123",
  "barcode": "BAR123",
  "time_prepare": "02:00",
  "is_instant_delivery": true,
  
  "bought_with": [1, 2, 3],
  
  "thumbnail": file,
  "media[]": [file1, file2, file3]
}
```

#### Variants
```json
{
  "variants[0][attributes_values_ids][]": [1, 5],
  "variants[0][images][]": [file1, file2],
  "variants[1][attributes_values_ids][]": [2, 6],
  "variants[1][images][]": [file3, file4]
}
```

#### Shop Variants
```json
{
  "shop_variants[0][shop_id]": 1,
  "shop_variants[0][variant_index]": 0,
  "shop_variants[0][price]": 100,
  "shop_variants[0][quantity]": 50
}
```

#### Category Details
```json
{
  "category_details[0][category_detail_id]": 1,
  "category_details[0][detail_value][en]": "Value",
  "category_details[0][detail_value][ar]": "القيمة"
}
```

#### Extra Details
```json
{
  "extra_details[0][detail_key][en]": "Weight",
  "extra_details[0][detail_key][ar]": "الوزن",
  "extra_details[0][detail_value][en]": "1kg",
  "extra_details[0][detail_value][ar]": "1 كجم",
  "extra_details[0][price]": 5
}
```

#### Badges & Icons
```json
{
  "badges[0][id]": 1,
  "badges[0][position]": "top",
  "badges[1][id]": 2,
  "badges[1][position]": "bottom",
  
  "icon_ids[]": [1, 2, 3]
}
```

#### SEO Fields
```json
{
  "seo_title[en]": "SEO Title",
  "seo_title[ar]": "عنوان SEO",
  "seo_description[en]": "SEO Description",
  "seo_description[ar]": "وصف SEO",
  "seo_keywords[en][]": ["keyword1", "keyword2"],
  "seo_keywords[ar][]": ["كلمة1", "كلمة2"],
  "seo_image": file
}
```
