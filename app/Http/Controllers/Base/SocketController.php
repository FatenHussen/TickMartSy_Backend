<?php

namespace App\Http\Controllers\Base;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SocketController extends Controller
{
    /**
     * Authorize socket connection for order tracking
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authorize(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'action' => 'required|in:send_location,join'
        ]);

        $orderId = $request->input('order_id');
        $action = $request->input('action');

        // Try to authenticate from different guards
        $user = $this->getAuthenticatedUser();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Not authorized'
            ], 403);
        }

        // Determine user role based on model type
        $role = $this->getUserRole($user);

        // Get the order
        $order = Order::find($orderId);

        // Authorization logic based on role
        $authorized = $this->checkAuthorization($user, $role, $order, $action);

        if (!$authorized) {
            return response()->json([
                'status' => false,
                'message' => 'Not authorized'
            ], 403);
        }

        return response()->json([
            'status' => true,
            'user_id' => $user->id,
            'role' => $role,
            'order_id' => $orderId
        ], 200);
    }

    /**
     * Get authenticated user information
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser(Request $request)
    {
        $user = $this->getAuthenticatedUser();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Not authorized'
            ], 401);
        }

        $role = $this->getUserRole($user);

        return response()->json([
            'status' => true,
            'user_id' => $user->id,
            'role' => $role,
            'name' => $user->name
        ], 200);
    }

    /**
     * Get authenticated user from any guard
     *
     * @return mixed
     */
    private function getAuthenticatedUser()
    {
        // Try admin guard
        if ($user = Auth::guard('admin')->user()) {
            return $user;
        }

        // Try driver guard
        if ($user = Auth::guard('driver')->user()) {
            return $user;
        }

        // Try user guard
        if ($user = Auth::guard('user')->user()) {
            return $user;
        }

        return null;
    }

    /**
     * Determine user role based on model type
     *
     * @param mixed $user
     * @return string
     */
    private function getUserRole($user): string
    {
        $modelClass = get_class($user);

        return match ($modelClass) {
            'App\Models\Admin' => 'admin',
            'App\Models\Driver' => 'driver',
            'App\Models\User' => 'user',
            default => 'unknown'
        };
    }

    /**
     * Check if user is authorized for the action
     *
     * @param mixed $user
     * @param string $role
     * @param Order $order
     * @param string $action
     * @return bool
     */
    private function checkAuthorization($user, string $role, Order $order, string $action): bool
    {
        // Admin: always authorized
        if ($role === 'admin') {
            return true;
        }

        // Driver: authorized if they are assigned to this order
        if ($role === 'driver') {
            return $order->driver_id == $user->id;
        }

        // User: only authorized for 'join' action and if they own the order
        if ($role === 'user') {
            if ($action === 'send_location') {
                return false;
            }
            return $order->user_id == $user->id;
        }

        return false;
    }
}
