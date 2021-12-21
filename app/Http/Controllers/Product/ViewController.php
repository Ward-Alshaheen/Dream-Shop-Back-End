<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\View;
use App\Traits\GeneralTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ViewController extends Controller
{
    use GeneralTrait;
    public function view(int $id): JsonResponse
    {
        if (View::where('user_id',Auth::id())->where("product_id",$id)->first()){
            return $this->returnError(401,"I've already seen it");
        }
        $product=Product::find($id);
        if (!$product){
            return $this->returnError(55, 'not found');
        }
        if ($product->user['id']==Auth::id())
            return $this->returnError(55, 'is your product');
        View::create([
            "user_id"=>Auth::id(),
            "product_id"=>$id
        ]);
        return $this->returnSuccessMessage("Successfully");
    }
}
