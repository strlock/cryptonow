<?php

use App\Http\Controllers\OrdersController;
use App\Http\Controllers\PriceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarketDeltaController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::middleware('api')->group(function () {
    Route::post('login', [UserController::class, 'login']);
    Route::get('marketDelta/{symbol}/{fromTime}/{toTime?}/{interval?}', [MarketDeltaController::class, 'index']);
    Route::get('price/{symbol}/{fromTime}/{toTime?}/{interval?}', [PriceController::class, 'index']);
    Route::middleware('jwt.verify:api')->group(function () {
        Route::resource('orders', OrdersController::class);
    });
});
