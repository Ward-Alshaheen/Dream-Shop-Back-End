<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\EmailLaravel;
use App\Models\Code;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Traits\GeneralTrait;

class AuthController extends Controller
{
    use GeneralTrait;

    /**
     * Create a new AuthController instance.
     *
     */
//    public function __construct()
//    {
//        $this->middleware('auth.guard:api', ['except' => ['login','register']]);
//    }

    /**
     * Get a JWT via given credentials.
     *
     */
    public function login(): JsonResponse
    {
        $credentials = request(['email', 'password']);
        $token = auth()->attempt($credentials);
        if (!$token) {
            return $this->returnError(401, 'Unauthorized');
        }
        $user = auth()->user();
        $user['token'] = $token;
//        $expires_in = auth()->factory()->getTTL() / 60 / 24 / 365;
//        $user['expires_in'] = "$expires_in years";
        return $this->returnData('user', $user, 'User login successfully');
    }

    /**
     * register
     *
     */
    public function register(Request $request): JsonResponse
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'c_password' => 'required|same:password'
        ]);
        if ($validator->fails()) {
            return $this->returnError(401, $validator->errors());
        }

        $input['password'] = Hash::make($input['password']);
        $user = User::create($input);
        $user['token'] = auth()->login($user);
//        $expires_in = auth()->factory()->getTTL() / 60 / 24 / 365;
//        $user['expires_in'] = "$expires_in years";
        $this->sendRegisterCode();
        return $this->returnData('user', $user, 'User registered sand your email ');
    }

    //Sand Register Code
    public  function sendRegisterCode(): JsonResponse
    {
        $user=Auth::user();
        $code = ['code' => $this->returnCode(), "user_id" => $user['id']];
        Code::create($code);
        Mail::to($user['email'])->send(new EmailLaravel($code['code']));
        return $this->returnSuccessMessage("send code");
    }

    public function checkRegisterCode(Request $request): JsonResponse
    {
        $reset=$request->all();
        $validator = Validator::make($reset, [
            'code' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->returnError(401, $validator->errors());
        }
        $code=Code::where('code',$reset)->first();
        if (!$code){
            return $this->returnError(401,'error code');
        }
        $user= $code->user;
        if (!Auth::id()==$user['id']){
            return $this->returnError(401,'error code');

        }
        $user['account_confirmation']=true;
        $user->save();
        $code->delete();
        return $this->returnSuccessMessage("Successfully");
    }

    /**
     * Log the user out (Invalidate the token).
     *
     */
    public function logout(): JsonResponse
    {
        auth()->logout();

        return $this->returnSuccessMessage('Successfully logged out');
    }


}
