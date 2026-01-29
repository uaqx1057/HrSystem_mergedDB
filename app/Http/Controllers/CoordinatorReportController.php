<?php

namespace App\Http\Controllers;

use App\DataTables\CoordinatorReportsDataTable;
use App\Enums\Salutation;
use App\Helper\Reply;
use App\Http\Requests\Admin\CoordinatorReport\StoreRequest;
use App\Http\Requests\Admin\CoordinatorReport\UpdateRequest;
use App\Http\Requests\Admin\CoordinatorReport\ImportRequest;
use App\Http\Requests\Admin\CoordinatorReport\ImportProcessRequest;
use App\Imports\CoordinatorReportImport;
use App\Jobs\ImportCoordinatorReportJob;
use App\Models\Driver;
use App\Models\LanguageSetting;
use App\Models\Role;
use App\Traits\ImportExcel;
use Illuminate\Http\Request;
use App\Helper\Files;
use App\Models\Business;
use App\Models\CoordinatorReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CoordinatorReportController extends AccountBaseController
{
    use ImportExcel;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.coordinatorReport';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('coordinatorReports', $this->user->modules));

            return $next($request);
        });
    }


    /**
     * Display a listing of the resource.
     */
    public function index(CoordinatorReportsDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_coordinator_reports');
        abort_403(!in_array($viewPermission, ['all']));
        $this->businesses = Business::select([ 'id', 'name' ])->get();
        $this->business_id = request()->business_id ?? $this->businesses->first()?->id;

        if (!$this->business_id) return redirect()->route('businesses.index');
        return $dataTable->with('business_id', $this->business_id)->render('coordinator-report.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $addPermission = user()->permission('add_coordinator_reports');
        abort_403(!in_array($addPermission, ['all', 'added']));

        $this->pageTitle = __('app.addProject');
        $this->businesses = Business::select([ 'id', 'name' ])->with([ 'fields' => fn ($q) => $q->where('admin_only', '<>', true) ])->get();
        $this->drivers = Driver::get();
        $this->view = 'coordinator-report.ajax.create';
        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('coordinator-report.create', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function importReportView()
    {
        $addPermission = user()->permission('add_coordinator_reports');
        abort_403(!in_array($addPermission, ['all', 'added']));

        $this->pageTitle = __('app.importReport');
        $this->businesses = Business::select([ 'id', 'name' ])->with([ 'fields' => fn ($q) => $q->where('admin_only', '<>', true) ])->get();
        $this->drivers = Driver::get();
        $this->view = 'coordinator-report.ajax.import';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('coordinator-report.import', $this->data);
    }

    public function importStore(ImportRequest $request)
    {
        $this->importFileProcess($request, CoordinatorReportImport::class);

        $view = view('coordinator-report.ajax.import_progress', $this->data)->render();

        return Reply::successWithData(__('messages.importUploadSuccess'), ['view' => $view]);
    }

    public function importProcess(ImportProcessRequest $request)
    {
        $batch = $this->importJobProcess($request, CoordinatorReportImport::class, ImportCoordinatorReportJob::class);

        return Reply::successWithData(__('messages.importProcessStart'), ['batch' => $batch]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $addPermission = user()->permission('add_coordinator_reports');
        abort_403(!in_array($addPermission, ['all', 'added']));

        DB::beginTransaction();
        try {
            $validated = $request->validated();

            $validated['report_date'] = Carbon::createFromFormat($this->company->date_format, $request->report_date)->format('Y-m-d');
            $fields = $validated['fields'];
            $fields = array_filter($fields, fn ($f) => isset($f['value']) && !empty($f['value']));
            unset($validated['fields']);

            $report = CoordinatorReport::create($validated);

            $fieldIds = array_column($fields, 'field_id');
            $dbFields = Business::find($request->business_id)->fields()->where('admin_only', '<>', true)->get();
            foreach ($dbFields as $dbField) {
                if ($dbField->required && !in_array($dbField->id, $fieldIds)) {
                    return Reply::error($dbField->name . ' is required.');
                }
            }

            $fields = array_map(function ($field) use ($dbFields) {
                $dbField = $dbFields->where('id', $field['field_id'])->first()->toArray();

                if ($dbField['type'] == 'DOCUMENT' && $field['value']) {
                    $fieldValue = [];
                    foreach ($field['value'] as $file) {
                        $fieldValue[] = Files::uploadLocalOrS3($file, 'coordinator-reports', 300);
                    }

                    $field['value'] = json_encode($fieldValue);
                };

                return $field;
            }, $fields);

            $report->field_values()->createMany($fields);

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

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('coordinator-report.index') . '?business_id=' . $request->business_id]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CoordinatorReport $coordinator_report)
    {
        $this->editPermission = user()->permission('edit_coordinator_reports');
        abort_403(!($this->editPermission == 'all'));

        $this->pageTitle = _('app.update');
        $this->coordinator_report = $coordinator_report;
        $this->fields = $coordinator_report->business->fields->where('admin_only', true);
        $this->view = 'coordinator-report.ajax.edit';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('coordinator-report.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, CoordinatorReport $coordinator_report)
    {
        $this->editPermission = user()->permission('edit_coordinator_reports');
        abort_403(!($this->editPermission == 'all'));

        $validated = $request->validated();
        $fields = $validated['fields'];
        $dbFields = $coordinator_report->business->fields()->where('admin_only', true)->get();

        $fields = array_map(function ($field) use ($dbFields) {
            $dbField = $dbFields->where('id', $field['field_id'])->first()->toArray();

            if ($dbField['type'] == 'DOCUMENT' && $field['value']) {
                $field['value'] = Files::uploadLocalOrS3($field['value'], 'coordinator-reports', 300);
            };

            return $field;
        }, $fields);

        $coordinator_report->field_values()->createMany($fields);

        $coordinator_report->update([ 'admin_submitted' => true ]);

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoordinatorReport $coordinator_report)
    {
       $coordinator_report->delete();
       return Reply::success(__('messages.deleteSuccess'));
    }
}
