# مثال كامل لإنشاء سلة مجدولة من الأدمن

## API Endpoint
```
POST /api/admin/scheduled-baskets
```

## Request Headers
```
Authorization: Bearer {admin_token}
Content-Type: multipart/form-data
Accept: application/json
```

## Request Body (Form Data)

### البيانات الأساسية
```
name[en] = Weekly Grocery Box
name[ar] = صندوق البقالة الأسبوعي
category_id = 10
image = (binary file)
discount = 15
discount_type = percent
delivery_price = 50
is_schedule = 1
```

### المنتجات (Items)
```
items[0][product_id] = 1
items[0][variant_id] = 1
items[0][shop_product_variant_id] = 1
items[0][quantity] = 2
items[0][is_required] = 1
items[0][is_extra] = 0
items[0][min_quantity] = 1
items[0][max_quantity] = 5

items[1][product_id] = 3
items[1][variant_id] = 5
items[1][shop_product_variant_id] = 5
items[1][quantity] = 1
items[1][is_required] = 1
items[1][is_extra] = 0
items[1][min_quantity] = 1
items[1][max_quantity] = 3

items[2][product_id] = 6
items[2][variant_id] = 12
items[2][shop_product_variant_id] = 12
items[2][quantity] = 1
items[2][is_required] = 0
items[2][is_extra] = 1
items[2][min_quantity] = 0
items[2][max_quantity] = 2
```

### الجدولات (Schedules)
```
schedules[0][scheduled_basket_id] = 2
schedules[1][scheduled_basket_id] = 5
schedules[2][scheduled_basket_id] = 8
```

### الشارات (Badges)
```
badges[0][id] = 1
badges[0][position] = top

badges[1][id] = 3
badges[1][position] = bottom
```

## مثال كامل بصيغة cURL

```bash
curl -X POST "https://tikmool.octopus-software.online/api/admin/scheduled-baskets" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Accept: application/json" \
  -F "name[en]=Weekly Grocery Box" \
  -F "name[ar]=صندوق البقالة الأسبوعي" \
  -F "category_id=10" \
  -F "image=@/path/to/basket-image.jpg" \
  -F "discount=15" \
  -F "discount_type=percent" \
  -F "delivery_price=50" \
  -F "is_schedule=1" \
  -F "items[0][product_id]=1" \
  -F "items[0][variant_id]=1" \
  -F "items[0][shop_product_variant_id]=1" \
  -F "items[0][quantity]=2" \
  -F "items[0][is_required]=1" \
  -F "items[0][is_extra]=0" \
  -F "items[0][min_quantity]=1" \
  -F "items[0][max_quantity]=5" \
  -F "items[1][product_id]=3" \
  -F "items[1][variant_id]=5" \
  -F "items[1][shop_product_variant_id]=5" \
  -F "items[1][quantity]=1" \
  -F "items[1][is_required]=1" \
  -F "items[1][is_extra]=0" \
  -F "items[1][min_quantity]=1" \
  -F "items[1][max_quantity]=3" \
  -F "items[2][product_id]=6" \
  -F "items[2][variant_id]=12" \
  -F "items[2][shop_product_variant_id]=12" \
  -F "items[2][quantity]=1" \
  -F "items[2][is_required]=0" \
  -F "items[2][is_extra]=1" \
  -F "items[2][min_quantity]=0" \
  -F "items[2][max_quantity]=2" \
  -F "schedules[0][scheduled_basket_id]=2" \
  -F "schedules[1][scheduled_basket_id]=5" \
  -F "schedules[2][scheduled_basket_id]=8" \
  -F "badges[0][id]=1" \
  -F "badges[0][position]=top" \
  -F "badges[1][id]=3" \
  -F "badges[1][position]=bottom"
```

## مثال بصيغة JavaScript (Axios)

```javascript
const formData = new FormData();

// البيانات الأساسية
formData.append('name[en]', 'Weekly Grocery Box');
formData.append('name[ar]', 'صندوق البقالة الأسبوعي');
formData.append('category_id', '10');
formData.append('image', imageFile); // File object
formData.append('discount', '15');
formData.append('discount_type', 'percent');
formData.append('delivery_price', '50');
formData.append('is_schedule', '1');

// المنتجات
const items = [
  {
    product_id: 1,
    variant_id: 1,
    shop_product_variant_id: 1,
    quantity: 2,
    is_required: 1,
    is_extra: 0,
    min_quantity: 1,
    max_quantity: 5
  },
  {
    product_id: 3,
    variant_id: 5,
    shop_product_variant_id: 5,
    quantity: 1,
    is_required: 1,
    is_extra: 0,
    min_quantity: 1,
    max_quantity: 3
  },
  {
    product_id: 6,
    variant_id: 12,
    shop_product_variant_id: 12,
    quantity: 1,
    is_required: 0,
    is_extra: 1,
    min_quantity: 0,
    max_quantity: 2
  }
];

items.forEach((item, index) => {
  Object.keys(item).forEach(key => {
    formData.append(`items[${index}][${key}]`, item[key]);
  });
});

// الجدولات
const schedules = [
  { scheduled_basket_id: 2 },
  { scheduled_basket_id: 5 },
  { scheduled_basket_id: 8 }
];

schedules.forEach((schedule, index) => {
  formData.append(`schedules[${index}][scheduled_basket_id]`, schedule.scheduled_basket_id);
});

// الشارات
const badges = [
  { id: 1, position: 'top' },
  { id: 3, position: 'bottom' }
];

badges.forEach((badge, index) => {
  formData.append(`badges[${index}][id]`, badge.id);
  formData.append(`badges[${index}][position]`, badge.position);
});

// إرسال الطلب
axios.post('/api/admin/scheduled-baskets', formData, {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'multipart/form-data'
  }
})
.then(response => {
  console.log('Success:', response.data);
})
.catch(error => {
  console.error('Error:', error.response.data);
});
```

## مثال بصيغة JSON (إذا كان الـ API يقبل JSON)

```json
POST /api/admin/scheduled-baskets

{
  "name": {
    "en": "Weekly Grocery Box",
    "ar": "صندوق البقالة الأسبوعي"
  },
  "category_id": 10,
  "discount": 15,
  "discount_type": "percent",
  "delivery_price": 50,
  "is_schedule": true,
  "items": [
    {
      "product_id": 1,
      "variant_id": 1,
      "shop_product_variant_id": 1,
      "quantity": 2,
      "is_required": true,
      "is_extra": false,
      "min_quantity": 1,
      "max_quantity": 5
    },
    {
      "product_id": 3,
      "variant_id": 5,
      "shop_product_variant_id": 5,
      "quantity": 1,
      "is_required": true,
      "is_extra": false,
      "min_quantity": 1,
      "max_quantity": 3
    },
    {
      "product_id": 6,
      "variant_id": 12,
      "shop_product_variant_id": 12,
      "quantity": 1,
      "is_required": false,
      "is_extra": true,
      "min_quantity": 0,
      "max_quantity": 2
    }
  ],
  "schedules": [
    { "scheduled_basket_id": 2 },
    { "scheduled_basket_id": 5 },
    { "scheduled_basket_id": 8 }
  ],
  "badges": [
    { "id": 1, "position": "top" },
    { "id": 3, "position": "bottom" }
  ]
}
```

## Response Example

```json
{
  "status": true,
  "message": "تمت العملية بنجاح.",
  "data": {
    "id": 5,
    "category": {
      "id": 10,
      "name": "صندوق البقالة الأسبوعي"
    },
    "name": {
      "ar": "صندوق البقالة الأسبوعي",
      "en": "Weekly Grocery Box"
    },
    "image": "https://tikmool.octopus-software.online/storage/baskets/xyz.png",
    "num_varieties": 3,
    "offer_ends_at": null,
    "original_price": 500,
    "discount": "15.00",
    "discount_type": "percent",
    "discount_amount": 75,
    "final_price": 425,
    "rating": 0,
    "average_rating": 0,
    "num_sold": 0,
    "is_on_offer": false,
    "delivery_price": 50,
    "is_schedule": true,
    "items": [
      {
        "id": 19,
        "product_id": 1,
        "variant_id": 1,
        "shop_product_variant_id": 1,
        "product": {
          "id": 1,
          "name": "أرز بسمتي فاخر",
          "image": "https://tikmool.octopus-software.online/storage/product/image8.jpg",
          "is_instant_delivery": 1
        },
        "variant": ["#fc0303", "وسط"],
        "quantity": 2,
        "unit_price": 193,
        "subtotal": 386,
        "is_required": true,
        "is_extra": false,
        "min_quantity": 1,
        "max_quantity": 5
      },
      {
        "id": 20,
        "product_id": 3,
        "variant_id": 5,
        "shop_product_variant_id": 5,
        "product": {
          "id": 3,
          "name": "عدس أحمر",
          "image": "https://tikmool.octopus-software.online/storage/product/image5.jpg",
          "is_instant_delivery": 1
        },
        "variant": ["أحمر", "1 كغ"],
        "quantity": 1,
        "unit_price": 80,
        "subtotal": 80,
        "is_required": true,
        "is_extra": false,
        "min_quantity": 1,
        "max_quantity": 3
      },
      {
        "id": 21,
        "product_id": 6,
        "variant_id": 12,
        "shop_product_variant_id": 12,
        "product": {
          "id": 6,
          "name": "برغل ناعم",
          "image": "https://tikmool.octopus-software.online/storage/product/image3.jpg",
          "is_instant_delivery": 0
        },
        "variant": ["ناعم", "500 غ"],
        "quantity": 1,
        "unit_price": 34,
        "subtotal": 34,
        "is_required": false,
        "is_extra": true,
        "min_quantity": 0,
        "max_quantity": 2
      }
    ],
    "extras": [],
    "schedules": [
      {
        "id": 2,
        "title": "كل أسبوع",
        "number_of_days": 7,
        "discount_type": null,
        "discount_value": null,
        "is_active": true,
        "created_at": "2026-03-20 10:00:00",
        "updated_at": "2026-03-20 10:00:00"
      },
      {
        "id": 5,
        "title": "كل أسبوعين",
        "number_of_days": 14,
        "discount_type": "percent",
        "discount_value": 5,
        "is_active": true,
        "created_at": "2026-03-20 10:00:00",
        "updated_at": "2026-03-20 10:00:00"
      },
      {
        "id": 8,
        "title": "كل شهر",
        "number_of_days": 30,
        "discount_type": "fixed",
        "discount_value": 20,
        "is_active": true,
        "created_at": "2026-03-20 10:00:00",
        "updated_at": "2026-03-20 10:00:00"
      }
    ],
    "badges": [
      {
        "id": 1,
        "name": "الأكثر مبيعاً",
        "icon": "https://tikmool.octopus-software.online/storage/badges/bestseller.png",
        "position": "top"
      },
      {
        "id": 3,
        "name": "عرض خاص",
        "icon": "https://tikmool.octopus-software.online/storage/badges/special.png",
        "position": "bottom"
      }
    ],
    "created_at": "2026-03-26 13:56:12",
    "updated_at": "2026-03-26 14:00:09"
  }
}
```

## شرح الحقول

### Items (المنتجات)
- `product_id`: معرف المنتج
- `variant_id`: معرف الـ variant
- `shop_product_variant_id`: معرف الـ shop variant (السعر والكمية)
- `quantity`: الكمية الافتراضية
- `is_required`: هل المنتج إجباري (1) أو اختياري (0)
- `is_extra`: هل المنتج إضافة (1) أو منتج أساسي (0)
- `min_quantity`: الحد الأدنى للكمية
- `max_quantity`: الحد الأقصى للكمية

### Schedules (الجدولات)
- `scheduled_basket_id`: معرف الجدولة من جدول `basket_schedules`
- يمكن إضافة أكثر من جدولة للسلة الواحدة
- كل جدولة لها:
  - `number_of_days`: عدد الأيام بين كل توصيل
  - `discount_type`: نوع الخصم (percent أو fixed)
  - `discount_value`: قيمة الخصم

### Badges (الشارات)
- `id`: معرف الشارة
- `position`: موقع الشارة (top أو bottom)

## ملاحظات مهمة

1. **الصورة**: يجب إرسالها كـ binary file في multipart/form-data
2. **الجدولات**: يمكن إضافة جدولة واحدة أو أكثر
3. **الشارات**: اختيارية، يمكن إضافة شارة واحدة أو أكثر
4. **Items vs Extras**: 
   - Items عادية: `is_extra=0, is_required=1`
   - Items إضافية: `is_extra=1, is_required=0`
5. **الخصم**: يطبق على السلة كاملة، وليس على المنتجات الفردية

## مثال مبسط (الحد الأدنى)

```bash
curl -X POST "https://tikmool.octopus-software.online/api/admin/scheduled-baskets" \
  -H "Authorization: Bearer {admin_token}" \
  -H "Accept: application/json" \
  -F "name[en]=Simple Basket" \
  -F "name[ar]=سلة بسيطة" \
  -F "category_id=10" \
  -F "image=@basket.jpg" \
  -F "is_schedule=1" \
  -F "items[0][product_id]=1" \
  -F "items[0][variant_id]=1" \
  -F "items[0][shop_product_variant_id]=1" \
  -F "items[0][quantity]=1" \
  -F "items[0][is_required]=1" \
  -F "items[0][is_extra]=0" \
  -F "items[0][min_quantity]=1" \
  -F "items[0][max_quantity]=10" \
  -F "schedules[0][scheduled_basket_id]=2" \
  -F "schedules[1][scheduled_basket_id]=5"
```

## Validation Rules

من `StoreRequest`:
- `name`: required, array with en/ar
- `category_id`: required, exists in categories
- `image`: required, image file
- `discount`: nullable, numeric
- `discount_type`: nullable, in:percent,fixed
- `delivery_price`: nullable, numeric
- `is_schedule`: required, boolean
- `items`: required, array, min:1
- `items.*.product_id`: required, exists
- `items.*.variant_id`: required, exists
- `items.*.shop_product_variant_id`: required, exists
- `items.*.quantity`: required, integer, min:1
- `items.*.is_required`: required, boolean
- `items.*.is_extra`: required, boolean
- `items.*.min_quantity`: required, integer, min:0
- `items.*.max_quantity`: required, integer, min:1
- `schedules`: nullable, array
- `schedules.*.scheduled_basket_id`: required, exists
- `badges`: nullable, array
- `badges.*.id`: required, exists
- `badges.*.position`: required, in:top,bottom
