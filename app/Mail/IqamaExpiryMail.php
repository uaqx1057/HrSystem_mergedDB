<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class IqamaExpiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $employees;

    public function __construct($employees)
    {
        $this->employees = $employees;
    }

    public function build()
    {
        return $this->subject('Iqama Expiry Alert — Action Required')
                    ->view('Mail.iqama-expiry');
    }
}
