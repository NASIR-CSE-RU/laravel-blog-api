<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/posts', [PostController::class, 'store'])->middleware('auth:api');
Route::get('/posts/{post}/comments', [CommentController::class, 'index'])->middleware('auth:api');
Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->middleware('auth:api');
Route::post('/reactions/toggle', [ReactionController::class, 'toggle'])->middleware('auth:api');
Route::get('/feeds', [FeedController::class, 'index'])->middleware('auth:api');

Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working',
        'status' => 'ok',
    ]);
});
