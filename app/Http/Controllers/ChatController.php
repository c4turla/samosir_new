<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ChatController extends Controller
{
    /**
     * Get all conversations for the current user.
     */
    public function getConversations()
    {
        return Auth::user()->conversations()
            ->with(['participants', 'latestMessage'])
            ->orderBy('last_message_at', 'desc')
            ->get();
    }

    /**
     * Get messages for a specific conversation.
     */
    public function getMessages(Conversation $conversation)
    {
        // Ensure user is part of conversation
        if (!$conversation->participants->contains(Auth::id())) {
            abort(403);
        }

        return $conversation->messages()
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Send a new message.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'nullable|exists:conversations,id',
            'receiver_id' => 'nullable|exists:users,id',
            'message' => 'required|string',
        ]);

        $conversationId = $request->conversation_id;

        // If no conversation_id, find or create private conversation
        if (!$conversationId && $request->receiver_id) {
            $receiverId = $request->receiver_id;
            $userId = Auth::id();

            // Find existing private conversation
            $conversation = Conversation::where('type', 'private')
                ->whereHas('participants', function ($query) use ($userId) {
                    $query->where('users.id', $userId);
                })
                ->whereHas('participants', function ($query) use ($receiverId) {
                    $query->where('users.id', $receiverId);
                })
                ->first();

            if (!$conversation) {
                $conversation = Conversation::create(['type' => 'private']);
                $conversation->participants()->attach([$userId, $receiverId]);
            }
            $conversationId = $conversation->id;
        }

        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => Auth::id(),
            'body' => $request->message,
            'type' => 'text',
        ]);

        // Update conversation timestamp
        Conversation::find($conversationId)->update([
            'last_message_at' => now()
        ]);

        // Broadcast event
        broadcast(new MessageSent($message->load('sender')))->toOthers();

        return $message->load('sender');
    }

    /**
     * Get list of users to start chat with.
     */
    public function getUsers()
    {
        $query = User::where('id', '!=', Auth::id());
        
        // Filter based on role if needed (e.g. masyarakat can only see staff)
        // For now, allow all as per user request "bebas"
        
        return $query->get(['id', 'name', 'role', 'photo']);
    }
}
