<?php

namespace App\Http\Controllers\User;

use App\Enums\OrderStatus;
use App\Http\Controllers\BaseCRUDController;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Address\StoreRequest;
use App\Http\Requests\User\Address\UpdateRequest;
use App\Http\Resources\Order\AllResource;
use App\Models\Order;
use App\Services\User\AddressService;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    public function statistics()
    {
        $affiliate = auth('user')->user();

        $orders = Order::where('affiliate_id', $affiliate->affiliate_id)
            ->where('status', OrderStatus::DELIVERED->value)
            ->get();

        $totalOrdersCount = $orders->count();

        $totalCommission = $orders->sum('affiliate_commission');

        // $totalWithdrawn = AffiliatePayout::where('affiliate_id', $affiliate->affiliate_id)
        //     ->where('status', 'paid')
        //     ->sum('amount');
        $totalWithdrawn = 0;

        // 4️⃣ الباقي
        $remainingBalance = $totalCommission - $totalWithdrawn;

        return response()->json([
            'affiliate' => [
                'affiliate_id' => $affiliate->affiliate_id,
                'coupon_code'  => $affiliate->coupon?->code,
                'rate'         => $affiliate->affiliate_rate,
            ],

            'stats' => [
                'orders_count'     => $totalOrdersCount,
                'total_commission' => round($totalCommission, 2),
                'withdrawn'        => round($totalWithdrawn, 2),
                'remaining'        => round($remainingBalance, 2),
            ],

        ]);


        return $this->sendResponse(
            data: $data,
            message: 'Driver statistics retrieved successfully'
        );
    }

    public function orders(Request $request)
    {
        $affiliate = auth('user')->user();

        $perPage = $request->get('per_page', 10);

        $result = Order::where('affiliate_id', $affiliate->affiliate_id)
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'items' => AllResource::collection($result->items()),
            'pagination' => [
                'current_page' => $result->currentPage(),
                'last_page'    => $result->lastPage(),
                'per_page'     => $result->perPage(),
                'total'        => $result->total(),
            ],
        ]);
    }
}
