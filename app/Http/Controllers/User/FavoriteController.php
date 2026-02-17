<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Favorite\FilterRequest;
use App\Http\Requests\User\Favorite\StoreRequest;
use Illuminate\Http\Request;
use App\Services\User\FavoriteService;

class FavoriteController extends Controller
{
    protected FavoriteService $service;

    public function __construct(FavoriteService $service)
    {
        $this->service = $service;
    }


    public function toggle(StoreRequest $request)
    {
        $userId = auth('user')->id() ?? 1;

        $isFavorite = $this->service->toggle($userId, $request->type, $request->id);


        return $this->sendResponse(data: [
            'message'     => $isFavorite ? 'Added to favorites' : 'Removed from favorites',
            'is_favorite' => $isFavorite,
        ]);
    }

    public function index(FilterRequest $request)
    {

        $userId = auth('user')->id() ?? 1;

        $data = $this->service->list($userId, $request->type);

        return $this->sendResponse(data: $data);
    }
}
