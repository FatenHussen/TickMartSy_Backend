<?php

use App\Http\Middleware\CheckIfBlocked;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->middleware([CheckIfBlocked::class, SetLocale::class]);

Route::middleware([CheckIfBlocked::class])->group(function () {});
// Route::group(['prefix' => 'translations', 'middleware' => ['web', 'auth']], function () {
//     \Barryvdh\TranslationManager\Controller::routes();
// });
