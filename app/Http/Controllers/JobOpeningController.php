<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Designation;
use App\Models\HrJobOpening;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\DataTables\JobOpeningDataTable;


class JobOpeningController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Job openings';
        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('manage_job_openings') !== 'all');

            return $next($request);
        });
    }

    public function index(JobOpeningDataTable $dataTable)
    {
        return $dataTable->render('job-openings.index', $this->data);
    }

    public function create()
    {
        $this->departments = Team::where('company_id', user()->company_id)->orderBy('team_name')->get();
        $this->designations = Designation::allDesignations();
        $this->branches = Branch::orderBy('name')->get();

        return view('job-openings.create', $this->data);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['company_id'] = user()->company_id;
        $data['created_by'] = user()->id;
        $data['public_slug'] = Str::random(20);

        HrJobOpening::create($data);

        return redirect()->route('job-openings.index')->with('success', __('messages.recordSaved'));
    }

    public function edit(HrJobOpening $jobOpening)
    {
        abort_403($jobOpening->company_id != user()->company_id);
        $this->jobOpening = $jobOpening;
        $this->departments = Team::where('company_id', user()->company_id)->orderBy('team_name')->get();
        $this->designations = Designation::allDesignations();
        $this->branches = Branch::orderBy('name')->get();
     

        return view('job-openings.edit', $this->data);
    }

    public function update(Request $request, HrJobOpening $jobOpening)
    {
        abort_403($jobOpening->company_id != user()->company_id);
        $jobOpening->update($this->validated($request));

        return redirect()->route('job-openings.index')->with('success', __('messages.updateSuccess'));
    }

    public function destroy(HrJobOpening $jobOpening)
    {
        abort_403($jobOpening->company_id != user()->company_id);
        $jobOpening->delete();

        return \App\Helper\Reply::successWithData(__('messages.deleteSuccess'), []);
    }

    public function toggleStatus(Request $request, HrJobOpening $jobOpening)
    {
        abort_403($jobOpening->company_id != user()->company_id);
        $request->validate(['status' => 'required|in:open,on_hold,closed']);
        $jobOpening->update(['status' => $request->status]);

        return back()->with('success', __('messages.updateSuccess'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'nullable|exists:teams,id',
            'designation_id' => 'nullable|exists:designations,id',
            'branch_id' => 'nullable|exists:branches,id',
            'employment_type' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'positions_count' => 'nullable|integer|min:1',
            'status' => 'nullable|in:open,on_hold,closed',
            'closes_at' => 'nullable|date',
        ]);
    }
}
