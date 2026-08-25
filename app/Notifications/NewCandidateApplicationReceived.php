<?php

namespace App\Notifications;

use App\Models\HrCandidate;
use App\Models\HrCandidateDocument;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use App\Services\StorageSetting;

class NewCandidateApplicationReceived extends Notification
{
    public function __construct(
        private HrCandidate $candidate,
        private ?HrCandidateDocument $resume = null
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('New candidate application received')
            ->greeting('Hello,')
            ->line('A new candidate application has been submitted and is ready for review.')
            ->line('Candidate: ' . $this->candidate->name)
            ->line('Email: ' . $this->candidate->email)
            ->line('Mobile: ' . ($this->candidate->mobile ?: 'Not provided'))
            ->line('Position: ' . ($this->candidate->jobOpening?->title ?: 'General application'))
            ->line('Employment type: ' . ucfirst($this->candidate->employee_type ?: 'Not specified'))
            ->line('Application received: ' . $this->candidate->created_at->format('d M Y, H:i'));

        if (filled($this->candidate->notes)) {
            $mail->line('Note: ' . $this->candidate->notes);
        }

        $mail->line('Please review the application in the recruitment section.');

        if ($this->resume) {
            $disk = config('filesystems.default');

            // stored_path is just the filename — reconstruct the dir it was saved under.
            $relativePath = 'candidate-documents/' . $this->resume->stored_path;

            if (Storage::disk($disk)->exists($relativePath)) {
                $mail->attachData(
                    Storage::disk($disk)->get($relativePath),
                    $this->resume->original_name,
                    ['mime' => $this->resume->mime_type]
                );
            }
        }

        return $mail;
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
