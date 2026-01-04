<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\FeedItemController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Feeds
    Route::get('/feeds', [FeedController::class, 'index']);
    Route::post('/feeds', [FeedController::class, 'store']);
    Route::put('/feeds/{feed}', [FeedController::class, 'update']);
    Route::delete('/feeds/{feed}', [FeedController::class, 'destroy']);
    Route::post('/feeds/{feed}/refresh', [FeedController::class, 'refresh']);

    // Feed Items
    Route::get('/items', [FeedItemController::class, 'index']);
    Route::get('/items/{feedItem}', [FeedItemController::class, 'show']);
    Route::post('/items/{feedItem}/read', [FeedItemController::class, 'markAsRead']);
    Route::post('/items/{feedItem}/unread', [FeedItemController::class, 'markAsUnread']);
    Route::post('/items/{feedItem}/star', [FeedItemController::class, 'toggleStar']);
    Route::post('/items/mark-all-read', [FeedItemController::class, 'markAllAsRead']);
});
