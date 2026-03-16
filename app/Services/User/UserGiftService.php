<?php

namespace App\Services\User;

use App\Http\Resources\User\UserGift\AllResource;
use App\Http\Resources\User\UserGift\OneResource;
use App\Models\UserGift;
use App\Services\BaseService;
use App\Exceptions\NotFoundException;
use App\Exceptions\CustomExceptionWithMessage;

class UserGiftService extends BaseService
{
    protected $model = UserGift::class;
    protected $resource = OneResource::class;
    protected $collection = AllResource::class;

    protected $relations = ['gift', 'address.city', 'address.area'];

    protected $searchableFields = ['id'];

    protected $sortableFields = ['id', 'created_at', 'status'];

    protected $pagination = true;

    /**
     * Get one user gift (ensure it belongs to the user)
     */
    public function getOne($id, $userId = null)
    {
        $query = $this->model::with($this->relations);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $object = $query->findOrFail($id);

        return new $this->resource($object);
    }

    /**
     * Update address for gift delivery
     */
    public function updateAddress($id, array $data, $userId)
    {
        $userGift = $this->model::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Only allow address update if status is pending
        if ($userGift->status !== 'pending') {
            throw new CustomExceptionWithMessage('custom.gifts.address_update_not_allowed');
        }

        $userGift->update([
            'address_id' => $data['address_id'],
            'user_notes' => $data['user_notes'] ?? null,
        ]);

        return new $this->resource($userGift->fresh($this->relations));
    }

    public function queryBuilder($query, $filters = [], $config = [])
    {
        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
            unset($filters['status']);
        }

        // Filter by user_id
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
            unset($filters['user_id']);
        }

        return parent::queryBuilder($query, $filters, $config);
    }
}
