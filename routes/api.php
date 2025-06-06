<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\ContentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\LikeDislikeController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\ReviewContentController;
use App\Http\Controllers\StreamController;
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

    // Category routes
    Route::get('categories', [ContentController::class, 'getAllCategories']);
    Route::get('contents/{id}/categories', [ContentController::class, 'getContentCategories']);
    Route::post('contents/{id}/categories', [ContentController::class, 'updateContentCategories']);

    Route::get('content/{id}', [ContentController::class, 'getContent']);
    Route::get('content/{id}/related', [ContentController::class, 'getRelatedContents']);
    Route::post('content/{id}/watch-time', [ContentController::class, 'reportWatchTime']);
    // Route::post('content/{id}/reaction', [ContentController::class, 'setReaction']);
    Route::get('contents/search', [ContentController::class, 'search']);
    Route::get('contents/details/{id}', [ContentController::class, 'getContentDetails']);
    Route::apiResource('contents', ContentController::class);

    Route::get('feedbacks/check', [FeedbackController::class, 'checkUserFeedback']);
    Route::apiResource('feedbacks', FeedbackController::class);

    Route::get('complaints/my-complaints', [ComplaintController::class, 'myComplaints']);
    Route::get('complaints/categories', [ComplaintController::class, 'categoriesComplaints']);
    Route::apiResource('complaints', ComplaintController::class);

    Route::get('/favorite/check/{id}', [FavoriteController::class, 'isFavorite']);
    Route::post('/favorite/toggle/{id}', [FavoriteController::class, 'toggleFavorite']);
    Route::get('/favorite', [FavoriteController::class, 'index']);

    Route::get('/like-dislike/check/{contentId}', [LikeDislikeController::class, 'checkStatus']);
    Route::post('/like-dislike/{contentId}', [LikeDislikeController::class, 'setReaction']);
    Route::delete('/like-dislike/{contentId}', [LikeDislikeController::class, 'remove']);

    Route::get('reviews/check/{contentId}', [ReviewContentController::class, 'checkUserReview']);
    Route::post('reviews', [ReviewContentController::class, 'store']);
});


Route::group(['prefix' => 'stream'], function() {
    Route::get('/now-playing', [StreamController::class, 'nowPlaying']);
    Route::get('/{content}/playlist', [StreamController::class, 'playlist']);
    Route::post('/{content}/start', [StreamController::class, 'startStream']);
    Route::post('/{content}/stop', [StreamController::class, 'stopStream'])->middleware('auth:sanctum');
});

Route::get('/stream/test-now-playing', [StreamController::class, 'testNowPlaying']);

Route::get('/test-ffmpeg', function() {
    $ffmpeg = env('FFMPEG_PATH');
    $command = "$ffmpeg -version";
    exec($command, $output, $return);
    
    return [
        'path' => $ffmpeg,
        'output' => $output,
        'return_code' => $return
    ];
});