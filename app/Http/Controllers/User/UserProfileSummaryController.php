<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\User\UserProfileSummaryService;

class UserProfileSummaryController extends Controller
{
    public function __construct(private UserProfileSummaryService $service) {}

    public function __invoke()
    {
        $user = auth('user')->user();

        return $this->sendResponse(data: $this->service->query($user->id));
    }
}
