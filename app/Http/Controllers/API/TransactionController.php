<?php

namespace App\Http\Controllers\API;

use App\Model\Cart;
use App\Model\Category;
use App\Model\Pharmacy;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Transaction;
use App\Model\TransactionCategory;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\User;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class TransactionController extends Controller
{
    public $successStatus = 200;
    public $failedStatus = 401;
    public function allTransactions(){
        try{
            $transactions=$this->getAllTransaction();
            return response()->json(['success' => true,"transaction"=>$transactions], $this-> successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"transaction"=>$e], $this-> failedStatus);
        }
    }

    public function addTransaction(Request $request){
        try{
            $categories = json_decode($request->input('categories'),true);
            $orderNumber = $this->quickRandom(10);//Str::random();
            $transaction = new Transaction();
            $transaction->user_id=$request->input('user_id');
            $transaction->title=$request->input('title');
            $transaction->order_number=$orderNumber;
            $transaction->save();
            foreach ($categories as $category){
                $transactionCategory = new TransactionCategory();
                $transactionCategory->order_number = $orderNumber;
                $transactionCategory->category_id = $category['category_id'];
                $transactionCategory->quantity = $category['quantity'];
                $transactionCategory->save();
            }
            $transactions = $this->getAllTransaction();
            $cart = Cart::where('user_id',$request->input('user_id'))->get();
            if(count($cart)>0){
                $cart->each(function ($item) {
                    $item->delete();
                });
            }
            $this->transactionRealTimeData(Auth::user()->id);
            $to=Pharmacy::where('user_id',Auth::user()->id)->first()->employee;
            $to = User::find($to)->remember_token;
            $from = Auth::user()->name;
            app('App\Http\Controllers\API\NotificationController')->newOrderNotification($to,$from,$orderNumber);
            return response()->json(['success' => true,"transaction"=>$transactions], $this-> successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"transaction"=>$e], $this-> failedStatus);
        }
    }
    public function rejectTransaction(Request $request){
        try{
            $transaction = Transaction::where('user_id', $request->input('user_id'))->where('order_number', $request->input('order_number'))->first();
            $transaction->status = "Rejected";
            $transaction->save();
            $transactions = $this->getAllTransaction();
            $from=Auth::user();
            $to=User::find($from->employee);
            app('App\Http\Controllers\API\NotificationController')->updateOrderNotification($to,$from->name,$request->input('order_number'));
            return response()->json(['success' => true, "transaction" => $transactions], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"transaction"=>$e], $this-> failedStatus);
        }
    }
    public function removeTransactionCategory(Request $request){
        try{
            $transaction = Transaction::where('user_id', $request->input('user_id'))->where('order_number', $request->input('order_number'))->first();
            $transactionCategory=$transaction->transactionCategory->where('category_id', $request->input('category_id'))->first();
            if($transactionCategory!=null){
                $transactionCategory->delete();
                $transaction = Transaction::where('user_id', $request->input('user_id'))->where('order_number', $request->input('order_number'))->first();
                if(count($transaction->transactionCategory)==0)
                {
                    $transaction->status='Rejected';
                }else{
                    $transaction->status='Changed';
                }
                $transaction->save();
            }
            $transactions = $this->getAllTransaction();
            $from=Auth::user();
            $to=User::find($from->employee);
            app('App\Http\Controllers\API\NotificationController')->updateOrderNotification($to,$from->name,$request->input('order_number'));
            return response()->json(['success' => true, "transaction" =>$transactions], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"transaction"=>$e], $this-> failedStatus);
        }
    }
    public function updateTransactionCategory(Request $request){
        try{
            $transaction = Transaction::where('user_id', $request->input('user_id'))->where('order_number', $request->input('order_number'))->first();
            $transactionCategory=$transaction->transactionCategory->where('category_id', $request->input('category_id'))->first();
            if($transactionCategory!=null){
                $transactionCategory->quantity = $request->input('quantity');
                $transactionCategory->save();
                $transaction->status='Changed';
                $transaction->save();
            }
            $transactions = $this->getAllTransaction();
            $from=Auth::user();
            $to=User::find($from->employee);
            app('App\Http\Controllers\API\NotificationController')->updateOrderNotification($to,$from->name,$request->input('order_number'));
            return response()->json(['success' => true, "transaction" =>$transactions], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"transaction"=>$e], $this-> failedStatus);
        }
    }
    function getAllTransaction(){
        try{
            $this->transactionRealTimeData(Auth::user()->id);
            $this->requestRealTimeData();
            return Transaction::where('user_id',Auth::user()->id)->with('transactionCategory')->orderBy('created_at','desc')->get();
        }catch (Exception $e){
            return null;
        }
    }
    public function getAllRequest(Request $request){
        try{
            $employeeRequest = Transaction::with('transactionCategory')->with('user')->orderBy('created_at','desc')->get();
            $this->requestRealTimeData();
            return response()->json(['success' => true, "request" =>$employeeRequest], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"request"=>$e], $this-> failedStatus);
        }
    }
    public  function updateRequestStatus(Request $request){
        try{
            $employeeRequest = Transaction::find($request->input('id'));
            $employeeRequest->status=$request->input('status');
            $employeeRequest->save();
            if($request->input('status')=="Accepted"){
                $transactionCategory = TransactionCategory::where('order_number',$employeeRequest->order_number)->first();
                $category = Category::find($transactionCategory->category_id);
                $category->carton = $category->carton-$transactionCategory->quantity;
                $category->save();
            }
            if($request->input('status')=="Rejected"){
                $transactionCategory = TransactionCategory::where('order_number',$employeeRequest->order_number)->first();
                $category = Category::find($transactionCategory->category_id);
                $category->carton = $category->carton+$transactionCategory->quantity;
                $category->save();
            }
            $employeeRequests = Transaction::with('transactionCategory')->with('user')->orderBy('created_at','desc')->get();
            $this->requestRealTimeData();
            $this->transactionRealTimeData($employeeRequest->user_id);
            $from=Auth::user();
            $to=User::find($employeeRequest->user_id);
            app('App\Http\Controllers\API\NotificationController')->updateOrderNotification($to,$from->name,$employeeRequest->order_number);
            return response()->json(['success' => true, "request" =>$employeeRequests], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"request"=>$e], $this-> failedStatus);
        }
    }
    public function saveRequestNote(Request $request){
        try{
            $transaction = Transaction::find($request->input('id'));
            $transaction->note = $request->input('note');
            $transaction->save();
            $employeeRequests = Transaction::with('transactionCategory')->with('user')->orderBy('created_at','desc')->get();
            $this->requestRealTimeData();
            return response()->json(['success' => true, "request" =>$employeeRequests], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"request"=>$e], $this-> failedStatus);
        }
    }
    public function quickRandom($length = 10)
    {
        $pool = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }
    public function transactionRealTimeData($id){
        try{
            $apiURL = "https://drugstore-262300.firebaseio.com/transaction/".$id.".json";
            $client = new Client();
            $response = $client->put( $apiURL,  [
                    'json'=>[
                        'success' => true,
                        'transaction'=>Transaction::where('user_id',$id)->with('transactionCategory')->orderBy('created_at','desc')->get()
                    ]
                ]
            );
            if($response->getStatusCode()==200){
                $result=json_decode($response->getBody()->getContents());
                return response()->json(["transaction"=>$result], $this->successStatus);
            }
            return response()->json(['success' => false,"transaction"=>$response], $this->failedStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"transaction"=>$e], $this-> failedStatus);
        }
    }
    public function requestRealTimeData(){
        try{
            $apiURL = "https://drugstore-262300.firebaseio.com/request.json";
            $client = new Client();
            $response = $client->put( $apiURL,  [
                    'json'=>[
                        'success' => true,
                        'request'=>Transaction::with('transactionCategory')->with('user')->orderBy('created_at','desc')->get()
                    ]
                ]
            );
            if($response->getStatusCode()==200){
                $result=json_decode($response->getBody()->getContents());
                return response()->json(["request"=>$result], $this->successStatus);
            }
            return response()->json(['success' => false,"request"=>$response], $this->failedStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"request"=>$e], $this-> failedStatus);
        }
    }

}
