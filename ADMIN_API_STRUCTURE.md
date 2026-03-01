# Admin API Structure - التقسيمات

## 1. Authentication (المصادقة)
- Login
- Logout
- Profile
- Store Token
- Notifications

## 2. Content Management (إدارة المحتوى)
### 2.1 Categories & Products (الفئات والمنتجات)
- Categories (الفئات)
- Category Attributes (خصائص الفئات)
- Category Details (تفاصيل الفئات)
- Brands (العلامات التجارية)
- Products (المنتجات)
- Product Variants (متغيرات المنتجات)
- Shop Product Variants (متغيرات المنتجات في المتاجر)

### 2.2 Sections & Pages (الأقسام والصفحات)
- Sections (الأقسام)
- Page Sections (أقسام الصفحات)
- Banners (البانرات)
- Recipes (الوصفات)

### 2.3 Baskets & Schedules (السلال والجداول)
- Baskets (السلال)
- Scheduled Baskets (السلال المجدولة)
- User Basket Schedules (جداول سلال المستخدمين)
- Schedules (الجداول الزمنية)

## 3. Locations (المواقع)
- Governorates (المحافظات)
- Cities (المدن)
- Areas (المناطق)

## 4. Users & Vendors (المستخدمين والموردين)
### 4.1 Users (المستخدمين)
- Users (المستخدمين)
- Marketers (المسوقين)

### 4.2 Vendors (الموردين)
- Vendors (الموردين)
- Vendor Users (مستخدمي الموردين)
- Seller Registrations (طلبات تسجيل البائعين)
  - Approve
  - Reject

### 4.3 Shops & Stores (المتاجر والفروع)
- Shops (الفروع)
- Stores (المتاجر)

### 4.4 Drivers (السائقين)
- Drivers (السائقين)

## 5. Orders (الطلبات)
- Orders List (قائمة الطلبات)
- Order Details (تفاصيل الطلب)
- Change Order Status (تغيير حالة الطلب)
- Assign Driver (تعيين سائق)
- Change Item Status (تغيير حالة عنصر)

## 6. Packages & Subscriptions (الباقات والاشتراكات)
### 6.1 User Packages (باقات المستخدمين)
- Packages (الباقات)
- Subscriptions (الاشتراكات)

### 6.2 Vendor Packages (باقات الموردين)
- Vendor Packages (باقات الموردين)
- Vendor Subscriptions (اشتراكات الموردين)

## 7. Rewards & Points (المكافآت والنقاط)
### 7.1 Gifts (الهدايا)
- Gifts (الهدايا)
- User Gifts (هدايا المستخدمين)

### 7.2 Points (النقاط)
- User Points (نقاط المستخدمين)
  - List
  - Show
  - Transactions
- Point Exchanges (تبادل النقاط)

## 8. Marketing & Promotions (التسويق والعروض)
- Coupons (الكوبونات)
- Services (الخدمات)
- Badges (الشارات)

## 9. Settings (الإعدادات)
### 9.1 System Settings (إعدادات النظام)
- Languages (اللغات)
- Currencies (العملات)

### 9.2 Content Settings (إعدادات المحتوى)
- Legal Documents (المستندات القانونية)
- FAQs (الأسئلة الشائعة)
- Notifications (الإشعارات)

### 9.3 Support (الدعم)
- Complaints (الشكاوى)

## 10. Administration (الإدارة)
### 10.1 Admins & Roles (المسؤولين والصلاحيات)
- Admins (المسؤولين)
- Roles (الأدوار)
- Permissions (الصلاحيات)

### 10.2 Activity (النشاط)
- Activity Logs (سجلات النشاط)

## 11. Statistics & Reports (الإحصائيات والتقارير)
### 11.1 Statistics (الإحصائيات)
- Dashboard (لوحة التحكم)
- Counts (الأعداد)
- Monthly Performance (الأداء الشهري)
- Orders by Status (الطلبات حسب الحالة)
- Top Shops (أفضل المتاجر)
- Revenue Trend (اتجاه الإيرادات)
- Orders by Hour (الطلبات حسب الساعة)
- Orders by Day (الطلبات حسب اليوم)
- Revenue by Payment (الإيرادات حسب طريقة الدفع)
- Top Categories (أفضل الفئات)
- User Growth (نمو المستخدمين)
- Order Funnel (قمع الطلبات)
- Average Order Value Trend (اتجاه متوسط قيمة الطلب)
- Driver Comparison (مقارنة السائقين)
- Stock Levels (مستويات المخزون)
- Sales Heatmap (خريطة المبيعات الحرارية)

### 11.2 Reports (التقارير)
- Sales Report (تقرير المبيعات)
- Product Movement (حركة المنتجات)
- Vendor Performance (أداء الموردين)
- Driver Performance (أداء السائقين)
- Sales by Location (المبيعات حسب الموقع)
- Sales by Category (المبيعات حسب الفئة)

### 11.3 Export Reports (تصدير التقارير)
- Export Sales (تصدير المبيعات)
- Export Product Movement (تصدير حركة المنتجات)
- Export Vendor Performance (تصدير أداء الموردين)
- Export Driver Performance (تصدير أداء السائقين)

---

## API Structure Summary

```
Admin API
├── Authentication
├── Content Management
│   ├── Categories & Products
│   ├── Sections & Pages
│   └── Baskets & Schedules
├── Locations
├── Users & Vendors
│   ├── Users
│   ├── Vendors
│   ├── Shops & Stores
│   └── Drivers
├── Orders
├── Packages & Subscriptions
│   ├── User Packages
│   └── Vendor Packages
├── Rewards & Points
│   ├── Gifts
│   └── Points
├── Marketing & Promotions
├── Settings
│   ├── System Settings
│   ├── Content Settings
│   └── Support
├── Administration
│   ├── Admins & Roles
│   └── Activity
└── Statistics & Reports
    ├── Statistics
    ├── Reports
    └── Export Reports
```
