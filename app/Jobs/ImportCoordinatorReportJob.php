<?php

namespace App\Jobs;

use App\Models\CoordinatorReport;
use App\Models\Role;
use App\Models\UniversalSearch;
use App\Models\Driver;
use App\Models\Business;
use App\Traits\ExcelImportable;
use App\Traits\UniversalSearchTrait;
use App\Models\UserAuth;
use App\Models\BusinessField;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ImportCoordinatorReportJob implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, UniversalSearchTrait;
    use ExcelImportable;

    private $row;
    private $columns;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($row, $columns)
    {
        $this->row = $row;
        $this->columns = $columns;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (
            $this->isColumnExists('Business ID') &&
            $this->isColumnExists('Iqaama Number') &&
            $this->isColumnExists('Date') &&
            $this->isColumnExists('Total Orders') &&
            $this->isColumnExists('Bonous') &&
            $this->isColumnExists('Tip') &&
            $this->isColumnExists('Other Tip')
            )
        {

            $business_id = $this->getColumnValue('Business ID');
            $iqaama_number = $this->getColumnValue('Iqaama Number');
            $date = $this->getColumnValue('Date');
            $total_orders = $this->getColumnValue('Total Orders');
            $bonous = $this->getColumnValue('Bonous');
            $tip = $this->getColumnValue('Tip');
            $other_tip = $this->getColumnValue('Other Tip');

            $business = Business::find($business_id);

            if (is_null($business)) {
                $this->failJobWithMessage(__('messages.businessNotFound') . $business_id);
                return;
            }

            $driver = Driver::where('iqaama_number', $iqaama_number)->first();

            if (is_null($driver)) {
                $this->failJobWithMessage(__('messages.iqaamaNumberNotFound') . $iqaama_number);
            }else{
                DB::beginTransaction();
                try {

                    // Parse and format the date correctly
                    $parsedDate = Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');

                    // New Coordinator Report Creation
                    $data = [];
                    $data['business_id'] = $business_id;
                    $data['driver_id'] = $driver->id;
                    $data['admin_submitted'] = 1;
                    $data['report_date'] = $parsedDate;

                    $checkCoordinatorReport = CoordinatorReport::where([
                        'driver_id' => $data['driver_id'],
                        'business_id' => $data['business_id'],
                        'report_date' => $data['report_date']
                    ])->first();

                    if (is_null($checkCoordinatorReport)) {
                        $report = CoordinatorReport::create($data);

                        // Fetching DB Fields Against Business
                        $fieldsName = ['Cash Paid at Restaurant', 'Cash Collected by Driver',
                        'Net Cash Received at Branch', 'Balance In Wallet', 'Total Orders', 'Fuel Amount', 'Tip', 'Bonus', 'Other Tip'];
                        $fieldsData = [];
                        foreach ($fieldsName as $key => $fieldName) {
                            $businessField = BusinessField::where(['business_id' => $business_id, 'name' => $fieldName])->first();

                            if ($businessField) {
                                $fieldValue = $this->isColumnExists($fieldName) ? $this->getColumnValue($fieldName) : 0;
                                $fieldsData[] = ['field_id' => $businessField->id, 'value' => $fieldValue];
                            }
                        }
                        $report->field_values()->createMany($fieldsData);
                    }


                    DB::commit();
                } catch (\Carbon\Exceptions\InvalidFormatException $e) {
                    DB::rollBack();
                    $this->failJob(__('messages.invalidDate'));
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->failJobWithMessage($e->getMessage());
                }

            }




        }else {
            $this->failJob(__('messages.invalidData'));
        }
    }
}
