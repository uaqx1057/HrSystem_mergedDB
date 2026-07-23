<?php
/**
 * Created by PhpStorm.
 * User: DEXTER
 * Date: 24/05/17
 * Time: 11:29 PM
 */

namespace App\Traits\SuperAdmin;

use App\Models\SuperAdmin\GlobalPaymentGatewayCredentials;
use Illuminate\Support\Facades\Config;

trait PaystackSettings
{

    public function setPaystackConfigs()
    {
        $settings = GlobalPaymentGatewayCredentials::first();

        if($settings->paystack_mode == 'sandbox'){
            $key       = ($settings->test_paystack_key) ?: config('paystack.publicKey');
            $apiSecret = ($settings->test_paystack_secret) ?: config('paystack.secretKey');
            $email = ($settings->test_paystack_merchant_email) ?: config('paystack.merchantEmail');
        }
        else{
            $key       = ($settings->paystack_key) ?: config('paystack.publicKey');
            $apiSecret = ($settings->paystack_secret) ?: config('paystack.secretKey');
            $email = ($settings->paystack_merchant_email) ?: config('paystack.merchantEmail');
        }

        $url = ($settings->paystack_payment_url) ?: config('paystack.paymentUrl');


        Config::set('paystack.publicKey', $key);
        Config::set('paystack.secretKey', $apiSecret);
        Config::set('paystack.paymentUrl', $url);
        Config::set('paystack.merchantEmail', $email);
    }

}



