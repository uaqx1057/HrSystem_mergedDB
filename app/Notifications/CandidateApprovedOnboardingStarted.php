<?php

namespace App\Notifications;

use App\Models\HrCandidate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CandidateApprovedOnboardingStarted extends Notification
{
    public function __construct(private HrCandidate $candidate, private bool $forHr = false)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        if ($this->forHr) {
            return (new MailMessage)
                ->subject('Candidate approved — onboarding started')
                ->line($this->candidate->name . ' has been approved and their pre-hire onboarding checklist has started.');
        }

        return (new MailMessage)
            ->subject('Welcome aboard!')
            ->greeting('Hello ' . $this->candidate->name . ',')
            ->line('Congratulations — you have been approved! HR will now guide you through a short onboarding checklist before your employee account is activated.');
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
