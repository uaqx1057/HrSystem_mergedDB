<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\HrCandidate;
use App\Models\HrInterviewSchedule;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class InterviewScheduledAdmin extends Notification
{
    public function __construct(
        private HrCandidate $candidate,
        private Event $event,
        private HrInterviewSchedule $interview,
        private ?Collection $interviewers = null
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Interview scheduled: ' . $this->candidate->name)
            ->greeting('Hello,')
            ->line('An interview has been scheduled for the following candidate.')
            ->line('Candidate: ' . $this->candidate->name)
            ->line('Email: ' . $this->candidate->email)
            ->line('Mobile: ' . ($this->candidate->mobile ?: 'Not provided'))
            ->line('Position: ' . ($this->candidate->jobOpening?->title ?: ($this->candidate->designation ?: 'General application')))
            ->line('Round: ' . $this->interview->round)
            ->line('Date & Time: ' . $this->event->start_date_time->format('F j, Y g:i A') . ' - ' . $this->event->end_date_time->format('g:i A'))
            ->line('Location/Link: ' . ($this->event->where ?: 'Office'));

        // if ($this->interviewers && $this->interviewers->isNotEmpty()) {
        //     $mail->line('Interviewer(s): ' . $this->interviewers->pluck('name')->implode(', '));
        // }

        $mail->line('Please review the interview details in the recruitment section.');

        return $mail;
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
