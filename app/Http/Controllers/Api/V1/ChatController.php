<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Get all conversations for the authenticated user.
     */
    public function conversations(Request $request)
    {
        $conversations = $request->user()->conversations()
            ->with(['participants' => function ($query) {
                $query->select('users.id', 'users.name', 'users.role', 'users.photo');
            }, 'latestMessage.sender'])
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($conversation) use ($request) {
                $participant = $conversation->participants
                    ->where('id', '!=', $request->user()->id)
                    ->first();

                return [
                    'id' => $conversation->id,
                    'type' => $conversation->type,
                    'participant' => $participant ? [
                        'id' => $participant->id,
                        'name' => $participant->name,
                        'role' => $participant->role,
                        'photo' => $participant->photo ? asset('storage/' . $participant->photo) : null,
                    ] : null,
                    'latest_message' => $conversation->latestMessage ? [
                        'body' => $conversation->latestMessage->body,
                        'sender_id' => $conversation->latestMessage->sender_id,
                        'sender_name' => $conversation->latestMessage->sender->name ?? null,
                        'created_at' => $conversation->latestMessage->created_at,
                    ] : null,
                    'last_message_at' => $conversation->last_message_at,
                    'read_at' => $conversation->pivot->read_at,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $conversations
        ]);
    }

    /**
     * Get messages for a specific conversation.
     */
    public function messages(Request $request, Conversation $conversation)
    {
        // Pastikan user adalah participant
        if (!$conversation->participants->contains($request->user()->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses ke percakapan ini.'
            ], 403);
        }

        $messages = $conversation->messages()
            ->with(['sender:id,name,role,photo'])
            ->orderBy('created_at', 'asc')
            ->paginate($request->get('per_page', 50));

        // Mark conversation as read
        $conversation->participants()->updateExistingPivot($request->user()->id, [
            'read_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $messages
        ]);
    }

    /**
     * Send a new message.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'nullable|exists:conversations,id',
            'receiver_id' => 'nullable|exists:users,id',
            'message' => 'required|string|max:5000',
        ]);

        $userId = $request->user()->id;
        $conversationId = $request->conversation_id;

        // Jika belum ada conversation, buat baru (private chat)
        if (!$conversationId && $request->receiver_id) {
            $receiverId = $request->receiver_id;

            // Cari conversation private yang sudah ada
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

        if (!$conversationId) {
            return response()->json([
                'status' => 'error',
                'message' => 'conversation_id atau receiver_id harus diisi.'
            ], 422);
        }

        // Pastikan user adalah participant
        $conversation = Conversation::findOrFail($conversationId);
        if (!$conversation->participants->contains($userId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses ke percakapan ini.'
            ], 403);
        }

        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => $userId,
            'body' => $request->message,
            'type' => 'text',
        ]);

        // Update timestamp conversation
        $conversation->update(['last_message_at' => now()]);

        // Broadcast event (akan diterima oleh web maupun mobile)
        broadcast(new MessageSent($message->load('sender')))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Pesan terhasil dikirim.',
            'data' => [
                'id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'sender_id' => $message->sender_id,
                'body' => $message->body,
                'type' => $message->type,
                'created_at' => $message->created_at,
                'sender' => [
                    'id' => $message->sender->id,
                    'name' => $message->sender->name,
                ],
            ]
        ], 201);
    }

    /**
     * Get list of users available to chat with.
     * Semua user bisa chat ke siapa saja (bebas).
     */
    public function contacts(Request $request)
    {
        $user = $request->user();
        $contacts = User::where('id', '!=', $user->id)
            ->where('is_active', true)
            ->get(['id', 'name', 'role', 'photo'])
            ->map(function ($contact) {
                return [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'role' => $contact->role,
                    'photo' => $contact->photo ? asset('storage/' . $contact->photo) : null,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $contacts
        ]);
    }
}
