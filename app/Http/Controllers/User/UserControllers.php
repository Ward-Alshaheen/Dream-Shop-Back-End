<?php

namespace App\Http\Controllers\User;

use App\Traits\GeneralTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserControllers extends AuthController
{
    use GeneralTrait;

    //Add Image
    public function addImage(Request $request): JsonResponse
    {
        $image = $request->all();
        $validator = Validator::make($image, [
            'image' => 'required|image',
        ]);
        if ($validator->fails()) {
            return $this->returnError(401, $validator->errors());
        }
        $user = Auth::user();
        $newImage = time() . $this->returnCode(20) . $image['image']->getClientOriginalName();
        $image['image']->move('uploads/userImage', $newImage);
        $user['image'] = 'http://127.0.0.1:8000/uploads/userImage/' . $newImage;
        $user->save();
        //unlink( substr($user['image'],22));
        return $this->returnData("image", $user['image']);
        //return $this->returnData("image",strlen('http://127.0.0.1:8000/'));
    }

    //Delete Image
    public function deleteImage(): JsonResponse
    {
        $user = Auth::user();
        if (!$user['image']) {
            return $this->returnError(401, "Image not found");
        }
        unlink(substr($user['image'], 22));
        $user['image'] = null;
        $user->save();
        return $this->returnSuccessMessage("Successfully");
    }

    //Update User
    public function updateUser(Request $request): JsonResponse
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'email' => 'required|email',
            'image' => 'image',
        ]);
        if ($validator->fails()) {
            return $this->returnError(401, $validator->errors());
        }
        $user = Auth::user();
        $user['name'] = $input['name'];
        if ($request->has('bio')) {
            $user['bio'] = $input['bio'];
        }else{
            $user['bio'] =null;
        }
        if ($request->has('image')) {
            if ($user['image'] != null) {
                $this->deleteImage();
            }
            $this->addImage($request);
        }
        if ($user['email'] != $input['email']) {
            $user['email'] = $input['email'];
            $user['account_confirmation']=false;
            $user->save();
            $this->sendRegisterCode();
            return $this->returnSuccessMessage("Update and send your email Successfully");
        }
        $user->save();
        return $this->returnSuccessMessage("Update  Successfully");
    }
    public  function changePassword(Request $request): JsonResponse
    {
        $reset=$request->all();
        $validator = Validator::make($reset, [
            'old_password'=>'required|min:8',
            'password' => 'required|min:8',
            'c_password' => 'required|same:password'
        ]);
        if ($validator->fails()) {
            return $this->returnError(401, $validator->errors());
        }
        $user=Auth::user();
        if (!Hash::check($reset['old_password'],$user['password'])){
            return $this->returnError(401,'The password is incorrect');
        }
        $user['password']=Hash::make($reset['password']);
        $user->save();
        return $this->returnSuccessMessage('Successfully');
    }
    /**
     * Get the authenticated User.
     *
     */
    public function me(): JsonResponse
    {
        return $this->returnData("user", auth()->user());
    }
    /**
     * Refresh a token.
     *
     */
    public function refresh(): JsonResponse
    {
        $token = auth()->refresh();
        return $this->returnData("new token", $token);
    }
}
