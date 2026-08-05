<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $notifications = $user->notifications()->take(50)->get()->map(function ($notif) {
            return [
                'id' => $notif->id,
                'title' => $notif->data['title'] ?? 'Notifikasi',
                'message' => $notif->data['message'] ?? '',
                'url' => $notif->data['url'] ?? '',
                'pdf_url' => $notif->data['pdf_url'] ?? null,
                'type' => $notif->data['type'] ?? 'info',
                'read_at' => $notif->read_at ? $notif->read_at->toISOString() : null,
                'created_at' => $notif->created_at->toISOString(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'notifications' => $notifications,
                'unread_count' => $user->unreadNotifications()->count(),
            ]
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
            return response()->json([
                'status' => 'success',
                'message' => 'Notifikasi ditandai telah dibaca.'
            ]);
        }
        
        return response()->json([
            'status' => 'error',
            'message' => 'Notifikasi tidak ditemukan.'
        ], 404);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Semua notifikasi ditandai telah dibaca.'
        ]);
    }
}
