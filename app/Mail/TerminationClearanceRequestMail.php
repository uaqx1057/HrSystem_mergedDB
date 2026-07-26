<?php

namespace App\Mail;

use App\Models\EmployeeTermination;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TerminationClearanceRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $termination;
    public $department;

    /**
     * @param EmployeeTermination $termination
     * @param string $department 'IT' or 'Finance'
     */
    public function __construct(EmployeeTermination $termination, string $department)
    {
        $this->termination = $termination;
        $this->department = $department;
    }

    public function build()
    {
        return $this->subject('Employee Termination Clearance Required - ' . $this->termination->employee->name)
                    ->view('mail.termination.clearance-request');
    }
}
