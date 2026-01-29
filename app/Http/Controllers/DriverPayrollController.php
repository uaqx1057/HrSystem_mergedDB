<?php

namespace App\Http\Controllers;

use App\DataTables\BusinessesDriverDataTable;
use App\DataTables\DriversPayrollDataTable;
use App\Helper\Reply;
use App\Http\Requests\Admin\Driver\StoreRequest;
use App\Models\{Driver, DriverType, BusinessField};
use App\Traits\ImportExcel;
use Illuminate\Http\Request;
use App\Helper\Files;
use App\Http\Requests\Admin\Driver\UpdateRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class DriverPayrollController extends AccountBaseController
{
    use ImportExcel;

    public function __construct(private DriversPayrollDataTable $driversPayrollDataTable)
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.payroll';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('drivers', $this->user->modules));
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $viewPermission = user()->permission('view_payroll');
        abort_403(!in_array($viewPermission, ['all']));

        $now = now();
        $this->year = $now->format('Y');
        $this->month = $now->format('m');


        $currentDate = Carbon::now();
        $startDate = $request->startDate ? Carbon::parse($request->startDate)->toDateString() : null;
        $endDate = $request->endDate ? Carbon::parse($request->endDate)->toDateString() : null;

        // Calculate the difference in days between the start date and end date
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $daysDifference = $start->diffInDays($end) + 1;

        $this->drivers = Driver::whereHas('coordinator_reports', function ($query) use ($request, $startDate, $endDate) {
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
        ])
        ->get([
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

        // Initialize an empty collection to hold the grouped drivers by report_date
        $groupedDrivers = collect();

        $this->total_orders = 0;
        foreach ($this->drivers as $driver) {

            // Getting Total Days
            // Iterate through each coordinator report of the driver
            $comissionSum = 0;
            foreach ($driver->coordinator_reports as $report) {
                // Comission Sum
                $orderFieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Total Orders'])->pluck('id')->first();
                $tipFieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Tip'])->pluck('id')->first();
                $otherTipFieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Other Tip'])->pluck('id')->first();
                $comissionSum += $report->field_values->where('field_id', $orderFieldId)->sum('value');
                $comissionSum += $report->field_values->where('field_id', $tipFieldId)->sum('value');
                $comissionSum += $report->field_values->where('field_id', $otherTipFieldId)->sum('value');


                $reportDate = $report->report_date->format('Y-m-d');

                // Check if the group already exists, if not create it
                if (!$groupedDrivers->has($reportDate)) {
                    $groupedDrivers->put($reportDate, collect());
                }

                // Add the driver to the corresponding group
                $groupedDrivers->get($reportDate)->push($driver);
                $driver->total_days = count($groupedDrivers);
            }
            $driver->comission = $comissionSum;
            $totalSum = 0;
            foreach ($driver->coordinator_reports as $report) {
                $fieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Total Orders'])->pluck('id')->first();
                $reportSum = $report->field_values->where('field_id', $fieldId)->sum('value');
                $totalSum += $reportSum;
            }
            $driver->total_orders = $totalSum;

            // Sum of specific fields from the driver
            $calculated_salary = $this->calculate_driver_order_price($driver->total_orders, 26, $driver->driver_type->is_freelancer);
            $driver->gross_salary =  $calculated_salary['gross_salary'] > 0 ? $calculated_salary['gross_salary'] : 0;
            $driver->base_salary =  $calculated_salary['base_salary'] > 0 ? $calculated_salary['base_salary'] : 0;
            $driver->deductions =  $calculated_salary['deductions'] > 0 ? $calculated_salary['deductions'] : 0;
            $driver->salary = ($driver->gross_salary + $driver->comission) - $driver->deductions;
        }

        // return $this->drivers;
        return $this->driversPayrollDataTable->render('drivers-payroll.index', $this->data);
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
