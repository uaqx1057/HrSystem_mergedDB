<?php

namespace App\Jobs;

use App\Mail\TerminationCompletedMail;
use App\Models\EmployeeTermination;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendTerminationCompletedNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $terminationId)
    {
    }

    public function handle(): void
    {
        $termination = EmployeeTermination::query()->findOrFail($this->terminationId);
        $employee = $termination->employee()->firstOrFail();
        $recipients = collect([$employee])
            ->merge(User::usersWithPermission('manage_it_clearance', $employee->company_id))
            ->merge(User::usersWithPermission('manage_finance_clearance', $employee->company_id))
            ->filter(fn (User $recipient) => !empty($recipient->email))
            ->unique('email');

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new TerminationCompletedMail($termination));
            } catch (\Exception $exception) {
                Log::error('Failed to send termination completed email: ' . $exception->getMessage());
            }
        }
    }
}
