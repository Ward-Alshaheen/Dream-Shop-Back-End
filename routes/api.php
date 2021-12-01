<?php


use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\PasswordController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'prefix' => 'auth',
], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('sandPasswordResetCode', [PasswordController::class, 'sandPasswordResetCode']);
    Route::post('checkPasswordResetCode', [PasswordController::class, 'checkPasswordResetCode']);

    Route::group([
        'middleware' => ['auth.user:api'],
    ], function () {
        Route::post('checkRegisterCode', [AuthController::class, 'checkRegisterCode']);
        Route::get('sandRegisterCode', [AuthController::class, 'sandRegisterCode'])->middleware(['account_confirmation']);
        Route::post('passwordReset', [PasswordController::class, 'passwordReset'])->middleware(['reset.password']);
        Route::get('logout', [AuthController::class, 'logout']);
        Route::get('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
    });
});
