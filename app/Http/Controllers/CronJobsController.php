<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Mail\IqamaExpiryMail;
use Illuminate\Support\Facades\Mail;


class CronJobsController extends Controller
{
    public function iqamaExpiryMail()
    {
        $today = Carbon::today();
        $oneWeekLater = Carbon::today()->addDays(7);

        $employees = User::with('employeeDetail')
            ->whereHas('employeeDetail', function ($query) use ($today, $oneWeekLater) {
                $query->whereNotNull('iqama_expiry_date')
                    ->whereDate('iqama_expiry_date', '>=', $today)
                    ->whereDate('iqama_expiry_date', '<=', $oneWeekLater);
            })
            ->get();

        if ($employees->isNotEmpty()) {
            Mail::to('hr@ilab.com')->send(new IqamaExpiryMail($employees));
            return 'mail send successfully';
        }

        return 'employee not found';
    }
}
