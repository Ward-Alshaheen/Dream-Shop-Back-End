<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Traits\GeneralTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use GeneralTrait;

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $products = Product::all();
        for ($i = 0; $i < count($products); $i++) {
            $products[$i]['images'] = json_decode($products[$i]['images'], true);
            //$products[$i]['expiration_date']=$products[$i]['expiration_date']->format('Y/m/d');
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
            'expiration_date' => 'required|date_equals:date',
            'phone' => 'required|string',
            'price' => 'required',
            'discounts' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->returnError(401, $validator->errors());
        }
        $product['discounts'] = json_decode($product['discounts'],true);
        $product['user_id'] = Auth::id();
        $product['expiration_date'] = date_create(date('Y/m/d', $product['expiration_date']));
        $dateNow = date_create(date('Y/m/d'));
        $diff = date_diff($dateNow, $product['expiration_date']);
        $product['remaining_days'] = $diff->format("%R%a") * 1;
        $product['images'][0] = $this->saveImage($product['image1']);
        for ($i = 2; $request->has('image' . $i); $i++) {
            $product['images'][] = $this->saveImage($product['image' . $i]);
        }
        $product['images'] = json_encode($product['images']);
        $product['discounts'] = ['main' => $product['price'], 'd' => $product['discounts']];
        $product['price']=$this->price($product['discounts'],$product['remaining_days']);
        $product['discounts'] = json_encode($product['discounts']);
        Product::create($product);
        return $this->returnSuccessMessage('Successfully');
    }

    //Save Image
    public function saveImage($image): string
    {
        $newImage = time() . $this->returnCode(20) . $image->getClientOriginalName();
        $image->move('uploads/productImage', $newImage);
        return 'http://127.0.0.1:8000/uploads/productImage/' . $newImage;
    }

    /**
     * Display the specified resource.
     *
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) {
            return $this->returnError(55, 'not found');
        }
        $product['images'] = json_decode($product['images'], true);
        return $this->returnData("product", $product);
    }
    /**
     * Update the specified resource in storage.
     *
     */
    public function update(Request $request, int $id): JsonResponse
    {
        return $this->returnData('1',1);
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
        if (!$product->user['id'] == Auth::id()) {
            return $this->returnError(401, "");
        }
        $product->delete();
        return $this->returnSuccessMessage('Successfully');
    }
}
