<?php

namespace App\Http\Controllers;

use App\DataTables\BranchesDataTable;
use App\Helper\Reply;
use App\Http\Requests\Admin\Branch\StoreRequest;
use App\Models\{Driver, DriverType, Branch};
use App\Traits\ImportExcel;
use Illuminate\Http\Request;
use App\Helper\Files;
use App\Http\Requests\Admin\Branch\UpdateRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BranchController extends AccountBaseController
{
    use ImportExcel;

    public function __construct(private BranchesDataTable $branchesDataTable)
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.branches';
        $this->middleware(function ($request, $next) {
            // abort_403(!in_array('branches', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $viewPermission = user()->permission('view_branches');
        abort_403(!in_array($viewPermission, ['all']));

        return $this->branchesDataTable->render('branches.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $addPermission = user()->permission('add_branches');
        abort_403(!in_array($addPermission, ['all', 'added']));

        $this->pageTitle = __('app.addBranch');
        $this->countries = countries();
        $this->view = 'branches.ajax.create';
        $this->driver_types = DriverType::all();

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('branches.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $addPermission = user()->permission('add_branches');
        abort_403(!in_array($addPermission, ['all', 'added']));
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $validated['registration_date'] = Carbon::createFromFormat($this->company->date_format, $request->registration_date)->format('Y-m-d');

            $branch = Branch::create($validated);
            if (isset($validated['driver_ids'])) {
                Driver::whereIn('id', $validated['driver_ids'])->update(['branch_id' => $branch->id]);
            }

            DB::commit();
        } catch (\Exception $e) {
            logger($e->getMessage());
            DB::rollback();

            return Reply::error('Some error occurred when inserting the data. Please try again or contact support '. $e->getMessage());
        }


        if (request()->add_more == 'true') {
            $html = $this->create();

            return Reply::successWithData(__('messages.recordSaved'), ['html' => $html, 'add_more' => true]);
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('branches.index')]);
    }

    public function ajaxLoadBranches(Request $request)
    {
        $search = $request->search;

        $branches = Branch::orderby('name')
            ->select('id', 'name')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->take(20)
            ->get();

        $response = array();

        foreach ($branches as $branch) {

            $response[] = array(
                'id' => $branch->id,
                'text' => $branch->name
            );

        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Branch $branch)
    {
        $this->editPermission = user()->permission('edit_branches');
        abort_403(!($this->editPermission == 'all'));

        $this->pageTitle = __('app.update');
        $this->branch = $branch;
        $this->view = 'branches.ajax.edit';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);

            return view($this->view, $this->data);
        }

        return view('drivers.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Branch $branch)
    {
        $this->editPermission = user()->permission('edit_branches');
        abort_403(!($this->editPermission == 'all'));

        $validated = $request->validated();
        $validated['registration_date'] =  Carbon::createFromFormat($this->company->date_format, $request->registration_date)->format('Y-m-d');

        $branch->update($validated);
        $branch->drivers()->update([ 'branch_id' => null ]);
        if (isset($validated['driver_ids'])) {
            Driver::whereIn('id', $validated['driver_ids'])->update(['branch_id' => $branch->id]);
        }

        return Reply::success(__('messages.updateSuccess'));
    }

}
