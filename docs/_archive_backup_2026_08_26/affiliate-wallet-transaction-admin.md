# Affiliate Wallet Transaction API (Admin)

توثيق API الخاص بالأدمن لقسم `AffiliateWalletTransactionController`.

## Base Endpoint
- `/api/admin/affiliate-wallet-transactions`
- جميع المسارات تتطلب `auth:admin`

## Endpoints

### 1) List Affiliate Wallet Transactions
- `GET /api/admin/affiliate-wallet-transactions`

**Query Params (اختيارية):**
- `search`: يبحث في `id`, `affiliate_id`, `type`, `order_id`
- `type`: `commission` أو `withdraw`
- `affiliate_id`: معرف الأفلييت (من `users.affiliate_id`)
- `order_id`: رقم الطلب
- `from`: تاريخ بداية (صيغة تاريخ)
- `to`: تاريخ نهاية (صيغة تاريخ)
- `min_amount`: أقل قيمة
- `max_amount`: أعلى قيمة
- `sort_field`: `id | created_at | amount | type`
- `sort_order`: `asc | desc`
- `page`: رقم الصفحة (افتراضي `1`)
- `per_page`: عدد العناصر في الصفحة (افتراضي `10`)

### 2) Show Single Transaction
- `GET /api/admin/affiliate-wallet-transactions/{id}`

---

## Validation (FilterRequest)

- `type`: nullable, in `commission,withdraw`
- `affiliate_id`: nullable, موجود في `users.affiliate_id`
- `order_id`: nullable, موجود في `orders.id`
- `from`: nullable date
- `to`: nullable date
- `min_amount`: nullable numeric >= 0
- `max_amount`: nullable numeric >= 0

---

## List Response (AllResource)

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "items": [
      {
        "id": 12,
        "affiliate_id": "AFF-1001",
        "affiliate": {
          "id": 5,
          "name": "John Doe",
          "email": "john@example.com",
          "phone": "+966500000000"
        },
        "type": "commission",
        "amount": 25.5,
        "order_id": 901,
        "order_number": 901,
        "created_at": "2026-04-14 11:30:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 3,
      "per_page": 10,
      "total": 28
    }
  }
}
```

## Show Response (OneResource)

```json
{
  "status": true,
  "message": "Success",
  "data": {
    "id": 12,
    "affiliate_id": "AFF-1001",
    "affiliate": {
      "id": 5,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+966500000000"
    },
    "type": "commission",
    "amount": 25.5,
    "order_id": 901,
    "order": {
      "id": 901,
      "status": "delivered"
    },
    "created_at": "2026-04-14 11:30:00",
    "updated_at": "2026-04-14 11:30:00"
  }
}
```

---

## Notes

- هذا القسم **قراءة فقط** حاليًا (لا يوجد `store`, `update`, `delete`).
- الأنواع المدعومة للحركات:
  - `commission`: رصيد عمولة
  - `withdraw`: سحب من المحفظة
