# Flutter — دليل شامل: الصفحات والأقسام (من الصفر)

> اعتبروا أن تطبيق Flutter **ما فيه شي مشتغل** بعد لهالموضوع.  
> هذا الملف وحده يكفي للبناء من البداية حتى الشاشات تشتغل صح مع آخر تعديلات الباك إند (آب 2026).

مراجع مختصرة إن احتجتم تفاصيل إضافية:

- `FRONTEND_SECTION_LAYOUT_AND_CARD.md`
- `FRONTEND_FLUTTER_PAGE_BUILDER.md`
- `FRONTEND_FLUTTER_CATEGORY_PAGES.md`
- `FRONTEND_WEB_FILTERS.md` (فلاتر المنتجات — نفس الـ API للموبايل)

---

## 0) الفكرة العامة

التطبيق يعرض **صفحات**، وكل صفحة = قائمة **أقسام (sections)** مرتّبة.

| نوع الصفحة | كيف تجيبها |
|------------|------------|
| رئيسية / عروض / أي صفحة بمُعرّف نصي | `GET /api/user/sections?page_slug={slug}` |
| صفحة فئة (أي مستوى شجرة) | `GET /api/user/categories/{categoryId}/page` |

**قاعدة ذهبية:** ابنوا **ويدجت واحد** لعرض الأقسام، واستخدمه في الصفحة الرئيسية وصفحة الفئة وكل صفحة `page_slug`.

التوكن اختياري لمعظم عرض الأقسام. أرسلوا `Accept-Language: ar` أو `en`.

---

## 1) الـ Endpoints

### أ) أقسام صفحة عامة

```http
GET /api/user/sections?page_slug=home
Accept-Language: ar
```

أمثلة `page_slug`: `home`, `offers`, `welcome`, `intro`, … (حسب ما يضبطه الأدمن).

الاستجابة: مصفوفة أقسام (غالباً داخل `data`).

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

### ج) قائمة منتجات مستقلة (شاشة «عرض الكل» / فلاتر)

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

### `layout` → ويدجت القسم

| القيمة | عربي | ويدجت Flutter |
|--------|------|----------------|
| `slider` | سلايدر عرضي (افتراضي) | `ListView(scrollDirection: Axis.horizontal)` أو carousel |
| `list` | ليستا | `ListView` / `Column` عمودي |
| `grid` | شبكة | `GridView` (مثلاً `crossAxisCount: 2`) |

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
`variant == horizontal` → سلايدر، `vertical` → قائمة، `square` → شبكة.  
**صار خطأ.** التخطيط = `layout`، شكل الكارد = `variant`.

### Fallback

```dart
final layout = section.layout ?? 'slider';
final cardVariant = section.variant ?? 'horizontal';
```

---

## 3) نموذج القسم الكامل (احفظوه في الموديل)

```json
{
  "id": 10,
  "name": { "ar": "منتجات رائجة", "en": "Trending" },
  "type": "manual",
  "content_type": "product",
  "manual_model": "product",
  "api_method": null,
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
  "action": {
    "page_slug": "product_details"
  },
  "items": []
}
```

### حقول مهمة

| حقل | الاستخدام في Flutter |
|-----|----------------------|
| `order` | رتّبوا الأقسام كما تصل — لا تعيدوا الترتيب محلياً |
| `type` | `manual` = عناصر ثابتة؛ `api` = عناصر ديناميكية من الباك |
| `content_type` / `display_type_id` | لمعرفة نوع الكارد (منتج/بانر/متجر…) |
| `background_color` | لون خلفية القسم |
| `background_card_color` | لون خلفية الكارد |
| `see_more` | زر «عرض الكل» إن وُجد |
| `action.page_slug` | شاشة التفاصيل عند ضغط العنصر |
| `show_when` | شروط ظهور القسم — فاضي/`{}` = اعرض دائماً |
| `end_date` / `discount` / `discount_type` | لأقسام عروض فلاش فقط؛ غير ذلك `null` |
| `items` | عناصر القسم — **لا تعرضوا قسماً بـ items فارغة** |

### `type` وتمييز المحتوى

| `type` | `manual_model` | `api_method` |
|--------|----------------|--------------|
| `manual` | موجود (مثل `banner`, `product`) | غالباً `null` |
| `api` | غالباً `null` | موجود (مثل `products`, `categories`) |

---

## 4) العناصر `items[]`

كل عنصر تقريباً بهذا الشكل:

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
    "discount": 0,
    "..."
  }
}
```

اعرضوا حسب نوع المحتوى:

| نوع | الكارد | عند الضغط |
|-----|--------|-----------|
| بانر | صورة عرضية | افتح `item.link` أو الرابط من العنصر |
| منتج | سعر / خصم / إضافة سلة | شاشة تفاصيل المنتج |
| متجر / مطعم | كارد متجر | شاشة المتجر |
| فئة | كارد فئة | **`CategoryPageScreen(id)`** — نفس الـ endpoint لأي عمق |
| ماركة / وصفة / سلة | كارد مناسب | الشاشة المناسبة |

---

## 5) البانرات

قسم بانرات عادة:

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

- الصورة **عرضية** → `AspectRatio` تقريباً `16/6` أو `3/1` بعرض الشاشة
- عنصر واحد → بانر ثابت
- عدة عناصر → `PageView` / carousel (نقاط + auto-play اختياري)
- الضغط → `link` (deep link داخلي أو رابط خارجي)

---

## 6) بناء الشاشات من الصفر

### أ) الصفحة الرئيسية / صفحة بـ slug

1. نادِ `GET /api/user/sections?page_slug=home`
2. `CustomScrollView` / `ListView` يمر على `sections`
3. لكل قسم → `SectionRenderer(section)`
4. احترموا `order` كما يصل
5. تجاهلوا الأقسام ذات `items` فارغة

### ب) شاشة الفئة

1. نادِ `GET /api/user/categories/{id}/page`
2. AppBar: `category.name` (حسب اللغة)
3. نفس `SectionRenderer` على `data.sections`
4. لا تفترضوا عدد أقسام ثابت (افتراضي: فرعية + منتجات، وقد يضيف الأدمن غيرها)
5. `RefreshIndicator` → أعد الطلب
6. كاش per `categoryId` اختياري + `AutomaticKeepAliveClientMixin` إن لزم

### ج) التنقّل

| من | إلى |
|----|-----|
| كارد فئة | `CategoryPageScreen(categoryId)` |
| كارد منتج | تفاصيل المنتج |
| `see_more` | شاشة قائمة مع `see_more.page_slug` + `see_more.params` |
| بانر برابط | افتح الرابط |

Breadcrumb اختياري عند التعمّق في الفئات: احتفظوا بمسار `[rootId, …, currentId]`.

---

## 7) ويدجت القسم الموحّد (مثال تنفيذ)

```dart
class SectionRenderer extends StatelessWidget {
  final Section section;
  const SectionRenderer({super.key, required this.section});

  @override
  Widget build(BuildContext context) {
    if (section.items.isEmpty) return const SizedBox.shrink();

    final layout = section.layout ?? 'slider';
    final cardVariant = section.variant ?? 'horizontal';

    return ColoredBox(
      color: parseColor(section.backgroundColor) ?? Colors.transparent,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SectionHeader(
            title: section.name,
            seeMore: section.seeMore,
          ),
          switch (layout) {
            'list' => SectionListBody(
                items: section.items,
                cardVariant: cardVariant,
                cardColor: section.backgroundCardColor,
              ),
            'grid' => SectionGridBody(
                items: section.items,
                cardVariant: cardVariant,
                cardColor: section.backgroundCardColor,
              ),
            _ => SectionSliderBody(
                items: section.items,
                cardVariant: cardVariant,
                cardColor: section.backgroundCardColor,
              ),
          },
        ],
      ),
    );
  }
}
```

داخل أي تخطيط، الكارد:

```dart
SectionItemCard(
  item: item,
  variant: cardVariant, // horizontal | vertical | square
  contentType: section.contentType ?? section.displayTypeId,
)
```

---

## 8) أقسام `api` والفلاتر (مهم للموبايل)

- قسم `type: "manual"` → `items` ثابتة — **لا تتأثر** بفلاتر الشاشة.
- قسم `type: "api"` → العناصر ديناميكية؛ الباك يدمج فلاتر القسم مع query إن وُجدت.

عند فتح «عرض الكل» للمنتجات استخدموا:

```http
GET /api/user/products
```

### فلاتر تعمل على `/api/user/products`

| Param | مثال | ملاحظة |
|-------|------|--------|
| `category_id` | `12` | الفئة **+ كل الفروع** |
| `brand_id` | `7` | |
| `shop_id` | `3` | |
| `price_min` / `price_max` | `100` | بعملة المستخدم |
| `search` | `أرز` | لا تستخدموا `name` |
| `country` | `تركيا` | نص — ليس `country_id` على `/products` |
| `is_free_delivery` | `true` | |
| `is_instant_delivery` | `true` | |
| `on_sale` | `true` | |
| `in_stock_only` | `true` | |
| `attribute_values` | `1,5` أو array | OR بين القيم |
| `type` | `trend` / `new` / … | قوائم جاهزة |
| `sort_by` | `price_asc` | |
| `page` / `per_page` | | |

صفات الفلاتر (chips):

```http
GET /api/user/categories/{categoryId}/attributes
```

مرّروا **الفئة الظاهرة على الشاشة** (جذر أو فرعية).

تفاصيل أعمق: `FRONTEND_WEB_FILTERS.md` (نفس الـ API).

---

## 9) `show_when`

- إذا `show_when` فاضي أو `{}` → اعرضوا القسم دائماً.
- إذا فيه شروط (مثل `category_id`) → اعرضوا فقط عندما يطابق سياق الشاشة الحالية (إن كنتم تدعمون ذلك؛ وإلا اعرضوا دائماً حتى يوضح الباك سلوكاً إضافياً).

---

## 10) Checklist بناء من الصفر

### موديلات

- [ ] `Section`: فيه `layout`, `variant`, `display_type_id`, `type`, `content_type`, `items`, `see_more`, `action`, `show_when`, ألوان، flash fields
- [ ] `SectionItem` + `item` الداخلي
- [ ] `CategoryPageResponse`: `category` + `sections`

### شاشات

- [ ] Home / page_slug تستخدم `GET /user/sections?page_slug=`
- [ ] Category page تستخدم `GET /user/categories/{id}/page`
- [ ] نفس `SectionRenderer` في المكانين
- [ ] Refresh + كاش اختياري لصفحات الفئات

### عرض

- [ ] `layout` → slider / list / grid
- [ ] `variant` → شكل الكارد فقط
- [ ] fallback بدون `layout` → `slider`
- [ ] لا عرض لقسم `items` فارغ
- [ ] بانرات بنسبة أفقية
- [ ] ضغط فئة → صفحة فئة جديدة (تنقّل متداخل)

### فلاتر / عرض الكل

- [ ] `see_more` يفتح قائمة مع params
- [ ] `/user/products` بالفلاتر الصحيحة
- [ ] `/user/categories/{id}/attributes` للـ chips

### ممنوع

- [ ] استخدام `variant` كبديل عن `layout`
- [ ] استخدام `display_type_id` لتحديد سلايدر/شبكة
- [ ] افتراض عدد ثابت لأقسام صفحة الفئة
- [ ] فلترة محلية تتجاهل أن `category_id` يجيب شجرة كاملة

---

## 11) ملخص جملة واحدة

1. جيبوا الأقسام من الـ API.  
2. رتّبوها كما هي.  
3. لكل قسم: اختاروا التخطيط من **`layout`**, والكارد من **`variant`**, ونوع المحتوى من **`display_type_id` / `content_type`**.  
4. نفس المكوّن للرئيسية ولصفحة أي فئة.

هذا كل المطلوب لتشغيل الصفحات والأقسام على Flutter من الصفر.
