# Toggle Status API Documentation

## Overview
API موحد لتفعيل/تعطيل أي Model في النظام من خلال تعديل حقل `is_active`.

## Endpoint
```
POST /api/admin/toggle-status
```

## Authentication
يتطلب: `auth:admin` middleware

## Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| type | string | Yes | اسم الـ Model (snake_case) |
| id | integer | Yes | معرف السجل |
| is_active | boolean | Yes | الحالة الجديدة (0 أو 1) |

## Supported Types

### Users & Authentication
- `user` - المستخدمين
- `vendor_user` - مستخدمي البائعين
- `store_user` - مستخدمي المتاجر
- `driver` - السائقين

### Vendors & Shops
- `vendor` - البائعين
- `vendor_package` - باقات البائعين
- `shop` - المحلات
- `store` - المتاجر

### Products & Categories
- `category` - الأصناف
- `brand` - العلامات التجارية
- `product` - المنتجات (uses `is_visible` instead)

### Content & Media
- `banner` - البانرات
- `faq` - الأسئلة الشائعة
- `recipe` - الوصفات
- `media` - الوسائط
- `icon` - الأيقونات

### Location
- `country` - البلدان
- `sale_country` - بلدان المبيع
- `governorate` - المحافظات
- `city` - المدن
- `area` - المناطق

### System
- `language` - اللغات
- `currency` - العملات
- `system_setting` - إعدادات النظام
- `service` - الخدمات
- `payment_method` - طرق الدفع

### Promotions & Rewards
- `promotion` - العروض الترويجية
- `coupon` - الكوبونات
- `badge` - الشارات
- `point_rule` - قواعد النقاط
- `package` - الباقات

### Schedules & Baskets
- `schedule` - الجداول
- `basket_schedule` - جداول السلل
- `user_basket_schedule` - جداول سلل المستخدمين

### Other
- `color` - الألوان

## Request Example

```json
{
  "type": "category",
  "id": 5,
  "is_active": 1
}
```

## Response Example

### Success Response (200)
```json
{
  "success": true,
  "message": "تم تحديث الحالة بنجاح",
  "data": {
    "id": 5,
    "type": "category",
    "is_active": true
  }
}
```

### Error Response (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "type": ["نوع غير صالح."],
    "id": ["حقل المعرف مطلوب."],
    "is_active": ["يجب أن تكون الحالة true أو false."]
  }
}
```

### Error Response (404)
```json
{
  "success": false,
  "message": "السجل غير موجود"
}
```

## Usage Examples

### تفعيل صنف
```bash
curl -X POST https://api.example.com/api/admin/toggle-status \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "category",
    "id": 5,
    "is_active": 1
  }'
```

### تعطيل بائع
```bash
curl -X POST https://api.example.com/api/admin/toggle-status \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "vendor",
    "id": 10,
    "is_active": 0
  }'
```

### تفعيل لون
```bash
curl -X POST https://api.example.com/api/admin/toggle-status \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "color",
    "id": 3,
    "is_active": 1
  }'
```

## Notes

1. الـ API يدعم 35 نوع مختلف من الـ Models
2. كل الـ Models المدعومة تحتوي على حقل `is_active` (boolean)
3. الـ Product يستخدم `is_visible` بدلاً من `is_active` (لكن لم يتم دعمه في هذا الـ API حالياً)
4. يتم التحقق من صحة الـ type قبل التنفيذ
5. يتم التحقق من وجود السجل قبل التعديل
6. الـ API آمن ويستخدم validation كامل

## Migration

تم إضافة حقل `is_active` للـ Models التالية:
- countries
- brands
- banners
- faqs
- services
- areas
- cities
- governorates
- badges
- colors

الـ Models الأخرى كانت تحتوي على الحقل مسبقاً.

