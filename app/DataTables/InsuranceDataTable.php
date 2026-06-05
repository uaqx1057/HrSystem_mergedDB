<?php

namespace App\DataTables;

use App\Models\Insurance;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class InsuranceDataTable extends BaseDataTable
{

    private $editInsurancePermission;
    private $deleteInsurancePermission;
    private $employeeId; // ✅ Add this
    private $driverId; // ✅ Add this
    private $assignRole;

    public function __construct($employeeId = null, $driverId = null)
    {
        parent::__construct();
        $this->editInsurancePermission = user()->permission('edit_insurance');
        $this->deleteInsurancePermission = user()->permission('delete_insurance');
        $this->employeeId = $employeeId; // ✅ Store it
        $this->driverId = $driverId; // ✅ Store it
        $this->assignRole = user()->roles->pluck('name')->toArray();
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="select-table-row" id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->addColumn('action', function ($row) {

                $action = '<div class="task_view">
<a href="' . route('insurance.show', [$row->id]) . '" class="taskView text-darkest-grey f-w-500 openRightModal">' . __('app.view') . '</a>';

                    $action .= '<div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a><div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                if ($this->editInsurancePermission == 'all') {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('insurance.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if ($this->deleteInsurancePermission == 'all') {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-insurance-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->editColumn('employee_name', function ($row) {
                $name = $row->employee_name ?? $row->driver_name ?? '-';
                return '<h5 class="mb-0 f-13 text-darkest-grey">' . $name . '</h5>';
            })
            ->editColumn('issue_date', function ($row) {
                return $row->issue_date ? $row->issue_date->format(company()->date_format) : '-';
            })
            ->editColumn('expiry_date', function ($row) {
                return $row->expiry_date ? $row->expiry_date->format(company()->date_format) : '-';
            })
            ->editColumn('company', function ($row) {
                return $row->company ?? '-';
            })
            ->editColumn('policy_no', function ($row) {
                return $row->policy_no ?? '-';
            })
            ->editColumn('class', function ($row) {
                return $row->class ?? '-';
            })
            // ✅ New: Status column with colored badges
            ->editColumn('status', function ($row) {
                if ($row->status == 'active') {
                    if ($row->expiry_date <= today()) {
                        return ' <i class="fa fa-circle mr-1 text-yellow f-10"></i>' . __('app.expired');
                    } else {
                        return ' <i class="fa fa-circle mr-1 text-light-green f-10"></i>' . __('app.active');
                    }
                }
                return '<i class="fa fa-circle mr-1 text-red f-10"></i>' . __('app.cancelled');
            })
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            ->rawColumns(['action', 'employee_name', 'check', 'status']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Insurance $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Insurance $model)
    {
        $request = $this->request();
        // dd($this->employeeId);
        $model = $model->leftJoin('users', 'users.id', '=', 'insurances.employee_id')->leftJoin('drivers', 'drivers.id', '=', 'insurances.driver_id')
            ->select('insurances.*', 'users.name as employee_name', 'drivers.name as driver_name')->orderBy('insurances.id', 'desc');
        if ($this->driverId == null && $this->employeeId == null) {

        } else {
            if ($this->driverId == 0) {
                $model->where('insurances.employee_id', $this->employeeId);
            } else{
                $model->where('insurances.driver_id', $this->driverId);
            }
        }

        if (in_array('employee', $this->assignRole) && count($this->assignRole) < 2) {
            $model->where(function ($query) use ($request) {
                $query->where('insurances.employee_id', user()->id)->where('insurances.status', 'active');
            });
        }

        if ($request->searchText != '') {
            $model->where(function ($query) use ($request) {
                $query->where('insurances.company', 'like', '%' . $request->searchText . '%')
                    ->orWhere('insurances.policy_no', 'like', '%' . $request->searchText . '%')
                    ->orWhere('insurances.class', 'like', '%' . $request->searchText . '%')
                    ->orWhere('users.name', 'like', '%' . $request->searchText . '%')->orWhere('drivers.name', 'like', '%' . $request->searchText . '%');
            });
        }

        if ($request->employeeId != '' && $request->employeeId != null && $request->employeeId != 'all') {
            $model->where('insurances.employee_id', $request->employeeId);
        }

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('insurances-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["insurances-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    })
                }',
            ]);

        if (canDataTableExport()) {
            $dataTable->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
        }

        return $dataTable;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false,
                'visible' => !in_array('client', user_roles()) && ($this->driverId == null && $this->employeeId == null) // ✅ Added condition
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.employee') . ' / ' . __('app.driver') => [
                'data' => 'employee_name',
                'name' => 'employee_name',
                'title' => __('app.employee'),
                'visible' => ($this->driverId == null && $this->employeeId == null) // ✅ Added condition
            ],
            __('modules.insurance.issue_date') => ['data' => 'issue_date', 'name' => 'issue_date', 'title' => __('modules.insurance.issue_date')],
            __('modules.insurance.expiry_date') => ['data' => 'expiry_date', 'name' => 'expiry_date', 'title' => __('modules.insurance.expiry_date')],
            __('app.company_name') => ['data' => 'company', 'name' => 'company', 'title' => __('app.company_name')],
            __('app.policy_no') => ['data' => 'policy_no', 'name' => 'policy_no', 'title' => __('app.policy_no')],
            __('app.class') => ['data' => 'class', 'name' => 'class', 'title' => __('app.class')],
            // ✅ New: Status column
            __('app.status') => [
                'data' => 'status',
                'name' => 'status',
                'title' => __('app.status'),
            ],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
                ->visible($this->driverId == null && $this->employeeId == null) // ✅ Add this
        ];
    }

}
