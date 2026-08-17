# شاشة خصائص الصنف (`/categories/attributes`) — الحذف مع تنبيه بالعناصر المرتبطة

## ما الذي تغيّر

سابقاً كانت الشاشة تعرض خطأ أحمر ويتوقف كل شيء:

> لا يمكن حذف خاصية الصنف لأنها مستخدمة في 8 متغير من المنتجات.

الآن **الحذف مسموح دائماً**. الرسالة لم تعد خطأً نهائياً، بل أصبحت **تنبيهاً** يشرح
ما هو المرتبط وما الذي سيحدث، مع تبويب يعرض كل العناصر المرتبطة بالتفصيل.

## التدفق المطلوب في الشاشة

1. المستخدم يضغط "حذف" على صف الخاصية.
2. الداشبورد ينادي `GET /category-attributes/{id}/delete-impact` ويعرض نافذة التأكيد
   الحالية نفسها، لكن مع قائمة التنبيهات (`warnings`) بدل نص "هل أنت متأكد؟" فقط.
3. داخل نفس النافذة يوجد تبويبان:
   - **التنبيه**: رسائل `warnings` الجاهزة للعرض.
   - **العناصر المرتبطة**: جدول من `GET /category-attributes/{id}/linked-items` (مع صفحات).
4. عند الضغط على "حذف": `DELETE /category-attributes/{id}?confirm=true`.

يمكن أيضاً تخطّي الخطوة 2 والذهاب مباشرة إلى `DELETE` بدون `confirm`؛ إن كانت الخاصية
مرتبطة بشيء يرجع `409` بنفس بيانات `delete-impact`، فتعرض النافذة عندها.

## نقاط النهاية

| الغرض | Method | Endpoint | الصلاحية |
|---|---|---|---|
| ملخّص أثر الحذف | GET | `/api/admin/category-attributes/{id}/delete-impact` | `categoryattribute.delete` |
| تبويب العناصر المرتبطة | GET | `/api/admin/category-attributes/{id}/linked-items?page=1&per_page=10` | `categoryattribute.view` |
| تنفيذ الحذف | DELETE | `/api/admin/category-attributes/{id}?confirm=true` | `categoryattribute.delete` |

`confirm` يُقبل في الـ query string أو في جسم الطلب، وتُقبل القيم `true` / `1`.

## 1) ملخّص أثر الحذف

`GET /api/admin/category-attributes/1/delete-impact`

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "type": "category_attribute",
    "id": 1,
    "name": { "ar": "اللون", "en": "Color" },
    "requires_confirmation": true,
    "counts": {
      "attribute_values": 30,
      "product_variants": 11,
      "products": 5,
      "active_orders": 0
    },
    "warnings": [
      { "key": "attribute_values", "count": 30, "message": "سيتم حذف 30 قيمة تابعة لهذه الخاصية." },
      { "key": "product_variants", "count": 11, "message": "مستخدمة في 11 متغير من المنتجات، وسيتم إزالة قيم هذه الخاصية منها دون حذف المتغيرات." },
      { "key": "products", "count": 5, "message": "تتأثر 5 منتج." }
    ]
  }
}
```

`requires_confirmation = false` يعني لا يوجد شيء مرتبط، فيمكن الحذف مباشرة بدون نافذة تحذير.

### معنى كل مفتاح

| المفتاح | المعنى | ماذا يحدث عند التأكيد |
|---|---|---|
| `attribute_values` | قيم الخاصية (أحمر، أزرق، ...) | **تُحذف** |
| `product_variants` | متغيّرات المنتجات التي تستخدم هذه القيم | **تبقى**، وتُزال منها قيم هذه الخاصية فقط |
| `products` | عدد المنتجات المتأثرة | تبقى كما هي |
| `active_orders` | طلبات نشطة تحتوي هذه المتغيّرات | **لا تتأثر إطلاقاً**، سجل الطلبات محفوظ |

النقطة المهمة للمستخدم: **لا يتم حذف أي منتج أو متغيّر أو طلب**. يُحذف فقط تعريف الخاصية
وقيمها، وتُنظّف الإشارة إليها من المتغيّرات.

## 2) تبويب العناصر المرتبطة

`GET /api/admin/category-attributes/1/linked-items?page=1&per_page=10`

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "items": [
      {
        "variant_id": 73,
        "variant_name": { "ar": "أحمر - وسط", "en": "Red - M" },
        "sku": "SKU-73",
        "variant_image": "https://.../variant.jpg",
        "product": {
          "id": 22,
          "product_number": "P-0022",
          "name": "سمنة",
          "image": "https://.../product.jpg",
          "category": { "id": 19, "name": "سمنة" }
        },
        "used_values": [
          { "id": 6, "name": "بنفسجي", "hex": "#800080" }
        ]
      }
    ],
    "pagination": { "total": 11, "per_page": 10, "current_page": 1, "last_page": 2 }
  }
}
```

أعمدة الجدول المقترحة: صورة المنتج، اسم المنتج، اسم المتغيّر / SKU، الفئة، القيم المستخدمة
(كـ chips، ومع دائرة لون إذا كان `hex` موجوداً).

`variant_name` قد يكون `{}` أو `[]` عندما لا يملك المتغيّر اسماً مترجَماً — استخدم اسم المنتج
كبديل في هذه الحالة.

## 3) تنفيذ الحذف

بدون `confirm` وفي وجود ارتباطات → **409**:

```json
{
  "status": false,
  "message": "حذف هذه الخاصية سيؤثر على عناصر مرتبطة. راجع التفاصيل ثم أعد إرسال الطلب مع confirm=true للتأكيد.",
  "requires_confirmation": true,
  "data": { "...": "نفس كائن delete-impact" }
}
```

مع `confirm=true` → **200**:

```json
{
  "status": true,
  "message": "تم حذف خاصية الصنف بنجاح.",
  "data": { "type": "category_attribute", "id": 1, "counts": {}, "warnings": [] }
}
```

## مثال تطبيقي

```js
async function deleteAttribute(id) {
  const { data: impact } = await api.get(`/admin/category-attributes/${id}/delete-impact`);

  if (impact.data.requires_confirmation) {
    const confirmed = await openDeleteDialog({
      title: 'تأكيد الحذف',
      warnings: impact.data.warnings.map((w) => w.message),
      loadLinkedItems: (page) =>
        api.get(`/admin/category-attributes/${id}/linked-items`, { params: { page, per_page: 10 } }),
    });

    if (!confirmed) return;
  }

  const res = await api.delete(`/admin/category-attributes/${id}`, { params: { confirm: true } });
  toast.success(res.data.message);
  refreshTable();
}
```

## ملاحظات للتعامل مع الشاشة الحالية

- احذف معالجة الخطأ القديمة التي تعرض `422` باللون الأحمر لهذه العملية؛ لم تعد تُرجع من الـ API.
- عامِل `409` كحالة "يحتاج تأكيد" وليس كخطأ، ولا تعرضها في شريط الأخطاء العلوي.
- نافذة "تأكيد الحذف" الحالية تبقى كما هي، ويُضاف لها فقط: قائمة التنبيهات + تبويب العناصر المرتبطة.
- الرسائل في `warnings[].message` مترجمة حسب لغة الطلب (`ar` / `en`)، فلا حاجة لصياغتها في الفرونت.
- نفس النمط مطبّق على حذف متغيّرات المنتجات، انظر `FRONTEND_DASHBOARD_VARIANT_DELETE_CONFIRM.md`.
