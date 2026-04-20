<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Promotion\AllResource;
use App\Models\Promotion;

class PromotionController extends Controller
{
    public function index()
    {
        $promotions = Promotion::query()
            ->active()
            ->orderBy('id')
            ->get();

        return $this->sendResponse(data: AllResource::collection($promotions));
    }
}
