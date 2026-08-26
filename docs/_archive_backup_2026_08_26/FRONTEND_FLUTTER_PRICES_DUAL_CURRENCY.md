# Flutter (تطبيق المستخدم) — الأسعار بالعملتين + ملاحظات آب 2026

> لتطبيق Flutter فقط. المسارات تحت `/api/user`.  
> الدليل الشامل الأوسع: [`FRONTEND_FLUTTER_COMPLETE.md`](./FRONTEND_FLUTTER_COMPLETE.md)

---

## الفهرس

1. [عرض السعر دولار + ليرة](#1-عرض-السعر)
2. [وين تستخدم أي حقل](#2-وين-تستخدم-أي-حقل)
3. [موديل مساعد](#3-موديل-مساعد)
4. [Checklist](#4-checklist)

---

## 1) عرض السعر

الباك يخزّن الأسعار بالدولار ويُرجع:

- السعر بعملة المستخدم الحالية: `price`, `price_formatted`, `currency`, `currency_symbol`
- نسختين ثابتتين للعرض: `price_currencies.USD` و `price_currencies.SYP`

**لا تحسب سعر الصرف داخل التطبيق.** اعرض قيم الـ API كما هي.

### مثال من تفاصيل منتج

```json
{
  "price": 5.01,
  "currency": "SYP",
  "currency_symbol": "ل.س",
  "price_formatted": "ل.س 5.01",
  "price_currencies": {
    "USD": {
      "amount": 0.000385,
      "currency": "USD",
      "symbol": "$",
      "formatted": "$ 0.000385"
    },
    "SYP": {
      "amount": 5.01,
      "currency": "SYP",
      "symbol": "SYP",
      "formatted": "SYP 5.01"
    }
  },
  "price_after_discount": 4.5,
  "price_after_discount_formatted": "ل.س 4.5",
  "price_after_discount_currencies": {
    "USD": { "amount": 0.000346, "formatted": "$ 0.000346" },
    "SYP": { "amount": 4.5, "formatted": "SYP 4.5" }
  }
}
```

نفس النمط يظهر على: قوائم المنتجات، السلة، الطلبات، السلال، الوصفات (`*_currencies`).

---

## 2) وين تستخدم أي حقل

| الشاشة | الحقل الأساسي | اختياري |
|--------|---------------|---------|
| كرت منتج في القائمة | `price_after_discount_formatted` أو `price_formatted` | خط على السعر الأصلي إن وُجد خصم |
| صفحة المنتج | `price_after_discount` (+ `price` مشطوب عند الخصم) | سطر صغير USD/SYP من `*_currencies` إن التصميم يطلبه |
| السلة / الدفع | مبالغ الـ API المحوَّلة (`final_price_formatted` …) | — |
| ملخص الطلب | `total_formatted` / `subtotal_formatted` | — |

### قواعد

1. للشراء اعرض **السعر بعد الخصم** إن وُجد، وإلا السعر العادي.
2. لا ترسل أسعاراً من التطبيق عند إضافة للسلة — أرسل `shop_product_variant_id` + `quantity` فقط؛ السيرفر يحسب السعر.
3. عند تغيير عملة المستخدم أعد جلب الشاشات الحساسة للسعر (أو اعتمد على الحقول المحوَّلة في الرد التالي).

---

## 3) موديل مساعد

```dart
class MoneyAmount {
  final double? amount;
  final String? currency;
  final String? symbol;
  final String? formatted;

  const MoneyAmount({this.amount, this.currency, this.symbol, this.formatted});

  factory MoneyAmount.fromJson(Map<String, dynamic>? json) {
    if (json == null) return const MoneyAmount();
    return MoneyAmount(
      amount: (json['amount'] as num?)?.toDouble(),
      currency: json['currency'] as String?,
      symbol: json['symbol'] as String?,
      formatted: json['formatted'] as String?,
    );
  }
}

class DualMoney {
  final MoneyAmount? usd;
  final MoneyAmount? syp;

  const DualMoney({this.usd, this.syp});

  factory DualMoney.fromJson(Map<String, dynamic>? json) {
    if (json == null) return const DualMoney();
    return DualMoney(
      usd: MoneyAmount.fromJson(json['USD'] as Map<String, dynamic>?),
      syp: MoneyAmount.fromJson(json['SYP'] as Map<String, dynamic>?),
    );
  }

  /// مثال عرض: "$ 1.5 / SYP 19500"
  String get dualLabel {
    final parts = <String>[
      if (usd?.formatted != null) usd!.formatted!,
      if (syp?.formatted != null) syp!.formatted!,
    ];
    return parts.join(' / ');
  }
}

/// استخراج من أي مورد فيه withCurrency
DualMoney readPriceCurrencies(Map<String, dynamic> json, {String key = 'price'}) {
  return DualMoney.fromJson(json['${key}_currencies'] as Map<String, dynamic>?);
}
```

### ويدجت بسيط

```dart
Text(
  product.priceAfterDiscountFormatted
      ?? product.priceFormatted
      ?? '',
  style: priceStyle,
);

// إن التصميم يطلب عملتين معاً:
Text(
  readPriceCurrencies(json, key: 'price_after_discount').dualLabel,
  style: secondaryPriceStyle,
);
```

---

## 4) Checklist

- [ ] كروت المنتج تستخدم `*_formatted` من الـ API (مو حساب يدوي)
- [ ] صفحة المنتج: السعر بعد الخصم بارز؛ الأصلي مشطوب عند وجود خصم
- [ ] إن لزم عرض USD+SYP: من `*_currencies` فقط
- [ ] السلة/الطلب: لا تعيد تسعير البنود محلياً
- [ ] بعد تغيير العملة: حدّث الشاشات أو أعد الطلبات

> حذف الفئات وتسعير الأدمن بالليرة = **داشبورد فقط** — انظر  
> `FRONTEND_DASHBOARD_PRODUCT_PRICE_AND_CATEGORY_DELETE.md`
