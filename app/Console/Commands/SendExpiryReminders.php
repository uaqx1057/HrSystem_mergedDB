<?php

namespace App\Console\Commands;

use App\Models\Insurance;
use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\DocumentExpiryMail;
use App\Mail\DocumentExpirySummaryMail;

class SendExpiryReminders extends Command
{
    protected $signature = 'send:expiry-reminders';

    protected $description = 'Send email reminders to employees whose Iqama or Passport expires in 7 days';

    public function handle()
    {
        $targetDay = Carbon::now()->addDays(7)->format('Y-m-d');

        $employees = User::with('employeeDetail')
            ->whereHas('employeeDetail', function ($query) use ($targetDay) {
                $query->where('iqama_expiry_date', $targetDay)
                    ->orWhere('passport_expiry_date', $targetDay);
            })
            ->get();

        $admins = User::allAdmins();

        // Collect all expiring items here, to build the admin summary later
        $expiringSummary = [];

        foreach ($employees as $employee) {
            $detail = $employee->employeeDetail;

            $insurance = Insurance::whereDate('expiry_date', $targetDay)
                ->where('employee_id', $employee->id)
                ->orderBy('id', 'desc')
                ->first();

            // Fix: guard against no matching insurance record
            $insurance_expiry = $insurance ? Carbon::parse($insurance->expiry_date)->format('Y-m-d') : null;

            $data = [
                'name' => $employee->name,
                'email' => $employee->email,
            ];

            $sendEmail = false;

            if ($detail->iqama_expiry_date == $targetDay) {
                $data['iqama'] = $detail->iqama_no;
                $data['iqama_expiry'] = $detail->iqama_expiry_date;
                $sendEmail = true;
            }

            if ($detail->passport_expiry_date == $targetDay) {
                $data['passport'] = $detail->passport_no;
                $data['passport_expiry'] = $detail->passport_expiry_date;
                $sendEmail = true;
            }

            if ($insurance_expiry === $targetDay) {
                $data['insurance_expiry'] = $insurance_expiry;
                $sendEmail = true;
            }

            if ($sendEmail) {
                Mail::to($employee->email)->send(new DocumentExpiryMail($data));
                $this->info('Email sent to: ' . $employee->email);

                // Add to the list that admins will see
                $expiringSummary[] = $data;
            }
        }

        // Send one summary email to all admins, only if there's something to report
        if (!empty($expiringSummary) && $admins->isNotEmpty()) {
            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new DocumentExpirySummaryMail($expiringSummary, $targetDay));
            }
            $this->info('Summary email sent to ' . $admins->count() . ' admin(s).');
        }
    }
}
