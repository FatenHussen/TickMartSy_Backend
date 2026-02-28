# Socket API Documentation

## Endpoints

### 1. Authorize Socket Connection

**Endpoint:** `GET /api/socket/authorize`

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `order_id` (required, integer): معرف الطلب
- `action` (required, string): القيمة المسموحة: `send_location` أو `join`

**Success Response (200):**
```json
{
    "status": true,
    "user_id": 88,
    "role": "user",
    "order_id": 123
}
```

**Fail Response (403):**
```json
{
    "status": false,
    "message": "Not authorized"
}
```

**Authorization Rules:**

1. **Admin**: مصرح دائماً بغض النظر عن الـ action
2. **Driver**: مصرح فقط إذا كان `driver_id == order.driver_id`
3. **User**: 
   - مصرح فقط للـ action `join`
   - يجب أن يكون `user_id == order.user_id`
   - إذا كان الـ action `send_location` يتم الرفض

---

### 2. Get User Information

**Endpoint:** `GET /api/socket/user`

**Headers:**
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "status": true,
    "user_id": 88,
    "role": "user",
    "name": "Hamdoon"
}
```

**Fail Response (401):**
```json
{
    "status": false,
    "message": "Not authorized"
}
```

**Roles:**
- `admin`: للمسؤولين
- `driver`: للسائقين
- `user`: للمستخدمين العاديين

---

## Usage Examples

### Example 1: User joining order tracking
```bash
curl -X GET "http://your-domain.com/api/socket/authorize?order_id=123&action=join" \
  -H "Authorization: Bearer user_token_here"
```

### Example 2: Driver sending location
```bash
curl -X GET "http://your-domain.com/api/socket/authorize?order_id=123&action=send_location" \
  -H "Authorization: Bearer driver_token_here"
```

### Example 3: Get current user info
```bash
curl -X GET "http://your-domain.com/api/socket/user" \
  -H "Authorization: Bearer token_here"
```

---

## Notes

- يتم التحقق من الـ token تلقائياً من خلال middleware `auth:sanctum`
- النظام يدعم ثلاثة أنواع من المستخدمين: Admin, Driver, User
- كل نوع له صلاحيات مختلفة حسب القواعد المذكورة أعلاه

