<?php

namespace App\Mail;

use App\Models\AdvanceSalary;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdvanceSalaryRequest extends Mailable
{
    use Queueable, SerializesModels;

    public $salary;

    /**
     * Pass the AdvanceSalary model to the mailable
     */
    public function __construct(AdvanceSalary $salary)
    {
        $this->salary = $salary;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmation: Advance Salary Request Submitted',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.advance-salary.advance_salary_request', 
        );
    }

    public function attachments(): array
    {
        return [];
    }
}