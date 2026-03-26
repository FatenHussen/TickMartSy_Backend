# Product Extras Feature

## Overview
نظام الإضافات (Extras) يسمح للمستخدمين بإضافة خيارات إضافية للمنتجات مع أسعار إضافية.

## Database Structure

### Table: `order_item_extras`
```sql
- id
- order_item_id (FK to order_items)
- product_extra_detail_id (FK to product_extra_details)
- price (سعر الإضافة وقت الطلب)
- timestamps
```

## API Request Format

### Create Order with Extras
```json
POST /api/user/orders

{
  "address_id": 1,
  "payment_method_id": 1,
  "cart_type": "default",
  "is_instant_delivery": true,
  "items": [
    {
      "shop_product_variant_id": 5,
      "quantity": 2,
      "extras": [1, 3, 5]  // IDs من product_extra_details
    }
  ]
}
```

### Preview Order with Extras
```json
POST /api/user/orders/preview

{
  "address_id": 1,
  "items": [
    {
      "shop_product_variant_id": 5,
      "quantity": 1,
      "extras": [2, 4]
    }
  ]
}
```

## API Response Format

### Order Item Response
```json
{
  "id": 1,
  "product_name": "Premium Basmati Rice",
  "quantity": 2,
  "price": 2500,
  "discount": 10,
  "price_after_discount": 2250,
  "extras_price": 500,
  "final_price_with_extras": 2750,
  "status": "pending",
  "variant_attributes": [...],
  "extras": [
    {
      "id": 1,
      "detail_key": {"en": "Extra Packaging", "ar": "تغليف إضافي"},
      "detail_value": {"en": "Premium Box", "ar": "علبة فاخرة"},
      "price": 300
    },
    {
      "id": 3,
      "detail_key": {"en": "Gift Wrap", "ar": "تغليف هدية"},
      "detail_value": {"en": "Yes", "ar": "نعم"},
      "price": 200
    }
  ]
}
```

## Price Calculation

### السعر النهائي للمنتج:
1. `price` = السعر الأساسي من shop_product_variant
2. `price_after_discount` = price - (price × discount%)
3. `extras_price` = مجموع أسعار الإضافات المختارة
4. `final_price_with_extras` = price_after_discount + extras_price

### السعر الإجمالي للـ order item:
```
total = final_price_with_extras × quantity
```

## Models & Relations

### OrderItem Model
```php
public function extras()
{
    return $this->hasMany(OrderItemExtra::class);
}
```

### OrderItemExtra Model
```php
public function orderItem()
{
    return $this->belongsTo(OrderItem::class);
}

public function extraDetail()
{
    return $this->belongsTo(ProductExtraDetail::class, 'product_extra_detail_id');
}
```

## Usage Flow

1. المستخدم يختار منتج ويضيف extras من القائمة المتاحة
2. يرسل request مع `extras` array فيها IDs الإضافات
3. النظام يحسب السعر النهائي = سعر المنتج + مجموع أسعار الـ extras
4. يحفظ الـ extras في جدول `order_item_extras`
5. يرجع في الـ response تفاصيل الإضافات والأسعار

## Notes
- الـ extras اختيارية (nullable)
- كل extra لها سعر خاص بها
- السعر يُحفظ في جدول order_item_extras كـ snapshot
- الـ extras تظهر في preview و order response
