# مثال صحيح لإنشاء سلة مجدولة (بناءً على الـ Request الفعلي)

## ملاحظة مهمة
الـ Request الحالي يدعم طريقتين:
1. **schedule** (مفرد): لإنشاء جدولة واحدة افتراضية
2. **schedules** (جمع): لربط السلة بجدولات موجودة مسبقاً

## الطريقة الأولى: إنشاء سلة مع جدولة واحدة (schedule)

### Request Body
```
POST /api/admin/scheduled-baskets

name[en] = Weekly Grocery Box
name[ar] = صندوق البقالة الأسبوعي
category_id = 10
image = (binary file)
discount = 15
discount_type = percentage
delivery_price = 50

schedule[title][en] = Weekly Delivery
schedule[title][ar] = توصيل أسبوعي
schedule[number_of_days] = 7
schedule[discount_type] = percentage
schedule[discount_value] = 5
schedule[is_active] = 1
schedule[is_default] = 1

items[0][shop_product_variant_id] = 1
items[0][shop_product_variant_ids][0] = 2
items[0][shop_product_variant_ids][1] = 3
items[0][quantity] = 2
items[0][is_required] = 1
items[0][is_extra] = 0
items[0][min_quantity] = 1
items[0][max_quantity] = 5

items[1][shop_product_variant_id] = 5
items[1][quantity] = 1
items[1][is_required] = 1
items[1][is_extra] = 0
items[1][min_quantity] = 1
items[1][max_quantity] = 3

badges[0][id] = 1
badges[0][position] = top
```

### cURL Example
```bash
curl -X POST "https://tikmool.octopus-software.online/api/admin/scheduled-baskets" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Accept: application/json" \
  -F "name[en]=Weekly Grocery Box" \
  -F "name[ar]=صندوق البقالة الأسبوعي" \
  -F "category_id=10" \
  -F "image=@basket.jpg" \
  -F "discount=15" \
  -F "discount_type=percentage" \
  -F "delivery_price=50" \
  -F "schedule[title][en]=Weekly Delivery" \
  -F "schedule[title][ar]=توصيل أسبوعي" \
  -F "schedule[number_of_days]=7" \
  -F "schedule[discount_type]=percentage" \
  -F "schedule[discount_value]=5" \
  -F "schedule[is_active]=1" \
  -F "schedule[is_default]=1" \
  -F "items[0][shop_product_variant_id]=1" \
  -F "items[0][shop_product_variant_ids][0]=2" \
  -F "items[0][shop_product_variant_ids][1]=3" \
  -F "items[0][quantity]=2" \
  -F "items[0][is_required]=1" \
  -F "items[0][is_extra]=0" \
  -F "items[0][min_quantity]=1" \
  -F "items[0][max_quantity]=5" \
  -F "items[1][shop_product_variant_id]=5" \
  -F "items[1][quantity]=1" \
  -F "items[1][is_required]=1" \
  -F "items[1][is_extra]=0" \
  -F "items[1][min_quantity]=1" \
  -F "items[1][max_quantity]=3" \
  -F "badges[0][id]=1" \
  -F "badges[0][position]=top"
```

## الطريقة الثانية: ربط السلة بجدولات موجودة (schedules)

**ملاحظة**: هذه الطريقة تتطلب تعديل الـ Service لدعم `schedules` (جمع)

### Request Body
```
POST /api/admin/scheduled-baskets

name[en] = Weekly Grocery Box
name[ar] = صندوق البقالة الأسبوعي
category_id = 10
image = (binary file)
discount = 15
discount_type = percentage
delivery_price = 50

schedules[0][scheduled_basket_id] = 2
schedules[1][scheduled_basket_id] = 5
schedules[2][scheduled_basket_id] = 8

items[0][shop_product_variant_id] = 1
items[0][quantity] = 2
items[0][is_required] = 1
items[0][is_extra] = 0
items[0][min_quantity] = 1
items[0][max_quantity] = 5

badges[0][id] = 1
badges[0][position] = top
```

## JavaScript Example (Axios)

```javascript
const formData = new FormData();

// البيانات الأساسية
formData.append('name[en]', 'Weekly Grocery Box');
formData.append('name[ar]', 'صندوق البقالة الأسبوعي');
formData.append('category_id', '10');
formData.append('image', imageFile);
formData.append('discount', '15');
formData.append('discount_type', 'percentage');
formData.append('delivery_price', '50');

// الجدولة الافتراضية (طريقة 1)
formData.append('schedule[title][en]', 'Weekly Delivery');
formData.append('schedule[title][ar]', 'توصيل أسبوعي');
formData.append('schedule[number_of_days]', '7');
formData.append('schedule[discount_type]', 'percentage');
formData.append('schedule[discount_value]', '5');
formData.append('schedule[is_active]', '1');
formData.append('schedule[is_default]', '1');

// المنتجات
const items = [
  {
    shop_product_variant_id: 1,
    shop_product_variant_ids: [2, 3], // البدائل
    quantity: 2,
    is_required: 1,
    is_extra: 0,
    min_quantity: 1,
    max_quantity: 5
  },
  {
    shop_product_variant_id: 5,
    quantity: 1,
    is_required: 1,
    is_extra: 0,
    min_quantity: 1,
    max_quantity: 3
  }
];

items.forEach((item, index) => {
  formData.append(`items[${index}][shop_product_variant_id]`, item.shop_product_variant_id);
  
  if (item.shop_product_variant_ids) {
    item.shop_product_variant_ids.forEach((altId, altIndex) => {
      formData.append(`items[${index}][shop_product_variant_ids][${altIndex}]`, altId);
    });
  }
  
  formData.append(`items[${index}][quantity]`, item.quantity);
  formData.append(`items[${index}][is_required]`, item.is_required);
  formData.append(`items[${index}][is_extra]`, item.is_extra);
  formData.append(`items[${index}][min_quantity]`, item.min_quantity);
  formData.append(`items[${index}][max_quantity]`, item.max_quantity);
});

// الشارات
formData.append('badges[0][id]', '1');
formData.append('badges[0][position]', 'top');

// إرسال الطلب
axios.post('/api/admin/scheduled-baskets', formData, {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'multipart/form-data'
  }
})
.then(response => console.log('Success:', response.data))
.catch(error => console.error('Error:', error.response.data));
```

## شرح الفرق بين الطريقتين

### الطريقة الأولى (schedule - مفرد)
- تنشئ جدولة جديدة مع السلة
- الجدولة تُحفظ في جدول `basket_schedules`
- مناسبة عندما تريد إنشاء جدولة مخصصة لهذه السلة

### الطريقة الثانية (schedules - جمع)
- تربط السلة بجدولات موجودة مسبقاً
- الجدولات يجب أن تكون موجودة في جدول `basket_schedules`
- مناسبة عندما تريد استخدام جدولات معرّفة مسبقاً

## شرح الحقول المهمة

### shop_product_variant_id
- المنتج الأساسي (Primary Variant)
- **إجباري** لكل item
- يُستخدم لحساب السعر

### shop_product_variant_ids
- المنتجات البديلة (Alternative Variants)
- **اختياري**
- يسمح للمستخدم باختيار بديل للمنتج الأساسي
- مثال: إذا كان المنتج الأساسي "أرز بسمتي 5 كغ"، البدائل يمكن أن تكون "أرز بسمتي 2 كغ" أو "أرز مصري 5 كغ"

### is_required
- `true` (1): المنتج إجباري في السلة
- `false` (0): المنتج اختياري

### is_extra
- `true` (1): المنتج إضافة (Extra)
- `false` (0): المنتج أساسي

### min_quantity & max_quantity
- تحدد نطاق الكمية المسموح بها
- المستخدم يمكنه تعديل الكمية ضمن هذا النطاق

## Validation Rules

```php
'category_id' => 'required|integer|exists:categories,id'
'name' => 'required|array'
'name.*' => 'required|string|max:255'
'discount' => 'nullable|numeric|min:0'
'discount_type' => 'required|in:fixed,percentage'
'image' => 'nullable|image|mimes:jpeg,png,jpg,gif'
'delivery_price' => 'nullable|numeric|min:0'

// Schedule (مفرد)
'schedule' => 'required|array'
'schedule.title' => 'nullable|array'
'schedule.title.*' => 'nullable|string|max:255'
'schedule.number_of_days' => 'required|integer|min:1'
'schedule.discount_type' => 'nullable|in:fixed,percentage'
'schedule.discount_value' => 'nullable|numeric|min:0'
'schedule.is_active' => 'nullable|boolean'
'schedule.is_default' => 'nullable|boolean'

// Items
'items' => 'required|array|min:1'
'items.*.shop_product_variant_id' => 'required|integer|exists:shop_product_variants,id'
'items.*.shop_product_variant_ids' => 'nullable|array'
'items.*.shop_product_variant_ids.*' => 'required|integer|exists:shop_product_variants,id'
'items.*.quantity' => 'required|integer|min:1'
'items.*.is_required' => 'required|boolean'
'items.*.is_extra' => 'required|boolean'
'items.*.min_quantity' => 'nullable|integer|min:1'
'items.*.max_quantity' => 'nullable|integer|min:1'

// Badges
'badges' => 'nullable|array'
'badges.*.id' => 'required|integer|exists:badges,id'
'badges.*.position' => 'required|in:top,bottom'
```

## ملاحظات مهمة

1. **discount_type**: يجب أن يكون `percentage` أو `fixed` (ليس `percent`)
2. **schedule**: إجباري في الـ Request الحالي
3. **items**: يجب إضافة منتج واحد على الأقل
4. **shop_product_variant_ids**: اختياري، لكن إذا أضفته يجب أن يكون array من IDs صحيحة
5. **badges**: اختياري بالكامل
