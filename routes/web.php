<?php

use App\Http\Controllers\VendorFcmTokenController;
use App\Http\Middleware\CheckIfBlocked;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\VendorFcmToken;
Route::get('/', function () {
    return view('welcome');
})->middleware([CheckIfBlocked::class, SetLocale::class]);

Route::middleware([CheckIfBlocked::class])->group(function () {});
// Route::group(['prefix' => 'translations', 'middleware' => ['web', 'auth']], function () {
//     \Barryvdh\TranslationManager\Controller::routes();
// });


Route::get('/cache', function () {
    Artisan::call('optimize:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('route:clear');
    Artisan::call('route:cache');
    Artisan::call('view:cache');
    return '✅ Laravel caches rebuilt successfully!';
});
Route::post('/vendor/fcm-token',[VendorFcmTokenController::class, 'store']);
//  function (Request $request) {

//     $user = Auth::guard('vendor-user')->user();

//     if (!$user) {
//         return response()->json(['error' => 'Unauthenticated'], 401);
//     }

//     VendorFcmToken::updateOrCreate(
//         [
//             'vendor_user_id' => $user->id,
//             'fcm_token' => $request->token
//         ],
//         [
//             'device_type' => 'web'
//         ]
//     );

//     return response()->json(['success' => true]);
// });