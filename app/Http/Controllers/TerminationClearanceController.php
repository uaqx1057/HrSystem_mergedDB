<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Mail\TerminationIssueClearedMail;
use App\Mail\TerminationReminderMail;
use App\Models\AdvanceSalary;
use App\Models\AssetAssignment;
use App\Models\EmployeeTermination;
use App\Models\User;
use App\Scopes\ActiveScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TerminationClearanceController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.pendingTermination';
    }

    private function findTermination($id)
    {
        $employee = User::withoutGlobalScope(ActiveScope::class)->findOrFail($id);

        $termination = EmployeeTermination::where('user_id', $id)->latest('id')->first();

        if (!$termination) {
            abort(404);
        }

        return [$employee, $termination];
    }

    private function checkPermission($permissionName, User $employee)
    {
        $permission = user()->permission($permissionName);

        abort_403(!(
            $permission == 'all'
            || ($permission == 'branch' && !is_null(user()->branch_id) && $employee->branch_id == user()->branch_id)
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | IT Department
    |--------------------------------------------------------------------------
    */

    public function itView($id)
    {
        [$employee, $termination] = $this->findTermination($id);

        $this->checkPermission('manage_it_clearance', $employee);

        $this->employee = $employee;
        $this->termination = $termination;
        $this->assignedAssets = AssetAssignment::with('asset')
            ->where('employee_id', $employee->id)
            ->where('status', 'Assigned')
            ->get();

        if (request()->ajax()) {
            $html = view('employees.ajax.it-clearance', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'IT Clearance']);
        }

        $this->view = 'employees.ajax.it-clearance';
        return view('employees.create', $this->data);
    }

    public function itSendReminder($id)
    {
        [$employee, $termination] = $this->findTermination($id);

        $this->checkPermission('manage_it_clearance', $employee);

        $pendingAssets = AssetAssignment::with('asset')
            ->where('employee_id', $employee->id)
            ->where('status', 'Assigned')
            ->get();

        if ($pendingAssets->isEmpty()) {
            return Reply::error('All assets have already been returned.');
        }

        $assetNames = $pendingAssets->map(function ($assignment) {
            return $assignment->asset->name ?? 'Asset';
        })->implode(', ');

        $reasonMessage = 'You still have the following company asset(s) pending return: ' . $assetNames . '. Please return them at the earliest to proceed with your IT clearance.';

        $recipients = collect([$employee])
            ->merge(User::allAdmins($employee->company_id))
            ->unique('email');

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new TerminationReminderMail($termination, 'IT', $reasonMessage));
            } catch (\Exception $e) {
                Log::error('Failed to send IT clearance reminder email: ' . $e->getMessage());
            }
        }

        $termination->it_reminder_sent_at = now();
        $termination->save();

        return Reply::success('Reminder sent successfully.');
    }

    public function itIssueClearance($id)
    {
        [$employee, $termination] = $this->findTermination($id);

        $this->checkPermission('manage_it_clearance', $employee);

        $pendingAssets = AssetAssignment::where('employee_id', $employee->id)
            ->where('status', 'Assigned')
            ->exists();

        if ($pendingAssets) {
            return Reply::error('Asset return is pending.');
        }

        $termination->it_clearance_status = EmployeeTermination::CLEARANCE_ISSUED;
        $termination->it_clearance_issued_by = user()->id;
        $termination->it_clearance_issued_at = now();
        $termination->save();

        $recipients = collect(User::usersWithPermission('manage_termination_employees', $employee->company_id))
            ->whereNotNull('email')
            ->unique('email');

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new TerminationIssueClearedMail($termination, 'IT'));
            } catch (\Exception $e) {
                Log::error('Failed to send IT issue cleared email: ' . $e->getMessage());
            }
        }

        return Reply::success('IT clearance letter generated successfully.');
    }

    public function itClearanceLetterPdf($id)
    {
        [$employee, $termination] = $this->findTermination($id);

        $this->checkPermission('manage_it_clearance', $employee);

        if ($termination->it_clearance_status !== EmployeeTermination::CLEARANCE_ISSUED) {
            return redirect()->back()->with('error', 'Asset return is pending.');
        }

        $pdf = Pdf::loadView('employees.pdf.it-clearance-letter', compact('employee', 'termination'));

        return $pdf->download('it-clearance-' . $employee->id . '.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | Finance Department
    |--------------------------------------------------------------------------
    */

    public function financeView($id)
    {
        [$employee, $termination] = $this->findTermination($id);

        $this->checkPermission('manage_finance_clearance', $employee);

        $this->employee = $employee;
        $this->termination = $termination;
        $this->pendingAdvances = AdvanceSalary::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->get();

        if (request()->ajax()) {
            $html = view('employees.ajax.finance-clearance', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'Finance Clearance']);
        }

        $this->view = 'employees.ajax.finance-clearance';
        return view('employees.create', $this->data);
    }

    public function financeSendReminder($id)
    {
        [$employee, $termination] = $this->findTermination($id);

        $this->checkPermission('manage_finance_clearance', $employee);

        $pendingAdvances = AdvanceSalary::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->get();

        if ($pendingAdvances->isEmpty()) {
            return Reply::error('No pending dues found.');
        }

        $totalDue = $pendingAdvances->sum('advance_salary');

        $reasonMessage = 'You have an outstanding advance salary / due amount of ' . number_format($totalDue, 2) . '. Please clear the pending dues at the earliest to proceed with your Finance clearance.';

        $recipients = collect([$employee])
            ->merge(User::allAdmins($employee->company_id))
            ->unique('email');

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new TerminationReminderMail($termination, 'Finance', $reasonMessage));
            } catch (\Exception $e) {
                Log::error('Failed to send Finance clearance reminder email: ' . $e->getMessage());
            }
        }

        $termination->finance_reminder_sent_at = now();
        $termination->save();

        return Reply::success('Reminder sent successfully.');
    }

    public function financeIssueClearance($id)
    {
        [$employee, $termination] = $this->findTermination($id);

        $this->checkPermission('manage_finance_clearance', $employee);

        $pendingDues = AdvanceSalary::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($pendingDues) {
            return Reply::error('Financial clearance is pending.');
        }

        $termination->finance_clearance_status = EmployeeTermination::CLEARANCE_ISSUED;
        $termination->finance_clearance_issued_by = user()->id;
        $termination->finance_clearance_issued_at = now();
        $termination->save();

        $recipients = collect(User::usersWithPermission('manage_termination_employees', $employee->company_id))
            ->whereNotNull('email')
            ->unique('email');

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new TerminationIssueClearedMail($termination, 'Finance'));
            } catch (\Exception $e) {
                Log::error('Failed to send Finance issue cleared email: ' . $e->getMessage());
            }
        }

        return Reply::success('Finance clearance letter generated successfully.');
    }

    public function financeClearanceLetterPdf($id)
    {
        [$employee, $termination] = $this->findTermination($id);

        $this->checkPermission('manage_finance_clearance', $employee);

        if ($termination->finance_clearance_status !== EmployeeTermination::CLEARANCE_ISSUED) {
            return redirect()->back()->with('error', 'Financial clearance is pending.');
        }

        $pdf = Pdf::loadView('employees.pdf.finance-clearance-letter', compact('employee', 'termination'));

        return $pdf->download('finance-clearance-' . $employee->id . '.pdf');
    }
}
