<?php

use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\CategoryProductController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StoreFirebaseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Healthy';
});

Route::post('/firebase', [StoreFirebaseController::class, 'create']);
Route::get('/firebase', [StoreFirebaseController::class, 'list']);

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verification', [AuthController::class, 'verifications']);
    Route::post('/resend-otp/{phone}', [AuthController::class, 'resendOTP']);
    Route::post('/phone-verify', [AuthController::class, 'phoneVerify']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/resend-otp-reset-password/{phone}', [AuthController::class, 'resendOtpforgotpassword']);
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:api');
});

Route::group(['namespace' => 'App\Http\Controllers'], function ($route) {
    Route::group(['prefix' => 'admin'], function ($route) {
        $route->group(['prefix' => 'master'], function ($route) {
            $route->apiResource('bank', BankController::class);
            $route->group(['prefix' => 'bank'], function ($route) {
                $route->get('/all', [BankController::class, 'all']);
                $route->get('/set_status/{bank}', [BankController::class, 'set_status']);
            });

            $route->group(['prefix' => 'blog/category'], function ($route) {
                $route->get('/', [BlogCategoryController::class, 'list']);
                $route->get('/all', [BlogCategoryController::class, 'all']);
                $route->get('/{blogCategory:id}', [BlogCategoryController::class, 'show']);
                $route->post('/', [BlogCategoryController::class, 'create']);
                $route->put('/{blogCategory:id}', [BlogCategoryController::class, 'update']);
                $route->delete('/{blogCategory:id}', [BlogCategoryController::class, 'delete']);
            });

            $route->group(['prefix' => 'product/category'], function ($route) {
                $route->get('/', [CategoryProductController::class, 'list']);
                $route->get('/sequence', [CategoryProductController::class, 'list']);
                $route->get('/all', [CategoryProductController::class, 'all']);
                $route->get('/{categoryProduct:slug}', [CategoryProductController::class, 'show']);
                $route->post('/', [CategoryProductController::class, 'create']);
                $route->put('/{categoryProduct:id}', [CategoryProductController::class, 'update']);
                $route->delete('/{categoryProduct:id}', [CategoryProductController::class, 'destroy']);
            });

            $route->group(['prefix' => 'product'], function ($route) {
                $route->get('/', [ProductController::class, 'list']);
                $route->get('/all', [ProductController::class, 'all']);
                $route->get('/{blogCategory:id}', [ProductController::class, 'show']);
                $route->post('/', [ProductController::class, 'create']);
                $route->put('/{blogCategory:id}', [ProductController::class, 'update']);
                $route->delete('/{blogCategory:id}', [ProductController::class, 'delete']);
            });
        });
    });
});
