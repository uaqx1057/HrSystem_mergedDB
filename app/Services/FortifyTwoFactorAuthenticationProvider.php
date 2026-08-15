<?php

namespace App\Services;

use Illuminate\Contracts\Cache\Repository;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use PragmaRX\Google2FA\Google2FA;

class FortifyTwoFactorAuthenticationProvider implements TwoFactorAuthenticationProviderContract
{
    public function __construct(private Google2FA $engine, private ?Repository $cache = null)
    {
    }

    public function generateSecretKey(int $secretLength = 16)
    {
        return $this->engine->generateSecretKey($secretLength);
    }

    public function qrCodeUrl($companyName, $companyEmail, $secret)
    {
        return $this->engine->getQRCodeUrl($companyName, $companyEmail, $secret);
    }

    public function verify($secret, $code)
    {
        // Match the tolerant window used during 2FA setup (UserAuth::confirmTwoFactorAuth),
        // otherwise a code confirmed at setup time can fail here due to normal clock drift.
        $window = max((int) config('fortify-options.two-factor-authentication.window', 2), 4);

        // Use time-window validation without per-code global cache lockout.
        return (bool) $this->engine->verifyKey($secret, $code, $window);
    }
}
