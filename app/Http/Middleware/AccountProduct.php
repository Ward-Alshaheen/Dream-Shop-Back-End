<?php

namespace App\Http\Middleware;

use App\Traits\GeneralTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountProduct
{
    use GeneralTrait;
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::user()['account_confirmation'])
            return $this->returnError(401,'your Account not Confirmation');
        return $next($request);
    }
}
