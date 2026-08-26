# داشبورد — آخر تحديث (26 آب 2026)

> **أرسلوا هذا الملف لفريق الداشبورد فقط.**  
> Base: `/api/admin` + Admin token.  
> هذه النسخة النهائية — تجمع كل تعديلات 26 آب للمنتج/الفئات/بلدان المبيع/قناة البيع.

---

## 1) بلدان المبيع — أخفوا حقل الأيقونة

**المشكلة:** فورم إنشاء/تعديل بلد المبيع يعرض رفع ملف بعنوان خاطئ («يلزم تسمية رمز بلد البيع»).

| الحقل | في الفورم؟ | ملاحظات |
|-------|------------|---------|
| `name.ar` / `name.en` | نعم | مطلوب |
| `is_active` | نعم | توغل تفعيل |
| `icon` | **لا — أخفوه واحذفوه** | من السيدر (إيموجي علم) — لا ترسلوه |

- `GET /api/admin/sale-countries` يرجع `icon` للعرض فقط (مثل `🇸🇾`)
- إنشاء منتج بدون `sale_country_id` → الباك يضع **سوريا** تلقائياً
- في select بلد المبيع بالمنتج: اجعل **سوريا** الخيار الافتراضي المحدد

```js
// POST/PUT sale-countries — بدون icon
{ name: { ar: 'سوريا', en: 'Syria' }, is_active: true }
```

---

## 2) فورم المنتج — الأسعار (حساب حيّ + مزامنة USD/SYP)

**المشكلة:** السعر يظهر مرتين (`price` قابل للتعديل + `price_after_discount` كـ «سعر المنتج»).

**سطر 1:** سعر `$` · سعر `ل.س` · نوع الخصم · قيمة الخصم · سعر بعد الخصم (**عرض فقط — يُحسب حيّاً**) · التكلفة  
**سطر 2:** الكمية · الوحدة

### أ) سعر بعد الخصم — احسبوه في الواجهة مباشرة

الحقل **read-only**. يتحدّث فور تغيّر السعر أو نوع/قيمة الخصم (قبل الحفظ):

```js
function priceAfterDiscount(price, discountType, discount) {
  const p = Number(price) || 0;
  const d = Number(discount) || 0;
  if (!p || !discountType || discountType === 'none' || d <= 0) return p;
  if (discountType === 'percentage') return Math.round((p - p * (d / 100)) * 100) / 100;
  if (discountType === 'fixed') return Math.max(0, Math.round((p - d) * 100) / 100);
  return p;
}

const afterUsd = priceAfterDiscount(priceUsd, discountType, discount);
const afterSyp = afterUsd * sypRate; // أو من price_after_discount_currencies بعد الحفظ
```

| `discount_type` | المعنى | الحساب |
|-----------------|--------|--------|
| `none` | بدون خصم | = السعر |
| `percentage` | نسبة % | `price - price * (discount/100)` |
| `fixed` | مبلغ ثابت **بالدولار** | `max(0, price - discount)` |

- **لا ترسل** `price_after_discount` — الباك يحسبه بعد الحفظ.
- بعد الحفظ يمكن مزامنة العرض من `price_after_discount` + `price_after_discount_currencies`.

### ب) إدخال دولار أو ليرة — يعبّي الحقل الثاني تلقائياً

الحقلان `price` ($) و `price_syp` (ل.س) **قابلان للإدخال**. أي واحد يكتب فيه الأدمن → يُحدَّث الثاني فوراً:

```js
// من إعدادات العملات: GET /api/admin/currencies → SYP.exchange_rate
const sypRate = currencies.find(c => c.code === 'SYP')?.exchange_rate ?? 1;

onChangeUsd(usd) => {
  setPriceUsd(usd);
  setPriceSyp(usd === '' || usd == null ? '' : round(Number(usd) * sypRate, 2));
};

onChangeSyp(syp) => {
  setPriceSyp(syp);
  setPriceUsd(syp === '' || syp == null ? '' : round(Number(syp) / sypRate, 6));
};
```

نفس المنطق لـ `cost_price` / `cost_price_syp`.

### ج) ماذا يُرسل عند الحفظ؟

| واجهة | يُرسل؟ | الحقل |
|-------|--------|--------|
| سعر دولار | نعم (إن أدخله) | `price` |
| سعر ليرة | نعم إن ما في دولار | `price_syp` |
| تكلفة | نعم | `cost_price` أو `cost_price_syp` |
| نوع/قيمة الخصم | نعم | `discount_type`, `discount` |
| سعر بعد الخصم | **لا** | عرض فقط |
| `*_currencies` | **لا** | عرض فقط |

**قاعدة الباك:** إذا أُرسل `price` و `price_syp` معاً → يعتمد **الدولار**.  
الأفضل بعد المزامنة المحلية: أرسل `price` (USD) دائماً إذا معروف، واترك `price_syp` للعرض أو أرسله فقط إذا الدولار فاضي.

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

## 3) بلد المنشأ — Select (مو نص حر)

| حقل | مطلوب؟ | ملاحظات |
|-----|--------|---------|
| `media` (صور) | **لا** | يمكن إنشاء منتج بدون صور |
| `country_id` (بلد المنشأ) | **لا** | **Select فقط** — ليس `country.ar` / `country.en` |

```http
GET /api/admin/countries
```

- الرد بدون pagination — `data.items[]`: `id`, `name`, `code`
- في الفورم: Select searchable — `value = country.id`
- عند الحفظ: `{ "country_id": 12 }` أو احذف الحقل / `null` إذا فارغ
- عند التعديل: املأ من `country_id` أو `origin_country.id`
- **لا تستخدم** نص حر `country` / `country.ar` / `country.en`

```bash
php artisan db:seed --class=CountrySeeder
```

---

## 4) حذف الفئات — نافذة تأكيد (مو توست أحمر)

| الغرض | Endpoint |
|-------|----------|
| ملخص الأثر | `GET /api/admin/categories/{id}/delete-impact` |
| العناصر المرتبطة | `GET /api/admin/categories/{id}/linked-items?page=1&per_page=10` |
| تنفيذ | `DELETE /api/admin/categories/{id}?confirm=true` |

1. حذف → نادِ `delete-impact`
2. نافذة: تبويب **التنبيه** (`warnings`) + تبويب **العناصر المرتبطة**
3. موافقة → `DELETE ?confirm=true`
4. **`409` + `requires_confirmation: true` = طلب تأكيد** — ليس خطأ أحمر

| مفتاح | عند التأكيد |
|-------|-------------|
| `child_categories` | حذف الفئات الفرعية |
| `products` | حذف ناعم + فك الارتباط (الطلبات تبقى) |
| `baskets` | تُحذف |
| `pages` | تُحذف صفحة الفئة وأقسامها |
| `recipe_links` | فك ارتباط فقط |

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

## 5) قناة البيع — للموقع أو ربط بمتجر فقط

> **مهم:** خياران فقط عبر `sale_channel`.  
> **احذفوا** صف الراديو «مستودعاتي / خارجي» — مو موجود بالباك وبيعمل تداخل مع قناة البيع.

| الخيار | `sale_channel` | المعنى |
|--------|----------------|--------|
| **للموقع** | `platform` | بدون بائع/متجر بالفورم؛ الربط بفرع المنصة تلقائي |
| **ربط بمتجر** | `shop` | بائع (اختياري للفلترة) + فرع إلزامي |

| احذفوا | ليش |
|--------|-----|
| «إنشاء منتج في مستودعاتي» | نفس معنى «للموقع» تقريباً |
| «خارجي» كصف منفصل | يختلط مع «ربط بمتجر» |

| الحقل | النوع | قيم | افتراضي |
|-------|-------|-----|---------|
| `sale_channel` | string | `platform` \| `shop` | `platform` عند الإنشاء |

يُرجع في `GET /api/admin/products/{id}` والقائمة (للـ badge).

| القناة | وقت التسليم |
|--------|-------------|
| للموقع | نص ثابت للمنصة (مثل `12-48 ساعة`) — لا حقل إدخال |
| ربط بمتجر | أظهروا حقل `delivery_time` |

### منتج للموقع

```text
sale_channel=platform
name[ar]=منتج الموقع
name[en]=Site product
category_id=20
price=25
quantity=10
```

- أخفوا متجر/بائع — **لا ترسلوا** `shop_variants` ولا `vendor_id`
- النتيجة: `vendor_id=1` · متغيّر افتراضي إن لزم · ربط بفرع المنصة (`is_default`)
- تحذير «غير مرتبط بفرع» **لا يظهر** لمنتجات الموقع

### منتج مربوط بمتجر

```text
sale_channel=shop
name[ar]=منتج الفرع
category_id=20
price=25
quantity=10
variants[0][sku]=X-1
variants[0][price]=25
variants[0][quantity]=10
variants[0][is_active]=1
shop_variants[0][shop_id]=5
shop_variants[0][variant_index]=0
shop_variants[0][cost_price]=10000
```

- فرع واحد على الأقل عبر `shop_variants` — وإلا **422**: «عند اختيار ربط بمتجر يجب اختيار فرع واحد على الأقل.»
- `vendor_id` يُستنتج من أول فرع
- تحذير «غير مرتبط بفرع» فقط إذا `sale_channel=shop` وما في روابط

### التعديل

| الإجراء | الـ payload |
|---------|-------------|
| تحويل لموقع | `sale_channel=platform` — لا ترسل فروع؛ الروابط تُستبدل بفرع المنصة |
| تحويل لمتجر | `sale_channel=shop` + `shop_variants[...]` كاملة |
| تعديل اسم فقط | **لا ترسل** `sale_channel` ولا `shop_variants` |

### قراءة الفورم (تعديل)

```js
const channel = product.sale_channel ?? 'platform';
setSaleChannel(channel);

if (channel === 'shop') {
  const shops = product.variants.flatMap((v, variantIndex) =>
    (v.shops ?? []).map((s) => ({
      shop_id: s.shop_id,
      variant_index: variantIndex,
      cost_price: s.cost_price,
    }))
  );
}
```

| `sale_channel` | Badge |
|----------------|--------|
| `platform` | للموقع |
| `shop` | متجر |

### شرط الباك (مهم)

لازم فرع لبائع المنصة (`vendor_id = 1`): `is_default=true` و `is_active=true` (أو أي فرع نشط كـ fallback).  
بدونه منتج «للموقع» ينحفظ بس ما ينربط → السلة معطّلة.

---

## Checklist

- [ ] بلد المبيع: احذف رفع الأيقونة — اسم + تفعيل فقط
- [ ] select بلد المبيع في المنتج: افتراضي **سوريا**
- [ ] أسعار: بعد الخصم يُحسب حيّاً؛ USD↔SYP مزامنة؛ لا ترسل `*_currencies` / `price_after_discount`
- [ ] `media` و `country_id` اختياريان (بدون `required`)
- [ ] بلد المنشأ = Select من `GET /api/admin/countries` عبر `country_id` (ليس نص حر)
- [ ] حذف فئة: impact + linked-items + `confirm=true`؛ `409` = تأكيد لا توست خطأ
- [ ] قناة البيع: راديو **للموقع / ربط بمتجر** فقط — **احذفوا مستودعاتي/خارجي**
- [ ] للموقع: إخفاء بائع+متجر؛ وقت تسليم ثابت؛ `platform` بدون `shop_variants`
- [ ] لمتجر: بائع اختياري للفلترة + فرع إلزامي + `delivery_time`؛ معالجة **422**
- [ ] عند التعديل: اقرأ `sale_channel` من GET
- [ ] قائمة: badge موقع / متجر
- [ ] تحذير «غير مرتبط بفرع» فقط لـ `shop` بدون روابط

---

## Backend

```bash
php artisan migrate
php artisan db:seed --class=CountrySeeder
php artisan db:seed --class=SaleCountrySeeder
```

Migrations:

- `2026_08_26_104500_make_product_category_id_nullable_for_category_delete`
- `2026_08_26_111600_add_sale_channel_to_products_table`
