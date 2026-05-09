<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public API
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working',
        'status' => 'ok',
    ]);
});

// Protected API
Route::middleware('auth:api')->group(function (): void {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts/{post}/comments', [CommentController::class, 'index']);
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
    Route::post('/reactions/toggle', [ReactionController::class, 'toggle']);
    Route::get('/feeds', [FeedController::class, 'index']);
});
