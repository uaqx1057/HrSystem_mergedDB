<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyAsset\StoreAssignRequest;
use App\Mail\AssetLossDeductionMail;
use App\Models\AssetAssignment;
use App\Models\AssetAssignmentHistory;
use App\Models\Branch;
use App\Models\CompanyAsset;
use App\Helper\Reply;
use App\Http\Controllers\Controller;
use App\Models\CompanyAssetSerial;
use App\Models\Department;
use App\Models\EmployeeAssessLoss;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf; // This is the Facade
use Illuminate\Http\Request;
use App\DataTables\CompanyAssetDataTable;
use App\Http\Requests\CompanyAsset\StoreRequest;
use App\Http\Requests\CompanyAsset\UpdateRequest;
use App\Mail\AssetAssignedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CompanyAssetController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.companyAssets');
        // $this->middleware(function ($request, $next) {
        //     abort_403(!in_array('employees', $this->user->modules));

        //     $assignRole = user()->roles->pluck('name')->toArray();
        //     abort_403(!in_array('admin', $assignRole));
        //     return $next($request);
        // });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(CompanyAssetDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_company_assets');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both','branch']));
        $this->assignRole = user()->roles->pluck('name')->toArray();

        return $dataTable->render('company-assets.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_company_assets');
        abort_403(!in_array($this->addPermission, ['all', 'branch']));

        $this->departments = Department::orderBy('name')->get();
        $this->branches = Branch::latest()->get();

        if (request()->ajax()) {
            $html = view('company-assets.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'company-assets.ajax.create';
        return view('company-assets.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $viewPermission = user()->permission('add_company_assets');
        abort_403(!in_array($viewPermission, ['all', 'branch']));

        $asset = new CompanyAsset();
        $asset->name = $request->name;
        $asset->catalog = $request->catalog ?? '';
        $asset->sku_no = $request->sku_no ?? '';
        $asset->type = $request->type ?? '';
        $asset->brand = $request->brand ?? '';
        $asset->department_id = $request->department_id;
        $asset->branch_id = $request->branch_id;
        $asset->qty = $request->qty;
        $asset->available_qty = $request->qty;
        $asset->status = 'Available';
        $asset->added_by = user()->id;
        $asset->save();

        foreach ($request->serial_no as $serialNo) {
            $asset->serials()->create([
                'serial_no' => trim($serialNo),
                'status'    => 'available',
            ]);
        }

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('company-assets.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $viewPermission = user()->permission('view_company_assets');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both','branch']));

        $this->asset = CompanyAsset::findOrFail($id);
        $this->serials = $this->asset->serials()->orderBy('id')->get();

        if (!$this->canManageRecord($this->asset, $viewPermission)) {
            abort(403);
        }

        if (request()->ajax()) {
            $html = view('company-assets.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'company-assets.ajax.show';
        return view('company-assets.create', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $viewPermission = user()->permission('edit_company_assets');
        abort_403(!in_array($viewPermission, ['all', 'added', 'branch']));

        $this->asset = CompanyAsset::findOrFail($id);

        if (!$this->canManageRecord($this->asset, $viewPermission)) {
            abort(403);
        }

        $this->departments = Department::orderBy('name')->get();
        $this->branches = Branch::latest()->get();
        $this->serials = $this->asset->serials()->orderBy('id')->get();

        if (request()->ajax()) {
            $html = view('company-assets.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'company-assets.ajax.edit';
        return view('company-assets.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, $id)
    {
        $viewPermission = user()->permission('edit_company_assets');
        abort_403(!in_array($viewPermission, ['all', 'added', 'branch']));

        $asset = CompanyAsset::findOrFail($id);
        $asset->name = $request->name;
        $asset->catalog = $request->catalog ?? '';
        $asset->sku_no = $request->sku_no ?? '';
        $asset->type = $request->type ?? '';
        $asset->brand = $request->brand ?? '';
        $asset->department_id = $request->department_id;
        $asset->branch_id = $request->branch_id;
        $asset->qty = $request->qty;
        $asset->save();

        $submittedIds = [];

        foreach ($request->serial_no as $i => $serialNo) {
            $serialId = $request->serial_id[$i] ?? null;

            if ($serialId) {
                // existing serial — update (readonly on frontend for assigned ones, but re-save is harmless)
                $serial = CompanyAssetSerial::find($serialId);
                if ($serial && $serial->company_asset_id == $asset->id) {
                    $serial->serial_no = trim($serialNo);
                    $serial->save();
                    $submittedIds[] = $serial->id;
                }
            } else {
                // new serial
                $newSerial = $asset->serials()->create([
                    'serial_no' => trim($serialNo),
                    'status'    => 'available',
                ]);
                $submittedIds[] = $newSerial->id;
            }
        }

        // remove serials that were dropped (only safe ones — never delete assigned)
        $asset->serials()
            ->whereNotIn('id', $submittedIds)
            ->where('status', 'available')
            ->delete();

        // recompute available_qty from actual remaining 'available' serials
        $asset->available_qty = $asset->serials()->where('status', 'available')->count();
        $asset->save();

        $redirectUrl = route('company-assets.index');
        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $viewPermission = user()->permission('delete_company_assets');
        abort_403(!in_array($viewPermission, ['all']));

        $this->asset = CompanyAsset::findOrFail($id);

        if (!$this->canManageRecord($this->asset, $viewPermission)) {
            abort(403);
        }
        CompanyAsset::destroy($id);
        AssetAssignment::where('company_asset_id',$id)->delete();
        AssetAssignmentHistory::where('company_asset_id',$id)->delete();

        $redirectUrl = route('company-assets.index');
        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Apply quick actions (bulk delete).
     */
    public function applyQuickAction(Request $request)
    {
        $viewPermission = user()->permission('delete_company_assets');
        abort_403(!in_array($viewPermission, ['all']));

        if ($request->action_type === 'delete') {
            $this->deleteRecords($request);
            return Reply::success(__('messages.deleteSuccess'));
        }

        return Reply::error(__('messages.selectAction'));
    }

    /**
     * Delete multiple records.
     */
    protected function deleteRecords($request)
    {
        $deletePermission = user()->permission('delete_company_assets');
        // abort_403($deletePermission != 'all');

        $rowIds = explode(',', $request->row_ids);

        if (($key = array_search('on', $rowIds)) !== false) {
            unset($rowIds[$key]);
        }

        $assets = CompanyAsset::whereIn('id', $rowIds)->get();
        // dd($assets);
            foreach ($assets as $asset) {
                // dd($deletePermission);
                if ($this->canManageRecord($asset, $deletePermission)) {
                    $asset->delete();
                }
            }
        // CompanyAsset::whereIn('id', $rowIds)->delete();
    }

    public function assignAsset($id)
    {
        $this->addPermission = user()->permission('assign_company_asset_to_employee');
        abort_403(!in_array($this->addPermission, ['all', 'added','branch']));

       if(in_array($this->addPermission, ['all','branch']) ){
            if($this->addPermission == 'branch' && hr_has_all_branch_access('company_assets')){
                $employeePermission = 'all';
            } else{
                $employeePermission = $this->addPermission;
            }
        } else{
            $employeePermission = null;
        }
        $this->employees = User::allEmployees(null, true, $employeePermission);
        $this->company_asset_id = $id;
        $this->employeeId = request('employee_id');
        $this->asset = CompanyAsset::findOrFail($id);
        $notAvailableSerials = AssetAssignment::where('company_asset_id', $id)->pluck('serial_no')->toArray();
        $this->serials = $this->asset->serials()->whereNotIn('serial_no',$notAvailableSerials)->orderBy('id')->get();

        if (request()->ajax()) {
            $html = view('company-assets.ajax.assign', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'company-assets.ajax.assign';
        return view('company-assets.create', $this->data);
    }

    public function storeAssignAsset(StoreAssignRequest $request)
    {
        $asset = CompanyAsset::findOrFail($request->company_asset_id);

        $qtyAssigned = (int) $request->qty;

        if ($qtyAssigned > $asset->available_qty) {
            return Reply::error(__('messages.qtyExceedsAvailable'));
        }

        DB::beginTransaction();

        try {
            $assign = new AssetAssignment();
            $assign->employee_id = $request->employee;
            $assign->company_asset_id = $request->company_asset_id;
            $assign->status = 'Pending';
            $assign->branch_id = $asset->branch_id;
            $assign->qty = $qtyAssigned;
            $assign->serial_no = $request->serial_no;
            $assign->added_by = user()->id;
            $assign->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return Reply::error($e->getMessage());
        }

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            if ($request->filled('employee_id')) {
                $redirectUrl = route('employees.show', [$request->employee_id, 'tab' => 'company-assets']);
            } else {
                $redirectUrl = route('company-assets.show', $asset->id);
            }
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function editAssignAsset($id)
    {
        $assignment = AssetAssignment::find($id);
        $asset = $assignment->asset;

        if ($assignment->status === 'Assigned') {
            abort_403(true);
        }

        $this->asset = $asset;
        $this->assignment = $assignment;
        $this->employeeId = request('employee_id', $assignment->employee_id);
        $this->employees = User::allEmployees();

        $notAvailableSerials = AssetAssignment::where('company_asset_id', $asset->id)->where('serial_no','<>', $assignment->serial_no)->pluck('serial_no')->toArray();
        $this->serials = $this->asset->serials()->whereNotIn('serial_no',$notAvailableSerials)->orderBy('id')->get();

        if (request()->ajax()) {
            $html = view('company-assets.ajax.edit-assign', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'Edit Assign Asset']);
        }

        $this->view = 'company-assets.ajax.edit-assign';
        return view('company-assets.create', $this->data);
    }

    public function updateAssignAsset(StoreAssignRequest $request, $id)
    {
        $assignment = AssetAssignment::findOrFail($request->id);
        $asset = $assignment->asset;

        $newQty = (int) $request->qty;
        $delta = $newQty - $assignment->qty;

        if ($delta > $asset->available_qty) {
            return Reply::error(__('messages.qtyExceedsAvailable'));
        }

        DB::beginTransaction();

        try {

            $assignment->employee_id = $request->employee;
            $assignment->qty = $newQty;
            $assignment->serial_no = $request->serial_no;
            $assignment->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return Reply::error($e->getMessage());
        }

        $redirectUrl = route('company-assets.show', $id);

        if ($request->filled('employee_id')) {
            $redirectUrl = route('employees.show', [$request->employee_id, 'tab' => 'company-assets']);
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function returnAsset($id)
    {

        $this->assignment = AssetAssignment::find($id);
        $asset = CompanyAsset::findOrFail($this->assignment->company_asset_id);
        $this->asset = $asset;
        $this->employeeId = request('employee_id', $this->assignment->employee_id);
        $this->employees = User::allEmployees();

        $this->serials = $this->asset->serials()->orderBy('id')->get();

        if (request()->ajax()) {
            $html = view('company-assets.ajax.return', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'Return Asset']);
        }

        $this->view = 'company-assets.ajax.return';
        return view('company-assets.create', $this->data);
    }

    public function storeReturnAsset(Request $request, $id)
    {
        $request->validate([
            'qty' => 'required|integer|min:1',
            'return_document' => 'required',
            'loss_amount' => 'required_if:assesses_loss_damage,checked|nullable',
        ]);

        $assignment = AssetAssignment::find($request->id);
        $asset = CompanyAsset::find($assignment->company_asset_id);
        $qtyReturned = (int) $request->qty;

        if ($qtyReturned > $assignment->qty) {
            return redirect()->back()->with('error', __('messages.qtyExceedsAvailable'));
        }

        if ($request->hasFile('return_document')) {
            $path = \App\Helper\Files::uploadLocalOrS3($request->return_document, 'asset');

            $remaining = $assignment->qty - $qtyReturned;

            $history = AssetAssignmentHistory::create([
                'company_asset_id' => $asset->id,
                'employee_id' => $assignment->employee_id,
                'serial_no' => $assignment->serial_no,
                'action_type' => 'Returned',
                'qty' => $qtyReturned,
                'signed_document' => $path,
                'added_by' => user()->id,
                'action_at' => now(),
            ]);

            $assetSerial = CompanyAssetSerial::where('company_asset_id', $assignment->company_asset_id)
                ->where('serial_no', $assignment->serial_no)
                ->where('status', 'assigned')
                ->first();

            if ($assetSerial) {
                $assetSerial->update(['status' => 'available']);
            }

            $asset->available_qty += $qtyReturned;
            $asset->status = $asset->available_qty == 0 ? 'Assigned' : 'Available';
            $asset->save();

            $remaining = max($remaining, 0);

            if ($remaining < 1) {
                $assignment->delete();
            } else {
                $assignment->update(['qty' => $remaining]);
            }
        }

        if ($request->has('assesses_loss_damage')) {
            $assessLoss = EmployeeAssessLoss::create([
                'company_asset_id' => $asset->id,
                'employee_id' => $assignment->employee_id,
                'asset_assignment_history_id' => $history->id,
                'loss_amount' => $request->loss_amount,
            ]);

            $financeUsers = User::usersWithPermission('manage_finance_clearance', $assignment->employee->company_id);

            foreach ($financeUsers as $financeUser) {
                if (!empty($financeUser->email)) {
                    try {
                        Mail::to($financeUser->email)
                            ->send(new AssetLossDeductionMail($assessLoss, $asset, $assignment));
                    } catch (\Exception $e) {
                        Log::error("Failed to send asset loss deduction email: " . $e->getMessage());
                    }
                }
            }
        }

        $redirectUrl = route('company-assets.show', $id);
        if ($request->filled('employee_id')) {
            $redirectUrl = route('employees.show', [$request->employee_id, 'tab' => 'company-assets']);
        }

        return redirect($redirectUrl)->with('success', __('messages.recordSaved'));
    }

    public function generatePdf($id)
    {
        $assignment = AssetAssignment::find($id);
        $asset = CompanyAsset::with(['assignments.employee', 'history.employee'])->findOrFail($assignment->company_asset_id);

        $pdf = PDF::loadView('company-assets.pdf', compact('asset', 'assignment'));
        return $pdf->download('asset_assignment.pdf');
    }

    public function returnPdf($id)
    {
        $assignment = AssetAssignment::find($id);
        $asset = CompanyAsset::with(['assignments.employee', 'history.employee'])->findOrFail($assignment->company_asset_id);

        $pdf = PDF::loadView('company-assets.return-pdf', compact('asset', 'assignment'));
        return $pdf->download('asset_assignment-return.pdf');
    }

    public function uploadSignature($id)
    {
        $this->assignment = AssetAssignment::find($id);
        $asset = CompanyAsset::findOrFail($this->assignment->company_asset_id);
        $this->asset = $asset;
        $this->employeeId = request('employee_id', $this->assignment->employee_id);

        if (request()->ajax()) {
            $html = view('company-assets.ajax.upload-signature', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'Upload Signature']);
        }

        $this->view = 'company-assets.ajax.upload-signature';
        return view('company-assets.create', $this->data);
    }

    public function storeSignature(Request $request, $id)
    {
        // dd($request->all());
        $request->validate([
            'signature' => 'required',
        ]);

        // Load assignment with its relationships
        $assignment = AssetAssignment::with(['employee', 'asset'])->find($request->id);

        // Only allow approval of a Pending assignment.
        if ($assignment->status !== 'Pending') {
            return redirect()->route('company-assets.show', $id)
                ->with('error', __('messages.assignmentAlreadyProcessed'));
        }

        if ($request->hasFile('signature')) {

            // Approving the request reduces the available quantity.
            $asset = $assignment->asset;

            // Guard: ensure enough quantity is still available. Because pending
            // assignments don't reduce available_qty, another pending may have
            // been approved first, so re-check BEFORE committing the deduction.
            if ($assignment->qty > $asset->available_qty) {
                return redirect()->route('company-assets.show', $id)
                    ->with('error', __('messages.qtyExceedsAvailable'));
            }

            $path = \App\Helper\Files::uploadLocalOrS3($request->signature, 'asset');

            $assignment->signed_document = $path;
            $assignment->status = 'Assigned';
            $assignment->save();

            $asset->available_qty -= $assignment->qty;
            $asset->status = $asset->available_qty == 0 ? 'Assigned' : 'Available';
            $asset->save();

            AssetAssignmentHistory::create([
                'company_asset_id' => $assignment->company_asset_id,
                'employee_id' => $assignment->employee_id,
                'action_type' => 'Assigned',
                'qty' => $assignment->qty,
                'serial_no' => $assignment->serial_no,
                'signed_document' => $path,
                'added_by' => user()->id,
                'action_at' => now(),
            ]);

            $assetSerial = CompanyAssetSerial::where('company_asset_id', $assignment->company_asset_id)->where('serial_no', $assignment->serial_no)->where('status', 'available')->first();
            if($assetSerial){
                $assetSerial->update([
                    'status' => 'assigned'
                ]);
            }

            // Send the email to the employee
            try {
                Mail::to($assignment->employee->email)->send(new AssetAssignedMail($assignment));
            } catch (\Exception $e) {
                // Log the error so the user doesn't see a crash if email fails
                Log::error("Failed to send asset assignment email: " . $e->getMessage());
            }
        }

        $redirectUrl = route('company-assets.show', $id);
        if ($request->filled('employee_id')) {
            $redirectUrl = route('employees.show', [$request->employee_id, 'tab' => 'company-assets']);
        }

        return redirect($redirectUrl)->with('success', __('messages.recordSaved'));
    }

    public function viewAssign($id)
    {
        $employeeId = request('employee_id');

        $this->asset = CompanyAsset::with([
            'assignments' => function ($query) use ($employeeId) {
                if ($employeeId) {
                    $query->where('employee_id', $employeeId);
                }
                $query->orderByDesc('id');
            },
            'assignments.employee',
            'history' => function ($query) use ($employeeId) {
                if ($employeeId) {
                    $query->where('employee_id', $employeeId);
                }
                $query->orderByDesc('action_at');
            },
            'history.employee',
        ])->findOrFail($id);

        $this->assignment = $this->asset->assignments->first();
        $this->history = $this->asset->history;

        if (request()->ajax()) {
            $html = view('company-assets.ajax.show-assign', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'View Assing Asset']);
        }

        $this->view = 'company-assets.ajax.show-assign';
        return view('company-assets.create', $this->data);
    }

    public function destroyAssignAsset($id)
    {
        $viewPermission = user()->permission('edit_assign_company_assets_to_employee');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both','branch']));

        $assignment = AssetAssignment::findOrFail($id);
        $asset = CompanyAsset::findOrFail($assignment->company_asset_id);

        // Only a Pending (not yet approved) assignment can be deleted without
        // affecting the available quantity. Approved assignments must be returned.
        if ($assignment->status === 'Pending') {
            $assignment->delete();
        } else {
            return redirect()->route('company-assets.show', $asset->id)
                ->with('error', __('messages.assignmentCannotDelete'));
        }

        return redirect()->route('company-assets.show', $asset->id)
            ->with('success', __('messages.deleteSuccess'));
    }

    protected function canManageRecord(CompanyAsset $asset, $permission): bool
    {
        if ($permission === 'all' || ($permission === 'branch' && hr_has_all_branch_access('company_assets'))) {
            return true;
        }

        if ($permission === 'added' && $asset->added_by == user()->id) {
            return true;
        }

        if ($permission === 'owned' && user()->id == $asset->assignments->employee_id) {
            return true;
        }
        if ($permission == 'both' && (user()->id == $asset->added_by || user()->id == $asset->assignments->employee_id)){
            return true;
        }

        if ($permission === 'branch' && $asset->branch_id == user()->branch_id) {
            return true;
        }

        return false;

    }
}
