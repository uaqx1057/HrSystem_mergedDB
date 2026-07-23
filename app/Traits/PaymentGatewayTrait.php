<?php

namespace App\Traits;

use App\Models\Company;
use Froiden\RestAPI\Exceptions\ApiException;
use Illuminate\Support\Facades\Config;

trait PaymentGatewayTrait
{

    private function paystackSet($companyHash)
    {

        // This needs to be set according to company id
        $paymentGateway = $this->getGateway($companyHash);

        $payStackMode = $paymentGateway->paystack_mode;

        if ($payStackMode == 'sandbox') {
            $key = ($paymentGateway->test_paystack_key) ?: config('paystack.publicKey');
            $apiSecret = ($paymentGateway->test_paystack_secret) ?: config('paystack.secretKey');
            $email = ($paymentGateway->test_paystack_merchant_email) ?: config('paystack.merchantEmail');
        }
        else {
            $key = ($paymentGateway->paystack_key) ?: config('paystack.publicKey');
            $apiSecret = ($paymentGateway->paystack_secret) ?: config('paystack.secretKey');
            $email = ($paymentGateway->paystack_merchant_email) ?: config('paystack.merchantEmail');
        }

        $url = ($paymentGateway->paystack_payment_url) ?: config('paystack.paymentUrl');

        Config::set('paystack.publicKey', $key);
        Config::set('paystack.secretKey', $apiSecret);
        Config::set('paystack.paymentUrl', $url);
        Config::set('paystack.merchantEmail', $email);

    }

    private function mollieSet($companyHash)
    {
        $paymentGateway = $this->getGateway($companyHash);
        $mollie_api_key = ($paymentGateway->mollie_api_key) ?: config('mollie.key');
        Config::set('mollie.key', $mollie_api_key);
    }

    private function payfastSet($companyHash)
    {
        $paymentGateway = $this->getGateway($companyHash);

        if ($paymentGateway->payfast_mode == 'sandbox') {
            $payfast_merchant_id = ($paymentGateway->test_payfast_merchant_id) ?: config('payfast.merchant.merchant_id');
            $payfast_merchant_key = ($paymentGateway->test_payfast_merchant_key) ?: config('payfast.merchant.merchant_key');
            $payfast_passphrase = ($paymentGateway->test_payfast_passphrase) ?: config('payfast.passphrase');
        }
        else {
            $payfast_merchant_id = ($paymentGateway->payfast_merchant_id) ?: config('payfast.merchant.merchant_id');
            $payfast_merchant_key = ($paymentGateway->payfast_merchant_key) ?: config('payfast.merchant.merchant_key');
            $payfast_passphrase = ($paymentGateway->payfast_passphrase) ?: config('payfast.passphrase');
        }

        $payfast_mode = ($paymentGateway->payfast_mode == 'sandbox');

        Config::set('payfast.merchant.merchant_id', $payfast_merchant_id);
        Config::set('payfast.merchant.merchant_key', $payfast_merchant_key);
        Config::set('payfast.passphrase', $payfast_passphrase);
        Config::set('payfast.testing', $payfast_mode);

    }

    private function flutterwaveSet($companyHash)
    {
        $paymentGateway = $this->getGateway($companyHash);
        // Flutterwave
        $flutterwave_mode = $paymentGateway->flutterwave_mode;

        if ($flutterwave_mode == 'sandbox') {
            $flutterwave_key = ($paymentGateway->test_flutterwave_key) ?: config('flutterwave.publicKey');
            $flutterwave_secret = ($paymentGateway->test_flutterwave_secret) ?: config('flutterwave.secretKey');
            $flutterwave_hash = ($paymentGateway->test_flutterwave_hash) ?: config('flutterwave.secretHash');
        }
        else {
            $flutterwave_key = ($paymentGateway->live_flutterwave_key) ?: config('flutterwave.publicKey');
            $flutterwave_secret = ($paymentGateway->live_flutterwave_secret) ?: config('flutterwave.secretKey');
            $flutterwave_hash = ($paymentGateway->live_flutterwave_hash) ?: config('flutterwave.secretHash');
        }


        Config::set('flutterwave.publicKey', $flutterwave_key);
        Config::set('flutterwave.secretKey', $flutterwave_secret);
        Config::set('secretHash.merchantEmail', $flutterwave_hash);
    }

    private function authorizeSet($companyHash)
    {
        $paymentGateway = $this->getGateway($companyHash);
        $authorize_api_login_id = ($paymentGateway->authorize_api_login_id) ?: config('services.authorize.login');
        $authorize_transaction_key = ($paymentGateway->authorize_transaction_key) ?: config('services.authorize.transaction');

        $authorize_environment = ($paymentGateway->authorize_environment == 'sandbox');

        Config::set('services.authorize.login', $authorize_api_login_id);
        Config::set('services.authorize.transaction', $authorize_transaction_key);
        Config::set('services.authorize.sandbox', $authorize_environment);


    }

    private function squareSet($companyHash)
    {
        $paymentGateway = $this->getGateway($companyHash);
        // square
        $square_application_id = ($paymentGateway->square_application_id) ?: config('services.square.application_id');
        $square_access_token = ($paymentGateway->square_access_token) ?: config('services.square.access_token');
        $square_location_id = ($paymentGateway->square_location_id) ?: config('services.square.location_id');

        $square_environment = $paymentGateway->square_environment;

        Config::set('services.square.application_id', $square_application_id);
        Config::set('services.square.access_token', $square_access_token);
        Config::set('services.square.location_id', $square_location_id);
        Config::set('services.square.environment', $square_environment);
    }

    private function getGateway($companyHash)
    {

        $company = Company::where('hash', $companyHash)->first();

        if (!$company) {
            throw new ApiException('Please enter the correct webhook url. You have entered wrong webhook url', null, 200);
        }

        // This needs to be set according to company id
        return $company->paymentGatewayCredentials;
    }

}
