<?php

namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\CategoryReport;
use App\Model\Category;
use App\Model\PharmacyReport;
use App\Model\Pharmacy;
use App\Model\NewsReport;
use App\Model\News;
use Illuminate\Support\Facades\Auth;
use App\User;
use Exception;

class ReportController extends Controller
{
    public $successStatus = 200;
    public $failedStatus = 401;

    public function getCategoryReport(){
        try{
            $reports = CategoryReport::with('user')->orderBy('created_at','desc')->get();
            return response()->json(['success' => true, "categoryReport" => $reports], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false, "categoryReport" => $e], $this->failedStatus);
        }
    }
    public function addCategoryReport(Request $request){
        try{
            $user = Auth::user();
            $report = new CategoryReport();
            $report->user=$user->id;
            $report ->category = $request->input('category_id');
            $report ->report = $request->input('report');
            $report ->rate = $request->input('rate');
            $report->save();
            $this->updateCategoryRate($request->input('category_id'));
            $items = $this->allCategory($user->language);
            $item = Category::find($request->input('category_id'))->name_en;
            app('App\Http\Controllers\API\NotificationController')->reportItemNotification($user->name,$item);
            return response()->json(['success' => true, "category" => $items], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false, "category" => $e], $this->failedStatus);
        }
    }
    public function addGuestCategoryReport(Request $request){
        try{
            $report = new CategoryReport();
            $report ->category = $request->input('category_id');
            $report ->report = $request->input('report');
            $report ->rate = $request->input('rate');
            $report->save();
            $this->updateCategoryRate($request->input('category_id'));
            $items = $this->allCategory(1);
            $item = Category::find($request->input('category_id'))->name_en;
            app('App\Http\Controllers\API\NotificationController')->reportItemNotification("Guest User",$item);
            return response()->json(['success' => true, "category" => $items], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false, "category" => $e], $this->failedStatus);
        }

    }
    function updateCategoryRate(int $id){
        try{
            $rate = CategoryReport::where('category',$id)->pluck('rate')->avg();
            $category = Category::find($id);
            $category->rate=$rate;
            $category->save();
        }catch (Exception $e){
            return null;
        }

    }
    public function getPharmacyReport(){
        try{
            $reports = PharmacyReport::with('user')->orderBy('created_at','desc')->get();
            return response()->json(['success' => true, "pharmacyReport" => $reports], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false, "pharmacyReport" => $e], $this->failedStatus);
        }
    }
    public function addPharmacyReport(Request $request){
        try{
            $user = Auth::user();
            $report = new PharmacyReport();
            $report->user=$user->id;
            $report ->pharmacy = $request->input('pharmacy_id');
            $report ->report = $request->input('report');
            $report ->rate = $request->input('rate');
            $report->save();
            $this->updatePharmacyRate($request->input('pharmacy_id'));
            $pharmacies = Pharmacy::orderBy('created_at','desc')->get();
            $to = Pharmacy::find($request->input('pharmacy_id'))->user_id;
            $to = User::find($to);
            app('App\Http\Controllers\API\NotificationController')->reportPharmacyNotification(Auth::user()->name,$to,$report ->report,$report ->rate);
            return response()->json(['success' => true, "pharmacy" => $pharmacies], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false, "pharmacy" => $e], $this->failedStatus);
        }
    }
    public function addGuestPharmacyReport(Request $request){
        try{
            $report = new PharmacyReport();
            $report ->pharmacy = $request->input('pharmacy_id');
            $report ->report = $request->input('report');
            $report ->rate = $request->input('rate');
            $report->save();
            $this->updatePharmacyRate($request->input('pharmacy_id'));
            $items = Pharmacy::orderBy('created_at','desc')->get();
            $to = Pharmacy::find($request->input('pharmacy_id'))->user_id;
            $to = User::find($to);
            app('App\Http\Controllers\API\NotificationController')->reportPharmacyNotification("Guest User",$to,$report ->report,$report ->rate);
            return response()->json(['success' => true, "pharmacy" => $items], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false, "pharmacy" => $e], $this->failedStatus);
        }
    }
    function updatePharmacyRate(int $id){
        try{
            $rate = PharmacyReport::where('pharmacy',$id)->pluck('rate')->avg();
            $pharmacy = Pharmacy::find($id);
            $pharmacy->rate=$rate;
            $pharmacy->save();
        }catch (Exception $e){
            return null;
        }
    }
    public function getNewsReport(){
        try{
            $reports = NewsReport::with('user')->orderBy('created_at','desc')->get();
            return response()->json(['success' => true, "newsReports" => $reports], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false, "newsReports" => $e], $this->failedStatus);
        }
    }
    public function addNewsReport(Request $request){
        try{
            $user = Auth::user();
            $report = new NewsReport();
            $report->user=$user->id;
            $report ->news = $request->input('news_id');
            $report ->report = $request->input('report');
            $report ->rate = $request->input('rate');
            $report->save();
            $this->updateNewsRate($request->input('news_id'));
            $items = News::where('active',1)->with('comments')->orderBy('created_at','desc')->get();
            $newsTitle = News::find($request->input('news_id'))->title;
            app('App\Http\Controllers\API\NotificationController')->reportNewsNotification(Auth::user()->name,$newsTitle);
            return response()->json(['success' => true, "news" => $items], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false, "news" => $e], $this->failedStatus);
        }
    }
    public function addGuestNewsReport(Request $request){
        try{
            $report = new NewsReport();
            $report ->news = $request->input('news_id');
            $report ->report = $request->input('report');
            $report ->rate = $request->input('rate');
            $report->save();
            $this->updateNewsRate($request->input('news_id'));
            $items = News::where('active',1)->with('comments')->orderBy('created_at','desc')->get();
            $newsTitle = News::find($request->input('news_id'))->title;
            app('App\Http\Controllers\API\NotificationController')->reportNewsNotification("Guest User",$newsTitle);
            return response()->json(['success' => true, "news" => $items], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false, "news" => $e], $this->failedStatus);
        }
    }
    function updateNewsRate(int $id){
        try{
            $rate = NewsReport::where('news',$id)->pluck('rate')->avg();
            $news = News::find($id);
            $news->rate=$rate;
            $news->save();
        }catch (Exception $e){
            return null;
        }
    }
    public function allCategory($language){
        try{
            $allCategorys = Category::where('active',1)->with('getCountry','getCompany','getFourm')->orderBy('created_at','desc')->get();
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
        }catch(Exception $e){
            return $e;
        }
    }
}
