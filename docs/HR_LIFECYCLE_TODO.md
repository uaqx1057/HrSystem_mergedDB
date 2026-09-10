# HR Lifecycle Implementation Tracker

Last reviewed: 2026-09-09. Nothing in this stack is committed or deployed yet — the
whole thing ships as one deploy after the "Left to do" section is cleared.

**2026-09-09 — coding + UI polish complete.** All of A–E, the follow-up items
(pending salary / notice auto-calc, structured RT inspection rows, My-Resignation
+ IT / Finance worklists + tabbed offboarding console, status-casing
normalisation, behavioural test suites), and a full UI-consistency pass are done:
every new HR-lifecycle screen now uses the app's `content-wrapper` + card +
`table-hover` + badge pattern, consistent page headers, empty states, FA4 icons,
progress bars on the console and lifecycle card, pre-filled certification
checklist defaults, and a two-column settlement worksheet with a live summary
panel. **67 unit tests pass; every changed PHP file lints; every blade compiles;
all route names in the new blades resolve.** Pending migrations: **11**
(`2026_09_09_130000` … `230000`). What is left is non-code: HR/legal sign-off on
the EOSB policy numbers, and the deploy prerequisites + server-side runtime
verification (no local DB here).

## Deployed to production — 2026-09-09

hr.speedlogi.sa (`/srv/apps/releases/HrSystem_mergedDB`). Backup:
`deploy-backups/20260909_215036_hr-lifecycle/` (`tables.sql` = 7 affected tables,
`changed_files_pre.tgz` = 56 pre-existing files, `new_files_will_be_added.txt`).

- 102 files uploaded, all hash-verified byte-identical (CRLF-normalised); 75 real
  changes, 27 already-identical.
- 11 migrations ran clean (batch 293). Backfill created **2** offboarding cases
  from the 2 in-flight pending terminations, 0 exceptions. Status-casing rewrote
  3 rows (`asset_assignments`: 2, `employee_assess_losses`: 1).
- Post-deploy remediation: the 2 backfilled cases had no task grid / sync job
  (the migration links the case but does not seed tasks like `approve()`); a
  one-off seeded the 7-row HR-0111 grid + a `revoke_access` sync job for each —
  **both jobs processed to `completed` by the live redis workers**, both cases
  now carry `access_revoked_at`.
- `queue:restart` + `config:cache` + `view:cache` (all blades compiled) +
  `systemctl reload php8.4-fpm`. Health: `/login` 200, all `account/*` 302, no
  ERROR/Exception in the log.
- **Caveat — `payroll_employee_setups` is empty on prod**, so the settlement
  engine suggests 0 for EOSB / leave / notice until per-employee payroll setup
  (basic salary etc.) is entered. Finance can hand-key every figure meanwhile.

Still non-code: HR/legal sign-off on the `hr_settlement_settings` numbers before
anyone uses **Finalise** on a real settlement; populate `payroll_employee_setups`;
browser click-through of the new screens; `git commit` the stack.

## Goal

One secure, auditable HR lifecycle from hiring through offboarding: one canonical
case per employee exit, role-owned clearance, settlement without double recovery,
formal asset disposition, confirmed DMS/DOBS access revocation, immutable legal
documents on the Speed Logi purple letterhead, and a visible employee timeline.

## Status at a glance

| Area | State |
|---|---|
| Phase 1 — critical safety & security | **Done** (code + string-assertion tests) |
| Phase 2 — canonical offboarding (workflow engine) | **Done** (plumbing); role console screens left |
| Phase 2.5 — asset return / write-off / recovery | **Done** (plumbing); RT PDF layout left |
| Phase 3 — final settlement | **Engine built** — auto-derives EOSB (Art. 84/85), leave, advances, asset recovery, pending salary, notice-in-lieu; legal sign-off outstanding |
| Phases 4–6 — onboarding / compliance / worklist / transfer | **Partial** (offboarding side complete) |
| Legal PDFs on letterhead (RT / FC / HR-0111 / handover Clause 6) | **Done** — full layouts, CR footer, purple accent, structured RT rows |
| Offboarding approvals inbox + HR-0111 grid & PDF + tabbed console + My-Resignation + IT/Finance worklists | **Done** |
| Backfill migration + status-casing normalisation | **Done** (`2026_09_09_200000`, `220000`) |
| Behavioural test coverage | **Done** for the new logic — OffboardingService (request/approve/reject/adopt/complete/revert), settlement math + finalize lock, recovery cap + no-double + waiver, EOSB bands |
| Deploy prerequisites | **Not started** (do at deploy time) |

---

## Done

### Phase 1 — Critical safety & security

- [x] Finance dues check `&&` → `||` — blocks when advance salary **or** asset-loss recovery remains (`TerminationClearanceController` ~L271).
- [x] Clearance / termination lookups filter `status = STATUS_PENDING` only — `TerminationClearanceController::findTermination`, `EmployeeController` (`showTerminatePending`, `completeTermination`, `canManageTermination`, `revertTermination`).
- [x] Literal `branch_id == 6` Head-Office override replaced with `HrAccess::canAccessEmployeeBranch()` — `EmployeeController::terminatePending` + `canManageTermination`.
- [x] `HrLifecycleController::syncCaseCompletion()` no longer deactivates the employee or triggers linked-system sync — generic task completion is inert.
- [x] `EmployeeAssessLoss` status constants (`STATUS_PENDING='Pending'`, `STATUS_SETTLED='Deducted'`); every `'Pending'` string literal replaced.
- [x] IT clearance issue wrapped in `DB::transaction` + `lockForUpdate`; uses `AssetAssignment::STATUS_ASSIGNED` constant.
- [x] Durable linked-system revocation outbox — `hr_system_sync_jobs` + `HrSystemSyncJob` model + `ProcessHrSystemSyncJob` (5 tries, backoff, idempotent, `failed()` handler), dispatched `->afterCommit()`.
- [x] Completed-termination notifications moved to `SendTerminationCompletedNotifications` job, dispatched after commit.
- [x] `EmployeeSystemSyncService` — added `syncEmployeeProfileToLinkedSystems($user, $throwOnFailure)` + `revokeLinkedSystemAccess()`.
- [x] `completeTermination` wrapped in `DB::transaction`; mail + sync dispatched after commit.
- [x] Route-scoped authorization on formal asset actions — `writeOffAssignment`, `updateSerialStatus`, `rtPdf` resolve the record from the route `{id}` and check permission + `canManageRecord` / `authorizeAssignmentManagement`.

### Phase 2 — Canonical offboarding (workflow engine)

- [x] Schema — `hr_offboarding_cases` gains approval / settlement / access-revocation / completion / reversion columns + company-scoped `reference` unique; `employee_terminations.offboarding_case_id` unique one-to-one. Migration `2026_09_09_130000`.
- [x] New models + tables — `HrOffboardingTask`, `HrOffboardingTaskTemplate`, `HrLifecycleEvent`, `HrSystemSyncJob`.
- [x] `OffboardingService` — `request / approve / reject / completeTask` with `lockForUpdate`, duplicate-open-case guard, `HR-000123` reference, lifecycle events, template-or-default task seeding, revoke-access sync job on approve.
- [x] `OffboardingService::complete()` + `revert()` — own the case-record transition (`status`, `completed_by/at`, `reverted_*`) + lifecycle event. *(bug fix B1)*
- [x] `approve()` adopts an existing unlinked pending `EmployeeTermination` instead of creating a duplicate. *(bug fix B3)*
- [x] Legacy writers rerouted through `OffboardingService::request()` — `HrLifecycleController::startOffboarding` / `startResignation`, `EmployeeController::terminatePending` / `submitResignation`.
- [x] `approveOffboarding` / `rejectOffboarding` routes; lifecycle-screen UI has Approve / Reject (with a real reason field) + approval-status badge + lifecycle timeline.
- [x] `completeTermination` gated on: IT + Finance issued **and** a `FINAL` `HrSettlementForm` for the current termination **and** (if case-linked) zero open required tasks **and** `access_revoked_at` set; delegates case closure to `OffboardingService::complete()`.
- [x] `revertTermination` delegates case reversal to `OffboardingService::revert()`.

### Phase 2.5 — Asset return / write-off / recovery

- [x] `asset_return_forms` + `asset_recovery_allocations` tables + models (constants, `allocatedAmount`, `remainingRecoveryAmount`). Migrations `2026_09_09_140000`, `2026_09_09_150000`.
- [x] `AssetReturnService::certifyReturn` — locks assignment + asset + serial, writes `AssetAssignmentHistory(returned)`, releases the serial only after IT certification, closes the assignment, `syncAvailability()`, builds the finalized RT form + JSON snapshot + `RT-000123` reference.
- [x] `AssetReturnService::writeOff` — terminal-outcome guard, `written_off` history action, serial → `lost` / `damaged` / `retired`, creates `EmployeeAssessLoss` when a recovery amount is given and links it into the snapshot.
- [x] `AssetReturnService::generatePdf` — immutable: refuses non-final, early-returns if `pdf_path` + `document_hash` already set, stores + SHA-256.
- [x] `AssetRecoveryApprovalService` — caps the approved amount at `recommended − already-allocated`, writes an `asset_recovery_allocations` (settlement) ledger row.
- [x] `AssetRecoveryWaiverService` — full-remaining waiver allocation row + reason.
- [x] Recovery uses the allocation ledger as the single source of truth — neither service touches `EmployeeAssessLoss.deducted_amount`; the loss `status` flips to `Deducted` only when allocations fully cover the recommendation (approval) or on waiver, via a guarded `where('status', PENDING)` update. Payroll cannot recover the same loss again. *(bug fix B4)*
- [x] `HrAssetCustodyCertificationController` — IT certification queue wired to `certifyReturn()`; `hr_asset_custody_records` extended (`asset_return_form_id`, `certified_by/at`, `certification_notes`). Migration `2026_09_09_160000`.
- [x] `CompanyAssetController::writeOffAssignment` + `updateSerialStatus` + `rtPdf`.

### Phase 3 — Final settlement (plumbing only)

- [x] `hr_settlement_forms` + `hr_settlement_line_items` tables + models. Migrations `2026_09_09_170000`, `2026_09_09_180000`.
- [x] `HrSettlementService::worksheet / finalize / generatePdf` — draft/final guard, `lockForUpdate`, stores `inputs` JSON + `policy_version`, rebuilds line items, `net = payable − recoverable`, immutable snapshot + SHA-256 PDF, refuses re-finalize.
- [x] `HrSettlementController` (edit / worksheet / finalize / pdf) + `hr-settlement/edit` screen, linked from the pending-termination detail modal.
- [x] Offboarding completion gated on a `FINAL` settlement tied to the current legal termination.

### Phases 4–6 (partial)

- [x] Transfer gains `asset_decision` (validated `required` when the employee holds active assets) + a `transfer_branch_sync` sync job on apply. Migration `2026_09_09_190000`.
- [x] `applyTransfer` blocks when active assets remain and `asset_decision != retain`.
- [x] HR worklist gains: pending asset recovery, IT return certifications, failed linked-system sync, and (fixed) document-expiry counts.
- [x] Lifecycle timeline on the employee lifecycle screen.
- [x] `AuthorizesHrAccess` trait created (used by `HrWorklistController`).
- [x] Document-expiry logic extracted to `App\Support\HrDocumentExpiry` and shared by the worklist + compliance dashboard (passport / iqama-visa / insurance / `employee_details.contract_end_date`; the bogus `contracts.client_id` join is gone). *(bug fix B5)*

---

## Bug fixes — 2026-09-09 session

| # | Bug | Fix | Files |
|---|---|---|---|
| B1 | Offboarding case stranded at `completion_pending` — nothing set it to `completed`. | Added `OffboardingService::complete()` / `revert()`; `EmployeeController::completeTermination` / `revertTermination` delegate the case-state transition inside their transactions. | `OffboardingService.php`, `EmployeeController.php` |
| B2 | Reject button posted a hardcoded reason (`"Rejected by reviewer"`). | Real required `name="reason"` text input on the reject form (matches `rejectOffboarding` validation). | `resources/views/hr-lifecycle/show.blade.php` |
| B3 | `OffboardingService::approve()` created a second pending termination for an employee who had a legacy `offboarding_case_id = NULL` one. | `approve()` finds and adopts the existing unlinked pending termination; only `firstOrCreate`s when none exists. | `OffboardingService.php` |
| B4 | Recovery had two sources of truth — approval inflated `EmployeeAssessLoss.deducted_amount` **and** wrote an allocation row; payroll could then double-count. | Allocation ledger is the only truth. Services stop writing `deducted_amount`; loss `status` flips to `Deducted` only when fully covered / waived, via a guarded `where('status', PENDING)` update. | `AssetRecoveryApprovalService.php`, `AssetRecoveryWaiverService.php` |
| B5 | Worklist "documents expiring" counted `Contract` (client/sales table) by `client_id` — meaningless. | Extracted `App\Support\HrDocumentExpiry`; worklist + compliance both delegate to it; label corrected to "Documents expired or expiring in 90 days". | new `app/Support/HrDocumentExpiry.php`, `HrWorklistController.php`, `HrComplianceController.php` |
| B6 | `hr-lifecycle/show.blade.php` — "Branch transfer" left outside its card wrapper, unbalanced `</div>`s. | Re-wrapped "Branch transfer" in its own `card` / `card-body`. | `resources/views/hr-lifecycle/show.blade.php` |

Tests touched: `HrComplianceExpiryTest`, `HrWorklistBlockersTest`, `AssetRecoveryAllocationStateTest` updated for moved code; new `OffboardingCaseClosureTest`.

## Verify status

- [x] `php artisan test tests/Unit` → 46 passed (sqlite `:memory:`, safe to run here).
- [x] `php -l` clean on every changed PHP file.
- [x] `php artisan view:cache` compiles every blade.
- [ ] Behavioural tests — ~20 of the current "tests" are `file_get_contents` + `assertStringContainsString`; they guard code structure, not behaviour. See "Left to do — E".

---

## Left to do (to "finish completely" before deploy)

### A. Settlement automation — Phase 3  *(built 2026-09-09)*

- [x] `hr_settlement_settings` table + `HrSettlementSetting` model — per-company EOSB policy (Art. 84 month-fractions, Art. 85 resignation fractions, wage basis, leave rules); seeded with KSA statutory defaults, admin-editable at `hr-settlement/settings`.
- [x] `HrSettlementService::computeEndOfService()` — pure, unit-tested EOSB engine (Art. 84 award × Art. 85 fraction / full award / Art. 80 zero).
- [x] `HrSettlementService::suggestedInputs()` auto-derives: monthly wage (from `payroll_employee_setups` per wage basis), service years (`joining_date`→LWD), EOSB, leave encashment (quota × daily wage), approved advance balance (`advance_salaries`), asset recovery (`asset_recovery_allocations` settlement method).
- [x] `hr-settlement/edit` rebuilt — every field shows the system value + an editable override; auto line items tagged `source_type = auto`.
- [x] Pending salary (from the last processed monthly slip → LWD, pro-rata) and notice payment-in-lieu (unserved notice after LWD, employer termination) are auto-derived; both still overridable by Finance.
- [ ] Cross-form auto-wiring pulls RT settlement allocations into the `asset_recovery` suggestion; confirm whether that line should be *locked* rather than a suggestion (business call).
- [ ] **HR/legal sign-off on every policy number before enabling finalised settlements in prod.**

### B. Legal document PDFs on the Speed Logi purple letterhead  *(done 2026-09-09)*

- [x] `company-assets/rt-final-pdf.blade.php` — full **RT-0005-0004** layout on `speedlogi-letterhead.png` (8 sections, Clause 6 cross-reference, tri-party signatures, CR footer, SHA-256).
- [x] `hr-settlement/pdf.blade.php` — full **FC-0111** layout (recoverable / payable tables with auto/manual source column, net summary, verification checklist, four signatures, CR + VAT footer).
- [x] `company-assets/pdf.blade.php` (handover) — recovery clause explicitly labelled `[Clause 6]` and named as the clause the RT form references.
- [x] RT accessories / technical / data-security are structured rows — `asset_return_form_lines` table + `AssetReturnFormLine` model + `AssetReturnService::CHECKLIST`; the certification form renders the 3 grids; the RT PDF renders structured tables (free-text summary kept as fallback).

### C. Role console screens — Phase 2 UX

- [x] Approvals inbox — `hr-lifecycle/approvals` (manager/HR list of `awaiting_approval` cases, approve + reject-with-reason), linked from the worklist.
- [x] **HR-0111** master clearance document — 7-department departmental-clearance grid (expanded `OffboardingService::DEFAULT_TASKS`, Fleet/Warehouse optional) + its own PDF at `hr-lifecycle/offboarding/{case}/clearance-pdf` on the letterhead, with an auto-derived Cleared / Not-yet-cleared decision.
- [x] Lifecycle screen links to the HR Clearance PDF, the Finance settlement worksheet, and shows the access-revoked timestamp.
- [x] Tabbed offboarding console — `hr-lifecycle/offboarding/{case}/console` (Overview / HR Clearance / Finance / Assets / Statutory), linked from the lifecycle screen and the approvals inbox.
- [x] "My resignation" self-service — `hr-lifecycle/my-resignation` (own case status read-only + resignation form when none open); linked in the HR menu for all employees.
- [x] Dedicated IT worklist (`hr-lifecycle/it-worklist`: return certifications + assets still out with departing staff) and Finance worklist (`hr-lifecycle/finance-worklist`: settlements to prepare/finalise + recoveries to approve); linked from the HR worklist.
- [ ] Optional: separation-of-duties on approve (initiator ≠ approver) — deliberately not enforced.

### D. Data / migration safety

- [x] Backfill migration `2026_09_09_200000_backfill_offboarding_case_links.php` — links unambiguous pending `employee_terminations` → `hr_offboarding_cases`, creates a 1:1 case where none exists, logs multi-candidate cases to `hr_lifecycle_events` as `offboarding_backfill_exception`. Idempotent; `down()` reverts only the cases it created.
- [x] Status-casing normalised to lowercase — `2026_09_09_220000_normalize_hr_status_casing` (`asset_assignments.status`, `employee_assess_losses.status`); every raw `'Assigned'`/`'Pending'`/`'Deducted'` reader (7 code sites + 5 blades) converted to the model constants first; migration is mysql-guarded and reversible.

### E. Behavioural tests

- [x] EOSB engine — `HrSettlementEngineTest`: Art. 84 half/full month bands, employer full award vs Art. 80 zero, Art. 85 fractions per service length, fraction boundaries.
- [x] Finish-scope guards — `HrLifecycleFinishScopeTest`: PDFs on letterhead + CR, settlement engine wiring, approvals/HR-0111 routes, backfill migration shape.
- [x] `OffboardingServiceBehaviourTest` (sqlite harness) — request creates one awaiting case + blocks duplicates + no termination; approve creates exactly one termination + the 7-row HR-0111 grid + queues the sync job + rejects a second approve; reject creates none + cancels; adopts a legacy pending termination; completeTask → completion_pending → complete → revert.
- [x] `SettlementAndRecoveryBehaviourTest` — approval cap rejects over-remaining; `deducted_amount` never moves on approval/waiver; partial → `partially_approved` (loss stays pending); full coverage → `approved` + loss `settled`; waiver writes a `waiver` allocation + settles the loss.
- [ ] `completeTermination` gate behavioural test — still source-string only (the controller path needs the full employee/termination schema). Guarded by `TerminationClearanceSafetyTest` + covered end-to-end in the deploy click-through.
- [ ] PDF immutability behavioural test — `generatePdf` early-return + SHA-256 asserted by source; full render check is a deploy-time step.

### F. Deploy prerequisites (execute at deploy time)

- [ ] Confirm HR prod `QUEUE_CONNECTION` — must be `sync` **or** a running `queue:work` worker; otherwise `access_revoked_at` is never set and case-linked terminations cannot complete.
- [ ] Back up the shared DB before the 11 create / ALTER migrations (`2026_09_09_130000` … `230000`); the backfill (`200000`) writes to `employee_terminations` + `hr_offboarding_cases`; `220000` rewrites two status columns (mysql-guarded, reversible).
- [ ] After migrating, review `hr_lifecycle_events` for `offboarding_backfill_exception` rows and link those cases by hand.
- [ ] `php artisan db:seed` is not required — `hr_settlement_settings` falls back to statutory defaults until an admin saves the policy screen.
- [ ] Commit the whole stack (all uncommitted on `main`).
- [ ] Post-deploy: `php artisan view:cache` + `config:cache` + `systemctl reload php8.4-fpm` (the `-hr` pool).
- [ ] Post-deploy click-through: 1 termination → Complete, 1 resignation, 1 reject, 1 asset write-off → recovery → settlement.

---

## Decisions needed from HR / legal (the engine is built with statutory defaults; sign off before go-live)

**Authority in the system (decided 2026-09-09):** editing the settlement policy
(`/account/hr-settlement/settings`) is **Administrator role only** — that is the
"sign-off" gate; give the CEO / MD the Administrator role. Preparing and
finalising individual settlements stays at Finance Manager level
(`manage_finance_clearance`), approving offboarding at HR Manager level
(`edit_employees`). All assigned per-role in the Role Permissions grid.

- [ ] Confirm or adjust the `hr_settlement_settings` figures: Art. 84 month-fractions (0.5 / 1.0), Art. 85 resignation fractions (0 / ⅓ / ⅔ / full), wage basis (`gross` by default).
- [ ] Partial-year handling and any minimum-service threshold (engine currently pro-rates every partial year with no floor).
- [ ] Unused-leave encashment basis (default: total leave quota − approved leaves this year, at wage ÷ 30).
- [ ] Final legal wording for handover Clause 6, the FC / RT / HR-0111 declarations, and the enforceable wage-deduction limit.
- [ ] Validate against 2–3 already-paid settlements before enabling **Finalise** in prod.
- [x] FC PDF VAT — now pulled from **Company → default address → tax number** when set, and cleanly omitted until then. Admin adds it in company settings later; no code change needed.
- [x] Green "Speed HR" sub-brand rejected — all forms use the Speed Logi purple letterhead + CR 7038950171. *(confirmed 2026-09-09)*

## Release gates (from the implementation plan)

- [ ] Behavioural tests pass on an isolated database.
- [ ] Migration / backfill exception report approved by HR.
- [ ] Role-based acceptance tests for Employee, Manager, HR, IT, Finance, branch-scoped users.
- [ ] Legal wording + settlement policy approved.
- [ ] Final PDFs, hashes, recovery allocations, failed-sync retries verified.
- [ ] Legacy routes cannot create duplicate or independent offboarding records.

## Follow-up deploy — driver payroll removed from HR (2026-09-09)

`deploy-backups/20260909_222255_payroll-drivers-removed/pre.tgz`. Driver payroll
lives entirely in DMS (`dms.payroll` / `DriverPayrollController`); the HR payroll
module is now employees-only.

- `PayrollController`: dropped `Driver` / `PayrollDriverSetup` imports, `$this->drivers`
  / `$this->driverSetups` loads, `driver` eager-loads, `payee_type in:employee,driver`
  → `in:employee`, `driver_id` handling, and the `storeDriverSetup` / `updateDriverSetup`
  / `destroyDriverSetup` / `resolveDriverPayrollStatusLabel` methods.
- `routes/web.php`: removed the 3 `payroll.salary-setups.drivers.*` routes.
- `payroll/index.blade.php`: Payee-type dropdown → hidden `employee`; driver select,
  the (already-commented) Driver Salary Setup card, and the driver JS branch removed.
- `GenerateMonthlyPayrollSlips` command: `generateForDrivers()` + its `PayrollDriverSetup`
  use removed — the daily 00:10 run now only generates employee slips.
- **Kept untouched** (shared with DMS): `payroll_driver_setups` table, `PayrollDriverSetup`
  / `Driver` models, `SalarySlip.driver()` relation + `payee_name` accessor, the shared
  Driver / Driver Types / Businesses CRUD.
- Verified: `salary-setups.drivers` routes gone, `/account/payroll` 302, caches rebuilt
  as `usman_ilab_sa`, 0 root-owned cache files, log clean.
