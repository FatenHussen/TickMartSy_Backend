<?php

namespace App\Http\Controllers\Driver;

use App\Models\Order;
use App\Models\OrderTracking;
use Illuminate\Http\Request;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Events\DriverLocationUpdated;

class DriverTrackingController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'lat'      => 'required|numeric',
            'lng'      => 'required|numeric',
        ]);

        $driverId = auth('driver')->id();

        $order = Order::findOrFail($request->order_id);

        // ✅ تأكد أنه طلبه
        if ($order->driver_id !== $driverId) {
            return response()->json(['message' => 'Not your order'], 403);
        }

        // ✅ لازم يكون OUT_DELIVERY
        if ($order->status !== OrderStatus::OUT_DELIVERY->value) {
            return response()->json(['message' => 'Tracking not active'], 400);
        }

        OrderTracking::updateOrCreate(
            ['order_id' => $order->id],
            [
                'driver_id' => $driverId,
                'lat'       => $request->lat,
                'lng'       => $request->lng,
            ]
        );

        broadcast(new DriverLocationUpdated(
            $order->id,
            $request->lat,
            $request->lng
        ));


        return $this->sendResponse();
    }
}
