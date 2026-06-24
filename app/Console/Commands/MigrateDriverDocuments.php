<?php

namespace App\Console\Commands;

use App\Models\Driver;
use App\Models\DriverDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MigrateDriverDocuments extends Command
{
    /**
     * php artisan drivers:migrate-documents --dry-run
     * php artisan drivers:migrate-documents
     * php artisan drivers:migrate-documents --only=hr
     * php artisan drivers:migrate-documents --only=dms
     * php artisan drivers:migrate-documents --only=dobs
     */
    protected $signature = 'drivers:migrate-documents
        {--dry-run : Preview only, no files copied and no DB rows inserted}
        {--only= : Limit to one system: hr, dms, or dobs}';

    protected $description = 'One-time migration of legacy per-system driver document columns into the shared driver_documents table/storage.';

    protected bool $isDryRun = false;

    /** Counters for the final summary */
    protected array $stats = [
        'copied' => 0,
        'skipped' => 0,
        'failed' => 0,
    ];

    // ------------------------------------------------------------------
    // CONFIG — confirm/adjust these absolute paths before running live
    // ------------------------------------------------------------------

    /** Root of the NEW shared disk (same value as DRIVER_DOCUMENT_PATH in .env) */
    protected string $sharedRoot;

    /** HR legacy root: local "user-uploads/" directory root (absolute path) */
    protected string $hrLegacyRoot;

    /** DMS legacy root: Laravel default public disk root (storage/app/public) */
    protected string $dmsLegacyRoot;

    /**
     * DOBS legacy root — TODO: confirm DOBS_STATIC_PATH on live server.
     * Left null on purpose; DOBS columns will be skipped/logged until this is set.
     */
    protected ?string $dobsLegacyRoot = null;

    /**
     * HR column -> new document_type, per the agreed mapping table.
     * sim_form -> contract, mobile_form/other_document -> other (confirmed by client note).
     */
    protected array $hrColumnMap = [
        'iqama'          => 'iqama',
        'license'        => 'license',
        'medical'        => 'medical',
        'sim_form'       => 'contract',
        'mobile_form'    => 'mobile',
        'other_document' => 'other',
        // 'image' intentionally excluded — that's the driver's profile photo/avatar,
        // not a "document" in the driver_documents sense. Remove this line and add
        // 'image' => 'other' below if the client actually wants it migrated too.
    ];

    /** DMS column -> new document_type */
    protected array $dmsColumnMap = [
        'iqama_image'   => 'iqama',
        'passport_image' => 'passport',
    ];

    /**
     * DOBS column -> new document_type.
     * Some DOBS columns map many-to-one (multiple old columns -> one new type),
     * which is fine: each becomes its own driver_documents row.
     */
    protected array $dobsColumnMap = [
        'iqama_card_upload'          => 'iqama',
        'company_contract_file'      => 'contract',
        'qiwa_contract_file'         => 'contract',
        'promissory_note_file'       => 'other',
        'transfer_fee_receipt'       => 'other',
        'sponsorship_transfer_proof' => 'other',
        'tamm_authorization_ss'      => 'other',
    ];

    public function handle(): int
    {
        $this->isDryRun = (bool) $this->option('dry-run');
        $only = $this->option('only');

        $this->sharedRoot   = rtrim(env('DRIVER_DOCUMENT_PATH'), '/\\');
        // user-uploads/ lives inside public/, not at the project root
        $this->hrLegacyRoot  = rtrim(public_path('user-uploads'), '/\\');
        $this->dmsLegacyRoot = rtrim(storage_path('app/public'), '/\\');
        // $this->dobsLegacyRoot = rtrim('/path/to/dobs/static/uploads', '/\\'); // TODO

        if (empty($this->sharedRoot)) {
            $this->error('DRIVER_DOCUMENT_PATH is not set in .env — aborting.');
            return self::FAILURE;
        }

        if (!File::isDirectory($this->sharedRoot)) {
            $this->warn("Shared root does not exist yet, creating: {$this->sharedRoot}");
            if (!$this->isDryRun) {
                File::makeDirectory($this->sharedRoot, 0755, true);
            }
        }

        $this->info($this->isDryRun
            ? '>>> DRY RUN — no files will be copied, no DB rows will be inserted.'
            : '>>> LIVE RUN — files will be copied and driver_documents rows inserted.');

        $drivers = Driver::withoutGlobalScopes()->get();
        $this->info("Found {$drivers->count()} driver record(s).");

        foreach ($drivers as $driver) {
            if (!$only || $only === 'hr') {
                $this->migrateHr($driver);
            }
            if (!$only || $only === 'dms') {
                $this->migrateDms($driver);
            }
            if (!$only || $only === 'dobs') {
                $this->migrateDobs($driver);
            }
        }

        $this->newLine();
        $this->info('=== Summary ===');
        $this->info("Copied:  {$this->stats['copied']}");
        $this->info("Skipped: {$this->stats['skipped']}");
        $this->info("Failed:  {$this->stats['failed']}");

        return self::SUCCESS;
    }

    // ------------------------------------------------------------------
    // HR
    // ------------------------------------------------------------------

    protected function migrateHr(Driver $driver): void
    {
        foreach ($this->hrColumnMap as $column => $documentType) {
            $value = $driver->{$column} ?? null;

            if (empty($value)) {
                $this->logAction('skipped', "HR driver #{$driver->id} column '{$column}' is empty.");
                continue;
            }

            $filename = ltrim($value, '/\\');
            $oldAbsolutePath = $this->hrLegacyRoot . '/' . $column . '/' . $filename;

            // --- NEW LOGIC FOR EXPIRY DATES ---
            $expiresAt = null;
            if ($documentType === 'iqama') {
                $expiresAt = $driver->iqaama_expiry_date;
            } elseif ($documentType === 'license') {
                $expiresAt = $driver->license_expiry_date;
            }
            // ----------------------------------

            $this->migrateOneFile(
                driver: $driver,
                oldAbsolutePath: $oldAbsolutePath,
                documentType: $documentType,
                uploadedFrom: 'hr',
                oldColumnLabel: "hr.{$column}",
                expiresAt: $expiresAt // Pass the date here
            );
        }
    }

    // ------------------------------------------------------------------
    // DMS
    // ------------------------------------------------------------------

    protected function migrateDms(Driver $driver): void
    {
        foreach ($this->dmsColumnMap as $column => $documentType) {
            $value = $driver->{$column} ?? null;

            if (empty($value)) {
                $this->logAction('skipped', "DMS driver #{$driver->id} column '{$column}' is empty.");
                continue;
            }

            // Default Laravel public disk stores relative paths, e.g. "iqama_image/abc123.pdf"
            $oldAbsolutePath = $this->dmsLegacyRoot . '/' . ltrim($value, '/\\');

            $this->migrateOneFile(
                driver: $driver,
                oldAbsolutePath: $oldAbsolutePath,
                documentType: $documentType,
                uploadedFrom: 'dms',
                oldColumnLabel: "dms.{$column}"
            );
        }
    }

    // ------------------------------------------------------------------
    // DOBS (stubbed until DOBS_STATIC_PATH is confirmed)
    // ------------------------------------------------------------------

    protected function migrateDobs(Driver $driver): void
    {
        if ($this->dobsLegacyRoot === null) {
            // Only warn once per run instead of once per driver/column to avoid log spam.
            static $warned = false;
            if (!$warned) {
                $this->warn('DOBS legacy root not configured — skipping all DOBS columns. Set $dobsLegacyRoot and re-run with --only=dobs.');
                $warned = true;
            }
            return;
        }

        foreach ($this->dobsColumnMap as $column => $documentType) {
            $value = $driver->{$column} ?? null;

            if (empty($value)) {
                $this->logAction('skipped', "DOBS driver #{$driver->id} column '{$column}' is empty.");
                continue;
            }

            $oldAbsolutePath = $this->dobsLegacyRoot . '/uploads/' . ltrim($value, '/\\');

            $this->migrateOneFile(
                driver: $driver,
                oldAbsolutePath: $oldAbsolutePath,
                documentType: $documentType,
                uploadedFrom: 'dobs',
                oldColumnLabel: "dobs.{$column}"
            );
        }
    }

    // ------------------------------------------------------------------
    // Shared copy + insert logic
    // ------------------------------------------------------------------

    protected function migrateOneFile(
        Driver $driver,
        string $oldAbsolutePath,
        string $documentType,
        string $uploadedFrom,
        string $oldColumnLabel,
        $expiresAt = null // Added optional parameter
    ): void {
        if (!File::exists($oldAbsolutePath)) {
            $this->logAction('failed', "Driver #{$driver->id} [{$oldColumnLabel}]: source file not found at {$oldAbsolutePath}");
            return;
        }

        $normalizedPath = str_replace('\\', '/', $oldAbsolutePath);
        $migrationIdentifier = "Migrated from {$oldColumnLabel}:";

        $alreadyMigrated = DriverDocument::where('driver_id', $driver->id)
            ->where('uploaded_from', $uploadedFrom)
            ->where('notes', 'like', "{$migrationIdentifier}%")
            ->exists();

        if ($alreadyMigrated) {
            $this->logAction('skipped', "Driver #{$driver->id} [{$oldColumnLabel}]: already migrated, skipping.");
            return;
        }

        $extension = File::extension($oldAbsolutePath) ?: 'bin';
        $timestamp = now()->format('YmdHis');
        $namePart  = Str::slug($driver->name ?? 'driver', '_');
        $idPart    = $driver->iqaama_number ?? $driver->id;

        $customName = "{$namePart}_{$idPart}_{$documentType}_{$timestamp}.{$extension}";
        $targetDir  = $this->sharedRoot . '/' . $driver->id;
        $targetPath = $targetDir . '/' . $customName;

        if ($this->isDryRun) {
            $this->logAction('copied', "[DRY-RUN] Would copy {$oldAbsolutePath} -> {$targetPath}");
            return;
        }

        try {
            if (!File::isDirectory($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }

            File::copy($oldAbsolutePath, $targetPath);

            DriverDocument::create([
                'driver_id'     => $driver->id,
                'document_type' => $documentType,
                'file_path'     => $driver->id . '/' . $customName,
                'original_name' => $customName,
                'file_size'     => File::size($targetPath),
                'uploaded_from' => $uploadedFrom,
                'uploaded_by'   => null,
                'notes'         => "{$migrationIdentifier} {$normalizedPath}",
                'expires_at'    => $expiresAt, // Now correctly populating from the argument
            ]);

            $this->logAction('copied', "Driver #{$driver->id} [{$oldColumnLabel}]: copied to {$targetPath}");
        } catch (\Throwable $e) {
            $this->logAction('failed', "Driver #{$driver->id} [{$oldColumnLabel}]: " . $e->getMessage());
        }
    }

    protected function logAction(string $type, string $message): void
    {
        $this->stats[$type] = ($this->stats[$type] ?? 0) + 1;

        match ($type) {
            'copied'  => $this->line("<fg=green>[COPIED]</>  {$message}"),
            'skipped' => $this->line("<fg=yellow>[SKIPPED]</> {$message}"),
            'failed'  => $this->line("<fg=red>[FAILED]</>  {$message}"),
            default   => $this->line($message),
        };

        // Also write to the standard Laravel log for a permanent audit trail.
        logger()->channel('single')->info("[drivers:migrate-documents][{$type}] {$message}");
    }
}
