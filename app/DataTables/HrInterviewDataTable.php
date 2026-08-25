<?php

namespace App\DataTables;

use App\Models\HrInterviewSchedule;
use Yajra\DataTables\Html\Column;

class HrInterviewDataTable extends BaseDataTable
{
    public function __construct(private int $candidateId)
    {
        parent::__construct();
    }

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('when', fn ($row) => $row->event?->start_date_time ?? '-')
            ->editColumn('status', fn ($row) => ucwords(str_replace('_', ' ', $row->status)))
            ->editColumn('outcome', function ($row) {
                if (! $row->outcome) {
                    return '-';
                }

                $map = [
                    'pass' => 'text-light-green',
                    'fail' => 'text-red',
                    'pending' => 'text-yellow',
                ];
                $color = $map[$row->outcome] ?? 'text-grey';
                return '<i class="fa fa-circle mr-1 ' . $color . ' f-10"></i>' . ucwords(str_replace('_', ' ', $row->outcome));
            })
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">';

                $action .= '<div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a><div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                if ($row->status !== 'completed') {
                    $action .= '<a href="javascript:void(0);" class="dropdown-item" data-toggle="modal" data-target="#outcomeModal-' . $row->id . '">
                            <i class="fa fa-check-circle mr-2"></i> Record Outcome
                        </a>';
                }

                $action .= '</div></div></div>';

                if ($row->status !== 'completed') {
                    $action .= '<div class="modal fade" id="outcomeModal-' . $row->id . '" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <form method="POST" action="' . route('hr-interview-schedules.outcome', $row) . '">' . csrf_field() . '
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Record Outcome - ' . e($row->round) . '</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Outcome</label>
                                                <select name="outcome" class="form-control height-35">
                                                    <option value="pass">Pass</option>
                                                    <option value="fail">Fail</option>
                                                    <option value="pending">Pending</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Note</label>
                                                <textarea name="feedback" rows="4" class="form-control" placeholder="Add a note (optional)"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>';
                }

                return $action;
            })
            ->rawColumns(['action', 'outcome']);
    }

    public function query(HrInterviewSchedule $model)
    {
        return $model->with('event')
            ->where('candidate_id', $this->candidateId)
            ->orderBy('id');
    }

    public function html()
    {
        return $this->setBuilder('hr-interviews-table', 1);
    }

    protected function getColumns()
    {
        return [
            'Round' => ['data' => 'round', 'name' => 'round', 'title' => 'Round'],
            'When' => ['data' => 'when', 'name' => 'event.start_date_time', 'title' => 'When', 'orderable' => false],
            'Status' => ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
            'Outcome' => ['data' => 'outcome', 'name' => 'outcome', 'title' => 'Outcome'],
            'Note' => ['data' => 'feedback', 'name' => 'feedback', 'title' => 'Note', 'orderable' => false],
            Column::computed('action', '')->exportable(false)->printable(false)->orderable(false)->searchable(false),
        ];
    }
}
