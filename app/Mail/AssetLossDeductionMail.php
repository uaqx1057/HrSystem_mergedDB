<?php

namespace App\Mail;

use App\Models\EmployeeAssessLoss;
use App\Models\CompanyAsset;
use App\Models\AssetAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AssetLossDeductionMail extends Mailable
{
    use Queueable, SerializesModels;

    public EmployeeAssessLoss $assessLoss;
    public CompanyAsset $asset;
    public AssetAssignment $assignment;

    public function __construct(EmployeeAssessLoss $assessLoss, CompanyAsset $asset, AssetAssignment $assignment)
    {
        $this->assessLoss = $assessLoss;
        $this->asset = $asset;
        $this->assignment = $assignment;
    }

    public function build()
    {
        return $this->subject('Asset Loss/Damage - Salary Deduction Required')
            ->view('emails.asset-loss-deduction');
    }
}
