# توثيق API التقارير الإدارية

## نظرة عامة
يوفر النظام مجموعة شاملة من التقارير للإدارة لتحليل الأداء والمبيعات.

---

## 1. تقرير المبيعات (Sales Report)

### Endpoint
```
GET {{base_url}}/admin/reports/sales
```

### المعاملات (Query Parameters)
| المعامل | النوع | مطلوب | الوصف |
|---------|------|-------|-------|
| from_date | date | لا | تاريخ البداية (YYYY-MM-DD) |
| to_date | date | لا | تاريخ النهاية (YYYY-MM-DD) |
| governorate_id | integer | لا | معرف المحافظة |
| city_id | integer | لا | معرف المدينة |
| vendor_id | integer | لا | معرف البائع |
| shop_id | integer | لا | معرف المتجر |
| payment_method | string | لا | طريقة الدفع |

### مثال على الطلب
```
GET {{base_url}}/admin/reports/sales?from_date=2026-01-01&to_date=2026-12-31&vendor_id=5
```

### الاستجابة
```json
{
  "status": true,
  "message": "Sales report retrieved successfully",
  "data": {
    "total_orders": 150,
    "total_revenue": 45000.50,
    "total_delivery_fees": 3000.00,
    "total_discounts": 2500.00,
    "average_order_value": 300.00,
    "orders": [
      {
        "order_code": "ORD-2026-001",
        "user": "أحمد محمد",
        "total": 350.00,
        "delivery_price": 20.00,
        "delivered_at": "2026-01-15 14:30:00"
      }
    ]
  }
}
```

### التصدير
```
GET {{base_url}}/admin/reports/export/sales?format=excel&from_date=2026-01-01&to_date=2026-12-31
GET {{base_url}}/admin/reports/export/sales?format=pdf&from_date=2026-01-01&to_date=2026-12-31
```

---

## 2. تقرير حركة المنتجات (Product Movement Report)

### Endpoint
```
GET {{base_url}}/admin/reports/product-movement
```

### المعاملات
| المعامل | النوع | مطلوب | الوصف |
|---------|------|-------|-------|
| from_date | date | لا | تاريخ البداية |
| to_date | date | لا | تاريخ النهاية |
| category_id | integer | لا | معرف الفئة |

### مثال على الطلب
```
GET {{base_url}}/admin/reports/product-movement?from_date=2026-01-01&to_date=2026-12-31&category_id=3
```

### الاستجابة
```json
{
  "status": true,
  "message": "Product movement report retrieved successfully",
  "data": {
    "top_selling": [
      {
        "product_id": 25,
        "product_name": {
          "ar": "منتج أ",
          "en": "Product A"
        },
        "total_sold": 500,
        "total_revenue": 15000.00
      }
    ],
    "least_selling": [
      {
        "product_id": 78,
        "product_name": {
          "ar": "منتج ب",
          "en": "Product B"
        },
        "total_sold": 5,
        "total_revenue": 150.00
      }
    ],
    "inactive_products": [
      {
        "id": 120,
        "name": {
          "ar": "منتج غير نشط",
          "en": "Inactive Product"
        },
        "sku": "SKU-120",
        "category": {
          "ar": "فئة أ",
          "en": "Category A"
        }
      }
    ]
  }
}
```

### التصدير
```
GET {{base_url}}/admin/reports/export/product-movement?format=excel
GET {{base_url}}/admin/reports/export/product-movement?format=pdf
```

---

## 3. تقرير أداء البائع (Vendor Performance Report)

### Endpoint
```
GET {{base_url}}/admin/reports/vendor-performance/{vendor_id}
```

### المعاملات
| المعامل | النوع | مطلوب | الوصف |
|---------|------|-------|-------|
| vendor_id | integer | نعم | معرف البائع (في URL) |
| from_date | date | لا | تاريخ البداية |
| to_date | date | لا | تاريخ النهاية |

### مثال على الطلب
```
GET {{base_url}}/admin/reports/vendor-performance/5?from_date=2026-01-01&to_date=2026-12-31
```

### الاستجابة
```json
{
  "status": true,
  "message": "Vendor performance report retrieved successfully",
  "data": {
    "vendor_id": 5,
    "vendor_name": {
      "ar": "بائع أ",
      "en": "Vendor A"
    },
    "total_sales": 125000.00,
    "total_orders": 450,
    "average_order_value": 277.78,
    "total_shops": 3,
    "active_shops": 2,
    "average_rating": 4.5,
    "total_ratings": 120,
    "customer_satisfaction": 85.50
  }
}
```

### التصدير
```
GET {{base_url}}/admin/reports/export/vendor-performance/{vendor_id}?format=excel
GET {{base_url}}/admin/reports/export/vendor-performance/{vendor_id}?format=pdf
```

---

## 4. تقرير أداء السائق (Driver Performance Report)

### Endpoint
```
GET {{base_url}}/admin/reports/driver-performance/{driver_id}
```

### المعاملات
| المعامل | النوع | مطلوب | الوصف |
|---------|------|-------|-------|
| driver_id | integer | نعم | معرف السائق (في URL) |
| from_date | date | لا | تاريخ البداية |
| to_date | date | لا | تاريخ النهاية |

### مثال على الطلب
```
GET {{base_url}}/admin/reports/driver-performance/1?from_date=2026-01-01&to_date=2026-12-31
```

### الاستجابة
```json
{
  "status": true,
  "message": "Driver performance report retrieved successfully",
  "data": {
    "driver_id": 1,
    "driver_name": {
      "ar": "سائق أ",
      "en": "Driver A"
    },
    "total_orders": 320,
    "total_earnings": 6400.00,
    "average_delivery_time_minutes": 25.50,
    "average_rating": 4.7,
    "total_ratings": 280,
    "total_complaints": 3
  }
}
```

### التصدير
```
GET {{base_url}}/admin/reports/export/driver-performance/{driver_id}?format=excel
GET {{base_url}}/admin/reports/export/driver-performance/{driver_id}?format=pdf
```

---

## 5. تقرير المبيعات حسب الموقع (Sales by Location Report)

### Endpoint
```
GET {{base_url}}/admin/reports/sales-by-location
```

### المعاملات
| المعامل | النوع | مطلوب | الوصف |
|---------|------|-------|-------|
| from_date | date | لا | تاريخ البداية |
| to_date | date | لا | تاريخ النهاية |

### مثال على الطلب
```
GET {{base_url}}/admin/reports/sales-by-location?from_date=2026-01-01&to_date=2026-12-31
```

### الاستجابة
```json
{
  "status": true,
  "message": "Sales by location report retrieved successfully",
  "data": {
    "by_governorate": [
      {
        "governorate": {
          "ar": "دمشق",
          "en": "Damascus"
        },
        "total_orders": 500,
        "total_revenue": 150000.00
      }
    ],
    "by_city": [
      {
        "city": {
          "ar": "دمشق",
          "en": "Damascus"
        },
        "total_orders": 500,
        "total_revenue": 150000.00
      }
    ]
  }
}
```

---

## 6. تقرير المبيعات حسب الفئة (Sales by Category Report)

### Endpoint
```
GET {{base_url}}/admin/reports/sales-by-category
```

### المعاملات
| المعامل | النوع | مطلوب | الوصف |
|---------|------|-------|-------|
| from_date | date | لا | تاريخ البداية |
| to_date | date | لا | تاريخ النهاية |

### مثال على الطلب
```
GET {{base_url}}/admin/reports/sales-by-category?from_date=2026-01-01&to_date=2026-12-31
```

### الاستجابة
```json
{
  "status": true,
  "message": "Sales by category report retrieved successfully",
  "data": {
    "by_category": [
      {
        "category": {
          "ar": "إلكترونيات",
          "en": "Electronics"
        },
        "total_quantity": 1500,
        "total_revenue": 450000.00
      }
    ]
  }
}
```

---

## ملاحظات مهمة

### التصفية حسب التاريخ
- جميع التقارير تدعم التصفية حسب التاريخ باستخدام `from_date` و `to_date`
- صيغة التاريخ: `YYYY-MM-DD` (مثال: 2026-01-01)
- إذا لم يتم تحديد تاريخ، سيتم عرض جميع البيانات

### التصدير
- جميع التقارير تدعم التصدير بصيغتين:
  - Excel: `format=excel`
  - PDF: `format=pdf`
- يتم تطبيق نفس الفلاتر المستخدمة في عرض التقرير على التصدير
- ملفات PDF تدعم اللغة العربية بشكل كامل باستخدام mPDF

### الأداء
- التقارير تعتمد على الطلبات المكتملة فقط (status = DELIVERED)
- يتم حساب المتوسطات والإحصائيات بشكل ديناميكي
- للحصول على أفضل أداء، يُنصح بتحديد نطاق تاريخ محدد

### الصلاحيات
- جميع endpoints التقارير تتطلب صلاحيات إدارية
- يجب إرسال token المصادقة في header الطلب

### معدل رضا العملاء (Customer Satisfaction)
- يتم حسابه كنسبة مئوية من التقييمات 4 نجوم فأكثر
- الصيغة: (عدد التقييمات >= 4) / (إجمالي التقييمات) × 100

---

## الإصلاحات المطبقة

### مشاكل تم حلها:
1. ✅ إصلاح علاقة `shopProductVariants` → `shopVariants` في ProductVariant model
2. ✅ إضافة علاقة `orderItems()` المفقودة في ShopProductVariant model
3. ✅ إصلاح استعلام الشكاوى في تقرير أداء السائق (من خلال علاقة Order)
4. ✅ إصلاح مشكلة عرض الأحرف العربية في PDF باستخدام mPDF
5. ✅ إضافة دعم كامل للغة العربية في جميع تقارير PDF

### التحسينات:
- استخدام mPDF بدلاً من DomPDF لدعم أفضل للعربية
- إضافة `autoScriptToLang` و `autoLangToFont` للتعامل التلقائي مع اللغات
- استخدام font DejaVu Sans الذي يدعم العربية بشكل كامل
- تحسين تنسيق جداول PDF للعرض من اليمين لليسار
- ترجمة جميع عناوين وحقول PDF للعربية
