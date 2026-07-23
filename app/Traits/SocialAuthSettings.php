<?php

/**
 * Created by PhpStorm.
 * User: DEXTER
 * Date: 24/05/17
 * Time: 11:29 PM
 */

namespace App\Traits;

use App\Models\SocialAuthSetting;
use Illuminate\Support\Facades\Config;

trait SocialAuthSettings
{

    public function setSocailAuthConfigs()
    {
        $settings = SocialAuthSetting::first();

        Config::set('services.facebook.client_id', ($settings->facebook_client_id) ?: config('services.facebook.client_id'));
        Config::set('services.facebook.client_secret', ($settings->facebook_secret_id) ?: config('services.facebook.client_secret'));
        Config::set('services.facebook.redirect', $this->updateMainAppUrl(route('social_login_callback', 'facebook')));

        Config::set('services.google.client_id', ($settings->google_client_id) ?: config('services.google.client_id'));
        Config::set('services.google.client_secret', ($settings->google_secret_id) ?: config('services.google.client_secret'));
        Config::set('services.google.redirect', $this->updateMainAppUrl(route('social_login_callback', 'google')));

        Config::set('services.twitter.client_id', ($settings->twitter_client_id) ?: config('services.twitter.client_id'));
        Config::set('services.twitter.client_secret', ($settings->twitter_secret_id) ?: config('services.twitter.client_secret'));
        Config::set('services.twitter.redirect', $this->updateMainAppUrl(route('social_login_callback', 'twitter')));

        Config::set('services.linkedin.client_id', ($settings->linkedin_client_id) ?: config('services.linkedin.client_id'));
        Config::set('services.linkedin.client_secret', ($settings->linkedin_secret_id) ?: config('services.linkedin.client_secret'));
        Config::set('services.linkedin.redirect', $this->updateMainAppUrl(route('social_login_callback', 'linkedin')));
    }

    private function updateMainAppUrl($url)
    {
        if (isWorksuiteSaas() && module_enabled('Subdomain')) {
            $appUrl = config('app.main_app_url');
            $appUrl = str($appUrl)->after('://')->before('/');
            $currentUrl = str(url('/'))->after('://')->before('/');
            $url = str($url)->replace($currentUrl, $appUrl)->__toString();
        }

        return $url;
    }

}
