<?php

namespace App\DataTables;

use App\Models\AirTicket;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class AirTicketDataTable extends BaseDataTable
{
    private $editAirTicketPermission;
    private $deleteInsurancePermission;
    private $assignRole;

    public function __construct()
    {
        parent::__construct();
        $this->editAirTicketPermission = user()->permission('edit_employees');
        $this->deleteInsurancePermission = user()->permission('delete_employees');
        $this->assignRole = user()->roles->pluck('name')->toArray();
    }

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

                    // Approve/Reject Buttons (Only if status is pending)
                if ($row->status == 'pending' && user()->permission('approve_or_reject_air_tickets') == 'all') {
                    $action .= '<a class="dropdown-item ticket-action-approved" href="javascript:;" data-ticket-id="' . $row->id . '" data-action="approved">
                            <i class="fa fa-check mr-2"></i> ' . __('app.approve') . '
                        </a>
                        <a class="dropdown-item ticket-action-reject" href="javascript:;" data-ticket-id="' . $row->id . '" data-action="rejected">
                            <i class="fa fa-times mr-2"></i> ' . __('app.reject') . '
                        </a>';
                }

                if (user()->permission('edit_air_tickets') == 'all') {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('air-tickets.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if (user()->permission('delete_air_tickets') == 'all') {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-air-ticket-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }

                $action .= '</div></div></div>';
                return $action;
            })
            ->editColumn('employee_name', function ($row) {
                $name = $row->employee_name ?? '-';
                return '<h5 class="mb-0 f-13 text-darkest-grey">' . $name . '</h5>';
            })
            ->editColumn('date', function ($row) {
                return $row->date ? \Carbon\Carbon::parse($row->date)->format(company()->date_format) : '-';
            })
            // --- ADDED STATUS COLUMN LOGIC ---
            ->editColumn('status', function ($row) {
                if ($row->status == 'approved') {
                    $class = 'text-light-green';
                    $status = __('app.approved');
                } else if ($row->status == 'pending') {
                    $class = 'text-yellow';
                    $status = __('app.pending');
                } else {
                    $class = 'text-red';
                    $status = __('app.rejected');
                }
                return '<i class="fa fa-circle mr-1 ' . $class . ' f-10"></i> ' . $status;
            })
            // ---------------------------------
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            // ✅ Updated rawColumns to include 'status'
            ->rawColumns(['action', 'employee_name', 'check', 'status']);
    }

    public function query(AirTicket $model)
    {
        $request = $this->request();
        $model = $model->leftJoin('users', 'users.id', '=', 'air_tickets.employee_id')
            ->select('air_tickets.*', 'users.name as employee_name')->orderBy('air_tickets.id', 'desc');

        if ($request->searchText != '') {
            $model->where(function ($query) use ($request) {
                $query->where('users.name', 'like', '%' . $request->searchText . '%');
            });
        }

        if ($request->employeeId != '' && $request->employeeId != null && $request->employeeId != 'all') {
            $model->where('air_tickets.employee_id', $request->employeeId);
        }

        if (in_array('employee', $this->assignRole) && count($this->assignRole) < 2) {
            $model->where(function ($query) use ($request) {
                $query->where('users.id', user()->id);
            });
        }

        return $model;
    }

    public function html()
    {
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
            __('app.employee') => [
                'data' => 'employee_name',
                'name' => 'employee_name',
                'title' => __('app.employee')
            ],
            __('modules.airTicket.date') => ['data' => 'date', 'name' => 'date', 'title' => __('modules.airTicket.date')],
            // ✅ ADDED STATUS COLUMN HEADER
            __('app.status') => ['data' => 'status', 'name' => 'status', 'title' => __('app.status')],

            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }
}
