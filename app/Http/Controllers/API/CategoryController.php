<?php

namespace App\Http\Controllers\API;
use App\Model\Category;
use App\Model\Configure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use File;
use App\Http\Controllers\API\NotificationController;
use Image;

class CategoryController extends Controller
{
    public $successStatus = 200;
    public $failedStatus = 401;
    public $model;

    public function __construct()
    {
        $this->model = new Category();
    }
    public function allCategories()
    {
        try{
            $allCategory = $this->all(1,0);
            return response()->json(['success' => true, "category" => $allCategory], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false, "category" => $e], $this->failedStatus);
        }
    }
    public function allUserCategories(Request $request)
    {
        try{
            $items = $this->all(Auth::user()->language,Auth::user()->role);
            return response()->json(['success' => true, "category" => $items], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false, "category" => $e], $this->failedStatus);
        }
    }
    public function updateCategoryQuantity(Request $request)
    {
        try {
            $item = Category::find($request->input('id'));
            $item->carton = $item->carton + $request->input('quantity');
            $item->save();
            $items = $this->all(Auth::user()->language,Auth::user()->role);
            return response()->json(['success' => true, "category" => $items], $this->successStatus);
        } catch (Exception $e) {
            return response()->json(['success' => false, "category" => $e], $this->failedStatus);
        }
    }
    public function addCategory(Request $request){
        try{
            $item = new Category();
            $image = $_POST['image'];
            if($image!=""){
                $fileName = "category/".$this->quickRandom().".jpg";
                $realImage = base64_decode($image);
                file_put_contents("uploads/images/".$fileName,$realImage);
                $item->imagefile=$fileName;
            }
            $item->name_en=$request->input('name_en');
            $item->name_ar=$request->input('name_ar');
            $item->name_ku=$request->input('name_ku');
            $item->barcode=$request->input('barcode');
            $item->income_price=$request->input('income_price');
            $item->price_market=$request->input('price_market');
            $item->price_store=$request->input('price_store');
            $item->note=$request->input('note');
            $item->size=$request->input('size');
            $item->carton=$request->input('carton');
            $item->brand=$request->input('brand');
            $item->batch_number=$request->input('batch_number');
            $item->expiry_date=$request->input('expiry_date');
            $item->country=$request->input('country');
            $item->company=$request->input('company');
            $item->fourm=$request->input('fourm');
            $item->save();
            $this->imageResize(public_path("uploads/images/".$item->imagefile));
            $items = $this->all(Auth::user()->language,1);
            $message="Name: ".$item->name_en.", Price:".$item->price_market;
            app('App\Http\Controllers\API\NotificationController')->newItemNotification($message);
            return response()->json(['success' => true, "category" => $items], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false, "category" => $e], $this->failedStatus);
        }
    }
    public function updateCategory(Request $request){
        try{
            $item = Category::find($request->input('id'));
            $image = $_POST['image'];
            if($image!=""){
                if(explode("/",$item->imagefile)[0]!="default"){
                    $file_path = public_path("uploads/images/".$item->imagefile); // app_path("public/test.txt");
                    if(file_exists($file_path)) {
                        File::delete($file_path);
                    }
                }
                $fileName = "category/".$this->quickRandom().".jpg";
                $realImage = base64_decode($image);
                file_put_contents("uploads/images/".$fileName,$realImage);
                $item->imagefile=$fileName;
            }
            $item->name_en=$request->input('name_en');
            $item->name_ar=$request->input('name_ar');
            $item->name_ku=$request->input('name_ku');
            $item->barcode=$request->input('barcode');
            $item->income_price=$request->input('income_price');
            $item->price_market=$request->input('price_market');
            $item->price_store=$request->input('price_store');
            $item->note=$request->input('note');
            $item->size=$request->input('size');
            $item->carton=$request->input('carton');
            $item->brand=$request->input('brand');
            $item->batch_number=$request->input('batch_number');
            $item->expiry_date=$request->input('expiry_date');
            $item->active=$request->input('active');
            if(Auth::user()->language==1){
                $country = Configure::where('en_name',$request->input('country'))->first();
                $item->country=$country->id;
                $company = Configure::where('en_name',$request->input('company'))->first();
                $item->company=$company->id;
                $fourm = Configure::where('en_name',$request->input('fourm'))->first();
                $item->fourm=$fourm->id;
            }elseif (Auth::user()->language==2){
                $country = Configure::where('ar_name',$request->input('country'))->first();
                $item->country=$country->id;
                $company = Configure::where('ar_name',$request->input('company'))->first();
                $item->company=$company->id;
                $fourm = Configure::where('ar_name',$request->input('fourm'))->first();
                $item->fourm=$fourm->id;
            }elseif (Auth::user()->language==3){
                $country = Configure::where('ku_name',$request->input('country'))->first();
                $item->country=$country->id;
                $company = Configure::where('ku_name',$request->input('company'))->first();
                $item->company=$company->id;
                $fourm = Configure::where('ku_name',$request->input('fourm'))->first();
                $item->fourm=$fourm->id;
            }
            $item->save();
            $this->imageResize(public_path("uploads/images/".$item->imagefile));
            $items = $this->all(Auth::user()->language,1);
            $message="Name: ".$item->name_en.", Price:".$item->price_market;
            app('App\Http\Controllers\API\NotificationController')->updateItemNotification($message);
            return response()->json(['success' => true, "category" => $items], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false, "category" => $e], $this->failedStatus);
        }
    }
    public function all($language,$role)
    {
        if($role == 1){
            $allCategorys = $this->model->with('getCountry','getCompany','getFourm')->orderBy('created_at','desc')->get();
        }else{
            $allCategorys = $this->model->where('active',1)->with('getCountry','getCompany','getFourm')->orderBy('created_at','desc')->get();
        }
        foreach ($allCategorys as $category){
            if($language==1){
                $category->name=$category->name_en;
                $category->country=$category->getCountry->en_name;
                $category->company=$category->getCompany->en_name;
                $category->fourm=$category->getFourm->en_name;
            }elseif ($language==2){
                $category->name=$category->name_ar;
                $category->country=$category->getCountry->ar_name;
                $category->company=$category->getCompany->ar_name;
                $category->fourm=$category->getFourm->ar_name;
            }elseif ($language==3){
                $category->name=$category->name_ku;
                $category->country=$category->getCountry->ku_name;
                $category->company=$category->getCompany->ku_name;
                $category->fourm=$category->getFourm->ku_name;
            }
        }
        return $allCategorys;
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

