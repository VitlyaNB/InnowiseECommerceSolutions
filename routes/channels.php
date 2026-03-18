<?php

use App\Broadcasting\ChatChannel;
use App\Broadcasting\UserChannel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', UserChannel::class);
Broadcast::channel('chat.{chatId}', ChatChannel::class);
