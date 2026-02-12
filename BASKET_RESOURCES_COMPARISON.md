# Basket Resources Comparison

## نظرة عامة
تم إنشاء Resources منفصلة لكل نوع من السلال لأن كل نوع له خصائص مختلفة.

---

## 📁 هيكل الـ Resources

### 1. Regular Baskets (السلال العادية)
```
app/Http/Resources/Admin/Basket/
├── AllResource.php
├── OneResource.php
└── BasketItemResource.php
```

### 2. Scheduled Baskets (السلال المجدولة)
```
app/Http/Resources/Admin/ScheduledBasket/
├── AllResource.php
├── OneResource.php
├── ScheduledBasketItemResource.php
└── ScheduleResource.php
```

### 3. User Basket Schedules (سلات المستخدمين)
```
app/Http/Resources/Admin/UserBasketSchedule/
├── AllResource.php
└── OneResource.php

app/Http/Resources/UserBasketSchedule/
├── AllResource.php
├── OneResource.php
├── BasketItemResource.php
├── BasketItemVariantResource.php
└── ScheduleResource.php
```

---

## 🔍 الفروقات الرئيسية

### BasketItemResource vs ScheduledBasketItemResource

| الخاصية | Regular Basket | Scheduled Basket |
|---------|----------------|------------------|
| **shop_product_variant_id** | ✅ ID واحد | ❌ null |
| **shop_product_variant_ids** | ❌ null | ✅ مصفوفة من IDs |
| **is_required** | ❌ دائماً true | ✅ true أو false |
| **is_extra** | ❌ دائماً false | ✅ true أو false |
| **min_quantity** | ❌ افتراضي (1) | ✅ قابل للتخصيص |
| **max_quantity** | ❌ افتراضي (10) | ✅ قابل للتخصيص |
| **shop_variants** | ❌ لا يوجد | ✅ مصفوفة من shop variants |

---

## 📋 تفاصيل الـ Resources

### 1. Regular Basket Item Resource

```php
// app/Http/Resources/Admin/Basket/BasketItemResource.php
{
    "id": 1,
    "product_id": 5,
    "variant_id": 10,
    "shop_product_variant_id": 2,  // ID واحد فقط
    
    "product": {...},
    "variant": {...},
    "shop_variant": {...},  // shop variant واحد
    
    "quantity": 2,
    "unit_price": 15.00,
    "subtotal": 30.00
    
    // لا يوجد is_required, is_extra, min/max_quantity
}
```

### 2. Scheduled Basket Item Resource

```php
// app/Http/Resources/Admin/ScheduledBasket/ScheduledBasketItemResource.php
{
    "id": 1,
    "product_id": 5,
    "variant_id": 10,
    "shop_product_variant_id": null,
    "shop_product_variant_ids": [10, 11, 12],  // مصفوفة من IDs
    
    "product": {...},
    "variant": {...},
    "shop_variants": [  // مصفوفة من shop variants
        {
            "id": 10,
            "shop_id": 3,
            "shop_name": "Shop A",
            "price": 15.00,
            "quantity": 100
        },
        {
            "id": 11,
            "shop_id": 4,
            "shop_name": "Shop B",
            "price": 12.00,
            "quantity": 80
        }
    ],
    
    "quantity": 2,
    "unit_price": 27.00,  // مجموع أسعار shop_variants
    "subtotal": 54.00,
    
    // خصائص إضافية للسلال المجدولة
    "is_required": true,
    "is_extra": false,
    "min_quantity": 1,
    "max_quantity": 5
}
```

### 3. Schedule Resource (للسلال المجدولة فقط)

```php
// app/Http/Resources/Admin/ScheduledBasket/ScheduleResource.php
{
    "id": 1,
    "title": {
        "en": "Weekly Delivery",
        "ar": "توصيل أسبوعي"
    },
    "number_of_days": 7,
    "discount_type": "percentage",
    "discount_value": 5,
    "is_active": true,
    "created_at": "2026-02-11 10:00:00",
    "updated_at": "2026-02-11 10:00:00"
}
```

---

## 🎯 متى تستخدم كل Resource؟

### استخدم Regular Basket Resources عندما:
- ✅ السلة عادية (`is_schedule = 0`)
- ✅ كل item له shop_product_variant_id واحد
- ✅ جميع الـ items مطلوبة (is_required = true)
- ✅ لا يوجد items إضافية (is_extra = false)

### استخدم Scheduled Basket Resources عندما:
- ✅ السلة مجدولة (`is_schedule = 1`)
- ✅ كل item له shop_product_variant_ids (مصفوفة)
- ✅ الـ items قد تكون مطلوبة أو اختيارية
- ✅ يوجد items إضافية (extras)
- ✅ يوجد جدولة افتراضية

### استخدم User Basket Schedule Resources عندما:
- ✅ عرض سلات المستخدمين الشخصية
- ✅ من لوحة تحكم الأدمن (مع معلومات المستخدم)
- ✅ من تطبيق المستخدم (بدون معلومات المستخدم)

---

## 📊 مثال مقارنة كامل

### Regular Basket Response:
```json
{
    "id": 1,
    "name": "Breakfast Basket",
    "is_schedule": false,
    "items": [
        {
            "id": 1,
            "shop_product_variant_id": 2,
            "quantity": 2,
            "unit_price": 15.00,
            "subtotal": 30.00
        }
    ]
}
```

### Scheduled Basket Response:
```json
{
    "id": 2,
    "name": "Weekly Vegetables",
    "is_schedule": true,
    "items": [
        {
            "id": 2,
            "shop_product_variant_ids": [10, 11, 12],
            "shop_variants": [
                {"id": 10, "price": 15.00},
                {"id": 11, "price": 12.00},
                {"id": 12, "price": 10.00}
            ],
            "quantity": 1,
            "unit_price": 37.00,
            "subtotal": 37.00,
            "is_required": true,
            "is_extra": false,
            "min_quantity": 1,
            "max_quantity": 5
        }
    ],
    "schedules": [
        {
            "id": 1,
            "number_of_days": 7,
            "discount_value": 5
        }
    ]
}
```

---

## 🔧 الـ Services المستخدمة

### Regular Baskets:
```php
// app/Services/Admin/BasketService.php
protected $resource = \App\Http\Resources\Admin\Basket\OneResource::class;
protected $collection = \App\Http\Resources\Admin\Basket\AllResource::class;
```

### Scheduled Baskets:
```php
// app/Services/Admin/ScheduledBasketService.php
protected $resource = \App\Http\Resources\Admin\ScheduledBasket\OneResource::class;
protected $collection = \App\Http\Resources\Admin\ScheduledBasket\AllResource::class;
```

### User Basket Schedules (Admin):
```php
// app/Services/Admin/UserBasketScheduleService.php
protected $resource = \App\Http\Resources\Admin\UserBasketSchedule\OneResource::class;
protected $collection = \App\Http\Resources\Admin\UserBasketSchedule\AllResource::class;
```

### User Basket Schedules (User):
```php
// app/Services/User/UserBasketScheduleService.php
protected $resource = \App\Http\Resources\UserBasketSchedule\OneResource::class;
protected $collection = \App\Http\Resources\UserBasketSchedule\AllResource::class;
```

---

## ✅ الملخص

| النوع | Resource Path | الخصائص الفريدة |
|------|--------------|-----------------|
| **Regular Baskets** | `Admin/Basket/` | shop_product_variant_id واحد |
| **Scheduled Baskets** | `Admin/ScheduledBasket/` | shop_product_variant_ids (مصفوفة) + is_required + is_extra + schedules |
| **User Baskets (Admin)** | `Admin/UserBasketSchedule/` | نفس User + معلومات المستخدم |
| **User Baskets (User)** | `UserBasketSchedule/` | للمستخدم فقط |

---

## 🎉 الفائدة

- ✅ **وضوح**: كل نوع له Resources خاصة به
- ✅ **صيانة**: سهولة التعديل بدون التأثير على الأنواع الأخرى
- ✅ **مرونة**: إضافة خصائص جديدة لنوع معين بدون تعقيد
- ✅ **أمان**: عدم عرض بيانات غير مطلوبة

---

## 📝 ملاحظات مهمة

1. **لا تخلط بين الـ Resources**: استخدم الـ Resource المناسب لكل نوع
2. **shop_product_variant_ids**: مصفوفة فقط في Scheduled Baskets
3. **is_required & is_extra**: موجودة فقط في Scheduled Baskets
4. **schedules**: موجودة فقط في Scheduled Baskets
5. **user info**: موجودة فقط في Admin User Basket Schedule Resources
