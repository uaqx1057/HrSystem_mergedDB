<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReceiptVoucherController extends Controller
{
    public function getReceiptVoucher(Request $request, $iqaama_number){
        $receipt_vouhcers = ReceiptVoucher::has('driver')->with(['driver' => function($query) use($iqaama_number) {
            return $query->where('iqaama_number', $iqaama_number);
        }])->get();

        return $receipt_vouhcers;
    }
}
