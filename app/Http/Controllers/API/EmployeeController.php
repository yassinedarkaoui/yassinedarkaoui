<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Employee;
use Exception;
use App\User;

class EmployeeController extends Controller
{
    public $successStatus = 200;
    public $failedStatus = 401;

    public function getAllEmployee(Request $request){
        try{
            $employee = $this->allEmployee($request);
            return response()->json(['success' => true,"employee"=>$employee], $this-> successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"employee"=>$e], $this->failedStatus);
        }
    }

    function allEmployee(Request $request){
        return $employee = Employee::with('employeeUser')->orderBy('created_at','desc')->get();
    }

}
