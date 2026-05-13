<?php

namespace App\Mail;

use App\Models\AssetAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AssetAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $assignment;

    public function __construct(AssetAssignment $assignment)
    {
        $this->assignment = $assignment;
    }

    public function build()
    {
        return $this->subject('Asset Assignment Confirmation - ' . $this->assignment->asset->name)
                    ->view('mail.assign-company-assets.asset_assigned');
    }
}
