<?php

namespace App\Mail;

use App\Models\EmployeeTermination;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TerminationRevertedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $termination;
    public $wasCompleted;

    public function __construct(EmployeeTermination $termination, bool $wasCompleted)
    {
        $this->termination = $termination;
        $this->wasCompleted = $wasCompleted;
    }

    public function build()
    {
        return $this->subject($this->wasCompleted
                ? 'Your termination has been reverted — welcome back'
                : 'Your pending termination has been cancelled')
            ->view('emails.termination-reverted');
    }
}
