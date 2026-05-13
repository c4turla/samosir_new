<?php

namespace App\Http\Controllers;

use App\Events\MessageDeleted;
use App\Events\MessageSent;
use App\Events\MessageUpdated;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ChatController extends Controller
{
    /**
     * Display the chat interface.
     */
    public function index()
    {
        return Inertia::render('Chat/Index', [
            'initialConversations' => $this->getConversations()
        ]);
    }

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
            'receiver_id'     => 'nullable|exists:users,id',
            'message'         => 'nullable|string',
            'file'            => 'nullable|file|max:10240', // 10 MB max
        ]);

        $conversationId = $request->conversation_id;

        // If no conversation_id, find or create private conversation
        if (!$conversationId && $request->receiver_id) {
            $receiverId = $request->receiver_id;
            $userId = Auth::id();

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
            'sender_id'       => Auth::id(),
            'body'            => $request->message ?? '',
            'type'            => $msgType,
            'file_url'        => $fileUrl,
            'file_name'       => $fileName,
            'file_type'       => $fileType,
        ]);

        Conversation::find($conversationId)->update([
            'last_message_at' => now()
        ]);

        broadcast(new MessageSent($message->load('sender')))->toOthers();

        return $message->load('sender');
    }

    /**
     * Find or create a conversation between users without sending a message.
     */
    public function getOrCreateConversation(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
        ]);

        $userId = Auth::id();
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

        return $conversation;
    }

    /**
     * Get list of users to start chat with.
     */
    public function getUsers()
    {
        $query = User::where('id', '!=', Auth::id());
        
        return $query->get(['id', 'name', 'role', 'photo']);
    }

    /**
     * Delete a conversation (only if user is a participant).
     */
    public function deleteConversation(Conversation $conversation)
    {
        // Ensure user is a participant
        if (!$conversation->participants->contains(Auth::id())) {
            abort(403);
        }

        $conversation->delete(); // cascade deletes messages

        return response()->json(['message' => 'Conversation deleted']);
    }

    /**
     * Delete a single message (soft delete - only sender can delete).
     */
    public function deleteMessage(Message $message)
    {
        // Ensure user is the sender
        if ($message->sender_id !== Auth::id()) {
            abort(403);
        }

        // Soft delete: mark as deleted instead of actually removing
        $message->update([
            'is_deleted' => true,
            'body' => 'Pesan ini telah dihapus',
        ]);

        broadcast(new MessageDeleted($message->load('sender')))->toOthers();

        return $message->load('sender');
    }

    /**
     * Update/edit a message body (only sender can edit, within 24 hours).
     */
    public function updateMessage(Request $request, Message $message)
    {
        $request->validate([
            'body' => 'required|string|max:10000',
        ]);

        // Ensure user is the sender
        if ($message->sender_id !== Auth::id()) {
            abort(403);
        }

        // Ensure message is not deleted
        if ($message->is_deleted) {
            abort(400, 'Cannot edit a deleted message.');
        }

        $message->update([
            'body' => $request->body,
            'is_edited' => true,
        ]);

        broadcast(new MessageUpdated($message->load('sender')))->toOthers();

        return $message->load('sender');
    }
}
