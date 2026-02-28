<?php

use App\Http\Controllers\Base\SocketController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/authorize', [SocketController::class, 'authorize']);
    Route::get('/user', [SocketController::class, 'getUser']);
});
