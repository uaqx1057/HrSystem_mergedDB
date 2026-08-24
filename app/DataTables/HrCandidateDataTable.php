<?php

namespace App\DataTables;

use App\Models\HrCandidate;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Column;

class HrCandidateDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('name', function ($row) {
                return '<a href="' . route('hr-candidates.show', $row) . '" class="text-darkest-grey f-w-500">' . e($row->name) . '</a>';
            })
            ->addColumn('contact', function ($row) {
                return e($row->email) . ($row->mobile ? '<br>' . e($row->mobile) : '');
            })
            ->editColumn('source', function ($row) {
                return ucwords(str_replace('_', ' ', $row->source ?? '-'));
            })
            ->addColumn('opening', function ($row) {
                return $row->jobOpening?->title ?? 'General';
            })
            ->editColumn('status', function ($row) {
                $map = [
                    'new' => 'text-grey',
                    'applied' => 'text-blue',
                    'screening' => 'text-yellow',
                    'interview_scheduled' => 'text-yellow',
                    'interviewed' => 'text-yellow',
                    'approved' => 'text-light-green',
                    'onboarding' => 'text-light-green',
                    'converted' => 'text-light-green',
                    'handoff' => 'text-light-green',
                    'rejected' => 'text-red',
                ];
                $color = $map[$row->status] ?? 'text-grey';
                return '<i class="fa fa-circle mr-1 ' . $color . ' f-10"></i>' . ucwords(str_replace('_', ' ', $row->status));
            })
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">';

                $action .= '<div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a><div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a href="' . route('hr-candidates.show', [
                        'candidate' => $row->id,
                        'tab' => 'detail'
                    ]) . '" class="dropdown-item">
                        <i class="fa fa-eye mr-2"></i> ' . __('app.view') . '
                    </a>';

                $action .= '</div></div></div>';

                return $action;
            })
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            ->rawColumns(['name', 'contact', 'status', 'action']);
    }

    public function query(HrCandidate $model)
    {
        $request = $this->request();

        $model = $model->where('company_id', user()->company_id)
            ->with('jobOpening')
            ->orderBy('id', 'desc');

        if ($request->filled('jobOpeningId') && $request->jobOpeningId !== 'all') {
            if ($request->jobOpeningId === 'general') {
                $model->whereNull('job_opening_id');
            } else {
                $model->where('job_opening_id', $request->jobOpeningId);
            }
        }

        if ($request->filled('statusFilter')) {
            $model->where('status', $request->statusFilter);
        }

        if ($request->filled('searchText')) {
            $model->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->searchText . '%')
                    ->orWhere('email', 'like', '%' . $request->searchText . '%')
                    ->orWhere('mobile', 'like', '%' . $request->searchText . '%');
            });
        }

        return $model;
    }

    public function html()
    {
        $dataTable = $this->setBuilder('hr-candidates-table', 2)
            ->parameters([
                'order' => [],
                'initComplete' => 'function () {
                   window.LaravelDataTables["hr-candidates-table"].buttons().container()
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
            'Name' => ['data' => 'name', 'name' => 'name', 'title' => 'Name'],
            'Contact' => ['data' => 'contact', 'name' => 'email', 'title' => 'Contact', 'orderable' => false],
            'Source' => ['data' => 'source', 'name' => 'source', 'title' => 'Source'],
            'Opening' => ['data' => 'opening', 'name' => 'jobOpening.title', 'title' => 'Opening', 'orderable' => false],
            'Status' => ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20'),
        ];
    }
}
