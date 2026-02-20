# مثال على استخدام نظام العملات

## 1. في الـ Resource (مثال: ProductResource)

```php
<?php

namespace App\Http\Resources\Product;

use App\Traits\HasCurrencyConversion;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray($request): array
    {
        // الطريقة الأولى: تحويل سعر واحد
        $priceData = $this->convertPrice($this->price);

        return [
            'id' => $this->id,
            'name' => $this->name,
            
            // عرض السعر بالعملة المطلوبة
            'price' => $priceData['amount'],
            'price_formatted' => $priceData['formatted'],
            'currency' => $priceData['currency'],
            'currency_symbol' => $priceData['symbol'],
            
            // أو استخدام الطريقة المختصرة
            ...$this->withCurrency($this->price, 'price'),
            
            // إذا كان عندك سعر قديم وسعر جديد
            ...$this->withCurrency($this->old_price, 'old_price'),
            ...$this->withCurrency($this->discount_price, 'discount_price'),
        ];
    }
}
```

## 2. في الـ Controller

```php
// جلب عملة المستخدم الحالي
$userCurrency = app(CurrencyService::class)->getUserCurrency(auth()->user());

// تحويل سعر معين
$convertedPrice = $userCurrency->convertFromUSD(100); // 100 دولار

// تحويل من العملة الحالية للدولار
$priceInUSD = $userCurrency->convertToUSD(367); // 367 درهم = 100 دولار
```

## 3. في الـ Model

```php
// إضافة العلاقة في User Model
public function currency()
{
    return $this->belongsTo(Currency::class);
}

// استخدامها
$user = auth()->user();
$userCurrency = $user->currency; // العملة المفضلة للمستخدم
```

## 4. API Endpoints

### User Endpoints:
- `GET /api/user/currencies` - جلب كل العملات النشطة
- `GET /api/user/currencies/my-currency` - جلب عملة المستخدم الحالي
- `PATCH /api/user/currencies/update-currency` - تحديث عملة المستخدم
  ```json
  {
    "currency_id": 2
  }
  ```

### Admin Endpoints:
- `GET /api/admin/currencies` - جلب كل العملات
- `POST /api/admin/currencies` - إنشاء عملة جديدة
  ```json
  {
    "code": "EUR",
    "name": {
      "en": "Euro",
      "ar": "يورو"
    },
    "symbol": "€",
    "exchange_rate": 0.92,
    "is_default": false,
    "is_active": true
  }
  ```
- `PUT /api/admin/currencies/{id}` - تحديث عملة
- `DELETE /api/admin/currencies/{id}` - حذف عملة
- `PATCH /api/admin/currencies/{id}/toggle-status` - تفعيل/تعطيل عملة

## 5. في الـ Database

```php
// كل الأسعار مخزنة بالدولار
Product::create([
    'name' => 'Product Name',
    'price' => 100.00, // دولار
]);

// عند العرض، يتم التحويل تلقائياً حسب عملة المستخدم
```

## 6. مثال كامل في Service

```php
use App\Services\User\CurrencyService;

class ProductService
{
    public function __construct(
        protected CurrencyService $currencyService
    ) {}

    public function getProductWithPrice($productId)
    {
        $product = Product::findOrFail($productId);
        $user = auth()->user();
        $currency = $this->currencyService->getUserCurrency($user);
        
        return [
            'product' => $product,
            'price' => $currency->convertFromUSD($product->price),
            'currency' => $currency->code,
            'symbol' => $currency->symbol,
        ];
    }
}
```

## 7. Response Example

عندما يطلب مستخدم سوري منتج سعره 100 دولار:

```json
{
  "id": 1,
  "name": "Product Name",
  "price": 1300000,
  "price_formatted": "ل.س 1,300,000.00",
  "currency": "SYP",
  "currency_symbol": "ل.س",
  "price_usd": 100
}
```

عندما يطلب مستخدم إماراتي نفس المنتج:

```json
{
  "id": 1,
  "name": "Product Name",
  "price": 367,
  "price_formatted": "د.إ 367.00",
  "currency": "AED",
  "currency_symbol": "د.إ",
  "price_usd": 100
}
```
