<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\Request;

class ImageControllers extends Controller
{
    public static function createImage(string $url,int $id){
        Image::create([
            "url"=>$url,
            'product_id'=>$id
        ]);
    }
    public static function getImages($id): array
    {
        $images=Image::where('product_id',$id)->get();
        $i=[];
        foreach ($images as $image){
            $i[]=$image['url'];
        }
        return $i;
    }
    public static function updateImage(int $id,int $index,string  $url){
        $images=Image::where('product_id',$id)->get();
        $images[$index]['url']=$url;
        $images[$index]->save();
    }
}
