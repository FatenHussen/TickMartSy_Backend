# Seller Registration API Documentation

## Overview
This API allows admins to manage seller registration requests, approve/reject them, and automatically create vendor accounts with credentials sent via email.

## Architecture

### Structure
```
app/
├── Http/
│   ├── Controllers/Admin/SellerRegistration/
│   │   └── SellerRegistrationCrudController.php
│   ├── Requests/Admin/SellerRegistration/
│   │   └── ApproveRequest.php
│   └── Resources/SellerRegistration/
│       ├── AllResource.php
│       └── OneResource.php
├── Services/Admin/
│   └── SellerRegistrationService.php
├── Models/
│   └── SellerRegistration.php
└── Mail/
    └── VendorCredentialsMail.php
```

## Base URL
```
/api/admin/seller-registrations
```

## Authentication
All endpoints require admin authentication using Bearer token.

```
Authorization: Bearer {admin_token}
```

---

## Endpoints

### 1. List All Seller Registrations

**GET** `/api/admin/seller-registrations`

Get a paginated list of all seller registration requests.

**Query Parameters:**
- `page` (optional): Page number for pagination (default: 1)
- `per_page` (optional): Items per page (default: 20)

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "seller_name": "John Doe",
        "email": "john@example.com",
        "store_name": "John's Store",
        "address": "123 Main St",
        "commercial_register_number": "CR123456",
        "commercial_register_date": "2024-01-15",
        "country": "Saudi Arabia",
        "governorate": {
          "id": 1,
          "name": "Riyadh"
        },
        "city": {
          "id": 5,
          "name": "Al Olaya"
        },
        "logo": "http://example.com/storage/seller-registrations/logo.jpg",
        "status": "pending",
        "registered_at": "2024-02-20 10:30:00",
        "created_at": "2024-02-20 10:30:00",
        "updated_at": "2024-02-20 10:30:00"
      }
    ],
    "per_page": 20,
    "total": 50
  }
}
```

---

### 2. Get Single Seller Registration

**GET** `/api/admin/seller-registrations/{id}`

Get details of a specific seller registration.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "seller_name": "John Doe",
    "email": "john@example.com",
    "store_name": "John's Store",
    "address": "123 Main St",
    "commercial_register_number": "CR123456",
    "commercial_register_date": "2024-01-15",
    "country": "Saudi Arabia",
    "governorate": {
      "id": 1,
      "name": "Riyadh"
    },
    "city": {
      "id": 5,
      "name": "Al Olaya"
    },
    "logo": "http://example.com/storage/seller-registrations/logo.jpg",
    "status": "pending",
    "registered_at": "2024-02-20 10:30:00",
    "created_at": "2024-02-20 10:30:00",
    "updated_at": "2024-02-20 10:30:00"
  }
}
```

---

### 3. Approve Seller Registration

**POST** `/api/admin/seller-registrations/{id}/approve`

Approve a seller registration and create vendor account. This will:
1. Create a Vendor record
2. Create a Shop record (first shop for the vendor)
3. Create a VendorUser record with generated password
4. Link the VendorUser to the Shop
5. Send email with credentials to the seller
6. Update registration status to 'approved'

**Request Body (Optional):**
```json
{
  "commission_rate": 10.5,
  "contract_duration_months": 12
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Registration approved successfully and credentials sent via email",
  "data": {
    "vendor_id": 15,
    "shop_id": 23,
    "user_id": 45
  }
}
```

**Response (Error - Not Pending):**
```json
{
  "success": false,
  "message": "Registration is not pending"
}
```

**Response (Error - Server Error):**
```json
{
  "success": false,
  "message": "Failed to approve registration: {error_message}"
}
```

---

### 4. Reject Seller Registration

**POST** `/api/admin/seller-registrations/{id}/reject`

Reject a seller registration request.

**Response (Success):**
```json
{
  "success": true,
  "message": "Registration rejected successfully"
}
```

**Response (Error - Not Pending):**
```json
{
  "success": false,
  "message": "Registration is not pending"
}
```

---

### 5. Delete Seller Registration

**DELETE** `/api/admin/seller-registrations/{id}`

Delete a seller registration record.

**Response (Success):**
```json
{
  "success": true,
  "message": "Registration deleted successfully"
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "Failed to delete registration: {error_message}"
}
```

---

## Status Values

- `pending`: Registration is waiting for admin review
- `approved`: Registration has been approved and vendor account created
- `rejected`: Registration has been rejected by admin

---

## Email Notification

When a registration is approved, an email is automatically sent to the seller with:
- Welcome message
- Login credentials (email and generated password)
- Link to vendor login page
- Security instructions to change password

---

## Filament Admin Panel

The system also includes a Filament admin panel resource for managing seller registrations with:
- List view with filters
- View/Edit forms
- Approve/Reject actions with confirmation
- Status badges
- Automatic email sending on approval

Access via: `/admin/seller-registrations`

---

## Database Structure

### seller_registrations table
- `id`: Primary key
- `email`: Seller email
- `password`: Encrypted password (from registration form)
- `seller_name`: Name of the seller
- `store_name`: Name of the store
- `registered_at`: Registration timestamp
- `address`: Store address
- `commercial_register_number`: CR number
- `commercial_register_date`: CR date
- `country`: Country
- `city_id`: Foreign key to cities
- `governorate_id`: Foreign key to governorates
- `logo`: Logo file path
- `status`: enum('pending', 'approved', 'rejected')

---

## Workflow

1. User submits seller registration form (via user API)
2. Admin reviews registration in Filament panel or via API
3. Admin approves registration:
   - System creates Vendor
   - System creates first Shop for Vendor
   - System creates VendorUser with random password
   - System links VendorUser to Shop
   - System sends email with credentials
4. Seller receives email and logs in to vendor panel
5. Seller changes password on first login

---

## Error Handling

All endpoints return appropriate HTTP status codes:
- `200`: Success
- `400`: Bad request (e.g., registration not pending)
- `404`: Registration not found
- `500`: Server error

---

## Security Notes

- All endpoints require admin authentication
- Passwords are hashed before storage
- Random secure passwords are generated for new vendors
- Email contains security instructions
- Vendors should change password on first login
