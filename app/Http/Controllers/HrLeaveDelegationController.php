<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDetails;
use App\Models\HrLeaveApproverDelegation;
use App\Models\Leave;
use App\Models\User;
use Illuminate\Http\Request;

class HrLeaveDelegationController extends AccountBaseController
{
    public function index()
    {
        $this->delegations = HrLeaveApproverDelegation::with('delegate')->where('manager_id', user()->id)->latest()->get();
        $this->isManager = EmployeeDetails::where('reporting_to', user()->id)->exists();
        $this->employees = User::allEmployees(user()->id, false, 'all', user()->company_id);
        return view('hr-leave-delegations.index', $this->data);
    }

    public function store(Request $request)
    {
        abort_403(!EmployeeDetails::where('reporting_to', user()->id)->exists());
        $data = $request->validate(['delegate_id' => 'required|exists:users,id', 'starts_at' => 'required|date|before_or_equal:ends_at', 'ends_at' => 'required|date']);
        abort_403((int) $data['delegate_id'] === (int) user()->id || !User::whereKey($data['delegate_id'])->where('company_id', user()->company_id)->exists());
        HrLeaveApproverDelegation::create($data + ['company_id' => user()->company_id, 'manager_id' => user()->id, 'created_by' => user()->id, 'is_active' => true]);
        return redirect()->route('hr-leave-delegations.index')->with('success', 'Leave approval delegation saved.');
    }

    public function revoke(HrLeaveApproverDelegation $delegation)
    {
        abort_403((int) $delegation->manager_id !== (int) user()->id);
        $delegation->update(['is_active' => false]);
        return redirect()->route('hr-leave-delegations.index')->with('success', 'Leave approval delegation revoked.');
    }

    public function teamCalendar(Request $request)
    {
        $managerIds = HrLeaveApproverDelegation::query()
            ->where('delegate_id', user()->id)->where('is_active', true)
            ->whereDate('starts_at', '<=', today())->whereDate('ends_at', '>=', today())
            ->pluck('manager_id')->push(user()->id)->unique();
        $employeeIds = EmployeeDetails::whereIn('reporting_to', $managerIds)->pluck('user_id');
        abort_403($employeeIds->isEmpty());

        if ($request->has(['start', 'end'])) {
            return Leave::with(['user', 'type'])->whereIn('user_id', $employeeIds)
                ->whereBetween('leave_date', [$request->input('start'), $request->input('end')])
                ->whereIn('status', ['pending', 'approved'])
                ->get()->map(fn ($leave) => [
                    'id' => $leave->id,
                    'title' => $leave->user->name . ' — ' . $leave->type->type_name . ' (' . $leave->status . ')',
                    'start' => $leave->leave_date->toDateString(),
                    'end' => $leave->leave_date->copy()->addDay()->toDateString(),
                    'color' => $leave->status === 'approved' ? $leave->type->color : '#f0ad4e',
                ])->values();
        }

        return view('hr-leave-delegations.team-calendar');
    }
}
