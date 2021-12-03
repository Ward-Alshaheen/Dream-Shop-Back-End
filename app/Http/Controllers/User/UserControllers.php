<?php

namespace App\Http\Controllers\User;

use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserControllers extends AuthController
{
    use GeneralTrait;

    //Add Image
    public function addImage(Request $request)
    {
        $image = $request->all();
        $validator = Validator::make($image, [
            'image' => 'required|image',
        ]);
        if ($validator->fails()) {
            return $this->returnError(401, $validator->errors());
        }
        $newImage = time() . $this->returnCode(20) . $image['image']->getClientOriginalName();
        $user = Auth::user();
        $image['image']->move('uploads/userImage', $newImage);
        $user['image'] = 'http://127.0.0.1:8000/uploads/userImage/' . $newImage;
        $user->save();
        //unlink( substr($user['image'],22));
        return $this->returnData("image", $user['image']);
        //return $this->returnData("image",strlen('http://127.0.0.1:8000/'));
    }

    //Delete Image
    public function deleteImage()
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
    public function updateUser(Request $request)
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
}
