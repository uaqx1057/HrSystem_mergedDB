<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentExpirySummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $expiringList;
    public $targetDay;

    public function __construct($expiringList, $targetDay)
    {
        $this->expiringList = $expiringList;
        $this->targetDay = $targetDay;
    }

    public function build()
    {
        return $this->subject('Upcoming Document Expiries — Employee Summary')
            ->view('Mail.document-expiry-summary');
    }
}
