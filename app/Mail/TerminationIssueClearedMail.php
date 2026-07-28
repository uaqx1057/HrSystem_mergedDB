<?php

namespace App\Mail;

use App\Models\EmployeeTermination;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TerminationIssueClearedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $termination;
    public $department;

    public function __construct(EmployeeTermination $termination, string $department)
    {
        $this->termination = $termination;
        $this->department = $department;
    }

    public function build()
    {
        return $this->subject($this->department . ' Issue Cleared - ' . $this->termination->employee->name)
                    ->view('mail.termination.issue-cleared');
    }
}
