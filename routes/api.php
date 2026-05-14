<?php
use App\Http\Controllers\Api\WooCommerceController;
use Illuminate\Support\Facades\Route;

Route::prefix('woocommerce')->group(function () {

    Route::get('/products', [WooCommerceController::class, 'index']);

    Route::post('/products', [WooCommerceController::class, 'store']);

    Route::put('/products/{id}', [WooCommerceController::class, 'update']);

    Route::delete('/products/{id}', [WooCommerceController::class, 'destroy']);

});
