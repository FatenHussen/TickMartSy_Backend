<?php

namespace App\Http\Controllers\User\UserGift;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserGift\UpdateAddressRequest;
use App\Services\User\UserGiftService;
use Illuminate\Http\Request;

class UserGiftController extends Controller
{
    protected $service;

    public function __construct(UserGiftService $service)
    {
        $this->service = $service;
    }

    /**
     * Get all user gifts
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        $filters = ['user_id' => $userId];

        if ($request->has('status')) {
            $filters['status'] = $request->status;
        }

        $config = [
            'page' => (int) $request->input('page', 1),
            'per_page' => (int) $request->input('per_page', 10),
        ];

        $res = $this->service->getAll($filters, $config);
        return $this->sendResponse(data: $res);
    }

    /**
     * Get single user gift
     */
    public function show($id)
    {
        $res = $this->service->getOne($id, auth()->id());
        return $this->sendResponse(data: $res);
    }

    /**
     * Update address for gift delivery
     */
    public function updateAddress(UpdateAddressRequest $request, $id)
    {
        $data = $request->validated();
        $res = $this->service->updateAddress($id, $data, auth()->id());
        return $this->sendResponse(data: $res);
    }
}
