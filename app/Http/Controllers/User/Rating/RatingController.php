<?php

namespace App\Http\Controllers\User\Rating;

use App\Http\Controllers\BaseCRUDController;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Rating\FilterRequest;
use App\Http\Requests\User\Rating\MyRatingsRequest;
use App\Http\Requests\User\Rating\StoreRequest;
use App\Http\Requests\User\Rating\UpdateRequest;
use App\Http\Resources\Rating\RatingWithTargetResource;
use App\Services\User\RatingService;
use Illuminate\Http\Request;

class RatingController extends BaseCRUDController
{
    public function __construct(RatingService $service)
    {
        $this->service = $service;
        $this->filterRequest = FilterRequest::class;
        $this->createRequest = StoreRequest::class;
        $this->updateRequest = UpdateRequest::class;
    }
    public function myRatings(MyRatingsRequest $request)
    {
        $ratings = $this->service->getMyRatings(
            $request->validated()
        );

        return RatingWithTargetResource::collection($ratings);
    }

    /**
     * Check if user can rate a specific product
     */
    public function canRate(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id'
        ]);

        $result = $this->service->canRateProduct($request->product_id);

        return response()->json([
            'status' => true,
            'message' => $result['can_rate']
                ? 'You can rate this product'
                : $result['reason'],
            'data' => $result
        ]);
    }
}
