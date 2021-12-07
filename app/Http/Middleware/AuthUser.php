<?php

namespace App\Http\Middleware;

use App\Traits\GeneralTrait;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;
class AuthUser
{
    use GeneralTrait;

    /**
     * Handle an incoming request.
     * @method parseToken()
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
            $token = $request->header('auth-token');
            $request->headers->set('auth-token', (string) $token, true);
            $request->headers->set('Authorization', 'Bearer '.$token, true);
            try {
                //  $user = $this->auth->authenticate($request);  //check authenticted user
                JWTAuth::parseToken()->authenticate();
            } catch (TokenExpiredException $e) {
                return  $this -> returnError(401,'Unauthenticated user');
            } catch (JWTException $e) {

                return  $this -> returnError(401, 'token_invalid');
            }


        return $next($request);
}
}
