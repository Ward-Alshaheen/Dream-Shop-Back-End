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
        if ($request->hasHeader('sort')) {
            if(
                $request->header('sort') != 'created_at' &&
                $request->header('sort') != 'price' &&
                $request->header('sort') != 'name' &&
                $request->header('sort') != 'expiration_date' &&
                $request->header('sort') != 'remaining_days' &&
                $request->header('sort') != 'category' &&
                $request->header('sort') != 'quantity' &&
                $request->header('sort') != 'likes_count' &&
                $request->header('sort') != 'views_count') {
                return $this->returnError(401, 'error sort');
            }
            if ($request->header('desc') == "true") {
                $products = Product::join('likes', 'likes.product_id', '=', 'products.id')
                    ->where('likes.user_id', Auth::id())
                    ->with('user')
                    ->withCount('likes')
                    ->withCount('views')
                    ->orderByDesc($request->header('sort'))
                    ->get();
            } else {
                $products = Product::join('likes', 'likes.product_id', '=', 'products.id')
                    ->where('likes.user_id', Auth::id())
                    ->with('user')
                    ->withCount('likes')
                    ->withCount('views')
                    ->orderBy($request->header('sort'))
                    ->get();
            }
        } else {
            $products = Product::join('likes', 'likes.product_id', '=', 'products.id')
                ->where('likes.user_id', Auth::id())
                ->with('user')
                ->withCount('likes')
                ->withCount('views')
                ->orderBy('remaining_days')
                ->get();
        }
        for ($i = 0; $i < count($products); $i++) {
            $products[$i]['images'] = json_decode($products[$i]['images'], true);
            $products[$i]['me_likes'] = LikeController::meLike($products[$i]['id']);
        }
        return $this->returnData('products', $products);
    }
}
