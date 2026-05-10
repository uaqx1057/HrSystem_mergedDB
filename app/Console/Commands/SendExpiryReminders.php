<?php

namespace App\Console\Commands;

use App\Models\Insurance;
use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\DocumentExpiryMail;

class SendExpiryReminders extends Command
{
    // The name and signature of the console command
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

        foreach ($employees as $employee) {
            $detail = $employee->employeeDetail;
            $insurance = Insurance::whereDate('expiry_date', $targetDay)->where('employee_id', $employee->id)->orderBy('id', 'desc')->first();
            $insurance_expiry = Carbon::parse($insurance->expiry_date)->format('Y-m-d');

            $data = [
                'name'  => $employee->name,
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

            // Check if Insurance is the one expiring
            if ($insurance_expiry == $targetDay) {
                $data['insurance_expiry'] = $insurance_expiry;
                $sendEmail = true;
            }

            if ($sendEmail) {
                // Send email to the employee
                Mail::to($employee->email)->send(new DocumentExpiryMail($data));
                $this->info('Email sent to: ' . $employee->email);
            }
        }
    }
}
