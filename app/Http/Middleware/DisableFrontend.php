<?php

namespace App\Http\Middleware;

use App\Models\GlobalSetting;
use Closure;

class DisableFrontend
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $global = global_setting();
        $frontendDisabled = $global && $global->frontend_disable;

        if ($frontendDisabled && request()->route()->getName() != 'front.signup.index' && !request()->ajax()) {
            return redirect(route('login'));
        }

        return $next($request);
    }

}
