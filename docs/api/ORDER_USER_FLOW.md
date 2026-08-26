# Order Flow (User) - شرح كامل للـ Frontend

هذا الملف يشرح فلو إنشاء/معاينة الطلب للمستخدم، ومصادر كل حقل في الـ body والـ response، وكيف ترتبط النقاط والباقات (الاشتراك) والعروض بالطلب.

المراجع الأساسية في الكود:
- `app/Http/Controllers/User/Order/OrderController.php`
- `app/Services/User/OrderService.php`
- `app/Http/Requests/User/Order/StoreRequest.php`
- `app/Http/Controllers/User/ActiveBenefitsController.php`
- `app/Services/User/SubscriptionBenefitsService.php`
- `app/Services/PointExchangeService.php`
- `app/Services/User/PromotionService.php`
- `app/Http/Resources/Order/OneResource.php`
- `app/Http/Resources/Order/AllResource.php`

## 1) Endpoints الخاصة باليوزر

- إنشاء الطلب
  - `POST /api/user/orders`
  - Controller: `OrderController@store`
  - Service: `OrderService@create`

- معاينة الطلب (Preview)
  - `POST /api/user/orders/preview`
  - Controller: `OrderController@preview`
  - Service: `OrderService@preview`

- إلغاء الطلب
  - `POST /api/user/orders/{orderId}/cancel`

- إعادة الطلب
  - `POST /api/user/orders/reorder/{orderId}`

- الطلب النشط
  - `GET /api/user/orders/active`

- جلب الـ Active Benefits (النقاط + الاشتراك)
  - `GET /api/user/active-benefits`
  - Controller: `ActiveBenefitsController@index`

- جلب مزايا الاشتراك فقط
  - `GET /api/user/subscription/benefits`

## 2) Body Request لإنشاء الطلب أو الـ Preview

Validation موجود في: `app/Http/Requests/User/Order/StoreRequest.php`

```json
{
  "address_id": 12,
  "payment_method_id": 2,
  "cart_type": "default",
  "is_instant_delivery": true,
  "items": [
    { "shop_product_variant_id": 101, "quantity": 2 },
    { "shop_product_variant_id": 205, "quantity": 1 }
  ],
  "recipe_id": null,
  "admin_basket_id": null,
  "admin_schedule_basket_id": null,
  "basket_schedule_id": null,
  "coupon": "SAVE10",
  "affiliate_id": null,
  "point_coupon_exchange_id": 2,
  "point_free_delivery_exchange_id": 3,
  "use_subscription_discount": true,
  "use_subscription_free_delivery": false,
  "promotion_id": 7
}
```

### شرح كل حقل ومن أين يُستخدم

- `address_id`
  - مصدره: اختيار المستخدم لعنوانه.
  - يُستخدم في:
    - `OrderService@resolveAddress` لتثبيت عنوان الطلب.
    - حساب التوصيل في `CalculateDeliveryPriceService`.

- `payment_method_id`
  - مصدره: شاشة طرق الدفع.
  - ملاحظة مهمة: في `OrderService@create` الحالي لا يتم حفظ هذا الحقل في الطلب (لا يوجد `payment_method_id` داخل `createOrder` ولا داخل `updateOrderTotals`).
  - النتيجة: هذا الحقل مطلوب بالـ validation لكنه غير مستخدم فعليا في إنشاء الطلب حاليا.

- `cart_type` (قيم: `default`, `recipe`, `admin_cart`, `schedule_admin_cart`)
  - يحدد كيف يتم حساب الخصم والتوصيل.
  - المصدر: نوع السلة التي اختارها المستخدم (سلة منتجات عادية / وصفة / سلة إدارية / سلة مجدولة).

- `is_instant_delivery`
  - مصدره: اختيار المستخدم (توصيل فوري/غير فوري).
  - يُحفظ في الطلب عند إنشاء `Order` في `createOrder`.

- `items[]`
  - عنصر الطلب الأساسي.
  - كل عنصر يحتوي:
    - `shop_product_variant_id` من واجهة المنتجات/الـ variants.
    - `quantity` الكمية.
  - في `addItemsToOrder` يتم:
    - جلب السعر من `ShopProductVariant->price`.
    - خصم المنتج يُطبّق فقط إذا:
      - لا يوجد خصم خارجي (كوبون/نقاط/اشتراك).
      - ولا يوجد سلة (basket).
      - و`cart_type = default`.

- `recipe_id`
  - مطلوب فقط إذا `cart_type = recipe`.
  - يُستخدم لجلب خصم السلة وسعر التوصيل من وصفة `Recipe`.

- `admin_basket_id`
  - مطلوب إذا `cart_type = admin_cart`.
  - يُستخدم لجلب خصم السلة وسعر التوصيل من `Basket`.

- `admin_schedule_basket_id` + `basket_schedule_id`
  - مطلوب إذا `cart_type = schedule_admin_cart`.
  - `admin_schedule_basket_id` يحدد السلة.
  - `basket_schedule_id` (اختياري) يحدد سكجول محدد، وإذا لديه `discount_value` يتم استخدامه بدل خصم السلة الأساسي.

- `coupon`
  - كود الكوبون.
  - يُستخدم في `applyCoupon`.
  - الخصم يُحسب فقط على العناصر المسموح بها (فلترة منتجات/تصنيفات/بائعين).
  - العناصر غير المسموح بها ترجع كـ `excluded_items` في الـ preview.
  - ملاحظة مهمة: الكوبون لا يتم حفظ `coupon_id` أو `coupon_code` داخل الطلب في هذا المسار حاليا (فقط قيمة الخصم تُحفظ).

- `affiliate_id`
  - موجود بالـ validation فقط.
  - غير مستخدم في `OrderService@create` الحالي.

- `point_coupon_exchange_id`
  - يأتي من `GET /api/user/active-benefits` (بالحقل `key = point_coupon_exchange_id`).
  - يتم التحقق منه في `handlePointExchanges`.
  - الشرط: يكون لـ نفس المستخدم، النوع `coupon`، الحالة `completed`، وغير منتهي.
  - عند الإنشاء يتم تعليم الـ exchange كـ `used`.
  - ملاحظة: الـ id لا يُحفظ في جدول الطلب حاليا (الحقول `used_coupon_exchange_id` غير محدثة هنا).

- `point_free_delivery_exchange_id`
  - يأتي من `GET /api/user/active-benefits` (بالحقل `key = point_free_delivery_exchange_id`).
  - إن كان صالحا، يصبح `free_delivery_from_points = true` ويتحوّل سعر التوصيل إلى 0.
  - ملاحظة: الـ id لا يُحفظ في الطلب حاليا (الحقول `used_free_delivery_exchange_id` غير محدثة هنا).

- `use_subscription_discount`
  - يأتي من `GET /api/user/active-benefits` (key: `use_subscription_discount` value: `true`).
  - `SubscriptionBenefitsService@applyBenefits` يحسب الخصم حسب نسبة خصم الباقة.
  - عند الإنشاء يتم تسجيل الاستخدام وإنقاص `remaining_orders`.
  - ملاحظة: لا يتم حفظ `subscription_id` أو `subscription_free_delivery` في الطلب حاليا، فقط قيمة `subscription_discount` يتم حفظها.

- `use_subscription_free_delivery`
  - يأتي من `GET /api/user/active-benefits` (key: `use_subscription_free_delivery` value: `true`).
  - إذا متاح يتم تصفير سعر التوصيل وإنقاص `remaining_free_deliveries` عند الإنشاء.
  - ملاحظة: لا يتم حفظ `subscription_free_delivery` في الطلب حاليا في هذا المسار.

- `promotion_id`
  - يُستخدم فقط للعروض المالية القابلة للاختيار (simple_discount أو spend_x_discount).
  - مصدره: `available_promotions` في رد الـ preview.
  - الخصم يُحسب في `PromotionService@applyDiscountPromotion`.
  - ملاحظة: لا يتم حفظ `promotion_id` في الطلب حاليا، فقط قيمة `promotion_discount` تُحفظ.

## 3) قواعد منع التعارض بين الخصومات والتوصيل المجاني

في `StoreRequest::withValidator`:

- لا يُسمح بأكثر من مصدر خصم مالي واحد من:
  - `coupon`
  - `point_coupon_exchange_id`
  - `use_subscription_discount`
  - `promotion_id` (إذا كان العرض من نوع خصم مالي)

- لا يُسمح بأكثر من مصدر توصيل مجاني واحد من:
  - `point_free_delivery_exchange_id`
  - `use_subscription_free_delivery`

## 4) كيف يتم ربط النقاط والباقات مع الطلب

### النقاط (Point Exchanges)
المصدر:
- إنشاء الاستبدال عبر `points/exchange/*`.
- عرض الاستبدالات النشطة عبر `GET /api/user/active-benefits`.

داخل الطلب:
- `point_coupon_exchange_id`:
  - يضيف خصم نقدي من `exchange_data.discount_amount`.
  - يتم تعليم الـ exchange كـ `used`.
- `point_free_delivery_exchange_id`:
  - يجعل `delivery_price = 0`.
  - يتم تعليم الـ exchange كـ `used`.

ملاحظة مهمة:
الحقول `used_coupon_exchange_id` و`used_free_delivery_exchange_id` موجودة على جدول الطلب، لكنها لا تُحدّث في هذا المسار الحالي. لذلك الـ response غالبا سيعيدها `null`.

### الباقات / الاشتراك (Subscription)
المصدر:
- حالة الاشتراك من جدول `subscriptions`.
- عرض المزايا عبر `GET /api/user/active-benefits` أو `GET /api/user/subscription/benefits`.

داخل الطلب:
- `use_subscription_discount`: يطبّق نسبة خصم من الباقة على قيمة `subtotal`.
- `use_subscription_free_delivery`: يصفّر سعر التوصيل إذا كان هناك رصيد توصيل مجاني.
يتم تسجيل الاستخدام في جدول `subscription_usage_logs`.

ملاحظة:
لا يتم حفظ `subscription_id` أو `subscription_free_delivery` داخل الطلب في هذا المسار الحالي. فقط قيمة `subscription_discount` تُحفظ.

## 5) العروض (Promotions)

### العروض المالية القابلة للاختيار
من `PromotionService@getAvailablePromotions` في الـ preview:
- `simple_discount`
- `spend_x_discount` (شرط حد أدنى من إجمالي السلة)

يتم إرسال `promotion_id` في الـ body لتطبيق الخصم.

### عروض الهدايا (buy_x_get_y)
من `PromotionService@applyNonDiscountPromotions`:
- تُطبّق تلقائيا (بدون اختيار).
- في الإنشاء يتم إضافة عناصر مجانية داخل `order_items` بسعر 0 وخصم 100%.
- في الـ preview ترجع داخل `non_discount_promotions`.

## 6) تدفق إنشاء الطلب (Create Flow)

المسار الفعلي داخل `OrderService@create`:

1. جلب المستخدم والعنوان.
2. إنشاء الطلب الأساسي (بدون totals).
3. تحديد خصم السلة + سعر التوصيل حسب `cart_type`.
4. بناء عناصر الطلب وحساب `subtotal`.
5. تطبيق الخصومات الخارجية (كوبون/نقاط/اشتراك/عروض مالية).
6. حساب خصم السلة (إذا لا يوجد خصومات خارجية).
7. حساب الإجمالي النهائي.
8. تحديث الطلب بالقيم (subtotal, total, discounts, delivery).
9. تطبيق عروض الهدايا (buy_x_get_y).
10. إرسال حدث تغيير الحالة.

## 7) Response لعملية إنشاء الطلب

الرد ملفوف بواسطة `sendResponse`:

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "id": 123,
    "order_code": "ORD-...",
    "status": "pending",
    "cart_type": "default",
    "is_instant_delivery": true,
    "delivery_price": 5.0,
    "subtotal": 120.0,
    "total": 110.0,
    "total_quantity": 3,
    "basket_discount": 0,
    "coupon_discount": 10,
    "promotion_discount": 0,
    "subscription_discount": 0,
    "coupon_discount_from_points": 0,
    "free_delivery_from_points": false,
    "use_coupon_exchange_id": null,
    "use_free_delivery_exchange_id": null,
    "subscription_free_delivery": false,
    "created_at": "2026-03-10 10:00:00",
    "affiliate": { ... },
    "timestamps": { ... },
    "user": { ... },
    "driver": { ... },
    "user_address": { ... },
    "payment_method": { ... },
    "items": [ ... ]
  }
}
```

### مصدر كل حقل في الـ response

- الحقول الأساسية مثل `id`, `order_code`, `status`, `cart_type`, `is_instant_delivery` من جدول `orders`.
- `delivery_price`: من حساب التوصيل أو تم تصفيره بسبب نقاط/اشتراك.
- `subtotal`: مجموع الأسعار قبل الخصومات الخارجية.
- `total`: بعد الخصومات + التوصيل.
- `basket_discount`: خصم السلة (recipe/admin/schedule) لكن فقط إذا لا يوجد خصم خارجي.
- `coupon_discount`: من الكوبون اليدوي.
- `coupon_discount_from_points`: من استبدال النقاط لكوبون.
- `promotion_discount`: من العروض المالية المختارة.
- `subscription_discount`: من اشتراك الباقة.
- `free_delivery_from_points`: true إذا تم استخدام توصيل مجاني من النقاط.
- `use_coupon_exchange_id`, `use_free_delivery_exchange_id`:
  - تأتي من أعمدة في الطلب لكنها غير محدثة حاليا في هذا المسار، فستكون `null`.
- `subscription_free_delivery`:
  - عمود في الطلب لكنه غير محدث حاليا في هذا المسار.
- `items`: من `OrderItemResource`.

## 8) Response للـ Preview

الرد ملفوف بواسطة `sendResponse` لكن `data` عبارة عن مصفوفة:

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "discounts": {
      "coupon_discount": 0,
      "coupon_discount_from_points": 10,
      "subscription_discount": 0,
      "promotion_discount": 0,
      "free_delivery_from_points": false,
      "delivery_price": 5.0,
      "total_discount": 10,
      "excluded_items": [101, 205]
    },
    "subtotal_before_discount": 120.0,
    "subtotal_after_product_discount": 115.0,
    "total_quantity": 3,
    "total": 110.0,
    "available_promotions": [
      { "id": 7, "name": "Spend 50 Get 10", "description": "..." }
    ],
    "non_discount_promotions": {
      "promotion_id": 3,
      "promotion_title": "Buy 2 Get 1",
      "free_items": [
        { "shop_product_variant_id": 101, "free_quantity": 1 }
      ]
    },
    "excluded_items": [101, 205]
  }
}
```

## 9) Active Benefits API (الكي/فاليو التي يرسلها الفرونت)

Endpoint:
- `GET /api/user/active-benefits`

Response (مختصر):

```json
{
  "status": true,
  "data": {
    "coupons": [
      {
        "key": "point_coupon_exchange_id",
        "value": 2,
        "title": "خصم من النقاط",
        "discount_amount": 10,
        "expired_at": "2026-04-01"
      },
      {
        "key": "use_subscription_discount",
        "value": true,
        "title": "خصم من الباقة",
        "discount_percentage": 5,
        "expired_at": "2026-05-10"
      }
    ],
    "free_deliveries": [
      {
        "key": "point_free_delivery_exchange_id",
        "value": 3,
        "title": "توصيل مجاني من النقاط",
        "expired_at": "2026-04-01"
      },
      {
        "key": "use_subscription_free_delivery",
        "value": true,
        "title": "توصيل مجاني من الباقة",
        "remaining_count": 2,
        "expired_at": "2026-05-10"
      }
    ],
    "has_benefits": true
  }
}
```

الفرونت يرسل هذه الـ `key` مع `value` في الـ body عند إنشاء الطلب:

- `point_coupon_exchange_id: 2`
- `point_free_delivery_exchange_id: 3`
- `use_subscription_discount: true`
- `use_subscription_free_delivery: true`

## 10) ملاحظات تنفيذية مهمة للفرونت

- لا تستخدم أكثر من مصدر خصم مالي واحد بنفس الطلب.
- لا تستخدم أكثر من مصدر توصيل مجاني واحد بنفس الطلب.
- الخصم من المنتج لا يُطبّق إذا تم استخدام كوبون/نقاط/اشتراك أو إذا كانت السلة ليست `default`.
- بعض الحقول المطلوبة بالـ validation لا تُحفظ حاليا في الطلب (مثل `payment_method_id`, `coupon_id`, `promotion_id`).
  إذا احتجنا حفظها يجب تعديل `OrderService@create` و/أو `updateOrderTotals`.
Below is the **English version of the same document**, keeping the same structure and technical meaning so it can be shared with the frontend team.

---

# Order Flow (User) – Full Frontend Guide

This document explains the **order creation and preview flow for the user**, the source of each field in the request body and response, and how **points, subscriptions, and promotions** interact with the order.

## Main Code References

* `app/Http/Controllers/User/Order/OrderController.php`
* `app/Services/User/OrderService.php`
* `app/Http/Requests/User/Order/StoreRequest.php`
* `app/Http/Controllers/User/ActiveBenefitsController.php`
* `app/Services/User/SubscriptionBenefitsService.php`
* `app/Services/PointExchangeService.php`
* `app/Services/User/PromotionService.php`
* `app/Http/Resources/Order/OneResource.php`
* `app/Http/Resources/Order/AllResource.php`

---

# 1) User Endpoints

### Create Order

```
POST /api/user/orders
```

Controller:
`OrderController@store`

Service:
`OrderService@create`

---

### Order Preview

```
POST /api/user/orders/preview
```

Controller:
`OrderController@preview`

Service:
`OrderService@preview`

---

### Cancel Order

```
POST /api/user/orders/{orderId}/cancel
```

---

### Reorder

```
POST /api/user/orders/reorder/{orderId}`
```

---

### Get Active Order

```
GET /api/user/orders/active
```

---

### Get Active Benefits (Points + Subscription)

```
GET /api/user/active-benefits
```

Controller:
`ActiveBenefitsController@index`

---

### Get Subscription Benefits Only

```
GET /api/user/subscription/benefits
```

---

# 2) Request Body for Order Creation / Preview

Validation is defined in:

```
app/Http/Requests/User/Order/StoreRequest.php
```

Example request:

```json
{
  "address_id": 12,
  "payment_method_id": 2,
  "cart_type": "default",
  "is_instant_delivery": true,
  "items": [
    { "shop_product_variant_id": 101, "quantity": 2 },
    { "shop_product_variant_id": 205, "quantity": 1 }
  ],
  "recipe_id": null,
  "admin_basket_id": null,
  "admin_schedule_basket_id": null,
  "basket_schedule_id": null,
  "coupon": "SAVE10",
  "affiliate_id": null,
  "point_coupon_exchange_id": 2,
  "point_free_delivery_exchange_id": 3,
  "use_subscription_discount": true,
  "use_subscription_free_delivery": false,
  "promotion_id": 7
}
```

---

# 3) Field Explanation and Usage

## address_id

Source: user-selected address.

Used in:

* `OrderService@resolveAddress` to determine the order address.
* `CalculateDeliveryPriceService` to calculate delivery cost.

---

## payment_method_id

Source: payment methods screen.

Important note:

Inside `OrderService@create`, this field **is not currently stored in the order**.
It exists in validation but is **not used during order creation**.

---

## cart_type

Possible values:

```
default
recipe
admin_cart
schedule_admin_cart
```

Determines how **basket discounts and delivery fees** are calculated.

---

## is_instant_delivery

Source: user choice (instant delivery or not).

Saved in the order during creation.

---

## items[]

Main order items.

Each item contains:

```
shop_product_variant_id
quantity
```

Inside `addItemsToOrder`:

* Price is retrieved from `ShopProductVariant->price`
* Product discount applies **only if**:

  * No external discount exists (coupon / points / subscription)
  * No basket is used
  * `cart_type = default`

---

## recipe_id

Required when:

```
cart_type = recipe
```

Used to retrieve:

* Basket discount
* Delivery price

From the `Recipe` model.

---

## admin_basket_id

Required when:

```
cart_type = admin_cart
```

Used to retrieve basket discount and delivery price from `Basket`.

---

## admin_schedule_basket_id + basket_schedule_id

Required when:

```
cart_type = schedule_admin_cart
```

* `admin_schedule_basket_id` identifies the basket
* `basket_schedule_id` (optional) identifies a schedule

If the schedule contains `discount_value`, it overrides the basket discount.

---

## coupon

Coupon code entered by the user.

Handled in:

```
applyCoupon()
```

The discount is calculated **only for allowed products**.

Disallowed products appear in:

```
excluded_items
```

Important note:

`coupon_id` or `coupon_code` **is not currently saved in the order**, only the discount value.

---

## affiliate_id

Exists in validation only.

Not used in `OrderService@create`.

---

## point_coupon_exchange_id

Returned from:

```
GET /api/user/active-benefits
```

Key:

```
point_coupon_exchange_id
```

Validated in:

```
handlePointExchanges()
```

Conditions:

* Belongs to the same user
* Type = `coupon`
* Status = `completed`
* Not expired

During order creation:

The exchange is marked as `used`.

Important note:

`used_coupon_exchange_id` exists in the orders table but **is not updated here**.

---

## point_free_delivery_exchange_id

Returned from:

```
GET /api/user/active-benefits
```

Key:

```
point_free_delivery_exchange_id
```

If valid:

```
delivery_price = 0
```

Important note:

`used_free_delivery_exchange_id` exists but **is not updated in this flow**.

---

## use_subscription_discount

Returned from:

```
GET /api/user/active-benefits
```

Key:

```
use_subscription_discount
```

Handled by:

```
SubscriptionBenefitsService@applyBenefits
```

Calculates discount based on subscription percentage.

When creating the order:

* Usage is recorded
* `remaining_orders` is decreased

Important note:

`subscription_id` is not stored in the order.

---

## use_subscription_free_delivery

Returned from:

```
GET /api/user/active-benefits
```

Key:

```
use_subscription_free_delivery
```

If available:

```
delivery_price = 0
```

`remaining_free_deliveries` is decreased.

---

## promotion_id

Used only for selectable financial promotions:

```
simple_discount
spend_x_discount
```

Source:

```
available_promotions
```

Handled in:

```
PromotionService@applyDiscountPromotion
```

Important note:

`promotion_id` is **not saved in the order**, only the discount value.

---

# 4) Discount Conflict Rules

Inside:

```
StoreRequest::withValidator
```

Only **one financial discount source** can be used:

* coupon
* point_coupon_exchange_id
* use_subscription_discount
* promotion_id

Only **one free delivery source** can be used:

* point_free_delivery_exchange_id
* use_subscription_free_delivery

---

# 5) Points and Subscription Integration

## Points (Point Exchanges)

Created through:

```
points/exchange/*
```

Displayed through:

```
GET /api/user/active-benefits
```

Usage in orders:

### Coupon Exchange

Adds discount from:

```
exchange_data.discount_amount
```

Exchange is marked as `used`.

---

### Free Delivery Exchange

Sets:

```
delivery_price = 0
```

Exchange marked as used.

---

Important note:

These columns exist in orders but are **not updated in this flow**:

```
used_coupon_exchange_id
used_free_delivery_exchange_id
```

---

## Subscription

Subscription status comes from:

```
subscriptions table
```

Benefits shown via:

```
GET /api/user/active-benefits
```

Usage:

### Subscription Discount

Applies discount percentage to:

```
subtotal
```

---

### Subscription Free Delivery

Sets:

```
delivery_price = 0
```

Usage is logged in:

```
subscription_usage_logs
```

---

# 6) Promotions

## Selectable Financial Promotions

From:

```
PromotionService@getAvailablePromotions
```

Types:

```
simple_discount
spend_x_discount
```

Frontend sends:

```
promotion_id
```

---

## Gift Promotions (buy_x_get_y)

Applied automatically through:

```
PromotionService@applyNonDiscountPromotions
```

Behavior:

* Free items are added to `order_items`
* Price = 0
* Discount = 100%

Preview returns them in:

```
non_discount_promotions
```

---

# 7) Order Creation Flow

Inside:

```
OrderService@create
```

Steps:

1. Retrieve user and address
2. Create base order (without totals)
3. Determine basket discount + delivery price
4. Build order items and calculate subtotal
5. Apply external discounts (coupon / points / subscription / promotions)
6. Apply basket discount if no external discount exists
7. Calculate final total
8. Update order totals
9. Apply gift promotions
10. Dispatch order status event

---

# 8) Order Creation Response

Wrapped with:

```
sendResponse
```

Example:

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "id": 123,
    "order_code": "ORD-...",
    "status": "pending",
    "cart_type": "default",
    "is_instant_delivery": true,
    "delivery_price": 5.0,
    "subtotal": 120.0,
    "total": 110.0,
    "total_quantity": 3,
    "basket_discount": 0,
    "coupon_discount": 10,
    "promotion_discount": 0,
    "subscription_discount": 0,
    "coupon_discount_from_points": 0,
    "free_delivery_from_points": false,
    "use_coupon_exchange_id": null,
    "use_free_delivery_exchange_id": null,
    "subscription_free_delivery": false,
    "created_at": "2026-03-10 10:00:00",
    "affiliate": {},
    "timestamps": {},
    "user": {},
    "driver": {},
    "user_address": {},
    "payment_method": {},
    "items": []
  }
}
```

---

# 9) Preview Response

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "discounts": {
      "coupon_discount": 0,
      "coupon_discount_from_points": 10,
      "subscription_discount": 0,
      "promotion_discount": 0,
      "free_delivery_from_points": false,
      "delivery_price": 5.0,
      "total_discount": 10,
      "excluded_items": [101, 205]
    },
    "subtotal_before_discount": 120.0,
    "subtotal_after_product_discount": 115.0,
    "total_quantity": 3,
    "total": 110.0,
    "available_promotions": [
      { "id": 7, "name": "Spend 50 Get 10", "description": "..." }
    ],
    "non_discount_promotions": {
      "promotion_id": 3,
      "promotion_title": "Buy 2 Get 1",
      "free_items": [
        { "shop_product_variant_id": 101, "free_quantity": 1 }
      ]
    },
    "excluded_items": [101, 205]
  }
}
```

---

# 10) Active Benefits API

Endpoint:

```
GET /api/user/active-benefits
```

Example response:

```json
{
  "status": true,
  "data": {
    "coupons": [
      {
        "key": "point_coupon_exchange_id",
        "value": 2,
        "title": "Points Discount",
        "discount_amount": 10,
        "expired_at": "2026-04-01"
      },
      {
        "key": "use_subscription_discount",
        "value": true,
        "title": "Subscription Discount",
        "discount_percentage": 5,
        "expired_at": "2026-05-10"
      }
    ],
    "free_deliveries": [
      {
        "key": "point_free_delivery_exchange_id",
        "value": 3,
        "title": "Free Delivery from Points",
        "expired_at": "2026-04-01"
      },
      {
        "key": "use_subscription_free_delivery",
        "value": true,
        "title": "Free Delivery from Subscription",
        "remaining_count": 2,
        "expired_at": "2026-05-10"
      }
    ],
    "has_benefits": true
  }
}
```

Frontend sends the selected values in the order request:

```
point_coupon_exchange_id: 2
point_free_delivery_exchange_id: 3
use_subscription_discount: true
use_subscription_free_delivery: true
```

---

# 11) Important Frontend Notes

* Only **one financial discount source** can be used per order.
* Only **one free delivery source** can be used per order.
* Product discounts **do not apply** when using coupon/points/subscription or when cart type is not `default`.
* Some validated fields are **not currently stored in the order**, such as:

```
payment_method_id
coupon_id
promotion_id
```

If these need to be stored, `OrderService@create` or `updateOrderTotals` must be updated.

---

