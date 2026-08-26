# آخر تحديث — داشبورد — 26 آب 2026

> **أرسلوا هذا الملف لفريق الداشبورد فقط.**  
> Base: `/api/admin` + Admin token.

---

## 1) بلدان المبيع — أخفوا حقل الأيقونة

**المشكلة:** فورم إنشاء/تعديل بلد المبيع يعرض رفع ملف بعنوان خاطئ («يلزم تسمية رمز بلد البيع»).

**المطلوب:**

| الحقل | في الفورم؟ | ملاحظات |
|-------|------------|---------|
| `name.ar` / `name.en` | نعم | مطلوب |
| `is_active` | نعم | توغل تفعيل |
| `icon` | **لا — أخفوه واحذفوه** | يأتي من السيدر (إيموجي علم) — لا ترسلوه |

- `GET /api/admin/sale-countries` ما زال يرجّع `icon` للعرض فقط (مثل `🇸🇾`).
- عند إنشاء منتج: إن لم تُرسل `sale_country_id` → الباك يضع **سوريا** تلقائياً.
- في select بلد المبيع: اجعل **سوريا** الخيار الافتراضي المحدد.

```js
// POST/PUT sale-countries — بدون icon
{
  name: { ar: 'سوريا', en: 'Syria' },
  is_active: true,
}
```

---

## 2) فورم المنتج — الأسعار (سطر أو سطرين)

**المشكلة:** السعر يظهر مرتين (`price` قابل للتعديل + `price_after_discount` كـ «سعر المنتج»).

**المطلوب:**

**سطر 1:** سعر `$` · سعر `ل.س` · نوع الخصم · قيمة الخصم · سعر بعد الخصم (عرض فقط) · التكلفة  
**سطر 2:** الكمية · الوحدة

| واجهة | يُرسل؟ | الحقل |
|-------|--------|--------|
| سعر دولار | نعم | `price` |
| سعر ليرة | نعم إن ما في دولار | `price_syp` |
| تكلفة دولار / ليرة | نعم | `cost_price` أو `cost_price_syp` |
| نوع/قيمة الخصم | نعم | `discount_type`, `discount` |
| سعر بعد الخصم | **لا** | من الرد: `price_after_discount` + `_currencies` |
| `*_currencies` | **لا ترسل** | عرض فقط |

إذا أُرسل `price` و `price_syp` معاً → الـ API يعتمد الدولار.

```js
{
  price: usd || undefined,
  price_syp: !usd && syp ? syp : undefined,
  cost_price: costUsd || undefined,
  cost_price_syp: !costUsd && costSyp ? costSyp : undefined,
  discount_type: 'none' | 'percentage' | 'fixed',
  discount: 0,
  quantity: 10,
  unit_id: 1,
}
// لا ترسل: price_currencies, price_after_discount, price_after_discount_currencies
```

---

## 3) حذف الفئات — مع تنبيه (مو توست أحمر)

| الغرض | Endpoint |
|-------|----------|
| ملخص الأثر | `GET /api/admin/categories/{id}/delete-impact` |
| العناصر المرتبطة | `GET /api/admin/categories/{id}/linked-items?page=1&per_page=10` |
| تنفيذ | `DELETE /api/admin/categories/{id}?confirm=true` |

**تدفق الشاشة:**

1. اضغط حذف → نادِ `delete-impact`
2. نافذة تأكيد بتبويبين: **التنبيه** (`warnings`) + **العناصر المرتبطة** (`linked-items`)
3. بعد الموافقة → `DELETE ?confirm=true`
4. **`409` + `requires_confirmation: true` = طلب تأكيد** — لا تعرضه كخطأ أحمر

**عند التأكيد:**

| مفتاح | ماذا يحدث |
|-------|-----------|
| `child_categories` | تُحذف الفئات الفرعية |
| `products` | حذف ناعم + فك الارتباط (الطلبات تبقى) |
| `baskets` | تُحذف |
| `pages` | تُحذف صفحة الفئة وأقسامها |
| `recipe_links` | يُفك الارتباط فقط |

```js
async function deleteCategory(id) {
  const { data: res } = await api.get(`/admin/categories/${id}/delete-impact`);
  const impact = res.data;

  if (impact.requires_confirmation) {
    const ok = await openDeleteDialog({
      warnings: impact.warnings.map((w) => w.message),
      loadLinkedItems: (page) =>
        api.get(`/admin/categories/${id}/linked-items`, {
          params: { page, per_page: 10 },
        }),
    });
    if (!ok) return;
  }

  await api.delete(`/admin/categories/${id}`, { params: { confirm: true } });
}
```

---

## 4) قناة البيع — للموقع أو ربط بمتجر

> التفصيل الكامل: [`FRONTEND_DASHBOARD_PRODUCT_SALE_CHANNEL.md`](./FRONTEND_DASHBOARD_PRODUCT_SALE_CHANNEL.md)

| واجهة | أرسل | ملاحظات |
|-------|------|---------|
| **للموقع** (افتراضي) | `sale_channel=platform` | **لا** متجر ولا بائع في الفورم — **لا ترسل** `shop_variants` |
| **ربط بمتجر** | `sale_channel=shop` + `shop_variants[0][shop_id]=…` | select فرع إلزامي |

- الرد يرجع `sale_channel` في GET منتج / قائمة
- تحويل لموقع عند التعديل: أرسل `sale_channel=platform` فقط
- بدون فرع منصة (`vendor_id=1` + `is_default`) منتج الموقع ما ينربط للسلة

```js
// للموقع
{ sale_channel: 'platform', name: { ar: '...' }, category_id: 20, price: 25 }

// لمتجر
{
  sale_channel: 'shop',
  shop_variants: [{ shop_id: 5, variant_index: 0 }],
  // + variants إن لزم
}
```

---

## Checklist

- [ ] فورم بلد المبيع: **احذف حقل رفع الأيقونة** — فقط اسم + تفعيل
- [ ] select بلد المبيع في المنتج: افتراضي **سوريا**
- [ ] فورم منتج: صف أسعار مضغوط + SYP قابل للإدخال عند الإنشاء
- [ ] لا ترسل `*_currencies` / `price_after_discount`
- [ ] حذف فئة: impact + linked-items + `confirm=true`
- [ ] `409` = confirmation لا توست خطأ
- [ ] فورم منتج: خيار **للموقع** / **ربط بمتجر** (`sale_channel`)
- [ ] للموقع: إخفاء المتجر وإرسال `platform` فقط
- [ ] لمتجر: فرع إلزامي عبر `shop_variants`

---

## Backend

```bash
php artisan db:seed --class=SaleCountrySeeder
php artisan migrate
```

Migrations:

- `2026_08_26_104500_make_product_category_id_nullable_for_category_delete`
- `2026_08_26_111600_add_sale_channel_to_products_table`

> أيقونات بلدان المبيع = سيدر (إيموجي)، الافتراضي سوريا.  
> `sale_channel` = واجهة موقع/متجر + ربط فرع منصة مخفي للمنتجات الموقعية.
