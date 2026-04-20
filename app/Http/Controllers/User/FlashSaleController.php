<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\FlashSale\ActiveResource;
use App\Models\FlashSale;

class FlashSaleController extends Controller
{
    public function active()
    {
        $flashSale = FlashSale::query()
            ->active()
            ->latest('id')
            ->first();

        return $this->sendResponse(
            data: $flashSale ? new ActiveResource($flashSale) : null
        );
    }
}
