<?php

namespace App\DataTables;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class DriversDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('name', 'drivers.datatable.name-with-image')
            ->addColumn('action', 'drivers.datatable.action')
            ->addColumn('status', 'drivers.datatable.status')
            ->addColumn('onboarding_status', function ($row) {
                $onboardingStatus = $this->resolveOnboardingStatus($row);

                return view('drivers.datatable.onboarding-status', [
                    'onboardingStatus' => $onboardingStatus,
                ])->render();
            })
            ->setRowId('id')
            ->rawColumns(['name', 'status', 'onboarding_status', 'action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Driver $model): QueryBuilder
    {
        $request = $this->request();

        $query = $model->withoutGlobalScopes()
            ->newQuery()
            ->select([
                'id',
                'driver_id',
                'name',
                'iqaama_number',
                'work_mobile_no',
                'status',
                'onboarding_stage',
                'offboard_request',
                'offboarding_stage',
                'image',
                'email',
            ]);

        $query->when($request->searchText && strlen(trim((string) $request->searchText)) >= 2, function ($query, $searchText) {
            $query->where(function ($subQuery) use ($searchText) {
                $subQuery->where('name', 'like', '%' . $searchText . '%')
                    ->orWhere('driver_id', 'like', '%' . $searchText . '%')
                    ->orWhere('iqaama_number', 'like', '%' . $searchText . '%');
            });
        });

        $query->when($request->status && $request->status !== 'all', function ($query) use ($request) {
            $status = strtolower((string) $request->status);

            $statusMap = [
                'active' => 'Active',
                'inactive' => 'Inactive',
                'busy' => 'Busy',
                'blocked' => 'Blocked',
            ];

            if (isset($statusMap[$status])) {
                $query->where('status', $statusMap[$status]);
            }
        });

        $query->when($request->onboarding_status && $request->onboarding_status !== 'all', function ($query) use ($request) {
            if ($request->onboarding_status === 'offboarding') {
                $query->where(function ($subQuery) {
                    $subQuery->where('offboard_request', 1)
                        ->orWhereNotNull('offboarding_stage');
                });
            }

            if ($request->onboarding_status === 'onboarding_completed') {
                $query->where('onboarding_stage', 'Completed')
                    ->where(function ($subQuery) {
                        $subQuery->whereNull('offboarding_stage')
                            ->where(function ($innerQuery) {
                                $innerQuery->whereNull('offboard_request')
                                    ->orWhere('offboard_request', 0);
                            });
                    });
            }

            if ($request->onboarding_status === 'pending_onboarding') {
                $query->where(function ($subQuery) {
                    $subQuery->where('onboarding_stage', '!=', 'Completed')
                        ->orWhereNull('onboarding_stage');
                })->where(function ($subQuery) {
                    $subQuery->whereNull('offboarding_stage')
                        ->where(function ($innerQuery) {
                            $innerQuery->whereNull('offboard_request')
                                ->orWhere('offboard_request', 0);
                        });
                });
            }
        });

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('drivers-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->pageLength(50)
                    //->dom('Bfrtip')
                    ->orderBy(1)
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('create'),
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
            Column::make('driver_id'),
            Column::make('name'),
            Column::make('iqaama_number'),
            Column::make('work_mobile_no'),
            Column::make('status'),
            Column::computed('onboarding_status')->title('Onboarding Status'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(60)
                  ->addClass('text-center'),
        ];
    }

    private function resolveOnboardingStatus(Driver $driver): string
    {
        $hasOffboarding = ((int) ($driver->offboard_request ?? 0) === 1) || !empty($driver->offboarding_stage);

        if ($hasOffboarding) {
            return 'offboarding';
        }

        if (strtolower((string) $driver->onboarding_stage) === 'completed') {
            return 'onboarding_completed';
        }

        return 'pending_onboarding';
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Drivers_' . date('YmdHis');
    }
}
