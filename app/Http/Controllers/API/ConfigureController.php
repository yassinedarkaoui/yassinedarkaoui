<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Configure;
use Exception;

class ConfigureController extends Controller
{
    public $successStatus = 200;
    public $failedStatus = 401;
    public function allConfigure(){
        try{
            $configure = Configure::orderBy('created_at','desc')->get();
            return response()->json(['success' => true,"configure"=>$configure], $this-> successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"configure"=>$e], $this->failedStatus);
        }
    }
    public function addConfigure(Request $request){
        try{
            $configure = new Configure();
            $configure->en_name=$request->input('en_name');
            $configure->ar_name=$request->input('ar_name');
            $configure->ku_name=$request->input('ku_name');
            $configure->type=$request->input('type');
            $configure->save();
            $configure = Configure::orderBy('created_at','desc')->get();
            return response()->json(['success' => true,"configure"=>$configure], $this-> successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"configure"=>$e], $this-> failedStatus);
        }
    }
    public function updateConfigure(Request $request){
        try{
            $configure = Configure::find($request->input('id'));
            $configure->en_name=$request->input('en_name');
            $configure->ar_name=$request->input('ar_name');
            $configure->ku_name=$request->input('ku_name');
            $configure->type=$request->input('type');
            $configure->save();
            $configure = Configure::orderBy('created_at','desc')->get();
            return response()->json(['success' => true,"configure"=>$configure], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"configure"=>$e], $this->failedStatus);
        }
    }
}
