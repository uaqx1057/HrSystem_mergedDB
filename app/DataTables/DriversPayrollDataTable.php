<?php

namespace App\DataTables;

use App\Models\{Driver, DriverType, Business, BusinessField};
use Carbon\Carbon;
use App\Models\CoordinatorReport;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class DriversPayrollDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $request = $this->request();
        $currentDate = Carbon::now();
        $startDate = $request->startDate ? Carbon::parse($request->startDate)->toDateString() : null;
        $endDate = $request->endDate ? Carbon::parse($request->endDate)->toDateString() : null;
        return (new EloquentDataTable($query))
            ->addColumn('contract_type', function ($row) {
                return ucwords(strtolower($row->contract_type));
            })
            ->addColumn('nationality', function ($row) {
                return ucwords(strtolower($row->nationality));
            })
            ->addColumn('contract_type', function ($row) {
                return $row->driver_type->name;
            })
            ->addColumn('working_days', function ($row) {
                $groupedDrivers = collect();
                foreach ($row->coordinator_reports as $report) {
                    $reportDate = $report->report_date->format('Y-m-d');

                    // Check if the group already exists, if not create it
                    if (!$groupedDrivers->has($reportDate)) {
                        $groupedDrivers->put($reportDate, collect());
                    }

                    // Add the driver to the corresponding group
                    $groupedDrivers->get($reportDate)->push($row);
                }
                $working_days = count($groupedDrivers);
                return $working_days;
            })
            ->addColumn('total_orders', function ($row) {
                $totalSum = 0;
                foreach ($row->coordinator_reports as $report) {
                    $fieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Total Orders'])->pluck('id')->first();
                    $reportSum = $report->field_values->where('field_id', $fieldId)->sum('value');
                    $totalSum += $reportSum;
                }
                $total_orders = $totalSum;
                return $total_orders;
            })
            ->addColumn('deductions', function ($row) {
                $totalSum = 0;
                foreach ($row->coordinator_reports as $report) {
                    $fieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Total Orders'])->pluck('id')->first();
                    $reportSum = $report->field_values->where('field_id', $fieldId)->sum('value');
                    $totalSum += $reportSum;
                }
                $total_orders = $totalSum;
                $calculated_salary = $this->calculate_driver_order_price($total_orders, 26, $row->driver_type->is_freelancer);
                $deductions =  $calculated_salary['deductions'] > 0 ? $calculated_salary['deductions'] : 0;
                return $deductions;
            })
            ->addColumn('bonus', function ($row) {
                $comissionSum = 0;
                foreach ($row->coordinator_reports as $report) {
                    // Comission Sum
                    $bonusFieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Bonus'])->pluck('id')->first();
                    $comissionSum += $report->field_values->where('field_id', $bonusFieldId)->sum('value');
                }
                return $comissionSum;
            })
            ->addColumn('tip', function ($row) {
                $comissionSum = 0;
                foreach ($row->coordinator_reports as $report) {
                    // Comission Sum
                    $tipFieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Tip'])->pluck('id')->first();
                    $comissionSum += $report->field_values->where('field_id', $tipFieldId)->sum('value');
                }
                return $comissionSum;
            })
            ->addColumn('other_tip', function ($row) {
                $comissionSum = 0;
                foreach ($row->coordinator_reports as $report) {
                    // Comission Sum
                    $otherTipFieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Other Tip'])->pluck('id')->first();
                    $comissionSum += $report->field_values->where('field_id', $otherTipFieldId)->sum('value');
                }
                return $comissionSum;
            })
            ->addColumn('commission_amount', function ($row) {
                $comissionSum = 0;
                foreach ($row->coordinator_reports as $report) {
                    // Comission Sum
                    $bonusFieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Bonus'])->pluck('id')->first();
                    $tipFieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Tip'])->pluck('id')->first();
                    $otherTipFieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Other Tip'])->pluck('id')->first();
                    $comissionSum += $report->field_values->where('field_id', $bonusFieldId)->sum('value');
                    $comissionSum += $report->field_values->where('field_id', $tipFieldId)->sum('value');
                    $comissionSum += $report->field_values->where('field_id', $otherTipFieldId)->sum('value');
                }
                return $comissionSum;
            })
            ->addColumn('base_salary', function ($row) {
                $totalSum = 0;
                foreach ($row->coordinator_reports as $report) {
                    $fieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Total Orders'])->pluck('id')->first();
                    $reportSum = $report->field_values->where('field_id', $fieldId)->sum('value');
                    $totalSum += $reportSum;
                }
                $total_orders = $totalSum;
                $calculated_salary = $this->calculate_driver_order_price($total_orders, 26, $row->driver_type->is_freelancer);
                $base_salary =  $calculated_salary['base_salary'] > 0 ? $calculated_salary['base_salary'] : 0;
                return $base_salary;
            })
            ->addColumn('salary', function ($row) {
                $totalSum = 0;
                $comissionSum = 0;
                foreach ($row->coordinator_reports as $report) {
                    $fieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Total Orders'])->pluck('id')->first();
                    $reportSum = $report->field_values->where('field_id', $fieldId)->sum('value');
                    $totalSum += $reportSum;

                    // Comission Sum
                    $bonusFieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Bonus'])->pluck('id')->first();
                    $tipFieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Tip'])->pluck('id')->first();
                    $otherTipFieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Other Tip'])->pluck('id')->first();
                    $comissionSum += $report->field_values->where('field_id', $bonusFieldId)->sum('value');
                    $comissionSum += $report->field_values->where('field_id', $tipFieldId)->sum('value');
                    $comissionSum += $report->field_values->where('field_id', $otherTipFieldId)->sum('value');

                }
                $total_orders = $totalSum;
                $calculated_salary = $this->calculate_driver_order_price($total_orders, 26, $row->driver_type->is_freelancer);
                $gross_salary =  $calculated_salary['gross_salary'] > 0 ? $calculated_salary['gross_salary'] : 0;
                $base_salary =  $calculated_salary['base_salary'] > 0 ? $calculated_salary['base_salary'] : 0;
                $deductions =  $calculated_salary['deductions'] > 0 ? $calculated_salary['deductions'] : 0;
                $salary = ($gross_salary + $comissionSum) - $deductions;
                return number_format($salary, 2);

            });
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Driver $model): QueryBuilder
    {
        $request = $this->request();
        $currentDate = Carbon::now();
        $startDate = $request->startDate ? Carbon::parse($request->startDate)->toDateString() : null;
        $endDate = $request->endDate ? Carbon::parse($request->endDate)->toDateString() : null;

        // Calculate the difference in days between the start date and end date
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $daysDifference = $start->diffInDays($end) + 1;

        $query = $model->whereHas('coordinator_reports', function ($query) use ($request, $startDate, $endDate) {
            $query->when($startDate, function ($q) use ($startDate) {
                    $q->where('report_date', '>=', $startDate);
                })
                ->when($endDate, function ($q) use ($endDate) {
                    $q->where('report_date', '<=', $endDate);
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
        ]);

        return $query;
    }

      /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        $columns = [];

        $columns = array_merge($columns, [
            Column::make('iqaama_number'),
            Column::make('name'),
            Column::make('contract_type'),
            Column::make('working_days'),
            Column::make('total_orders'),
            Column::make('deductions'),
            Column::make('bonus'),
            Column::make('tip'),
            Column::make('other_tip'),
            Column::make('commission_amount'),
            Column::make('base_salary'),
            Column::make('salary'),
        ]);

        return $columns;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('drivers-payroll-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
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
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Drivers_' . date('YmdHis');
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

        return ['gross_salary' => $final_salary, 'base_salary' => $base_salary, 'deductions' => $deductions];

    }
}
