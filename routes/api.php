<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\Auth\AuthController;
use App\Http\Controllers\Api\v1\SupplierController;

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
});

Route::middleware('auth:api')->group(function () {
    Route::get('suppliers', [SupplierController::class, 'index']);
    Route::get('suppliers/{id}', [SupplierController::class, 'show']);
    Route::post('suppliers', [SupplierController::class, 'store']);
    Route::match(['put', 'patch'], 'suppliers/{id}', [SupplierController::class, 'update']);
    Route::delete('suppliers/{id}', [SupplierController::class, 'destroy']);
});
