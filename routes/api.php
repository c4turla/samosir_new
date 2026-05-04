<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Profile
        Route::post('/profile/update', [AuthController::class, 'updateProfile']);
        Route::post('/profile/change-password', [AuthController::class, 'changePassword']);

        // Arrivals
        Route::get('/arrivals', [App\Http\Controllers\Api\V1\ArrivalController::class, 'index']);
        Route::get('/arrivals/{id}', [App\Http\Controllers\Api\V1\ArrivalController::class, 'show']);

        // Departures
        Route::get('/departures', [App\Http\Controllers\Api\V1\DepartureController::class, 'index']);
        Route::get('/departures/{id}', [App\Http\Controllers\Api\V1\DepartureController::class, 'show']);

        // Fish Commodities
        Route::get('/fish', [App\Http\Controllers\Api\V1\FishController::class, 'index']);

        // Chat
        Route::get('/chat/conversations', [App\Http\Controllers\Api\V1\ChatController::class, 'conversations']);
        Route::get('/chat/conversations/{conversation}/messages', [App\Http\Controllers\Api\V1\ChatController::class, 'messages']);
        Route::post('/chat/messages', [App\Http\Controllers\Api\V1\ChatController::class, 'sendMessage']);
        Route::get('/chat/contacts', [App\Http\Controllers\Api\V1\ChatController::class, 'contacts']);
    });
});
