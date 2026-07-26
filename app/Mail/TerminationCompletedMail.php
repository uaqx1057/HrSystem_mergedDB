<?php

namespace App\Mail;

use App\Models\EmployeeTermination;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TerminationCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $termination;

    public function __construct(EmployeeTermination $termination)
    {
        $this->termination = $termination;
    }

    public function build()
    {
        return $this->subject('Employee Termination Completed - ' . $this->termination->employee->name)
                    ->view('mail.termination.completed');
    }
}
