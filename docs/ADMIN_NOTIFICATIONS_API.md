# Admin Notifications API

هذا الملف يشرح واجهات الـ API الخاصة بإدارة الإشعارات من لوحة الإدارة عبر `NotificationController`.

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
        "title": "تنبيه للسائقين",
        "body": "يوجد طلبات جديدة بانتظار التوصيل",
        "type": "driver",
        "target_page": "orders",
        "channels": ["fcm"],
        "created_at": "2026-04-16 14:30",
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
    "title": "تنبيه للسائقين",
    "body": "يوجد طلبات جديدة بانتظار التوصيل",
    "type": "driver",
    "target_page": "orders",
    "channels": ["fcm"],
    "created_at": "2026-04-16 14:30",
    "emoji": "🚚",
    "media": {
      "type": "image",
      "url": "https://example.com/storage/notifications/abc.jpg"
    }
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
- `type` (required): `all` | `driver` | `user` | `vendor`
- `channels` (required array, min 1)
- `channels[]` values:
  - `fcm`
  - `sms`
  - `email`
- `target_page` (optional): يجب أن يكون `slug` موجود في جدول `pages`
- `emoji` (optional): نص قصير حتى 10 أحرف
- `media` (optional file): `jpeg`, `jpg`, `png`, `gif`, `webp`

### Example Multipart Request

```http
POST /api/admin/notifications
Content-Type: multipart/form-data
```

Fields:

```text
title=عرض جديد
body=تم إطلاق عرض جديد اليوم
type=user
channels[0]=fcm
channels[1]=email
target_page=offers
emoji=🎉
media=(image file)
```

### Example JSON Request

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

### Example Response

```json
{
  "status": true,
  "message": "Item created successfully.",
  "data": {
    "id": 5,
    "title": "تنبيه للسائقين",
    "body": "يوجد طلبات جديدة بانتظار التوصيل",
    "type": "driver",
    "target_page": "orders",
    "channels": ["fcm"],
    "created_at": "2026-04-16 15:10",
    "emoji": "🚚",
    "media": null
  }
}
```

---

## سلوك الإرسال

عند إنشاء الإشعار:

1. يتم حفظ سجل في جدول `admin_notifications`
2. إذا تم رفع `media` يتم تخزينها داخل:
   - `storage/app/public/notifications`
3. يتم dispatch لـ:
   - `SendBulkNotificationJob`

والـ job يستقبل:

- `title`
- `body`
- `type`
- `channels`
- بيانات إضافية:
  - `type = admin`
  - `target_page`
  - `emoji`
  - `media_type`
  - `media_url`

---

## ملاحظات للفرونت

- عند وجود ملف، أرسلي الطلب كـ `multipart/form-data`
- `channels` يجب أن تكون array حقيقية
- `target_page` ليس نصًا حرًا بالكامل؛ يجب أن يكون `slug` موجودًا في جدول `pages`
- `media` في الرد تأتي بهذا الشكل:

```json
{
  "type": "image",
  "url": "https://example.com/storage/notifications/abc.jpg"
}
```

- إذا لم يوجد ملف:
  - `media = null`

