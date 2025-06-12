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
use App\Http\Controllers\TrainController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VotingController;

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
    Route::get('/contents/{content}/playlist.m3u8', [ContentController::class, 'getHlsPlaylist'])
    ->name('contents.hls.playlist');

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

// // Voting routes
// Route::prefix('voting')->group(function () {
//     Route::get('/active', [VotingController::class, 'getActiveVoting']);
//     Route::post('/vote', [VotingController::class, 'submitVote']);
//     Route::get('/{voting}/results', [VotingController::class, 'getResults']);
//     Route::get('/{voting}/winner', [VotingController::class, 'getWinnerAndSchedule']);
    
//     // Admin routes (bisa ditambah middleware auth)
//     Route::post('/create', [VotingController::class, 'createVoting']);
// });

// // Update existing stream routes
// Route::prefix('stream')->group(function () {
//     Route::get('/now-playing', [StreamController::class, 'nowPlaying']);
//     Route::get('/next', [StreamController::class, 'getNextContent']);
//     Route::get('/status', [StreamController::class, 'getStreamStatus']);
//     Route::post('/auto-start', [StreamController::class, 'autoStartVotedContent']);
    
//     Route::get('/{content}/playlist', [StreamController::class, 'playlist']);
//     Route::post('/{content}/start', [StreamController::class, 'startStream']);
//     Route::post('/{content}/stop', [StreamController::class, 'stopStream']);
//     Route::get('/{content}/sync', [StreamController::class, 'syncData']);
// });
// Voting routes
Route::prefix('voting')->group(function () {
    // Mendapatkan voting aktif (untuk ditampilkan di client)
    // Route::get('/active', [VotingController::class, 'getActiveVoting']);
    Route::get('/carriages/{carriageId}/voting', [VotingController::class, 'getVotingForCarriage']);

    // Mengirim vote (untuk user)
    Route::post('/vote', [VotingController::class, 'submitVote']);

    // Mendapatkan hasil voting untuk voting spesifik (mungkin setelah berakhir)
    Route::get('/{voting}/results', [VotingController::class, 'getResults']);

    // Ini adalah endpoint yang seharusnya dipanggil oleh **backend scheduler**
    // BUKAN oleh frontend/client secara langsung.
    // Frontend tidak perlu tahu tentang logic penjadwalan pemenang.
    // Jika Anda tetap ingin ini bisa diakses, pertimbangkan middleware otentikasi admin yang ketat.
    Route::post('/{voting}/end-and-schedule-winner', [VotingController::class, 'endVotingAndScheduleWinner']);

    // Admin route untuk membuat voting baru
    // Harusnya dilindungi dengan middleware otentikasi admin
    Route::post('/create', [VotingController::class, 'createVoting']);
});

// Stream routes
Route::prefix('stream')->group(function () {
    // Mendapatkan status stream saat ini (live, next scheduled, voting info)
    // Route::get('/status/{carriage}', [StreamController::class, 'getStreamStatus']);
    Route::get('/status', [StreamController::class, 'getStatusForLocation']);

    Route::get('/now-playing', [StreamController::class, 'nowPlaying']);

    Route::get('/next', [StreamController::class, 'getNextContent']);

    Route::get('/{content}/playlist', [StreamController::class, 'playlist']);

    Route::post('/{content}/start', [StreamController::class, 'startStream']);

    Route::post('/{content}/stop', [StreamController::class, 'stopStream']);

    Route::get('/{content}/sync', [StreamController::class, 'syncData']);

    Route::post('/manage-transitions', [StreamController::class, 'manageStreamTransitions']);
});

Route::get('/trains', [TrainController::class, 'index']);

Route::get('/contents/{content}/playlist.m3u8', [ContentController::class, 'getHlsPlaylist'])
    ->name('contents.hls.playlist');

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