# Flutter — التسجيل بدون إيميل

الإيميل صار **اختياري** بشاشة التسجيل، ورقم الهاتف صار **مطلوب دايماً**.

قبل، كان لازم واحد من الاثنين (`phone` أو `email`). هلق الهاتف هو هوية الحساب، والإيميل حقل إضافي بس.

> المسار: `POST /api/user/auth/register` — public، بدون توكن.

---

## 1) شنو تغيّر

| الحقل | قبل | بعد |
|---|---|---|
| `phone` | مطلوب إذا ما في إيميل | **مطلوب دايماً** |
| `email` | مطلوب إذا ما في هاتف | **اختياري** |
| كود التحقق (OTP) | SMS أو إيميل حسب اللي أرسلته | **SMS دايماً** على `phone` |
| التحقق من حساب موجود | على الحقل اللي أرسلته | على `phone` |

الإيميل إذا أرسلته يتخزن بالحساب بس **غير موثّق** (`email_verified_at = null`).

---

## 2) Request

### `POST /api/user/auth/register`

مع إيميل:

```json
{
  "name": "أحمد محمد",
  "phone": "0501234567",
  "email": "ahmad@example.com",
  "password": "Passw0rd!",
  "city_id": 1,
  "governorate_id": 1
}
```

بدون إيميل — صحيح تماماً:

```json
{
  "name": "أحمد محمد",
  "phone": "0501234567",
  "password": "Passw0rd!",
  "city_id": 1,
  "governorate_id": 1
}
```

**Response (200):**

```json
{
  "status": true,
  "message": "Success",
  "data": []
}
```

`data` فاضية وما في توكن. بعد النجاح انتقل مباشرة على شاشة OTP.

---

## 3) قواعد التحقق

| الحقل | القاعدة |
|---|---|
| `name` | مطلوب، نص |
| `phone` | **مطلوب**، أرقام فقط (`/^\d+$/`) — بدون `+` وبدون مسافات |
| `email` | اختياري، وإذا أرسلته لازم صيغة إيميل صحيحة |
| `password` | مطلوب، 8+ حرف، فيه حرف صغير وكبير ورقم ورمز |
| `city_id` | مطلوب، موجود |
| `governorate_id` | مطلوب، موجود |

**لا ترسل `email: ""`** — الباك اند بيرفضها كإيميل غير صحيح. إذا الحقل فاضي احذف المفتاح من الـ body.

### شكل الخطأ (422)

```json
{
  "error": "The phone field is required."
}
```

مفتاح واحد `error` فيه أول خطأ بس — ما في map للحقول. اعرضه بـ SnackBar أو تحت الحقل المناسب إذا قدرت تستنتجه.

---

## 4) بناء الـ payload

```dart
Future<void> register({
  required String name,
  required String phone,
  required String password,
  required int cityId,
  required int governorateId,
  String? email,
}) async {
  final body = <String, dynamic>{
    'name': name.trim(),
    'phone': phone.replaceAll(RegExp(r'[^\d]'), ''),
    'password': password,
    'city_id': cityId,
    'governorate_id': governorateId,
  };

  final trimmedEmail = email?.trim() ?? '';
  if (trimmedEmail.isNotEmpty) {
    body['email'] = trimmedEmail;
  }

  await dio.post('/api/user/auth/register', data: body);
}
```

خلي `email` بالموديل `String?` وليس `String`، وكذلك بموديل اليوزر لأنه ممكن يجي `null` من السيرفر.

---

## 5) الفورم

```dart
TextFormField(
  controller: phoneController,
  keyboardType: TextInputType.phone,
  inputFormatters: [FilteringTextInputFormatter.digitsOnly],
  decoration: const InputDecoration(labelText: 'رقم الهاتف *'),
  validator: (value) {
    if (value == null || value.trim().isEmpty) return 'رقم الهاتف مطلوب';
    return null;
  },
),

TextFormField(
  controller: emailController,
  keyboardType: TextInputType.emailAddress,
  decoration: const InputDecoration(
    labelText: 'الإيميل (اختياري)',
    helperText: 'لإشعارات واستعادة كلمة السر',
  ),
  validator: (value) {
    final email = value?.trim() ?? '';
    if (email.isEmpty) return null; // اختياري
    return RegExp(r'^\S+@\S+\.\S+$').hasMatch(email) ? null : 'إيميل غير صحيح';
  },
),
```

- احذف `*` من label الإيميل.
- احذف أي `TabBar` أو toggle بين "تسجيل بالإيميل" و"تسجيل بالهاتف" من شاشة التسجيل.
- احذف أي validator نوع "لازم إيميل أو هاتف".

---

## 6) بعد التسجيل — التحقق

الكود يوصل **SMS على الهاتف**، سواء اليوزر كتب إيميل أو لا.

```http
POST /api/user/auth/verify-otp
```

```json
{
  "phone": "0501234567",
  "code": "12345"
}
```

**Response:**

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "user": { "id": 12, "name": "أحمد محمد", "phone": "0501234567" },
    "token": "12|xxxxxxxx"
  }
}
```

- خزّن `data.token`.
- **انتبه:** كائن `user` يرجّع `phone` **أو** `email` — مو الاثنين، والأولوية للهاتف. فكل الحسابات الجديدة بترجع `phone`. لا تعتمد على `data.user.email`؛ استخدم `GET /api/user/auth/profile`.
- نص الشاشة: "أرسلنا الكود على رقم هاتفك" — ما في حالة إيميل بمسار التسجيل.
- إعادة الإرسال بنفس `phone`.
- لو اليوزر عمل login قبل التوثيق، الباك اند يرسل كود جديد ويرجّع خطأ تحقق → ودّيه على شاشة OTP.

```dart
Navigator.push(
  context,
  MaterialPageRoute(
    builder: (_) => OtpPage(phone: normalizedPhone),
  ),
);
```

---

## 7) الدخول واستعادة الباسورد — ما تغيّر

- `POST /api/user/auth/login` بعده يقبل `phone` أو `email` + `password`.
- `POST /api/user/auth/send-password` بعدها `verify-password` تقبل `phone` أو `email`.

بس حساب بلا إيميل ما بيقدر يستعيد الباسورد بالإيميل → خلي **الهاتف** هو الافتراضي بشاشة الاستعادة.

---

## 8) Do / don't

- **Do:** الهاتف مطلوب وأرقام فقط قبل الإرسال.
- **Do:** احذف مفتاح `email` كامل لما يكون فاضي.
- **Do:** خلي `email` nullable بكل الموديلات.
- **Do:** بعد التسجيل روح على OTP بالهاتف.
- **Don't:** ما ترسل `email: ""` أو `email: null` مع الحقل موجود بدون داعي.
- **Don't:** ما تمنع الـ submit لأن الإيميل فاضي.
- **Don't:** ما تستخدم الإيميل كمعرّف للحساب (ممكن `null` وممكن يتكرر).
- **Don't:** ما تعرض الإيميل كموثّق بعد التسجيل.

---

## 9) UI checklist

- [ ] حقل الهاتف مطلوب + `digitsOnly`
- [ ] label الإيميل صار "(اختياري)" وبدون validator إجباري
- [ ] validator الإيميل يرجّع `null` إذا الحقل فاضي
- [ ] الـ payload بلا مفتاح `email` عند الفراغ
- [ ] حذف tabs/toggle "إيميل أو هاتف" من التسجيل
- [ ] التسجيل ينجح بدون إيميل من الجهاز
- [ ] شاشة OTP تستقبل `phone` والنص يذكر الهاتف
- [ ] `verify-otp` ينرسل بـ `phone` والتوكن يتخزن
- [ ] عرض `error` من الـ 422 بشكل مباشر
- [ ] الملف الشخصي وشاشة التعديل تتعامل مع `email == null`
- [ ] شاشة استعادة الباسورد افتراضها الهاتف

### ما تلمسه

- شاشة الدخول (بعدها تقبل إيميل أو هاتف)
- تحديث الإيميل من الملف الشخصي: `POST /api/user/auth/profile/update_email` ثم `POST /api/user/auth/profile/verify`
- تسجيل التاجر `seller-register` — الإيميل هناك بعده **مطلوب**

---

## 10) ملاحظات

- `users.email` بقاعدة البيانات nullable وبدون unique.
- اليوزر يقدر يضيف إيميله لاحقاً من الملف الشخصي مع OTP على الإيميل، وهذا هو المكان الوحيد اللي يوثّق الإيميل.
- نقاط مكافأة التسجيل تُمنح عند نجاح `verify-otp`، مو عند `register`.
- Web: `FRONTEND_WEB_REGISTER_EMAIL_OPTIONAL.md`
