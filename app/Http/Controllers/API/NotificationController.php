<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Model\Pharmacy;

class NotificationController extends Controller
{
    public $successStatus = 200;
    public $failedStatus = 401;
    public $firebaseKey = "AIzaSyA4zrGIz3g5wmou5LCC537pfrHa6Vhxg1g";
    public function sendBuzzNotification(Request $request){
        try{
        $fromUser=Auth::user();
        $toUser = Pharmacy::where('user_id',Auth::user()->id)->first()->employee;
        $toUser = User::find($toUser);
        $orderNumber=$request->input('order_number');
        $apiURL = "https://fcm.googleapis.com/fcm/send";
        $client = new Client([
            'headers' => [
                'authorization' => 'key='.$this->firebaseKey,
                'content-type' => 'application/json',
                'Accept'=>'application/json'
            ]
        ]);
        $response = $client->post( $apiURL,  [
                'json'=>[
                    'to' => $toUser->remember_token,
                    'notification'=>[
                        'title'=>'Buzz from '.$fromUser->name,
                        'body'=>'I need my order complete as soon as possible.'.'Order Number: '.$orderNumber
                    ],
                    'data'=>[
                        'type'=>'buzz',
                        'order_number'=>$orderNumber,
                        'user_name'=>$fromUser->name
                    ]
                ]
            ]
        );
        if($response->getStatusCode()==200){
            $result=json_decode($response->getBody()->getContents());
            return response()->json(['success' => true,"notification"=>$result], $this->successStatus);
        }
            return response()->json(['success' => false,"notification"=>$response], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"notification"=>$e], $this-> failedStatus);
        }
    }
    public function updateItemNotification($message){
        try{
            $apiURL = "https://fcm.googleapis.com/fcm/send";
            $client = new Client([
                'headers' => [
                    'authorization' => 'key='.$this->firebaseKey,
                    'content-type' => 'application/json',
                    'Accept'=>'application/json'
                ]
            ]);
            $response = $client->post( $apiURL,  [
                    'json'=>[
                        'notification'=>[
                            'title'=>'Item of Drug store updated.',
                            'body'=>$message
                        ],
                        'data'=>[
                            'type'=>'updateItem'
                        ],
                        'condition'=>"!('anytopicyoudontwanttouse' in topics)"
                    ]
                ]
            );
        }catch (Exception $e){
            return false;
        }
    }
    public function newItemNotification($message){
        try{
            $apiURL = "https://fcm.googleapis.com/fcm/send";
            $client = new Client([
                'headers' => [
                    'authorization' => 'key='.$this->firebaseKey,
                    'content-type' => 'application/json',
                    'Accept'=>'application/json'
                ]
            ]);
            $response = $client->post( $apiURL,  [
                    'json'=>[
                        'notification'=>[
                            'title'=>'New Item of Drug store have been added.',
                            'body'=>$message
                        ],
                        'data'=>[
                            'type'=>'newItem'
                        ],
                        'condition'=>"!('anytopicyoudontwanttouse' in topics)"
                    ]
                ]
            );
        }catch (Exception $e){
            return false;
        }
    }
    public function newNewsNotification($message){
        try{
            $apiURL = "https://fcm.googleapis.com/fcm/send";
            $client = new Client([
                'headers' => [
                    'authorization' => 'key='.$this->firebaseKey,
                    'content-type' => 'application/json',
                    'Accept'=>'application/json'
                ]
            ]);
            $response = $client->post( $apiURL,  [
                    'json'=>[
                        'notification'=>[
                            'title'=>'New News of Drug store have been added.',
                            'body'=>$message
                        ],
                        'data'=>[
                            'type'=>'newNews'
                        ],
                        'condition'=>"!('anytopicyoudontwanttouse' in topics)"
                    ]
                ]
            );
        }catch (Exception $e){
            return false;
        }
    }
    public function newOrderNotification($to,$from,$orderNumber){
        try{
            $apiURL = "https://fcm.googleapis.com/fcm/send";
            $client = new Client([
                'headers' => [
                    'authorization' => 'key='.$this->firebaseKey,
                    'content-type' => 'application/json',
                    'Accept'=>'application/json'
                ]
            ]);
            $response = $client->post( $apiURL,  [
                    'json'=>[
                        'to' => $to,
                        'notification'=>[
                            'title'=>'New Order',
                            'body'=>'Dear Employee, New Order created by '.$from."."
                        ],
                        'data'=>[
                            'type'=>'newOrder',
                            'orderNumber'=>$orderNumber
                        ]
                    ]
                ]
            );

        }catch (Exception $e){
            return false;
        }
    }
    public function reportItemNotification($from,$item){
        try{
            $apiURL = "https://fcm.googleapis.com/fcm/send";
            $client = new Client([
                'headers' => [
                    'authorization' => 'key='.$this->firebaseKey,
                    'content-type' => 'application/json',
                    'Accept'=>'application/json'
                ]
            ]);
            $tos = User::where('role',2)->get();
            foreach ($tos as $to){
                if($to->remember_token!=null)
                $response = $client->post( $apiURL,  [
                        'json'=>[
                            'to' => $to->remember_token,
                            'notification'=>[
                                'title'=>'New Report about Item',
                                'body'=>'Dear '.$to->name.', '.$from.' reported to Item: '.$item
                            ],
                            'data'=>[
                                'type'=>'itemReport',
                                'itemName'=>$item
                            ]
                        ]
                    ]
                );
            }
        }catch (Exception $e){
            return $e;
        }
    }
    public function reportPharmacyNotification($from,$to,$report,$rate){
        try{
            $apiURL = "https://fcm.googleapis.com/fcm/send";
            $client = new Client([
                'headers' => [
                    'authorization' => 'key='.$this->firebaseKey,
                    'content-type' => 'application/json',
                    'Accept'=>'application/json'
                ]
            ]);
            if($to->remember_token!=null)
            $response = $client->post( $apiURL,  [
                    'json'=>[
                        'to' => $to->remember_token,
                        'notification'=>[
                            'title'=>'New Report about pharmacy',
                            'body'=>'Dear '.$to->name.', '.$from.' reported to your Pharmacy'
                        ],
                        'data'=>[
                            'type'=>'itemReport',
                            'report'=>$report,
                            'rate'=>$rate
                        ]
                    ]
                ]
            );
        }catch (Exception $e){
            return $e;
        }
    }
    public function reportNewsNotification($from,$news){
        try{
            $apiURL = "https://fcm.googleapis.com/fcm/send";
            $client = new Client([
                'headers' => [
                    'authorization' => 'key='.$this->firebaseKey,
                    'content-type' => 'application/json',
                    'Accept'=>'application/json'
                ]
            ]);
            $tos = User::where('role',2)->get();
            foreach ($tos as $to){
                if($to->remember_token!=null)
                    $response = $client->post( $apiURL,  [
                            'json'=>[
                                'to' => $to->remember_token,
                                'notification'=>[
                                    'title'=>'New Report about News',
                                    'body'=>'Dear '.$to->name.', '.$from.' reported to News: '.$news
                                ],
                                'data'=>[
                                    'type'=>'newsReport',
                                    'newsName'=>$news
                                ]
                            ]
                        ]
                    );
            }
        }catch (Exception $e){
            return $e;
        }
    }
    public function chatNotification(Request $request){
        try{
            $fromUser=Auth::user();
            $toUser = Pharmacy::where('user_id',Auth::user()->id)->first()->employee;
            $toUser = User::find($toUser);
            $orderNumber=$request->input('order_number');
            $apiURL = "https://fcm.googleapis.com/fcm/send";
            $client = new Client([
                'headers' => [
                    'authorization' => 'key='.$this->firebaseKey,
                    'content-type' => 'application/json',
                    'Accept'=>'application/json'
                ]
            ]);
            if($toUser->remember_token!=null)
            $response = $client->post( $apiURL,  [
                    'json'=>[
                        'to' => $toUser->remember_token,
                        'notification'=>[
                            'title'=>'Chat Request from '.$fromUser->name,
                            'body'=>'Dear '.$toUser->name.', '.$fromUser->name.' request chat for his Order.'.'Order Number:'.$orderNumber
                        ],
                        'data'=>[
                            'type'=>'chat',
                            'order_number'=>$orderNumber,
                            'user_name'=>$fromUser->name
                        ]
                    ]
                ]
            );
            if($response->getStatusCode()==200){
                $result=json_decode($response->getBody()->getContents());
                return response()->json(['success' => true,"notification"=>$result], $this->successStatus);
            }
            return response()->json(['success' => false,"notification"=>$response], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"notification"=>$e], $this-> failedStatus);
        }
    }

    public function updateOrderNotification($to,$from,$orderNumber){
        try{
            $apiURL = "https://fcm.googleapis.com/fcm/send";
            $client = new Client([
                'headers' => [
                    'authorization' => 'key='.$this->firebaseKey,
                    'content-type' => 'application/json',
                    'Accept'=>'application/json'
                ]
            ]);
            $response = $client->post( $apiURL,  [
                    'json'=>[
                        'to' => $to->remember_token,
                        'notification'=>[
                            'title'=>'Order Changed',
                            'body'=>'Dear '.$to->name.', Order:'.$orderNumber.' changed by '.$from.'.'
                        ],
                        'data'=>[
                            'type'=>'newOrder',
                            'orderNumber'=>$orderNumber
                        ]
                    ]
                ]
            );
        }catch (Exception $e){
            return false;
        }
    }
}
