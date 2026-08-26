# حذف المتغيّرات (Variants) مع تنبيه بما سيُحذف أو يتأثر

## ما الذي تغيّر

سابقاً كان الحذف **ممنوعاً** إذا كان المتغيّر مرتبطاً بطلبات نشطة، وكان الـ API يرجع `422`
ولا يمكن إتمام الحفظ.

الآن الحذف **مسموح دائماً**، لكنه يمرّ بخطوتين:

1. الطلب الأول بدون تأكيد يرجع `409` مع تفاصيل كل ما سيتأثر.
2. الداشبورد يعرض التنبيه للمستخدم، ثم يعيد نفس الطلب مع `confirm=true` فيتم الحذف.

إذا لم يكن هناك أي شيء مرتبط، يتم الحذف مباشرة من أول طلب ويرجع `200`.

## ضمانات مهمة

- الحذف أصبح **soft delete** لكل من `product_variants` و`shop_product_variants`.
- **سجلات الطلبات لا تُحذف أبداً**، حتى لو حُذف المتغيّر أثناء وجود طلبات نشطة.
  الطلب يبقى ظاهراً بكامل تفاصيله (الاسم، الصورة، السعر، الخصائص) لأن العلاقات
  تقرأ النسخ المحذوفة (`withTrashed`).
- تحديث المخزون للطلبات الجارية (إلغاء / إرجاع) يستمر بالعمل بعد الحذف.
- صور المتغيّر تبقى محفوظة على القرص عند الحذف الناعم.

## نقاط النهاية

| الغرض | Method | Endpoint |
|---|---|---|
| معاينة أثر الحذف (لا يحذف شيئاً) | GET | `/api/admin/product-variants/{id}/delete-impact` |
| حذف متغيّر منتج | DELETE | `/api/admin/product-variants/{id}?confirm=true` |
| معاينة أثر الحذف | GET | `/api/admin/shop-product-variants/{id}/delete-impact` |
| حذف ربط متجر | DELETE | `/api/admin/shop-product-variants/{id}?confirm=true` |

الصلاحيات: `productvariant.delete` و`shopproductvariant.delete`.

`confirm` يُقبل في الـ query string أو في جسم الطلب، وتُقبل القيم `true` / `1`.

## شكل الاستجابة

### 409 — يحتاج تأكيد

```json
{
  "status": false,
  "message": "حذف هذا المتغير سيؤثر على بيانات مرتبطة. راجع التفاصيل ثم أعد إرسال الطلب مع confirm=true للتأكيد.",
  "requires_confirmation": true,
  "data": {
    "type": "product_variant",
    "id": 12,
    "requires_confirmation": true,
    "counts": {
      "active_orders": 2,
      "past_orders": 5,
      "basket_items": 4,
      "recipe_items": 0,
      "scheduled_items": 1,
      "gifts": 0,
      "shop_variants": 3,
      "images": 2
    },
    "warnings": [
      {
        "key": "active_orders",
        "count": 2,
        "message": "مرتبط بـ 2 طلب نشط. سجل الطلبات سيبقى محفوظاً ولن يتأثر."
      },
      {
        "key": "basket_items",
        "count": 4,
        "message": "سيتم حذف 4 عنصر من سلات المستخدمين."
      }
    ],
    "active_orders": [
      { "id": 1, "order_code": "ORD-260816-00001", "status": "pending" }
    ]
  }
}
```

- `warnings[].message` جاهزة للعرض مباشرة وتتبع لغة الطلب (`ar` / `en`).
- `active_orders` عيّنة بحد أقصى 10 طلبات نشطة لعرضها في نافذة التأكيد.
- `counts` للاستخدام إذا أردت صياغة نص مخصّص.

### 200 — تم الحذف

نفس شكل `data`، ويصف ما تم حذفه أو تأثّره فعلياً:

```json
{
  "status": true,
  "message": "تم حذف المتغير بنجاح.",
  "data": { "type": "product_variant", "id": 12, "counts": {}, "warnings": [], "active_orders": [] }
}
```

## دلالة كل مفتاح في `counts`

| المفتاح | المعنى |
|---|---|
| `active_orders` | طلبات بحالة `pending` / `preparing` / `out_delivery` — **تبقى كما هي** |
| `past_orders` | طلبات منتهية أو ملغاة — **تبقى كما هي** |
| `shop_variants` | روابط المتاجر التي ستُحذف مع المتغيّر |
| `basket_items` | عناصر سلات المستخدمين التي ستُحذف |
| `recipe_items` | عناصر الوصفات التي ستُحذف |
| `scheduled_items` | عناصر السلات المجدولة التي سيُلغى ربطها |
| `gifts` | الهدايا التي سيُلغى ربطها |
| `images` | صور المتغيّر (تبقى محفوظة) |

## مثال تطبيقي

```js
async function deleteVariant(id) {
  let res = await api.delete(`/admin/product-variants/${id}`);

  if (res.status === 409 && res.data.requires_confirmation) {
    const impact = res.data.data;
    const confirmed = await showWarningDialog({
      title: 'تأكيد الحذف',
      lines: impact.warnings.map((w) => w.message),
      orders: impact.active_orders,
    });

    if (!confirmed) return;

    res = await api.delete(`/admin/product-variants/${id}?confirm=true`);
  }

  return res.data;
}
```

لعرض التنبيه **قبل** أن يضغط المستخدم على زر الحذف، استخدم `GET .../delete-impact`
فهو يرجع نفس كائن `data` دون تنفيذ أي حذف.
