  # Dashboard — أسعار المنتج بالدولار/الليرة + حذف الفئات بتأكيد

  > للداشبورد React فقط. المسارات تحت `/api/admin` مع Admin token.

  ---

  ## الفهرس

  1. [فورم المنتج: تخطيط الأسعار (سطر أو سطرين)](#1-فورم-المنتج)
  2. [إدخال السعر بالليرة السورية](#2-إدخال-السعر-بالليرة)
  3. [حذف الفئات مع تنبيه](#3-حذف-الفئات)
  4. [Checklist](#4-checklist)

  ---

  ## 1) فورم المنتج

  ### المشكلة الحالية

  السعر يظهر مرتين:

  | الصف | المعنى الحقيقي | قابل للتعديل؟ |
  |------|----------------|---------------|
  | «السعر / المبلغ» USD + SYP | `price` (سعر البيع) | نعم |
  | «سعر المنتج» USD + SYP | غالباً `price_after_discount` | **لا** — عرض فقط |

  `price_currencies` و `price_after_discount` و `price_after_discount_currencies` **للعرض فقط**. لا تُرسلها في POST/PUT.

  ### التخطيط المطلوب (سطر أو سطرين)

  **سطر 1 — التسعير والخصم**

  | حقل واجهة | يُرسل للـ API | ملاحظات |
  |-----------|---------------|---------|
  | سعر ($) | `price` | اختياري؛ يُخزَّن بالدولار |
  | سعر (ل.س) | `price_syp` | اختياري؛ يُحوَّل تلقائياً لدولار إن لم يُرسل `price` |
  | نوع الخصم | `discount_type` | `none` \| `percentage` \| `fixed` |
  | قيمة الخصم | `discount` | يظهر إذا النوع ≠ `none` |
  | سعر بعد الخصم | — | **Placeholder للقراءة فقط** من `price_after_discount` + `_currencies` |
  | التكلفة ($) | `cost_price` | أو `cost_price_syp` بدلها |

  **سطر 2 — المخزون**

  | حقل | API |
  |-----|-----|
  | الكمية | `quantity` |
  | الوحدة | `unit_id` (أو `unit`) |

  ### مزامنة USD ↔ SYP في الواجهة

  ```js
  // عند الكتابة بالدولار → حدّث عرض الليرة من سعر الصرف
  // عند الكتابة بالليرة فقط → أرسل price_syp (أو احسب USD محلياً وأرسل price)

  // عند الحفظ:
  const payload = {
    price: usdValue ?? undefined,          // إن أدخله الأدمن
    price_syp: !usdValue && sypValue ? sypValue : undefined,
    cost_price: costUsd ?? undefined,
    cost_price_syp: !costUsd && costSyp ? costSyp : undefined,
    discount_type,
    discount: discount_type === 'none' ? 0 : discount,
    quantity,
    unit_id,
  };

  // لا ترسل: price_currencies, price_after_discount, price_after_discount_currencies
  ```

  إذا أُرسل `price` و `price_syp` معاً → الـ API يعتمد **الدولار** ويتجاهل الليرة.

  ### حقول اختيارية عند الإنشاء

  | حقل | مطلوب؟ |
  |-----|--------|
  | `media` (صور المنتج) | **لا** — يمكن إنشاء منتج بدون صور |
  | `country_id` (بلد المنشأ) | **لا** — **Select من قائمة الدول** وليس إدخال نص |

  لا تضع `required` على هذين الحقلين في فورم الإضافة/التعديل.

  ### بلد المنشأ — قائمة منسدلة (كل دول العالم)

  الحقل **ليس نص حر**. استخدم `country_id` كـ enum/Select:

  1. جلب الخيارات:
     ```http
     GET /api/admin/countries
     ```
     الرد بدون pagination — `data.items[]` فيها كل الدول (`id`, `name`, `code`).
  2. في الفورم: `<Select>` searchable — `value = country.id` و`label = name` حسب اللغة.
  3. عند الحفظ أرسل فقط:
     ```json
     { "country_id": 12 }
     ```
     أو احذف الحقل / أرسل `null` إذا فارغ.
  4. عند التعديل املأ القيمة من `country_id` أو `origin_country.id` في `GET /api/admin/products/{id}`.

  **لا تستخدم** حقول نص مثل `country` / `country.ar` / `country.en` — لم تعد مدعومة.

  ---

  ## 2) إدخال السعر بالليرة

  ### Create / Update

  ```http
  POST /api/admin/products
  PUT  /api/admin/products/{id}
  ```

  | حقل جديد | النوع | السلوك |
  |----------|-------|--------|
  | `price_syp` | number ≥ 0 | يُحوَّل لـ `price` (USD) عبر سعر صرف SYP |
  | `cost_price_syp` | number ≥ 0 | يُحوَّل لـ `cost_price` |
  | `variants.*.price_syp` | number ≥ 0 | نفس التحويل لمتغيّر |

  ### قراءة العرض بعد الحفظ

  ```json
  {
    "price": 0.000385,
    "price_currencies": {
      "USD": { "amount": 0.000385, "currency": "USD", "symbol": "$" },
      "SYP": { "amount": 5.01, "currency": "SYP", "symbol": "SYP" }
    },
    "price_after_discount": 0.000385,
    "price_after_discount_currencies": { "USD": {}, "SYP": {} },
    "cost_price": 0.0002,
    "cost_price_currencies": { "USD": {}, "SYP": {} }
  }
  ```

  - حقول الإدخال: `price` / `price_syp` / `cost_price` / `cost_price_syp`
  - حقول العرض فقط: `*_currencies` و `price_after_discount*`

  ---

  ## 3) حذف الفئات

  > التفصيل الكامل: `FRONTEND_DASHBOARD_CATEGORY_DELETE_CONFIRM.md`

  ### Endpoints

  | الغرض | Method | Endpoint |
  |-------|--------|----------|
  | ملخّص الأثر | GET | `/api/admin/categories/{id}/delete-impact` |
  | العناصر المرتبطة | GET | `/api/admin/categories/{id}/linked-items?page=1&per_page=10` |
  | تنفيذ الحذف | DELETE | `/api/admin/categories/{id}?confirm=true` |

  ### نافذة التأكيد (تبويبان)

  1. **التنبيه** — اعرض `data.warnings[].message`
  2. **العناصر المرتبطة** — جدول من `linked-items` (`type`: `product` | `basket` | `child_category`)

  ### مهم

  - `409` + `requires_confirmation: true` = **طلب تأكيد**، ليس خطأ أحمر في التوست.
  - بعد موافقة الأدمن: أعد `DELETE` مع `confirm=true`.

  ### عند التأكيد

  | مفتاح | ماذا يحدث |
  |-------|-----------|
  | `child_categories` | تُحذف الفئات الفرعية |
  | `products` | حذف ناعم + فك الارتباط؛ الطلبات تبقى |
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
          api.get(`/admin/categories/${id}/linked-items`, { params: { page, per_page: 10 } }),
      });
      if (!ok) return;
    }

    await api.delete(`/admin/categories/${id}`, { params: { confirm: true } });
    toast.success('تم حذف الفئة بنجاح');
  }
  ```

  ---

  ## 4) Checklist

  ### فورم المنتج

  - [ ] صف واحد للأسعار: USD + SYP + نوع خصم + خصم + سعر بعد الخصم (read-only) + تكلفة
  - [ ] صف للكمية والوحدة
  - [ ] لا تعرض `price` و `price_after_discount` كحقلين قابلين للتعديل معاً
  - [ ] اسمح بإدخال الليرة عند الإنشاء (`price_syp`) حتى لو الدولار فارغ
  - [ ] نظّف الـ payload: لا ترسل `*_currencies` ولا `price_after_discount`
  - [ ] `media` و `country_id` اختياريان (بدون required في الفورم)
  - [ ] بلد المنشأ = Select من `GET /api/admin/countries` عبر `country_id` (ليس نص حر)

  ### حذف الفئات

  - [ ] `GET delete-impact` قبل/بدل التوست الأحمر
  - [ ] تبويب تنبيهات + تبويب عناصر مرتبطة
  - [ ] `DELETE ?confirm=true` بعد الموافقة
  - [ ] تعامل مع `409` كـ confirmation لا كفشل
