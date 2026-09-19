# Flutter — متغيّران على شاشة المنتج (الاثنين يشتغلوا)

> **أرسلوا هذا الملف لفريق Flutter فقط.**  
> **آخر تحديث:** 20 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> داشبورد: [`DASHBOARD_PRODUCT_ALL_VARIANTS.md`](./DASHBOARD_PRODUCT_ALL_VARIANTS.md)  
> ويب: [`WEB_PRODUCT_ALL_VARIANTS.md`](./WEB_PRODUCT_ALL_VARIANTS.md)

الأدمن يضيف **أزرق S** و **أسود L**. التطبيق لازم يفعّل **الاثنين** — مو `shopVariants.first` فقط.  
ما في endpoint جديد: `GET /api/user/products/{id}`.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [شكل الاستجابة](#2-شكل-الاستجابة)
3. [اختيار اللون / المقاس](#3-اختيار-اللون--المقاس)
4. [Dart](#4-dart)
5. [السلة](#5-السلة)
6. [غلط vs صح](#6-غلط-vs-صح)
7. [Checklist](#7-checklist)

---

## 1) القاعدة

كل عنصر في `shop_variants` = تركيبة واحدة. لا تفترضوا كل لون × كل مقاس.

| اختيار | صف موجود؟ |
|--------|-----------|
| أزرق + S | نعم |
| أسود + L | نعم |
| أزرق + L / أسود + S | لا — غيّروا المقاس مع اللون |

المتغيّر `is_active: false` من الداش **ما يرجع** في الـ API.

`id == null` أو `shop_id == null` → اعرضوا السعر ولا تفعّلوا السلة.

---

## 2) شكل الاستجابة

```http
GET /api/user/products/{id}
Accept-Language: ar
```

```json
{
  "attributes_map": [
    { "attribute": "لون", "type": "color", "values": ["أزرق", "أسود"] },
    { "attribute": "قياس", "type": "square", "values": ["S", "L"] }
  ],
  "shop_variants": [
    {
      "id": 881,
      "variant_id": 91,
      "attributes": [
        { "id": 40, "attribute": "لون", "value": "أزرق", "type": "color", "hex": "#0000FF" },
        { "id": 31, "attribute": "قياس", "value": "S", "type": "square", "hex": null }
      ],
      "quantity": 10,
      "shop_id": 1,
      "has_variant_images": true,
      "images": [{ "id": 44, "path": "https://…/blue.webp" }]
    },
    {
      "id": 882,
      "variant_id": 92,
      "attributes": [
        { "id": 41, "attribute": "لون", "value": "أسود", "type": "color", "hex": "#000000" },
        { "id": 32, "attribute": "قياس", "value": "L", "type": "square", "hex": null }
      ],
      "quantity": 10,
      "shop_id": 1,
      "has_variant_images": false,
      "images": [{ "id": 10, "path": "https://…/main.webp" }]
    }
  ]
}
```

- `hex` لدائرة اللون — بلا قيمة الدائرة فاضية والأسود «ما ينضغط».
- صور المتغيّر: `has_variant_images` + `images[].path` وإلا صور المنتج — [`PRODUCT_VARIANT_IMAGES_WEB_DASHBOARD.md`](./PRODUCT_VARIANT_IMAGES_WEB_DASHBOARD.md).

---

## 3) اختيار اللون / المقاس

طابقوا `attribute` + `value` (أو `attributes[].id`).  
عند تغيير اللون: إذا المقاس الحالي غير موجود، خذوا أول مقاس متاح لهاللون.

---

## 4) Dart

```dart
class VariantAttr {
  final int? id;
  final String attribute;
  final String value;
  final String? type;
  final String? hex;

  VariantAttr({
    this.id,
    required this.attribute,
    required this.value,
    this.type,
    this.hex,
  });

  factory VariantAttr.fromJson(Map<String, dynamic> json) => VariantAttr(
        id: json['id'] as int?,
        attribute: json['attribute']?.toString() ?? '',
        value: json['value']?.toString() ?? '',
        type: json['type']?.toString(),
        hex: json['hex']?.toString(),
      );
}

ShopVariant? findVariant(
  List<ShopVariant> variants,
  Map<String, String> selected,
) {
  final entries = selected.entries.where((e) => e.value.isNotEmpty);
  if (entries.isEmpty) return null;
  for (final v in variants) {
    final ok = entries.every(
      (e) => v.attributes.any((a) => a.attribute == e.key && a.value == e.value),
    );
    if (ok) return v;
  }
  return null;
}

List<String> sizesForColor(List<ShopVariant> variants, String color) {
  return variants
      .where((v) => v.attributes.any((a) => a.type == 'color' && a.value == color))
      .expand((v) => v.attributes.where((a) => a.type == 'square').map((a) => a.value))
      .toSet()
      .toList();
}

void onColorTap(String color) {
  selectedColor = color;
  final sizes = sizesForColor(shopVariants, color);
  if (!sizes.contains(selectedSize)) {
    selectedSize = sizes.isEmpty ? '' : sizes.first;
  }
}
```

Swatch:

```dart
CircleAvatar(
  backgroundColor: hex != null ? colorFromHex(hex!) : Colors.grey.shade300,
)
```

---

## 5) السلة

```dart
bool get canAddToCart =>
    selected?.id != null &&
    selected?.shopId != null &&
    (selected?.quantity ?? 0) > 0;
```

```json
{ "shop_product_variant_id": 882, "quantity": 1 }
```

`shop_product_variant_id` = `selected.id` (882 للأسود L) — **مو** دائماً `shopVariants.first.id`.

---

## 6) غلط vs صح

**غلط:**

```dart
final v = product.shopVariants.first;
prefs.setString('size', 'S'); // ثابت بعد اختيار أسود
```

**صح:**

```dart
final v = findVariant(product.shopVariants, {
  'لون': selectedColor,
  'قياس': selectedSize,
});
cart.add(shopProductVariantId: v!.id!);
```

---

## 7) Checklist

- [ ] موديل `attributes` فيه `id` و `hex` اختياريين
- [ ] لا تعتمدوا على `shopVariants.first` بعد ما المستخدم يختار
- [ ] تغيير اللون يعيد تعيين المقاس إذا لزم
- [ ] دائرة اللون من `hex`
- [ ] `canAddToCart` على المتغيّر المختار
- [ ] معرض الصور يتبدل مع `has_variant_images`
- [ ] بعد `git pull` + إعادة حفظ المنتج: جرّبوا التركيبات الاثنتين
