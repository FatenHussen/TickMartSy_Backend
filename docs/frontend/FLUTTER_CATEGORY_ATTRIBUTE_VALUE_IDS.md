# Flutter — قيم الصفات بالـ ID (إعادة التسمية ما تقطع المنتج)

> **أرسلوا هذا الملف لفريق Flutter فقط.**  
> **آخر تحديث:** 20 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> **الباك جاهز بعد `git pull`**  
> داشبورد: [`DASHBOARD_CATEGORY_ATTRIBUTE_VALUE_IDS.md`](./DASHBOARD_CATEGORY_ATTRIBUTE_VALUE_IDS.md)  
> ويب: [`WEB_CATEGORY_ATTRIBUTE_VALUE_IDS.md`](./WEB_CATEGORY_ATTRIBUTE_VALUE_IDS.md)

الأدمن يغيّر «صغير» → «XS». الـ ID يبقى. المنتج ما ينمسح.  
التطبيق يعرض الاسم الجديد ويصفّي / يختار بالـ ID.

ما في endpoint جديد.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [فلاتر الفئة — chips](#2-فلاتر-الفئة--chips)
3. [شاشة المنتج](#3-شاشة-المنتج)
4. [Dart](#4-dart)
5. [غلط vs صح](#5-غلط-vs-صح)
6. [Checklist](#6-checklist)

---

## 1) القاعدة

| شاشة | المفتاح الثابت | النص |
|------|----------------|------|
| chips / فلتر | `values[].id` → `attribute_values=31` | `name` من آخر GET |
| picker | `attributes_map[].options[].id` أو `attributes[].id` | `name` / `value` |
| سلة | `shop_variants[].id` | — |

لا تخزّنوا «صغير» في `SharedPreferences`. الاسم يتغيّر. الـ ID لا.

```http
GET /api/user/categories/{categoryId}/attributes
GET /api/user/products?category_id=12&attribute_values=31,40
GET /api/user/products/{id}
```

---

## 2) فلاتر الفئة — chips

```http
GET /api/user/categories/{categoryId}/attributes
Accept-Language: ar
```

```json
{
  "data": [
    {
      "id": 11,
      "root_category_id": 5,
      "name": "القياس",
      "type": "square",
      "values": [
        { "id": 31, "name": "XS" },
        { "id": 32, "name": "XL" }
      ]
    }
  ]
}
```

- `selectedIds` = `Set<int>`
- نص الـ chip = `value.name`
- طلب: `attribute_values=31,40` — **OR**
- كاش بـ `root_category_id` — بعد إعادة تسمية: أعدوا الجلب

---

## 3) شاشة المنتج

```http
GET /api/user/products/{id}
Accept-Language: ar
```

```json
{
  "attributes_map": [
    {
      "id": 10,
      "attribute": "لون",
      "type": "color",
      "values": ["أحمر", "أزرق"],
      "options": [
        { "id": 40, "name": "أحمر", "hex": "#FF0000" },
        { "id": 41, "name": "أزرق", "hex": "#0000FF" }
      ]
    },
    {
      "id": 11,
      "attribute": "قياس",
      "type": "square",
      "values": ["XS", "XL"],
      "options": [
        { "id": 31, "name": "XS", "hex": null },
        { "id": 32, "name": "XL", "hex": null }
      ]
    }
  ],
  "shop_variants": [
    {
      "id": 881,
      "attributes": [
        { "id": 40, "attribute": "لون", "value": "أحمر", "type": "color", "hex": "#FF0000" },
        { "id": 31, "attribute": "قياس", "value": "XS", "type": "square", "hex": null }
      ]
    }
  ]
}
```

- فضّلوا `options[].id` على `values` (strings)
- swatch: `hex`
- بعد إعادة التسمية: نفس `id`، الاسم الجديد
- لون و قياس منفصلين — لا `أحمر XS` في زر واحد

عند تغيير اللون: إذا المقاس غير موجود، أول مقاس متاح من `shop_variants`.  
السلة: `shop_product_variant_id` = `shop_variants[].id` المختار.

---

## 4) Dart

```dart
class AttributeOption {
  final int id;
  final String name;
  final String? hex;

  AttributeOption({required this.id, required this.name, this.hex});

  factory AttributeOption.fromJson(Map<String, dynamic> json) =>
      AttributeOption(
        id: json['id'] as int,
        name: json['name'].toString(),
        hex: json['hex'] as String?,
      );
}

Map<String, dynamic> productFilterParams({
  required int categoryId,
  required Set<int> attributeValueIds,
}) =>
    {
      'category_id': categoryId,
      if (attributeValueIds.isNotEmpty)
        'attribute_values': attributeValueIds.join(','),
    };

ShopVariant? findVariant(List<ShopVariant> variants, Set<int> selectedIds) {
  if (selectedIds.isEmpty) return null;
  for (final v in variants) {
    final ids = v.attributes.map((a) => a.id).toSet();
    if (selectedIds.every(ids.contains)) return v;
  }
  return null;
}
```

Chip:

```dart
FilterChip(
  key: ValueKey(value.id),
  label: Text(value.name),
  selected: selectedIds.contains(value.id),
  onSelected: (on) => on ? selectedIds.add(value.id) : selectedIds.remove(value.id),
)
```

---

## 5) غلط vs صح

**غلط:**

```dart
selectedNames.add('صغير');
prefs.setString('selected_size', 'صغير');
final variant = product.shopVariants.first;
final label = attrs.map((a) => a.value).join(' '); // أحمر XS
```

**صح:**

```dart
selectedIds.add(31);
final variant = findVariant(product.shopVariants, selectedIds);
addToCart(variant?.id);
```

---

## 6) Checklist

- [ ] chips: `ValueKey(value.id)` و `attribute_values` كـ IDs
- [ ] picker من `attributes_map[].options[]` — صفة لكل صف
- [ ] مطابقة المتغيّر بـ `attributes[].id`
- [ ] السلة: `shop_variants[].id` المختار — مو `first`
- [ ] بعد «صغير» → «XS»: الاسم يتبدّل والمتغيّر يبقى
- [ ] لا `أحمر XS` في خيار واحد
- [ ] لا أسماء في `SharedPreferences`
