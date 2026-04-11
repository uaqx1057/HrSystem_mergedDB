<?php

namespace App\DataTables;

use App\Models\AirTicket;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class AirTicketDataTable extends BaseDataTable
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
                return '<input type="checkbox" class="select-table-row" id="datatable-row-' . $row->id . '" name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->addColumn('action', function ($row) {

                $action = '<div class="task_view">
<a href="' . route('air-tickets.show', [$row->id]) . '" class="taskView text-darkest-grey f-w-500 openRightModal">' . __('app.view') . '</a>
                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                if ($this->editInsurancePermission == 'all') {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('air-tickets.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if ($this->deleteInsurancePermission == 'all') {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-air-ticket-id="' . $row->id . '">
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
                $name = $row->employee_name ?? '-';
                return '<h5 class="mb-0 f-13 text-darkest-grey">' . $name . '</h5>';
            })
            // ✅ Fix 1: editColumn key must match the column 'data' name ('date')
            // ✅ Fix 2: Use Carbon::parse() to safely format date regardless of model cast
            ->editColumn('date', function ($row) {
                return $row->date ? \Carbon\Carbon::parse($row->date)->format(company()->date_format) : '-';
            })
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            // ✅ Fix 3: Added 'check' to rawColumns
            ->rawColumns(['action', 'employee_name', 'check']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AirTicket $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AirTicket $model)
    {
        $request = $this->request();

        $model = $model->leftJoin('users', 'users.id', '=', 'air_tickets.employee_id')
            ->select('air_tickets.*', 'users.name as employee_name');

        if ($request->searchText != '') {
            $model->where(function ($query) use ($request) {
                // ✅ Fix 4: Search on actual column 'users.name', not the alias 'employee_name'
                $query->where('users.name', 'like', '%' . $request->searchText . '%');
            });
        }

        if ($request->employeeId != '' && $request->employeeId != null && $request->employeeId != 'all') {
            $model->where('air_tickets.employee_id', $request->employeeId);
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
        // ✅ Fix 5: Use unique table ID 'air-tickets-table' instead of 'insurances-table'
        $dataTable = $this->setBuilder('air-tickets-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["air-tickets-table"].buttons().container()
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
            // ✅ Fix 6: Added 'check' column for bulk quick-actions
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false,
                'visible' => !in_array('client', user_roles())
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.employee') => [
                'data' => 'employee_name',
                'name' => 'employee_name',
                'title' => __('app.employee')
            ],
            __('modules.airTicket.date') => ['data' => 'date', 'name' => 'date', 'title' => __('modules.airTicket.date')],

            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }

}
