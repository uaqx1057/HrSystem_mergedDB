<?php

namespace App\DataTables;

use App\Models\BaseModel;
use App\Models\EmployeeDetails;
use App\Scopes\ActiveScope;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Model;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;

class TerminatedDataTable extends BaseDataTable
{

    private $viewEmployeePermission;

    public function __construct()
    {
        parent::__construct();
        $this->viewEmployeePermission = user()->permission('view_terminated_employees');
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $datatables = datatables()->eloquent($query);

        $datatables->editColumn('current_role_name', function ($row) {
            $userRole = $row->roles->pluck('name')->toArray();

            if (in_array('admin', $userRole)) {
                return $row->roles()->withoutGlobalScopes()->latest()->first()->display_name;
            }

            return $row->current_role_name;
        });

        $datatables->editColumn('iqama_expiry_date', function ($row) {
            return $row->iqama_expiry_date
                ? Carbon::parse($row->iqama_expiry_date)->format('d-m-Y')
                : '--';
        });

        $datatables->addColumn('action', function ($row) {
            $action = '<div class="task_view">

                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

            $action .= '<a href="' . route('employees.show-terminated', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

            $action .= '</div>
                    </div>
                </div>';

            return $action;
        });

        $datatables->addColumn('employee_name', function ($row) {
            return $row->name;
        });

        $datatables->editColumn(
            'created_at',
            function ($row) {
                return Carbon::parse($row->created_at)->translatedFormat($this->company->date_format);
            }
        );

        $datatables->editColumn(
            'status',
            function ($row) {
                return '<i class="fa fa-circle mr-1 text-red f-10"></i>' . __('app.menu.terminated');
            }
        );

        $datatables->editColumn('name', function ($row) {
            return view('components.employee', [
                'user' => $row,
                'leave' => true
            ]);
        });

        $datatables->editColumn('employee_id', function ($row) {
            return '<a href="' . route('employees.show-terminated', [$row->id]) . '" class="text-darkest-grey">' . $row->employee_id . '</a>';
        });

        $datatables->editColumn('joining_date', function ($row) {
            return Carbon::parse($row->joining_date)->translatedFormat('Y-m-d');
        });

        $datatables->addIndexColumn();
        $datatables->setRowId(function ($row) {
            return 'row-' . $row->id;
        });
        $datatables->removeColumn('roleId');
        $datatables->removeColumn('roleName');
        $datatables->removeColumn('current_role');

        $customFieldColumns = CustomField::customFieldData($datatables, EmployeeDetails::CUSTOM_FIELD_MODEL, 'employeeDetail');

        $datatables->rawColumns(array_merge(['name', 'action', 'status', 'employee_id'], $customFieldColumns));

        return $datatables;
    }

    /**
     * @param User $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(User $model)
    {
        $request = $this->request();

        $users = $model->with('role', 'roles', 'employeeDetail', 'session')
            ->withoutGlobalScope(ActiveScope::class)
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->leftJoin('designations', 'employee_details.designation_id', '=', 'designations.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->select('users.id', 'users.branch_id', 'employee_details.added_by', 'users.salutation', 'users.name', 'users.email', 'users.created_at', 'roles.name as roleName', 'roles.id as roleId', 'users.image', 'users.gender', 'users.status', DB::raw('(select user_roles.role_id from role_user as user_roles where user_roles.user_id = users.id ORDER BY user_roles.role_id DESC limit 1) as `current_role`'), DB::raw('(select roles.name from roles as roles where roles.id = current_role limit 1) as `current_role_name`'), 'designations.name as designation_name', 'employee_details.employee_id', 'employee_details.joining_date', 'employee_details.iqama_no', 'employee_details.iqama_expiry_date', 'employee_details.sponsor_kafala')
            ->onlyEmployee()
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('employee_terminations')
                    ->whereColumn('employee_terminations.user_id', 'users.id')
                    ->where('employee_terminations.status', 'completed');
            });

        if ($request->searchText != '') {
            $users = $users->where(function ($query) {
                $query->where('users.name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('users.email', 'like', '%' . request('searchText') . '%')
                    ->orWhere('employee_details.employee_id', 'like', '%' . request('searchText') . '%');
            });
        }

        if ($this->viewEmployeePermission == 'added') {
            $users = $users->where('employee_details.added_by', user()->id);
        }

        if ($this->viewEmployeePermission == 'owned') {
            $users = $users->where('employee_details.user_id', user()->id);
        }

        if ($this->viewEmployeePermission == 'both') {
            $users = $users->where(function ($q) {
                $q->where('employee_details.user_id', user()->id);
                $q->orWhere('employee_details.added_by', user()->id);
            });
        }

        if ($this->viewEmployeePermission == 'branch') {
            $currentBranchId = user()->branch_id;

            if (!is_null($currentBranchId)) {
                $users = $users->where('users.branch_id', $currentBranchId);
            } else {
                $users = $users->whereRaw('1 = 0');
            }
        }

        return $users->groupBy('users.id');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('employees-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["employees-table"].buttons().container()
                     .appendTo( "#table-actions")
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
        $data = [
            __('app.id') => ['data' => 'id', 'name' => 'id', 'title' => __('app.id'), 'visible' => false],
            __('modules.employees.employeeId') => ['data' => 'employee_id', 'name' => 'employee_id', 'title' => __('modules.employees.employeeId')],
            __('app.name') => ['data' => 'name', 'name' => 'name', 'exportable' => false, 'title' => __('app.name')],
            __('app.employee') => ['data' => 'employee_name', 'name' => 'name', 'visible' => false, 'title' => __('app.employee')],
            __('app.email') => ['data' => 'email', 'name' => 'email', 'title' => __('app.email')],
            __('modules.employees.role') => ['data' => 'current_role_name', 'name' => 'current_role_name', 'visible' => false, 'title' => __('modules.employees.role')],
            __('modules.employees.joiningDate') => ['data' => 'joining_date', 'name' => 'joining_date', 'visible' => false, 'title' => __('modules.employees.joiningDate')],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'title' => __('app.status')]
        ];

        $action = [
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];

        return array_merge($data, CustomFieldGroup::customFieldsDataMerge(new EmployeeDetails()), $action);
    }

}
