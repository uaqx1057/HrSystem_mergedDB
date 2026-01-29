<?php

namespace App\Http\Controllers;

use App\DataTables\{BusinessesDriverDataTable, DriversRevenueReportDataTable};
use App\Helper\Reply;
use App\Http\Requests\Admin\Driver\StoreRequest;
use App\Models\{Driver, DriverType, Business, BusinessField, Branch};
use App\Traits\ImportExcel;
use Illuminate\Http\Request;
use App\Helper\Files;
use App\Http\Requests\Admin\Driver\UpdateRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DriverRevenueReportingController extends AccountBaseController
{
    use ImportExcel;

    public function __construct(private DriversRevenueReportDataTable $driverRevenueReportDataTable)
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.revenue_reporting';
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
        // return user_modules();
        $viewPermission = user()->permission('view_revenue_reporting');
        abort_403(!in_array($viewPermission, ['all']));
        $currentDate = Carbon::now();
        $startDate = $request->startDate ? Carbon::parse($request->startDate)->toDateString() : $currentDate->startOfMonth()->toDateString();
        $endDate = $request->endDate ? Carbon::parse($request->endDate)->toDateString() : Carbon::today()->toDateString();

        // Calculate the difference in days between the start date and end date
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $daysDifference = $start->diffInDays($end) + 1;


        $this->businesses = Business::with('coordinator_reports.field_values')->get();
        foreach ($this->businesses as $business) {
            $fieldId = BusinessField::where(['business_id' => $business->id, 'name' => 'Total Orders'])->pluck('id')->first();
            $totalSum = 0;
            foreach ($business->coordinator_reports as $report) {
                $totalSum += $report->field_values->where('field_id', $fieldId)->sum('value');
            }
            $business->total_orders = $totalSum;
        }

        $this->driver_types = DriverType::all();
        $this->branches = Branch::all();
        $this->drivers = Driver::has('coordinator_reports')->with([
            'branch',
            'driver_type',
            'coordinator_reports' => function($query) use ($request, $startDate, $endDate) {
                $query->with('field_values')
                      ->when($request->startDate, fn ($q) => $q->where('report_date', '>=', $startDate))
                      ->when($request->endDate, fn ($q) => $q->where('report_date', '<=', $endDate));
            }
        ])->get([
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
        $this->total_cost = 0;
        $this->total_orders = 0;
        $this->total_revenue = 0;
        // Initialize an empty collection to hold the grouped drivers by report_date
        $groupedDrivers = collect();
        foreach ($this->drivers as $driver) {
            $totalSum = 0;
            $business_reports = [];
            foreach ($driver->coordinator_reports as $report) {
                $fieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Total Orders'])->pluck('id')->first();
                $reportSum = $report->field_values->where('field_id', $fieldId)->sum('value');
                $totalSum += $reportSum;

                // Aggregating total field_values for each business_id
                $business_id = $report->business_id;
                if (!isset($business_reports[$business_id])) {
                    $business_reports[$business_id] = [
                        'business_id' => $business_id,
                        'total_orders' => 0,
                    ];
                }
                $business_reports[$business_id]['total_orders'] += $reportSum;

                $reportDate = $report->report_date->format('Y-m-d');

                // Check if the group already exists, if not create it
                if (!$groupedDrivers->has($reportDate)) {
                    $groupedDrivers->put($reportDate, collect());
                }

                // Add the driver to the corresponding group
                $groupedDrivers->get($reportDate)->push($driver);
                $driver->total_days = count($groupedDrivers);

            }
            $driver->total_orders = $totalSum;
            $driver->business_reports = array_values($business_reports);

            foreach ($driver->business_reports as $business_report) {
                $businessCalculations = Business::with('driver_calculations')->find($business_report['business_id']);

                if ($businessCalculations && $businessCalculations->driver_calculations) {
                    foreach ($businessCalculations->driver_calculations as $calculation) {
                        if ($calculation->type == 'RANGE' &&
                            $business_report['total_orders'] >= $calculation->from_value &&
                            $business_report['total_orders'] <= $calculation->to_value) {

                            $this->total_revenue += $business_report['total_orders'] * $calculation->amount;
                            break; // Assuming each total_orders fits only one range, you can break the loop once matched
                        }
                        if($calculation->type == 'FIXED'){
                            $this->total_revenue += $business_report['total_orders'] * $calculation->amount;
                        }
                    }
                }
            }

            // Sum of specific fields from the driver
            $calculated_salary = $this->calculate_driver_order_price($driver->total_orders, 26, $driver->driver_type->is_freelancer);
            $driver->total_salary =  $calculated_salary > 0 ? $calculated_salary : 0;
            $total_coordinate_days = $driver->coordinator_reports->count();
            $total_gprs = $daysDifference ? ($driver->gprs / 30) * $daysDifference : 0;
            $total_fuel =  $daysDifference ? ($driver->fuel / 30) * $daysDifference: 0;
            $total_government_cost = $daysDifference ? ($driver->government_cost / 30) * $daysDifference: 0;
            $total_accommodation = $daysDifference ? ($driver->accommodation / 30) * $daysDifference: 0;
            $total_vehicle_monthly_cost = $daysDifference ? ($driver->vehicle_monthly_cost / 30) * $daysDifference: 0;
            $total_mobile_data = $daysDifference ? ($driver->mobile_data / 30) * $daysDifference: 0;

            $driver->total_cost = $driver->total_salary + $total_gprs + $total_fuel + $total_government_cost + $total_accommodation + $total_vehicle_monthly_cost + $total_mobile_data;
            $this->total_cost += $driver->total_cost;
            $this->total_orders += $driver->total_orders;
        }
        $this->gross_profile = $this->total_revenue - $this->total_cost;

        // return $drivers;
        // Get Company Revenue As Per Business
        // 1. Get Driver Total Orders By Company
        // 2. Get Company's Driver Calculations
        // 3. Implement Logic For Multiple Each Order In Range and Fixed



        return $this->driverRevenueReportDataTable->render('drivers-revenue.index', $this->data);
    }

    public function getContent(Request $request){

        $currentDate = Carbon::now();
        $startDate = $request->startDate ? Carbon::parse($request->startDate)->toDateString() : $currentDate->startOfMonth()->toDateString();
        $endDate = $request->endDate ? Carbon::parse($request->endDate)->toDateString() : Carbon::today()->toDateString();

        // Calculate the difference in days between the start date and end date
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $daysDifference = $start->diffInDays($end) + 1;

        $this->businesses = Business::with(['coordinator_reports' => function($query) use ($startDate, $endDate, $request) {
            $query->with(['driver' => function($query) use ($request) {
                $query->when($request->driver_type_id, function ($q) use ($request) {
                    $q->where('driver_type_id', $request->driver_type_id);
                })->when($request->branch_id, function ($q) use ($request) {
                    $q->where('branch_id', $request->branch_id);
                });
            }])
            ->whereBetween('report_date', [$startDate, $endDate])
                  ->when($request->business_id, function ($q) use ($request) {
                      $q->where('business_id', $request->business_id);
                  })
                  ->when($request->driver_id, function ($q) use ($request) {
                      $q->where('driver_id', $request->driver_id);
                  });
        }, 'coordinator_reports.field_values'])->get();

        foreach ($this->businesses as $business) {
            $fieldId = BusinessField::where(['business_id' => $business->id, 'name' => 'Total Orders'])->pluck('id')->first();
            $totalSum = 0;
            foreach ($business->coordinator_reports as $report) {
                $totalSum += $report->field_values->where('field_id', $fieldId)->sum('value');
            }
            $business->total_orders = $totalSum;
        }


        $this->drivers = Driver::whereHas('coordinator_reports', function ($query) use ($request, $startDate, $endDate) {
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




        $this->total_cost = 0;
        $this->total_orders = 0;
        $this->total_revenue = 0;
        foreach ($this->drivers as $driver) {
            $totalSum = 0;
            $business_reports = [];
            foreach ($driver->coordinator_reports as $report) {
                $fieldId = BusinessField::where(['business_id' => $report->business_id, 'name' => 'Total Orders'])->pluck('id')->first();
                $reportSum = $report->field_values->where('field_id', $fieldId)->sum('value');
                $totalSum += $reportSum;

                // Aggregating total field_values for each business_id
                $business_id = $report->business_id;
                if (!isset($business_reports[$business_id])) {
                    $business_reports[$business_id] = [
                        'business_id' => $business_id,
                        'total_orders' => 0,
                    ];
                }
                $business_reports[$business_id]['total_orders'] += $reportSum;

            }
            $driver->total_orders = $totalSum;
            $driver->business_reports = array_values($business_reports);

            foreach ($driver->business_reports as $business_report) {
                $businessCalculations = Business::with('driver_calculations')->find($business_report['business_id']);

                if ($businessCalculations && $businessCalculations->driver_calculations) {
                    foreach ($businessCalculations->driver_calculations as $calculation) {
                        if ($calculation->type == 'RANGE' &&
                            $business_report['total_orders'] >= $calculation->from_value &&
                            $business_report['total_orders'] <= $calculation->to_value) {

                            $this->total_revenue += $business_report['total_orders'] * $calculation->amount;
                            break; // Assuming each total_orders fits only one range, you can break the loop once matched
                        }
                        if($calculation->type == 'FIXED'){
                            $this->total_revenue += $business_report['total_orders'] * $calculation->amount;
                        }
                    }
                }
            }

            // Sum of specific fields from the driver
            $calculated_salary = $this->calculate_driver_order_price($driver->total_orders, 26, $driver->driver_type->is_freelancer);
            $driver->total_salary =  $calculated_salary > 0 ? $calculated_salary : 0;
            $total_coordinate_days = $driver->coordinator_reports->count();
            $total_gprs = $daysDifference ? ($driver->gprs / 30) * $daysDifference : 0;
            $total_fuel =  $daysDifference ? ($driver->fuel / 30) * $daysDifference: 0;
            $total_government_cost = $daysDifference ? ($driver->government_cost / 30) * $daysDifference: 0;
            $total_accommodation = $daysDifference ? ($driver->accommodation / 30) * $daysDifference: 0;
            $total_vehicle_monthly_cost = $daysDifference ? ($driver->vehicle_monthly_cost / 30) * $daysDifference: 0;
            $total_mobile_data = $daysDifference ? ($driver->mobile_data / 30) * $daysDifference: 0;

            $driver->total_cost = $driver->total_salary + $total_gprs + $total_fuel + $total_government_cost + $total_accommodation + $total_vehicle_monthly_cost + $total_mobile_data;
            $this->total_cost += $driver->total_cost;
            $this->total_orders += $driver->total_orders;
        }
        $this->gross_profit = $this->total_revenue - $this->total_cost;

        return response()->json([
            'gross_profit' => number_format($this->gross_profit, 2),
            'total_cost' => number_format($this->total_cost, 2),
            'total_orders' => number_format($this->total_orders, 2),
            'total_revenue' => number_format($this->total_revenue, 2),
            'businesses' => $this->businesses
        ]);
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
