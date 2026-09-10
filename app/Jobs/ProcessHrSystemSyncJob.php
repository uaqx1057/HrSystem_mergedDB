<?php

namespace App\Jobs;

use App\Models\HrSystemSyncJob;
use App\Models\HrOffboardingCase;
use App\Services\EmployeeSystemSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessHrSystemSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public array $backoff = [60, 300, 900, 3600];

    public function __construct(public int $syncJobId)
    {
    }

    public function handle(EmployeeSystemSyncService $syncService): void
    {
        $syncJob = HrSystemSyncJob::query()->findOrFail($this->syncJobId);

        if ($syncJob->status === HrSystemSyncJob::STATUS_COMPLETED) {
            return;
        }

        $syncJob->update([
            'status' => HrSystemSyncJob::STATUS_PROCESSING,
            'attempts' => $syncJob->attempts + 1,
            'last_error' => null,
        ]);

        try {
            $employee = $syncJob->employee()->firstOrFail();
            if ($syncJob->operation === 'revoke_access') {
                $syncService->revokeLinkedSystemAccess($employee);
            } else {
                $syncService->syncEmployeeProfileToLinkedSystems($employee, true);
            }

            $syncJob->update([
                'status' => HrSystemSyncJob::STATUS_COMPLETED,
                'processed_at' => now(),
            ]);
            if ($syncJob->operation === 'revoke_access' && $syncJob->offboarding_case_id) {
                HrOffboardingCase::query()->whereKey($syncJob->offboarding_case_id)->update(['access_revoked_at' => now()]);
            }
        } catch (Throwable $exception) {
            $syncJob->update([
                'status' => HrSystemSyncJob::STATUS_PENDING,
                'last_error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        HrSystemSyncJob::query()->whereKey($this->syncJobId)->update([
            'status' => HrSystemSyncJob::STATUS_FAILED,
            'last_error' => $exception->getMessage(),
        ]);
    }
}
