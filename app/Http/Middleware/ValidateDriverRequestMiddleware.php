<?php

namespace App\Http\Middleware;

use App\Models\Driver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateDriverRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authorizationHeader = $request->header('Authorization');

        if ($authorizationHeader) {
            // Extract the token from the header
            list($type, $encodedToken) = explode(' ', $authorizationHeader, 2);

            if ($type === 'Bearer' && $encodedToken) {
                // Decode the base64 encoded token
                $encodedToken = str_replace('Bearer ', '', $encodedToken);

                $ciphering = "AES-128-CTR";
                $options = 0;
                $encryption_iv = '1234567891011121';
                $encryption_key = 'H%$^&%!@)(*)^%0';
                $value = str_replace('_', '/', $encodedToken);
                $value = openssl_decrypt($value, $ciphering, $encryption_key, $options, $encryption_iv);

                // Validate the driver ID and token
                $driver = Driver::where('iqaama_number', $value)->first();
                if ($driver) {
                    // Attach the driver to the request
                    $request->attributes->set('driver', $driver);
                    // Token is valid
                    return $next($request);
                }
            }
        }

        // Invalid token
        return response()->json(['status' => 401, 'message' => 'Unauthorized'], 401);
    }
}
