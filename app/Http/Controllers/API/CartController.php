<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Cart;
use Exception;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public $successStatus = 200;
    public $failedStatus = 401;
    public function getCart(Request $request){
        try{
            $cart = Cart::where('user_id',Auth::user()->id)->orderBy('created_at','desc')->get();
            return response()->json(['success' => true,"cart"=>$cart], $this-> successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"cart"=>$e], $this-> failedStatus);
        }
    }
   public function addCart(Request $request){
       try{
           $preCart = Cart::where('user_id',Auth::user()->id)->where('category_id',$request->input('category_id'))->first();
           if($preCart!=null){
               $preCart->quantity=$preCart->quantity+$request->input('quantity');
               $preCart->save();
               $preCart = Cart::where('user_id',$request->input('user_id'))->orderBy('created_at','desc')->get();
               return response()->json(['success' => true,"cart"=>$preCart], $this-> successStatus);
           }else{
               $cart = new Cart();
               $cart->user_id=$request->input('user_id');
               $cart->category_id=$request->input('category_id');
               $cart->quantity=$request->input('quantity');
               $cart->save();
               $cart = Cart::where('user_id',$request->input('user_id'))->orderBy('created_at','desc')->get();
               return response()->json(['success' => true,"cart"=>$cart], $this-> successStatus);
           }
       }catch (Exception $e){
           return response()->json(['success' => false,"cart"=>$e], $this-> failedStatus);
       }
   }
    public function removeCart(Request $request){
        try{
            $cart = Cart::find($request->input('id'));
            if($cart!=null){
                $cart->delete();
                $cart = Cart::where('user_id',Auth::user()->id)->orderBy('created_at','desc')->get();
                return response()->json(['success' => true,"cart"=>$cart], $this-> successStatus);
            }else{
                return response()->json(['success' => false,"cart"=>null], $this-> successStatus);
            }
        }catch (Exception $e){
            return response()->json(['success' => false,"cart"=>$e], $this-> failedStatus);
        }
    }
    public function resetCart(Request $request){
        try{
            $cart = Cart::where('user_id',Auth::user()->id)->get();
            if(count($cart)>0){
                $cart->each(function ($item) {
                    $item->delete();
                });
                $cart = Cart::where('user_id',$request->input('user_id'))->orderBy('created_at','desc')->get();
                return response()->json(['success' => true,"cart"=>$cart], $this-> successStatus);
            }else{
                return response()->json(['success' => false,"cart"=>null], $this-> successStatus);
            }
        }catch (Exception $e){
            return response()->json(['success' => false,"cart"=>$e], $this-> failedStatus);
        }
    }
    public function updateCartQuantity(Request $request){
        try{
            $cart = Cart::find($request->input('id'));
            $cart->quantity = $request->input('quantity');
            $cart->save();
            $carts = Cart::where('user_id',Auth::user()->id)->orderBy('created_at','desc')->get();
            return response()->json(['success' => true,"cart"=>$carts], $this-> successStatus);
        }catch (Exception $e){
            return response()->json(['success' => false,"cart"=>$e], $this-> failedStatus);
        }
    }
}
