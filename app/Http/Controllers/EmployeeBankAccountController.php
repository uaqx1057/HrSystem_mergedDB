<?php

namespace App\Http\Controllers;

use App\DataTables\EmployeeBankAccountDataTable;
use App\Helper\Reply;
use App\Http\Requests\EmployeeBankAccount\StoreEmployeeBankAccount;
use App\Http\Requests\EmployeeBankAccount\UpdateEmployeeBankAccount;
use App\Models\EmployeeBankAccount;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeBankAccountController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.employeebankaccount');
    }

    public function index(EmployeeBankAccountDataTable $dataTable)
    {
        $this->viewPermission = user()->permission('view_employee_bank_account');
        abort_403(!in_array($this->viewPermission, ['all', 'added', 'owned', 'both', 'branch']));

       if(in_array($this->viewPermission, ['all','branch']) ){
            if($this->viewPermission == 'branch' && hr_has_all_branch_access('employee_bank_accounts')){
                $employeePermission = 'all';
            } else{
                $employeePermission = $this->viewPermission;
            }
        } else{
            $employeePermission = null;
        }
        $this->employees = User::allEmployees(null, true, $employeePermission);

        return $dataTable->render('employee-bank-accounts.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_employee_bank_account');
        abort_403(!in_array($this->addPermission, ['all','added', 'branch']));

        if(in_array($this->addPermission, ['all','branch']) ){
            if($this->addPermission == 'branch' && hr_has_all_branch_access('employee_bank_accounts')){
                $employeePermission = 'all';
            } else{
                $employeePermission = $this->addPermission;
            }
        } else{
            $employeePermission = null;
        }
        $this->employees = User::allEmployees(null, true, $employeePermission);

        if (request()->ajax()) {
            $html = view('employee-bank-accounts.ajax.create', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'employee-bank-accounts.ajax.create';

        return view('employee-bank-accounts.create', $this->data);
    }

    public function store(StoreEmployeeBankAccount $request)
    {
        $addPermission = user()->permission('add_employee_bank_account');
        abort_403(!in_array($addPermission, ['all','added', 'branch']));

        $account = new EmployeeBankAccount();
        $account->employee_id = $request->employee_id;
        $account->bank_name = $request->bank_name;
        $account->iban_number = $request->iban_number;
        $account->account_number = $request->account_number;
        $account->swift_code = $request->swift_code;
        $account->is_main_account = (bool) $request->is_main_account;
        $account->added_by = user()->id;
        $account->save();

        if ($account->is_main_account) {
            EmployeeBankAccount::where('employee_id', $account->employee_id)
                ->where('id', '!=', $account->id)
                ->update(['is_main_account' => false]);
        }

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('employee-bank-accounts.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function show($id)
    {
        $viewPermission = user()->permission('view_employee_bank_account');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both', 'branch']));

        $this->account = EmployeeBankAccount::with('employee')->findOrFail($id);

        if ($this->canAccessRecord($this->account, $viewPermission)) {
            if (request()->ajax()) {
                $html = view('employee-bank-accounts.ajax.show', $this->data)->render();

                return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
            }

            $this->view = 'employee-bank-accounts.ajax.show';

            return view('employee-bank-accounts.create', $this->data);
        }

        abort(403);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_employee_bank_account');
        abort_403(!in_array($this->editPermission, ['all', 'added', 'owned', 'both', 'branch']));

        $this->account = EmployeeBankAccount::findOrFail($id);

        if (!$this->canManageRecord($this->account, $this->editPermission)) {
            abort(403);
        }

        if(in_array($this->editPermission, ['all','branch']) ){
            if($this->editPermission == 'branch' && hr_has_all_branch_access('employee_bank_accounts')){
                $employeePermission = 'all';
            } else{
                $employeePermission = $this->editPermission;
            }
        } else{
            $employeePermission = null;
        }
        $this->employees = User::allEmployees(null, true, $employeePermission);

        if (request()->ajax()) {
            $html = view('employee-bank-accounts.ajax.edit', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'employee-bank-accounts.ajax.edit';

        return view('employee-bank-accounts.create', $this->data);
    }

    public function update(UpdateEmployeeBankAccount $request, $id)
    {
        $editPermission = user()->permission('edit_employee_bank_account');
        abort_403(!in_array($editPermission, ['all', 'added', 'owned', 'both', 'branch']));

        $account = EmployeeBankAccount::findOrFail($id);

        if (!$this->canManageRecord($account, $editPermission)) {
            abort(403);
        }

        $account->employee_id = $request->employee_id;
        $account->bank_name = $request->bank_name;
        $account->iban_number = $request->iban_number;
        $account->account_number = $request->account_number;
        $account->swift_code = $request->swift_code;
        $account->is_main_account = (bool) $request->is_main_account ?? false;
        $account->save();

        if ($account->is_main_account) {
            EmployeeBankAccount::where('employee_id', $account->employee_id)
                ->where('id', '!=', $account->id)
                ->update(['is_main_account' => false]);
        }

        $redirectUrl = route('employee-bank-accounts.index');

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_employee_bank_account');
        abort_403(!in_array($deletePermission, ['all', 'added', 'owned', 'both', 'branch']));

        $account = EmployeeBankAccount::findOrFail($id);

        if (!$this->canManageRecord($account, $deletePermission)) {
            abort(403);
        }

        $account->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function applyQuickAction(Request $request)
    {
        $deletePermission = user()->permission('delete_employee_bank_account');
        abort_403(!in_array($deletePermission, ['all', 'added', 'owned', 'both', 'branch']));

        if ($request->action_type === 'delete') {
            $this->deleteRecords($request);

            return Reply::success(__('messages.deleteSuccess'));
        }

        return Reply::error(__('messages.selectAction'));
    }

    protected function deleteRecords($request)
    {
        $rowIds = explode(',', $request->row_ids);

        if (($key = array_search('on', $rowIds)) !== false) {
            unset($rowIds[$key]);
        }

        $accounts = EmployeeBankAccount::whereIn('id', $rowIds)->get();

        foreach ($accounts as $account) {
            if ($this->canManageRecord($account, user()->permission('delete_employee_bank_account'))) {
                $account->delete();
            }
        }
    }

    protected function canAccessRecord(EmployeeBankAccount $account, $permission): bool
    {
        if ($permission === 'all' || ($permission === 'branch' && hr_has_all_branch_access('employee_bank_accounts'))) {
            return true;
        }

        if ($permission === 'added') {
            return $account->added_by == user()->id;
        }

        if ($permission === 'owned') {
            return $account->employee_id == user()->id;
        }

        if ($permission === 'both') {
            return $account->employee_id == user()->id || $account->added_by == user()->id;
        }

        if ($permission === 'branch' && $account->employee->branch_id == user()->branch_id) {
            return true;
        }

        return false;
    }

    protected function canManageRecord(EmployeeBankAccount $account, $permission): bool
    {
        if ($permission === 'all' || ($permission === 'branch' && hr_has_all_branch_access('employee_bank_accounts'))) {
            return true;
        }

        if ($permission === 'added' && $account->added_by == user()->id) {
            return true;
        }

        if ($permission === 'owned' && $account->employee_id == user()->id) {
            return true;
        }
        if ($permission == 'both' && (user()->id == $account->added_by || user()->id == $account->employee_id)){
            return true;
        }

        if ($permission === 'branch' && $account->employee->branch_id == user()->branch_id) {
            return true;
        }

        return false;

    }
}
