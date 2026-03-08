# نظام الأيقونات للمنتجات - Icons System

## نظرة عامة
نظام لإضافة أيقونات للمنتجات (مثل: جديد، عرض خاص، توصيل مجاني، إلخ). كل أيقونة لها اسم وصورة ووصف.

---

## Database Structure

### Icons Table
```sql
- id
- name (string)
- image (string) - path to image
- description (text, nullable)
- is_active (boolean)
- timestamps
```

### icon_product Pivot Table
```sql
- icon_id (foreign key)
- product_id (foreign key)
- primary key (icon_id, product_id)
```

---

## Admin APIs

### 1. Get All Icons
**GET** `/api/admin/icons`

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "جديد",
            "image": "http://localhost:8000/storage/icons/icon1.png",
            "description": "منتج جديد",
            "is_active": true,
            "created_at": "2026-03-08T16:00:00.000000Z",
            "updated_at": "2026-03-08T16:00:00.000000Z"
        }
    ]
}
```

### 2. Get Single Icon
**GET** `/api/admin/icons/{id}`

### 3. Create Icon
**POST** `/api/admin/icons`

**Body (multipart/form-data):**
```
name: "جديد"
image: [file]
description: "منتج جديد"
is_active: true
```

### 4. Update Icon
**PUT/PATCH** `/api/admin/icons/{id}`

**Body (multipart/form-data):**
```
name: "جديد - محدث"
image: [file] (optional)
description: "منتج جديد محدث"
is_active: false
```

### 5. Delete Icon
**DELETE** `/api/admin/icons/{id}`

---

## Adding Icons to Products

### في Product Create/Update Request

أضف `icon_ids` كمصفوفة من IDs الأيقونات:

**Example:**
```json
{
    "name": {
        "ar": "منتج تجريبي",
        "en": "Test Product"
    },
    "category_id": 1,
    "price": 100,
    "icon_ids": [1, 2, 3],
    ...
}
```

---

## User APIs - Product Response

عند جلب المنتج، الأيقونات ترجع مع المنتج:

**GET** `/api/user/products/{id}`

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "منتج تجريبي",
        "price": 100,
        ...
        "icons": [
            {
                "id": 1,
                "name": "جديد",
                "image": "http://localhost:8000/storage/icons/icon1.png",
                "description": "منتج جديد"
            },
            {
                "id": 2,
                "name": "عرض خاص",
                "image": "http://localhost:8000/storage/icons/icon2.png",
                "description": "عرض لفترة محدودة"
            }
        ]
    }
}
```

---

## Files Created

### Models
- `app/Models/Icon.php`

### Migrations
- `database/migrations/2026_03_08_164006_create_icons_table.php`
- `database/migrations/2026_03_08_164130_create_icon_product_table.php`

### Controllers
- `app/Http/Controllers/Admin/IconController.php`

### Services
- `app/Services/Admin/IconService.php`

### Requests
- `app/Http/Requests/Admin/Icon/StoreIconRequest.php`
- `app/Http/Requests/Admin/Icon/UpdateIconRequest.php`

### Resources
- `app/Http/Resources/Icon/IconResource.php` (للـ Admin)
- `app/Http/Resources/Icon/IconSimpleResource.php` (للـ User)

### Routes
- Added to `routes/api/admin.php`

### Modified Files
- `app/Models/Product.php` - added icons() relationship
- `app/Services/Admin/ProductService.php` - added icon_ids handling
- `app/Services/User/ProductService.php` - added icons to relations
- `app/Http/Resources/Product/OneResource.php` - added icons to response

---

## Usage Examples

### 1. Create Icon
```bash
curl -X POST http://localhost:8000/api/admin/icons \
  -H "Authorization: Bearer {admin_token}" \
  -F "name=جديد" \
  -F "image=@icon.png" \
  -F "description=منتج جديد" \
  -F "is_active=true"
```

### 2. Attach Icons to Product
```bash
curl -X PUT http://localhost:8000/api/admin/products/1 \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "icon_ids": [1, 2, 3]
  }'
```

### 3. Get Product with Icons (User)
```bash
curl -X GET http://localhost:8000/api/user/products/1 \
  -H "Authorization: Bearer {user_token}"
```

---

## Notes

- الصور تُحفظ في `storage/icons/`
- عند حذف أيقونة، الصورة تُحذف تلقائياً
- عند تحديث أيقونة بصورة جديدة، الصورة القديمة تُحذف
- العلاقة many-to-many بين Products و Icons
- الأيقونات ترجع فقط للـ User إذا كانت مرتبطة بالمنتج
- يمكن للـ Admin تعطيل أيقونة بدون حذفها (is_active = false)

---

## Frontend Integration

### Display Icons on Product Card
```javascript
// في قائمة المنتجات
product.icons.forEach(icon => {
    console.log(icon.name); // "جديد"
    console.log(icon.image); // "http://..."
    console.log(icon.description); // "منتج جديد"
});
```

### Example HTML
```html
<div class="product-card">
    <div class="product-icons">
        <img v-for="icon in product.icons" 
             :key="icon.id"
             :src="icon.image" 
             :alt="icon.name"
             :title="icon.description" />
    </div>
    <h3>{{ product.name }}</h3>
    <p>{{ product.price }}</p>
</div>
```
