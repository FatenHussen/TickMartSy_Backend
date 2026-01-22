<?php

namespace App\Services\User;

use App\Enums\RateableType;
use App\Models\Rating;
use App\Services\BaseService;

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
            abort(422, 'Rateable type is required');
        }

        $data['user_id'] = auth('user')->id();
        $data['is_verified'] = true;

        $data['rateable_type'] = $this->resolveRateableType($data['type']);
        unset($data['type']);
        parent::create($data);
        return true;
    }


    /**
     * Only owner can update
     */
    public function update($id, array $data)
    {
        parent::update($id, $data);
        return true;
    }

    /**
     * Only owner can delete
     */
    public function delete($id): bool
    {
        $rating = Rating::findOrFail($id);

        abort_if($rating->user_id !== auth('user')->id(), 403);

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
            default => abort(422, 'Invalid rateable type'),
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
}
