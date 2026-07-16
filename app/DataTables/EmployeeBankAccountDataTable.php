<?php

namespace App\DataTables;

use App\Models\EmployeeBankAccount;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class EmployeeBankAccountDataTable extends BaseDataTable
{
    private $editPermission;
    private $deletePermission;
    private $viewPermission;
    private $employeeId;

    public function __construct($employeeId = null)
    {
        parent::__construct();
        $this->editPermission = user()->permission('edit_employee_bank_account');
        $this->deletePermission = user()->permission('delete_employee_bank_account');
        $this->viewPermission = user()->permission('view_employee_bank_account');
        $this->employeeId = $employeeId;
    }

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="select-table-row" id="datatable-row-' . $row->id . '" name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view"><div class="dropdown">';
                $action .= '<a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link" id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="icon-options-vertical icons"></i></a>';
                $action .= '<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';
                $action .= '<a href="' . route('employee-bank-accounts.show', $row->id) . '" class="dropdown-item openRightModal"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if ($this->editPermission == 'all' || ($this->editPermission == 'added' && user()->id == $row->added_by) || ($this->editPermission == 'owned' && user()->id == $row->employee_id) || ($this->editPermission == 'both' && (user()->id == $row->added_by || user()->id == $row->employee_id)) ) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('employee-bank-accounts.edit', [$row->id]) . '"><i class="fa fa-edit mr-2"></i>' . __('app.edit') . '</a>';
                }

                if ($this->deletePermission == 'all' || ($this->deletePermission == 'added' && user()->id == $row->added_by) || ($this->deletePermission == 'owned' && user()->id == $row->employee_id) || ($this->deletePermission == 'both' && (user()->id == $row->added_by || user()->id == $row->employee_id)) ) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-employee-bank-account-id="' . $row->id . '"><i class="fa fa-trash mr-2"></i>' . __('app.delete') . '</a>';
                }

                $action .= '</div></div></div>';

                return $action;
            })
            ->addColumn('employee_name', function ($row) {
                return $row->employee ? '<h5 class="mb-0 f-13 text-darkest-grey">' . e($row->employee->name) . '</h5>' : '--';
            })
            ->editColumn('is_main_account', function ($row) {
                return $row->is_main_account ? '<span class="badge badge-success">' . __('app.yes') . '</span>' : '<span class="badge badge-secondary">' . __('app.no') . '</span>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at->format('Y-m-d H:i') : '--';
            })
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            ->rawColumns(['action', 'employee_name', 'is_main_account', 'check']);
    }

    public function query(EmployeeBankAccount $model)
    {
        $request = $this->request();
        $query = $model->newQuery()->with('employee');

        if ($request->searchText) {
            $query->where(function ($q) use ($request) {
                $q->where('bank_name', 'like', '%' . $request->searchText . '%')
                    ->orWhere('iban_number', 'like', '%' . $request->searchText . '%')
                    ->orWhere('account_number', 'like', '%' . $request->searchText . '%')
                    ->orWhere('swift_code', 'like', '%' . $request->searchText . '%')
                    ->orWhereHas('employee', function ($employee) use ($request) {
                        $employee->where('name', 'like', "%{$request->searchText}%");
                    });
            });
        }

        $employeeId = $request->employeeId ?? $this->employeeId;

        if ($employeeId != 'all' && !is_null($employeeId)) {
            $query->where('employee_id', $employeeId);
        }

        if ($this->viewPermission == 'added') {
            $query->where('added_by', user()->id);
        }
        elseif ($this->viewPermission == 'owned') {
            $query->where('employee_id', user()->id);
        }
        elseif ($this->viewPermission == 'both') {
            $query->where(function ($q) {
                $q->where('employee_id', user()->id)
                    ->orWhere('added_by', user()->id);
            });
        }

        return $query;
    }

    // public function html()
    // {
    //     return $this->setBuilder('employee-bank-accounts-table', 2)
    //         ->parameters([
    //             'initComplete' => 'function () {
    //                 window.LaravelDataTables["employee-bank-accounts-table"].buttons().container()
    //                     .appendTo("#table-actions")
    //             }',
    //         ])
    //         ->buttons(
    //             Button::make('export')
    //         );
    // }

    public function html()
    {
        $dataTable = $this->setBuilder('employee-bank-accounts-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["employee-bank-accounts-table"].buttons().container()
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

    public function getColumns()
    {
        return [
            Column::make('check')->title('<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">')->orderable(false)->searchable(false)->exportable(false)->printable(false)->width(50),
            // Column::make('id')->title(__('app.id')),
            Column::make('employee_name')->title(__('app.employee')),
            Column::make('bank_name')->title(__('app.bankName')),
            Column::make('iban_number')->title(__('app.ibanNumber')),
            Column::make('account_number')->title(__('app.accountNumber')),
            Column::make('swift_code')->title(__('app.swiftCode')),
            Column::make('is_main_account')->title(__('app.mainAccount')),
            // Column::make('created_at')->title(__('app.createdAt')),
            Column::computed('action')->title(__('app.action'))->orderable(false)->searchable(false)->exportable(false)->printable(false),
        ];
    }

    protected function filename(): string
    {
        return 'employee-bank-accounts-' . now()->format('Y-m-d-H-i-s');
    }
}
