<?php

namespace App\DataTables;

use App\Models\AssetAssignment;
use App\Models\CompanyAsset;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class CompanyAssetDataTable extends BaseDataTable
{
    private $editPermission;
    private $deletePermission;
    private $viewPermission;
    private $assignPermission;
    private $historyPermission;

    public function __construct()
    {
        parent::__construct();
        $this->viewPermission = user()->permission('view_company_assets');
        $this->editPermission = user()->permission('edit_company_assets');
        $this->deletePermission = user()->permission('delete_company_assets');
        $this->assignPermission = user()->permission('assign_company_asset_to_employee');
        $this->historyPermission = user()->permission('view_assign_company_assets_to_employee');
    }

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="select-table-row" id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->editColumn('name', function ($row) {
                $meta = collect([$row->brand, $row->type])->filter()->implode(' &middot; ');
                $name = '<h5 class="mb-0 f-13 text-darkest-grey"><a href="' . route('company-assets.show', ['company_asset' => $row->id]) . '" class="openRightModal">' . e($row->name) . '</a></h5>';
                if ($meta !== '') {
                    $name .= '<span class="f-11 text-lightest">' . $meta . '</span>';
                }
                return $name;
            })
            ->editColumn('department', fn ($row) => $row->department_id ? e(optional($row->department)->name) : '--')
            ->editColumn('branch', fn ($row) => $row->branch_id ? e(optional($row->branch)->name) : '--')
            ->editColumn('qty', function ($row) {
                return '<span class="text-darkest-grey">' . (int) $row->available_qty . '</span> <span class="text-lightest">/ ' . (int) $row->qty . ' available</span>';
            })
            ->addColumn('assignment', function ($row) {
                $out = (int) ($row->assigned_count ?? $row->assignedAssignments->count());

                if ($out === 0) {
                    return '<span class="text-lightest f-12">— none out</span>';
                }

                $names = $row->assignedAssignments
                    ->map(fn ($a) => optional($a->employee)->name)
                    ->filter()
                    ->values();

                $shown = $names->take(2)->implode(', ');
                if ($names->count() > 2) {
                    $shown .= ' +' . ($names->count() - 2);
                }

                return '<span class="badge badge-info">' . $out . ' out</span>'
                    . '<div class="f-11 text-lightest text-truncate" style="max-width:180px;" title="' . e($names->implode(', ')) . '">' . e($shown) . '</div>';
            })
            ->editColumn('status', function ($row) {
                $map = [
                    CompanyAsset::STATUS_AVAILABLE          => ['badge-success', 'Available'],
                    CompanyAsset::STATUS_PARTIALLY_ASSIGNED => ['badge-warning', 'Partially assigned'],
                    CompanyAsset::STATUS_ASSIGNED           => ['badge-info', 'Fully assigned'],
                ];
                [$class, $label] = $map[strtolower((string) $row->status)] ?? ['badge-secondary', ucfirst((string) $row->status)];

                return '<span class="badge ' . $class . '">' . e($label) . '</span>';
            })
            ->addColumn('action', function ($row) {
                $canEdit = $this->canTouch($this->editPermission, $row);
                $canDelete = $this->canTouch($this->deletePermission, $row);
                $canAssign = in_array($this->assignPermission, ['all', 'added', 'branch'])
                    && ($this->assignPermission !== 'branch' || hr_has_all_branch_access('company_assets') || user()->branch_id == $row->branch_id)
                    && ($this->assignPermission !== 'added' || user()->id == $row->added_by);
                $canHistory = in_array($this->historyPermission, ['all', 'added', 'owned', 'both', 'branch']);

                $action = '<div class="task_view">';
                $action .= '<a href="' . route('company-assets.show', ['company_asset' => $row->id]) . '" class="taskView text-darkest-grey f-w-500">' . __('app.view') . '</a>';
                $action .= '<div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                if ($canAssign && (int) $row->available_qty > 0) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('company-assets.assign', [$row->id]) . '">
                                <i class="fa fa-user-plus mr-2"></i> ' . __('app.assign') . '</a>';
                }

                if ($canEdit) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('company-assets.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i> ' . __('app.edit') . '</a>';
                }

                if ($canHistory) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('company-assets.view-assign', [$row->id]) . '">
                                <i class="fa fa-history mr-2"></i> ' . __('app.assignmentHistory') . '</a>';
                }

                if ($canDelete) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-asset-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i> ' . __('app.delete') . '</a>';
                }

                $action .= '</div></div></div>';

                return $action;
            })
            ->smart(false)
            ->setRowId(fn ($row) => 'row-' . $row->id)
            ->rawColumns(['check', 'action', 'name', 'department', 'branch', 'qty', 'assignment', 'status']);
    }

    public function query(CompanyAsset $model)
    {
        $request = $this->request();

        $query = $model->newQuery()
            ->with(['department', 'branch', 'assignedAssignments.employee:id,name'])
            ->withCount('assignedAssignments as assigned_count')
            ->orderBy('company_assets.id', 'desc');

        if ($request->searchText != '') {
            $query->where(function ($q) use ($request) {
                $q->where('company_assets.name', 'like', '%' . $request->searchText . '%')
                    ->orWhere('company_assets.catalog', 'like', '%' . $request->searchText . '%')
                    ->orWhere('company_assets.sku_no', 'like', '%' . $request->searchText . '%')
                    ->orWhere('company_assets.type', 'like', '%' . $request->searchText . '%')
                    ->orWhere('company_assets.brand', 'like', '%' . $request->searchText . '%')
                    ->orWhereHas('serials', fn ($s) => $s->where('serial_no', 'like', '%' . $request->searchText . '%'))
                    ->orWhereHas('assignedAssignments.employee', fn ($e) => $e->where('name', 'like', '%' . $request->searchText . '%'));
            });
        }

        if ($this->viewPermission == 'added') {
            $query->where('company_assets.added_by', user()->id);
        } elseif ($this->viewPermission == 'owned') {
            $query->whereHas('assignments', fn ($a) => $a->where('employee_id', user()->id)->where('status', AssetAssignment::STATUS_ASSIGNED));
        } elseif ($this->viewPermission == 'both') {
            $query->where(function ($q) {
                $q->where('company_assets.added_by', user()->id)
                    ->orWhereHas('assignments', fn ($a) => $a->where('employee_id', user()->id)->where('status', AssetAssignment::STATUS_ASSIGNED));
            });
        } elseif ($this->viewPermission == 'branch' && !hr_has_all_branch_access('company_assets')) {
            $query->where('company_assets.branch_id', user()->branch_id);
        }

        return $query;
    }

    private function canTouch($permission, $row): bool
    {
        return $permission == 'all'
            || ($permission == 'branch' && hr_has_all_branch_access('company_assets'))
            || ($permission == 'branch' && user()->branch_id == $row->branch_id)
            || ($permission == 'added' && user()->id == $row->added_by);
    }

    public function html()
    {
        $dataTable = $this->setBuilder('company-assets-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["company-assets-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({ selector: "[data-toggle=\"tooltip\"]" });
                    $(".select-picker").selectpicker();
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
            Column::make('check')->exportable(false)->printable(false)->width(10),
            Column::make('name')->title(__('app.name'))->width(22),
            Column::make('catalog')->title(__('app.catalog'))->width(12),
            Column::make('sku_no')->title(__('SKU No'))->width(12),
            Column::make('department')->title(__('app.department'))->width(13),
            Column::make('branch')->title(__('app.branchName'))->width(12),
            Column::make('qty')->title(__('app.availableQty'))->orderable(true)->searchable(false)->width(12),
            Column::computed('assignment')->title('Assigned To')->exportable(false)->printable(false)->width(15),
            Column::make('status')->title(__('app.status'))->width(12),
            Column::computed('action')->exportable(false)->printable(false)->width(15)->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'CompanyAssets_' . date('YmdHis');
    }
}
