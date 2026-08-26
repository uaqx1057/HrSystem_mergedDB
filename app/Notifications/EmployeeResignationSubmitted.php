<?php

namespace App\Notifications;

use App\Models\EmployeeTermination;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeResignationSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private EmployeeTermination $resignation)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $employee = $this->resignation->employee;

        return (new MailMessage)
            ->subject('Employee resignation submitted')
            ->greeting('Hello,')
            ->line('An employee has submitted a resignation request.')
            ->line('Employee: ' . ($employee?->name ?? 'Unknown'))
            ->line('Email: ' . ($employee?->email ?? 'Not provided'))
            ->line('Resignation date: ' . optional($this->resignation->resignation_date)->format('d M Y'))
            ->line('Last working date: ' . optional($this->resignation->last_working_date)->format('d M Y'))
            ->line('Reason: ' . ($this->resignation->reason ?: $this->resignation->terminate_reason ?: 'Not provided'))
            ->line('Please review the request in Pending Offboard and complete the IT and Finance clearance process.');
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
