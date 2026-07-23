<?php

namespace App\DataTables;

use App\Models\CompanyAsset;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\EloquentDataTable;

class CompanyAssetDataTable extends BaseDataTable
{
    private $editPermission;
    private $deletePermission;
    private $assignRole;
    private $viewPermission;
    private $historyPermission;

    public function __construct()
    {
        parent::__construct();
        $this->viewPermission = user()->permission('view_company_assets');
        $this->editPermission = user()->permission('edit_company_assets');
        $this->deletePermission = user()->permission('delete_company_assets');
        $this->historyPermission = user()->permission('view_assign_company_assets_to_employee');
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
            ->addIndexColumn()
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="select-table-row" id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->editColumn('name', function ($row) {
                // Fix: passing id as named parameter for clarity
                $name = '<h5 class="mb-0 f-13 text-darkest-grey"><a href="' . route('company-assets.show', ['company_asset' => $row->id]) . '" class="openRightModal">' . $row->name . '</a></h5>';
                return $name;
            })
            ->editColumn('department', function ($row) {
                return $row->department_id ? $row->department->name : '--';
            })
            ->editColumn('branch', function ($row) {
                return $row->branch_id ? $row->branch->name : '--';
            })
            ->addColumn('action', function ($row) {

                $action = '<div class="task_view">';
                    $action .= '<a href="' . route('company-assets.show', ['company_asset' => $row->id]) . '" class="taskView text-darkest-grey f-w-500">' . __('app.view') . '</a>';
                    $action .= '<div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $assignment = $row->assignments->first();

                if (!$assignment) {
                    // if (user()->permission('assign_company_asset_to_employee') == 'all') {
                    //     $action .= '<a class="dropdown-item openRightModal" href="' . route('company-assets.assign', [$row->id]) . '">
                    //             <i class="fa fa-user-check mr-2"></i>
                    //             ' . trans('app.assign') . '
                    //         </a>';
                    // }
                } elseif ($assignment->status == 'Pending') {
                    // if (user()->permission('edit_assign_company_assets_to_employee') == 'all') {
                    //     $action .= '<a class="dropdown-item openRightModal" href="' . route('company-assets.edit-assign', [$row->id]) . '">
                    //             <i class="fa fa-edit mr-2"></i>
                    //             Edit Assign
                    //         </a>';
                    // }
                } elseif ($assignment->status == 'Approve') {
                    // if (user()->permission('upload_signature_assign_company_assets_to_employee') == 'all') {
                    //     $action .= '<a class="dropdown-item" href="' . route('company-assets.generate-pdf', [$row->id]) . '">
                    //             <i class="fa fa-file-pdf mr-2"></i>
                    //             Generate Pdf
                    //         </a>';
                    //     $action .= '<a class="dropdown-item " href="' . route('company-assets.upload-signature', [$row->id]) . '">
                    //             <i class="fa fa-upload mr-2"></i>
                    //             Update Signature
                    //         </a>';
                    // }
                } elseif ($assignment->status == 'Assigned') {
                    // if (user()->permission('view_assign_company_assets_to_employee') == 'all') {
                    // $action .= '<a class="dropdown-item openRightModal" href="' . route('company-assets.view-assign', [$row->id]) . '">
                    //             <i class="fa fa-eye mr-2"></i>
                    //             View Assign
                    //         </a>';
                    // }
                }

                // Return action: show when there is at least one active assignment
                // if ($row->assignments->count() > 0) {
                //     if (user()->permission('assign_company_asset_to_employee') == 'all') {
                //         $action .= '<a class="dropdown-item openRightModal" href="' . route('company-assets.return', [$row->id]) . '">
                //                 <i class="fa fa-undo mr-2"></i>
                //                 ' . trans('app.return') . '
                //             </a>';
                //     }
                // }

                if (
                    $this->editPermission == 'all'
                    || ($this->editPermission == 'branch' && user()->branch_id == 6)
                    || ($this->editPermission == 'branch' && user()->branch_id == $row->branch_id)
                    || ($this->editPermission == 'added' && user()->id == $row->added_by)
                ) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('company-assets.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }
                if(in_array(user()->permission('view_assign_company_assets_to_employee'), ['all', 'added', 'owned', 'both','branch'])){
                $action .= '<a class="dropdown-item openRightModal" href="' . route('company-assets.view-assign', [$row->id]) . '">
                                <i class="fa fa-history mr-2"></i>
                                ' . trans('app.assignmentHistory') . '
                            </a>';
                }
                if (
                    $this->deletePermission == 'all'
                    || ($this->deletePermission == 'branch' && user()->branch_id == 6)
                    || ($this->deletePermission == 'branch' && user()->branch_id == $row->branch_id)
                    || ($this->deletePermission == 'added' && user()->id == $row->added_by)
                ) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-asset-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->smart(false)
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            ->rawColumns(['check', 'action', 'name','department','branch']);
    }

    /**
     * @param CompanyAsset $model
     * @return \Illuminate\Database\Query\Builder
     */
    public function query(CompanyAsset $model)
    {
        $request = $this->request();

        // FIX: select only company_assets.* to avoid ID collisions with assignments table
        $model = $model->newQuery()
            ->with(['assignments', 'department', 'branch'])
            ->leftJoin('asset_assignments', 'asset_assignments.company_asset_id', '=', 'company_assets.id')
            ->select('company_assets.*')
            ->groupBy('company_assets.id')->orderBy('company_assets.id', 'desc');

        if ($request->searchText != '') {
            // FIX: Wrap OR conditions in a closure to keep logic correct with the non-admin filter
            $model->where(function ($query) use ($request) {
                $query->where('company_assets.name', 'like', '%' . $request->searchText . '%')
                    ->orWhere('company_assets.catalog', 'like', '%' . $request->searchText . '%')
                    ->orWhere('company_assets.sku_no', 'like', '%' . $request->searchText . '%')
                    ->orWhere('company_assets.type', 'like', '%' . $request->searchText . '%')
                    ->orWhere('company_assets.brand', 'like', '%' . $request->searchText . '%');
            });
        }

        // Logic: Only show assigned assets for non-admins
        // if (count($this->assignRole) < 2) {
        //     $model->where('asset_assignments.employee_id', user()->id)->where('asset_assignments.status', 'Assigned');
        // }
        if ($this->viewPermission == 'added') {
            $model->where(function ($query) use ($request) {
                $query->where('company_assets.added_by', user()->id);
            });
        }

        if ($this->viewPermission == 'owned') {
            $model->where(function ($query) use ($request) {
                $query->where('asset_assignments.employee_id', user()->id)->where('asset_assignments.status', 'Assigned');
            });
        }

        if ($this->viewPermission == 'both') {
            $model->where(function ($query) use ($request) {
                $query->where('asset_assignments.employee_id', user()->id)->where('asset_assignments.status', 'Assigned');
            })->orWhere('company_assets.added_by', user()->id);
        }

        if ($this->viewPermission == 'branch' && user()->branch_id !== 6) {
            $model->where(function ($query) use ($request) {
                $query->where('company_assets.branch_id', user()->branch_id);
            });
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
        $dataTable = $this->setBuilder('company-assets-table')
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["company-assets-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: "[data-toggle=\"tooltip\"]"
                    });
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
            Column::make('check')
                ->exportable(false)
                ->printable(false)
                ->width(10),
            Column::make('catalog')->title(__('app.catalog'))->width(20),
            Column::make('sku_no')->title(__('SKU No'))->width(20),
            Column::make('name')->title(__('app.name'))->width(20),
            Column::make('type')->title(__('app.type'))->width(15),
            Column::make('brand')->title(__('app.brand'))->width(15),
            Column::make('department')->title(__('app.department'))->width(15),
            Column::make('branch')->title(__('app.branchName'))->width(15),
            Column::make('qty')->title(__('app.qty'))->width(10),
            Column::make('available_qty')->title(__('app.availableQty'))->width(10),
            Column::make('status')->title(__('app.status'))->width(15),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(20)
                ->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'CompanyAssets_' . date('YmdHis');
    }
}
