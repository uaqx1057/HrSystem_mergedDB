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

trait StripeSettings
{

    public function setStripConfigs()
    {
        $settings = GlobalPaymentGatewayCredentials::first();

        if($settings->stripe_mode == 'test'){
            $stripeClientId = $settings->test_stripe_client_id;
            $stripeSecret = $settings->test_stripe_secret;
            $stripeWebhookSecret = $settings->test_stripe_webhook_secret;
        }
        else{
            $stripeClientId = $settings->live_stripe_client_id;
            $stripeSecret = $settings->live_stripe_secret;
            $stripeWebhookSecret = $settings->live_stripe_webhook_secret;
        }

        $key       = ($stripeClientId) ? $stripeClientId : config('cashier.key');
        $apiSecret = ($stripeSecret) ? $stripeSecret : config('cashier.secret');
        $webhookKey = ($stripeWebhookSecret) ? $stripeWebhookSecret : config('cashier.webhook.secret');

        Config::set('cashier.key', $key);
        Config::set('cashier.secret', $apiSecret);
        Config::set('cashier.webhook.secret', $webhookKey);
    }

}



