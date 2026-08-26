# فلاتر المنتجات (Web) — آخر تحديث 19 آب 2026

هذا الملف هو **المصدر للواجهة الويب**. مكتوب من الكود الحالي في:

- `app/Http/Requests/User/Product/FilterRequest.php`
- `app/Services/User/ProductService.php`
- `app/Http/Controllers/User/Category/CategoryController.php` (`attributes`)
- `app/Support/PageSectionRuntimeFilters.php`

> لا تعتمد `docs/PRODUCTS_FILTERS_API.md` — فيه معلومات قديمة (مثلاً أن القائمة تتطلب توكن، وناقص `is_instant_delivery` و`latest_flash_sale`).

---

## 1) شو تغيّر (قبل → بعد)

| الموضوع | قبل | بعد (الحالي) |
|---------|-----|----------------|
| `GET /products?category_id=` | غالباً أوراق الشجرة فقط | منتجات **الفئة + كل الفروع بأي عمق** |
| صفات الفلاتر | غالباً تظهر على ورقة فقط / قائمة فاضية على فرعية | أي `categoryId` يرجع صفات **الجذر** + قيمها |
| حقول الصفة | `id`, `name`, `type`, `values` | نفسهم + `category_id` + `root_category_id` |
| توصيل فوري | غير موجود | `is_instant_delivery=true\|false` |
| بلد المنشأ في البطاقة | object `{ id, name: {ar,en} }` | **نص** حسب `Accept-Language` أو `null` |
| أقسام الصفحة `type=api` | فلاتر القسم فقط | `merge(فلاتر القسم, query الـ URL)` والـ URL يغلّب |

لا يوجد endpoint جديد لقائمة المنتجات. نفس:

```http
GET /api/user/products
Accept-Language: ar
```

عام (بدون توكن). التوكن اختياري — يفعّل فقط `is_favorite`.

---

## 2) باراميترات `GET /api/user/products`

أرسل فقط المفاتيح المفعّلة. لا ترسل `null` ولا `""`.

### يعمل الآن (validation + تطبيق في ProductService)

| Param | النوع | مثال | السلوك |
|-------|--------|------|---------|
| `category_id` | int | `12` | الفئة **وكل أحفادها**. المنتج الراجع قد يكون `category` لفئة فرعية — **لا تفلتر محلياً**. |
| `brand_id` | int | `7` | ماركة واحدة |
| `shop_id` | int | `3` | متوفر في هذا الفرع |
| `price_min` / `price_max` | number | `100` | بعملة المستخدم؛ الباك يحوّل لـ USD قبل المقارنة |
| `search` | string ≤255 | `أرز` | بحث في الاسم + الوصف حسب لغة الطلب |
| `country` | string ≤100 | `تركيا` | تطابق جزئي على حقل البلد النصي حسب اللغة |
| `is_free_delivery` | bool | `true` / `1` | فروع `is_free_delivery` |
| `is_instant_delivery` | bool | `true` / `1` | توصيل فوري على المنتج |
| `on_sale` | bool | `true` | `discount > 0` |
| `in_stock_only` | bool | `true` | كمية المتغيّر `> 0` |
| `attribute_values` | array أو `"1,5"` | انظر تحت | منطق **OR**: أي قيمة من القائمة تكفي |
| `type` | enum | `trend` | قائمة جاهزة (فلتر + ترتيب). انظر §4 |
| `sort_by` | enum | `price_asc` | انظر §5 — يُطبَّق **بعد** `type` إن وُجد الاثنان |
| `page` | int | `1` | افتراضي 1 |
| `per_page` | int | `15` | افتراضي 15 |

### مقبول في الـ request لكن **لا يفلتر** القائمة

| Param | لا تستخدمه لـ `/products` |
|-------|---------------------------|
| `name` | موجود في الـ validation ولا يُطبَّق. استخدم `search`. |
| `country_id` | **يُحذف** من `GET /products` (غير موجود في FilterRequest). يعمل فقط كـ query على أقسام الصفحة `api_method=products`. لفلتر بلد في صفحة المنتجات أرسل `country` كنص. |
| `sort_by=rating_desc` أو `rating_asc` | الـ validation يقبلهم، لكن الترتيب الفعلي للتقييم هو **`rating`** (من الأعلى). |

### البوليان

اقبل: `true`, `false`, `1`, `0`. Axios: `params: { in_stock_only: true }`.

---

## 3) صفات الفئة (chips الفلاتر)

```http
GET /api/user/categories/{categoryId}/attributes
```

مرّر **الفئة الظاهرة على الشاشة** (جذر أو فرعية). الباك يصعد للجذر ويرجع صفاته وقيمه.

```json
{
  "status": true,
  "data": [
    {
      "id": 10,
      "category_id": 5,
      "root_category_id": 5,
      "name": { "ar": "اللون", "en": "Color" },
      "type": "color",
      "values": [
        { "id": 31, "name": { "ar": "أحمر", "en": "Red" } },
        { "id": 32, "name": { "ar": "أزرق", "en": "Blue" } }
      ]
    }
  ]
}
```

| حقل | استخدام الويب |
|-----|----------------|
| `name` | عنوان مجموعة الفلتر (object حسب اللغة أو string) |
| `values[].id` | يُرسل في `attribute_values` |
| `values[].name` | نص الـ chip |
| `type` | `square` / `circle` / `color` — شكل الـ chip |
| `root_category_id` | مفتاح كاش. نفس الجذر → **لا تعيد الطلب** |

قواعد:

- اعرض الفلاتر فور فتح **أي** فئة، بما فيها الجذر. لا تنتظر ورقة.
- قائمة فاضية = الجذر بدون صفات، وليست خطأ.
- عند النزول Food → Grains أبقِ نفس الـ chips والاختيارات إن أردت، وغيّر فقط `category_id` في طلب المنتجات.
- أعد جلب الصفات فقط إذا تغيّر `root_category_id` (شجرة أخرى).
- صفحة تفاصيل المنتج **لا** تُبنى من هذا الـ endpoint — استخدم `attributes_map` / `shop_variants` (`FRONTEND_WEB_PRODUCT_DETAIL_SHOP_VARIANTS.md`).

### إرسال القيم

```http
GET /api/user/products?category_id=12&attribute_values[]=31&attribute_values[]=40
GET /api/user/products?category_id=12&attribute_values=31,40
```

الاثنان صحيحان.

---

## 4) `type` (قوائم جاهزة)

| القيمة | المعنى الحالي في الباك |
|--------|-------------------------|
| `new` | الأحدث |
| `trend` / `most_popular` | متغيّر `is_trend` + ترتيب حسب المبيعات |
| `top_rated` | أعلى تقييم |
| `offers` | عليه خصم، مرتّب بنسبة الخصم |
| `latest_flash_sale` | منتجات آخر فلاش سيل فعّال — إن ما في سيل: قائمة فارغة |
| `recommended` / `for_you` | **حالياً لا يضيفا شرطاً** (placeholder). لا تعتمد عليهما لتصفية حقيقية. |
| `search_based` | يحتاج `search` |

---

## 5) `sort_by` (ما يعمل فعلياً)

| القيمة | الترتيب |
|--------|---------|
| `price_desc` | السعر تنازلي (حقل المنتج `price`) |
| `price_asc` | السعر تصاعدي |
| `newest` | الأحدث |
| `oldest` | الأقدم |
| `rating` | الأعلى تقييماً |

بدون `type` وبدون `sort_by` → `latest()`.

على **أقسام الصفحة** فقط يمكن أيضاً:

```text
?sortField=price&sortOrder=desc   →  sort_by=price_desc
?sortField=rating&sortOrder=desc  →  sort_by=rating_desc
```

لصفحة `/products` استخدم `sort_by` مباشرة، وللتقييم أرسل `rating`.

---

## 6) مصادر خيارات الفلتر (dropdowns)

| الفلتر | من أين تجيب الخيارات |
|--------|----------------------|
| الفئة | الشجرة الحالية / `GET /categories?parent_id=` — الفلتر نفسه هو `category_id` الظاهر |
| الصفات | `GET /categories/{id}/attributes` |
| الماركة | `GET /api/user/brands` → أرسل `brand_id` |
| المتجر | `GET /api/user/shops` → أرسل `shop_id` |
| بلد المنشأ (عرض) | `GET /api/user/countries` — للفلتر أرسل **اسم** البلد في `country` حسب اللغة |
| السعر | حقول الرقم `price_min` / `price_max` (لا يوجد endpoint لنطاق جاهز) |

لا يوجد `GET /products/filter-options`. ابنِ الـ sidebar من الطلبات فوق + الصفات.

---

## 7) صفحة المنتجات مقابل أقسام الـ Page Builder

### شبكة المنتجات (الصفحة الأساسية)

كل الفلاتر في **طلب واحد** مع `category_id`. الـ pagination على النتيجة الكاملة (الشجرة + الفلاتر). عند أي تغيير فلتر/فئة: `page=1`.

```js
async function loadProducts({ categoryId, page = 1, filters = {} }) {
  const params = { page, per_page: 20, ...clean(filters) };
  if (categoryId) params.category_id = categoryId;
  const { data } = await api.get("/api/user/products", { params });
  return data.data; // { data: Product[], pagination }
}

function clean(obj) {
  return Object.fromEntries(
    Object.entries(obj).filter(([, v]) => v !== "" && v != null && v !== false)
  );
}
```

للبوليان المفعّل أرسل `true`؛ إذا المستخدم ألغى الخيار **احذف المفتاح** (لا ترسل `false` إلا إذا تريد العكس صراحة، مثل `is_instant_delivery=false`).

### أقسام `type: "api"`

الباك يدمج فلاتر القسم مع query الصفحة. الـ URL يغلّب.

نفس مفاتيح المنتجات تقريباً، **وهنا `country_id` يُقرأ من الـ query**. الأقسام اليدوية (`manual`) لا تتأثر بالـ URL.

تفاصيل العرض: `FRONTEND_WEB_PAGE_BUILDER.md`.

---

## 8) أمثلة طلبات

فئة + متوفر + توصيل مجاني + ترتيب:

```http
GET /api/user/products?category_id=2&in_stock_only=1&is_free_delivery=1&sort_by=newest&page=1
```

فئة + صفات + سعر:

```http
GET /api/user/products?category_id=5&attribute_values=31,40&price_min=10&price_max=80
```

عروض توصيل فوري:

```http
GET /api/user/products?on_sale=true&is_instant_delivery=true
```

بحث داخل فئة:

```http
GET /api/user/products?category_id=3&search=jeans&in_stock_only=true
```

---

## 9) ممنوع في الواجهة

```js
// خطأ: يحذف منتجات الفروع
products.filter((p) => p.category_id === selectedCategoryId);
products.filter((p) => p.category === selectedCategoryName);
```

- لا تفلتر محلياً بعد استجابة API (سعر، ستوك، صفات، فئة).
- لا تجلب كل ابن بطلب منتجات ثم تدمج.
- لا تشترط leaf قبل إظهار الفلاتر أو المنتجات.
- لا تبني chips التفاصيل من `/attributes` — ذلك لشبكة الفئة فقط.
- `product.country` نص للعرض، ليس `{ name.ar }`.

---

## 10) Checklist للويب

- [ ] شريط الفلاتر على صفحة الفئة **و** صفحة المنتجات العامة
- [ ] `GET /categories/{id}/attributes` عند فتح أي فئة
- [ ] كاش حسب `root_category_id` — بلا إعادة طلب داخل نفس الشجرة
- [ ] `attribute_values` + باقي الفلاتر في نفس `GET /products`
- [ ] `category_id` = الفئة الحالية (شجرة كاملة من الباك)
- [ ] ممنوع `filter` محلي على `category_id`
- [ ] `is_instant_delivery` و `is_free_delivery` و `on_sale` و `in_stock_only` كـ toggles
- [ ] السعر `price_min` / `price_max` بعملة العرض
- [ ] ترتيب من القائمة: `price_asc` | `price_desc` | `newest` | `oldest` | `rating`
- [ ] تغيير أي فلتر يعيد `page` إلى `1`
- [ ] empty state من `pagination.total === 0` فقط
- [ ] أقسام API: query الـ URL يُمرَّر كما هو (override)

---

## مراجع مرتبطة

| الملف | الموضوع |
|-------|---------|
| `FRONTEND_WEB_CATEGORY_PRODUCT_SUBTREE.md` | شجرة `category_id` |
| `FRONTEND_WEB_PAGE_BUILDER.md` | merge فلاتر URL على الأقسام |
| `FRONTEND_WEB_PRODUCT_DETAIL_SHOP_VARIANTS.md` | `country` نص + `attributes_map` |
| `FRONTEND_WEB_CATEGORY_PAGES.md` | صفحة الفئة |
