<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\HrCandidate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewScheduled extends Notification
{
    public function __construct(private HrCandidate $candidate, private Event $event)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your interview has been scheduled')
            ->greeting('Hello ' . $this->candidate->name . ',')
            ->line('Your interview has been scheduled for ' . $this->event->start_date_time->format('F j, Y g:i A') . '.')
            ->line('Location/Link: ' . $this->event->where)
            ->line($this->event->description);
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
