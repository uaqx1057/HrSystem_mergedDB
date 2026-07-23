<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\HrAccessScope;
use App\Models\User;
use Illuminate\Http\Request;

class HrAccessScopeController extends AccountBaseController
{
    private const MODULES = ['leave', 'attendance', 'shift_schedules', 'payroll', 'insurance', 'air_tickets', 'advance_salaries', 'company_assets', 'employee_bank_accounts'];

    public function index()
    {
        $this->authorizeAdmin();
        $this->scopes = HrAccessScope::with('user', 'grantedBy')->where('company_id', company()->id)->latest()->get();
        $this->employees = User::allEmployees();
        $this->modules = self::MODULES;
        return view('hr-access-scopes.index', $this->data);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();
        $data = $request->validate(['user_id' => 'required|exists:users,id', 'module' => 'required|string|in:' . implode(',', self::MODULES), 'ends_at' => 'nullable|date']);
        abort_403(!User::where('id', $data['user_id'])->where('company_id', company()->id)->exists());
        HrAccessScope::updateOrCreate(['company_id' => company()->id, 'user_id' => $data['user_id'], 'module' => $data['module']], ['scope' => 'all', 'is_active' => true, 'starts_at' => now(), 'ends_at' => $data['ends_at'] ?? null, 'granted_by' => user()->id]);
        return $this->reply($request, 'Cross-branch HR access granted.');
    }

    public function revoke(Request $request, HrAccessScope $scope)
    {
        $this->authorizeAdmin(); abort_403($scope->company_id !== company()->id);
        $scope->update(['is_active' => false, 'ends_at' => now(), 'granted_by' => user()->id]);
        return $this->reply($request, 'Cross-branch HR access revoked.');
    }

    private function authorizeAdmin(): void { abort_403(!in_array('admin', user_roles())); }
    private function reply(Request $request, string $message) { return $request->ajax() ? Reply::success($message) : redirect()->route('hr-access-scopes.index')->with('success', $message); }
}
