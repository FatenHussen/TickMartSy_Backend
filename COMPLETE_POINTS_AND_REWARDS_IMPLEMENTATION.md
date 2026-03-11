# نظام النقاط والجوائز الشامل | Complete Points and Rewards System

## 📋 جدول المحتويات | Table of Contents
1. [نظرة عامة](#نظرة-عامة)
2. [بنية قاعدة البيانات](#بنية-قاعدة-البيانات)
3. [المودلز](#المودلز)
4. [الخدمات](#الخدمات)
5. [الكونترولرز](#الكونترولرز)
6. [الراوتس](#الراوتس)
7. [الموارد](#الموارد)
8. [الطلبات](#الطلبات)
9. [الإشعارات](#الإشعارات)
10. [أمثلة الاستخدام](#أمثلة-الاستخدام)

---

## 🎯 نظرة عامة | Overview

### النقاط | Points
- **التعريف**: نقاط يكسبها المستخدم من خلال الشراء والأنشطة
- **الاستخدام**: استبدالها بخصومات أو هدايا أو توصيل مجاني
- **الصلاحية**: تنتهي بعد سنة من الحصول عليها

### الجوائز | Rewards
- **التعريف**: هدايا يرسلها الإدارة للمستخدمين
- **الأنواع**: منتجات أو قسائم أو نقاط
- **التسليم**: يحدد المستخدم عنوان التسليم عند استقبال الجائزة

---

## 🗄️ بنية قاعدة البيانات | Database Structure

### 1. جدول المحافظ | point_wallets Table

```sql
CREATE TABLE point_wallets (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL UNIQUE,
  balance INT DEFAULT 0,
  expire_at TIMESTAMP NULL,
  last_earned_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_id (user_id)
);
```

**الحقول | Fields:**
- `balance`: الرصيد الحالي للنقاط
- `expire_at`: تاريخ انتهاء الصلاحية
- `last_earned_at`: آخر وقت تم كسب نقاط فيه

---

### 2. جدول معاملات النقاط | point_transactions Table

```sql
CREATE TABLE point_transactions (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL,
  wallet_id BIGINT NOT NULL,
  rule_id BIGINT,
  created_by_admin_id BIGINT,
  source VARCHAR(50),
  points INT NOT NULL,
  status ENUM('pending', 'earned', 'expired', 'redeemed') DEFAULT 'pending',
  reference_type VARCHAR(50),
  reference_id BIGINT,
  expires_at TIMESTAMP NULL,
  reason TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (wallet_id) REFERENCES point_wallets(id) ON DELETE CASCADE,
  FOREIGN KEY (rule_id) REFERENCES point_rules(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by_admin_id) REFERENCES admins(id) ON DELETE SET NULL,
  INDEX idx_user_id (user_id),
  INDEX idx_status (status),
  INDEX idx_expires_at (expires_at),
  INDEX idx_created_at (created_at)
);
```

**الحقول | Fields:**
- `source`: مصدر النقاط (purchase, review, referral, birthday, etc.)
- `status`: حالة المعاملة (pending, earned, expired, redeemed)
- `reference_type`: نوع المرجع (order, product, etc.)
- `reference_id`: معرف المرجع
- `expires_at`: تاريخ انتهاء الصلاحية

---

### 3. جدول قواعد النقاط | point_rules Table

```sql
CREATE TABLE point_rules (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  rule_type ENUM('purchase', 'review', 'referral', 'birthday', 'profile_completion', 'custom') NOT NULL,
  points_value INT NOT NULL,
  multiplier DECIMAL(5, 2) DEFAULT 1.00,
  is_active BOOLEAN DEFAULT TRUE,
  min_purchase_amount DECIMAL(10, 2),
  max_points_per_transaction INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX idx_rule_type (rule_type),
  INDEX idx_is_active (is_active)
);
```

---

### 4. جدول استبدال النقاط | point_exchanges Table

```sql
CREATE TABLE point_exchanges (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  user_id BIGINT NOT NULL,
  transaction_id BIGINT,
  exchange_type ENUM('coupon', 'free_delivery', 'gift') NOT NULL,
  exchange_data JSON,
  status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
  delivered_at TIMESTAMP NULL,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (transaction_id) REFERENCES point_transactions(id) ON DELETE SET NULL,
  INDEX idx_user_id (user_id),
  INDEX idx_status (status),
  INDEX idx_exchange_type (exchange_type)
);
```

**exchange_data JSON Structure:**
```json
{
  "coupon": {
    "coupon_id": 123,
    "discount_amount": 50,
    "discount_percentage": 10
  },
  "free_delivery": {
    "delivery_zones": ["zone1", "zone2"],
    "expires_at": "2024-12-31"
  },
  "gift": {
    "gift_id": 456,
    "gift_name": "منتج مميز",
    "delivery_address": {
      "full_name": "أحمد محمد",
      "phone": "+966501234567",
      "city": "الرياض",
      "area": "النخيل",
      "street": "شارع الملك فهد",
      "building": "123",
      "apartment": "45"
    }
  }
}
```

---

### 5. جدول الهدايا | user_gifts Table

```sql
CREATE TABLE user_gifts (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  gift_id BIGINT NOT NULL,
  user_id BIGINT NOT NULL,
  address_id BIGINT,
  status ENUM('pending', 'accepted', 'address_pending', 'shipped', 'delivered', 'rejected', 'expired') DEFAULT 'pending',
  admin_notes TEXT,
  user_notes TEXT,
  delivered_at TIMESTAMP NULL,
  deleted_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (gift_id) REFERENCES gifts(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (address_id) REFERENCES user_addresses(id) ON DELETE SET NULL,
  INDEX idx_user_id (user_id),
  INDEX idx_status (status),
  INDEX idx_delivered_at (delivered_at)
);
```

---

## 📦 المودلز | Models

### 1. PointWallet Model

```php
<?php
namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PointWallet extends Model
{
    use LogsActivity;
    
    protected $fillable = [
        'user_id',
        'balance',
        'expire_at',
        'last_earned_at',
    ];

    protected $casts = [
        'expire_at' => 'datetime',
        'last_earned_at' => 'datetime',
    ];

    // العلاقات | Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class, 'wallet_id');
    }

    // الدوال المساعدة | Helper Methods
    public function addPoints(int $points): void
    {
        $this->increment('balance', $points);
        $this->update(['last_earned_at' => now()]);
    }

    public function deductPoints(int $points): bool
    {
        if ($this->balance < $points) {
            return false;
        }
        $this->decrement('balance', $points);
        return true;
    }

    public function getAvailableBalance(): int
    {
        return max(0, $this->balance);
    }
}
```

---

### 2. PointTransaction Model

```php
<?php
namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointTransaction extends Model
{
    use LogsActivity;
    
    protected $fillable = [
        'user_id',
        'wallet_id',
        'rule_id',
        'created_by_admin_id',
        'source',
        'points',
        'status',
        'reference_type',
        'reference_id',
        'expires_at',
        'reason',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    // العلاقات | Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(PointWallet::class, 'wallet_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(PointRule::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    // الدوال المساعدة | Helper Methods
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function getIcon(): string
    {
        return match($this->source) {
            'purchase' => 'shopping-bag',
            'review' => 'star',
            'referral' => 'users',
            'birthday' => 'gift',
            'profile_completion' => 'check-circle',
            default => 'help-circle'
        };
    }

    public function getDescription(): string
    {
        return match($this->source) {
            'purchase' => 'شراء منتج | Product Purchase',
            'review' => 'تقييم منتج | Product Review',
            'referral' => 'إحالة صديق | Referral',
            'birthday' => 'عيد ميلاد | Birthday',
            'profile_completion' => 'إكمال الملف الشخصي | Profile Completion',
            default => 'نقاط | Points'
        };
    }
}
```

---

### 3. PointExchange Model

```php
<?php
namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointExchange extends Model
{
    use LogsActivity;
    
    protected $fillable = [
        'user_id',
        'transaction_id',
        'exchange_type',
        'exchange_data',
        'status',
        'delivered_at',
        'notes',
    ];

    protected $casts = [
        'exchange_data' => 'array',
        'delivered_at' => 'datetime',
    ];

    // العلاقات | Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PointTransaction::class);
    }

    // الدوال المساعدة | Helper Methods
    public function getExchangeDetails(): array
    {
        return match ($this->exchange_type) {
            'coupon' => [
                'type' => 'كوبون خصم | Coupon',
                'coupon_id' => $this->exchange_data['coupon_id'] ?? null,
                'discount_amount' => $this->exchange_data['discount_amount'] ?? null,
            ],
            'free_delivery' => [
                'type' => 'توصيل مجاني | Free Delivery',
                'delivery_zones' => $this->exchange_data['delivery_zones'] ?? [],
                'expires_at' => $this->exchange_data['expires_at'] ?? null,
            ],
            'gift' => [
                'type' => 'هدية | Gift',
                'gift_id' => $this->exchange_data['gift_id'] ?? null,
                'gift_name' => $this->exchange_data['gift_name'] ?? null,
                'delivery_address' => $this->exchange_data['delivery_address'] ?? null,
            ],
            default => ['type' => 'غير محدد | Unknown'],
        };
    }

    public function getIcon(): string
    {
        return match($this->exchange_type) {
            'coupon' => 'ticket',
            'free_delivery' => 'truck',
            'gift' => 'gift',
            default => 'help-circle'
        };
    }
}
```

---

### 4. UserGift Model

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserGift extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'gift_id',
        'user_id',
        'address_id',
        'status',
        'admin_notes',
        'user_notes',
        'delivered_at',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
    ];

    // العلاقات | Relationships
    public function gift(): BelongsTo
    {
        return $this->belongsTo(Gift::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'address_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeAddressPending($query)
    {
        return $query->where('status', 'address_pending');
    }

    // الدوال المساعدة | Helper Methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAddressPending(): bool
    {
        return $this->status === 'address_pending';
    }

    public function accept(): bool
    {
        $this->update(['status' => 'accepted']);
        return true;
    }

    public function reject(): bool
    {
        $this->update(['status' => 'rejected']);
        return true;
    }

    public function setAddress(int $addressId): bool
    {
        $this->update([
            'address_id' => $addressId,
            'status' => 'address_pending'
        ]);
        return true;
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'معلقة | Pending',
            'accepted' => 'مقبولة | Accepted',
            'address_pending' => 'في انتظار العنوان | Address Pending',
            'shipped' => 'مشحونة | Shipped',
            'delivered' => 'مسلمة | Delivered',
            'rejected' => 'مرفوضة | Rejected',
            'expired' => 'منتهية الصلاحية | Expired',
            default => 'غير محدد | Unknown'
        };
    }
}
```

---


## 🔧 الخدمات | Services

### PointService

```php
<?php
namespace App\Services;

use App\Models\PointWallet;
use App\Models\PointTransaction;
use App\Models\PointRule;
use App\Models\PointExchange;
use App\Http\Resources\Point\PointSummaryResource;
use Illuminate\Database\Eloquent\Builder;

class PointService extends BaseService
{
    protected $model = PointTransaction::class;
    protected $resource = PointTransactionResource::class;
    protected $collection = PointTransactionCollection::class;
    protected $pagination = true;

    protected $relations = ['user', 'wallet', 'rule'];
    protected $searchableFields = ['reason'];
    protected $sortableFields = ['id', 'points', 'created_at', 'expires_at'];

    /**
     * الحصول على أو إنشاء محفظة نقاط المستخدم
     * Get or create user's point wallet
     */
    public function getOrCreateWallet(int $userId): PointWallet
    {
        return PointWallet::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0]
        );
    }

    /**
     * منح نقاط للمستخدم
     * Award points to user
     */
    public function awardPoints(
        int $userId,
        int $points,
        string $source,
        ?int $ruleId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $reason = null
    ): PointTransaction {
        $wallet = $this->getOrCreateWallet($userId);
        
        $expiresAt = now()->addYear();
        
        $transaction = PointTransaction::create([
            'user_id' => $userId,
            'wallet_id' => $wallet->id,
            'rule_id' => $ruleId,
            'source' => $source,
            'points' => $points,
            'status' => 'earned',
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'expires_at' => $expiresAt,
            'reason' => $reason,
        ]);

        $this->addPointsToWallet($wallet, $points);

        return $transaction;
    }

    /**
     * إضافة نقاط إلى المحفظة
     * Add points to wallet
     */
    public function addPointsToWallet(PointWallet $wallet, int $points): void
    {
        $wallet->addPoints($points);
    }

    /**
     * خصم نقاط من المحفظة
     * Deduct points from wallet
     */
    public function deductPoints(
        int $userId,
        int $points,
        string $reason = null
    ): ?PointTransaction {
        $wallet = $this->getOrCreateWallet($userId);

        if (!$wallet->deductPoints($points)) {
            return null;
        }

        $transaction = PointTransaction::create([
            'user_id' => $userId,
            'wallet_id' => $wallet->id,
            'source' => 'redemption',
            'points' => -$points,
            'status' => 'redeemed',
            'reason' => $reason,
        ]);

        return $transaction;
    }

    /**
     * الحصول على ملخص نقاط المستخدم
     * Get user points summary
     */
    public function getUserPointsSummary(int $userId): PointSummaryResource
    {
        $wallet = $this->getOrCreateWallet($userId);
        
        $transactions = PointTransaction::where('user_id', $userId)
            ->get();

        $earned = $transactions->where('status', 'earned')->sum('points');
        $redeemed = $transactions->where('status', 'redeemed')->sum('points');
        $expired = $transactions->where('status', 'expired')->sum('points');
        $pending = $transactions->where('status', 'pending')->sum('points');

        return new PointSummaryResource([
            'current_balance' => $wallet->balance,
            'total_earned' => $earned,
            'total_redeemed' => $redeemed,
            'total_expired' => $expired,
            'pending_points' => $pending,
            'last_earned_at' => $wallet->last_earned_at,
        ]);
    }

    /**
     * استبدال النقاط
     * Redeem points
     */
    public function redeemPoints(
        int $userId,
        int $points,
        string $reason = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): ?PointTransaction {
        $transaction = $this->deductPoints($userId, $points, $reason);

        if ($transaction) {
            $transaction->update([
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);
        }

        return $transaction;
    }

    /**
     * انتهاء صلاحية النقاط القديمة
     * Expire old points
     */
    public function expireOldPoints(): int
    {
        $expiredCount = PointTransaction::where('expires_at', '<', now())
            ->where('status', '!=', 'expired')
            ->update(['status' => 'expired']);

        return $expiredCount;
    }

    /**
     * الحصول على معاملات المستخدم
     * Get user transactions
     */
    public function getUserTransactions(
        int $userId,
        int $perPage = 15,
        ?string $status = null,
        array $config = []
    ) {
        $query = PointTransaction::where('user_id', $userId);

        if ($status) {
            $query->where('status', $status);
        }

        return $this->queryBuilder($query, [], $config)
            ->paginate($perPage);
    }

    /**
     * الحصول على عدد المعاملات حسب الحالة
     * Get transactions count by status
     */
    public function getTransactionsCountByStatus(int $userId): array
    {
        $transactions = PointTransaction::where('user_id', $userId)
            ->get()
            ->groupBy('status')
            ->map->count();

        return [
            'pending' => $transactions['pending'] ?? 0,
            'earned' => $transactions['earned'] ?? 0,
            'expired' => $transactions['expired'] ?? 0,
            'redeemed' => $transactions['redeemed'] ?? 0,
        ];
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $query = parent::queryBuilder($query, $filters, $config);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest();
    }
}
```

---

### UserGiftService

```php
<?php
namespace App\Services\User;

use App\Models\UserGift;
use App\Models\Gift;
use App\Services\BaseService;

class UserGiftService extends BaseService
{
    protected $model = UserGift::class;
    protected $resource = UserGiftResource::class;
    protected $collection = UserGiftCollection::class;
    protected $pagination = true;

    protected $relations = ['gift', 'user', 'address'];
    protected $searchableFields = ['gift.name'];
    protected $sortableFields = ['id', 'created_at', 'status'];

    /**
     * الحصول على جوائز المستخدم
     * Get user gifts
     */
    public function getUserGifts(int $userId, ?string $status = null)
    {
        $query = UserGift::where('user_id', $userId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->with($this->relations)
            ->latest()
            ->paginate(15);
    }

    /**
     * قبول الجائزة
     * Accept gift
     */
    public function acceptGift(int $giftId, int $userId): bool
    {
        $gift = UserGift::where('id', $giftId)
            ->where('user_id', $userId)
            ->first();

        if (!$gift || !$gift->isPending()) {
            return false;
        }

        $gift->accept();
        return true;
    }

    /**
     * رفض الجائزة
     * Reject gift
     */
    public function rejectGift(int $giftId, int $userId): bool
    {
        $gift = UserGift::where('id', $giftId)
            ->where('user_id', $userId)
            ->first();

        if (!$gift || !$gift->isPending()) {
            return false;
        }

        $gift->reject();
        return true;
    }

    /**
     * تحديد عنوان التسليم
     * Set delivery address
     */
    public function setDeliveryAddress(int $giftId, int $userId, int $addressId): bool
    {
        $gift = UserGift::where('id', $giftId)
            ->where('user_id', $userId)
            ->first();

        if (!$gift || !$gift->isAddressPending()) {
            return false;
        }

        $gift->setAddress($addressId);
        return true;
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        $query = parent::queryBuilder($query, $filters, $config);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }
}
```

---

## 🎮 الكونترولرز | Controllers

### PointController

```php
<?php
namespace App\Http\Controllers\User\Point;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Requests\User\Point\TransactionFilterRequest;
use App\Http\Requests\User\Point\RedeemPointsRequest;
use App\Services\PointService;

class PointController extends BaseCRUDController
{
    public function __construct(PointService $service)
    {
        $this->service = $service;
        $this->filterRequest = TransactionFilterRequest::class;
    }

    /**
     * الحصول على ملخص النقاط
     * Get points summary
     * GET /api/user/points/summary
     */
    public function summary()
    {
        $userId = auth('user')->id();
        $summary = $this->service->getUserPointsSummary($userId);

        return $this->sendResponse(
            message: 'تم جلب ملخص النقاط بنجاح | Points summary retrieved successfully',
            data: $summary
        );
    }

    /**
     * الحصول على سجل المعاملات
     * Get transactions history
     * GET /api/user/points/transactions
     */
    public function transactions()
    {
        return $this->index(request());
    }

    /**
     * استبدال النقاط
     * Redeem points
     * POST /api/user/points/redeem
     */
    public function redeem(RedeemPointsRequest $request)
    {
        $userId = auth('user')->id();
        
        $transaction = $this->service->redeemPoints(
            $userId,
            $request->points,
            $request->reason,
            $request->reference_type,
            $request->reference_id
        );

        if (!$transaction) {
            return $this->sendError(
                message: 'رصيد النقاط غير كافي | Insufficient points balance',
                code: 400
            );
        }

        return $this->sendResponse(
            message: 'تم استبدال النقاط بنجاح | Points redeemed successfully',
            data: $transaction
        );
    }

    /**
     * الحصول على إحصائيات المعاملات
     * Get transactions statistics
     * GET /api/user/points/statistics
     */
    public function statistics()
    {
        $userId = auth('user')->id();
        $counts = $this->service->getTransactionsCountByStatus($userId);

        return $this->sendResponse(
            data: [
                'transactions_count' => $counts,
                'status_types' => [
                    'pending' => 'معاملات معلقة | Pending transactions',
                    'earned' => 'نقاط مكتسبة | Earned points',
                    'expired' => 'نقاط منتهية | Expired points',
                    'redeemed' => 'نقاط مستبدلة | Redeemed points',
                ]
            ]
        );
    }
}
```

---

### UserGiftController

```php
<?php
namespace App\Http\Controllers\User\UserGift;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserGift\UpdateAddressRequest;
use App\Services\User\UserGiftService;
use App\Http\Resources\UserGift\UserGiftResource;

class UserGiftController extends Controller
{
    public function __construct(private UserGiftService $service)
    {}

    /**
     * الحصول على قائمة الجوائز
     * Get user gifts list
     * GET /api/user/user-gifts
     */
    public function index()
    {
        $userId = auth('user')->id();
        $status = request('status');

        $gifts = $this->service->getUserGifts($userId, $status);

        return $this->sendResponse(
            message: 'تم جلب الجوائز بنجاح | Gifts retrieved successfully',
            data: UserGiftResource::collection($gifts)
        );
    }

    /**
     * الحصول على تفاصيل جائزة
     * Get gift details
     * GET /api/user/user-gifts/{id}
     */
    public function show($id)
    {
        $userId = auth('user')->id();
        $gift = UserGift::where('id', $id)
            ->where('user_id', $userId)
            ->with(['gift', 'address'])
            ->first();

        if (!$gift) {
            return $this->sendError(
                message: 'الجائزة غير موجودة | Gift not found',
                code: 404
            );
        }

        return $this->sendResponse(
            data: new UserGiftResource($gift)
        );
    }

    /**
     * قبول الجائزة
     * Accept gift
     * POST /api/user/user-gifts/{id}/accept
     */
    public function accept($id)
    {
        $userId = auth('user')->id();
        
        if (!$this->service->acceptGift($id, $userId)) {
            return $this->sendError(
                message: 'لا يمكن قبول هذه الجائزة | Cannot accept this gift',
                code: 400
            );
        }

        return $this->sendResponse(
            message: 'تم قبول الجائزة بنجاح | Gift accepted successfully'
        );
    }

    /**
     * رفض الجائزة
     * Reject gift
     * POST /api/user/user-gifts/{id}/reject
     */
    public function reject($id)
    {
        $userId = auth('user')->id();
        
        if (!$this->service->rejectGift($id, $userId)) {
            return $this->sendError(
                message: 'لا يمكن رفض هذه الجائزة | Cannot reject this gift',
                code: 400
            );
        }

        return $this->sendResponse(
            message: 'تم رفض الجائزة | Gift rejected'
        );
    }

    /**
     * تحديث عنوان التسليم
     * Update delivery address
     * PUT /api/user/user-gifts/{id}/address
     */
    public function updateAddress($id, UpdateAddressRequest $request)
    {
        $userId = auth('user')->id();
        
        if (!$this->service->setDeliveryAddress($id, $userId, $request->address_id)) {
            return $this->sendError(
                message: 'لا يمكن تحديث العنوان | Cannot update address',
                code: 400
            );
        }

        return $this->sendResponse(
            message: 'تم تحديث العنوان بنجاح | Address updated successfully'
        );
    }
}
```

---

### ExchangeController

```php
<?php
namespace App\Http\Controllers\User\Point;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Point\ExchangeForCouponRequest;
use App\Http\Requests\User\Point\ExchangeForGiftRequest;
use App\Services\PointService;
use App\Models\PointExchange;

class ExchangeController extends Controller
{
    public function __construct(private PointService $service)
    {}

    /**
     * الحصول على خيارات الاستبدال
     * Get exchange options
     * GET /api/user/points/exchange/options
     */
    public function options()
    {
        return $this->sendResponse(
            data: [
                'options' => [
                    [
                        'id' => 'coupon',
                        'name' => 'كوبون خصم | Coupon',
                        'description' => 'احصل على كوبون خصم | Get a discount coupon',
                        'icon' => 'ticket',
                        'min_points' => 100,
                    ],
                    [
                        'id' => 'free_delivery',
                        'name' => 'توصيل مجاني | Free Delivery',
                        'description' => 'احصل على توصيل مجاني | Get free delivery',
                        'icon' => 'truck',
                        'min_points' => 200,
                    ],
                    [
                        'id' => 'gift',
                        'name' => 'هدية | Gift',
                        'description' => 'استبدل بهدية | Exchange for a gift',
                        'icon' => 'gift',
                        'min_points' => 500,
                    ],
                ]
            ]
        );
    }

    /**
     * استبدال النقاط بكوبون
     * Exchange for coupon
     * POST /api/user/points/exchange/coupon
     */
    public function exchangeForCoupon(ExchangeForCouponRequest $request)
    {
        $userId = auth('user')->id();
        $points = $request->points;

        // التحقق من رصيد النقاط
        $transaction = $this->service->redeemPoints(
            $userId,
            $points,
            'استبدال بكوبون | Coupon exchange'
        );

        if (!$transaction) {
            return $this->sendError(
                message: 'رصيد النقاط غير كافي | Insufficient points',
                code: 400
            );
        }

        // إنشاء كوبون
        $coupon = Coupon::create([
            'code' => 'COUPON-' . uniqid(),
            'discount_amount' => $points / 10, // 100 نقطة = 10 دولار
            'user_id' => $userId,
            'expires_at' => now()->addMonths(3),
        ]);

        // تسجيل الاستبدال
        PointExchange::create([
            'user_id' => $userId,
            'transaction_id' => $transaction->id,
            'exchange_type' => 'coupon',
            'exchange_data' => [
                'coupon_id' => $coupon->id,
                'discount_amount' => $coupon->discount_amount,
            ],
            'status' => 'completed',
        ]);

        return $this->sendResponse(
            message: 'تم الاستبدال بنجاح | Exchange successful',
            data: [
                'coupon_code' => $coupon->code,
                'discount_amount' => $coupon->discount_amount,
                'expires_at' => $coupon->expires_at,
            ]
        );
    }

    /**
     * استبدال النقاط بهدية
     * Exchange for gift
     * POST /api/user/points/exchange/gift
     */
    public function exchangeForGift(ExchangeForGiftRequest $request)
    {
        $userId = auth('user')->id();
        $points = $request->points;
        $giftId = $request->gift_id;

        // التحقق من رصيد النقاط
        $transaction = $this->service->redeemPoints(
            $userId,
            $points,
            'استبدال بهدية | Gift exchange'
        );

        if (!$transaction) {
            return $this->sendError(
                message: 'رصيد النقاط غير كافي | Insufficient points',
                code: 400
            );
        }

        // الحصول على الهدية
        $gift = Gift::find($giftId);
        if (!$gift) {
            return $this->sendError(
                message: 'الهدية غير موجودة | Gift not found',
                code: 404
            );
        }

        // إنشاء جائزة للمستخدم
        $userGift = UserGift::create([
            'gift_id' => $giftId,
            'user_id' => $userId,
            'status' => 'pending',
        ]);

        // تسجيل الاستبدال
        PointExchange::create([
            'user_id' => $userId,
            'transaction_id' => $transaction->id,
            'exchange_type' => 'gift',
            'exchange_data' => [
                'gift_id' => $giftId,
                'gift_name' => $gift->name,
            ],
            'status' => 'completed',
        ]);

        return $this->sendResponse(
            message: 'تم الاستبدال بنجاح | Exchange successful',
            data: [
                'gift_id' => $userGift->id,
                'gift_name' => $gift->name,
                'status' => 'pending',
            ]
        );
    }

    /**
     * الحصول على سجل الاستبدالات
     * Get exchange history
     * GET /api/user/points/exchange/history
     */
    public function history()
    {
        $userId = auth('user')->id();
        
        $exchanges = PointExchange::where('user_id', $userId)
            ->with('transaction')
            ->latest()
            ->paginate(15);

        return $this->sendResponse(
            data: $exchanges
        );
    }

    /**
     * الحصول على الاستبدالات النشطة
     * Get active exchanges
     * GET /api/user/points/exchange/active
     */
    public function activeExchanges()
    {
        $userId = auth('user')->id();
        
        $exchanges = PointExchange::where('user_id', $userId)
            ->where('status', 'completed')
            ->with('transaction')
            ->latest()
            ->get();

        return $this->sendResponse(
            data: $exchanges
        );
    }
}
```

---

## 🛣️ الراوتس | Routes

```php
<?php
// routes/api/user.php

Route::middleware(['auth:user'])->group(function () {
    // ==================== النقاط | Points ====================
    Route::prefix('points')->group(function () {
        // الحصول على ملخص النقاط | Get points summary
        Route::get('/summary', [PointController::class, 'summary']);
        
        // الحصول على سجل المعاملات | Get transactions history
        Route::get('/transactions', [PointController::class, 'transactions']);
        
        // الحصول على إحصائيات | Get statistics
        Route::get('/statistics', [PointController::class, 'statistics']);
        
        // استبدال النقاط | Redeem points
        Route::post('/redeem', [PointController::class, 'redeem']);

        // CRUD operations
        Route::get('/', [PointController::class, 'index']);
        Route::get('/{id}', [PointController::class, 'show']);

        // ==================== خيارات الاستبدال | Exchange Options ====================
        Route::prefix('exchange')->group(function () {
            // الحصول على خيارات الاستبدال | Get exchange options
            Route::get('/options', [ExchangeController::class, 'options']);
            
            // استبدال بكوبون | Exchange for coupon
            Route::post('/coupon', [ExchangeController::class, 'exchangeForCoupon']);
            
            // استبدال بتوصيل مجاني | Exchange for free delivery
            Route::post('/free-delivery', [ExchangeController::class, 'exchangeForFreeDelivery']);
            
            // استبدال بهدية | Exchange for gift
            Route::post('/gift', [ExchangeController::class, 'exchangeForGift']);
            
            // سجل الاستبدالات | Exchange history
            Route::get('/history', [ExchangeController::class, 'history']);
            
            // الاستبدالات النشطة | Active exchanges
            Route::get('/active', [ExchangeController::class, 'activeExchanges']);
        });
    });

    // ==================== الجوائز | User Gifts ====================
    Route::prefix('user-gifts')->group(function () {
        // الحصول على قائمة الجوائز | Get gifts list
        Route::get('/', [UserGiftController::class, 'index']);
        
        // الحصول على تفاصيل جائزة | Get gift details
        Route::get('/{id}', [UserGiftController::class, 'show']);
        
        // قبول الجائزة | Accept gift
        Route::post('/{id}/accept', [UserGiftController::class, 'accept']);
        
        // رفض الجائزة | Reject gift
        Route::post('/{id}/reject', [UserGiftController::class, 'reject']);
        
        // تحديث عنوان التسليم | Update delivery address
        Route::put('/{id}/address', [UserGiftController::class, 'updateAddress']);
    });
});
```

---

## 📦 الموارد | Resources

### PointSummaryResource

```php
<?php
namespace App\Http\Resources\Point;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PointSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'current_balance' => $this['current_balance'],
            'total_earned' => $this['total_earned'],
            'total_redeemed' => $this['total_redeemed'],
            'total_expired' => $this['total_expired'],
            'pending_points' => $this['pending_points'],
            'last_earned_at' => $this['last_earned_at']?->toIso8601String(),
        ];
    }
}
```

### PointTransactionResource

```php
<?php
namespace App\Http\Resources\Point;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PointTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->source,
            'description' => $this->getDescription(),
            'points' => $this->points,
            'date' => $this->created_at->toIso8601String(),
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'reference' => $this->reference_id,
            'expiry_date' => $this->expires_at?->toDateString(),
            'icon' => $this->getIcon(),
            'is_expired' => $this->isExpired(),
        ];
    }

    private function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'معلقة | Pending',
            'earned' => 'مكتسبة | Earned',
            'expired' => 'منتهية | Expired',
            'redeemed' => 'مستبدلة | Redeemed',
            default => 'غير محدد | Unknown'
        };
    }
}
```

### UserGiftResource

```php
<?php
namespace App\Http\Resources\UserGift;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserGiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'gift' => [
                'id' => $this->gift->id,
                'name' => $this->gift->name,
                'description' => $this->gift->description,
                'image' => $this->gift->image_url,
            ],
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'address' => $this->address ? [
                'id' => $this->address->id,
                'full_name' => $this->address->full_name,
                'phone' => $this->address->phone,
                'city' => $this->address->city,
                'area' => $this->address->area,
                'street' => $this->address->street,
            ] : null,
            'received_at' => $this->created_at->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'admin_notes' => $this->admin_notes,
            'user_notes' => $this->user_notes,
        ];
    }
}
```

---

## 📝 الطلبات | Requests

### RedeemPointsRequest

```php
<?php
namespace App\Http\Requests\User\Point;

use Illuminate\Foundation\Http\FormRequest;

class RedeemPointsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'points' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
            'reference_type' => 'nullable|string|in:order,product,coupon',
            'reference_id' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'points.required' => 'النقاط مطلوبة | Points are required',
            'points.integer' => 'النقاط يجب أن تكون رقم | Points must be a number',
            'points.min' => 'النقاط يجب أن تكون أكثر من 0 | Points must be greater than 0',
        ];
    }
}
```

### ExchangeForCouponRequest

```php
<?php
namespace App\Http\Requests\User\Point;

use Illuminate\Foundation\Http\FormRequest;

class ExchangeForCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'points' => 'required|integer|min:100',
        ];
    }

    public function messages(): array
    {
        return [
            'points.min' => 'الحد الأدنى 100 نقطة | Minimum 100 points required',
        ];
    }
}
```

### ExchangeForGiftRequest

```php
<?php
namespace App\Http\Requests\User\Point;

use Illuminate\Foundation\Http\FormRequest;

class ExchangeForGiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'points' => 'required|integer|min:500',
            'gift_id' => 'required|integer|exists:gifts,id',
        ];
    }

    public function messages(): array
    {
        return [
            'points.min' => 'الحد الأدنى 500 نقطة | Minimum 500 points required',
            'gift_id.required' => 'الهدية مطلوبة | Gift is required',
            'gift_id.exists' => 'الهدية غير موجودة | Gift not found',
        ];
    }
}
```

### UpdateAddressRequest

```php
<?php
namespace App\Http\Requests\User\UserGift;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => 'required|integer|exists:user_addresses,id',
        ];
    }

    public function messages(): array
    {
        return [
            'address_id.required' => 'العنوان مطلوب | Address is required',
            'address_id.exists' => 'العنوان غير موجود | Address not found',
        ];
    }
}
```

---

## 🔔 الإشعارات | Notifications

### PointsEarnedNotification

```php
<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Models\PointTransaction;

class PointsEarnedNotification extends Notification
{
    public function __construct(private PointTransaction $transaction)
    {}

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'نقاط جديدة | New Points',
            'message' => "تم إضافة {$this->transaction->points} نقطة إلى حسابك",
            'message_en' => "{$this->transaction->points} points added to your account",
            'type' => 'points_earned',
            'icon' => 'star',
            'color' => 'success',
            'data' => [
                'points' => $this->transaction->points,
                'source' => $this->transaction->source,
                'transaction_id' => $this->transaction->id,
            ]
        ];
    }
}
```

### GiftReceivedNotification

```php
<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Models\UserGift;

class GiftReceivedNotification extends Notification
{
    public function __construct(private UserGift $gift)
    {}

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'جائزة جديدة | New Gift',
            'message' => "تلقيت جائزة جديدة: {$this->gift->gift->name}",
            'message_en' => "You received a new gift: {$this->gift->gift->name}",
            'type' => 'gift_received',
            'icon' => 'gift',
            'color' => 'success',
            'action_url' => "/user-gifts/{$this->gift->id}",
            'data' => [
                'gift_id' => $this->gift->id,
                'gift_name' => $this->gift->gift->name,
            ]
        ];
    }
}
```

---

## 📱 أمثلة الاستخدام | Usage Examples

### مثال 1: الحصول على ملخص النقاط

```javascript
// JavaScript / React
async function getPointsSummary() {
  try {
    const response = await fetch('/api/user/points/summary', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    });
    
    const data = await response.json();
    
    if (data.success) {
      console.log('الرصيد الحالي:', data.data.current_balance);
      console.log('إجمالي المكتسب:', data.data.total_earned);
      console.log('إجمالي المستبدل:', data.data.total_redeemed);
    }
  } catch (error) {
    console.error('خطأ:', error);
  }
}
```

### مثال 2: استبدال النقاط بكوبون

```javascript
async function exchangeForCoupon(points) {
  try {
    const response = await fetch('/api/user/points/exchange/coupon', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ points })
    });
    
    const data = await response.json();
    
    if (data.success) {
      alert(`تم الاستبدال! الكوبون: ${data.data.coupon_code}`);
    } else {
      alert(`خطأ: ${data.message}`);
    }
  } catch (error) {
    console.error('خطأ:', error);
  }
}
```

### مثال 3: قبول جائزة وتحديد العنوان

```javascript
async function acceptGiftAndSetAddress(giftId, addressId) {
  try {
    // الخطوة 1: قبول الجائزة
    const acceptResponse = await fetch(`/api/user/user-gifts/${giftId}/accept`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    });
    
    const acceptData = await acceptResponse.json();
    
    if (acceptData.success) {
      // الخطوة 2: تحديد العنوان
      const addressResponse = await fetch(`/api/user/user-gifts/${giftId}/address`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ address_id: addressId })
      });
      
      const addressData = await addressResponse.json();
      
      if (addressData.success) {
        alert('تم تحديد العنوان بنجاح!');
      }
    }
  } catch (error) {
    console.error('خطأ:', error);
  }
}
```

---

## 🔄 تدفق العمليات | Process Flows

### تدفق كسب النقاط | Points Earning Flow

```
المستخدم يشتري منتج | User purchases product
    ↓
تفعيل حدث الشراء | Trigger purchase event
    ↓
حساب النقاط حسب القاعدة | Calculate points by rule
    ↓
إنشاء معاملة نقاط | Create point transaction
    ↓
إضافة النقاط للمحفظة | Add points to wallet
    ↓
إرسال إشعار | Send notification
    ↓
تحديث الرصيد | Update balance
```

### تدفق استبدال النقاط | Points Redemption Flow

```
المستخدم يختار الاستبدال | User selects redemption
    ↓
التحقق من الرصيد | Verify balance
    ↓
خصم النقاط | Deduct points
    ↓
إنشاء معاملة استبدال | Create exchange transaction
    ↓
إنشاء الكوبون/الهدية | Create coupon/gift
    ↓
إرسال إشعار | Send notification
    ↓
إكمال العملية | Complete process
```

### تدفق استقبال الجائزة | Gift Reception Flow

```
الإدارة تنشئ جائزة | Admin creates gift
    ↓
إرسال إشعار للمستخدم | Send notification to user
    ↓
المستخدم يقبل الجائزة | User accepts gift
    ↓
تحديد عنوان التسليم | Select delivery address
    ↓
إرسال إشعار للإدارة | Notify admin
    ↓
الشحن والتسليم | Shipping & Delivery
    ↓
تحديث الحالة | Update status
```

---

## ⚙️ الإعدادات والتكوين | Configuration

### ملف الإعدادات | Config File

```php
<?php
// config/points.php

return [
    // قواعد النقاط | Points Rules
    'rules' => [
        'purchase' => [
            'points_per_dollar' => 1,
            'min_purchase' => 10,
        ],
        'review' => [
            'points' => 10,
        ],
        'referral' => [
            'points' => 50,
        ],
        'birthday' => [
            'points' => 100,
        ],
        'profile_completion' => [
            'points' => 25,
        ],
    ],

    // صلاحية النقاط | Points Validity
    'validity' => [
        'days' => 365, // سنة واحدة
    ],

    // معدلات الاستبدال | Exchange Rates
    'exchange_rates' => [
        'coupon' => [
            'points_per_dollar' => 10, // 100 نقطة = 10 دولار
        ],
        'free_delivery' => [
            'points' => 200,
        ],
        'gift' => [
            'min_points' => 500,
        ],
    ],
];
```

---

## 🎨 مكونات الواجهة | UI Components

### بطاقة النقاط | Points Card Component

```jsx
// React Component
import React, { useState, useEffect } from 'react';

export function PointsCard() {
  const [points, setPoints] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchPointsSummary();
  }, []);

  const fetchPointsSummary = async () => {
    try {
      const response = await fetch('/api/user/points/summary', {
        headers: { 'Authorization': `Bearer ${token}` }
      });
      const data = await response.json();
      if (data.success) {
        setPoints(data.data);
      }
    } catch (error) {
      console.error('Error:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <div>جاري التحميل...</div>;

  return (
    <div className="points-card">
      <div className="card-header">
        <h2>نقاطي | My Points</h2>
        <span className="info-icon">ℹ️</span>
      </div>

      <div className="points-display">
        <div className="main-balance">
          <span className="label">الرصيد الحالي | Current Balance</span>
          <span className="value">{points?.current_balance}</span>
          <span className="unit">نقطة | Points</span>
        </div>

        <div className="points-breakdown">
          <div className="item">
            <span className="label">مكتسبة | Earned</span>
            <span className="value">{points?.total_earned}</span>
          </div>
          <div className="item">
            <span className="label">مستبدلة | Redeemed</span>
            <span className="value">{points?.total_redeemed}</span>
          </div>
          <div className="item">
            <span className="label">منتهية | Expired</span>
            <span className="value">{points?.total_expired}</span>
          </div>
        </div>
      </div>

      <div className="points-actions">
        <button className="btn-primary" onClick={() => navigateTo('/exchange')}>
          استبدال | Redeem
        </button>
        <button className="btn-secondary" onClick={() => navigateTo('/points-history')}>
          السجل | History
        </button>
      </div>
    </div>
  );
}
```

### بطاقة الجائزة | Gift Card Component

```jsx
export function GiftCard({ gift, onAccept, onReject }) {
  return (
    <div className="gift-card">
      <div className="gift-image">
        <img src={gift.gift.image} alt={gift.gift.name} />
        <span className="badge">جديد | New</span>
      </div>

      <div className="gift-content">
        <h3>{gift.gift.name}</h3>
        <p className="description">{gift.gift.description}</p>

        <div className="gift-meta">
          <span className={`status ${gift.status}`}>
            {getStatusLabel(gift.status)}
          </span>
          <span className="date">
            {new Date(gift.received_at).toLocaleDateString('ar-SA')}
          </span>
        </div>
      </div>

      <div className="gift-actions">
        {gift.status === 'pending' && (
          <>
            <button 
              className="btn-accept"
              onClick={() => onAccept(gift.id)}
            >
              قبول | Accept
            </button>
            <button 
              className="btn-reject"
              onClick={() => onReject(gift.id)}
            >
              رفض | Reject
            </button>
          </>
        )}
        {gift.status === 'accepted' && (
          <button 
            className="btn-primary"
            onClick={() => navigateTo(`/gifts/${gift.id}/address`)}
          >
            تحديد العنوان | Select Address
          </button>
        )}
      </div>
    </div>
  );
}
```

### نموذج تحديد العنوان | Address Selection Form

```jsx
export function AddressSelectionForm({ giftId, onSubmit }) {
  const [addresses, setAddresses] = useState([]);
  const [selectedAddress, setSelectedAddress] = useState(null);
  const [showNewAddress, setShowNewAddress] = useState(false);

  useEffect(() => {
    fetchAddresses();
  }, []);

  const fetchAddresses = async () => {
    const response = await fetch('/api/user/addresses', {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    const data = await response.json();
    setAddresses(data.data);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    const response = await fetch(`/api/user/user-gifts/${giftId}/address`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ address_id: selectedAddress })
    });

    const data = await response.json();
    if (data.success) {
      onSubmit();
    }
  };

  return (
    <form onSubmit={handleSubmit} className="address-form">
      <h3>تحديد عنوان التسليم | Select Delivery Address</h3>

      <div className="address-list">
        {addresses.map(address => (
          <label key={address.id} className="address-option">
            <input
              type="radio"
              name="address"
              value={address.id}
              onChange={(e) => setSelectedAddress(e.target.value)}
            />
            <div className="address-info">
              <p className="name">{address.full_name}</p>
              <p className="details">
                {address.street}, {address.area}, {address.city}
              </p>
            </div>
          </label>
        ))}
      </div>

      <button type="submit" className="btn-primary" disabled={!selectedAddress}>
        تأكيد | Confirm
      </button>
    </form>
  );
}
```

---


## 🧪 الاختبارات | Tests

### PointServiceTest

```php
<?php
namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PointService;
use App\Models\User;
use App\Models\PointWallet;

class PointServiceTest extends TestCase
{
    private PointService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PointService::class);
        $this->user = User::factory()->create();
    }

    public function test_can_award_points()
    {
        $transaction = $this->service->awardPoints(
            $this->user->id,
            100,
            'purchase'
        );

        $this->assertNotNull($transaction);
        $this->assertEquals(100, $transaction->points);
        $this->assertEquals('earned', $transaction->status);

        $wallet = PointWallet::where('user_id', $this->user->id)->first();
        $this->assertEquals(100, $wallet->balance);
    }

    public function test_can_redeem_points()
    {
        // أولاً: منح نقاط
        $this->service->awardPoints($this->user->id, 200, 'purchase');

        // ثانياً: استبدال النقاط
        $transaction = $this->service->redeemPoints(
            $this->user->id,
            100,
            'استبدال بكوبون'
        );

        $this->assertNotNull($transaction);
        $this->assertEquals(-100, $transaction->points);

        $wallet = PointWallet::where('user_id', $this->user->id)->first();
        $this->assertEquals(100, $wallet->balance);
    }

    public function test_cannot_redeem_more_than_balance()
    {
        $this->service->awardPoints($this->user->id, 50, 'purchase');

        $transaction = $this->service->redeemPoints(
            $this->user->id,
            100,
            'استبدال'
        );

        $this->assertNull($transaction);
    }

    public function test_can_get_points_summary()
    {
        $this->service->awardPoints($this->user->id, 100, 'purchase');
        $this->service->awardPoints($this->user->id, 50, 'review');

        $summary = $this->service->getUserPointsSummary($this->user->id);

        $this->assertEquals(150, $summary['current_balance']);
        $this->assertEquals(150, $summary['total_earned']);
    }
}
```

---

## 🔐 الأمان والتحقق | Security & Validation

### نقاط الأمان | Security Points:

1. **التحقق من الرصيد | Balance Verification**
   - التحقق من رصيد النقاط قبل الاستبدال
   - منع الاستبدال المتكرر

2. **التشفير | Encryption**
   - تشفير بيانات العنوان
   - حماية معلومات الجوائز

3. **التسجيل | Logging**
   - تسجيل جميع المعاملات
   - تتبع التغييرات

4. **التحقق من الصلاحية | Validity Check**
   - التحقق من صلاحية النقاط
   - حذف النقاط المنتهية

---

## 📋 قائمة التحقق | Checklist

- [ ] إنشاء جداول قاعدة البيانات
- [ ] إنشاء المودلز
- [ ] إنشاء الخدمات
- [ ] إنشاء الكونترولرز
- [ ] إضافة الراوتس
- [ ] إنشاء الموارد
- [ ] إنشاء الطلبات
- [ ] إنشاء الإشعارات
- [ ] إضافة الاختبارات
- [ ] توثيق الـ API
- [ ] اختبار الواجهة الأمامية
- [ ] نشر الإنتاج

---


**آخر تحديث | Last Updated:** 2024-03-11
**الإصدار | Version:** 1.0.0

