# HR Lifecycle and Offboarding Implementation Plan

## Purpose

Make the modern HR lifecycle layer the canonical workflow for onboarding, transfer, resignation, termination, clearance, settlement, access revocation, and document retention.

`HrOffboardingCase` owns the process. `EmployeeTermination` remains the legal employment record, created and driven only by an approved case. No legacy controller may independently create, complete, or revoke an exit.

## Architecture Decision

Adopt the modern HR lifecycle layer.

- `HrOffboardingCase` is the single offboarding workflow record.
- `EmployeeTermination` has a one-to-one relationship with its case and retains legal dates, exit reason, and final status.
- IT, Finance, access revocation, handover, statutory work, and record retention are case tasks/categories.
- Existing pending-termination and clearance routes become redirects or read-only history after migration.
- `HrLifecycleController::syncCaseCompletion()` must not deactivate an employee merely because generic tasks are checked off.

## Non-Negotiable Invariants

1. An employee has at most one open offboarding case.
2. An approved case has exactly one `EmployeeTermination` record.
3. Completion requires approved case state, all required tasks, a finalised settlement, and resolved access-revocation work.
4. IT and Finance clearance may be issued only against the current pending legal termination.
5. Every mutation is authorized, branch-scoped, auditable, and reversible where business-safe.
6. Finalised PDFs are immutable snapshots; only draft documents may regenerate from live data.
7. A financial obligation may be allocated once only. Payroll and final settlement cannot recover the same amount twice.

## Current-State Findings

- Finance clearance blocks only when both an advance and asset loss remain; it must block when either remains.
- Clearance lookup uses the latest termination without status filtering, so historic or reverted exits can be selected.
- Classic and modern paths each create pending terminations. Modern task completion can deactivate and sync an employee independently of clearance gates.
- Head Office authority uses a literal branch ID instead of an organizational access rule.
- Employee asset-custody return records a request but does not close an `AssetAssignment`; it cannot clear IT custody.
- Linked DMS/DOBS synchronization logs and suppresses failures. A local database transaction cannot guarantee cross-system completion by itself.
- The existing asset return PDF is a static four-section form. The RT inspection form must be an actual stateful workflow record, not only a template change.

## Case States

### Offboarding case

`approval_status`: `awaiting_approval`, `approved`, `rejected`.

`status`: `open`, `completion_pending`, `completed`, `reverted`, `cancelled`.

`settlement_status`: `not_started`, `draft`, `finalised`, `voided`.

### Legal termination

`EmployeeTermination.status`: `pending`, `completed`, `reverted`, `rejected`.

The case owns lifecycle state. The legal termination follows it and retains clearance metadata for legacy history only.

### Completion gate

Completion is allowed only when the case is approved and open; required tasks are complete or formally waived; no active asset assignment remains; Finance has finalised settlement; linked-system revocation is confirmed or is in a durable visible remediation state; and the actor is authorized.

## Phase 0: Confirm the Target

Approve the modern layer as the only future write path. Do not retain two write-capable offboarding systems.

Canonical flow:

1. Employee submits resignation, or HR/manager starts termination.
2. Manager/HR approves or rejects with reason.
3. Approval atomically creates legal termination, template tasks, timeline event, and document drafts.
4. Each role completes its clearance category.
5. Finance finalises settlement.
6. HR completes offboarding.
7. A durable job revokes DMS/DOBS access and records confirmation or retries failure.
8. Case, settlement, and final documents are retained records.

## Phase 1: Critical Safety and Security Fixes

No schema changes. Ship before structural work.

1. Change Finance outstanding-dues check from `&&` to `||`.
2. Query clearance records only through the current `pending` termination; reject issue actions for missing, completed, reverted, or rejected records.
3. Replace `branch_id == 6` with a named organization-wide HR access helper backed by permissions/configuration. Do not remove intended Head Office authority accidentally.
4. Remove employee deactivation and linked-system syncing from generic lifecycle task completion.
5. Add permission and assignment-scope checks to asset-return, return-PDF, handover-PDF, and serial-state actions. Formal actions must resolve the assignment from the route, never an untrusted request ID.
6. Wrap local multi-write actions in `DB::transaction`: case/termination state, employee state, employee dates, category state, audit event, and outbox job.
7. Dispatch mail and sync only after commit; mail must not decide transaction success.
8. Pre-fill notice dates from resignation/last-working dates. Allow authorized edits only and log them.
9. Add `EmployeeAssessLoss` status constants. Preserve stored status casing until the dedicated data migration.

### Durable linked-system revocation

Create an `hr_system_sync_jobs` outbox record inside the completion transaction. Queue it with `afterCommit()`. Track each system as `pending`, `processing`, `completed`, or `failed`; retry idempotently; surface failures in IT/HR worklists. Do not label an exit fully access-cleared while revocation is unresolved.

### Phase 1 acceptance tests

- Advance only, loss only, both, and neither financial-due conditions.
- Pending-only lookup and issuance rules.
- Cross-branch and organization-scope authorization without a numeric branch ID.
- Generic tasks cannot deactivate an employee.
- Unauthorized users cannot process another employee's assignment.
- Simulated DMS/DOBS sync failure yields a visible, retryable job rather than a silent success.

## Phase 2: Unified Offboarding Workflow

### Schema

Extend `hr_offboarding_cases` with approval fields, approving/rejecting actors/times, manager, settlement state/amount, access-revoked time, immutable company-scoped reference, and completion/reversion metadata.

Add nullable `employee_terminations.offboarding_case_id`, backfill unambiguous records, resolve exceptions with HR, then enforce uniqueness.

Create:

- `HrOffboardingTask` model over `hr_offboarding_tasks`;
- `hr_offboarding_task_templates` with company, exit type, employee type, category, title, owner type, required flag, and sort order;
- `hr_lifecycle_events` with subject employee, event, actor, metadata, and timestamp;
- `hr_system_sync_jobs` for idempotent cross-system work;
- `hr_clearance_forms` and `asset_return_forms` for document drafts/final snapshots.

Task categories: `asset`, `finance`, `access`, `handover`, `statutory`, `documents`, `custom`. Tasks track assignee, due date, completed actor/time, blocked reason, waiver reason, and evidence.

### Canonical service

Create `OffboardingService` as the sole write path for request, approve/reject, task update/waive, category clearance, settlement finalization, completion, and reversion. Lock the employee and active case using `lockForUpdate()` during state changes to prevent concurrent duplicate submissions.

### Screens

1. **My resignation**: employee request, approval state, then read-only clearance and settlement progress.
2. **Approvals inbox**: manager/HR list of awaiting cases with approve/reject reason.
3. **Offboarding console**: Overview, HR Clearance, Finance, Assets, and Statutory tabs.
4. **IT worklist**: asset returns, inspections, and write-off decisions.
5. **Finance worklist**: dues, worksheet, verification, and finalisation.

### Phase 2 acceptance tests

- Concurrent initiation cannot create duplicate open cases/terminations.
- Approval creates exactly one termination and one task set.
- Rejection creates no pending legal termination.
- Task completion cannot bypass assets, settlement, or access gates.
- Every mutation emits one lifecycle event with actor/time and non-sensitive metadata.

## Phase 2.5: Asset Return, Inspection, Write-Off, and Recovery

Employees may submit a return request/evidence. Only IT or an authorized asset custodian may inspect, certify return, waive, write off, or submit a recovery recommendation.

### Formal return

In one transaction: lock assignment and serial; create `AssetAssignmentHistory(returned)`; create/update the RT form; release serial only after IT certification; close assignment; update availability; write a timeline event.

### Waive/write-off

Require reason, evidence, disposition, and authorized actor. Create a distinct `written_off` history action, set serial to `lost`, `damaged`, or `retired`, close assignment, and create a recovery recommendation. Finance must approve it before it becomes allocatable.

### RT asset return form

One `asset_return_forms` record per returned unit with draft/final data for:

1. Asset identification.
2. Custodian and return details.
3. Accessories checklist.
4. Technical inspection.
5. Data/security clearance.
6. Outcome, disposition, recovery recommendation, and final-settlement consent.
7. Declarations.
8. Signatures and clearance state.

Use a company-scoped sequential reference. On finalization persist a structured snapshot, PDF path, SHA-256 hash, actor/time, and signature/evidence references.

### Recovery allocation rule

An RT recommendation is not a deduction. Finance approves recoverable amount. A ledger records allocation through payroll, settlement, waiver, or external payment. Remaining balance derives from allocations, never a vague status.

## Phase 3: Final Settlement and Finance Clearance

### Data and service

Create `hr_settlement_settings`, `hr_finance_clearance_forms`, and structured settlement/allocation rows where reporting/audit requires them. JSON is for frozen document snapshots and flexible observations only; IDs, amounts, state, dates, approvals, and references remain first-class columns.

`HrSettlementService::worksheet(EmployeeTermination $termination)` produces versioned calculations for end-of-service benefit, leave encashment, pending salary, approved advances, approved asset-loss recoveries, manual adjustments, and notice shortfall/payment in lieu. Persist inputs, policy/formula version, overrides/reasons, reviewer, and finalization time.

### EOSB policy control

Do not implement the shorthand "one-third month per year". Configure the Article 84 base award, then apply the applicable Article 85 resignation entitlement fraction and any approved statutory/contractual exception. Service boundaries, partial years, wage basis, minimum duration, and leave encashment rules require HR/legal policy sign-off before production use.

### FC finance-clearance form

Include identity/employment/salary data; recoverable and payable lines; net settlement; payment/recovery method; payroll-stop/statutory checks; issuance/finalization; and signatures. Auto-derived rows are identified and cannot be silently overwritten. Manual rows record actor, date, and reason.

### Phase 3 acceptance tests

- Financial obligation allocation cannot exceed remaining balance.
- Payroll and settlement cannot recover the same loss/advance twice.
- Net equals payable total minus recoverable total.
- Finalization locks inputs, policy/formula version, and document snapshot.

## Phase 4: Onboarding and Probation

Candidate handoff creates employee and onboarding case in one transaction. Onboarding uses company/employee-type/department templates and deep-links to assets, access, documents, payroll, insurance, and manager confirmation.

Configure probation duration, set `probation_end_date` from joining date, and add a 30-day due worklist/report: confirm, extend with reason, or fail probation through canonical offboarding. Notify manager two weeks before review.

## Phase 5: Compliance, Timeline, and Worklist

Create an expiry dashboard for Iqama, passport, licence, contract, and insurance: expired, 0-30, 31-60, and 61-90 day bands with filters. Existing reminder jobs remain notification mechanisms.

Render lifecycle events on employee profile: hire, onboarding, transfer, leave/payroll flags, request/approval, task completion/waiver, clearance, settlement, access sync, completion/reversion, and final documents.

Make HR Worklist the first HR menu entry: approvals, blockers, IT/Finance queues, onboarding, probation, expiry, failed sync, attendance exceptions, and payroll preflight.

## Phase 6: Consistency and Connected Workflows

1. Add `AuthorizesHrAccess` with canonical view/manage/organization-scope helpers.
2. Wrap local multi-write HR actions in transactions: leave, attendance, air ticket, advance, insurance, appreciation, holiday, department, and designation.
3. Send notifications and external effects after commit through durable jobs where needed.
4. Create payroll flags for approved unpaid leave.
5. Surface attendance exceptions in HR Worklist and payroll preflight.
6. Extend transfer application to handle payroll setup, asset reassignment/return decision, and linked-system branch synchronization.
7. Normalize status casing through a dedicated migration only after all readers accept constants and historical data is assessed.

## Document Standards

Use one Speed Logi legal identity and letterhead for HR clearance, Finance settlement, and asset forms. Share letter-size background placement, safe-area margins, transparent table cells over the watermark, legal footer, CR, finance VAT details where approved, references, and signature treatment.

- **HR Clearance and Offboarding**: departmental grid, handover, statutory actions, leave/entitlements, declaration, decision, signatures.
- **Finance Clearance and Dues Settlement**: recovery/payable worksheet, net summary, verification, status, signatures.
- **Asset Return, Inspection and Clearance**: per-unit receipt, accessories, technical/data inspection, disposition, recovery authorization, signatures.

The asset handover agreement must contain the numbered recovery clause referenced by RT. Payroll/final-settlement recovery requires applicable approval and signed acknowledgement; final legal wording and deduction limits require company counsel approval.

## Migration and Rollout

1. Deploy Phase 1 first.
2. Add Phase 2 schema additively.
3. Backfill only unambiguous active legacy terminations. Produce an HR exception report; never guess duplicate linkage.
4. Release canonical screens behind feature flag while legacy history is read-only.
5. Acceptance-test on a database copy before enabling new writes.
6. Enable canonical creation after backfill sign-off.
7. Redirect old write routes for one release; remove only after retained-document verification.
8. Do not use destructive broad rollback against shared production data. Disable flags/routes first and retain additive records unless an approved retention decision says otherwise.

## Verification and Deployment

Add focused tests for financial gates, pending-only clearance, authorization, concurrency, state transitions, asset lifecycle, recovery allocation, settlement arithmetic/versioning, PDF immutability/reference uniqueness, sync retries, and legacy redirect/no-duplicate behavior.

Before release: back up the shared database and verify restore access; confirm relevant HR roles; run named migrations and focused tests; rebuild config/views; restart queue workers; manually test each role; verify final PDF retrieval and hashes; verify sync job processing and employee timeline; confirm legacy routes cannot create new cases.

## Done Definition

Every employee exit has one visible case, one legal termination record, role-owned tasks, finalised/auditable settlement, deliberate disposition for each assigned asset, durable access-revocation confirmation, immutable signed documents, and a complete timeline. No legacy endpoint can independently create or complete an offboarding process.
