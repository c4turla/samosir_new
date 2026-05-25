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

        // Vessels
        Route::get('/vessels/available', [App\Http\Controllers\Api\V1\VesselController::class, 'available']);
        Route::get('/vessels/summary', [App\Http\Controllers\Api\V1\VesselController::class, 'summary']);
        Route::get('/vessels/my-vessels', [App\Http\Controllers\Api\V1\VesselController::class, 'myVessels']);
        Route::get('/vessels/{id}', [App\Http\Controllers\Api\V1\VesselController::class, 'show']);
        Route::post('/vessels/register-manager', [App\Http\Controllers\Api\V1\VesselController::class, 'registerManager']);
        Route::put('/vessels/update-manager', [App\Http\Controllers\Api\V1\VesselController::class, 'updateManager']);
        Route::delete('/vessels/unregister-manager', [App\Http\Controllers\Api\V1\VesselController::class, 'unregisterManager']);
        Route::get('/vessels/{vesselId}/managers', [App\Http\Controllers\Api\V1\VesselController::class, 'vesselManagers']);

        // Fish Commodities
        Route::get('/fish', [App\Http\Controllers\Api\V1\FishController::class, 'index']);

        // Chat
        Route::get('/chat/conversations', [App\Http\Controllers\Api\V1\ChatController::class, 'conversations']);
        Route::post('/chat/conversations/get', [App\Http\Controllers\Api\V1\ChatController::class, 'getOrCreateConversation']);
        Route::delete('/chat/conversations/{conversation}', [App\Http\Controllers\Api\V1\ChatController::class, 'deleteConversation']);
        Route::get('/chat/conversations/{conversation}/messages', [App\Http\Controllers\Api\V1\ChatController::class, 'messages']);
        Route::post('/chat/messages', [App\Http\Controllers\Api\V1\ChatController::class, 'sendMessage']);
        Route::delete('/chat/messages/{message}', [App\Http\Controllers\Api\V1\ChatController::class, 'deleteMessage']);
        Route::put('/chat/messages/{message}', [App\Http\Controllers\Api\V1\ChatController::class, 'updateMessage']);
        Route::get('/chat/contacts', [App\Http\Controllers\Api\V1\ChatController::class, 'contacts']);
    });
});
