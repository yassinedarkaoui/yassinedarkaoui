<?php

namespace App\Http\Controllers\API;

use http\Env\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use App\Model\News;
use App\Model\NewsComment;
use Illuminate\Support\Facades\Auth;
use File;
//use ImageOptimizer;
use Image;
use Exception;


class NewsController extends Controller
{
    public $successStatus = 200;
    public $failedStatus = 401;

    public function allNews(){
        try{
            $news=News::where('active',1)->with('comments')->orderBy('created_at','desc')->get();
            return response()->json(['success' => true,"message"=>'welcome!',"news"=>$news], $this-> successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"message"=>'welcome!',"news"=>$e], $this-> failedStatus);
        }

    }
    public function adminAllNews(){
        try{
            $news=News::with('comments')->orderBy('created_at','desc')->get();
            return response()->json(['success' => true,"message"=>'welcome!',"news"=>$news], $this-> successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"message"=>'welcome!',"news"=>$e], $this-> successStatus);
        }
    }

    public function addNewsComment(Request $request){
        try{
            $comment= new NewsComment();
            $user=Auth::user();
            $comment->news_id=$request->input('news_id');
            $comment->user_name=$user->name;
            $comment->user_email=$user->email;
            $comment->comment=$request->input('comment');
            $comment->save();
            if($user->role==1){
                return $this->adminAllNews();
            }
            return $this->allNews();
        }catch (Exception $e){
            return null;
        }

    }
    public function addGuestNewsComment(Request $request){
        try{
            $comment= new NewsComment();
            if($request->input('news_id')!=null)
                $comment->news_id=$request->input('news_id');
            if($request->input('user_name')!=null)
                $comment->user_name=$request->input('user_name');
            if($request->input('user_email')!=null)
                $comment->user_email=$request->input('user_email');
            $comment->comment=$request->input('comment');
            $comment->save();
            return $this->allNews();
        }catch (Exception $e){
            return null;
        }
    }

    public  function updateNewsActive(Request $request){
        try{
            $news = News::find($request->input('id'));
            $news->active = !$news->active;
            $news->save();
            $news=News::with('comments')->orderBy('created_at','desc')->get();
            return response()->json(['success' => true,"message"=>'welcome!',"news"=>$news], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"message"=>'welcome!',"news"=>$e], $this->failedStatus);
        }

    }

    public  function removeNews(Request $request){
        try{
            $news = News::find($request->input('id'));
            $news->delete();
            $news=News::with('comments')->orderBy('created_at','desc')->get();
            return response()->json(['success' => true,"message"=>'welcome!',"news"=>$news], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"message"=>'welcome!',"news"=>$e], $this->failedStatus);
        }
    }
    public  function updateNews(Request $request){
        try{
            $news = News::find($request->input('id'));
            $news ->text = $request->input('text');
            $news ->title = $request->input('title');
            $image = $_POST['image'];
            $fileName = "news/".$this->quickRandom().".jpg";
            if($image!=""){
                if(explode("/",$news->image)[0]!="default"){
                    $file_path = public_path("uploads/images/".$news->image);
                    if(file_exists($file_path)) {
                        File::delete($file_path);
                    }
                }
                $realImage = base64_decode($image);
                file_put_contents("uploads/images/".$fileName,$realImage);
                //ImageOptimizer::optimize(public_path("uploads/images/".$fileName));
                $news->image=$fileName;
            }
            $news->save();
            $this->imageResize(public_path("uploads/images/".$news->image));
            $news=News::with('comments')->orderBy('created_at','desc')->get();
            return response()->json(['success' => true,"message"=>'welcome',"news"=>$news], $this-> successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"message"=>'welcome',"news"=>$e], $this->failedStatus);
        }
    }
    public  function addNews(Request $request){
        try{
            $news = new News();
            $news ->text = $request->input('text');
            $news ->title = $request->input('title');
            $image = $_POST['image'];
            $fileName = "news/".$this->quickRandom().".jpg";
            $realImage = base64_decode($image);
            file_put_contents("uploads/images/".$fileName,$realImage);
            $news->image=$fileName;
            $news->save();
            $this->imageResize(public_path("uploads/images/".$news->image));
            $allNews=News::with('comments')->orderBy('created_at','desc')->get();
            $message="News Name: ".$news ->title;
            app('App\Http\Controllers\API\NotificationController')->newNewsNotification($message);
            return response()->json(['success' => true,"message"=>'welcome',"news"=>$allNews], $this-> successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"message"=>'welcome',"news"=>$e], $this-> failedStatus);
        }
    }
    public function quickRandom($length = 10)
    {
        $pool = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }
    public function imageResize($path){
        $img = Image::make($path);
        $img->resize(500, null, function ($constraint) {$constraint->aspectRatio();})
            ->save($path);
    }
}

