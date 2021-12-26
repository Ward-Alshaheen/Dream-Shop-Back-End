<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Support\Facades\Validator;

class DiscountController extends Controller
{
    public static function fromArray(array $discounts,$price): array
    {
        return [
            'main_price' => $price,
            'discount1' => $discounts[0]['discount'],
            'remaining_days1' => $discounts[0]['remaining_days'],
            'discount2' => $discounts[1]['discount'],
            'remaining_days2' => $discounts[1]['remaining_days'],
            'discount3' => $discounts[2]['discount'],
            'remaining_days3' => $discounts[2]['remaining_days'],
        ];
    }

    public static function fromJson($discounts): array
    {
        return [
            "main" => $discounts['main_price'],
            'd' => [
                ['remaining_days' => $discounts['remaining_days1'],
                    'discount' => $discounts['discount1']],
                ['remaining_days' => $discounts['remaining_days2'],
                    'discount' => $discounts['discount2']],
                ['remaining_days' => $discounts['remaining_days3'],
                    'discount' => $discounts['discount3']],
            ]
        ];
    }
    public static function discountValidator($discount): bool
    {
        $validator = Validator::make($discount, [
            'main_price'=>'required|regex:/^[0-9]+(\.[0-9][0-9]?)?$/',
            'remaining_days1'=>'required|Integer',
            'discount1'=>'required|regex:/^[0-9]+(\.[0-9][0-9]?)?$/',
            'remaining_days2'=>'required|Integer|numeric|max:'.$discount['remaining_days1'],
            'discount2'=>'required|regex:/^[0-9]+(\.[0-9][0-9]?)?$/|numeric|min:'.$discount['discount1'],
            'remaining_days3'=>'required|Integer|numeric|max:'.$discount['remaining_days2'],
            'discount3'=>'required|regex:/^[0-9]+(\.[0-9][0-9]?)?$/|numeric|min:'.$discount['discount2'],
        ]);
        if ($validator->fails()) {
            return false;
        }
        return true;
    }
    public static function createDiscount(int  $id,$discount)
    {
        $discount['product_id']=$id;
        Discount::create($discount);

    }
    public static function updateDiscount($id,$discount,$price)
    {
        $discount=DiscountController::fromArray($discount,$price);
        if (DiscountController::discountValidator($discount)) {
            $meDiscount = Discount::firstWhere('product_id', $id);
            $meDiscount['main_price'] = $discount['main_price'];
            $meDiscount['discount3'] = $discount['discount3'];
            $meDiscount['discount2'] = $discount['discount2'];
            $meDiscount['discount1'] = $discount['discount1'];
            $meDiscount['remaining_days3'] = $discount['remaining_days3'];
            $meDiscount['remaining_days2'] = $discount['remaining_days2'];
            $meDiscount['remaining_days1'] = $discount['remaining_days1'];
            $meDiscount->save();
            return DiscountController::fromJson($discount);
        }
        return false;
    }
}
