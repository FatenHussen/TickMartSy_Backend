# الداشبورد — إنشاء منتج: `vendor_id` (توست «حقل vendor id غير موجود»)

> **الجمهور:** فريق الداشبورد  
> **تاريخ:** 20 أيلول 2026  
> **الباك:** جاهز بعد `git pull` — **لا حقل جديد في الفورم**  
> Base: `POST /api/admin/products`  
> الفروع ملغاة: [`DASHBOARD_NO_SHOP_BRANCHES.md`](./DASHBOARD_NO_SHOP_BRANCHES.md)

---

## هل نغيّر شيء في الداشبورد؟

إنشاء المنتج يبقى بدون `vendor_id`. الباك هو اللي يضبطه.

| القناة | ماذا ترسلون | ماذا **لا** ترسلون |
|--------|-------------|---------------------|
| **للموقع** (افتراضي) | `sale_channel=platform` | `vendor_id` · `shop_variants` |
| **ربط بمتجر** | `sale_channel=shop` + `shop_id` | `vendor_id` — الباك يأخذه من المتجر |

نفس القواعد في [`dashboard.md` §14](./dashboard.md#14-قناة-البيع--للموقع-أو-ربط-بمتجر).

---

## ماذا كان الخطأ؟

التوست الأحمر:

```text
حقل vendor id غير موجود
```

معناه: انرسل (أو انحقن) `vendor_id` مو موجود في جدول `vendors` — غالباً `0` أو `""` أو id مستخدم بائع بدل id البائع.

التوستان المتطابقان = **نفس** الخطأ معروض مرتين (مرّة من `message` ومرّة من `errors.vendor_id`). الباك بعد الإصلاح ما رح يرجع هالرسالة إذا ما أرسلتوا `vendor_id` غلط.

---

## Payload

```text
# ✅ موقع — بدون vendor_id
sale_channel=platform
category_id=20
name[ar]=...
name[en]=...

# ✅ متجر — بدون vendor_id؛ المتجر يكفي
sale_channel=shop
shop_id=5
```

```js
// ❌ لا
formData.append('vendor_id', vendorId || 0);
formData.append('vendor_id', '');
body.vendor_id = selectedVendorUserId; // هذا vendor_users.id مو vendors.id

// ✅ نعم — احذفوا المفتاح إذا فاضي
if (vendorId) formData.append('vendor_id', vendorId); // اختياري للمتجر فقط — والباك ما يحتاجه
```

دروب داون «بائع» إن وجد: لاختيار المتجر (واحد لكل بائع). لا تضيفوه على `POST /products`.

---

## توست الأخطاء (موصيّ به)

422 يرجع:

```json
{
  "status": false,
  "message": "خطأ في التحقق",
  "errors": {
    "vendor_id": ["حقل vendor id غير موجود"]
  }
}
```

اعرضوا **`errors` مرة واحدة** (flat unique). لا توست لـ `message` إذا نفس النص موجود داخل `errors`.

---

## Checklist

- [ ] إنشاء منتج **للموقع**: `sale_channel=platform` — **بدون** `vendor_id` وبدون `shop_variants`
- [ ] إنشاء منتج **متجر**: `sale_channel=shop` + `shop_id` — **بدون** `vendor_id` وبدون قائمة فروع
- [ ] لا `vendor_id=0` ولا `""` ولا id من `vendor_users`
- [ ] توست 422: رسائل `errors` مرة واحدة (لا توستان بنفس النص)
- [ ] بائع في الواجهة = اختيار المتجر، مو حقل حفظ

**الباك جاهز. الداشبورد: لا حقل جديد — فقط لا ترسلوا `vendor_id` ولا تعرضوا فروع.**
