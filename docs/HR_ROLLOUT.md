# HR lifecycle rollout

## Scope

This release adds HR-only tables and routes. It does not change DMS/DOBS roles, permissions, assets, or payroll data structures. BioTime is explicitly out of scope.

## Before deployment

1. Back up the shared database and confirm restore access.
2. Review `php artisan migrate:status` and ensure the application version includes all `2026_07_23_*` HR migrations.
3. Confirm at least one HR admin has `edit_employees=all` and payroll admins have `add_payroll=all`.
4. Put no manual SQL changes into DMS/DOBS tables.
5. Verify the runtime has the PHP `zip` extension enabled. Laravel bootstrap loads the backup package and cannot run route/test commands without `ZipArchive`.
6. Clear/rebuild Laravel config cache on the deployment host. A local cached config may contain an absolute path from another machine; never copy `bootstrap/cache/config.php` between environments.

## Deploy

```powershell
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan queue:restart
```

## Smoke test

1. HR admin: open `/hr-worklist`, `/hr-lifecycle`, `/hr-compliance`, and `/hr-candidates`.
2. Manager: create a dated leave delegation; verify the delegate can act only for direct reports.
3. Employee: submit an attendance exception, self-service request, and asset acknowledgement.
4. Payroll admin: open `/hr-payroll-preflight`, approve the current period, then create one employee salary slip.
5. Candidate: stage candidate, hand off to employee creation, save employee, verify candidate is converted and onboarding appears.
6. Branch user: verify cross-branch HR records return 403 unless an explicit `hr_access_scopes` entry exists.

## Rollback

Do not run broad destructive rollback on the shared database. If rollback is required, first disable the new HR routes/release, preserve the backup, and roll back only the named HR migrations in reverse order after confirming no production data needs retention. HR tables are additive; retaining them is normally safer than deleting operational records.

## Ongoing operations

- Keep scheduler and queue workers active for document expiry reminders.
- Review payroll preflight before employee payroll generation each period.
- Review expired certifications, pending probation reviews, open offboarding, and asset returns through the HR worklist.
