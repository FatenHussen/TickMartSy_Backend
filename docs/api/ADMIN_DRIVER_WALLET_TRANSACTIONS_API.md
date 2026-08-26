# Admin Driver Wallet Transactions API

هذا الملف يشرح واجهات الـ Admin الخاصة بمحفظة السائق (`DriverWalletTransactionController`).

## Base URL

`/api/admin`

## Authentication

كل الـ endpoints تتطلب `auth:admin` عبر:

```http
Authorization: Bearer {admin_token}
```

## Endpoints

## 1) List Driver Wallet Transactions

`GET /api/admin/driver-wallet-transactions`

### Query Params (اختيارية)

- `type`: `paid_by_user` | `paid_by_system`
- `driver_id`: رقم السائق
- `order_id`: رقم الطلب
- `from`: تاريخ بداية (YYYY-MM-DD)
- `to`: تاريخ نهاية (YYYY-MM-DD)
- `min_amount`: أقل مبلغ
- `max_amount`: أعلى مبلغ
- `page`: رقم الصفحة (افتراضي 1)
- `per_page`: عدد العناصر في الصفحة (افتراضي 10)
- `sort_field`: مثل `id` أو `created_at` أو `amount` أو `type`
- `sort_order`: `asc` | `desc`
- `search`: بحث عام (حسب `id`, `driver_id`, `type`, `order_id`)

### مثال طلب

```http
GET /api/admin/driver-wallet-transactions?type=paid_by_system&driver_id=3&from=2026-04-01&to=2026-04-30&sort_field=created_at&sort_order=desc
```

### Example Response

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "items": [
      {
        "id": 12,
        "driver_id": 3,
        "driver": {
          "id": 3,
          "name": "محمد علي",
          "email": "driver@example.com",
          "phone": "0999999999",
          "image_url": null
        },
        "type": "paid_by_system",
        "amount": 750.0,
        "delivery_fee": 5000.0,
        "rate_percent": 15.0,
        "order_id": 98,
        "order_number": 98,
        "created_at": "2026-04-16 14:20:31"
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

## 2) Show One Driver Wallet Transaction

`GET /api/admin/driver-wallet-transactions/{id}`

### مثال

```http
GET /api/admin/driver-wallet-transactions/12
```

### Example Response

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "id": 12,
    "driver_id": 3,
    "driver": {
      "id": 3,
      "name": "محمد علي",
      "email": "driver@example.com",
      "phone": "0999999999",
      "image_url": null
    },
    "type": "paid_by_system",
    "amount": 750.0,
    "delivery_fee": 5000.0,
    "rate_percent": 15.0,
    "order_id": 98,
    "order": {
      "id": 98,
      "status": "delivered"
    },
    "created_at": "2026-04-16 14:20:31",
    "updated_at": "2026-04-16 14:20:31"
  }
}
```

---

## ملاحظات منطق العمل

- `type = paid_by_user`
  - تعني أن اليوزر دفع التوصيل.
- `type = paid_by_system`
  - تعني أن اليوزر كان توصيله مجاني (عرض/باقة/نقاط)، والنظام يتحمل التوصيل.
- `amount`
  - هي حصة السائق الفعلية (`delivery_fee * rate_percent / 100`).

