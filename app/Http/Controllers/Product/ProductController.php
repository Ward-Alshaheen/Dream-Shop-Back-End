<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Traits\GeneralTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use GeneralTrait;

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
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
                $request->header('sort') != 'likes_count'&&
                $request->header('sort') != 'views_count') {
                return $this->returnError(401, 'error sort');
            }
            if ($request->header('desc')=="true") {
                $products = Product::with('user')
                    ->withCount('likes')
                    ->withCount('views')
                    ->orderByDesc($request->header('sort'))
                    ->get();
            } else {
                $products = Product::with('user')
                    ->withCount('likes')
                    ->withCount('views')
                    ->orderBy($request->header('sort'))
                    ->get();
            }
        } else {
            $products = Product::with('user')
                ->withCount('likes')
                ->withCount('views')
                ->orderBy('remaining_days')
                ->get();
        }
        for ($i = 0; $i < count($products); $i++) {
            $products[$i]['images'] = json_decode($products[$i]['images'], true);
            $products[$i]['me_likes'] = LikeController::meLike($products[$i]['id']);
            $products[$i]['discounts'] = json_decode($products[$i]['discounts'], true);
        }
        return $this->returnData('products', $products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $product = $request->all();
        $validator = Validator::make($product, [
            'name' => 'required|string',
            'image1' => 'required|image',
            'description' => 'required|string',
            'category' => 'required|string',
            'expiration_date' => 'required|date',
            'phone' => 'required|string',
            'price' => 'required|regex:/^[0-9]+(\.[0-9][0-9]?)?$/',
            'discounts' => 'required|json',
            'quantity' => 'regex:/^[0-9]+(\.[0-9][0-9]?)?$/',
            'facebook' => 'URL'
        ]);
        if ($validator->fails()) {
            return $this->returnError(401, $validator->errors());
        }
        $product['images'][0] = $this->saveImage($product['image1'], 'productImage');
        for ($i = 2; $request->has('image' . $i); $i++) {
            $validator = Validator::make($product, [
                'image' . $i => 'required|image',
            ]);
            if ($validator->fails()) {
                return $this->returnError(401, $validator->errors());
            }
            $product['images'][] = $this->saveImage($product['image' . $i], 'productImage');
        }
        $product['discounts'] = json_decode($product['discounts'], true);
        $product['user_id'] = Auth::id();
        $product['expiration_date'] = date_create(date('Y/m/d', strtotime($product['expiration_date'])));
        $dateNow = date_create(date('Y/m/d'));
        $diff = date_diff($dateNow, $product['expiration_date']);
        $product['remaining_days'] = $diff->format("%R%a") * 1;
        $product['images'] = json_encode($product['images']);
        $product['discounts'] = ['main' => $product['price']*1, 'd' => $product['discounts']];
        $product['price'] = $this->price($product['discounts'], $product['remaining_days']);
        $product['discounts'] = json_encode($product['discounts']);
        Product::create($product);
        return $this->returnSuccessMessage('Successfully');
    }

    /**
     * Display the specified resource.
     *
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with('user')->withCount('likes')->withCount('views')->find($id);
        if (!$product) {
            return $this->returnError(55, 'not found');
        }
        $product['images'] = json_decode($product['images'], true);
        $product['me_likes'] = LikeController::meLike($product['id']);
        return $this->returnData("product", $product);
    }

    //Show Category
    public function showCategory(Request $request): JsonResponse
    {
        $category = $request->all();
        $validator = Validator::make($category, [
            'category' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->returnError(401, $validator->errors());
        }
        if ($request->hasHeader('sort')) {
            if (
                $request->header('sort') != 'created_at' &&
                $request->header('sort') != 'price' &&
                $request->header('sort') != 'name' &&
                $request->header('sort') != 'expiration_date' &&
                $request->header('sort') != 'remaining_days' &&
                $request->header('sort') != 'category' &&
                $request->header('sort') != 'quantity' &&
                $request->header('sort') != 'likes_count'&&
                $request->header('sort') != 'views_count') {
                return $this->returnError(401, 'error sort');
            }
            if ($request->header('desc')=="true") {
                $products = Product::with('user')
                    ->withCount('likes')
                    ->withCount('views')
                    ->where('category', $category['category'])
                    ->orderByDesc($request->header('sort'))
                    ->get();
            } else {
                $products = Product::with('user')
                    ->withCount('likes')
                    ->withCount('views')
                    ->where('category', $category['category'])
                    ->orderBy($request->header('sort'))
                    ->get();
            }
        } else {
            $products = Product::with('user')
                ->withCount('likes')
                ->withCount('views')
                ->where('category', $category['category'])
                ->orderBy('remaining_days')->get();
        }
        if (count($products) == 0) {
            return $this->returnError(401, 'not fond');
        }
        for ($i = 0; $i < count($products); $i++) {
            $products[$i]['images'] = json_decode($products[$i]['images'], true);
            $products[$i]['me_likes'] = LikeController::meLike($products[$i]['id']);
        }
        return $this->returnData('products', $products);
    }

    /**
     * Update the specified resource in storage.
     *
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) {
            return $this->returnError(55, 'not found');
        }
        if ($product->user['id'] != Auth::id()) {
            return $this->returnError(401, "");
        }
        $product['images'] = json_decode($product['images'], true);

        $productUpdate = $request->all();
        $productUpdate['images'] = $product['images'];
        $validator = Validator::make($productUpdate, [
            'price'=>'required|regex:/^[0-9]+(\.[0-9][0-9]?)?$/',
            'discounts'=>'required|json',
            'name' => 'required|string',
            'description' => 'required|string',
            'category' => 'required|string',
            'phone' => 'required|string',
            'quantity' => 'required|Integer',
            'facebook' => 'URL'
        ]);
        if ($validator->fails()) {
            return $this->returnError(401, $validator->errors());
        }
        $c = count($product['images']);
        for ($i = 1; $c >= $i || $request->has('image' . $i); $i++) {
            $validator = Validator::make($productUpdate, [
                'image' . $i => 'image',
            ]);
            if ($validator->fails()) {
                return $this->returnError(401, $validator->errors());
            }
            if ($request->has('image' . $i)) {
                if ($c >= $i) {
                    unlink(substr($product['images'][$i - 1], strlen(URL::to('/')) + 1));
                    $productUpdate['images'][$i - 1] = $this->saveImage($productUpdate['image' . $i], 'productImage');
                    continue;
                }
                $productUpdate['images'][] = $this->saveImage($productUpdate['image' . $i], 'productImage');
            }
        }
        $productUpdate['discounts']=json_decode($productUpdate['discounts'],true);
        $productUpdate['discounts'] = ['main' => $productUpdate['price']*1, 'd' => $productUpdate['discounts']];
        $product['price']=$this->price($productUpdate['discounts'],$product['remaining_days']);
        $product['discounts'] =json_encode($productUpdate['discounts']) ;
        $product['images'] = json_encode($productUpdate['images']);
        $product['name'] = $productUpdate['name'];
        $product['description'] = $productUpdate['description'];
        $product['category'] = $productUpdate['category'];
        $product['phone'] = $productUpdate['phone'];
        $product['quantity'] = $productUpdate['quantity'] * 1;
        
        if ($request->has('facebook')) {
            $product['facebook'] = $productUpdate['facebook'];
        }
        $product->save();
        $product['images'] = json_decode($product['images'], true);
        $product->user = null;
        $product['user'] = null;
        return $this->returnData("product", $product, "Successfully");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) {
            return $this->returnError(55, 'not found');
        }
        if ($product->user['id'] != Auth::id()) {
            return $this->returnError(401, "");
        }
        $im = json_decode($product['images'], true);
        foreach ($im as $item) {
            unlink(substr($item, strlen(URL::to('/')) + 1));
        }
        $product->delete();
        return $this->returnSuccessMessage('Successfully');
    }

    //My Product
    public function myProduct(Request $request): JsonResponse
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
                $request->header('sort') != 'likes_count'&&
                $request->header('sort') != 'views_count') {
                return $this->returnError(401, 'error sort');
            }
            if($request->header('desc')=="true") {
                $products = Product::with('user')
                    ->withCount('likes')
                    ->withCount('views')
                    ->where('user_id', Auth::id())
                    ->orderByDesc($request->header('sort'))
                    ->get();
            } else {
                $products = Product::with('user')
                    ->withCount('likes')
                    ->withCount('views')
                    ->where('user_id', Auth::id())
                    ->orderBy($request->header('sort'))
                    ->get();
            }
        } else {
            $products = Product::with('user')
                ->withCount('likes')
                ->withCount('views')
                ->where('user_id', Auth::id())
                ->orderBy('remaining_days')
                ->get();
        }
        for ($i = 0; $i < count($products); $i++) {
            $products[$i]['images'] = json_decode($products[$i]['images'], true);
            $products[$i]['me_likes'] = LikeController::meLike($products[$i]['id']);
            $products[$i]['discounts'] = json_decode($products[$i]['discounts'], true);
        }
        return $this->returnData('products', $products);
    }

    //products user
    public function productUser(Request $request, int $id): JsonResponse
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
                $request->header('sort') != 'likes_count'&&
                $request->header('sort') != 'views_count') {
                return $this->returnError(401, 'error sort');
            }
            if ($request->header('desc')=="true") {
                $products = Product::with('user')
                    ->withCount('likes')
                    ->withCount('views')
                    ->where('user_id', $id)
                    ->orderByDesc($request->header('sort'))
                    ->get();
            } else {
                $products = Product::with('user')
                    ->withCount('likes')
                    ->withCount('views')
                    ->where('user_id', $id)
                    ->orderBy($request->header('sort'))
                    ->get();
            }
        } else {
            $products = Product::with('user')
                ->withCount('likes')
                ->withCount('views')
                ->where('user_id', $id)
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
