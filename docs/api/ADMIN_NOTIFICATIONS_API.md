# Admin Notifications API

هذا الملف يشرح واجهات الـ API الخاصة بـ `NotificationController` في لوحة الإدارة.

## Base URL

`/api/admin`

## Authentication

كل هذه الواجهات تتطلب `auth:admin`.

```http
Authorization: Bearer {admin_token}
```

## Endpoints

## 1) List Notifications

`GET /api/admin/notifications`

### Query Params

- `type` (optional): `all` | `driver` | `user` | `vendor`
- `search` (optional): بحث في `id`, `title`, `body`, `type`
- `page` (optional): رقم الصفحة
- `per_page` (optional): عدد العناصر
- `sort_field` (optional): مثل `id`
- `sort_order` (optional): `asc` | `desc`

### Example Request

```http
GET /api/admin/notifications?type=driver&page=1&per_page=10
```

### Example Response

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "items": [
      {
        "id": 4,
        "title": "تنبيه للفئات المحددة",
        "body": "يوجد تحديث جديد",
        "type": "driver,user",
        "target_page": "orders",
        "channels": ["fcm"],
        "created_at": "2026-04-22 14:30",
        "emoji": "🚚",
        "media": {
          "type": "image",
          "url": "https://example.com/storage/notifications/abc.jpg"
        }
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 10,
      "total": 1
    }
  }
}
```

---

## 2) Show One Notification

`GET /api/admin/notifications/{id}`

### Example Request

```http
GET /api/admin/notifications/4
```

### Example Response

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "id": 4,
    "title": "تنبيه للفئات المحددة",
    "body": "يوجد تحديث جديد",
    "type": "driver,user",
    "target_page": "orders",
    "channels": ["fcm", "email"],
    "created_at": "2026-04-22 14:30",
    "emoji": "🚚",
    "media": null
  }
}
```

---

## 3) Create Notification

`POST /api/admin/notifications`

### Content-Type

- استخدمي `multipart/form-data` إذا يوجد `media`
- ويمكن استخدام JSON إذا لا يوجد ملف

### Request Fields

- `title` (required)
- `body` (required)
- `channels` (required array, min 1)
- `channels[]` values:
  - `fcm`
  - `sms`
  - `email`
- `target_page` (optional): يجب أن يكون `slug` موجود في جدول `pages`
- `emoji` (optional): نص قصير حتى 10 أحرف
- `media` (optional file): `jpeg`, `jpg`, `png`, `gif`, `webp`

### Targeting Fields

يمكنك تحديد المستلمين بإحدى الطريقتين:

1. **طريقة قديمة (Type مفرد):**
   - `type` (required إذا لم يتم إرسال `types`)
   - القيم: `all` | `driver` | `user` | `vendor`

2. **طريقة جديدة (Types متعددة):**
   - `types` (required إذا لم يتم إرسال `type`)
   - مصفوفة من: `all` | `driver` | `user` | `vendor`
   - مثال: `["driver", "user"]`

### Optional IDs (استهداف جزئي)

- `driver_ids` (optional array): إذا أرسلتِها مع وجود `driver` ضمن الأنواع، يتم الإرسال فقط لهؤلاء السائقين
- `user_ids` (optional array): إذا أرسلتِها مع وجود `user` ضمن الأنواع، يتم الإرسال فقط لهؤلاء المستخدمين
- `vendor_ids` (optional array): إذا أرسلتِها مع وجود `vendor` ضمن الأنواع، يتم الإرسال فقط لهؤلاء البائعين (`vendor_users.id`)

> إذا لم يتم إرسال `*_ids` لنوع معيّن، يتم الإرسال إلى **كل** ذلك النوع.

### Example 1: Type مفرد (التوافق الخلفي)

```json
{
  "title": "تنبيه للسائقين",
  "body": "يوجد طلبات جديدة بانتظار التوصيل",
  "type": "driver",
  "channels": ["fcm"],
  "target_page": "orders",
  "emoji": "🚚"
}
```

### Example 2: Types متعددة + IDs مخصصة

```json
{
  "title": "تنبيه مخصص",
  "body": "رسالة موجهة لفئات محددة",
  "types": ["driver", "user", "vendor"],
  "driver_ids": [1, 5, 9],
  "user_ids": [10, 11],
  "vendor_ids": [3, 7],
  "channels": ["fcm", "email"],
  "target_page": "offers",
  "emoji": "🎉"
}
```

### Example 3: Types متعددة بدون IDs (إرسال جماعي)

```json
{
  "title": "إشعار عام",
  "body": "رسالة لكل المستخدمين والسائقين",
  "types": ["user", "driver"],
  "channels": ["fcm"]
}
```

### Example Response

```json
{
  "status": true,
  "message": "Item created successfully.",
  "data": {
    "id": 5,
    "title": "تنبيه مخصص",
    "body": "رسالة موجهة لفئات محددة",
    "type": "driver,user,vendor",
    "target_page": "offers",
    "channels": ["fcm", "email"],
    "created_at": "2026-04-22 15:10",
    "emoji": "🎉",
    "media": null
  }
}
```

---

## Validation Rules (مختصر)

- يجب إرسال واحد فقط على الأقل من:
  - `type`
  - `types`
- `types` يجب أن تكون array غير فارغة
- `driver_ids.*` يجب أن تكون موجودة في `drivers.id`
- `user_ids.*` يجب أن تكون موجودة في `users.id`
- `vendor_ids.*` يجب أن تكون موجودة في `vendor_users.id`

---

## سلوك الإرسال

عند إنشاء الإشعار:

1. يتم حفظ سجل في جدول `admin_notifications`
2. إذا تم رفع `media` يتم تخزينها داخل:
   - `storage/app/public/notifications`
3. يتم dispatch لـ:
   - `SendBulkNotificationJob`
4. الجوب يحدد المستلمين بناءً على `type`/`types`
5. إذا كانت `*_ids` موجودة لنوع معيّن، يتم حصر الإرسال على هذه المعرفات

---

## ملاحظات للفرونت

- عند وجود ملف، أرسلي الطلب كـ `multipart/form-data`
- `channels` يجب أن تكون array حقيقية
- `target_page` ليس نصًا حرًا بالكامل؛ يجب أن يكون `slug` موجودًا في جدول `pages`
- حقل `type` في الاستجابة قد يكون قيمة مفردة (`driver`) أو قيم مجمعة مفصولة بفاصلة (`driver,user`)
- `media` في الرد تأتي بهذا الشكل:

```json
{
  "type": "image",
  "url": "https://example.com/storage/notifications/abc.jpg"
}
```

- إذا لم يوجد ملف:
  - `media = null`

