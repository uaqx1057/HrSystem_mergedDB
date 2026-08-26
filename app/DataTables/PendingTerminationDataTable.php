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

class PendingTerminationDataTable extends BaseDataTable
{

    private $editEmployeePermission;
    private $deleteEmployeePermission;
    private $viewEmployeePermission;
    private $changeEmployeeRolePermission;
    private $terminateEmployeePermission;
    private $itClearancePermission;
    private $financeClearancePermission;
    private $assignRole;

    public function __construct()
    {
        parent::__construct();
        $this->editEmployeePermission = user()->permission('edit_employees');
        $this->deleteEmployeePermission = user()->permission('delete_employees');
        $this->viewEmployeePermission = user()->permission('view_pending_termination_employees');
        $this->changeEmployeeRolePermission = user()->permission('change_employee_role');
        $this->terminateEmployeePermission = user()->permission('manage_termination_employees');
        $this->itClearancePermission = user()->permission('manage_it_clearance');
        $this->financeClearancePermission = user()->permission('manage_finance_clearance');

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

        $roles = Role::where('name', '<>', 'client')->get();
        $datatables = datatables()->eloquent($query);
        // $datatables->addColumn('check', function ($row) {
        //     if (!$row->hasRole('admin') && $row->id != user()->id) {
        //         return '<input type="checkbox" class="select-table-row" id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
        //     }

        //     return '--';
        // });

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

        $datatables->addColumn('role', function ($row) use ($roles) {
            $userRole = $row->roles->pluck('name')->toArray();

            if (in_array('admin', $userRole)) {
                $uRole = $row->roles()->withoutGlobalScopes()->latest()->first()->display_name;
            }
            else {
                $uRole = $row->current_role_name;
            }

            if (in_array('admin', $userRole) && !in_array('admin', user_roles())) {
                return $uRole . ' <i data-toggle="tooltip" data-original-title="' . __('messages.roleCannotChange') . '" class="fa fa-info-circle"></i>';
            }

            if ($row->id == user()->id) {
                return $uRole . ' <i data-toggle="tooltip" data-original-title="' . __('messages.roleCannotChange') . '" class="fa fa-info-circle"></i>';
            }

            $role = '<select class="form-control select-picker assign_role" data-user-id="' . $row->id . '">';

            foreach ($roles as $item) {
                if (
                    $item->name != 'admin'
                    || ($item->name == 'admin' && in_array('admin', user_roles()))
                ) {

                    $role .= '<option ';

                    if (
                        (in_array($item->name, $userRole) && $item->name == 'admin')
                        || (in_array($item->name, $userRole) && !in_array('admin', $userRole))
                    ) {
                        $role .= 'selected';
                    }

                    $role .= ' value="' . $item->id . '">' . $item->display_name . '</option>';

                }
            }

            $role .= '</select>';

            return $role;
        });
        $datatables->addColumn('action', function ($row) {
            $userRole = $row->roles->pluck('name')->toArray();
            $action = '<div class="task_view">

                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

            $action .= '<a href="' . route('employees.show-terminate-pending', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

            $canManageIt = $this->itClearancePermission == 'all'
                || ($this->itClearancePermission == 'branch' && !is_null(user()->branch_id) && $row->branch_id == user()->branch_id);

            if ($canManageIt) {
                $action .= '<a href="' . route('employees.it-clearance', [$row->id]) . '" class="dropdown-item"><i class="fa fa-laptop mr-2"></i>IT Clearance</a>';
            }

            $canManageFinance = $this->financeClearancePermission == 'all'
                || ($this->financeClearancePermission == 'branch' && !is_null(user()->branch_id) && $row->branch_id == user()->branch_id);

            if ($canManageFinance) {
                $action .= '<a href="' . route('employees.finance-clearance', [$row->id]) . '" class="dropdown-item"><i class="fa fa-money mr-2"></i>Finance Clearance</a>';
            }

            $canRevert = $this->terminateEmployeePermission == 'all'
                || ($this->terminateEmployeePermission == 'branch' && user()->branch_id == 6)
                || ($this->terminateEmployeePermission == 'branch' && !is_null(user()->branch_id) && $row->branch_id == user()->branch_id);

            if ($canRevert) {
                $action .= '<a class="dropdown-item revert-termination-row" href="javascript:;" data-user-id="' . $row->id . '" data-exit-type="' . ($row->exit_type ?? 'termination') . '">
                            <i class="fa fa-undo mr-2"></i>
                            Revert ' . ucfirst($row->exit_type ?? 'termination') . '
                        </a>';
            }

            $action .= '</div>
                    </div>
                </div>';

            return $action;
        });
        $datatables->addColumn('employee_name', function ($row) {
            return $row->name;
        });
        $datatables->addColumn('exit_type_label', function ($row) {
            return ucfirst($row->exit_type ?? 'termination');
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
                return ' <i class="fa fa-circle mr-1 text-warning f-10"></i>Pending ' . ucfirst($row->exit_type ?? 'termination');
            }
        );
        $datatables->editColumn('name', function ($row) {
            return view('components.employee', [
                'user' => $row,
                'leave' => true
            ]);
        });
        $datatables->editColumn('employee_id', function ($row) {
            return '<a href="' . route('employees.show', [$row->id]) . '" class="text-darkest-grey">' . $row->employee_id . '</a>';
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

        // Custom Fields For export
        $customFieldColumns = CustomField::customFieldData($datatables, EmployeeDetails::CUSTOM_FIELD_MODEL, 'employeeDetail');

        $datatables->rawColumns(array_merge(['name', 'action', 'role', 'status', 'check', 'employee_id'], $customFieldColumns));

        return $datatables;
    }

    /**
     * @param User $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(User $model)
    {
        $request = $this->request();

        $userRoles = '';

        if ($request->role != 'all' && $request->role != '') {
            $userRoles = Role::findOrFail($request->role);
        }

        $users = $model->with('role', 'roles', 'employeeDetail', 'session')
            ->withoutGlobalScope(ActiveScope::class)
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->leftJoin('designations', 'employee_details.designation_id', '=', 'designations.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->select('users.id', 'users.branch_id', 'employee_details.added_by', 'users.salutation', 'users.name', 'users.email', 'users.created_at', 'roles.name as roleName', 'roles.id as roleId', 'users.image', 'users.gender', 'users.status', DB::raw('(select employee_terminations.exit_type from employee_terminations where employee_terminations.user_id = users.id and employee_terminations.status = \'pending\' order by employee_terminations.id desc limit 1) as exit_type'), DB::raw('(select user_roles.role_id from role_user as user_roles where user_roles.user_id = users.id ORDER BY user_roles.role_id DESC limit 1) as `current_role`'), DB::raw('(select roles.name from roles as roles where roles.id = current_role limit 1) as `current_role_name`'), 'designations.name as designation_name', 'employee_details.employee_id', 'employee_details.joining_date','employee_details.iqama_no','employee_details.iqama_expiry_date','employee_details.sponsor_kafala')
            ->onlyEmployee()
                ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('employee_terminations')
                    ->whereColumn('employee_terminations.user_id', 'users.id')
                    ->where('employee_terminations.status', 'pending')
                    ->whereIn('employee_terminations.exit_type', ['termination', 'resignation']);
            });


        if ($request->status != 'all' && $request->status != '') {

            if ($request->status === 'ex_employee') {
                $users = $users->whereNotNull('employee_details.last_date');
                $users->whereRaw('Date(employee_details.last_date) <= ?', [now()]);
            }
            else {
                $users = $users->where('users.status', $request->status);
            }
        }

        if ($request->gender != 'all' && $request->gender != '') {
            $users = $users->where('users.gender', $request->gender);
        }

        if ($request->employee != 'all' && $request->employee != '') {
            $users = $users->where('users.id', $request->employee);
        }

        if ($request->designation != 'all' && $request->designation != '') {
            $users = $users->where('employee_details.designation_id', $request->designation);
        }

        if ($request->department != 'all' && $request->department != '') {
            $users = $users->where('employee_details.department_id', $request->department);
        }

        if ($request->role != 'all' && $request->role != '' && $userRoles) {
            if ($userRoles->name == 'admin') {
                $users = $users->where('roles.id', $request->role);
            }
            elseif ($userRoles->name == 'employee') {
                $users = $users->where(DB::raw('(select user_roles.role_id from role_user as user_roles where user_roles.user_id = users.id ORDER BY user_roles.role_id DESC limit 1)'), $request->role)
                    ->having('roleName', '<>', 'admin');
            }
            else {
                $users = $users->where(DB::raw('(select user_roles.role_id from role_user as user_roles where user_roles.user_id = users.id ORDER BY user_roles.role_id DESC limit 1)'), $request->role);
            }
        }

        if ((is_array($request->skill) && $request->skill[0] != 'all') && $request->skill != '' && $request->skill != null && $request->skill != 'null') {
            $users = $users->join('employee_skills', 'employee_skills.user_id', '=', 'users.id')
                ->whereIn('employee_skills.skill_id', $request->skill);
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
                if($currentBranchId !== 6){
                    $users = $users->where('users.branch_id', $currentBranchId);
                }
            } else {
                $users = $users->whereRaw('1 = 0');
            }
        }

        if ($request->startDate != '' && $request->endDate != '') {
            $startDate = Carbon::createFromFormat($this->company->date_format, $request->startDate)->toDateString();
            $endDate = Carbon::createFromFormat($this->company->date_format, $request->endDate)->toDateString();

            $users = $users->whereRaw('Date(employee_details.joining_date) >= ?', [$startDate])->whereRaw('Date(employee_details.joining_date) <= ?', [$endDate]);
        }

        if ($request->status == 'ex_employee' && isset($request->lastStartDate) && isset($request->lastEndDate) && $request->lastStartDate != '' && $request->lastEndDate != '') {
            $startDate = Carbon::createFromFormat($this->company->date_format, $request->lastStartDate)->toDateString();
            $endDate = Carbon::createFromFormat($this->company->date_format, $request->lastEndDate)->toDateString();
            $users = $users->whereNotNull('last_date')->whereRaw('Date(employee_details.last_date) >= ?', [$startDate])->whereRaw('Date(employee_details.last_date) <= ?', [$endDate]);
        }

        if ($request->searchText != '') {
            $users = $users->where(function ($query) {
                $query->where('users.name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('users.email', 'like', '%' . request('searchText') . '%')
                    ->orWhere('employee_details.employee_id', 'like', '%' . request('searchText') . '%');
            });
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
                'fnDrawCallback' => 'function( oSettings ) {
                   $(".select-picker").selectpicker();
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
            // 'check' => [
            //     'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
            //     'exportable' => false,
            //     'orderable' => false,
            //     'searchable' => false
            // ],
            // '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.id') => ['data' => 'id', 'name' => 'id', 'title' => __('app.id'), 'visible' => false],
            __('modules.employees.employeeId') => ['data' => 'employee_id', 'name' => 'employee_id', 'title' => __('modules.employees.employeeId')],
            __('app.name') => ['data' => 'name', 'name' => 'name', 'exportable' => false, 'title' => __('app.name')],
            __('app.employee') => ['data' => 'employee_name', 'name' => 'name', 'visible' => false, 'title' => __('app.employee')],
            __('app.email') => ['data' => 'email', 'name' => 'email', 'title' => __('app.email')],
            __('Iqama Number') => [
                'data' => 'iqama_no',
                'name' => 'iqama_no',
                'title' => 'Iqama Number'
            ],

            __('Iqama Expiry') => [
                'data' => 'iqama_expiry_date',
                'name' => 'iqama_expiry_date',
                'title' => 'Iqama Expiry'
            ],

            __('Sponsor') => [
                'data' => 'sponsor_kafala',
                'name' => 'sponsor_kafala',
                'title' => 'Sponsor'
            ],
            // __('app.role') => ['data' => 'role', 'name' => 'role', 'width' => '20%', 'orderable' => false, 'exportable' => false, 'title' => __('app.role'), 'visible' => ($this->changeEmployeeRolePermission == 'all')],
            __('modules.employees.role') => ['data' => 'current_role_name', 'name' => 'current_role_name', 'visible' => false, 'title' => __('modules.employees.role')],
            __('modules.employees.joiningDate') => ['data' => 'joining_date', 'name' => 'joining_date', 'visible' => false, 'title' => __('modules.employees.joiningDate')],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'title' => __('app.status')]
            , 'Exit Type' => ['data' => 'exit_type_label', 'name' => 'exit_type', 'title' => 'Exit Type']
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
