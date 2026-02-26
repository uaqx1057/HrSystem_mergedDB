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

    public function __construct()
    {
        parent::__construct();
        $this->editInsurancePermission = user()->permission('edit_employees');
        $this->deleteInsurancePermission = user()->permission('delete_employees');
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
<a href="' . route('insurance.show', [$row->id]) . '" class="taskView text-darkest-grey f-w-500 openRightModal">' . __('app.view') . '</a>
                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

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
                return '<h5 class="mb-0 f-13 text-darkest-grey">' . ($row->employee_name ?? '-') . '</h5>';
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
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            ->rawColumns(['action', 'employee_name', 'check']);
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

        $model = $model->leftJoin('users', 'users.id', '=', 'insurances.employee_id')
            ->select('insurances.*', 'users.name as employee_name');

        if ($request->searchText != '') {
            $model->where(function ($query) use ($request) {
                $query->where('insurances.company', 'like', '%' . $request->searchText . '%')
                    ->orWhere('insurances.policy_no', 'like', '%' . $request->searchText . '%')
                    ->orWhere('insurances.class', 'like', '%' . $request->searchText . '%')
                    ->orWhere('users.name', 'like', '%' . $request->searchText . '%');
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
                'visible' => !in_array('client', user_roles())
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.employee') => ['data' => 'employee_name', 'name' => 'employee_name', 'title' => __('app.employee')],
            __('modules.insurance.issue_date') => ['data' => 'issue_date', 'name' => 'issue_date', 'title' => __('modules.insurance.issue_date')],
            __('modules.insurance.expiry_date') => ['data' => 'expiry_date', 'name' => 'expiry_date', 'title' => __('modules.insurance.expiry_date')],
            __('app.company_name') => ['data' => 'company', 'name' => 'company', 'title' => __('app.company_name')],
            __('app.policy_no') => ['data' => 'policy_no', 'name' => 'policy_no', 'title' => __('app.policy_no')],
            __('app.class') => ['data' => 'class', 'name' => 'class', 'title' => __('app.class')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }

}
