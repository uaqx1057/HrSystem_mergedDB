<?php

namespace App\DataTables;

use App\Models\AdvanceSalary;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class AdvanceSalaryDataTable extends BaseDataTable
{
    private $viewPermission;
    private $editPermission;
    private $deletePermission;
    private $approveRejectPermission;
    private $assignRole;

    public function __construct()
    {
        parent::__construct();
        $this->viewPermission = user()->permission('view_advance_salary');
        $this->editPermission = user()->permission('edit_advance_salary');
        $this->deletePermission = user()->permission('delete_advance_salary');
        $this->approveRejectPermission = user()->permission('approve_or_reject_advance_salary');
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
                $action = '<div class="task_view">';
                if (
                    $this->viewPermission == 'all'
                    || ($this->viewPermission == 'branch' && user()->branch_id == 6)
                    || ($this->viewPermission == 'branch' && user()->branch_id == $row->employee_branch)
                    || ($this->viewPermission == 'added' && user()->id == $row->added_by)
                    || ($this->viewPermission == 'owned' && user()->id == $row->employee_id)
                    || ($this->viewPermission == 'both' && (user()->id == $row->employee_id
                    || user()->id == $row->added_by))
                ) {
                $action .= '<a href="' . route('advance-salaries.show', [$row->id]) . '" class="taskView text-darkest-grey f-w-500 openRightModal">' . __('app.view') . '</a>';
                }

                    $action .= '<div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                if ($row->status == 'pending' &&
                    ($this->approveRejectPermission == 'all'
                    || ($this->approveRejectPermission == 'branch' && user()->branch_id == 6)
                    || ($this->approveRejectPermission == 'branch' && user()->branch_id == $row->employee_branch)
                    || ($this->approveRejectPermission == 'added' && user()->id == $row->added_by)
                    || ($this->approveRejectPermission == 'owned' && user()->id == $row->employee_id)
                    || ($this->approveRejectPermission == 'both' && (user()->id == $row->employee_id
                    || user()->id == $row->added_by)))
                ) {
                    $action .= '<a class="dropdown-item salary-action-approved" href="javascript:;" data-salary-id="' . $row->id . '" data-action="approved">
                            <i class="fa fa-check mr-2"></i> ' . __('app.approve') . '
                        </a>
                        <a class="dropdown-item salary-action-reject" href="javascript:;" data-salary-id="' . $row->id . '" data-action="rejected">
                            <i class="fa fa-times mr-2"></i> ' . __('app.reject') . '
                        </a>';
                }

                if (
                    $this->editPermission == 'all'
                    || ($this->editPermission == 'branch' && user()->branch_id == 6)
                    || ($this->editPermission == 'branch' && user()->branch_id == $row->employee_branch)
                    || ($this->editPermission == 'added' && user()->id == $row->added_by)
                    || ($this->editPermission == 'owned' && user()->id == $row->employee_id)
                    || ($this->editPermission == 'both' && (user()->id == $row->employee_id
                    || user()->id == $row->added_by))
                ) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('advance-salaries.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if (
                    $this->deletePermission == 'all'
                    || ($this->deletePermission == 'branch' && user()->branch_id == 6)
                    || ($this->deletePermission == 'branch' && user()->branch_id == $row->employee_branch)
                    || ($this->deletePermission == 'added' && user()->id == $row->added_by)
                    || ($this->deletePermission == 'owned' && user()->id == $row->employee_id)
                    || ($this->deletePermission == 'both' && (user()->id == $row->employee_id
                    || user()->id == $row->added_by))
                ) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-advance-salary-id="' . $row->id . '">
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
            ->editColumn('advance_salary', function ($row) {
                return $row->advance_salary ? number_format($row->advance_salary, 2) : '-';
            })
            ->editColumn('date', function ($row) {
                return $row->date ? \Carbon\Carbon::parse($row->date)->format(company()->date_format) : '-';
            })
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
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            ->rawColumns(['action', 'employee_name', 'check', 'status']);
    }

    public function query(AdvanceSalary $model)
    {
        $request = $this->request();
        $model = $model->leftJoin('users', 'users.id', '=', 'advance_salaries.employee_id')
            ->select('advance_salaries.*', 'users.name as employee_name', 'users.branch_id as employee_branch')->orderBy('advance_salaries.id', 'desc');

        if ($request->searchText != '') {
            $model->where(function ($query) use ($request) {
                $query->where('users.name', 'like', '%' . $request->searchText . '%');
            });
        }

        if ($request->employeeId != '' && $request->employeeId != null && $request->employeeId != 'all') {
            $model->where('advance_salaries.employee_id', $request->employeeId);
        }

        // if (count($this->assignRole) < 2) {
        //     $model->where(function ($query) use ($request) {
        //         $query->where('users.id', user()->id);
        //     });
        // }
        if ($this->viewPermission == 'added') {
            $model->where(function ($query) use ($request) {
                $query->where('advance_salaries.added_by', user()->id);
            });
        }

        if ($this->viewPermission == 'owned') {
            $model->where(function ($query) use ($request) {
                $query->where('advance_salaries.employee_id', user()->id);
            });
        }

        if ($this->viewPermission == 'both') {
            $model->where(function ($query) use ($request) {
                $query->where('advance_salaries.employee_id', user()->id)->orWhere('advance_salaries.added_by', user()->id);
            });
        }

        if ($this->viewPermission == 'branch' && user()->branch_id !== 6) {
            $model->where(function ($query) use ($request) {
                $query->where('users.branch_id', user()->branch_id);
            });
        }

        return $model;
    }

    public function html()
    {
        $dataTable = $this->setBuilder('advance-salaries-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["advance-salaries-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: "[data-toggle=\"tooltip\"]"
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
            __('modules.advanceSalary.amount') => ['data' => 'advance_salary', 'name' => 'advance_salary', 'title' => __('modules.advanceSalary.amount')],
            __('modules.advanceSalary.date') => ['data' => 'date', 'name' => 'date', 'title' => __('modules.advanceSalary.date')],
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
