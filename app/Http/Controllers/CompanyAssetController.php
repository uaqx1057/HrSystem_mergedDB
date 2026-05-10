<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyAsset\StoreAssignRequest;
use App\Models\AssetAssignment;
use App\Models\CompanyAsset;
use App\Helper\Reply;
use App\Http\Controllers\Controller;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf; // This is the Facade
use Illuminate\Http\Request;
use App\DataTables\CompanyAssetDataTable;
use App\Http\Requests\CompanyAsset\StoreRequest;
use App\Http\Requests\CompanyAsset\UpdateRequest;

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
        $this->assignRole = user()->roles->pluck('name')->toArray();
        // $viewPermission = user()->permission('view_company_asset');
        // abort_403(!in_array($viewPermission, ['all']));

        return $dataTable->render('company-assets.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->assignRole = user()->roles->pluck('name')->toArray();
        abort_403(!in_array('admin', $this->assignRole));

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
        $asset = new CompanyAsset();
        $asset->name = $request->name;
        $asset->catalog = $request->catalog ?? '';
        $asset->sku_no = $request->sku_no ?? '';
        $asset->type = $request->type ?? '';
        $asset->brand = $request->brand ?? '';
        $asset->save();

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
        $this->asset = CompanyAsset::findOrFail($id);

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
        $this->assignRole = user()->roles->pluck('name')->toArray();
        abort_403(!in_array('admin', $this->assignRole));

        $this->asset = CompanyAsset::findOrFail($id);

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
        // $editPermission = user()->permission('edit_company_asset');
        // abort_403($editPermission != 'all');

        $asset = CompanyAsset::findOrFail($id);
        $asset->name = $request->name;
        $asset->catalog = $request->catalog ?? '';
        $asset->sku_no = $request->sku_no ?? '';
        $asset->type = $request->type ?? '';
        $asset->brand = $request->brand ?? '';
        $asset->save();

        $redirectUrl = route('company-assets.index');
        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // $deletePermission = user()->permission('delete_company_asset');
        // abort_403($deletePermission != 'all');

        CompanyAsset::destroy($id);

        $redirectUrl = route('company-assets.index');
        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Apply quick actions (bulk delete).
     */
    public function applyQuickAction(Request $request)
    {
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
        // $deletePermission = user()->permission('delete_company_asset');
        // abort_403($deletePermission != 'all');

        $rowIds = explode(',', $request->row_ids);

        if (($key = array_search('on', $rowIds)) !== false) {
            unset($rowIds[$key]);
        }

        CompanyAsset::whereIn('id', $rowIds)->delete();
    }

    public function assignAsset($id)
    {
        $this->company_asset_id = $id;
        $this->asset = CompanyAsset::findOrFail($id);
        $this->employees = User::allEmployees();

        if (request()->ajax()) {
            $html = view('company-assets.ajax.assign', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'company-assets.ajax.assign';
        return view('company-assets.create', $this->data);
    }

    public function storeAssignAsset(StoreAssignRequest $request)
    {
        $assign = new AssetAssignment();
        $assign->employee_id = $request->employee;
        $assign->company_asset_id = $request->company_asset_id;
        $assign->status = $request->status;
        $assign->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('company-assets.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function editAssignAsset($id)
    {
        $asset = CompanyAsset::findOrFail($id);
        $assignment = $asset->assignments()->first();
        $this->asset = $asset;
        $this->assignment = $assignment;
        $this->employees = User::allEmployees();

        if (request()->ajax()) {
            $html = view('company-assets.ajax.edit-assign', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'Edit Assign Asset']);
        }

        $this->view = 'company-assets.ajax.edit-assign';
        return view('company-assets.create', $this->data);
    }

    public function updateAssignAsset(StoreAssignRequest $request, $id)
    {
        $assignment = AssetAssignment::where('company_asset_id', $id)->firstOrFail();
        $assignment->employee_id = $request->employee;
        $assignment->status = $request->status;
        $assignment->save();

        $redirectUrl = route('company-assets.index');

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function generatePdf($id)
    {
        $asset = CompanyAsset::with('assignments.employee')->findOrFail($id);
        $assignment = $asset->assignments->first();

        $pdf = PDF::loadView('company-assets.pdf', compact('asset', 'assignment'));
        return $pdf->download('asset_assignment.pdf');
    }

    public function uploadSignature($id)
    {
        $asset = CompanyAsset::findOrFail($id);
        $this->asset = $asset;

        if (request()->ajax()) {
            $html = view('company-assets.ajax.upload-signature', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'Upload Signature']);
        }

        $this->view = 'company-assets.ajax.upload-signature';
        return view('company-assets.create', $this->data);
    }

    public function storeSignature(Request $request, $id)
    {
        $request->validate([
            'signature' => 'required',
        ]);

        $assignment = AssetAssignment::where('company_asset_id', $id)->firstOrFail();

        if ($request->hasFile('signature')) {

            $path = \App\Helper\Files::uploadLocalOrS3($request->signature, 'asset');

            $assignment->signed_document = $path;
            $assignment->status = 'Assigned';
            $assignment->save();

        }

        return redirect()->route('company-assets.index')->with('success', __('messages.recordSaved'));
    }

    public function viewAssign($id)
    {
        $this->asset = CompanyAsset::with('assignments.employee')->findOrFail($id);
        $this->assignment = $this->asset->assignments->first();

        if (request()->ajax()) {
            $html = view('company-assets.ajax.show-assign', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'View Assing Asset']);
        }

        $this->view = 'company-assets.ajax.show-assign';
        return view('company-assets.create', $this->data);
    }
}
