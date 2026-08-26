# ميزة بلد المنشأ وبلد المبيع للمنتجات

## نظرة عامة

تم إضافة حقلين جديدين لجدول المنتجات:
1. **بلد المنشأ** (`country_id`) - من جدول `countries` الموجود
2. **بلد المبيع** (`sale_country_id`) - من جدول `sale_countries` الجديد

## التغييرات في قاعدة البيانات

### 1. جدول جديد: `sale_countries`

```sql
CREATE TABLE sale_countries (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name JSON NOT NULL,
    icon VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 2. تعديل جدول `products`

```sql
-- تم حذف
country JSON NULL

-- تم إضافة
country_id BIGINT NULL FOREIGN KEY REFERENCES countries(id)
sale_country_id BIGINT NULL FOREIGN KEY REFERENCES sale_countries(id)
```

## الـ Models

### SaleCountry Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SaleCountry extends Model
{
    use HasTranslations;

    protected $fillable = ['name', 'icon', 'is_active'];
    public array $translatable = ['name'];
    protected $casts = ['is_active' => 'boolean'];

    public function products()
    {
        return $this->hasMany(Product::class, 'sale_country_id');
    }

    public function getIconUrlAttribute()
    {
        return $this->icon ? asset('storage/' . $this->icon) : null;
    }
}
```

### Product Model - العلاقات الجديدة

```php
public function originCountry()
{
    return $this->belongsTo(Country::class, 'country_id');
}

public function saleCountry()
{
    return $this->belongsTo(SaleCountry::class, 'sale_country_id');
}
```

---

## API Endpoints

### إدارة بلدان المبيع (Sale Countries)

#### 1. عرض قائمة بلدان المبيع
```
GET /api/admin/sale-countries
```

**Query Parameters:**
- `page`: رقم الصفحة
- `per_page`: عدد العناصر
- `is_active`: فلترة حسب الحالة (0 أو 1)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "السعودية",
      "icon": "http://example.com/storage/sale-countries/sa.png",
      "is_active": true,
      "created_at": "2026-03-27T18:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 6
  }
}
```

#### 2. عرض بلد مبيع محدد
```
GET /api/admin/sale-countries/{id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": {
      "ar": "السعودية",
      "en": "Saudi Arabia"
    },
    "icon": "http://example.com/storage/sale-countries/sa.png",
    "is_active": true,
    "created_at": "2026-03-27T18:00:00.000000Z",
    "updated_at": "2026-03-27T18:00:00.000000Z"
  }
}
```

#### 3. إضافة بلد مبيع جديد
```
POST /api/admin/sale-countries
```

**Request Body (form-data):**
```
name[ar]: السعودية
name[en]: Saudi Arabia
is_active: 1
```

> ملاحظة: حقل `icon` لم يعد يُرفع من الداشبورد — يأتي من `SaleCountrySeeder` (إيموجي علم). سوريا هي الافتراضي عند إنشاء منتج بدون `sale_country_id`.

#### 4. تعديل بلد مبيع
```
PUT /api/admin/sale-countries/{id}
```

**Request Body (form-data):**
```
name[ar]: المملكة العربية السعودية
name[en]: Kingdom of Saudi Arabia
icon: [ملف صورة جديد - اختياري]
is_active: 1
```

#### 5. حذف بلد مبيع
```
DELETE /api/admin/sale-countries/{id}
```

---

## استخدام الحقول الجديدة في المنتجات

### إضافة منتج مع بلد المنشأ وبلد المبيع

**Request:**
```
POST /api/admin/products
```

**Body (form-data):**
```
category_id: 1
price: 15000
name[ar]: هاتف سامسونج
name[en]: Samsung Phone
description[ar]: وصف المنتج
description[en]: Product description

# بلد المنشأ (من جدول countries)
country_id: 5

# بلد المبيع (من جدول sale_countries)
sale_country_id: 1

media[0]: [صورة]
```

### تعديل منتج - تحديث البلدان

**Request:**
```
POST /api/admin/products/20
```

**Body (form-data):**
```
# تحديث بلد المنشأ
country_id: 3

# تحديث بلد المبيع
sale_country_id: 2
```

---

## Response المنتج مع البلدان

### OneResource (تفاصيل المنتج)

```json
{
  "success": true,
  "data": {
    "id": 20,
    "name": "هاتف سامسونج",
    "price": 15000,
    
    "origin_country": {
      "id": 5,
      "name": "كوريا الجنوبية"
    },
    
    "sale_country": {
      "id": 1,
      "name": "السعودية",
      "icon": "http://example.com/storage/sale-countries/sa.png"
    },
    
    ...
  }
}
```

### AllResource (قائمة المنتجات)

```json
{
  "success": true,
  "data": [
    {
      "id": 20,
      "name": "هاتف سامسونج",
      "price": 15000,
      "origin_country": "كوريا الجنوبية",
      "sale_country": "السعودية",
      ...
    }
  ]
}
```

---

## أمثلة Postman

### 1. إضافة بلد مبيع جديد

```
POST http://127.0.0.1:8000/api/admin/sale-countries

Headers:
Authorization: Bearer YOUR_TOKEN
Accept: application/json

Body (form-data):
name[ar]: السعودية
name[en]: Saudi Arabia
icon: [اختر ملف صورة]
is_active: 1
```

### 2. إضافة منتج مع البلدان

```
POST http://127.0.0.1:8000/api/admin/products

Body (form-data):
category_id: 1
price: 15000
name[ar]: آيفون 15
name[en]: iPhone 15
description[ar]: أحدث هاتف من آبل
description[en]: Latest Apple phone
country_id: 10
sale_country_id: 1
media[0]: [صورة]
```

### 3. تعديل منتج - تحديث البلدان فقط

```
POST http://127.0.0.1:8000/api/admin/products/20

Body (form-data):
country_id: 5
sale_country_id: 2
```

---

## Validation Rules

### SaleCountry

**Create:**
- `name.ar`: required|string|max:255
- `name.en`: required|string|max:255
- `icon`: nullable|image|max:2048
- `is_active`: nullable|boolean

**Update:**
- `name.ar`: nullable|string|max:255
- `name.en`: nullable|string|max:255
- `icon`: nullable|image|max:2048
- `is_active`: nullable|boolean

### Product

**Create & Update:**
- `country_id`: nullable|exists:countries,id
- `sale_country_id`: nullable|exists:sale_countries,id

---

## الفرق بين بلد المنشأ وبلد المبيع

| الميزة | بلد المنشأ (country_id) | بلد المبيع (sale_country_id) |
|--------|------------------------|------------------------------|
| الجدول | `countries` | `sale_countries` |
| الاستخدام | البلد الذي صُنع فيه المنتج | البلد الذي يُباع فيه المنتج |
| مثال | كوريا الجنوبية (سامسونج) | السعودية |
| الحقول | name, code, flag, etc. | name, icon, is_active |
| API | `/api/admin/countries` | `/api/admin/sale-countries` |

---

## Seeder

تم إنشاء Seeder لبلدان المبيع مع 6 دول خليجية:

```bash
php artisan db:seed --class=SaleCountrySeeder
```

البلدان المضافة:
1. السعودية
2. الإمارات
3. الكويت
4. قطر
5. البحرين
6. عمان

---

## ملاحظات مهمة

1. ✅ **حقل `country` القديم تم حذفه** - كان JSON، الآن استبدل بـ `country_id`
2. ✅ **كلا الحقلين اختياريين** - `nullable`
3. ✅ **العلاقات تُحمل تلقائياً** - في ProductService
4. ✅ **الصور تُخزن في** `storage/app/public/sale-country/`
5. ✅ **يمكن فلترة بلدان المبيع** - حسب `is_active`

---

## الملفات المضافة/المعدلة

### ملفات جديدة:
- `database/migrations/2026_03_27_184643_create_sale_countries_table.php`
- `database/migrations/2026_03_27_184718_add_country_fields_to_products_table.php`
- `app/Models/SaleCountry.php`
- `app/Http/Controllers/Admin/SaleCountry/SaleCountryCrudController.php`
- `app/Http/Requests/Admin/SaleCountry/FilterRequest.php`
- `app/Http/Requests/Admin/SaleCountry/StoreRequest.php`
- `app/Http/Requests/Admin/SaleCountry/UpdateRequest.php`
- `app/Http/Resources/Admin/SaleCountry/AllResource.php`
- `app/Http/Resources/Admin/SaleCountry/OneResource.php`
- `app/Services/Admin/SaleCountryService.php`
- `database/seeders/SaleCountrySeeder.php`

### ملفات معدلة:
- `app/Models/Product.php`
- `app/Services/Admin/ProductService.php`
- `app/Http/Requests/Admin/Product/StoreRequest.php`
- `app/Http/Requests/Admin/Product/UpdateRequest.php`
- `app/Http/Resources/Admin/Product/OneResource.php`
- `app/Http/Resources/Admin/Product/AllResource.php`
- `routes/api/admin.php`

---

**تاريخ الإنشاء**: 27 مارس 2026
