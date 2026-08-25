<?php

namespace App\Notifications;

use App\Models\HrCandidate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCandidateApplicationReceived extends Notification
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
        return (new MailMessage)
            ->subject('New candidate application received')
            ->greeting('Hello,')
            ->line('A new candidate application has been submitted and is ready for review.')
            ->line('Candidate: ' . $this->candidate->name)
            ->line('Email: ' . $this->candidate->email)
            ->line('Mobile: ' . ($this->candidate->mobile ?: 'Not provided'))
            ->line('Position: ' . ($this->candidate->jobOpening?->title ?: 'General application'))
            ->line('Employment type: ' . ucfirst($this->candidate->employee_type ?: 'Not specified'))
            ->line('Application received: ' . $this->candidate->created_at->format('d M Y, H:i'))
            ->line('Please review the application in the recruitment section.');
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
