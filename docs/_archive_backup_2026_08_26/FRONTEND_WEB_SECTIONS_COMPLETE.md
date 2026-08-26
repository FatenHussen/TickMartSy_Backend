# Web — دليل شامل: الصفحات والأقسام (من الصفر)

> اعتبروا أن موقع الويب **ما فيه شي مشتغل** بعد لهالموضوع.  
> هذا الملف وحده يكفي للبناء من البداية حتى الصفحات تشتغل صح مع آخر تعديلات الباك إند (آب 2026).

مراجع مختصرة إن احتجتم تفاصيل إضافية:

- `FRONTEND_SECTION_LAYOUT_AND_CARD.md`
- `FRONTEND_WEB_PAGE_BUILDER.md`
- `FRONTEND_WEB_CATEGORY_PAGES.md`
- `FRONTEND_WEB_FILTERS.md` (فلاتر المنتجات — اقرأوه كاملاً عند بناء شاشة الفلاتر)

---

## 0) الفكرة العامة

الموقع يعرض **صفحات**، وكل صفحة = قائمة **أقسام (sections)** مرتّبة من الأدمن.

| نوع الصفحة | كيف تجيبها |
|------------|------------|
| رئيسية / عروض / أي صفحة بمُعرّف نصي | `GET /api/user/sections?page_slug={slug}` |
| صفحة فئة (أي مستوى شجرة) | `GET /api/user/categories/{categoryId}/page` |

**قاعدة ذهبية:** ابنوا **مكوّن React واحد** لعرض الأقسام (`SectionRenderer`)، واستخدمه في الرئيسية وصفحة الفئة وكل صفحة `page_slug`.

التوكن اختياري لمعظم عرض الأقسام. أرسلوا:

```http
Accept-Language: ar
```

أو `en`.

---

## 1) الـ Endpoints

### أ) أقسام صفحة عامة

```http
GET /api/user/sections?page_slug=home
Accept-Language: ar
```

أمثلة `page_slug`: `home`, `offers`, `welcome`, `intro`, … (حسب إعداد الأدمن).

الاستجابة: مصفوفة أقسام داخل `data` (أو حسب شكل الـ envelope عندكم).

### ب) صفحة فئة

```http
GET /api/user/categories/{categoryId}/page
Accept-Language: ar
```

```json
{
  "status": true,
  "data": {
    "category": {
      "id": 12,
      "name": { "ar": "إلكترونيات", "en": "Electronics" },
      "children": []
    },
    "sections": []
  }
}
```

### ج) قائمة منتجات مستقلة («عرض الكل» / صفحة فلاتر)

```http
GET /api/user/products?category_id=12&page=1&per_page=15
Accept-Language: ar
```

### د) صفات الفلاتر لفئة

```http
GET /api/user/categories/{categoryId}/attributes
```

---

## 2) الحقول الثلاثة — لا تخلطوا بينها (مهم جداً)

| الحقل | المعنى | القيم | مين يحدده |
|--------|--------|--------|-----------|
| **`layout`** | طريقة عرض **القسم كامل** | `slider` \| `list` \| `grid` | الأدمن |
| **`variant`** | شكل **الكارد داخل** القسم | `horizontal` \| `vertical` \| `square` | الأدمن |
| **`display_type_id`** | نوع **المحتوى** | أرقام ثابتة | الباك إند |

### `layout` → مكوّن القسم

| القيمة | عربي | العرض في الويب |
|--------|------|----------------|
| `slider` | سلايدر عرضي (افتراضي) | تمرير أفقي يمين/يسار (أسهم / drag / scroll) |
| `list` | ليستا | عناصر تحت بعض عمودياً |
| `grid` | شبكة | شبكة (مثلاً عمودين على الموبايل، أكثر على الديسكتوب) |

### `variant` → شكل الكارد فقط

| القيمة | عربي | الاستخدام |
|--------|------|-----------|
| `horizontal` | عرضي | كارد عريض |
| `vertical` | طولي | كارد رأسي (منتج غالباً) |
| `square` | مربع | كارد مربع (فئة غالباً) |

### `display_type_id` → نوع المحتوى فقط

| id | النوع |
|----|--------|
| 1 | banner |
| 2 | product |
| 3 | shop |
| 4 | basket |
| 5 | schedule-basket |
| 6 | brand |
| 7 | recipe |
| 8 | category |

**غلط شائع (قديم):**  
اعتبار كل الأقسام سلايدر أفقي دائماً، و`variant` يحدد التخطيط.  
**صار خطأ.** التخطيط = `layout`، شكل الكارد = `variant`.

### Fallback

```ts
const layout = section.layout ?? 'slider';
const cardVariant = section.variant ?? 'horizontal';
```

---

## 3) نموذج القسم الكامل (TypeScript)

```ts
type Section = {
  id: number;
  name: { ar?: string; en?: string } | string;
  type: 'manual' | 'api';
  content_type?: string | null;
  manual_model?: string | null;
  api_method?: string | null;
  position?: 'before' | 'after';
  order: number;
  layout?: 'slider' | 'list' | 'grid' | null;
  variant?: 'horizontal' | 'vertical' | 'square' | null;
  display_type_id?: number | null;
  is_default?: boolean;
  background_color?: string | null;
  background_card_color?: string | null;
  end_date?: string | null;
  discount?: number | null;
  discount_type?: string | null;
  see_more?: { page_slug: string; params?: Record<string, unknown> } | null;
  show_when?: Record<string, unknown>;
  action?: { page_slug?: string };
  items: SectionItem[];
};
```

مثال JSON:

```json
{
  "id": 10,
  "name": { "ar": "منتجات رائجة", "en": "Trending" },
  "type": "api",
  "content_type": "product",
  "manual_model": null,
  "api_method": "products",
  "position": "before",
  "order": 1,
  "layout": "slider",
  "variant": "vertical",
  "display_type_id": 2,
  "is_default": false,
  "background_color": "#F7F7F7",
  "background_card_color": "#FFFFFF",
  "end_date": null,
  "discount": null,
  "discount_type": null,
  "see_more": {
    "page_slug": "products",
    "params": { "category_id": 5 }
  },
  "show_when": {},
  "action": { "page_slug": "product_details" },
  "items": []
}
```

### حقول مهمة

| حقل | الاستخدام في الويب |
|-----|---------------------|
| `order` | اعرضوا الأقسام بالترتيب القادم — لا تعيدوا الترتيب |
| `type` | `manual` = عناصر ثابتة؛ `api` = ديناميكية |
| `content_type` / `display_type_id` | لاختيار نوع الكارد |
| `background_color` | خلفية القسم |
| `background_card_color` | خلفية الكارد |
| `see_more` | زر «عرض الكل» |
| `action.page_slug` | مسار التفاصيل عند ضغط العنصر |
| `show_when` | فاضي/`{}` = اعرض دائماً |
| `end_date` / `discount` / `discount_type` | flash sale فقط؛ غير ذلك `null` |
| `is_default` | `true` = مولَّد تلقائياً لصفحة فئة؛ `false` = أضافه الأدمن — **اعرضوا الكل** |
| `items` | **لا تعرضوا قسماً بـ items فارغة** |

### تمييز `manual` / `api`

| `type` | `manual_model` | `api_method` |
|--------|----------------|--------------|
| `manual` | موجود | غالباً `null` |
| `api` | غالباً `null` | موجود (`products`, `categories`, …) |

---

## 4) العناصر `items[]`

```json
{
  "id": 1,
  "link": null,
  "order": 0,
  "item": {
    "id": 44,
    "title": { "ar": "...", "en": "..." },
    "image": "https://...",
    "price": 10.5,
    "discount": 0
  }
}
```

| نوع | الكارد | عند الضغط |
|-----|--------|-----------|
| بانر | صورة عرضية | `item.link` |
| منتج | سعر / خصم / إضافة سلة | صفحة تفاصيل المنتج |
| متجر / مطعم | كارد متجر | صفحة المتجر |
| فئة | كارد فئة | **`/categories/{id}` → نفس page endpoint** لأي عمق |
| ماركة / وصفة / سلة | كارد مناسب | الصفحة المناسبة |

---

## 5) البانرات

```json
{
  "type": "manual",
  "layout": "slider",
  "variant": "horizontal",
  "items": [
    {
      "id": 1,
      "link": "https://...",
      "order": 0,
      "item": {
        "id": 12,
        "image": "https://.../banner.jpg",
        "title": { "ar": "...", "en": "..." }
      }
    }
  ]
}
```

- صورة **عرضية** → نسبة ≈ `16/6` أو `3/1` بعرض الحاوية
- عنصر واحد → بانر ثابت
- عدة عناصر → سلايدر (أسهم / نقاط / auto-play اختياري)
- الضغط → `link`

### ملاحظة تكديس البانرات

- قسم بانر واحد فيه عدة `items` → سلايدر واحد (صح)
- عدة أقسام بانر متجاورة كل واحد بعنصر واحد → قد تظهر سلايدرات مكدّسة  
  الحل الأفضل من الداشبورد: قسم بانرات واحد + عدة `item_ids`  
  احتياطي في الويب: دمج أقسام بانر متجاورة عند العرض فقط إذا لزم

---

## 6) بناء الصفحات من الصفر

### أ) الرئيسية / صفحة بـ slug

1. `GET /api/user/sections?page_slug=home`
2. مرّوا على `sections` بالترتيب
3. كل قسم → `<SectionRenderer section={s} />`
4. تجاهلوا `items.length === 0`

### ب) صفحة الفئة

1. Route مثل `/categories/:id`
2. `GET /api/user/categories/{id}/page`
3. الهيدر: `category.name` (+ tabs/breadcrumb من `category.children` اختياري)
4. نفس `<SectionRenderer />` على `data.sections`
5. لا تفترضوا عدد أقسام ثابت (افتراضي: فرعية + منتجات + ما يضيفه الأدمن)
6. كاش per `categoryId` + إعادة جلب عند العودة / refresh

### ج) التنقّل

| من | إلى |
|----|-----|
| كارد فئة | صفحة فئة بنفس الـ route/endpoint |
| كارد منتج | تفاصيل المنتج |
| `see_more` | صفحة قائمة مع `page_slug` + query من `params` |
| بانر | افتح `link` |

---

## 7) مكوّن القسم الموحّد (مثال React)

```tsx
function SectionRenderer({ section }: { section: Section }) {
  if (!section.items?.length) return null;

  const layout = section.layout ?? 'slider';
  const cardVariant = section.variant ?? 'horizontal';

  return (
    <section style={{ background: section.background_color ?? undefined }}>
      <SectionHeader title={section.name} seeMore={section.see_more} />

      {layout === 'list' && (
        <SectionList items={section.items} cardVariant={cardVariant} section={section} />
      )}
      {layout === 'grid' && (
        <SectionGrid items={section.items} cardVariant={cardVariant} section={section} />
      )}
      {(layout === 'slider' || !['list', 'grid'].includes(layout)) && (
        <SectionSlider items={section.items} cardVariant={cardVariant} section={section} />
      )}
    </section>
  );
}

// داخل أي تخطيط:
<ProductCard
  variant={cardVariant} // horizontal | vertical | square
  item={item.item}
  cardBg={section.background_card_color}
/>
```

---

## 8) أقسام `api` وفلاتر URL

- `type: "manual"` → `items` ثابتة — **لا تتأثر** بـ query الـ URL
- `type: "api"` → الباك يدمج فلاتر القسم مع query الصفحة (الـ URL يغلّب)

عند بناء صفحة «عرض الكل» للمنتجات:

```http
GET /api/user/products
```

### فلاتر تعمل على `/api/user/products`

| Param | مثال | ملاحظة |
|-------|------|--------|
| `category_id` | `12` | الفئة **+ كل الفروع** — لا تفلتروا محلياً لتقليص الشجرة |
| `brand_id` | `7` | |
| `shop_id` | `3` | |
| `price_min` / `price_max` | `100` | بعملة المستخدم |
| `search` | `أرز` | لا تستخدموا `name` |
| `country` | `تركيا` | نص — **ليس** `country_id` على `/products` |
| `is_free_delivery` | `true` | |
| `is_instant_delivery` | `true` | |
| `on_sale` | `true` | |
| `in_stock_only` | `true` | |
| `attribute_values` | `1,5` أو array | OR |
| `type` | `trend` / `new` / … | قوائم جاهزة |
| `sort_by` | `price_asc` | |
| `page` / `per_page` | | |

صفات الفلاتر (chips):

```http
GET /api/user/categories/{categoryId}/attributes
```

مرّروا **الفئة الظاهرة على الشاشة**.

التفاصيل الكاملة والجداول: **`FRONTEND_WEB_FILTERS.md`**.

> ملاحظة: `country_id` قد يُقرأ كـ query على أقسام الصفحة `api_method=products`، لكنه **غير مقبول** على `FilterRequest` الخاص بـ `/products` — هناك استخدموا `country` كنص.

---

## 9) `show_when`

- فاضي أو `{}` → اعرضوا القسم دائماً
- إن فيه شروط (مثل `category_id`) → طبّقوا المطابقة مع سياق الصفحة إن دعمتم ذلك؛ وإلا اعرضوا دائماً حتى يثبت عقد أوضح

---

## 10) Checklist بناء من الصفر

### Types / API

- [ ] نوع `Section` فيه `layout`, `variant`, `display_type_id`, `type`, `content_type`, `items`, `see_more`, `action`, `show_when`, ألوان، flash fields
- [ ] نوع عنصر القسم + `item`
- [ ] نوع استجابة صفحة الفئة: `category` + `sections`

### صفحات

- [ ] Home / page_slug → `GET /user/sections?page_slug=`
- [ ] Category → `GET /user/categories/{id}/page`
- [ ] نفس `SectionRenderer` في المكانين
- [ ] كاش لصفحات الفئات

### عرض

- [ ] `layout` → slider / list / grid
- [ ] `variant` → شكل الكارد فقط
- [ ] fallback بدون `layout` → `slider`
- [ ] لا عرض لقسم فارغ
- [ ] بانرات بنسبة أفقية
- [ ] ضغط فئة → صفحة فئة (تنقّل متداخل)
- [ ] اعرضوا أقسام `is_default` وبدونها معاً بالترتيب

### فلاتر / عرض الكل

- [ ] `see_more` يفتح قائمة مع params
- [ ] `/user/products` بالفلاتر الصحيحة من `FRONTEND_WEB_FILTERS.md`
- [ ] `/user/categories/{id}/attributes` للـ chips

### ممنوع

- [ ] اعتبار كل الأقسام سلايدر دائماً وتجاهل `layout`
- [ ] استخدام `variant` كبديل عن `layout`
- [ ] استخدام `display_type_id` لتحديد سلايدر/شبكة
- [ ] افتراض عدد ثابت لأقسام صفحة الفئة
- [ ] فلترة محلية تتجاهل أن `category_id` يجيب الشجرة كاملة

---

## 11) ملخص جملة واحدة

1. جيبوا الأقسام من الـ API.  
2. رتّبوها كما هي.  
3. لكل قسم: التخطيط من **`layout`**, الكارد من **`variant`**, نوع المحتوى من **`display_type_id` / `content_type`**.  
4. نفس المكوّن للرئيسية ولصفحة أي فئة.

هذا كل المطلوب لتشغيل الصفحات والأقسام على الويب من الصفر.
