# Flash Sales Admin API

هذا الملف يشرح واجهات الـ Admin الخاصة بـ `FlashSaleController`.

## Base URL + Auth

- جميع المسارات تحت: `POST/GET/PUT /api/admin/...`
- مسارات `flash-sales` موجودة داخل `Route::middleware('auth:admin')`، يعني لازم توكن أدمن صالح.

## Routes

تم تعريفها كالتالي:

- `GET /api/admin/flash-sales`
- `POST /api/admin/flash-sales`
- `PUT /api/admin/flash-sales/{flash_sale}`

> `Route::apiResource('flash-sales', FlashSaleController::class)->only(['index','store', 'update'])`

---

## 1) List Flash Sales

`GET /api/admin/flash-sales`

### Query Params

- `page` (اختياري، افتراضي: `1`)
- `per_page` (اختياري، افتراضي: `10`)

### Response Shape

الاستجابة داخل `sendResponse`:

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "items": [
      {
        "id": 1,
        "name": "Weekend Deals",
        "end_date": "2026-04-20T20:00:00.000000Z",
        "is_active": true
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 10,
      "total": 1
    }
  }
}
```

> عناصر القائمة تأتي من `FlashSale\AllResource`.

---

## 2) Create Flash Sale

`POST /api/admin/flash-sales`

### Request Body

```json
{
  "name": "Ramadan Sale",
  "end_date": "2026-04-30 23:59:59",
  "is_active": true,
  "product_ids": [11, 12, 13],
  "category_id": 5,
  "vendor_id": 9
}
```

### Validation Rules

- `name`: required, string, max 255
- `end_date`: required, date, after now
- `is_active`: nullable, boolean
- `product_ids`: sometimes, array
- `product_ids.*`: integer, exists in `products.id`
- `category_id`: sometimes|nullable, exists in `categories.id`
- `vendor_id`: sometimes|nullable, exists in `vendors.id`

### Business Rules

- إذا `is_active = true`:
  - لا يسمح بوجود Flash Sale آخر active وغير منتهي.
  - في حال وجود واحد، يرجع خطأ validation على `is_active`.
- تجميع المنتجات يتم بدمج:
  - `product_ids` المرسلة مباشرة
  - جميع منتجات `category_id` (يشمل descendants)
  - جميع منتجات `vendor_id`
- يتم إزالة التكرارات من المنتج النهائي (`unique`).

### Product Assignment Behavior

بعد إنشاء الـ flash sale:

- يتم ربط المنتجات النهائية عبر `products.flash_sale_id`.
- إذا لم يكن هناك أي منتجات بعد الدمج، لا يتم ربط أي منتج.

### Response

يرجع `sendResponse` مع موديل `FlashSale` بعد `fresh()` (وليس Resource مخصص):

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "id": 7,
    "name": "Ramadan Sale",
    "end_date": "2026-04-30T23:59:59.000000Z",
    "is_active": true,
    "created_at": "2026-04-14T12:40:00.000000Z",
    "updated_at": "2026-04-14T12:40:00.000000Z"
  }
}
```

---

## 3) Update Flash Sale

`PUT /api/admin/flash-sales/{flash_sale}`

نفس Validation الخاصة بالإنشاء (حالياً `name` و`end_date` مطلوبين أيضاً في التعديل).

### Important Behavior in Update

- يتم تحديث بيانات الـ flash sale (`name`, `end_date`, `is_active`).
- ثم يتم **إلغاء ربط كل المنتجات المرتبطة مسبقاً بنفس الفلاش سيل**:
  - `Product::where('flash_sale_id', $flashSale->id)->update(['flash_sale_id' => null])`
- بعدها يتم إعادة ربط المنتجات الجديدة الناتجة من دمج:
  - `product_ids` + منتجات `category_id` + منتجات `vendor_id`.
- إذا الناتج النهائي فارغ، يبقى الفلاش سيل بدون منتجات.

### Response

يرجع بنفس شكل `store` (موديل محدث بعد `fresh()`).

---

## Common Validation Error Example

عند محاولة تفعيل Flash Sale جديد بينما يوجد واحد active:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "is_active": [
      "An active flash sale already exists. Finish it before approving another one."
    ]
  }
}
```

---

## Notes

- لا يوجد `show` أو `destroy` في هذا الـ resource حالياً.
- تفاصيل أكثر مثل `product_count` موجودة في `FlashSale\OneResource` لكن هذا الـ controller لا يستخدمه الآن مباشرة في `store/update`.
