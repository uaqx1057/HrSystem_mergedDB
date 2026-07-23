<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDetails;
use App\Models\HrLeaveApproverDelegation;
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
}
