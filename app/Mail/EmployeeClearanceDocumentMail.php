<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Sends one offboarding clearance document (Finance / IT letter or the HR-0111
 * form) as a PDF attachment to a departing employee's personal address.
 */
class EmployeeClearanceDocumentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $employeeName,
        public string $docTitle,
        public string $reference,
        public string $pdfAbsolutePath,
    ) {
    }

    public function build()
    {
        return $this->subject($this->docTitle . ' - ' . $this->employeeName)
            ->view('mail.clearance.document')
            ->attach($this->pdfAbsolutePath, [
                'as' => $this->reference . '.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}
