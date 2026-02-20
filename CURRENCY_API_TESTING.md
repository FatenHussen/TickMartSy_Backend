# اختبار APIs نظام العملات

## User APIs

### 1. جلب كل العملات النشطة
```http
GET /api/user/currencies
```

Response:
```json
{
  "status": true,
  "message": "تم جلب العملات بنجاح",
  "data": [
    {
      "id": 1,
      "code": "USD",
      "name": "دولار أمريكي",
      "symbol": "$",
      "is_default": true
    },
    {
      "id": 2,
      "code": "SYP",
      "name": "ليرة سورية",
      "symbol": "ل.س",
      "is_default": false
    }
  ]
}
```

### 2. جلب عملة المستخدم الحالي (يحتاج Authentication)
```http
GET /api/user/currencies/my-currency
Authorization: Bearer {token}
```

Response:
```json
{
  "status": true,
  "message": "تم جلب عملة المستخدم بنجاح",
  "data": {
    "id": 2,
    "code": "SYP",
    "name": {
      "en": "Syrian Pound",
      "ar": "ليرة سورية"
    },
    "localized_name": "ليرة سورية",
    "symbol": "ل.س",
    "exchange_rate": "13000.000000",
    "is_default": false,
    "is_active": true
  }
}
```

### 3. تحديث عملة المستخدم (يحتاج Authentication)
```http
PATCH /api/user/currencies/update-currency
Authorization: Bearer {token}
Content-Type: application/json

{
  "currency_id": 3
}
```

Response:
```json
{
  "status": true,
  "message": "تم تحديث عملة المستخدم بنجاح",
  "data": {
    "id": 3,
    "code": "AED",
    "name": {
      "en": "UAE Dirham",
      "ar": "درهم إماراتي"
    },
    "localized_name": "درهم إماراتي",
    "symbol": "د.إ",
    "exchange_rate": "3.670000",
    "is_default": false,
    "is_active": true
  }
}
```

---

## Admin APIs

### 1. جلب كل العملات
```http
GET /api/admin/currencies
Authorization: Bearer {admin_token}
```

### 2. إنشاء عملة جديدة
```http
POST /api/admin/currencies
Authorization: Bearer {admin_token}
Content-Type: application/json

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

### 3. تحديث عملة
```http
PUT /api/admin/currencies/1
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "exchange_rate": 13500.00
}
```

### 4. حذف عملة
```http
DELETE /api/admin/currencies/5
Authorization: Bearer {admin_token}
```

### 5. تفعيل/تعطيل عملة
```http
PATCH /api/admin/currencies/2/toggle-status
Authorization: Bearer {admin_token}
```

---

## أمثلة على استخدام التحويل في Resources

### مثال 1: Product Resource بسيط
```php
use App\Traits\HasCurrencyConversion;

class ProductResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            ...$this->withCurrency($this->price, 'price'),
        ];
    }
}
```

Output للمستخدم السوري:
```json
{
  "id": 1,
  "name": "Product",
  "price_usd": 100,
  "price": 1300000,
  "currency": "SYP",
  "currency_symbol": "ل.س",
  "price_formatted": "ل.س 1,300,000.00"
}
```

### مثال 2: Order Resource مع أسعار متعددة
```php
class OrderResource extends JsonResource
{
    use HasCurrencyConversion;

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            ...$this->withCurrency($this->subtotal, 'subtotal'),
            ...$this->withCurrency($this->tax, 'tax'),
            ...$this->withCurrency($this->delivery_fee, 'delivery_fee'),
            ...$this->withCurrency($this->total, 'total'),
        ];
    }
}
```

---

## ملاحظات مهمة

1. كل الأسعار في الداتابيز مخزنة بالدولار
2. التحويل يتم تلقائياً عند العرض حسب عملة المستخدم
3. إذا المستخدم ما اختار عملة، العملة الافتراضية هي الدولار
4. لا يمكن تعطيل العملة الافتراضية
5. عند تعيين عملة جديدة كافتراضية، يتم إلغاء الافتراضية من العملات الأخرى تلقائياً
