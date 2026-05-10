<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentExpiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $expiryData;

    public function __construct($expiryData)
    {
        $this->expiryData = $expiryData;
    }

    public function build()
    {
        return $this->subject('Document Expiry Notification')
                    ->view('Mail.document-expiry');
    }
}
