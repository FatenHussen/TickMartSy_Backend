# طلب سريع — زر الهيدر منفصل عن القسم

> **أرسلوا هذا الملف لفرق الويب + Flutter + الداشبورد.**  
> **آخر تحديث:** 25 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> تفصيل كامل: [`../custom-orders/`](../custom-orders/)

**قبل:** سويتش واحد (`quick_order_enabled` / `is_enabled`) كان يخفي **زر الهيدر** و**قسم الطلب السريع** مع بعض.  
**بعد:** كل واحد له سويتش لحاله.

---

## 1) داشبورد — إعدادان منفصلان

**Base:** `/api/admin/settings`

| Key | Type | ماذا يتحكم |
|-----|------|------------|
| `quick_order_header_enabled` | boolean | **زر** «طلب سريع / Urgent» في الهيدر / الـ Nav فقط |
| `quick_order_enabled` | boolean | **قسم** الطلب السريع في جسم الصفحة فقط |
| `quick_order_page_ids` | json | على أي صفحات يظهر **القسم** (لا يؤثر على الزر) |

```http
PUT /api/admin/settings/quick_order_header_enabled
{ "value": false }
```

```http
PUT /api/admin/settings/quick_order_enabled
{ "value": false }
```

**UI مقترح (تبويب طلب سريع):**
- سويتش «إظهار زر الهيدر»
- سويتش «إظهار القسم»
- multi-select صفحات (للقسم فقط)

يمكن إخفاء القسم وإبقاء الزر، أو العكس — مستقلان 100%.

---

## 2) ويب / Flutter — اقرأوا الحقلين

```http
GET /api/user/settings
```

`data.quick_order`:

```json
{
  "show_header": true,
  "show_section": true,
  "is_enabled": true,
  "page_ids": [1],
  "page_slugs": ["home"]
}
```

| حقل | معنى |
|-----|------|
| `show_header` | اعرض زر الهيدر إن `true` |
| `show_section` | القسم مفعّل (وما زال يحتاج مطابقة الصفحة) |
| `is_enabled` | = `show_section` (توافق خلفي — **لا** تستخدمه للزر) |

### قاعدة الإظهار

```js
const qo = settings.quick_order;

const showHeader = qo?.show_header === true;
const showSection =
  qo?.show_section === true &&
  qo.page_slugs?.includes(currentPageSlug);

// Nav / AppBar
{showHeader && <QuickOrderHeaderButton label={qo.badge} />}

// جسم الصفحة
{showSection && <QuickOrderSection config={qo} />}
```

```dart
final showHeader = qo.showHeader; // من show_header
final showSection = qo.showSection &&
    qo.pageSlugs.contains(currentPageSlug);
```

| حالة | زر الهيدر | القسم |
|------|-----------|--------|
| `show_header=true`, `show_section=true`, صفحة ∈ `page_slugs` | يظهر | يظهر |
| `show_header=true`, `show_section=false` | يظهر | مخفي |
| `show_header=false`, `show_section=true` + صفحة مناسبة | مخفي | يظهر |
| كلاهما `false` | مخفي | مخفي |

---

## 3) Checklist

### داشبورد
- [ ] سويتشان منفصلان: زر الهيدر + القسم
- [ ] `PUT .../quick_order_header_enabled` و `PUT .../quick_order_enabled`
- [ ] multi-select الصفحات يؤثر على القسم فقط

### ويب / Flutter
- [ ] parse `show_header` و `show_section`
- [ ] الزر يعتمد على `show_header` فقط
- [ ] القسم يعتمد على `show_section` + `page_slugs`
- [ ] لا تربطوا الزر بـ `is_enabled` بعد اليوم
