<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\ContentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/profile', [AuthController::class, 'update']);
    Route::put('/profile/change-password', [AuthController::class, 'changePassword']);
    Route::delete('/profile', [AuthController::class, 'delete']);

    Route::apiResource('categories', CategoryController::class);
    
    Route::apiResource('contents', ContentController::class);

    Route::get('feedbacks/check', [FeedbackController::class, 'checkUserFeedback']);
    Route::apiResource('feedbacks', FeedbackController::class);

    Route::get('complaints/my-complaints', [ComplaintController::class, 'myComplaints']);
    Route::apiResource('complaints', ComplaintController::class);

    Route::apiResource('favorites', FavoriteController::class)->only(['index', 'store', 'destroy']);
});