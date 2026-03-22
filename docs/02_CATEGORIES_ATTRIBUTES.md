# Categories & Attributes API

## Categories API

### Base URL
```
/api/admin/categories
```

## 1. List Categories
**GET** `/api/admin/categories`

### Query Parameters
- `page`, `per_page`, `search`
- `parent_id` (int): Filter by parent category
- `is_active` (boolean): Filter active/inactive

### Response
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": "Electronics",
        "icon": "storage/categories/icon.jpg",
        "parent_id": null,
        "order": 1,
        "is_active": true,
        "children_count": 5
      }
    ]
  }
}
```

## 2. Create Category
**POST** `/api/admin/categories`

### Request Body
```json
{
  "name[en]": "Category Name",
  "name[ar]": "اسم الفئة",
  "icon": file,
  "parent_id": null,
  "order": 1,
  "is_active": true
}
```


## Category Attributes API

### Base URL
```
/api/admin/category-attributes
```

## 1. List Attributes for Category
**GET** `/api/admin/categories/{categoryId}/attributes`

### Response
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": {"en": "Color", "ar": "اللون"},
      "type": "select",
      "values": [
        {"id": 1, "name": {"en": "Red", "ar": "أحمر"}},
        {"id": 2, "name": {"en": "Blue", "ar": "أزرق"}}
      ]
    }
  ]
}
```

## 2. Create Attribute
**POST** `/api/admin/category-attributes`

### Request Body
```json
{
  "category_id": 1,
  "name[en]": "Size",
  "name[ar]": "الحجم",
  "type": "select",
  "values": [
    {"name[en]": "Small", "name[ar]": "صغير"},
    {"name[en]": "Large", "name[ar]": "كبير"}
  ]
}
```
