# أيقونات المنتج — الموقع والداشبورد

> **الجمهور:** ويب + داشبورد  
> **الباك:** جاهز بعد `git pull` + `php artisan db:seed --class=IconSeeder`  
> **تظهر على الموقع** في صفحة المنتج (`/product/{id}`) من `GET /api/user/products/{id}` → `icons[]`  
> **تاريخ:** 18 أيلول 2026

---

## هل نغيّر الموقع أو الداشبورد؟

| فريق | هل في شغل؟ | ماذا بالضبط |
|------|-------------|--------------|
| **الموقع (web)** | **نعم — عرض فقط** | ارسموا صف الأيقونات من `icons[]` القادم من الـ API. **لا تثبّتوا** «توصيل آمن / دفع عند الاستلام / …» بالكود. |
| **الداشبورد** | **نعم — ربط فقط** | ما في حقل جديد. اختاروا الأيقونات على المنتج (`icon_ids`). بدون ربط = الموقع فاضي حتى لو الـ seeder اشتغل. |
| **Flutter** | نفس الموقع | `icons[].icon` أو `icons[].image` + `name` |

الباك يجهّز الصور الاحترافية (SVG) ويرجّع الرابط. **الظهور على الموقع يعتمد على: الأدمن ربط الأيقونة بالمنتج + الموقع رسم `icons[]`.**

---

## 1) الموقع — صفحة المنتج

```http
GET /api/user/products/{id}
Accept-Language: ar
```

```json
{
  "icons": [
    {
      "id": 12,
      "name": "توصيل آمن",
      "icon": "https://tickdash.tickmartsy.com/storage/icons/secure-delivery.svg",
      "image": "https://tickdash.tickmartsy.com/storage/icons/secure-delivery.svg",
      "description": "عملية التوصيل مضمونة…"
    }
  ]
}
```

| الحقل | استخدموا |
|--------|----------|
| الصورة | `icon` **أو** `image` (نفس الرابط) |
| العنوان | `name` (حسب `Accept-Language`) |
| التلميح / المودال | `description` اختياري |

```tsx
{(product.icons ?? []).map((item) => (
  <div key={item.id}>
    <img src={item.icon || item.image} alt={item.name} />
    <span>{item.name}</span>
  </div>
))}
```

**غلط:**

```tsx
const PERKS = ['توصيل آمن', 'توصيل سريع', 'دفع عند الاستلام']; // ثابت
```

إذا `icons` فاضي أو `null`: أخفوا الصف بالكامل — لا placeholders وهمية.

---

## 2) الداشبورد — إنشاء / تعديل منتج

ما في API جديد للمنتج.

```http
GET /api/admin/icons
```

القائمة فيها الاسم + صورة SVG.

عند الحفظ:

```text
icon_ids[]=11
icon_ids[]=12
icon_ids[]=13
```

أو JSON: `"icon_ids": [11, 12, 13]`

| | |
|--|--|
| تجيبوا القائمة | `GET /api/admin/icons` |
| تربطوا بالمنتج | `icon_ids` في `POST/PUT /api/admin/products` |
| تشوفوا اللي انربط | `GET /api/admin/products/{id}` → `icons[].icon` |

لا ترفعوا PNG يدوي للأيقونات الجاهزة — الـ seeder حطّ الملفات في `storage/icons/*.svg`.

---

## 3) الأيقونات الجاهزة (بعد الـ seeder)

| عربي | English | ملف |
|------|---------|-----|
| جديد | New | `new.svg` |
| عرض خاص | Special Offer | `special-offer.svg` |
| الأكثر مبيعاً | Best Seller | `best-seller.svg` |
| توصيل مجاني | Free Delivery | `free-delivery.svg` |
| توصيل سريع | Fast Delivery | `fast-delivery.svg` |
| خصم | Discount | `discount.svg` |
| محدود | Limited | `limited.svg` |
| عضوي | Organic | `organic.svg` |
| مستورد | Imported | `imported.svg` |
| دفع عند الاستلام | Cash on Delivery | `cod.svg` |
| منتج مكفول | Warranted Product | `warranty.svg` |
| توصيل آمن | Secure Delivery | `secure-delivery.svg` |
| معاملتك آمنة | Secure Transaction | `secure-transaction.svg` |

---

## 4) الباك — نشر

```bash
cd /var/www/apps/backend
git pull
php artisan db:seed --class=IconSeeder --force
php artisan storage:link   # إذا الرابط مو موجود
```

بعدها من الداشبورد: عدّلوا المنتج → اختاروا الأيقونات → احفظوا → حدّثوا صفحة الموقع.

---

## Checklist

**ويب**

- [ ] صفحة `/product/{id}` تقرأ `data.icons`
- [ ] الصورة من `icon` أو `image` (URL كامل)
- [ ] لا صف أيقونات ثابت بالكود
- [ ] `icons.length === 0` → إخفاء القسم

**داشبورد**

- [ ] دروب/تشك بوكس من `GET /api/admin/icons`
- [ ] الحفظ يرسل `icon_ids`
- [ ] منتج بدون اختيار → الموقع بدون أيقونات (متوقع)

**الباك**

- [ ] `IconSeeder` + الملفات تحت `storage/app/public/icons/`
