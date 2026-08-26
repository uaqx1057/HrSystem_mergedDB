<?php

namespace App\Notifications;

use App\Models\HrCandidate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CandidateApplicationReceived extends Notification implements ShouldQueue
{
    use Queueable;


    public function __construct(private HrCandidate $candidate)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('We received your application')
            ->greeting('Hello ' . $this->candidate->name . ',')
            ->line('Thank you for applying' . ($this->candidate->jobOpening ? ' for the ' . $this->candidate->jobOpening->title . ' position' : '') . '.')
            ->line('Our HR team will review your application and reach out if there is a match.');
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
