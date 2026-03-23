# Recipes Filters API - User

## Base URL
```
GET /api/user/recipes
```

## Authentication
```
Authorization: Bearer {user_token}
```

---

## Available Filters

### 1. Search Filter
**Parameter:** `search`  
**Type:** string  
**Description:** البحث في اسم ووصف الوصفة

**Example:**
```
GET /api/user/recipes?search=كبسة
GET /api/user/recipes?search=rice
```

---

### 2. Discount Filters
**Parameters:**
- `discount_min` (numeric): الحد الأدنى للخصم
- `discount_max` (numeric): الحد الأقصى للخصم
- `has_discount` (boolean): وصفات عليها خصم فقط

**Examples:**
```
# وصفات بخصم 10% فما فوق
GET /api/user/recipes?discount_min=10

# وصفات بخصم بين 10% و 50%
GET /api/user/recipes?discount_min=10&discount_max=50

# وصفات عليها خصم فقط
GET /api/user/recipes?has_discount=true
```

---

### 3. Rating Filters
**Parameters:**
- `rating_min` (numeric, 0-5): الحد الأدنى للتقييم
- `rating_max` (numeric, 0-5): الحد الأقصى للتقييم

**Examples:**
```
# وصفات بتقييم 4 فما فوق
GET /api/user/recipes?rating_min=4

# وصفات بتقييم بين 4 و 5
GET /api/user/recipes?rating_min=4&rating_max=5

# وصفات بتقييم ممتاز (4.5+)
GET /api/user/recipes?rating_min=4.5
```

---

### 4. Serves Filter
**Parameter:** `serves`  
**Type:** string  
**Description:** عدد الأشخاص الذين تكفيهم الوصفة (بحث جزئي)

**Examples:**
```
# وصفات تكفي شخصين
GET /api/user/recipes?serves=2

# وصفات تكفي 2-4 أشخاص
GET /api/user/recipes?serves=2-4

# وصفات تكفي 4 أشخاص أو أكثر
GET /api/user/recipes?serves=4
```

---

### 5. Prepare Time Filter
**Parameter:** `prepare_time`  
**Type:** string  
**Description:** وقت التحضير بالدقائق (بحث جزئي)

**Examples:**
```
# وصفات تحضيرها 25 دقيقة
GET /api/user/recipes?prepare_time=25

# وصفات تحضيرها 30 دقيقة
GET /api/user/recipes?prepare_time=30

# وصفات سريعة (20 دقيقة)
GET /api/user/recipes?prepare_time=20
```

---

### 6. Type Filter (الفلترة حسب النوع)
**Parameter:** `type`  
**Type:** enum  
**Values:** `newest`, `popular`, `top_rated`, `on_sale`  
**Description:** فلترة وترتيب حسب نوع معين

#### Type Options:

**a) newest - الأحدث**
```
GET /api/user/recipes?type=newest
```
يعرض الوصفات الأحدث أولاً (حسب تاريخ الإنشاء)

**b) popular - الأكثر شعبية**
```
GET /api/user/recipes?type=popular
```
يعرض الوصفات الأكثر طلباً (حسب عدد الطلبات)

**c) top_rated - الأعلى تقييماً**
```
GET /api/user/recipes?type=top_rated
```
يعرض الوصفات بتقييم 4+ فقط، مرتبة من الأعلى للأقل

**d) on_sale - عليها خصم**
```
GET /api/user/recipes?type=on_sale
```
يعرض الوصفات التي عليها خصم فقط، مرتبة من الأعلى خصماً للأقل

---

### 7. Sort By (الترتيب)
**Parameter:** `sort_by`  
**Type:** enum  
**Values:** `newest`, `oldest`, `price_asc`, `price_desc`, `rating_desc`, `rating_asc`, `popular`, `discount_desc`

#### Sort Options:

**a) newest - الأحدث**
```
GET /api/user/recipes?sort_by=newest
```

**b) oldest - الأقدم**
```
GET /api/user/recipes?sort_by=oldest
```

**c) price_asc - الأرخص سعراً**
```
GET /api/user/recipes?sort_by=price_asc
```

**d) price_desc - الأغلى سعراً**
```
GET /api/user/recipes?sort_by=price_desc
```

**e) rating_desc - الأعلى تقييماً**
```
GET /api/user/recipes?sort_by=rating_desc
```

**f) rating_asc - الأقل تقييماً**
```
GET /api/user/recipes?sort_by=rating_asc
```

**g) popular - الأكثر شعبية**
```
GET /api/user/recipes?sort_by=popular
```

**h) discount_desc - الأعلى خصماً**
```
GET /api/user/recipes?sort_by=discount_desc
```

---

## Combined Filters Examples

### Example 1: وصفات شعبية بتقييم عالي
```
GET /api/user/recipes?type=popular&rating_min=4
```

### Example 2: وصفات سريعة التحضير عليها خصم
```
GET /api/user/recipes?prepare_time=20&has_discount=true
```

### Example 3: وصفات تكفي 4 أشخاص بتقييم ممتاز
```
GET /api/user/recipes?serves=4&rating_min=4.5&sort_by=rating_desc
```

### Example 4: وصفات بخصم كبير للعائلات
```
GET /api/user/recipes?discount_min=20&serves=4&type=on_sale
```

### Example 5: أحدث الوصفات السريعة
```
GET /api/user/recipes?type=newest&prepare_time=25
```

### Example 6: وصفات شعبية بسعر منخفض
```
GET /api/user/recipes?type=popular&sort_by=price_asc
```

### Example 7: البحث عن كبسة بتقييم عالي
```
GET /api/user/recipes?search=كبسة&rating_min=4&sort_by=rating_desc
```

### Example 8: وصفات للشخصين بخصم
```
GET /api/user/recipes?serves=2&has_discount=true&sort_by=discount_desc
```

---

## Pagination

**Parameters:**
- `page` (int): رقم الصفحة (default: 1)
- `per_page` (int): عدد العناصر في الصفحة (default: 10)

**Example:**
```
GET /api/user/recipes?type=popular&page=2&per_page=20
```

---

## Response Format

```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": "أرز الكبسة",
        "description": "أرز كبسة بالدجاج مع التوابل الشرقية",
        "image": "https://...",
        "rating": 4.5,
        "discount": 20,
        "serves": "2-4",
        "prepare_time": "25",
        "delivery_price": 5.00,
        "original_price": 50.00,
        "price_after_discount": 40.00,
        "orders_count": 150,
        "is_favorite": false
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 10,
      "total": 50
    }
  }
}
```

---

## Filter Priority

عند استخدام فلاتر متعددة، يتم تطبيقها بالترتيب التالي:

1. **Search** - البحث في الاسم والوصف
2. **Discount Filters** - فلترة حسب الخصم
3. **Rating Filters** - فلترة حسب التقييم
4. **Serves Filter** - فلترة حسب عدد الأشخاص
5. **Prepare Time Filter** - فلترة حسب وقت التحضير
6. **Type Filter** - فلترة وترتيب حسب النوع
7. **Sort By** - ترتيب إضافي (إذا لم يكن هناك type)

---

## Best Practices

### 1. استخدم `type` للفلترة السريعة
```
# بدلاً من
GET /api/user/recipes?rating_min=4&sort_by=rating_desc

# استخدم
GET /api/user/recipes?type=top_rated
```

### 2. دمج الفلاتر للنتائج الدقيقة
```
GET /api/user/recipes?type=popular&serves=2-4&rating_min=4
```

### 3. استخدم `has_discount` بدلاً من `discount_min=1`
```
# بدلاً من
GET /api/user/recipes?discount_min=1

# استخدم
GET /api/user/recipes?has_discount=true
```

### 4. البحث مع الفلاتر
```
GET /api/user/recipes?search=برياني&type=top_rated&serves=3
```

---

## Common Use Cases

### حالة 1: الصفحة الرئيسية - عرض الوصفات الشعبية
```
GET /api/user/recipes?type=popular&per_page=10
```

### حالة 2: قسم العروض - وصفات عليها خصم
```
GET /api/user/recipes?type=on_sale&per_page=20
```

### حالة 3: وصفات سريعة للعشاء
```
GET /api/user/recipes?prepare_time=25&serves=2-4&type=top_rated
```

### حالة 4: وصفات للعائلات الكبيرة
```
GET /api/user/recipes?serves=4&sort_by=popular
```

### حالة 5: وصفات اقتصادية
```
GET /api/user/recipes?sort_by=price_asc&rating_min=3.5
```

### حالة 6: اكتشف وصفات جديدة
```
GET /api/user/recipes?type=newest&rating_min=4
```

---

## Notes

1. **Type vs Sort By:**
   - `type` يجمع بين الفلترة والترتيب
   - `sort_by` للترتيب فقط
   - إذا استخدمت كلاهما، `sort_by` يطبق بعد `type`

2. **Partial Matching:**
   - `serves` و `prepare_time` يستخدمان بحث جزئي (LIKE)
   - مثال: `serves=2` يطابق "2", "2-4", "2-3"

3. **Default Behavior:**
   - بدون فلاتر: يعرض جميع الوصفات مرتبة من الأحدث
   - مع `type`: يطبق الفلترة والترتيب الخاص بالنوع
   - مع `sort_by` فقط: يطبق الترتيب المحدد

4. **Rating Range:**
   - التقييم من 0 إلى 5
   - يمكن استخدام أرقام عشرية (مثل: 4.5)

5. **Discount:**
   - الخصم بالنسبة المئوية (0-100)
   - `has_discount=true` يفلتر الوصفات بخصم > 0
