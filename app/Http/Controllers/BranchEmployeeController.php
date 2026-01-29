<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Admin\BranchEmployee\StoreRequest;
use App\Models\Branch;
use App\Models\Driver;
use App\Models\Business;
use App\Models\User;
use App\Traits\ImportExcel;
use Illuminate\Support\Facades\DB;

class BranchEmployeeController extends AccountBaseController
{
    use ImportExcel;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.drivers';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('employees', $this->user->modules));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(User $employee)
    {
        
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(User $employee)
    {
        $addPermission = $employee->permission('link_to_branch');
        abort_403(!in_array($addPermission, ['all', 'owned', 'both']));

        $this->pageTitle = __('app.addBranch');
        $this->employee = $employee;
        $this->view = 'employees.branches.ajax.create';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('employees.branches.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request, User $employee)
    {
        $linkBranchPermission = $employee->permission('link_to_branch');
        abort_403(!in_array($linkBranchPermission, ['all', 'owned', 'both']));

        if ($linkBranchPermission !== 'all' && count($request->branch_ids) > 1)
            return Reply::error(__('messages.onlyOneBranchCanBeLinked'));

        if ($employee->branches()->whereIn('branch_employee.branch_id', $request->branch_ids)->exists()) {
            return Reply::error(__('messages.branchExists'));
        }

        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $employee->branches()->attach($validated['branch_ids']);

            DB::commit();
        } catch (\Exception $e) {
            logger($e->getMessage());
            DB::rollback();

            return Reply::error('Some error occurred when inserting the data. Please try again or contact support '. $e->getMessage());
        }

        if (request()->add_more == 'true') {
            $html = $this->create($employee);

            return Reply::successWithData(__('messages.recordSaved'), ['html' => $html, 'add_more' => true]);
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('employees.show', $employee->id) . '?tab=link-branch']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
      
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Driver $driver, Business $business)
    {
       
    }

    /**
     * Update the specified resource in storage.
     */
    public function update()
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $employee, Branch $branch)
    {
        $linkToBranchPermission = $employee->permission('link_to_branch');
        abort_403(!(in_array($linkToBranchPermission, [ 'all', 'owned', 'both' ])));

        $this->linkedDriver = $employee->branches()->detach($branch->id);

        return Reply::success(__('messages.deleteSuccess'));
    }
}
