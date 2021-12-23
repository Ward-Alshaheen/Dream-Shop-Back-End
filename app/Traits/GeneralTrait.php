<?php

namespace App\Traits;

use App\Http\Controllers\Product\ImageControllers;
use App\Http\Controllers\Product\LikeController;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

trait GeneralTrait
{

    public function returnError($errNum, $msg): JsonResponse
    {
        return response()->json([
            'status' => false,
            'errNum' => $errNum,
            'msg' => $msg
        ], 401);
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

    public function price($discounts, $remaining_days)
    {
        $price = $discounts['main'];
        $discounts = $discounts['d'];
        if ($remaining_days > $discounts[0]['remaining_days']) {
            return $price * 1;
        }
        if ($remaining_days > $discounts[1]['remaining_days']) {
            $d = 100 - $discounts[0]['discount'];
            return $price * 1 * ($d / 100);
        }
        if ($remaining_days > $discounts[2]['remaining_days']) {
            $d = 100 - $discounts[1]['discount'];
            return $price * 1 * ($d / 100);
        }
        $d = 100 - $discounts[2]['discount'];
        return $price * 1 * ($d / 100);
    }

    //Save Image
    public function saveImage($image, $file): string
    {
        $newImage = time() . $this->returnCode(20) . $image->getClientOriginalName();
        $image->move("uploads/$file", $newImage);
        return URL::to("/uploads/$file") . "/" . $newImage;
    }

    //is sort
    public function isSort(Request $request): bool
    {
        if ($request->hasHeader('sort')) {
            if (
                $request->header('sort') != 'created_at' &&
                $request->header('sort') != 'price' &&
                $request->header('sort') != 'name' &&
                $request->header('sort') != 'expiration_date' &&
                $request->header('sort') != 'remaining_days' &&
                $request->header('sort') != 'category' &&
                $request->header('sort') != 'quantity' &&
                $request->header('sort') != 'likes_count' &&
                $request->header('sort') != 'views_count') {
                return false;
            }
            return true;
        }
        return false;
    }
    public function descSort(Request $request):bool{
        if ($this->isSort($request)&&$request->header('desc')=="true"){
            return true;
        }
        return false;
    }
    public function productQuery(string  $sort,bool $desc): Builder
    {
        if ($desc){
            return Product::with('user')
                ->withCount('likes')
                ->withCount('views')
                ->orderByDesc($sort);
        }
        return Product::with('user')
            ->withCount('likes')
            ->withCount('views')
            ->orderBy($sort);
    }
    public function getProducts($products){
        for ($i = 0; $i < count($products); $i++) {
            $products[$i]['images'] = ImageControllers::getImages($products[$i]['id']);
            $products[$i]['me_likes'] = LikeController::meLike($products[$i]['id']);
            $products[$i]['discounts'] = json_decode($products[$i]['discounts'], true);
        }
        return $products;
    }
}

