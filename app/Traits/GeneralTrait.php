<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait GeneralTrait
{

    public function returnError($errNum, $msg): JsonResponse
    {
        return response()->json([
            'status' => false,
            'errNum' => $errNum,
            'msg' => $msg
        ],401);
    }


    public function returnSuccessMessage($msg = "", $errNum = "S000"): JsonResponse
    {
        return response()->json([
            'status' => true,
            'errNum' => $errNum,
            'msg' => $msg
        ]);
    }

    public function returnData($key, $value, $msg = ""): JsonResponse
    {
        return response()->json([
            'status' => true,
            'errNum' => "S000",
            'msg' => $msg,
            $key => $value
        ]);
    }
    function returnCode($length = 6): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    public function  price($discounts,$remaining_days){
        $price=$discounts['main'];
        $discounts=$discounts['d'];
        if ($remaining_days>$discounts[0]['remaining_days']){
            return $price*1;
        }
        if ($remaining_days<=$discounts[0]['remaining_days']&&$remaining_days>$discounts[1]['remaining_days']){
         $d=100-$discounts[0]['discount'];
         return $price *1*($d/100);
        }
        if ($remaining_days<=$discounts[1]['remaining_days']&&$remaining_days>$discounts[2]['remaining_days']){
            $d=100-$discounts[1]['discount'];
            return $price *1*($d/100);
        }
        $d=100-$discounts[2]['discount'];
        return $price *1*($d/100);
    }

}
