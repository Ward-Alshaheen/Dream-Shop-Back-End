<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Product;
use App\Traits\GeneralTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    use GeneralTrait;

    public function like(int $id): JsonResponse
    {
        if ($like = Like::where('user_id', Auth::id())->where("product_id", $id)->first()) {
            $like->delete();
            return $this->returnSuccessMessage("Successfully");
        }
        $product = Product::find($id);
        if (!$product) {
            return $this->returnError(55, 'not found');
        }
        if ($product->user['id'] == Auth::id())
            return $this->returnError(55, 'is your product');
        Like::create([
            "user_id" => Auth::id(),
            "product_id" => $id
        ]);
        return $this->returnSuccessMessage("Successfully");
    }

    static public function meLike(int $id): bool
    {
        if (Like::where('user_id', Auth::id())->where("product_id", $id)->first())
            return true;
        return false;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function myProductLike(Request $request): JsonResponse
    {
        $products=$this->getSort($request);
        $products=$this->getProducts( $products
            ->join('likes', 'likes.product_id', '=', 'products.id')
            ->where('likes.user_id', Auth::id())
            ->get());
        return $this->returnData('products', $products);
    }
}
