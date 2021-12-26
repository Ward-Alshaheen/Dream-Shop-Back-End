<?php


use App\Http\Controllers\Product\CommentController;
use App\Http\Controllers\Product\LikeController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\ViewController;
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
    'prefix'=>'products',
    'middleware'=>['auth.user:api','account_product','password_confirmation'],

], function () {
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::post('/', [ProductController::class, 'store']);
    Route::get('/', [ProductController::class, 'index']);
    Route::delete('/{id}', [ProductController::class, 'destroy'])->name('delete');
    Route::post('/category',[ProductController::class,'showCategory']);
    Route::post('/{id}', [ProductController::class, 'update']);
    Route::get('view/{id}',[ViewController::class,'view']);
    Route::get('my/products',[ProductController::class,'myProduct']);
    Route::get('like/{id}',[LikeController::class,'like']);
    Route::get('my/products/like',[LikeController::class,'myProductLike']);
    Route::get('user/{id}',[ProductController::class,'productUser']);
    Route::post('search/name', [ProductController::class, 'searchName']);
    Route::post('search/date', [ProductController::class, 'searchDate']);

    Route::group([
        'prefix'=>'comments',

    ], function () {
        Route::post('/{id}',[CommentController::class,'add']);
        Route::get('/{id}',[CommentController::class,'all']);
        Route::delete('/{id}',[CommentController::class,'delete']);
        Route::put('/{id}',[CommentController::class,'update']);
    });
});

