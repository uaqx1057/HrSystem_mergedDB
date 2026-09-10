<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyAsset\StoreAssignRequest;
use App\Mail\AssetLossDeductionMail;
use App\Models\AssetAssignment;
use App\Models\AssetAssignmentHistory;
use App\Models\AssetReturnForm;
use App\Models\Branch;
use App\Models\CompanyAsset;
use App\Helper\Reply;
use App\Http\Controllers\Controller;
use App\Models\CompanyAssetSerial;
use App\Models\Department;
use App\Models\EmployeeAssessLoss;
use App\Models\User;
use App\Services\AssetReturnService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\DataTables\CompanyAssetDataTable;
use App\Http\Requests\CompanyAsset\StoreRequest;
use App\Http\Requests\CompanyAsset\UpdateRequest;
use App\Mail\AssetAssignedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompanyAssetController extends AccountBaseController
{
    /** How many serial rows a single asset may hold. */
    private const MAX_QTY = 500;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.companyAssets');
    }

    public function index(CompanyAssetDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_company_assets');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both', 'branch']));
        $this->assignRole = user()->roles->pluck('name')->toArray();

        return $dataTable->render('company-assets.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_company_assets');
        abort_403(!in_array($this->addPermission, ['all', 'branch']));

        $this->departments = Department::orderBy('name')->get();
        $this->branches = Branch::latest()->get();
        $this->maxQty = self::MAX_QTY;

        if (request()->ajax()) {
            $html = view('company-assets.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'company-assets.ajax.create';
        return view('company-assets.create', $this->data);
    }

    public function store(StoreRequest $request)
    {
        $viewPermission = user()->permission('add_company_assets');
        abort_403(!in_array($viewPermission, ['all', 'branch']));

        $asset = DB::transaction(function () use ($request) {
            $asset = new CompanyAsset();
            $asset->name = $request->name;
            $asset->catalog = $request->catalog ?? '';
            $asset->sku_no = $request->sku_no ?? '';
            $asset->type = $request->type ?? '';
            $asset->brand = $request->brand ?? '';
            $asset->department_id = $request->department_id;
            $asset->branch_id = $request->branch_id;
            $asset->qty = 0;
            $asset->available_qty = 0;
            $asset->status = CompanyAsset::STATUS_AVAILABLE;
            $asset->added_by = user()->id;
            $asset->save();

            foreach ($request->serial_no as $serialNo) {
                $asset->serials()->create([
                    'serial_no' => trim($serialNo),
                    'status'    => CompanyAssetSerial::STATUS_AVAILABLE,
                ]);
            }

            $asset->syncAvailability();

            return $asset;
        });

        $redirectUrl = urldecode((string) $request->redirect_url) ?: route('company-assets.index');

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function show($id)
    {
        $viewPermission = user()->permission('view_company_assets');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both', 'branch']));

        $this->asset = CompanyAsset::with(['department', 'branch'])->findOrFail($id);

        if (!$this->canManageRecord($this->asset, $viewPermission)) {
            abort(403);
        }

        $this->serials = $this->asset->serials()
            ->with(['assignment.employee'])
            ->orderBy('id')
            ->get();

        $this->manualSerialStatuses = CompanyAssetSerial::MANUAL_STATUSES;

        if (request()->ajax()) {
            $html = view('company-assets.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'company-assets.ajax.show';
        return view('company-assets.create', $this->data);
    }

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
        $this->maxQty = self::MAX_QTY;

        if (request()->ajax()) {
            $html = view('company-assets.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'company-assets.ajax.edit';
        return view('company-assets.create', $this->data);
    }

    public function update(UpdateRequest $request, $id)
    {
        $viewPermission = user()->permission('edit_company_assets');
        abort_403(!in_array($viewPermission, ['all', 'added', 'branch']));

        $asset = CompanyAsset::findOrFail($id);

        if (!$this->canManageRecord($asset, $viewPermission)) {
            abort(403);
        }

        // A serial that is reserved, out or flagged (lost/damaged/retired) can never be dropped.
        $protectedCount = $asset->serials()
            ->whereNotIn('status', [CompanyAssetSerial::STATUS_AVAILABLE])
            ->count();

        if ((int) $request->qty < $protectedCount) {
            return Reply::error(__('messages.qtyBelowInUse', ['count' => $protectedCount]));
        }

        DB::transaction(function () use ($request, $asset) {
            $asset->fill($request->only([
                'name', 'catalog', 'sku_no', 'type', 'brand', 'department_id', 'branch_id',
            ]));
            $asset->save();

            $submittedIds = [];

            foreach ($request->serial_no as $i => $serialNo) {
                $serialId = $request->serial_id[$i] ?? null;

                if ($serialId) {
                    $serial = $asset->serials()->find($serialId);
                    if ($serial) {
                        $serial->serial_no = trim($serialNo);
                        $serial->save();
                        $submittedIds[] = $serial->id;
                    }
                } else {
                    $newSerial = $asset->serials()->create([
                        'serial_no' => trim($serialNo),
                        'status'    => CompanyAssetSerial::STATUS_AVAILABLE,
                    ]);
                    $submittedIds[] = $newSerial->id;
                }
            }

            // Soft-delete dropped serials — only the truly free ones, and the row is recoverable.
            $asset->serials()
                ->whereNotIn('id', $submittedIds)
                ->where('status', CompanyAssetSerial::STATUS_AVAILABLE)
                ->get()
                ->each->delete();

            $asset->syncAvailability();
        });

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('company-assets.index')]);
    }

    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_company_assets');
        abort_403(!in_array($deletePermission, ['all']));

        $asset = CompanyAsset::findOrFail($id);

        if (!$this->canManageRecord($asset, $deletePermission)) {
            abort(403);
        }

        if ($asset->hasActiveAssignments()) {
            return Reply::error(__('messages.assetHasActiveAssignments'));
        }

        DB::transaction(function () use ($asset) {
            // Soft-delete the asset and its serials. History is kept for the audit trail.
            $asset->serials()->get()->each->delete();
            $asset->delete();
        });

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('company-assets.index')]);
    }

    public function applyQuickAction(Request $request)
    {
        $deletePermission = user()->permission('delete_company_assets');
        abort_403(!in_array($deletePermission, ['all']));

        if ($request->action_type !== 'delete') {
            return Reply::error(__('messages.selectAction'));
        }

        $rowIds = array_filter(explode(',', (string) $request->row_ids), fn ($v) => is_numeric($v));
        $skipped = 0;

        CompanyAsset::whereIn('id', $rowIds)->get()->each(function (CompanyAsset $asset) use ($deletePermission, &$skipped) {
            if (!$this->canManageRecord($asset, $deletePermission) || $asset->hasActiveAssignments()) {
                $skipped++;
                return;
            }
            DB::transaction(function () use ($asset) {
                $asset->serials()->get()->each->delete();
                $asset->delete();
            });
        });

        if ($skipped) {
            return Reply::success(__('messages.deleteSuccessWithSkipped', ['count' => $skipped]));
        }

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function assignAsset($id)
    {
        $this->addPermission = user()->permission('assign_company_asset_to_employee');
        abort_403(!in_array($this->addPermission, ['all', 'added', 'branch']));

        if (in_array($this->addPermission, ['all', 'branch'])) {
            $employeePermission = ($this->addPermission === 'branch' && hr_has_all_branch_access('company_assets'))
                ? 'all'
                : $this->addPermission;
        } else {
            $employeePermission = null;
        }

        $this->employees = User::allEmployees(null, true, $employeePermission);
        $this->company_asset_id = $id;
        $this->employeeId = request('employee_id');
        $this->asset = CompanyAsset::findOrFail($id);
        $this->serials = $this->asset->serials()->available()->orderBy('id')->get();

        if (request()->ajax()) {
            $html = view('company-assets.ajax.assign', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'company-assets.ajax.assign';
        return view('company-assets.create', $this->data);
    }

    public function storeAssignAsset(StoreAssignRequest $request)
    {
        $assignPermission = user()->permission('assign_company_asset_to_employee');
        abort_403(!in_array($assignPermission, ['all', 'added', 'branch']));

        $asset = CompanyAsset::findOrFail($request->company_asset_id);
        $serial = $this->resolveSerial($asset, $request);

        if (!$serial || !$serial->isAvailable()) {
            return Reply::error(__('messages.serialNotAvailable'));
        }

        DB::transaction(function () use ($request, $asset, $serial) {
            $assign = new AssetAssignment();
            $assign->employee_id = $request->employee;
            $assign->company_asset_id = $asset->id;
            $assign->company_asset_serial_id = $serial->id;
            $assign->serial_no = $serial->serial_no;
            $assign->status = AssetAssignment::STATUS_PENDING;
            $assign->branch_id = $asset->branch_id;
            $assign->qty = 1;
            $assign->added_by = user()->id;
            $assign->save();

            $serial->update(['status' => CompanyAssetSerial::STATUS_PENDING]);
            $asset->syncAvailability();
        });

        $redirectUrl = urldecode((string) $request->redirect_url);

        if ($redirectUrl === '') {
            $redirectUrl = $request->filled('employee_id')
                ? route('employees.show', [$request->employee_id, 'tab' => 'company-assets'])
                : route('company-assets.show', $asset->id);
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function editAssignAsset($id)
    {
        $assignment = AssetAssignment::with('serial')->findOrFail($id);
        $asset = $assignment->asset;

        if ($assignment->isAssigned()) {
            abort_403(true);
        }

        $this->asset = $asset;
        $this->assignment = $assignment;
        $this->employeeId = request('employee_id', $assignment->employee_id);
        $this->employees = User::allEmployees();

        // available serials + the one this pending assignment currently holds
        $this->serials = $asset->serials()
            ->where(function ($q) use ($assignment) {
                $q->where('status', CompanyAssetSerial::STATUS_AVAILABLE)
                    ->orWhere('id', $assignment->company_asset_serial_id);
            })
            ->orderBy('id')
            ->get();

        if (request()->ajax()) {
            $html = view('company-assets.ajax.edit-assign', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'Edit Assign Asset']);
        }

        $this->view = 'company-assets.ajax.edit-assign';
        return view('company-assets.create', $this->data);
    }

    public function updateAssignAsset(StoreAssignRequest $request, $id)
    {
        $assignment = AssetAssignment::with('serial')->findOrFail($request->id);
        $asset = $assignment->asset;

        if ($assignment->isAssigned()) {
            abort_403(true);
        }

        $newSerial = $this->resolveSerial($asset, $request);

        if (!$newSerial) {
            return Reply::error(__('messages.serialNotAvailable'));
        }

        $serialChanged = (int) $newSerial->id !== (int) $assignment->company_asset_serial_id;

        if ($serialChanged && !$newSerial->isAvailable()) {
            return Reply::error(__('messages.serialNotAvailable'));
        }

        DB::transaction(function () use ($request, $assignment, $asset, $newSerial, $serialChanged) {
            if ($serialChanged) {
                $assignment->serial?->update(['status' => CompanyAssetSerial::STATUS_AVAILABLE]);
                $newSerial->update(['status' => CompanyAssetSerial::STATUS_PENDING]);
            }

            $assignment->employee_id = $request->employee;
            $assignment->company_asset_serial_id = $newSerial->id;
            $assignment->serial_no = $newSerial->serial_no;
            $assignment->qty = 1;
            $assignment->save();

            $asset->syncAvailability();
        });

        $redirectUrl = $request->filled('employee_id')
            ? route('employees.show', [$request->employee_id, 'tab' => 'company-assets'])
            : route('company-assets.show', $asset->id);

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function returnAsset($id)
    {
        $this->assignment = AssetAssignment::with(['serial', 'asset'])->findOrFail($id);
        $this->authorizeAssignmentManagement($this->assignment);
        $this->asset = CompanyAsset::findOrFail($this->assignment->company_asset_id);
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
            'return_document' => 'required|file',
            'loss_amount'     => 'nullable|numeric|min:0|required_with:assesses_loss_damage',
        ]);

        abort_404($request->filled('id') && (int) $request->id !== (int) $id);

        $assignment = AssetAssignment::with(['serial', 'employee', 'asset'])->findOrFail($id);
        $this->authorizeAssignmentManagement($assignment);
        $asset = $assignment->asset;

        $path = \App\Helper\Files::uploadLocalOrS3($request->return_document, 'asset');

        $form = app(AssetReturnService::class)->certifyReturn($assignment, user()->id, [
            'return_document' => $path,
            'accessories_checklist' => $request->input('accessories_checklist'),
            'technical_inspection' => $request->input('technical_inspection'),
            'data_clearance_notes' => $request->input('data_clearance_notes'),
        ]);

        if ($request->has('assesses_loss_damage') && $request->filled('loss_amount')) {
            $assessLoss = EmployeeAssessLoss::create([
                'company_asset_id'             => $asset->id,
                'employee_id'                  => $assignment->employee_id,
                'asset_assignment_history_id'  => $form->asset_assignment_history_id,
                'loss_amount'                  => $request->loss_amount,
                'status'                       => EmployeeAssessLoss::STATUS_PENDING,
            ]);

            $companyId = optional($assignment->employee)->company_id;
            $financeUsers = $companyId ? User::usersWithPermission('manage_finance_clearance', $companyId) : collect();

            foreach ($financeUsers as $financeUser) {
                if (!empty($financeUser->email)) {
                    try {
                        Mail::to($financeUser->email)->send(new AssetLossDeductionMail($assessLoss, $asset, $assignment));
                    } catch (\Exception $e) {
                        Log::error('Failed to send asset loss deduction email: ' . $e->getMessage());
                    }
                }
            }
        }

        $redirectUrl = $request->filled('employee_id')
            ? route('employees.show', [$request->employee_id, 'tab' => 'company-assets'])
            : route('company-assets.show', $id);

        return redirect($redirectUrl)->with('success', __('messages.recordSaved'));
    }

    public function uploadSignature($id)
    {
        $this->assignment = AssetAssignment::with(['serial', 'asset'])->findOrFail($id);
        $this->authorizeAssignmentManagement($this->assignment);
        $this->asset = CompanyAsset::findOrFail($this->assignment->company_asset_id);
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
        $request->validate(['signature' => 'required|file']);

        abort_404($request->filled('id') && (int) $request->id !== (int) $id);

        $assignment = AssetAssignment::with(['employee', 'asset', 'serial'])->findOrFail($id);
        $this->authorizeAssignmentManagement($assignment);

        if (!$assignment->isPending()) {
            return redirect()->route('company-assets.show', $id)
                ->with('error', __('messages.assignmentAlreadyProcessed'));
        }

        $serial = $assignment->serial ?? $assignment->asset->serials()
            ->where('serial_no', $assignment->serial_no)
            ->first();

        if (!$serial || !in_array($serial->status, [CompanyAssetSerial::STATUS_PENDING, CompanyAssetSerial::STATUS_AVAILABLE])) {
            return redirect()->route('company-assets.show', $id)
                ->with('error', __('messages.serialNotAvailable'));
        }

        $path = \App\Helper\Files::uploadLocalOrS3($request->signature, 'asset');

        DB::transaction(function () use ($assignment, $serial, $path) {
            $assignment->signed_document = $path;
            $assignment->status = AssetAssignment::STATUS_ASSIGNED;
            $assignment->company_asset_serial_id = $serial->id;
            $assignment->save();

            $serial->update(['status' => CompanyAssetSerial::STATUS_ASSIGNED]);

            AssetAssignmentHistory::create([
                'company_asset_id'        => $assignment->company_asset_id,
                'company_asset_serial_id' => $serial->id,
                'employee_id'             => $assignment->employee_id,
                'action_type'             => AssetAssignmentHistory::ACTION_ASSIGNED,
                'qty'                     => 1,
                'serial_no'               => $serial->serial_no,
                'signed_document'         => $path,
                'asset_assignment_id'     => $assignment->id,
                'added_by'                => user()->id,
                'action_at'               => now(),
            ]);

            $assignment->asset->syncAvailability();
        });

        try {
            Mail::to($assignment->employee->email)->send(new AssetAssignedMail($assignment));
        } catch (\Exception $e) {
            Log::error('Failed to send asset assignment email: ' . $e->getMessage());
        }

        $redirectUrl = $request->filled('employee_id')
            ? route('employees.show', [$request->employee_id, 'tab' => 'company-assets'])
            : route('company-assets.show', $id);

        return redirect($redirectUrl)->with('success', __('messages.recordSaved'));
    }

    /** Admin flags a single unit lost / damaged / retired, or brings it back to available. */
    /** Write off a unit currently out with an employee: lost, damaged beyond repair, or retired. */
    public function writeOffAssignment(Request $request, $id)
    {
        abort_404($request->filled('id') && (int) $request->id !== (int) $id);

        $assignment = AssetAssignment::with(['serial', 'asset', 'employee'])->findOrFail($id);
        $this->authorizeAssignmentManagement($assignment);

        $data = $request->validate([
            'outcome' => 'required|in:' . implode(',', [AssetReturnForm::OUTCOME_LOST, AssetReturnForm::OUTCOME_DAMAGED, AssetReturnForm::OUTCOME_RETIRED]),
            'disposition_notes' => 'required|string|max:2000',
            'recommended_recovery_amount' => 'nullable|numeric|min:0',
            'recovery_reason' => 'nullable|string|max:1000|required_with:recommended_recovery_amount',
        ]);

        $serial = $assignment->serial ?? $assignment->asset->serials()->where('serial_no', $assignment->serial_no)->first();

        if (!$serial) {
            return Reply::error(__('messages.serialNotAvailable'));
        }

        app(AssetReturnService::class)->writeOff($serial, user()->id, $data['outcome'], $data);

        $redirectUrl = $request->filled('employee_id')
            ? route('employees.show', [$request->employee_id, 'tab' => 'company-assets'])
            : route('company-assets.show', $assignment->company_asset_id);

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function updateSerialStatus(Request $request, $serialId)
    {
        $editPermission = user()->permission('edit_company_assets');
        abort_403(!in_array($editPermission, ['all', 'added', 'branch']));

        $data = $request->validate([
            'status' => 'required|in:' . implode(',', CompanyAssetSerial::MANUAL_STATUSES),
        ]);

        $serial = CompanyAssetSerial::with('asset')->findOrFail($serialId);

        if (!$this->canManageRecord($serial->asset, $editPermission)) {
            abort(403);
        }

        // Never touch a unit that is reserved or out with someone.
        if (in_array($serial->status, [CompanyAssetSerial::STATUS_PENDING, CompanyAssetSerial::STATUS_ASSIGNED])) {
            return Reply::error(__('messages.serialInUseCannotFlag'));
        }

        DB::transaction(function () use ($serial, $data) {
            $serial->update(['status' => $data['status']]);
            $serial->asset->syncAvailability();
        });

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('company-assets.show', $serial->company_asset_id)]);
    }

    public function rtPdf($id)
    {
        $form = AssetReturnForm::with('asset')->findOrFail($id);
        $permission = user()->permission('assign_company_asset_to_employee');
        abort_403(!in_array($permission, ['all', 'added', 'branch']) || !$this->canManageRecord($form->asset, $permission));
        $form = app(AssetReturnService::class)->generatePdf($form);
        return Storage::download($form->pdf_path, $form->reference . '.pdf', ['Content-Type' => 'application/pdf']);
    }
    public function generatePdf($id)
    {
        $assignment = AssetAssignment::with([
            'employee.employeeDetail.designation',
            'employee.employeeDetail.department',
            'employee.branch',
            'serial',
        ])->findOrFail($id);
        $this->authorizeAssignmentManagement($assignment);

        $asset = CompanyAsset::with(['department', 'branch'])->findOrFail($assignment->company_asset_id);
        $company = company();

        $pdf = PDF::loadView('company-assets.pdf', compact('asset', 'assignment', 'company'))->setPaper('letter');

        return $pdf->download('asset-handover-' . ($assignment->serialLabel() ?: $asset->id) . '.pdf');
    }

    public function returnPdf($id)
    {
        $assignment = AssetAssignment::with([
            'employee.employeeDetail.designation',
            'employee.employeeDetail.department',
            'employee.branch',
            'serial',
        ])->findOrFail($id);
        $this->authorizeAssignmentManagement($assignment);

        $asset = CompanyAsset::with(['department', 'branch'])->findOrFail($assignment->company_asset_id);
        $company = company();

        $pdf = PDF::loadView('company-assets.return-pdf', compact('asset', 'assignment', 'company'))->setPaper('letter');

        return $pdf->download('asset-return-' . ($assignment->serialLabel() ?: $asset->id) . '.pdf');
    }

    public function viewAssign($id)
    {
        $viewPermission = user()->permission('view_assign_company_assets_to_employee');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both', 'branch']));

        $employeeId = request('employee_id');

        $this->asset = CompanyAsset::with([
            'assignments' => fn ($q) => $q->when($employeeId, fn ($qq) => $qq->where('employee_id', $employeeId))->orderByDesc('id'),
            'assignments.employee',
            'assignments.serial',
            'history' => fn ($q) => $q->when($employeeId, fn ($qq) => $qq->where('employee_id', $employeeId))->orderByDesc('action_at'),
            'history.employee',
        ])->findOrFail($id);

        $this->assignments = $this->asset->assignments;
        $this->history = $this->asset->history;

        if (request()->ajax()) {
            $html = view('company-assets.ajax.show-assign', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'Assignment History']);
        }

        $this->view = 'company-assets.ajax.show-assign';
        return view('company-assets.create', $this->data);
    }

    public function destroyAssignAsset($id)
    {
        $editPermission = user()->permission('edit_assign_company_assets_to_employee');
        abort_403(!in_array($editPermission, ['all', 'added', 'owned', 'both', 'branch']));

        $assignment = AssetAssignment::with('serial')->findOrFail($id);
        $asset = CompanyAsset::findOrFail($assignment->company_asset_id);

        if (!$assignment->isPending()) {
            return redirect()->route('company-assets.show', $asset->id)
                ->with('error', __('messages.assignmentCannotDelete'));
        }

        DB::transaction(function () use ($assignment, $asset) {
            if ($assignment->serial && $assignment->serial->status === CompanyAssetSerial::STATUS_PENDING) {
                $assignment->serial->update(['status' => CompanyAssetSerial::STATUS_AVAILABLE]);
            }
            $assignment->delete();
            $asset->syncAvailability();
        });

        return redirect()->route('company-assets.show', $asset->id)
            ->with('success', __('messages.deleteSuccess'));
    }

    /**
     * Resolve the target serial from either the new company_asset_serial_id
     * field or the legacy serial_no string, scoped to this asset.
     */
    private function resolveSerial(CompanyAsset $asset, Request $request): ?CompanyAssetSerial
    {
        if ($request->filled('company_asset_serial_id')) {
            return $asset->serials()->find($request->company_asset_serial_id);
        }

        if ($request->filled('serial_no')) {
            return $asset->serials()->where('serial_no', trim($request->serial_no))->first();
        }

        return null;
    }

    protected function canManageRecord(CompanyAsset $asset, $permission): bool
    {
        if ($permission === 'all' || ($permission === 'branch' && hr_has_all_branch_access('company_assets'))) {
            return true;
        }

        if ($permission === 'added' && $asset->added_by == user()->id) {
            return true;
        }

        $ownsAssignment = $asset->relationLoaded('assignments')
            ? $asset->assignments->contains('employee_id', user()->id)
            : $asset->assignments()->where('employee_id', user()->id)->exists();

        if ($permission === 'owned' && $ownsAssignment) {
            return true;
        }

        if ($permission === 'both' && ($asset->added_by == user()->id || $ownsAssignment)) {
            return true;
        }

        if ($permission === 'branch' && $asset->branch_id == user()->branch_id) {
            return true;
        }

        return false;
    }

    protected function authorizeAssignmentManagement(AssetAssignment $assignment): void
    {
        $permission = user()->permission('assign_company_asset_to_employee');
        abort_403(!in_array($permission, ['all', 'added', 'branch']));
        abort_403(!$this->canManageRecord($assignment->asset, $permission));
    }
}
