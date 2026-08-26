# الصلاحيات الجديدة (Seeder) والمسارات المرتبطة

تمت إضافة هذه الصلاحيات في `AdminRolePermissionSeeder` عبر الموديلات التالية (لكل موديل: `view`, `create`, `update`, `delete`).

الجدول يربط **اسم الصلاحية** بـ **مسار الـ API** و**اسم الـ route في Laravel** (حيث وُجد).  
البادئة الكاملة للـ URL: `/api/admin/...`.

---



## 2. `productvariant.*`

| الصلاحية | Method | URI | اسم الـ route (Laravel) |
|----------|--------|-----|-------------------------|
| `productvariant.view` | GET | `product-variants` | `product-variants.index` |
| `productvariant.view` | GET | `product-variants/create` | `product-variants.create` |
| `productvariant.view` | GET | `product-variants/{product_variant}` | `product-variants.show` |
| `productvariant.view` | GET | `product-variants/{product_variant}/edit` | `product-variants.edit` |
| `productvariant.create` | POST | `product-variants` | `product-variants.store` |
| `productvariant.update` | PUT, PATCH | `product-variants/{product_variant}` | `product-variants.update` |
| `productvariant.delete` | DELETE | `product-variants/{product_variant}` | `product-variants.destroy` |
| `productvariant.view` | GET | `products/{product}/variants` | *(بدون اسم route)* |

**Middleware:** `crud.permission:productvariant` على الـ resource؛ و`admin.permission:productvariant.view` على `products/{product}/variants`.

---

## 3. `shopproductvariant.*`

| الصلاحية | Method | URI | اسم الـ route (Laravel) |
|----------|--------|-----|-------------------------|
| `shopproductvariant.view` | GET | `shop-product-variants` | `shop-product-variants.index` |
| `shopproductvariant.view` | GET | `shop-product-variants/create` | `shop-product-variants.create` |
| `shopproductvariant.view` | GET | `shop-product-variants/{shop_product_variant}` | `shop-product-variants.show` |
| `shopproductvariant.view` | GET | `shop-product-variants/{shop_product_variant}/edit` | `shop-product-variants.edit` |
| `shopproductvariant.create` | POST | `shop-product-variants` | `shop-product-variants.store` |
| `shopproductvariant.update` | PUT, PATCH | `shop-product-variants/{shop_product_variant}` | `shop-product-variants.update` |
| `shopproductvariant.delete` | DELETE | `shop-product-variants/{shop_product_variant}` | `shop-product-variants.destroy` |

**Middleware:** `crud.permission:shopproductvariant`.

---

## 4. `usergift.*`

| الصلاحية | Method | URI | اسم الـ route (Laravel) |
|----------|--------|-----|-------------------------|
| `usergift.view` | GET | `user-gifts` | `user-gifts.index` |
| `usergift.view` | GET | `user-gifts/{user_gift}` | `user-gifts.show` |
| `usergift.create` | POST | `user-gifts` | `user-gifts.store` |
| `usergift.update` | PUT, PATCH | `user-gifts/{user_gift}` | `user-gifts.update` |
| `usergift.delete` | DELETE | `user-gifts/{user_gift}` | `user-gifts.destroy` |

**Middleware:** `crud.permission:usergift` على `apiResource('user-gifts', ...)`.

---

## 5. `vendorwithdrawrequest.*`

| الصلاحية | Method | URI | اسم الـ route (Laravel) |
|----------|--------|-----|-------------------------|
| `vendorwithdrawrequest.view` | GET | `vendor-withdraw-requests` | `vendor-withdraw-requests.index` |
| `vendorwithdrawrequest.view` | GET | `vendor-withdraw-requests/{vendor_withdraw_request}` | `vendor-withdraw-requests.show` |
| `vendorwithdrawrequest.update` | PUT, PATCH | `vendor-withdraw-requests/{vendor_withdraw_request}` | `vendor-withdraw-requests.update` |

**Middleware:** `crud.permission:vendorwithdrawrequest` على `apiResource(...)->only(['index', 'show', 'update'])`.  
*(مسارات `create` / `destroy` غير معرّفة لهذا المورد؛ صلاحيات `vendorwithdrawrequest.create` و`vendorwithdrawrequest.delete` موجودة في الـ seed لكن لا يوجد مسار admin يطبّقها حالياً.)*

---

## 6. `systemsetting.*`

| الصلاحية | Method | URI |
|----------|--------|-----|
| `systemsetting.view` | GET | `system-settings` |
| `systemsetting.view` | GET | `system-settings/group/{group}` |
| `systemsetting.view` | GET | `system-settings/{key}` |
| `systemsetting.update` | POST | `system-settings/batch` |
| `systemsetting.update` | POST | `system-settings/clear-cache` |
| `systemsetting.update` | PUT | `system-settings/{key}` |
| `systemsetting.update` | POST | `system-settings` |
| `systemsetting.delete` | DELETE | `system-settings/{key}` |

**Middleware:** مجموعات `admin.permission:systemsetting.view`، `admin.permission:systemsetting.update`، و`admin.permission:systemsetting.delete` على المسارات أعلاه.  
*(اسم route غير مُسمّى في `route:list` لهذا البادئة.)*

---

## 7. `vendoraccounting.*`

| الصلاحية | Method | URI |
|----------|--------|-----|
| `vendoraccounting.view` | GET | `vendor-accounting/summary` |
| `vendoraccounting.view` | GET | `vendor-accounting/vendors` |
| `vendoraccounting.view` | GET | `vendor-accounting/vendors/{vendorId}` |

**Middleware:** `admin.permission:vendoraccounting.view` على المجموعة.  
*(صلاحيات `vendoraccounting.create` / `update` / `delete` مُنشأة في الـ seed؛ لا يوجد مسار admin يطبّقها حالياً.)*

---

## ملاحظة

ربط **HTTP → صلاحية** في حالة `crud.permission` يتبع: GET → `view`, POST → `create`, PUT/PATCH → `update`, DELETE → `delete`.
