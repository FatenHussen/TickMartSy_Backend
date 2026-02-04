<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AffiliateWalletTransaction;
use App\Models\AffiliateWithdrawRequest;

class AffiliateWalletController extends Controller
{
    /**
     * 1️⃣ عرض المحفظة + العمليات
     */
    public function wallet(Request $request)
    {
        $affiliate = auth('user')->user();

        // كل العمليات المالية للمسوّق
        $transactions = AffiliateWalletTransaction::where('affiliate_id', $affiliate->affiliate_id)
            ->latest()
            ->paginate($request->get('per_page', 10));

        // حساب الرصيد
        $totalCommission = AffiliateWalletTransaction::where('affiliate_id', $affiliate->affiliate_id)
            ->where('type', 'commission')
            ->where('status', 'completed')
            ->sum('amount');

        $totalWithdrawn = AffiliateWalletTransaction::where('affiliate_id', $affiliate->affiliate_id)
            ->where('type', 'withdraw')
            ->where('status', 'completed')
            ->sum(fn($t) => abs($t->amount));

        $availableBalance = $totalCommission - $totalWithdrawn;

        return response()->json([
            'affiliate' => [
                'affiliate_id' => $affiliate->affiliate_id,
                'rate' => $affiliate->affiliate_rate,
                'coupon_id' => $affiliate->coupon_id,
            ],
            'balance' => [
                'total_commission' => round($totalCommission, 2),
                'withdrawn' => round($totalWithdrawn, 2),
                'available' => round($availableBalance, 2),
            ],
            'transactions' => [
                'items' => $transactions->items(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ]
            ]
        ]);
    }

    /**
     * 2️⃣ تقديم طلب سحب
     */
    public function requestWithdraw(Request $request)
    {
        $affiliate = auth('user')->user();

        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        // حساب الرصيد المتاح
        $totalCommission = AffiliateWalletTransaction::where('affiliate_id', $affiliate->affiliate_id)
            ->where('type', 'commission')
            ->where('status', 'completed')
            ->sum('amount');

        $totalWithdrawn = AffiliateWalletTransaction::where('affiliate_id', $affiliate->affiliate_id)
            ->where('type', 'withdraw')
            ->where('status', 'completed')
            ->sum(fn($t) => abs($t->amount));

        $availableBalance = $totalCommission - $totalWithdrawn;

        if ($request->amount > $availableBalance) {
            return response()->json(['message' => 'Amount exceeds available balance'], 400);
        }

        // إنشاء طلب السحب
        $withdrawRequest = AffiliateWithdrawRequest::create([
            'affiliate_id' => $affiliate->affiliate_id,
            'amount' => $request->amount,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Withdraw request submitted successfully',
            'withdraw_request' => $withdrawRequest,
        ]);
    }

    /**
     * 3️⃣ عرض طلبات السحب السابقة
     */
    public function withdrawRequests(Request $request)
    {
        $affiliate = auth('user')->user();

        $requests = AffiliateWithdrawRequest::where('affiliate_id', $affiliate->affiliate_id)
            ->latest()
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'items' => $requests->items(),
            'pagination' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
        ]);
    }
}
