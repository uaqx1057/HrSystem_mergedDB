<?php

namespace App\Mail;

use App\Models\EmployeeTermination;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TerminationReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $termination;
    public $department;
    public $reasonMessage;

    /**
     * @param EmployeeTermination $termination
     * @param string $department 'IT' or 'Finance'
     * @param string $reasonMessage
     */
    public function __construct(EmployeeTermination $termination, string $department, string $reasonMessage)
    {
        $this->termination = $termination;
        $this->department = $department;
        $this->reasonMessage = $reasonMessage;
    }

    public function build()
    {
        return $this->subject($this->department . ' Clearance Reminder - ' . $this->termination->employee->name)
                    ->view('mail.termination.reminder');
    }
}
