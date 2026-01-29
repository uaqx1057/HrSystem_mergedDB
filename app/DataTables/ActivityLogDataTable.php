<?php

namespace App\DataTables;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ActivityLogDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->setRowId('id')
            ->addColumn('old_properties', function ($activity) {
                return isset($activity->changes['old']) ? json_encode($activity->changes['old']) : '-';
            })
            ->addColumn('new_properties', function ($activity) {
                return isset($activity->changes['attributes']) ? json_encode($activity->changes['attributes']) : '-';
            })
            ->addColumn('causer', fn ($activity) => $activity->causer?->email ?? '-')
            ->addColumn('created_at', fn ($activity) => $activity->created_at->format('d-m-Y H:i:s'))
            ->orderColumn('created_at', fn ($q, $order) => $q->orderBy('created_at', $order))
            ->orderColumn('causer', fn ($q, $order) => $q->orderBy('created_at', $order));
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Activity $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with('causer:id,email')
            ->where('company_id', company()->id);

        if ($this->request()->searchText != '') {
            $search = $this->request()->searchText;
            $query->whereHas('causer', fn ($q) => $q->where('email', 'like', "%$search%"))
                ->orWhere('subject_type', 'like', "%$search%");
        }

        if ($this->request()->event != 'all') {
            $query->where('event', $this->request()->event);
        }

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('activity-log-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(6)
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('export'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload')
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id'),
            Column::make('subject_type'),
            Column::make('causer')
                ->orderable(false),
            Column::make('event'),
            Column::make('old_properties'),
            Column::make('new_properties'),
            Column::make('ip_address')
                ->title('IP Address'),
            Column::make('created_at'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Activity_' . date('YmdHis');
    }
}
