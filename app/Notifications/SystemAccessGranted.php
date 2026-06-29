<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemAccessGranted extends Notification
{
    public function __construct(
        private string $system,
        private string $role,
        private string $systemUrl,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $systemName = strtoupper($this->system);
        $loginUrl   = rtrim($this->systemUrl, '/') . '/login';

        return (new MailMessage)
            ->subject("Your {$systemName} System Access Has Been Granted")
            ->greeting("Hello {$notifiable->name},")
            ->line("You have been granted **{$this->role}** access to the **{$systemName}** system.")
            ->line("Use the button below to visit the login page, then click **Forgot Password** with your email address (**{$notifiable->email}**) to set your password.")
            ->action("Login to {$systemName}", $loginUrl)
            ->line('If you have any questions, please contact your HR administrator.');
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
