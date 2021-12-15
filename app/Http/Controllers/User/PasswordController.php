<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\EmailLaravel;
use App\Models\Code;
use App\Models\User;
use App\Traits\GeneralTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PasswordController extends Controller
{
    use GeneralTrait;

    //Sand Password Reset Code
    public function sendPasswordResetCode(Request $request): JsonResponse
    {
        $email=$request->all();
        $validator = Validator::make($email, [
            'email' => 'required|email'
        ]);
        if ($validator->fails()) {
            return $this->returnError(401, $validator->errors());
        }
        $user = User::where('email',$email)->first();
        if (!$user){
            return $this->returnError(400,"email not found");
        }
        $code = ['code' => $this->returnCode(), "user_id" => $user['id']];
        Code::create($code);
        Mail::to($user['email'])->send(new EmailLaravel($code['code']));
        return $this->returnSuccessMessage("send code");

    }

    //Check the password reset code
    public function checkPasswordResetCode(Request $request): JsonResponse
    {

        $reset=$request->all();
        $validator = Validator::make($reset, [
            'code' => 'required',
            'email' => 'required|email'
        ]);
        if ($validator->fails()) {
            return $this->returnError(401, $validator->errors());
        }
        $code=Code::where('code',$reset['code'])->first();
        if (!$code){
            return $this->returnError(401,'error code');
        }
        $user= $code->user;
        if ($user['email']!=$reset['email']){
            return $this->returnError(401,'error code');
        }
        $user['reset_password']=true;
        $user->save();
        $code->delete();
        $token = auth()->login($user);
        $user['token'] = $token;

        return $this->returnData("user",$user);

    }

    //Password Reset
    public function passwordReset(Request $request): JsonResponse
    {
        $reset=$request->all();
        $validator = Validator::make($reset, [
            'password' => 'required|min:8',
            'c_password' => 'required|same:password'
        ]);
        if ($validator->fails()) {
            return $this->returnError(401, $validator->errors());
        }
        $user=Auth::user();
        $user['password']=Hash::make($reset['password']);
        $user['reset_password']=false;
        $user->save();
        return $this->returnSuccessMessage("Password Reset Successfully");
    }

}
