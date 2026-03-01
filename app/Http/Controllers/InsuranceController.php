<?php

namespace App\Http\Controllers;

use App\DataTables\InsuranceDataTable;
use App\Helper\Reply;
use App\Http\Requests\Insurance\StoreInsurance;
use App\Http\Requests\Insurance\UpdateInsurance;
use App\Models\Insurance;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class InsuranceController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.insurance');

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('employees', $this->user->modules));

            return $next($request);
        });
    }
    /**
     * @param InsuranceDataTable $dataTable
     * @return mixed|void
     */


    public function index(InsuranceDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_employees');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $this->employees = User::allEmployees();
        return $dataTable->render('insurances.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->employees = User::allEmployees();

        if (request()->ajax()) {
            $html = view('insurances.ajax.create', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'insurances.ajax.create';

        return view('insurances.create', $this->data);
    }

    /**
     * @param StoreInsurance $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreInsurance $request)
    {
        $insurance = new Insurance();
        $insurance->employee_id = $request->employee;
        $insurance->issue_date = $request->issue_date;
        $insurance->expiry_date = $request->expiry_date;
        $insurance->company = $request->company_name;
        $insurance->policy_no = $request->policy_no;
        $insurance->class = $request->class;
        $insurance->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('insurance.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $this->insurance = Insurance::findOrFail($id);

        if (request()->ajax()) {
            $html = view('insurances.ajax.show', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'insurances.ajax.show';

        return view('insurances.create', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->insurance = Insurance::findOrFail($id);
        $this->employees = User::allEmployees();

        if (request()->ajax()) {
            $html = view('insurances.ajax.edit', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'insurances.ajax.edit';

        return view('insurances.create', $this->data);
    }

    /**
     * @param UpdateInsurance $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateInsurance $request, $id)
    {
        $editDepartment = user()->permission('edit_employees');
        abort_403($editDepartment != 'all');

        $insurance = Insurance::findOrFail($id);
        $insurance->employee_id = $request->employee;
        $insurance->issue_date = $request->issue_date;
        $insurance->expiry_date = $request->expiry_date;
        $insurance->company = $request->company_name;
        $insurance->policy_no = $request->policy_no;
        $insurance->class = $request->class;
        $insurance->save();

        $redirectUrl = route('insurance.index');

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_employees');
        abort_403($deletePermission != 'all');

        Insurance::findOrFail($id)->delete();

        $redirectUrl = route('insurance.index');

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => $redirectUrl]);
    }

        public function applyQuickAction(Request $request)
    {
        $ids = explode(',', $request->row_ids);

        if ($request->action_type === 'delete') {
            Insurance::whereIn('id', $ids)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Records deleted successfully.'
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid action.']);
    }
}
