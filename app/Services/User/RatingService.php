<?php

namespace App\Services\User;

use App\Enums\RateableType;
use App\Models\Rating;
use App\Services\BaseService;
use Illuminate\Support\Facades\Log;

class RatingService extends BaseService
{
    public function __construct()
    {
        $this->model = Rating::class;

        $this->relations = [
            'user',
            'rateable',
        ];

        $this->searchableFields = [
            'comment',
        ];

        $this->sortableFields = [
            'id',
            'rating',
            'created_at',
        ];

        $this->resource   = \App\Http\Resources\Rating\OneResource::class;
        $this->collection = \App\Http\Resources\Rating\AllResource::class;

        $this->singleImages = ['image'];
    }


    public function queryBuilder($query, $filters = [], $config = [])
    {
        // handle rateable_type (enum → class)
        if (!empty($filters['rateable_type'])) {
            $query->where(
                'rateable_type',
                $this->resolveRateableType($filters['rateable_type'])
            );

            unset($filters['rateable_type']);
        }

        return parent::queryBuilder($query, $filters, $config);
    }
    /**
     * Override create logic
     */
    public function create($data)
    {
        if (empty($data['type'])) {
            abort(422, __('custom.ratings.rateable_type_required'));
        }

        $data['user_id'] = auth('user')->id();
        $data['is_verified'] = true;

        $data['rateable_type'] = $this->resolveRateableType($data['type']);
        unset($data['type']);

        $rating = parent::create($data);

        // Award product review points
        try {
            $pointService = app(\App\Services\PointService::class);
            $pointService->awardPoints(
                userId: $data['user_id'],
                ruleCode: 'product_review',
                referenceType: 'rating',
                referenceId: $rating->id
            );
        } catch (\Throwable $e) {
            Log::error('Failed to award review points', [
                'user_id' => $data['user_id'],
                'rating_id' => $rating->id,
                'error' => $e->getMessage()
            ]);
        }

        return true;
    }


    /**
     * Only owner can update within 24 hours
     */
    public function update($id, array $data)
    {
        $rating = Rating::findOrFail($id);

        if ($rating->user_id !== auth('user')->id()) {
            throw new \App\Exceptions\CustomExceptionWithMessage('custom.ratings.update_own_only');
        }

        if ($rating->created_at->diffInHours(now()) > 24) {
            throw new \App\Exceptions\CustomExceptionWithMessage('custom.ratings.update_within_24_hours');
        }

        parent::update($id, $data);
        return true;
    }

    /**
     * Only owner can delete within 24 hours
     */
    public function delete($id): bool
    {
        $rating = Rating::findOrFail($id);

        if ($rating->user_id !== auth('user')->id()) {
            throw new \App\Exceptions\CustomExceptionWithMessage('custom.ratings.delete_own_only');
        }

        if ($rating->created_at->diffInHours(now()) > 24) {
            throw new \App\Exceptions\CustomExceptionWithMessage('custom.ratings.delete_within_24_hours');
        }

        return parent::delete($id);
    }

    private function resolveRateableType(string $type): string
    {
        return match ($type) {
            RateableType::PRODUCT->value => \App\Models\Product::class,
            RateableType::BRAND->value => \App\Models\Brand::class,
            RateableType::SHOP->value => \App\Models\Shop::class,
            RateableType::DELIVERY->value => \App\Models\Driver::class,
            RateableType::RECIPE->value => \App\Models\Recipe::class,
            RateableType::BASKET->value => \App\Models\Basket::class,
            RateableType::SCHEDULED_BASKET->value => \App\Models\BasketSchedule::class,
            RateableType::ORDER->value => \App\Models\Order::class,
            default => abort(422, __('custom.ratings.invalid_rateable_type')),
        };
    }
    public function getMyRatings(array $filters = [])
    {
        $query = $this->model::with($this->relations)
            ->where('user_id', auth('user')->id());

        if (!empty($filters['type'])) {
            $query->where('rateable_type', $this->resolveRateableType($filters['type']));
        }

        if (!empty($filters['rateable_id'])) {
            $query->where('rateable_id', $filters['rateable_id']);
        }

        return $query->latest()->get();
    }

    /**
     * Check if user can rate a specific product
     * User can rate only if they have ordered any variant of this product in a delivered order
     */
    public function canRateProduct(int $productId): array
    {
        $userId = auth('user')->id();

        // Check if product exists
        $product = \App\Models\Product::find($productId);
        if (!$product) {
            return [
                'can_rate' => false,
                'reason' => 'Product not found',
                'reason_ar' => 'المنتج غير موجود'
            ];
        }

        // Check if user has ordered any variant of this product in a delivered order
        // ShopProductVariant -> ProductVariant -> Product
        $hasOrdered = \App\Models\OrderItem::whereHas('order', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->where('status', \App\Enums\OrderStatus::DELIVERED->value);
        })
        ->whereHas('shopProductVariant.productVariant', function ($query) use ($productId) {
            $query->where('product_id', $productId);
        })
        ->exists();

        if (!$hasOrdered) {
            return [
                'can_rate' => false,
                'reason' => 'You must purchase this product before rating it',
                'reason_ar' => 'يجب عليك شراء هذا المنتج قبل تقييمه'
            ];
        }

        // Check if user already rated this product
        $alreadyRated = Rating::where('user_id', $userId)
            ->where('rateable_type', \App\Models\Product::class)
            ->where('rateable_id', $productId)
            ->exists();

        if ($alreadyRated) {
            return [
                'can_rate' => false,
                'reason' => 'You have already rated this product',
                'reason_ar' => 'لقد قمت بتقييم هذا المنتج مسبقاً'
            ];
        }

        return [
            'can_rate' => true,
            'product_id' => $productId
        ];
    }
}
