<?php
namespace App\Http\Controllers\API;

use App\Model\Category;
use App\Model\Employee;
use App\Model\Pharmacy;
use App\Model\TransactionGps;
use App\Model\Transaction;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Reference;
use Validator;
use Exception;
use File;
use Image;

class UserController extends Controller
{
    public $successStatus = 200;
    public $failedStatus = 401;
    public function login(){
        try{
            if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
                $user = Auth::user();
                $token =  $user->createToken('MyApp')-> accessToken;
                $data['token']=$token;
                $user = User::where('email',request('email'))->first();
                $user->remember_token = request('notification_token');
                $user->save();
                if($user->block==0){
                    $data['user']=User::with('pharmacy','employee')->find($user->id);
                    return response()->json(['success' => true,"message"=>'welcome!',"data"=>$data], $this-> successStatus);
                }else{
                    return response()->json(['success' =>false,"message"=>'you are blocked.',"data"=>null], $this-> failedStatus);
                }
            }
            else{
                return response()->json(['success'=>false,'message'=>'email or password is incorrect.','data'=>null], $this->failedStatus);
            }
        }catch (Exception $e){
            return response()->json(['success'=>false,'message'=>'Unauthorised','data'=>null], $this->failedStatus);
        }
    }
    public function register(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required',
                'c_password' => 'required|same:password',
            ]);
            if ($validator->fails()) {
//            return response()->json(['error'=>$validator->errors()], 401);
                return null;
            }
            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $user = User::create($input);
            $data['token'] =  $user->createToken('MyApp')-> accessToken;
            $data['name'] =  $user->name;
//        return response()->json(['success'=>true,'data'=>$data], $this-> successStatus);
            return $user;
        }catch (Exception $e){
            return null;
        }
    }
    public function allUsers()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this-> successStatus);
    }
    public function getAllEmployee(){
        try{
            $employee = $this->allEmployee();
            return response()->json(['success' => true,"employee"=>$employee], $this-> successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"employee"=>$e], $this-> failedStatus);
        }
    }
    public function addEmployee(Request $request){
        try{
            $user = $this->register($request);
            if($user!=null){
                $employee = new Employee();
                $employee->user_id = $user->id;
                $employee->phone = $request->input('phone');
                $employee->city = $request->input('address');
                $employee->latitude = $request->input('latitude');
                $employee->longitude = $request->input('longitude');
                $employee->save();
                $employees = $this->allEmployee();
                return response()->json(['success' => true,"employee"=>$employees], $this-> successStatus);
            }
            return response()->json(['success' => false,"employee"=>null], $this-> failedStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"employee"=>$e], $this-> failedStatus);
        }

    }
    function allEmployee(){
        return $employee = Employee::with('employeeUser')->orderBy('created_at','desc')->get();
    }
    public function getAllPharmacy(){
        try{
            $pharmacy = $this->allPharmacies();
            return response()->json(['success' => true,"pharmacy"=>$pharmacy], $this-> successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"pharmacy"=>$e], $this-> failedStatus);
        }
    }
    public function addPharmacy(Request $request){
        try{
            $user = $this->register($request);
            if($user!=null){
                $pharmacy = new Pharmacy();
                $pharmacy->user_id = $user->id;
                $pharmacy->name=$request->input('name');
                $pharmacy->latitude=$request->input('latitude');
                $pharmacy->longitude=$request->input('longitude');
                $pharmacy->country=$request->input('address');
                $pharmacy->city=$request->input('address');
                $pharmacy->phone=$request->input('phone');
                $pharmacy->employee=$request->input('employee');
                $pharmacy->save();
                $pharmacy = $this->allPharmacies();
                return response()->json(['success' => true,"pharmacy"=>$pharmacy], $this-> successStatus);
            }
            return response()->json(['success' => false,"pharmacy"=>null], $this-> failedStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"pharmacy"=>$e], $this-> failedStatus);
        }
    }
    public function allPharmacies(){
        return Pharmacy::with('pharmacyUser')->orderBy('created_at','desc')->get();
    }
    public function userBlockOrUnblock(Request $request){
        try{
            $user = User::find($request->input('id'));
            $user->block = !$user->block;
            $user->save();
            if($user->role==2){
                $pharmacy = $this->allPharmacies();
                return response()->json(['success' => true,"pharmacy"=>$pharmacy], $this-> successStatus);
            }elseif ($user->role==3){
                $employee = $this->allEmployee();
                return response()->json(['success' => true,"employee"=>$employee], $this-> successStatus);
            }else{
                return response()->json(['success' => false,"employee"=>null], $this-> failedStatus);
            }
        }catch (Exception $e){
            return response()->json(['success' => false,"employee"=>$e], $this-> failedStatus);
        }
    }
    public function updateUser(Request $request){
        try{
            $user = User::find($request->input('id'));
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => false,'message'=>$validator->errors()], $this->failedStatus);
            }
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            if($user->role==$request->input('role'))
            {
                $user->save();
                if($user->role==2){
                    $pharmacy = Pharmacy::where('user_id',$request->input('id'))->first();
                    $pharmacy ->name=$request->input('name');
                    $pharmacy ->longitude=$request->input('longitude');
                    $pharmacy ->latitude=$request->input('latitude');
                    $pharmacy ->country=$request->input('address');
                    $pharmacy ->city=$request->input('address');
                    $pharmacy ->phone=$request->input('phone');
                    if($request->input('employee')!=null)
                        $pharmacy ->employee=$request->input('employee');
                    $pharmacy->save();
                    $pharmacy=$this->allPharmacies();
                    $employee=$this->allEmployee();
                    return response()->json(['success' => true,"employee"=>$employee,'pharmacy'=>$pharmacy], $this-> successStatus);
                }else{
                    $employee = Employee::where('user_id',$request->input('id'))->first();
                    $employee ->longitude=$request->input('longitude');
                    $employee ->latitude=$request->input('latitude');
                    $employee ->city=$request->input('address');
                    $employee ->phone=$request->input('phone');
                    $employee->save();
                    $pharmacy=$this->allPharmacies();
                    $employee=$this->allEmployee();
                    return response()->json(['success' => true,"employee"=>$employee,'pharmacy'=>$pharmacy], $this-> successStatus);
                }
            }else{
                $user->role=$request->input('role');
                $user->save();
                if($request->input('role')==2){
                    $employee = Employee::where('user_id',$request->input('id'))->first();
                    $employee->delete();
                    $pharmacy = new Pharmacy();
                    $pharmacy ->user_id=$request->input('id');
                    $pharmacy ->name=$request->input('name');
                    $pharmacy ->longitude=$request->input('longitude');
                    $pharmacy ->latitude=$request->input('latitude');
                    $pharmacy ->country=$request->input('address');
                    $pharmacy ->city=$request->input('address');
                    $pharmacy ->phone=$request->input('phone');
                    if($request->input('employee')!=null)
                        $pharmacy->employee=$request->input('employee');
                    $pharmacy->save();
                    $pharmacy=$this->allPharmacies();
                    $employee=$this->allEmployee();
                    return response()->json(['success' => true,"employee"=>$employee,'pharmacy'=>$pharmacy], $this-> successStatus);
                }else{
                    $pharmacy = Pharmacy::where('user_id',$request->input('id'))->first();
                    $pharmacy->delete();
                    $employee = new Employee();
                    $employee->user_id=$request->input('id');
                    $employee ->longitude=$request->input('longitude');
                    $employee ->latitude=$request->input('latitude');
                    $employee ->city=$request->input('address');
                    $employee ->phone=$request->input('phone');
                    $employee->save();
                    $pharmacy=$this->allPharmacies();
                    $employee=$this->allEmployee();
                    return response()->json(['success' => true,"employee"=>$employee,'pharmacy'=>$pharmacy], $this-> successStatus);
                }
            }
        }catch (Exception $e){
            return response()->json(['success' => false,"employee"=>$e,'pharmacy'=>$e], $this-> failedStatus);
        }
    }
    public function settingUpdate(Request $request){
        try{
            $user=Auth::user();
            $user->language=$request->input('language');
            $user->notification=$request->input('notification');
            $user->save();
            $user=User::find($user->id);
            return response()->json(['success' => true,"message"=>'welcome!',"user"=>$user], $this-> successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"message"=>'welcome!',"user"=>$e], $this-> failedStatus);
        }

    }
    public function updateProfile(Request $request){
        try{
            $id=Auth::user()->id;
            $user = User::find($id);
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->password = bcrypt($request->input('password'));
            $image = $_POST['avatar'];
            $fileName = "user/".$this->quickRandom().".jpg";
            if($image!=""){
                if(explode("/",$user->avatar)[0]!="default"){
                    $file_path = public_path("uploads/images/".$user->avatar); // app_path("public/test.txt");
                    if(file_exists($file_path)) {
                        File::delete($file_path);
                    }
                }
                $realImage = base64_decode($image);
                file_put_contents("uploads/images/".$fileName,$realImage);
                $user->avatar=$fileName;
            }
            $user->save();
            $this->imageResize(public_path("uploads/images/".$user->avatar));
            if($user->role==2){
                $pharmacy = Pharmacy::where('user_id',$id)->first();
                $pharmacy ->name=$request->input('name');
                $pharmacy ->longitude=$request->input('longitude');
                $pharmacy ->latitude=$request->input('latitude');
                $pharmacy ->country=$request->input('address');
                $pharmacy ->city=$request->input('address');
                $pharmacy ->phone=$request->input('phone');
                if($image!=""){$pharmacy ->logo=$fileName;}
                $pharmacy->save();
                return response()->json(['success' => true,"avatar"=>$user->avatar,], $this-> successStatus);
            }elseif($user->role==3){
                $employee = Employee::where('user_id',$id)->first();
                $employee ->longitude=$request->input('longitude');
                $employee ->latitude=$request->input('latitude');
                $employee ->city=$request->input('address');
                $employee ->phone=$request->input('phone');
                $employee->save();
                return response()->json(['success' => true,"avatar"=>$user->avatar,], $this-> successStatus);
            }elseif($user->role==1){
                return response()->json(['success' => true,"avatar"=>$user->avatar,], $this-> successStatus);
            }
        }catch (Exception $e){
            return response()->json(['success' => false,"avatar"=>$e,], $this-> failedStatus);
        }
    }
    public function quickRandom($length = 10)
    {
        $pool = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }
    public function getSoldCategory(){
        try{
            $category = Category::with('sold')->orderBy('created_at','desc')->get('id');
            return response()->json(['success' => true,"soldCategory"=>$category,], $this-> successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"soldCategory"=>$e,], $this-> failedStatus);
        }
    }
    public function getChatBasedOrderNumber(){
        try{
            $transactions = Transaction::with('pharmacy')->orderBy('created_at','desc')->get();
            $employees = Transaction::with('employee')->orderBy('created_at','desc')->get();
            $employees = $employees->pluck('employee')->pluck('pharmacy')->pluck('employee');
            foreach ($transactions as $key=>$transaction){
                $employee = User::find($employees[$key]);
                $transaction['chatEmployee']=$employee;
            }
            return response()->json(['success' => true,"chat"=>$transactions,], $this-> successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"chat"=>$e,], $this->failedStatus);
        }
    }
    public function recoverPassword(Request $request){
        try{
            $email = $request->input('email');
            $user = User::where('email',$email)->first();
            if($user==null){
                return response()->json(['success' => false,"message"=>"There is no User.",], $this-> failedStatus);
            }else{
                $password = $this->quickRandom();
                $user->password = bcrypt($password);
                $user->save();
                $this->sendEmail($email,$password);
                return response()->json(['success' => true,"message"=>null,], $this-> successStatus);
            }
        }catch (Exception $e){
            return response()->json(['success' => false,"message"=>$e,], $this-> failedStatus);
        }
    }
    public function updateCurrentLocation(Request $request){
        try{
            $user = Auth::user();
            $currentLocation = new TransactionGps();
            $currentLocation->user_id = $user->id;
            $currentLocation->latitude = $request->input('lat');
            $currentLocation->longitude = $request->input('lng');
            $currentLocation->save();
            $this->locationRealTimeData();
            return response()->json(['success' => true,"lat"=>$currentLocation->latitude,'lng'=>$currentLocation->longitude,], $this-> successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"location"=>$e], $this-> failedStatus);
        }
    }

    public function locationRealTimeData(){
        try{
            $pharmacies = Pharmacy::all();
            $location= [];
            foreach ($pharmacies as $pharmacy){
                $gps = TransactionGps::with('getUser')->where("user_id",$pharmacy->user_id)->orderBy('created_at','desc')->first();
                if($gps!=null){
                    array_push($location,$gps);
                }
            }
            $apiURL = "https://drugstore-262300.firebaseio.com/pharmacyLocation.json";
            $client = new Client();
            $response = $client->put( $apiURL,  [
                    'json'=>[
                        'success' => true,
                        'location'=>$location
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
    public function sendEmail($to,$pws){
        try{
            $data = array(
                'to'=>$to,
                'pws'=>$pws
            );
            Mail::send([], [], function($message) use($data) {
                $message->to($data['to'])->subject('Reset Password!')->from('support@drugstore.com','Drug Store');
                $message->setBody('Your ResetPassWord: '.$data['pws']);
            });
            return response()->json(['success' => true,"messages"=>$data], $this->successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"messages"=>$e], $this->failedStatus);
        }
    }
    public function imageResize($path){
        try{
            $img = Image::make($path);
            $img->resize(500, null, function ($constraint) {$constraint->aspectRatio();})
                ->save($path);
        }catch (Exception $e){
            return null;
        }
    }

}
