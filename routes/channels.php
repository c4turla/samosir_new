<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{conversation_id}', function ($user, $conversation_id) {
    return $user->conversations()->where('conversation_id', $conversation_id)->exists();
});

Broadcast::channel('chat-presence', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});
