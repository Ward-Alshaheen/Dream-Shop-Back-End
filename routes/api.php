<?php


use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\PasswordController;
use App\Http\Controllers\User\UserControllers;
use Illuminate\Support\Facades\Route;

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
Route::group([
    'prefix' => 'auth',
], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('sendPasswordResetCode', [PasswordController::class, 'sendPasswordResetCode']);
    Route::post('checkPasswordResetCode', [PasswordController::class, 'checkPasswordResetCode']);

    Route::group([
        'middleware' => ['auth.user:api'],
    ], function () {
        Route::post('checkRegisterCode', [AuthController::class, 'checkRegisterCode']);
        Route::get('sendRegisterCode', [AuthController::class, 'sendRegisterCode'])->middleware(['account_confirmation']);
        Route::post('passwordReset', [PasswordController::class, 'passwordReset'])->middleware(['reset.password']);
        Route::get('logout', [AuthController::class, 'logout']);
        Route::get('refresh', [UserControllers::class, 'refresh']);
        Route::get('me', [UserControllers::class, 'me']);
        Route::delete('deleteImage', [UserControllers::class, 'deleteImage']);
        Route::post('updateUser', [UserControllers::class, 'updateUser']);
        Route::post('changePassword', [UserControllers::class, 'changePassword']);
    });
});
Route::group([
    'middleware'=>['auth.user:api','account_product','password_confirmation'],
], function () {
    Route::get('products/{id}', [ProductController::class, 'show']);
    Route::post('products', [ProductController::class, 'store']);
    Route::get('products', [ProductController::class, 'index']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);
});
