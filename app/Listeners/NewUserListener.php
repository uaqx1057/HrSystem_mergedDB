<?php

namespace App\Listeners;

use App\Events\NewUserEvent;
use App\Notifications\NewUser;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NewUserListener
{

    public function handle(NewUserEvent $event)
    {
        try {
            Notification::send($event->user, new NewUser($event->user, $event->password));
        }
        catch (\Throwable $exception) {
            Log::error('Failed to send new user notification', [
                'user_id' => $event->user->id,
                'email' => $event->user->email,
                'exception' => $exception,
            ]);
        }
    }

}
