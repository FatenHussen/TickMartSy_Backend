<?php

namespace App\Services\Admin;

use App\Exceptions\CustomExceptionWithMessage;
use App\Helpers\SendFCMNotification;
use App\Http\Resources\EndUser\AllResource;
use App\Http\Resources\EndUser\OneResource;
use App\Models\Coupon;
use App\Models\User;
use App\Models\Vendor;
use App\Services\Base\NotificationService;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserService extends BaseService
{
    public function __construct(User $model)
    {
        $this->model      = $model;
        $this->resource   = OneResource::class;
        $this->collection = AllResource::class;
        $this->pagination = true;
        $this->relations = ['area', 'addresses'];
        $this->searchableFields = ['id', 'name', 'code'];
    }

    public function create($data)
    {
        if ($data['affiliate_id'] && $data['affiliate_rate']) {
            $data['is_affiliate'] = true;
            $data['affiliate_approved'] = true;
        }
        parent::create($data);
    }

    public function update($id, array $data)
    {
        $object = $this->model::findOrFail($id);

        $hasAffiliateData =
            array_key_exists('affiliate_id', $data) ||
            array_key_exists('affiliate_rate', $data);

        if ($hasAffiliateData) {
            Log::info("hasAffiliateData");

            if (!$object->is_affiliate) {
                throw new CustomExceptionWithMessage('المستخدم غير مقدم على طلب مسوّق');
            }

            if (
                array_key_exists('affiliate_id', $data) &&
                $object->affiliate_id &&
                $object->affiliate_id != $data['affiliate_id']
            ) {
                throw new CustomExceptionWithMessage('لا يمكن تغيير رقم المسوّق');
            }

            // approve automatically if rate exists
            if (array_key_exists('affiliate_rate', $data)) {
                Log::info("affiliate_approved");
                $data['affiliate_approved'] = true;
                (new NotificationService)->send(
                    $this->model,
                    'قبول طلبك ك مسوّق',
                    'تم قبول طلبك ك مسوق من قبل الادمن ',
                    [
                        'type' => 'markter'
                    ]
                );
            }
        }

        return parent::update($id, $data);
    }

    public function markters()
    {
        $users = User::where('affiliate_approved', true)
            ->select('id', 'affiliate_id', 'name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->affiliate_id,
                    'label' => $user->id . '-' . $user->affiliate_id . '-' . $user->name,
                ];
            });

        return $users;
    }
}
