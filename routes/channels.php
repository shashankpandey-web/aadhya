<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::routes(['middleware' => ['auth:api']]);

Broadcast::channel('chat.{currentUserId}.{otherUserId}', function ($user, $currentUserId, $otherUserId) {
    return (int) $user->id == (int) $currentUserId || $user->id == $otherUserId; 
});

