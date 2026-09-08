# الويب — تدفق التسجيل

> **أرسلوا هذا الملف لفريق الويب.**  
> الدليل الكامل (ويب + Flutter): [`REGISTER_FLOW.md`](./REGISTER_FLOW.md)  
> **آخر تحديث:** 8 أيلول 2026  
> Base: `/api/user` — بدون توكن حتى OTP

```
فورم تسجيل → POST /auth/register (200 بدون توكن)
     → شاشة OTP → POST /auth/verify-otp → احفظوا data.token
```

إعادة إرسال الكود: `POST /auth/login` بنفس الهاتف وكلمة المرور (403 = SMS جديد).  
**لا** تستخدموا `/auth/send-otp` هنا (هذا لنسيت كلمة المرور).

| الحقل | مطلوب |
|--------|--------|
| `name` · `phone` · `password` · `city_id` · `governorate_id` | نعم |
| `email` | لا — لا ترسلوا `""` |

`phone`: أرقام فقط. كلمة المرور: ≥ 8 + صغير + كبير + رقم + رمز.

مدن: `GET /api/user/cities?governorate_id={id}` — الباراميتر إلزامي.

التفاصيل والأمثلة والأخطاء: [`REGISTER_FLOW.md`](./REGISTER_FLOW.md).
