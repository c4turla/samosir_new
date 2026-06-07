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
        
        // Broadcasting Auth (Sanctum) - Manual channel authorization
        Route::post('/broadcasting/auth', function (Request $request) {
            $user = $request->user();
            if (!$user) {
                \Log::warning('Broadcasting auth: No authenticated user');
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $channelName = str_replace('private-', '', $request->input('channel_name', ''));
            $socketId = $request->input('socket_id');

            \Log::info('Broadcasting auth attempt', [
                'user_id' => $user->id,
                'channel_name' => $channelName,
                'socket_id' => $socketId,
            ]);

            // Authorize based on channel pattern
            $authorized = false;

            // User notification channel: App.Models.User.{id}
            if (preg_match('/^App\.Models\.User\.(\d+)$/', $channelName, $matches)) {
                $authorized = (int) $user->id === (int) $matches[1];
            }
            // Chat conversation channel: chat.{conversation_id}
            elseif (preg_match('/^chat\.(\d+)$/', $channelName, $matches)) {
                $authorized = $user->conversations()->where('conversations.id', $matches[1])->exists();
            }

            if (!$authorized) {
                \Log::warning('Broadcasting auth denied', [
                    'user_id' => $user->id,
                    'channel' => $channelName,
                ]);
                return response()->json(['error' => 'Forbidden'], 403);
            }

            // Generate Pusher auth signature
            $pusherKey = config('broadcasting.connections.reverb.key') ?: env('REVERB_APP_KEY');
            $pusherSecret = config('broadcasting.connections.reverb.secret') ?: env('REVERB_APP_SECRET');

            $stringToSign = $socketId . ':' . 'private-' . $channelName;
            $signature = hash_hmac('sha256', $stringToSign, $pusherSecret);

            \Log::info('Broadcasting auth granted', [
                'user_id' => $user->id,
                'channel' => $channelName,
            ]);

            return response()->json([
                'auth' => $pusherKey . ':' . $signature,
            ]);
        });

                // Profile
        Route::post('/profile/update', [AuthController::class, 'updateProfile']);
        Route::post('/profile/signature', [AuthController::class, 'updateSignature']);
        Route::post('/profile/change-password', [AuthController::class, 'changePassword']);

        // Notifications
        Route::get('/notifications', [App\Http\Controllers\Api\V1\NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [App\Http\Controllers\Api\V1\NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [App\Http\Controllers\Api\V1\NotificationController::class, 'markAllAsRead']);

        // Schedules
        Route::get('/schedules', [App\Http\Controllers\Api\V1\ScheduleController::class, 'index']);

        // Landing Sites & Syahbandars
        Route::get('/landing-sites', [App\Http\Controllers\Api\V1\LandingSiteController::class, 'index']);
        Route::get('/syahbandars', [App\Http\Controllers\Api\V1\SyahbandarController::class, 'index']);

        // Arrivals
        Route::middleware('ensure.docs')->group(function () {
            Route::get('/arrivals', [App\Http\Controllers\Api\V1\ArrivalController::class, 'index']);
            Route::post('/arrivals', [App\Http\Controllers\Api\V1\ArrivalController::class, 'store']);
            Route::get('/arrivals/{id}', [App\Http\Controllers\Api\V1\ArrivalController::class, 'show']);
        });

        // Departures
        Route::middleware('ensure.docs')->group(function () {
            Route::get('/departures', [App\Http\Controllers\Api\V1\DepartureController::class, 'index']);
            Route::post('/departures', [App\Http\Controllers\Api\V1\DepartureController::class, 'store']);
            Route::get('/departures/{id}', [App\Http\Controllers\Api\V1\DepartureController::class, 'show']);
        });

        // SPR Departures
        Route::get('/spr-departures', [App\Http\Controllers\Api\V1\SprDepartureController::class, 'index']);
        Route::middleware('ensure.docs')->group(function () {
            Route::post('/spr-departures', [App\Http\Controllers\Api\V1\SprDepartureController::class, 'store']);
        });

        // Vessels
        Route::get('/vessels/available', [App\Http\Controllers\Api\V1\VesselController::class, 'available']);
        Route::get('/vessels/summary', [App\Http\Controllers\Api\V1\VesselController::class, 'summary']);
        Route::get('/vessels/my-vessels', [App\Http\Controllers\Api\V1\VesselController::class, 'myVessels']);
        Route::get('/vessels/{id}', [App\Http\Controllers\Api\V1\VesselController::class, 'show']);
        Route::get('/vessels/{vesselId}/managers', [App\Http\Controllers\Api\V1\VesselController::class, 'vesselManagers']);

        Route::middleware('ensure.docs')->group(function () {
            Route::post('/vessels/register-manager', [App\Http\Controllers\Api\V1\VesselController::class, 'registerManager']);
            Route::put('/vessels/update-manager', [App\Http\Controllers\Api\V1\VesselController::class, 'updateManager']);
            Route::delete('/vessels/unregister-manager', [App\Http\Controllers\Api\V1\VesselController::class, 'unregisterManager']);
        });

        // Services
        Route::get('/services', [App\Http\Controllers\Api\V1\ServiceController::class, 'index']);
        Route::middleware('ensure.docs')->group(function () {
            Route::post('/services/water', [App\Http\Controllers\Api\V1\ServiceController::class, 'storeWater']);
            Route::post('/services/equipment', [App\Http\Controllers\Api\V1\ServiceController::class, 'storeEquipment']);
            Route::post('/services/ice-cruiser', [App\Http\Controllers\Api\V1\ServiceController::class, 'storeIceCruiser']);
        });

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
