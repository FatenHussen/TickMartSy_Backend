# Flutter — تدفق التسجيل

> **أرسلوا هذا الملف لفريق Flutter.**  
> الدليل الكامل (ويب + Flutter): [`REGISTER_FLOW.md`](./REGISTER_FLOW.md)  
> **آخر تحديث:** 8 أيلول 2026  
> Base: `/api/user` — بدون توكن حتى OTP

```
Register screen → POST /auth/register (200, no token)
     → OTP screen → POST /auth/verify-otp → save data.token
```

Resend code: `POST /auth/login` with the same phone + password (`403` = new SMS).  
Do **not** call `/auth/send-otp` on this screen (that endpoint is password reset).

| Field | Required |
|--------|----------|
| `name` · `phone` · `password` · `city_id` · `governorate_id` | yes |
| `email` | no — never send `""` |

`phone`: digits only (`replaceAll(RegExp(r'\D'), '')`).  
Password: ≥ 8 + lower + upper + digit + symbol (`Passw0rd!`).

Cities: `GET /api/user/cities?governorate_id={id}` — query is required.

After token: `POST /auth/store-token` `{ deviceId, fcmToken }` if you have FCM.

Models: `email` on profile is `String?`. Full payloads and errors: [`REGISTER_FLOW.md`](./REGISTER_FLOW.md).
