<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class PasswordChanged extends BaseNotification
{
    public function __construct(
        private string $password,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $this->company = $notifiable->company;

        return $this->build()
            ->subject('Your password has been changed')
            ->greeting("Hello {$notifiable->name},")
            ->line('Your account password has been changed successfully.')
            ->line("Your new password is: {$this->password}")
            ->line('If you did not make this change, please contact your administrator immediately.');
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}