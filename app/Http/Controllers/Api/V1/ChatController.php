<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Events\MessageDeleted;
use App\Events\MessageSent;
use App\Events\MessageUpdated;
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
            'message' => 'nullable|string',
            'file' => 'nullable|file|max:10240', // 10 MB max
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

        // Handle file upload
        $fileUrl  = null;
        $fileName = null;
        $fileType = null;
        $msgType  = 'text';

        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $fileUrl  = $file->store('chat', 'public');
            $fileName = $file->getClientOriginalName();
            $fileType = $file->getMimeType();
            $msgType  = str_starts_with($fileType, 'image/') ? 'image' : 'file';
        }

        $message = Message::create([
            'conversation_id' => $conversationId,
            'sender_id' => $userId,
            'body' => $request->message ?? '',
            'type' => $msgType,
            'file_url' => $fileUrl,
            'file_name' => $fileName,
            'file_type' => $fileType,
        ]);

        // Update timestamp conversation
        $conversation->update(['last_message_at' => now()]);

        // Broadcast event (akan diterima oleh web maupun mobile)
        broadcast(new MessageSent($message->load('sender')))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Pesan berhasil dikirim.',
            'data' => $message->load('sender')
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

    /**
     * Find or create a conversation between users without sending a message.
     */
    public function getOrCreateConversation(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
        ]);

        $userId = $request->user()->id;
        $receiverId = $request->receiver_id;

        // Find existing private conversation
        $conversation = Conversation::where('type', 'private')
            ->whereHas('participants', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->whereHas('participants', function ($query) use ($receiverId) {
                $query->where('users.id', $receiverId);
            })
            ->with(['participants', 'latestMessage'])
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create(['type' => 'private']);
            $conversation->participants()->attach([$userId, $receiverId]);
            $conversation->load(['participants', 'latestMessage']);
        }

        return response()->json([
            'status' => 'success',
            'data' => $conversation
        ]);
    }

    /**
     * Delete a conversation (only if user is a participant).
     */
    public function deleteConversation(Request $request, Conversation $conversation)
    {
        // Ensure user is a participant
        if (!$conversation->participants->contains($request->user()->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses ke percakapan ini.'
            ], 403);
        }

        $conversation->delete(); // cascade deletes messages

        return response()->json([
            'status' => 'success',
            'message' => 'Conversation deleted'
        ]);
    }

    /**
     * Delete a single message (soft delete - only sender can delete).
     */
    public function deleteMessage(Request $request, Message $message)
    {
        // Ensure user is the sender
        if ($message->sender_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak berhak menghapus pesan ini.'
            ], 403);
        }

        // Soft delete: mark as deleted instead of actually removing
        $message->update([
            'is_deleted' => true,
            'body' => 'Pesan ini telah dihapus',
        ]);

        broadcast(new MessageDeleted($message->load('sender')))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Pesan berhasil dihapus',
            'data' => $message->load('sender')
        ]);
    }

    /**
     * Update/edit a message body (only sender can edit).
     */
    public function updateMessage(Request $request, Message $message)
    {
        $request->validate([
            'body' => 'required|string|max:10000',
        ]);

        // Ensure user is the sender
        if ($message->sender_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak berhak mengubah pesan ini.'
            ], 403);
        }

        // Ensure message is not deleted
        if ($message->is_deleted) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak dapat mengubah pesan yang telah dihapus.'
            ], 400);
        }

        $message->update([
            'body' => $request->body,
            'is_edited' => true,
        ]);

        broadcast(new MessageUpdated($message->load('sender')))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Pesan berhasil diubah',
            'data' => $message->load('sender')
        ]);
    }
}
