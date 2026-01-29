<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class CoordinatorReportImport implements ToArray
{

    public static function fields(): array
    {
        return array(
            array('id' => 'Business ID', 'name' => __('modules.businesses.businessId'), 'required' => 'Yes'),
            array('id' => 'Iqaama Number', 'name' => __('modules.businesses.iqaamaNumber'), 'required' => 'Yes'),
            array('id' => 'Date', 'name' => __('modules.businesses.date'), 'required' => 'Yes'),
            array('id' => 'Total Orders', 'name' => __('modules.businesses.totalOrders'), 'required' => 'Yes'),
            array('id' => 'Cash Paid at Restaurant', 'name' => __('modules.businesses.cashPaidAtRestaurant'), 'required' => 'No'),
            array('id' => 'Cash Collected by Driver', 'name' => __('modules.businesses.cashCollectedByDriver'), 'required' => 'No'),
            array('id' => 'Net Cash Received at Branch', 'name' => __('modules.businesses.netCashReceivedAtBranch'), 'required' => 'No'),
            array('id' => 'Balance In Wallet', 'name' => __('modules.businesses.balanceInWallet'), 'required' => 'No'),
            array('id' => 'Fuel Amount', 'name' => __('modules.businesses.fuelAmount'), 'required' => 'No'),
            array('id' => 'Tip', 'name' => __('modules.businesses.tip'), 'required' => 'No'),
            array('id' => 'Bonous', 'name' => __('modules.businesses.bonous'), 'required' => 'No',),
            array('id' => 'Other Tip', 'name' => __('modules.businesses.other_tip'), 'required' => 'No')
        );
    }

    public function array(array $array): array
    {
        return $array;
    }

}
