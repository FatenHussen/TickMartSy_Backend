# Flutter — إلغاء الفروع واسم المتجر من شاشة المنتج

> **أرسلوا هذا الملف لفريق Flutter فقط.**  
> **آخر تحديث:** 20 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> داشبورد: [`DASHBOARD_NO_SHOP_BRANCHES.md`](./DASHBOARD_NO_SHOP_BRANCHES.md)  
> ويب: [`WEB_NO_SHOP_BRANCHES.md`](./WEB_NO_SHOP_BRANCHES.md)

على شاشة المنتج كان يظهر اختيار **Branch / فرعة المنصة** لأن المنتج مربوط بمتجر.  
**هذا ملغى.** ما في فروع. ما في اختيار متجر. ما في اسم متجر على الشاشة.

الألوان والمقاسات تبقى. وقت التسليم يبقى. السلة تبقى.

ما في endpoint جديد: `GET /api/user/products/{id}`.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [ماذا يُحذف](#2-ماذا-يحذف)
3. [شكل الـ API](#3-شكل-الـ-api)
4. [Dart](#4-dart)
5. [السلة](#5-السلة)
6. [غلط vs صح](#6-غلط-vs-صح)
7. [Checklist](#7-checklist)

---

## 1) القاعدة

| قبل | بعد |
|-----|-----|
| Dropdown Branch + اسم المتجر | **لا UI** |
| `available_shops = [{ id, name }]` | `available_shops = []` دائماً |
| الزبون يختار فرع | الزبون يختار لون / مقاس فقط |

`shop_variants` = تركيبات المنتج (أزرق S، أسود L) — **مو** قائمة فروع.

---

## 2) ماذا يُحذف

احذفوا من شاشة المنتج بالكامل:

- Dropdown **Branch** / **الفرع** / **المتجر**
- أي نص «فرعة المنصة» أو اسم متجر فوق الألوان
- أي ويدجت مبني على `availableShops` / `available_shops` / `shopName`

```dart
// ❌ لا
for (final shop in product.availableShops) Text(shop.name);

// ✅ نعم
// available_shops دائماً []
```

`shop_variants[].shop_id` رقم داخلي — **لا** تعرضوه كاسم.

---

## 3) شكل الـ API

```http
GET /api/user/products/{id}
Accept-Language: ar
```

```json
{
  "available_shops": [],
  "attributes_map": [
    {
      "attribute": "لون",
      "type": "color",
      "values": ["أزرق", "أسود"]
    }
  ],
  "shop_variants": [
    {
      "id": 44,
      "variant_id": 91,
      "shop_id": 1,
      "sku": "JEANS-BLUE-S",
      "attributes": [
        { "attribute": "لون", "value": "أزرق", "type": "color", "hex": "#0000FF" }
      ]
    }
  ]
}
```

| حقل | بعد التحديث | الواجهة |
|-----|-------------|---------|
| `available_shops` | دائماً `[]` | **لا UI** |
| `shop_variants[].shop_id` | رقم أو `null` | لا تعرضوه |
| `shop_variants[].id` | هوية السلة | أضيفوا للسلة فقط |
| `shop_variants[].attributes` | لون / مقاس | اعرضوا الـ picker |

---

## 4) Dart

```dart
class ProductDetail {
  final List<ShopVariant> shopVariants;
  // لا حقل availableShops للعرض
}

class ShopVariant {
  final int? id;      // للسلة
  final int? shopId;  // داخلي — لا UI
  final List<VariantAttribute> attributes;
}

ShopVariant? findVariant(List<ShopVariant> variants, {String? color, String? size}) {
  return variants.cast<ShopVariant?>().firstWhere(
    (v) => matches(v!, color, size),
    orElse: () => null,
  );
}
```

لا `shopVariants.first` كاختيار وحيد إذا في أكثر من تركيبة.

---

## 5) السلة

```dart
{
  'shop_product_variant_id': selected.id,
  'quantity': 1,
}
```

إذا `id == null` أو `shopId == null` → اعرضوا السعر ولا تفعّلوا السلة.

---

## 6) غلط vs صح

**غلط**

```dart
if (product.availableShops.isNotEmpty) {
  showBranchPicker(product.availableShops);
}
```

**صح**

```dart
final selected = findVariant(product.shopVariants, color: color, size: size);
// لا Branch — فقط اللون / المقاس
```

---

## 7) Checklist

- [ ] احذفوا Dropdown Branch / اسم المتجر من شاشة المنتج
- [ ] لا تبنوا UI على `available_shops`
- [ ] الألوان / المقاسات من `attributes_map` و `shop_variants` تبقى
- [ ] السلة تبقى بـ `shop_product_variant_id` = `shop_variants[].id`
- [ ] وقت التسليم (`delivery_time`) يبقى إن كان موجود
- [ ] `findVariant` مو `shopVariants.first`
