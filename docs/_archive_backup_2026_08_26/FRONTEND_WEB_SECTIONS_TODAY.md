# تعديلات الويب — الأقسام والصفحات (2026-08-20)

مستند لفريق موقع الويب. يغطي آخر تعديلات اليوم على عرض الأقسام داخل الصفحات وصفحات الفئات.

مراجع إضافية:

- `FRONTEND_SECTION_LAYOUT_AND_CARD.md` — فصل `layout` / `variant` / `display_type_id`
- `FRONTEND_WEB_PAGE_BUILDER.md` — تفاصيل الـ endpoints وشكل الاستجابة
- `FRONTEND_WEB_CATEGORY_PAGES.md` — صفحات الفئات

---

## الملخص

صار فصل واضح بين 3 مفاهيم كانت مختلطة:

| الحقل | المعنى | القيم |
|--------|--------|--------|
| **`layout`** | طريقة عرض **القسم كامل** | `slider` \| `list` \| `grid` |
| **`variant`** | شكل **الكارد داخل** القسم | `horizontal` \| `vertical` \| `square` |
| **`display_type_id`** | نوع **المحتوى** | أرقام ثابتة (بانر / منتج / متجر...) |

الويب **يقرأ** هذه الحقول من استجابة الأقسام فقط — لا يرسلها (واجهة المستخدم قراءة فقط).

---

## 1) أين تظهر الأقسام؟

نفس شكل القسم في كل مكان:

| الغرض | Endpoint |
|--------|----------|
| صفحة عامة (رئيسية / عروض...) | `GET /api/user/sections?page_slug={slug}` |
| صفحة فئة (أي مستوى) | `GET /api/user/categories/{categoryId}/page` |

`sections` بنفس الشكل → استخدموا **نفس مكوّن عرض الأقسام**.

---

## 2) شكل القسم في الاستجابة

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

#### أولاً: `layout` — تخطيط القسم

| `layout` | السلوك المطلوب |
|----------|----------------|
| `slider` | سلايدر أفقي (تمرير يمين/يسار) — **الافتراضي** |
| `list` | قائمة عمودية (عناصر تحت بعض) |
| `grid` | شبكة (مثلاً عمودين) |

#### ثانياً: `variant` — شكل كل كارد داخل التخطيط

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

> لا تستخدموا `display_type_id` كبديل عن `layout` أو `variant`.  
> لا تخلطوا: `horizontal` ليست طريقة عرض القسم — هي `variant` لشكل الكارد.

### Fallback للتوافق مع بيانات قديمة

- إذا `layout` غائب أو `null` → اعتبروه **`slider`**
- إذا `variant` غائب → اعتبروه **`horizontal`**

---

## 3) ما يجب تعديله في كود الويب

1. **مكوّن عرض القسم**
   - اقرأوا `layout` أولاً → اختاروا Slider / List / Grid
   - داخل المكوّن مرّروا `variant` لكارد العنصر

2. **أزيلوا المنطق القديم** إن وُجد
   - «كل الأقسام = سلايدر أفقي دائماً» و`variant` يحدد التخطيط  
   → صار غلط؛ التخطيط = `layout`، شكل الكارد = `variant`

3. **كارد المنتج / الفئة / المتجر**
   - يعتمد على `variant` (عرضي / طولي / مربع)
   - ليس على `display_type_id`

4. **صفحات الفئات**
   - `GET /api/user/categories/{id}/page`
   - نفس مكوّن الأقسام
   - الأقسام الافتراضية غالباً: فئات فرعية بكروت `square`، منتجات بكروت `vertical`، مع `layout` (غالباً `slider` ما لم يغيّره الأدمن)

5. **البانرات**
   - `type: "manual"` + محتوى بانر
   - غالباً `layout: "slider"` + `variant: "horizontal"`
   - صورة عرضية؛ عنصر واحد = بانر ثابت؛ عدة عناصر = سلايدر

---

## 4) مثال سريع (React)

```ts
// 1) تخطيط القسم
switch (section.layout ?? 'slider') {
  case 'list':
    return <SectionList section={section} />;
  case 'grid':
    return <SectionGrid section={section} />;
  default:
    return <SectionSlider section={section} />;
}

// 2) شكل الكارد داخل أي تخطيط
<ProductCard variant={section.variant ?? 'horizontal'} item={item} />
```

---

## 5) Checklist

- [ ] مكوّن الأقسام يدعم `layout`: `slider` | `list` | `grid`
- [ ] كارد العنصر يدعم `variant`: `horizontal` | `vertical` | `square`
- [ ] fallback: بدون `layout` → `slider`
- [ ] `display_type_id` للقراءة / نوع المحتوى فقط — ليس للتخطيط
- [ ] نفس المكوّن لـ `page_slug` ولـ `categories/{id}/page`
- [ ] لا كسر إذا وصل قسم قديم بدون `layout`

---

## ملاحظة (للتوضيح فقط — مش شغل الويب)

في الداشبورد الأدمن يختار:

1. `layout` = سلايدر / ليست / شبكة  
2. `variant` = شكل الكارد  

الويب يعرض فقط حسب ما يرجع من الـ API.
