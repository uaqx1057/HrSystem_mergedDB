<?php

namespace App\Mail;

use App\Models\AdvanceSalary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdvanceSalaryStatusUpdate extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $salary;

    public function __construct(AdvanceSalary $salary)
    {
        $this->salary = $salary;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update: Your Advance Salary Request has been ' . ucfirst($this->salary->status),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.advance-salary.status_update',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
