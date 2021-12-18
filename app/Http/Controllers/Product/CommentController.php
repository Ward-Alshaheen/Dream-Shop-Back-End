<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Product;
use App\Traits\GeneralTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    use GeneralTrait;

    /**
     * @throws Exception
     */
    public function all(int $id): JsonResponse
    {
        $comments=Comment::where('product_id',$id)->get();
        foreach ($comments as $comment){
            $comment->user;
            $comment['time']=$this->timeAgo($comment['created_at']);
        }
        return $this->returnData('comments',$comments);
    }
    public function add(Request $request,int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->returnError(401, $validator->errors());
        }
        if(!Product::find($id)){
            return $this->returnError(401,"product not found");
        }
        Comment::create([
            "user_id"=>Auth::id(),
            'text'=>$request->all()['text'],
            "product_id"=>$id
        ]);
        return $this->returnSuccessMessage("Successfully");
    }
    public function delete(int $id): JsonResponse
    {
        $comment=Comment::find($id);
        if (!$comment){
           return $this->returnError(401,"not found");
        }
        if ($comment->user['id']!=Auth::id()){
           return $this->returnError(401,"error");
        }
        $comment->delete();
        return $this->returnSuccessMessage("Successfully");
    }

    public function update(Request $request, int  $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->returnError(401, $validator->errors());
        }
        $comment=Comment::find($id);
        if (!$comment){
            return $this->returnError(401,"not found");
        }
        if ($comment->user['id']!=Auth::id()){
            return $this->returnError(401,"error");
        }
        $comment['text']=$request->all()['text'];
        $comment['edit']=true;
        $comment->save();
        return $this->returnSuccessMessage("Successfully");
    }
    public function timeAgo($time_ago): string
    {
        $time_ago = strtotime($time_ago);
        $cur_time   = time();
        $time_elapsed   = $cur_time - $time_ago;
        $seconds    = $time_elapsed ;
        $minutes    = round($time_elapsed / 60 );
        $hours      = round($time_elapsed / 3600);
        $days       = round($time_elapsed / 86400 );
        $weeks      = round($time_elapsed / 604800);
        $months     = round($time_elapsed / 2600640 );
        $years      = round($time_elapsed / 31207680 );
        // Seconds
        if($seconds <= 60){
            return "just now";
        }
        //Minutes
        else if($minutes <=60){
            if($minutes==1){
                return "one minute ago";
            }
            else{
                return "$minutes minutes ago";
            }
        }
        //Hours
        else if($hours <=24){
            if($hours==1){
                return "an hour ago";
            }else{
                return "$hours hrs ago";
            }
        }
        //Days
        else if($days <= 7){
            if($days==1){
                return "yesterday";
            }else{
                return "$days days ago";
            }
        }
        //Weeks
        else if($weeks <= 4.3){
            if($weeks==1){
                return "a week ago";
            }else{
                return "$weeks weeks ago";
            }
        }
        //Months
        else if($months <=12){
            if($months==1){
                return "a month ago";
            }else{
                return "$months months ago";
            }
        }
        //Years
        else{
            if($years==1){
                return "one year ago";
            }else{
                return "$years years ago";
            }
        }
    }
}
