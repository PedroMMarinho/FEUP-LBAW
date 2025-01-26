<?php
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user-balance.{userId}', function ($user, $userId) {
    return $user->id === (int) $userId;
});