<?php

namespace App\DataTables;

use App\Models\HrJobOpening;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Column;

class JobOpeningDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('title', function ($row) {
                return '<a href="' . route('job-openings.edit', $row) . '" class="text-darkest-grey f-w-500">' . e($row->title) . '</a>';
            })
            ->addColumn('branch_name', function ($row) {
                return $row->branch?->name ?? '-';
            })
            ->editColumn('positions_count', function ($row) {
                return $row->positions_count ?? 1;
            })
            ->editColumn('status', function ($row) {
                $map = [
                    'open' => 'text-light-green',
                    'on_hold' => 'text-yellow',
                    'closed' => 'text-red',
                ];
                $color = $map[$row->status] ?? 'text-grey';
                return '<i class="fa fa-circle mr-1 ' . $color . ' f-10"></i>' . ucwords(str_replace('_', ' ', $row->status));
            })
            ->addColumn('candidates_count', function ($row) {
                return '<a href="' . route('hr-candidates.index', ['job_opening_id' => $row->id]) . '" class="text-dark">' . $row->candidates_count . '</a>';
            })
            ->addColumn('public_link', function ($row) {
                $url = route('careers.show', $row->public_slug);
                return '<a href="' . $url . '" target="_blank"><i class="fa fa-external-link-alt mr-1"></i>Preview</a>';
            })
            ->addColumn('closes_at', function ($row) {
                return $row->closes_at ? $row->closes_at->format(company()->date_format) : '-';
            })
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">';
                $action .= '<a href="' . route('hr-candidates.index', ['job_opening_id' => $row->id]) . '" class="taskView text-darkest-grey f-w-500">View Candidates</a>';

                $action .= '<div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a><div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a class="dropdown-item" href="' . route('job-openings.edit', $row) . '">
                                <i class="fa fa-edit mr-2"></i> ' . trans('app.edit') . '
                            </a>';

                $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-job-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i> ' . trans('app.delete') . '
                            </a>';

                $action .= '</div></div></div>';

                return $action;
            })
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            ->rawColumns(['title', 'status', 'candidates_count', 'public_link', 'action']);
    }

    public function query(HrJobOpening $model)
    {
        $request = $this->request();

        $model = $model->where('company_id', user()->company_id)
            ->with('branch')
            ->withCount('candidates')
            ->orderBy('id', 'desc');

        if ($request->filled('statusFilter')) {
            $model->where('status', $request->statusFilter);
        }

        if ($request->filled('searchText')) {
            $model->where('title', 'like', '%' . $request->searchText . '%');
        }

        return $model;
    }

    public function html()
    {
        $dataTable = $this->setBuilder('job-openings-table', 2)
            ->parameters([
                'order' => [], // keep server-side "recent first" ordering, don't let DT override it
                'initComplete' => 'function () {
                   window.LaravelDataTables["job-openings-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    })
                }',
            ]);

        if (canDataTableExport()) {
            $dataTable->buttons(\Yajra\DataTables\Html\Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
        }

        return $dataTable;
    }

    protected function getColumns()
    {
        return [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            'Title' => ['data' => 'title', 'name' => 'title', 'title' => 'Title'],
            'Branch' => ['data' => 'branch_name', 'name' => 'branch.name', 'title' => 'Branch', 'orderable' => false],
            'Positions' => ['data' => 'positions_count', 'name' => 'positions_count', 'title' => 'Positions'],
            'Status' => ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
            'Closes At' => ['data' => 'closes_at', 'name' => 'closes_at', 'title' => 'Closes At'],
            'Candidates' => ['data' => 'candidates_count', 'name' => 'candidates_count', 'title' => 'Candidates', 'orderable' => false],
            'Public link' => ['data' => 'public_link', 'name' => 'public_link', 'title' => 'Public link', 'orderable' => false, 'searchable' => false],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20'),
        ];
    }
}