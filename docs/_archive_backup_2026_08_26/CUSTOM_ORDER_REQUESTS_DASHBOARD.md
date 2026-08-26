# Custom Order Requests — Dashboard (Admin)

لوحة الإدارة: استلام طلبات النص/الصور، تحويلها لطلب نظامي (كتالوج و/أو بنود خارجية)، وإشعار الزبون للموافقة.

**Base:** `/api/admin`  
**Auth:** `auth:admin`  
**صلاحيات Spatie:**

| صلاحية | استخدام |
|--------|---------|
| `customorderrequest.view` | قائمة + تفاصيل |
| `customorderrequest.update` | تحويل `convert` + إلغاء `cancel` |

بعد إضافة الموديل للـ seeder شغّل:

```bash
php artisan db:seed --class=RolePermissionSeeder
```

(أو امنح الدور الصلاحيات الجديدة يدوياً.)

---

## Endpoints

| Method | Path | صلاحية |
|--------|------|--------|
| `GET` | `/custom-order-requests` | view |
| `GET` | `/custom-order-requests/{id}/get_one` | view |
| `POST` | `/custom-order-requests/{id}/convert` | update (`multipart`) |
| `POST` | `/custom-order-requests/{id}/cancel` | update |

فلاتر القائمة: `status`, `user_id`, `search`, `sort_field`, `sort_order`, `page`, `per_page`.

---

## فلو الأدمن

```
1) إشعار: طلب سريع جديد (pending_pricing)
2) فتح التفاصيل: قراءة النص + صور الزبون + العنوان + الوقت المتوقع + طريقة الدفع المبدئية
3) تحويل convert:
   أ) كل البنود من الموقع → type=catalog فقط (سعر النظام)
   ب) جزء من برا → catalog + external (سعر + فاتورة اختيارية + تفاوت إلزامي)
4) الحالة → waiting_approval + إنشاء Order (cart_type=custom)
5) الزبون يوافق → preparing (مسار الطلب العادي)
   أو يلغي → cancelled
```

**لا ترسل سعراً تقريبياً وحده.** ابنِ بنوداً واضحة ثم أرسل التفاصيل.

---

## Convert — جسم الطلب

```http
POST /api/admin/custom-order-requests/{id}/convert
Content-Type: multipart/form-data
```

### حقول عامة

| حقل | مطلوب | وصف |
|-----|--------|-----|
| `items` | نعم | مصفوفة بنود ≥ 1 |
| `delivery_price` | لا | افتراضي 0 |
| `approximate_total` | لا | اختياري؛ نادراً يُستخدم |
| `price_variance_type` | نعم إن وُجد external | `percent` أو `fixed` |
| `price_variance_value` | نعم إن وُجد external | رقم ≥ 0 |
| `admin_note` | لا | ملاحظة داخلية/للطلب |
| `is_instant_delivery` | لا | boolean |

### بند كتالوج

| حقل | مطلوب |
|-----|--------|
| `items[i][type]` | `catalog` |
| `items[i][shop_product_variant_id]` | نعم |
| `items[i][quantity]` | نعم ≥ 1 |
| `items[i][note]` | لا |

السعر يُؤخذ من النظام (لا تسعير يدوي للبند الكتالوج).

### بند خارجي

| حقل | مطلوب |
|-----|--------|
| `items[i][type]` | `external` |
| `items[i][product_name]` | نعم |
| `items[i][unit_price]` | نعم ≥ 0 |
| `items[i][quantity]` | نعم |
| `items[i][note]` | لا (افتراضي: مصدر خارجي) |
| `items[i][invoice_image]` | لا | صورة فاتورة → تُخزَّن في `custom_order_invoices/` |

### مثال (حالة مختلطة)

```
items[0][type]=catalog
items[0][shop_product_variant_id]=101
items[0][quantity]=2
items[1][type]=external
items[1][product_name]=جبنة بلدية من السوق
items[1][unit_price]=15000
items[1][quantity]=1
items[1][invoice_image]=@invoice.jpg
delivery_price=2000
price_variance_type=percent
price_variance_value=10
admin_note=البند الثاني غير متوفر بالمخزون
```

---

## إلغاء من الأدمن

```http
POST /api/admin/custom-order-requests/{id}/cancel
Content-Type: application/json

{ "rejection_reason": "تعذر توفير المطلوب" }
```

- يعمل على `pending_pricing` أو `waiting_approval`.
- إن وُجد طلب نظامي مرتبط: يُلغى (`cancelled_by_admin`) ويُعاد المخزون للبنود الكتالوج فقط.

---

## حالات الطلب النظامي الناتج

| حقل Order | قيمة |
|-----------|------|
| `status` عند التحويل | `waiting_approval` |
| `cart_type` | `custom` |
| `is_paid` | `false` حتى موافقة الزبون |
| `has_external_items` | حسب البنود |
| `custom_order_request_id` | ربط عكسي |

بعد موافقة الزبون: `preparing` → `out_delivery` → … عبر APIs الطلب العادية.

انتقالات الأدمن على الطلب تسمح من `waiting_approval` إلى `preparing` / إلغاء — لكن المسار المقصود: موافقة الزبون ثم معالجة عادية.

---

## UI الداش المقترح

1. قائمة بفلتر سريع: **بانتظار التسعير** (`pending_pricing`).
2. شاشة تفاصيل على عمودين:
   - يمين/يسار: نص الزبون + معرض صوره + عنوان + وقت متوقع.
   - الجانب الآخر: نموذج إضافة بنود (بحث variant + زر «بند خارجي»).
3. ملخص أسعار حي قبل الإرسال.
4. عند وجود بند خارجي: إجبار حقول التفاوت + رفع فاتورة اختياري.
5. زر «إرسال للزبون للموافقة» = `convert`.
6. بعد التحويل: عرض `order_id` ورابط لصفحة الطلب النظامي.

---

## إشعارات

| حدث | للمستلم | محتوى تقريبي |
|-----|---------|----------------|
| إنشاء طلب سريع | كل الأدمن | «طلب سريع جديد بانتظار التسعير» + `custom_order_request_id` |
| بعد convert | الزبون | تفاصيل البنود + المجموع + تنبيه التفاوت إن وُجد |
| إلغاء أدمن | الزبون | السبب |

`data.type = custom_order_request` لفتح شاشة الطلب المخصص في الواجهة.

---

## إعدادات قسم الطلب السريع (الهوم)

التحكم بإظهار القسم في التطبيق/الويب وخلفيته وشكل الكروت عبر **Settings**:

**Base:** `/api/admin/settings`  
**صلاحيات:** `setting.view` / `setting.update`

| Key | Type | وصف |
|-----|------|-----|
| `quick_order_enabled` | boolean | إظهار/إخفاء القسم + زر الهيدر في التطبيق |
| `quick_order_background_image` | file | صورة خلفية القسم (`multipart` حقل `value`) |
| `quick_order_background_color` | string | لون احتياطي إن لم تُرفع صورة (مثال `#FFE8D6`) |
| `quick_order_card_background_color` | string | خلفية كروت الخطوات |
| `quick_order_card_variant` | string | `horizontal` \| `vertical` \| `square` |
| `quick_order_badge` | json | `{ "ar": "...", "en": "..." }` |
| `quick_order_title` | json | عنوان القسم |
| `quick_order_subtitle` | json | الوصف |
| `quick_order_cta` | json | نص زر CTA |
| `quick_order_steps` | json | مصفوفة خطوات (حتى 6) |

```http
PUT /api/admin/settings/quick_order_enabled
Content-Type: application/json

{ "value": false }
```

```http
PUT /api/admin/settings/quick_order_background_image
Content-Type: multipart/form-data

value: <image file>
```

```http
PUT /api/admin/settings/quick_order_card_variant
{ "value": "horizontal" }
```

التطبيق يقرأ النتيجة مجمّعة من `GET /api/user/settings` → `data.quick_order`.

**UI لوحة مقترح:** تبويب «طلب سريع» ضمن الإعدادات: سويتش تفعيل، رفع صورة خلفية، اختيار لون القسم ولون الكارد، قائمة منسدلة لشكل الكارد، حقول نصوص AR/EN، محرر الخطوات.

---

## ملاحظات تقنية

- صور الزبون: `storage/app/public/custom_order_requests/`
- فواتير الأدمن: `storage/app/public/custom_order_invoices/`
- بنود خارجية: `order_items.shop_product_variant_id = null`, `is_external = true`
- المخزون يُنقص عند `convert` للبنود الكتالوج ويُعاد عند الإلغاء قبل التحضير
- طلبات السلة العادية (`pending`) لم تُمس؛ الحالة الجديدة `waiting_approval` للطلبات المخصصة فقط عند الإنشاء من هذا المسار
