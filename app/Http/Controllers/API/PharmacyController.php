<?php

namespace App\Http\Controllers\API;
use App\Model\Pharmacy;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;

class PharmacyController extends Controller
{
    public $successStatus = 200;
    public $failedStatus = 401;

    public function allPharmacies(){
        try{
            $allPharmacys= Pharmacy::orderBy('created_at','desc')->get();
            return response()->json(['success' => true,"message"=>null,"pharmacy"=>$allPharmacys,], $this-> successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"message"=>null,"pharmacy"=>$e,], $this->failedStatus);
        }
    }
}
