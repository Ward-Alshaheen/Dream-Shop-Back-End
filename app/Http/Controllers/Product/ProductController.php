<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Image;
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
        if ($this->descSort($request)) {
            $products = $this->productQuery($request->header('sort'), true);
        } elseif ($this->isSort($request)) {
            $products = $this->productQuery($request->header('sort'), false);
        } else {
            $products = $this->productQuery('remaining_days', false);
        }
        $products = $this->getProducts($products->get());
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
        $product['discounts'] = json_decode($product['discounts'], true);
        $product['user_id'] = Auth::id();
        $product['expiration_date'] = date_create(date('Y/m/d', strtotime($product['expiration_date'])));
        $dateNow = date_create(date('Y/m/d'));
        $diff = date_diff($dateNow, $product['expiration_date']);
        $product['remaining_days'] = $diff->format("%R%a") * 1;
        $product['discounts'] = ['main' => $product['price'] * 1, 'd' => $product['discounts']];
        $product['price'] = $this->price($product['discounts'], $product['remaining_days']);
        $product['discounts'] = json_encode($product['discounts']);
        $newProduct = Product::create($product);
        ImageControllers::createImage($this->saveImage($product['image1'], 'productImage'), $newProduct['id']);
        for ($i = 2; $request->has('image' . $i); $i++) {
            $validator = Validator::make($product, [
                'image' . $i => 'required|image',
            ]);
            if ($validator->fails()) {
                return $this->returnError(401, $validator->errors());
            }
            ImageControllers::createImage($this->saveImage($product['image' . $i], 'productImage'), $newProduct['id']);
        }
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
        if ($this->descSort($request)) {
            $products = $this->productQuery($request->header('sort'), true);
        } elseif ($this->isSort($request)) {
            $products = $this->productQuery($request->header('sort'), false);
        } else {
            $products = $this->productQuery('remaining_days', false);
        }
        $products = $this->getProducts($products->where('category', $category['category'])->get());
        return $this->returnData('products', $products);
    }

    /**
     * Update the specified resource in storage.
     *
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::withCount('images')->find($id);
        if (!$product) {
            return $this->returnError(55, 'not found');
        }
        if ($product->user['id'] != Auth::id()) {
            return $this->returnError(401, "");
        }
        $productUpdate = $request->all();
        $validator = Validator::make($productUpdate, [
            'price' => 'required|regex:/^[0-9]+(\.[0-9][0-9]?)?$/',
            'discounts' => 'required|json',
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
        $c = $product['images_count'];
        $images=ImageControllers::getImages($product['id']);
        for ($i = 1; $c >= $i || $request->has('image' . $i); $i++) {
            $validator = Validator::make($productUpdate, [
                'image' . $i => 'image',
            ]);
            if ($validator->fails()) {
                return $this->returnError(401, $validator->errors());
            }
            if ($request->has('image' . $i)) {
                if ($c >= $i) {
                    unlink(substr($images[$i - 1], strlen(URL::to('/')) + 1));
                    ImageControllers::updateImage($product['id'],$i-1,$this->saveImage($productUpdate['image' . $i], 'productImage'));
                    continue;
                }
                ImageControllers::createImage($this->saveImage($productUpdate['image' . $i], 'productImage'), $product['id']);
            }
        }
        $productUpdate['discounts'] = json_decode($productUpdate['discounts'], true);
        $productUpdate['discounts'] = ['main' => $productUpdate['price'] * 1, 'd' => $productUpdate['discounts']];
        $product['price'] = $this->price($productUpdate['discounts'], $product['remaining_days']);
        $product['discounts'] = json_encode($productUpdate['discounts']);
        $product['name'] = $productUpdate['name'];
        $product['description'] = $productUpdate['description'];
        $product['category'] = $productUpdate['category'];
        $product['phone'] = $productUpdate['phone'];
        $product['quantity'] = $productUpdate['quantity'] * 1;

        if ($request->has('facebook')) {
            $product['facebook'] = $productUpdate['facebook'];
        }
        $product->save();
        return $this->returnSuccessMessage("Successfully");
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
        $im = $product->images;
        foreach ($im as $item) {
            unlink(substr($item['url'], strlen(URL::to('/')) + 1));
        }
        $product->delete();
        return $this->returnSuccessMessage('Successfully');
    }

    //My Product
    public function myProduct(Request $request): JsonResponse
    {
        if ($this->descSort($request)) {
            $products = $this->productQuery($request->header('sort'), true);
        } elseif ($this->isSort($request)) {
            $products = $this->productQuery($request->header('sort'), false);
        } else {
            $products = $this->productQuery('remaining_days', false);
        }
        $products = $this->getProducts($products->where('user_id', Auth::id())->get());
        return $this->returnData('products', $products);
    }

    //products user
    public function productUser(Request $request, int $id): JsonResponse
    {
        if ($this->descSort($request)) {
            $products = $this->productQuery($request->header('sort'), true);
        } elseif ($this->isSort($request)) {
            $products = $this->productQuery($request->header('sort'), false);
        } else {
            $products = $this->productQuery('remaining_days', false);
        }
        $products = $this->getProducts($products->where('user_id', $id)->get());
        return $this->returnData('products', $products);
    }
}
