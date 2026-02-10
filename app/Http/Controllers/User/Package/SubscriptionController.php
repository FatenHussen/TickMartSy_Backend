<?php

namespace App\Http\Controllers\User\Package;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Package\RenewSubscribeRequest;
use App\Http\Requests\User\Package\SubscribeRequest;
use App\Http\Resources\User\Package\PackageResource;
use App\Http\Resources\User\Package\SubscriptionResource;
use App\Models\Package;
use App\Services\User\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionService $service
    ) {}

    public function packages()
    {
        $packages = Package::where('is_active', true)->get();
        return PackageResource::collection($packages);
    }
    public function subscribe(SubscribeRequest $request)
    {
        $package = Package::findOrFail($request->package_id);

        $subscription = $this->service->subscribe(
            auth('user')->user(),
            $package
        );

        return $this->sendResponse();
    }

    public function mySubscription()
    {
        $subscription = auth('user')->user()->subscription;

        if (!$subscription) {
            return response()->json([
                'message' => 'No active subscription'
            ], 404);
        }

        return $this->sendResponse(data: new SubscriptionResource($subscription));
    }

    public function renew(RenewSubscribeRequest $request)
    {
        $package = Package::findOrFail($request->package_id);

        $subscription = $this->service->subscribe(
            auth('user')->user(),
            $package,
            true 
        );

        return $this->sendResponse();
    }
}
