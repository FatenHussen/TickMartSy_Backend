<?php

use App\Http\Middleware\CheckIfBlocked;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

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
