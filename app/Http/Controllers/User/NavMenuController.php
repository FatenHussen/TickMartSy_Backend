<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\NavMenuResource;
use App\Services\User\NavMenuService;

class NavMenuController extends Controller
{
    public function __construct(private NavMenuService $service) {}

    public function index()
    {
        $items = $this->service->getMenuForUser();

        return $this->sendResponse(data: NavMenuResource::collection($items));
    }
}
