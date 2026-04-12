# Frontend React Guide - Restaurant Category Logic
# دليل الفرونت (React) - منطق كاتيجوري المطاعم

**Last Updated:** 2026-04-09  
**Scope:** Admin Frontend + (Optional) User Frontend behavior  
**Base URL:** `/api/admin` و `/api/user`

---

## 1) الفكرة الأساسية

عند اختيار فئة (`Category`) من نوع مطاعم (`is_restaurant = true`) يجب أن يتعامل الفرونت مع المنتج بشكل مختلف:

- إخفاء/تعطيل حقول:
  - `sku`
  - `model`
  - `barcode`
  - `country_id`
  - `sale_country_id`
- تصفير هذه القيم في الـ payload (`null`) عند الإرسال.

> الـ Backend حالياً يطبق هذا تلقائياً أيضاً، لكن لازم الفرونت يطبقه لتجربة استخدام صحيحة ومنع لخبطة الفورم.

---

## 2) ماذا يدعم الـ Backend حالياً

### Category Resources

`is_restaurant` يرجع في:

- Admin categories list/single
- User categories list/single

### Product Resources

- `GET /api/admin/products` يحتوي: `is_restaurant_category`
- `GET /api/admin/products/{id}` يحتوي: `category.is_restaurant`

### Product Requests (Store/Update)

الـ backend يقوم تلقائياً بـ:

- تحويل القيم التالية إلى `null` إذا كانت الفئة مطاعم:
  - `country_id`
  - `sale_country_id`
  - `sku`
  - `model`
  - `barcode`

---

## 3) المطلوب من الفرونت - Admin

## 3.1 شاشة إدارة الفئات (Categories)

في Create/Update Category:

- أضف Toggle باسم: `is_restaurant`
- عند العرض في الجدول أظهر badge:
  - Restaurant
  - Normal

### مثال payload

```json
{
  "name": { "ar": "مطاعم", "en": "Restaurants" },
  "parent_id": null,
  "is_active": true,
  "is_restaurant": true
}
```

---

## 3.2 شاشة إنشاء/تعديل المنتج (Products)

### القاعدة

عند اختيار `category_id`:

1. اقرأ `is_restaurant` من category المختارة.
2. إذا `true`:
   - أخفِ/عطّل الحقول المقيدة.
   - اجعلها `null` داخل state.
   - لا ترسل قيم قديمة لها.
3. إذا `false`:
   - أظهر الحقول كالمعتاد.

### الحقول المقيدة (Restricted Fields)

- `sku`
- `model`
- `barcode`
- `country_id`
- `sale_country_id`

---

## 4) React Implementation (Practical)

## 4.1 Helper

```ts
export function isRestaurantCategory(
  categories: Array<{ id: number; is_restaurant: boolean }>,
  categoryId?: number | null
) {
  if (!categoryId) return false;
  return !!categories.find((c) => c.id === categoryId)?.is_restaurant;
}
```

## 4.2 Form State Behavior

```ts
const restaurantMode = isRestaurantCategory(categories, values.category_id);

useEffect(() => {
  if (!restaurantMode) return;

  setFieldValue("sku", null);
  setFieldValue("model", null);
  setFieldValue("barcode", null);
  setFieldValue("country_id", null);
  setFieldValue("sale_country_id", null);
}, [restaurantMode]);
```

## 4.3 Payload Builder

```ts
function buildProductPayload(values: any, restaurantMode: boolean) {
  const payload = { ...values };

  if (restaurantMode) {
    payload.sku = null;
    payload.model = null;
    payload.barcode = null;
    payload.country_id = null;
    payload.sale_country_id = null;
  }

  return payload;
}
```

---

## 5) Validation (Yup/Zod style)

الحقول المقيدة تكون optional دائماً، لكن في الفرونت:

- إذا `restaurantMode = true`:
  - ignore validation عليها.
- إذا `restaurantMode = false`:
  - طبق validation المعتاد.

مثال منطقي:

```ts
const requireCommercialFields = !restaurantMode;
```

---

## 6) UI/UX Recommendations

1. عند تفعيل Restaurant mode اعرض تنبيه:
   - "هذه الفئة مطاعم، حقول SKU/Model/Barcode/Country غير مطلوبة."
2. لا تعرض الحقول المقيدة disabled فقط؛ الأفضل إخفاؤها بالكامل لتقليل الالتباس.
3. في Edit Product:
   - إذا المنتج تابع لفئة مطاعم، افتح الشاشة مباشرة على restaurant mode.

---

## 7) Optional for User Frontend

بما أن `is_restaurant` متاح في `/api/user/categories`:

- ممكن تقسيم الواجهة إلى Tabين:
  - `Restaurants`
  - `Market`
- أو فلترة الكاتيجوري حسب `is_restaurant`.

---

## 8) Testing Checklist

- [ ] Category form يحتوي `is_restaurant`.
- [ ] عند اختيار فئة مطاعم تختفي الحقول المقيدة في Product form.
- [ ] عند التحويل من فئة عادية إلى مطاعم يتم مسح القيم المقيدة من state.
- [ ] payload النهائي لا يرسل قيم تجارية لفئة مطاعم.
- [ ] عند فتح منتج مطاعم في وضع التعديل يظهر Restaurant mode مباشرة.
- [ ] عند الرجوع لفئة عادية تظهر الحقول المقيدة مجددًا.

---

## 9) Endpoints Reference (Quick)

### Admin

- `GET /api/admin/categories`
- `GET /api/admin/categories/{id}`
- `POST /api/admin/categories`
- `PUT /api/admin/categories/{id}`

- `GET /api/admin/products`
- `GET /api/admin/products/{id}`
- `POST /api/admin/products`
- `PUT /api/admin/products/{id}`

### User

- `GET /api/user/categories`

