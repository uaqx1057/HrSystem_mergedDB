<?php

namespace App\DataTables;

use App\Models\{Driver, BusinessField, Business};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class DriversRevenueReportDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $request = $this->request();
        $currentDate = Carbon::now();
        $startDate = $request->startDate ? Carbon::parse($request->startDate)->toDateString() : $currentDate->startOfMonth()->toDateString();
        $endDate = $request->endDate ? Carbon::parse($request->endDate)->toDateString() : Carbon::today()->toDateString();

        // Calculate the difference in days between the start date and end date
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $daysDifference = $start->diffInDays($end) + 1;

        return (new EloquentDataTable($query))
            ->with('daysDifference', $daysDifference)
            ->addColumn('branch', function ($row) {
                return $row->branch->name ?? '';
            })
            ->addColumn('contract', function ($row) {
                return $row->driver_type->name ?? '';
            })
            ->addColumn('total_orders', function ($row) {
                $totalSum = $row->coordinator_reports->sum(function ($report) {
                    $fieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Total Orders'])->pluck('id')->first();
                    return $report->field_values->where('field_id', $fieldId)->sum('value');
                });
                return number_format($totalSum);
            })
            ->addColumn('total_revenue', function ($row) {
                $business_reports = $row->coordinator_reports->groupBy('business_id')->map(function ($reports, $business_id) {
                    $fieldId = BusinessField::where(['business_id' => $business_id, 'name' => 'Total Orders'])->pluck('id')->first();
                    $total_orders = $reports->sum(function ($report) use ($fieldId) {
                        return $report->field_values->where('field_id', $fieldId)->sum('value');
                    });

                    return [
                        'business_id' => $business_id,
                        'total_orders' => $total_orders,
                    ];
                });

                $total_revenue = $business_reports->sum(function ($business_report) {
                    $businessCalculations = Business::with('driver_calculations')->find($business_report['business_id']);
                    $total_orders = $business_report['total_orders'];
                    $total_revenue = 0;

                    if ($businessCalculations && $businessCalculations->driver_calculations) {
                        foreach ($businessCalculations->driver_calculations as $calculation) {
                            if ($calculation->type == 'RANGE' && $total_orders >= $calculation->from_value && $total_orders <= $calculation->to_value) {
                                $total_revenue += $total_orders * $calculation->amount;
                                break;
                            } else if ($calculation->type == 'FIXED') {
                                $total_revenue += $total_orders * $calculation->amount;
                            }
                        }
                    }

                    return $total_revenue;
                });

                return number_format($total_revenue);
            })
            ->addColumn('total_cost', function ($row) use ($daysDifference) {
                $totalSum = $row->coordinator_reports->sum(function ($report) {
                    $fieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Total Orders'])->pluck('id')->first();
                    return $report->field_values->where('field_id', $fieldId)->sum('value');
                });
                $calculated_salary = $this->calculate_driver_order_price($totalSum, 26, $row->driver_type->is_freelancer);
                $total_salary = $calculated_salary > 0 ? $calculated_salary : 0;
                $total_gprs = ($row->gprs / 30) * $daysDifference;
                $total_fuel = ($row->fuel / 30) * $daysDifference;
                $total_government_cost = ($row->government_cost / 30) * $daysDifference;
                $total_accommodation = ($row->accommodation / 30) * $daysDifference;
                $total_vehicle_monthly_cost = ($row->vehicle_monthly_cost / 30) * $daysDifference;
                $total_mobile_data = ($row->mobile_data / 30) * $daysDifference;
                $total_cost = $total_salary + $total_gprs + $total_fuel + $total_government_cost + $total_accommodation + $total_vehicle_monthly_cost + $total_mobile_data;
                return number_format($total_cost, 2);
            })
            ->addColumn('profit_loss', function ($row) use ($daysDifference) {
                $totalSum = $row->coordinator_reports->sum(function ($report) {
                    $fieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Total Orders'])->pluck('id')->first();
                    return $report->field_values->where('field_id', $fieldId)->sum('value');
                });

                $business_reports = $row->coordinator_reports->groupBy('business_id')->map(function ($reports, $business_id) {
                    $fieldId = BusinessField::where(['business_id' => $business_id, 'name' => 'Total Orders'])->pluck('id')->first();
                    $total_orders = $reports->sum(function ($report) use ($fieldId) {
                        return $report->field_values->where('field_id', $fieldId)->sum('value');
                    });

                    return [
                        'business_id' => $business_id,
                        'total_orders' => $total_orders,
                    ];
                });

                $total_revenue = $business_reports->sum(function ($business_report) {
                    $businessCalculations = Business::with('driver_calculations')->find($business_report['business_id']);
                    $total_orders = $business_report['total_orders'];
                    $total_revenue = 0;

                    if ($businessCalculations && $businessCalculations->driver_calculations) {
                        foreach ($businessCalculations->driver_calculations as $calculation) {
                            if ($calculation->type == 'RANGE' && $total_orders >= $calculation->from_value && $total_orders <= $calculation->to_value) {
                                $total_revenue += $total_orders * $calculation->amount;
                                break;
                            } else if ($calculation->type == 'FIXED') {
                                $total_revenue += $total_orders * $calculation->amount;
                            }
                        }
                    }

                    return $total_revenue;
                });

                $calculated_salary = $this->calculate_driver_order_price($totalSum, 26, $row->driver_type->is_freelancer);
                $total_salary = $calculated_salary > 0 ? $calculated_salary : 0;
                $total_gprs = ($row->gprs / 30) * $daysDifference;
                $total_fuel = ($row->fuel / 30) * $daysDifference;
                $total_government_cost = ($row->government_cost / 30) * $daysDifference;
                $total_accommodation = ($row->accommodation / 30) * $daysDifference;
                $total_vehicle_monthly_cost = ($row->vehicle_monthly_cost / 30) * $daysDifference;
                $total_mobile_data = ($row->mobile_data / 30) * $daysDifference;
                $total_cost = $total_salary + $total_gprs + $total_fuel + $total_government_cost + $total_accommodation + $total_vehicle_monthly_cost + $total_mobile_data;
                $gross_profit = $total_revenue - $total_cost;
                return number_format($gross_profit, 2);
            });
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Driver $model): QueryBuilder
    {
        $request = $this->request();
        $currentDate = Carbon::now();
        $startDate = $request->startDate ? Carbon::parse($request->startDate)->toDateString() : $currentDate->startOfMonth()->toDateString();
        $endDate = $request->endDate ? Carbon::parse($request->endDate)->toDateString() : Carbon::today()->toDateString();
        return $model->whereHas('coordinator_reports', function ($query) use ($request, $startDate, $endDate) {
            $query->when($startDate, function ($q) use ($startDate) {
                    $q->where('report_date', '>=', $startDate);
                })
                ->when($endDate, function ($q) use ($endDate) {
                    $q->where('report_date', '<=', $endDate);
                })
                ->when($request->business_id, function ($q) use ($request) {
                    $q->where('business_id', $request->business_id);
                });
        })
        ->with([
            'branch',
            'driver_type',
            'coordinator_reports' => function ($query) use ($request, $startDate, $endDate) {
                $query->with('field_values')
                    ->when($startDate, function ($q) use ($startDate) {
                        return $q->where('report_date', '>=', $startDate);
                    })
                    ->when($endDate, function ($q) use ($endDate) {
                        return $q->where('report_date', '<=', $endDate);
                    })
                    ->when($request->business_id, function ($q) use ($request) {
                        return $q->where('business_id', $request->business_id);
                    });
            }
        ])
        ->when($request->driver_type_id, function ($q) use ($request) {
            $q->where('driver_type_id', $request->driver_type_id);
        })->when($request->branch_id, function ($q) use ($request) {
            $q->where('branch_id', $request->branch_id);
        })
        ->select([
            'id',
            'name',
            'iqaama_number',
            'branch_id',
            'driver_type_id',
            'fuel',
            'gprs',
            'government_cost',
            'accommodation',
            'vehicle_monthly_cost',
            'mobile_data'
        ]);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('drivers-revenue-report-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
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
            Column::make('name'),
            Column::make('branch'),
            Column::make('contract'),
            Column::make('total_orders'),
            Column::make('total_revenue'),
            Column::make('total_cost'),
            Column::make('profit_loss')->title('Profit/Loss'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'DriversRevenueReport_' . date('YmdHis');
    }

    private function calculate_base_salary($working_days, $freelancer, $BASE_SALARY_PER_MONTH, $WORKING_DAYS_PER_MONTH) {
        if ($freelancer) {
            return ($BASE_SALARY_PER_MONTH / $WORKING_DAYS_PER_MONTH) * min($working_days, $WORKING_DAYS_PER_MONTH);
        }
        return $BASE_SALARY_PER_MONTH;
    }

    private function calculate_base_order_limit($working_days, $freelancer, $BASE_ORDER_LIMIT_PER_MONTH, $WORKING_DAYS_PER_MONTH) {
        if ($freelancer) {
            return ($BASE_ORDER_LIMIT_PER_MONTH / $WORKING_DAYS_PER_MONTH) * min($working_days, $WORKING_DAYS_PER_MONTH);
        }
        return $BASE_ORDER_LIMIT_PER_MONTH;
    }

    public function calculate_driver_order_price($total_order, $working_days, $freelancer) {
        $WORKING_DAYS_PER_MONTH = 26;
        $BASE_SALARY_PER_MONTH = 400;
        $BASE_ORDER_LIMIT_PER_MONTH = 250;
        $COMMISSION_RATE = 9;

        $base_salary = $this->calculate_base_salary($working_days, $freelancer, $BASE_SALARY_PER_MONTH, $WORKING_DAYS_PER_MONTH);
        $base_order_limit = $this->calculate_base_order_limit($working_days, $freelancer, $BASE_ORDER_LIMIT_PER_MONTH, $WORKING_DAYS_PER_MONTH);
        $per_order_base_salary = $base_salary / $base_order_limit;

        if ($total_order <= $base_order_limit) {
            // $base_salary = $per_order_base_salary * $total_order;
            $deductions = $per_order_base_salary * ($base_order_limit - $total_order);
            $commission_amount = 0;
        } else {
            $deductions = 0;
            $commission_amount = ($total_order - $base_order_limit) * $COMMISSION_RATE;
        }

        $final_salary = ($base_salary + $commission_amount) - $deductions;

        return $final_salary;
    }
}
