<?php

namespace App\Notifications;

use App\Models\HrCandidate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CandidateRejected extends Notification
{
    public function __construct(private HrCandidate $candidate)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Update on your application')
            ->greeting('Hello ' . $this->candidate->name . ',')
            ->line('Thank you for your interest in joining us. After careful review, we will not be moving forward with your application at this time.');

        if ($this->candidate->rejection_reason) {
            $message->line('Reason: ' . $this->candidate->rejection_reason);
        }

        return $message->line('We wish you the best in your job search.');
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
