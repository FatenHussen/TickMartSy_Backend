# الويب — الهيدر الإعلاني فوق تتبع الطلب

> **أرسلوا هذا الملف لفريق الويب فقط.**  
> **آخر تحديث:** 25 أيلول 2026  
> Base: `/api/user` + `Accept-Language: ar|en`  
> بنر (حقول العرض): [`WEB_BANNER_REQUIRED_FIELDS.md`](./WEB_BANNER_REQUIRED_FIELDS.md)  
> طلب نشط: [`../api/ORDER_USER_FLOW.md`](../api/ORDER_USER_FLOW.md) — `GET /api/user/orders/active`

على `/home` حالياً كرت **تتبع الطلب** يظهر فوق **الهيدر الإعلاني (البنر)**.  
المطلوب: **البنر أول شي** تحت الـ Nav، وبعده كرت التتبع (إن وُجد طلب نشط).

**ما في تغيير API** — ترتيب العرض عندكم فقط.

---

## الفهرس

1. [القاعدة](#1-القاعدة)
2. [مصدر كل بلوك](#2-مصدر-كل-بلوك)
3. [ترتيب الـ DOM](#3-ترتيب-الـ-dom)
4. [غلط vs صح](#4-غلط-vs-صح)
5. [Checklist](#5-checklist)

---

## 1) القاعدة

| الترتيب | العنصر | متى يظهر |
|---------|--------|----------|
| 1 | Nav | دائماً |
| 2 | **هيدر إعلاني (قسم بنر)** | إذا `GET /sections?page_slug=home` فيه قسم `manual_model: "banner"` و`items` غير فارغة |
| 3 | **كرت تتبع الطلب** | مستخدم مسجّل + `GET /orders/active` يرجع طلب (مو `null`) |
| 4 | باقي أقسام الصفحة حسب `order` | من نفس `/sections` |
| … | `quick_order` وغيره | حسب منطقكم الحالي |

البنر = قسم من الـ Page Builder.  
كرت التتبع = ويدجت ثابت عندكم — **مو** قسم في `/sections`.

---

## 2) مصدر كل بلوك

```http
GET /api/user/sections?page_slug=home
GET /api/user/orders/active
```

| البلوك | المصدر | ملاحظة |
|--------|--------|--------|
| بنر / هيدر إعلاني | `sections` حيث `manual_model === "banner"` (أو `display_type_id` البنر) | رتّبوا الأقسام بـ `order` من الـ API |
| تتبع الطلب | `orders/active` | إذا `data === null` → لا تعرضوا الكرت |

تأكدوا من الأدمن أن قسم البنر على الرئيسية `order` أصغر من باقي الأقسام (غالباً `1`).  
حتى لو البنر أول في الـ API، إذا رسمتم كرت التتبع **قبل** `ApiSectionsRenderer` سيظهر فوق البنر — وهذا الغلط الحالي.

---

## 3) ترتيب الـ DOM

```tsx
<>
  <Nav />

  {/* 1) بنر أولاً */}
  {bannerSections.map((section) => (
    <BannerSection key={section.id} section={section} />
  ))}

  {/* 2) تتبع الطلب بعد البنر */}
  {activeOrder && <ActiveOrderTracker order={activeOrder} />}

  {/* 3) باقي الأقسام (بدون البنر إن فصلتوه أعلاه) */}
  <ApiSectionsRenderer sections={restSections} />
</>
```

بديل مقبول: اعرضوا كل الأقسام حسب `order`، و**احشروا** كرت التتبع مباشرة بعد أول قسم بنر (أو بعد كل أقسام البنر المتتالية في أعلى الصفحة) — مو قبل أي قسم.

---

## 4) غلط vs صح

**غلط**

```tsx
<>
  {activeOrder && <ActiveOrderTracker />}
  <ApiSectionsRenderer sections={sections} />  {/* البنر يظهر تحت التتبع */}
</>
```

**صح**

```tsx
<>
  {/* بنر / أقسام order منخفضة أولاً */}
  <BannerOrFirstSections />
  {activeOrder && <ActiveOrderTracker />}
  <RestOfSections />
</>
```

---

## 5) Checklist

- [ ] على `/home` البنر فوق كرت «Track Order» / تتبع الطلب
- [ ] بدون طلب نشط: البنر يبقى أول محتوى تحت الـ Nav
- [ ] كرت التتبع فقط إذا `GET /orders/active` فيه بيانات
- [ ] أقسام `/sections` تُعرض حسب `order`
- [ ] حقول البنر حسب [`WEB_BANNER_REQUIRED_FIELDS.md`](./WEB_BANNER_REQUIRED_FIELDS.md)
