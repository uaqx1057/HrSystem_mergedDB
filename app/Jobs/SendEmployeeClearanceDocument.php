<?php

namespace App\Jobs;

use App\Mail\EmployeeClearanceDocumentMail;
use App\Models\EmployeeTermination;
use App\Models\User;
use App\Scopes\ActiveScope;
use App\Support\Clearance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Emails a departing employee a copy of one clearance document (Finance / IT
 * letter or the HR-0111 form) as a PDF, to their personal address, when that
 * clearance is issued. A missing personal email is skipped, not an error.
 */
class SendEmployeeClearanceDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const TITLES = [
        'finance' => 'Finance Clearance & Dues Settlement',
        'it'      => 'IT Clearance & Asset Return Summary',
        'hr'      => 'HR Clearance & Offboarding Form',
    ];

    public function __construct(public int $employeeId, public string $kind)
    {
    }

    public function handle(): void
    {
        if (!array_key_exists($this->kind, self::TITLES)) {
            return;
        }

        $employee = User::withoutGlobalScope(ActiveScope::class)->with('employeeDetail')->find($this->employeeId);
        if (!$employee) {
            return;
        }

        $personal = $employee->employeeDetail?->personal_email;
        if (empty($personal)) {
            Log::info("Clearance document ({$this->kind}) not emailed - no personal email for employee {$this->employeeId}.");
            return;
        }

        $termination = EmployeeTermination::query()
            ->where('user_id', $this->employeeId)
            ->whereIn('status', [EmployeeTermination::STATUS_PENDING, EmployeeTermination::STATUS_COMPLETED])
            ->latest('id')
            ->first();
        if (!$termination) {
            return;
        }

        [$view, $data, $reference] = Clearance::letterView($this->kind, $termination);

        $tmp = storage_path('app/tmp');
        if (!is_dir($tmp)) {
            @mkdir($tmp, 0775, true);
        }
        $path = $tmp . '/' . $reference . '-' . uniqid() . '.pdf';

        try {
            file_put_contents($path, Pdf::loadView($view, $data)->setPaper('letter')->output());

            $mail = Mail::to($personal);
            if (!empty($employee->email)) {
                $mail->cc($employee->email);
            }
            $mail->send(new EmployeeClearanceDocumentMail(
                $employee->name,
                self::TITLES[$this->kind],
                $reference,
                $path,
            ));
        } catch (\Throwable $e) {
            Log::error("Failed to email clearance document ({$this->kind}) for employee {$this->employeeId}: " . $e->getMessage());
        } finally {
            @unlink($path);
        }
    }
}
