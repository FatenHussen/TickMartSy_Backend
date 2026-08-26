# User Promotions & Flash Sales APIs

هذا الملف يشرح الـ APIs المعرفة في `routes/api/user.php` ضمن الجزء:

- `Route::prefix('promotions')`
- `Route::prefix('flash-sales')`

## Base Route

كل المسارات هنا تحت:

- `GET /api/user/...`

هذه المسارات **Public** (لا تحتاج توكن) في الوضع الحالي.

---

## 1) Get Active Promotions

`GET /api/user/promotions`

### الهدف

إرجاع كل العروض (`promotions`) الفعالة حالياً فقط.

### كيف يتم الفلترة؟

في `PromotionController@index`:

- يتم استخدام `Promotion::query()->active()`
- Scope `active()` يطبق الشروط التالية:
  - `is_active = true`
  - `starts_at` إما `null` أو <= الوقت الحالي
  - `ends_at` إما `null` أو >= الوقت الحالي
- ثم الترتيب بـ `id` تصاعدي.

### شكل الاستجابة

الاستجابة داخل `sendResponse`، وبيانات العناصر من `Promotion\AllResource`.

```json
{
  "status": true,
  "message": "Success",
  "data": [
    {
      "id": 1,
      "name": {
        "ar": "عرض خصم",
        "en": "Discount Offer"
      },
      "description": {
        "ar": "وصف العرض",
        "en": "Offer description"
      },
      "type": "simple_discount",
      "is_active": true,
      "created_at": "2026-04-20 14:30"
    }
  ]
}
```

---

## 2) Get Active Flash Sale With Products

`GET /api/user/flash-sales/active`

### الهدف

إرجاع **Flash Sale واحد فقط** (الأحدث) بشرط أن يكون فعال، مع قائمة منتجاته.

### كيف يتم جلب الـ Flash Sale؟

في `FlashSaleController@active`:

- `FlashSale::query()->active()->latest('id')->first()`

يعني:

- لازم `is_active = true`
- و `end_date > now()`
- ويتم أخذ أحدث سجل (أكبر `id`) فقط.

### كيف يتم ربط واسترجاع المنتجات؟

بنفس منطق الربط الموجود في `FlashSaleService`:

- الربط يتم عن طريق الحقل `products.flash_sale_id`.
- لذلك الاسترجاع يتم بـ:
  - `Product::where('flash_sale_id', $flashSaleId)`

في `FlashSale\ActiveResource`:

- يتم جلب المنتجات المرتبطة بنفس `flash_sale_id`
- فلترة `is_active = true`
- تحميل العلاقات اللازمة للعرض في `Product\AllResource` مثل:
  - `category`, `vendor`, `variants.shopVariants`, `media`, `badges`
- وإذا المستخدم مسجل دخول يتم إضافة `is_favorite`.

### إذا لا يوجد Flash Sale فعال

يرجع:

```json
{
  "status": true,
  "message": "Success",
  "data": null
}
```

### شكل الاستجابة عند وجود Flash Sale فعال

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "id": 7,
    "name": "Weekend Deals",
    "end_date": "2026-04-30T23:59:59.000000Z",
    "is_active": true,
    "discount": 15,
    "discount_type": "percent",
    "products": [
      {
        "id": 101,
        "name": {
          "ar": "منتج",
          "en": "Product"
        },
        "price": 120,
        "price_after_discount": 102
      }
    ]
  }
}
```

---

## Routes Summary

- `GET /api/user/promotions`
- `GET /api/user/flash-sales/active`
