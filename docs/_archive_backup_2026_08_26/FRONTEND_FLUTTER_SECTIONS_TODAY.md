# تعديلات Flutter — الأقسام والصفحات (2026-08-20)

> **⚠️ مدمج في [`FRONTEND_FLUTTER_COMPLETE.md`](./FRONTEND_FLUTTER_COMPLETE.md)** — ارسلوا الملف الموحّد لفريق Flutter.

مستند لفريق تطبيق الموبايل (Flutter). يغطي آخر تعديلات اليوم على عرض الأقسام داخل الصفحات وصفحات الفئات.

مراجع إضافية:

- `FRONTEND_SECTION_LAYOUT_AND_CARD.md` — فصل `layout` / `variant` / `display_type_id`
- `FRONTEND_FLUTTER_PAGE_BUILDER.md` — تفاصيل الـ endpoints وشكل الاستجابة
- `FRONTEND_FLUTTER_CATEGORY_PAGES.md` — صفحات الفئات (إن وُجد)

---

## الملخص

صار فصل واضح بين 3 مفاهيم كانت مختلطة:

| الحقل | المعنى | القيم |
|--------|--------|--------|
| **`layout`** | طريقة عرض **القسم كامل** | `slider` \| `list` \| `grid` |
| **`variant`** | شكل **الكارد داخل** القسم | `horizontal` \| `vertical` \| `square` |
| **`display_type_id`** | نوع **المحتوى** | أرقام ثابتة (بانر / منتج / متجر...) |

التطبيق **يقرأ** هذه الحقول من استجابة الأقسام فقط — لا يرسلها.

---

## 1) أين تظهر الأقسام؟

نفس شكل القسم في كل مكان → **ويدجت عرض أقسام موحّد**:

| الغرض | Endpoint |
|--------|----------|
| صفحة عامة (رئيسية / عروض...) | `GET /api/user/sections?page_slug={slug}` |
| صفحة فئة (أي مستوى) | `GET /api/user/categories/{categoryId}/page` |

عند فتح شاشة فئة: نادِ `categories/{id}/page`، اعرض `category` في الهيدر، وابنِ `sections` بنفس مُعرّض الأقسام.

---

## 2) نموذج القسم في الاستجابة

```json
{
  "id": 10,
  "name": { "ar": "...", "en": "..." },
  "type": "manual",
  "content_type": "product",
  "layout": "slider",
  "variant": "vertical",
  "display_type_id": 2,
  "is_default": false,
  "background_color": "#F7F7F7",
  "background_card_color": "#FFFFFF",
  "see_more": { "page_slug": "products", "params": { "category_id": 5 } },
  "show_when": {},
  "action": { "page_slug": "product_details" },
  "items": []
}
```

### قواعد العرض (بهذا الترتيب)

#### أولاً: `layout` — تخطيط القسم → ويدجت القسم

| `layout` | ويدجت مقترح |
|----------|-------------|
| `slider` | `ListView(scrollDirection: Axis.horizontal)` — **الافتراضي** |
| `list` | `ListView` / `Column` عمودي |
| `grid` | `GridView` (مثلاً `crossAxisCount: 2`) |

#### ثانياً: `variant` — شكل كارد العنصر داخل التخطيط

| `variant` | شكل الكارد |
|-----------|------------|
| `horizontal` | كارد عرضي عريض |
| `vertical` | كارد طولي / رأسي |
| `square` | كارد مربع (مناسب للفئات) |

#### ثالثاً: `display_type_id` — نوع المحتوى فقط

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

> لا تستخدموا `display_type_id` لاختيار السلايدر/الشبكة.  
> لا تخلطوا: سابقاً كان `variant: horizontal` يُفهم كسلايدر — **صار غلط**. السلايدر = `layout: slider`، وشكل الكارد = `variant`.

### Fallback للتوافق

- إذا `layout` غائب أو `null` → **`slider`**
- إذا `variant` غائب → **`horizontal`**

استخدموا `background_color` لخلفية القسم و`background_card_color` لخلفية الكارد.

---

## 3) ما يجب تعديله في كود Flutter

1. **موديل القسم (Section model / fromJson)**
   - أضيفوا حقل `layout` (String?)
   - أبقوا `variant` لشكل الكارد فقط
   - لا تغيّروا معنى `display_type_id`

2. **ويدجت عرض القسم الموحّد**
   - فرّعوا على `layout` أولاً (slider / list / grid)
   - داخل كل تخطيط مرّروا `variant` لويدجت الكارد

3. **أزيلوا المنطق القديم** إن وُجد
   - `variant == horizontal` → سلايدر أفقي  
   - `variant == vertical` → قائمة  
   - `variant == square` → شبكة  
   → هذا صار غلط؛ استخدموا `layout` للتخطيط و`variant` للكارد فقط

4. **كروت العناصر** (`ProductCard` / `CategoryCard` / …)
   - تأخذ `variant` وتغيّر نسبة العرض/الارتفاع أو التخطيط الداخلي للكارد

5. **صفحات الفئات**
   - نفس ويدجت الأقسام
   - غالباً: فئات فرعية `variant: square`، منتجات `variant: vertical`، مع `layout` (غالباً `slider`)

6. **البانرات**
   - غالباً `layout: slider` + `variant: horizontal`
   - صورة عرضية؛ عنصر واحد = بانر ثابت؛ عدة = سلايدر أفقي

---

## 4) مثال سريع (Dart)

```dart
Widget buildSection(Section section) {
  final layout = section.layout ?? 'slider';
  final cardVariant = section.variant ?? 'horizontal';

  switch (layout) {
    case 'list':
      return SectionListView(
        items: section.items,
        cardVariant: cardVariant,
      );
    case 'grid':
      return SectionGridView(
        items: section.items,
        cardVariant: cardVariant,
      );
    case 'slider':
    default:
      return SizedBox(
        height: cardHeightFor(cardVariant),
        child: ListView.builder(
          scrollDirection: Axis.horizontal,
          itemCount: section.items.length,
          itemBuilder: (_, i) => SectionItemCard(
            item: section.items[i],
            variant: cardVariant,
          ),
        ),
      );
  }
}
```

---

## 5) Checklist

- [ ] الموديل فيه `layout` + `variant` + `display_type_id`
- [ ] ويدجت الأقسام يفرّع على `layout`: `slider` | `list` | `grid`
- [ ] الكارد يعتمد على `variant`: `horizontal` | `vertical` | `square`
- [ ] fallback: بدون `layout` → `slider`
- [ ] `display_type_id` لنوع المحتوى فقط — ليس للتخطيط
- [ ] نفس ويدجت الأقسام لـ `page_slug` ولـ `categories/{id}/page`
- [ ] أقسام قديمة بدون `layout` ما تكسر الشاشة

---

## ملاحظة

الأدمن في الداشبورد يختار `layout` و`variant`.  
Flutter يعرض فقط حسب ما يرجع من الـ API.
