# داشبورد — قناة البيع (`sale_channel`): للموقع أو ربط بمتجر

> **للفريق الداشبورد (React).**  
> Base: `/api/admin` + Admin token.

---

## الفكرة

في فورم المنتج يظهر **خياران واضحان** — الأدمن ما يدخل متجر/بائع إلا إذا اختار الربط بمتجر:

| الخيار في الواجهة | القيمة المرسلة | شو بصير |
|-------------------|----------------|---------|
| **للموقع** (افتراضي) | `sale_channel=platform` | ما يظهر اختيار متجر ولا بائع. الباك يضع المنتج تحت بائع المنصة ويربطه بفرع المنصة الافتراضي تلقائياً |
| **ربط بمتجر** | `sale_channel=shop` | يظهر select للفروع. لازم فرع واحد على الأقل عبر `shop_variants` |

الأدمن يشوف «موقع / متجر». تحت الغطاء النظام دائماً يحتاج فرع للسلة/التوصيل — بس للمنصة هذا مخفي.

---

## UX المطلوب

```
○ للموقع          ← افتراضي عند الإنشاء
○ ربط بمتجر

إذا «ربط بمتجر»:
  [ اختيار الفرع / الفروع ]  *
  (تاب المتغيرات كما هو)
```

- عند **للموقع**: أخفوا حقول البائع والمتجر و`shop_variants` بالكامل — **لا ترسلوا** `shop_variants`
- عند **ربط بمتجر**: أظهروا اختيار الفرع؛ أرسلوا `shop_variants` مع `shop_id`
- تحذير «المنتج غير مرتبط بأي فرع» يظهر فقط إذا `sale_channel=shop` وما في فروع محفوظة (خطأ أدمن)، مو لمنتجات الموقع

---

## الحقل في الـ API

| الحقل | النوع | قيم | افتراضي |
|-------|-------|-----|---------|
| `sale_channel` | string | `platform` \| `shop` | `platform` عند الإنشاء إذا ما أُرسل |

يُرجع في:

- `GET /api/admin/products/{id}` → `sale_channel`
- `GET /api/admin/products` → `sale_channel` (للـ badge في القائمة)

---

## 1) منتج للموقع

```http
POST /api/admin/products
Content-Type: multipart/form-data
```

```text
sale_channel=platform
name[ar]=منتج الموقع
name[en]=Site product
category_id=20
price=25
quantity=10
```

**لا ترسل:** `shop_variants` · `vendor_id` (الباك يضبطهم)

**النتيجة:**

- `sale_channel` = `platform`
- `vendor_id` = `1` (تيكموول)
- متغيّر افتراضي إن لزم
- ربط تلقائي بفرع المنصة (`shops.vendor_id=1` و `is_default=true`، أو أول فرع نشط للمنصة)

بعد الحفظ: المنتج قابل للشراء على الويب/التطبيق بدون ما الأدمن يختار متجر.

---

## 2) منتج مربوط بمتجر

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

**إلزامي:** `shop_variants` بمصفوفة فيها عنصر واحد على الأقل.

إذا `sale_channel=shop` بدون `shop_variants` → **422** مع رسالة:

> عند اختيار «ربط بمتجر» يجب اختيار فرع واحد على الأقل.

`vendor_id` يُستنتج من أول فرع مختار.

---

## 3) التعديل

| الإجراء | الـ payload |
|---------|-------------|
| تحويل لموقع | `sale_channel=platform` — لا ترسل فروع؛ الروابط القديمة تُستبدل بفرع المنصة |
| تحويل لمتجر | `sale_channel=shop` + `shop_variants[...]` كاملة |
| تعديل اسم فقط | **لا ترسل** `sale_channel` ولا `shop_variants` — يبقى كما هو |

---

## 4) قراءة الفورم (تعديل)

```js
const channel = product.sale_channel ?? 'platform';

setSaleChannel(channel); // 'platform' | 'shop'

if (channel === 'shop') {
  // عبّي اختيار الفروع من product.variants[].shops
  const shops = product.variants.flatMap((v, variantIndex) =>
    (v.shops ?? []).map((s) => ({
      shop_id: s.shop_id,
      variant_index: variantIndex,
      cost_price: s.cost_price,
    }))
  );
}
```

Badge مقترح في القائمة:

| `sale_channel` | العرض |
|----------------|--------|
| `platform` | للموقع |
| `shop` | متجر |

---

## 5) شرط الباك إند (مهم للتشغيل)

لازم يوجد فرع لبائع المنصة (`vendor_id = 1`):

- معلّم `is_default = true` و `is_active = true` (مفضّل)
- أو أي فرع نشط لنفس البائع كـ fallback

بدون هالفرع، منتج «للموقع» ينحفظ بس ما ينربط → السلة تضل معطّلة.

---

## Checklist داشبورد

- [ ] راديو / تبويب: **للموقع** | **ربط بمتجر**
- [ ] الافتراضي عند الإنشاء = **للموقع**
- [ ] للموقع: إخفاء متجر/بائع/`shop_variants`
- [ ] إرسال `sale_channel=platform` بدون `shop_variants`
- [ ] لمتجر: إظهار اختيار فرع + إرسال `sale_channel=shop` + `shop_variants`
- [ ] معالجة 422 إذا متجر بدون فروع
- [ ] عند التعديل: اقرأ `sale_channel` من GET
- [ ] قائمة المنتجات: badge للموقع / متجر
- [ ] تحذير «غير مرتبط بفرع» فقط لـ `sale_channel=shop` بدون روابط

---

## Backend

```bash
php artisan migrate
```

Migration: `2026_08_26_111600_add_sale_channel_to_products_table`
