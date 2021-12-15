<?php

namespace App\Http\Controllers\Like;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Product;
use App\Traits\GeneralTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    use GeneralTrait;
    public function like(int $id): JsonResponse
    {
        if ($like=Like::where('user_id',Auth::id())->where("product_id",$id)->first()){
            $like->delete();
            return $this->returnSuccessMessage("Successfully");
        }
        $product=Product::find($id);
        if (!$product){
            return $this->returnError(55, 'not found');
        }
        Like::create([
            "user_id"=>Auth::id(),
            "product_id"=>$id
        ]);
        return $this->returnSuccessMessage("Successfully");
    }
   static public function countLike(int $id): int{
        return Like::where('product_id',$id)->count();
    }
    static public function meLike(int $id):bool{
        if (Like::where('user_id',Auth::id())->where("product_id",$id)->first())
            return true;
        return false;
    }
    public function myProductLike(): JsonResponse
    {
        $products=[];
        $likes=Like::where('user_id',Auth::id())->get();
      for ($i=0;$i<count($likes);$i++){
          $products[]=$likes[$i]->product;
          $products[$i]['images'] = json_decode($products[$i]['images'], true);
          $products[$i]->user;
          $products[$i]['likes']=LikeController::countLike($products[$i]['id']);
          $products[$i]['meLikes']=LikeController::meLike($products[$i]['id']);
      }
        return $this->returnData('products', $products);
    }
}
