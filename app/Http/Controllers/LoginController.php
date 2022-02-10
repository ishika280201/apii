<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Code;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function register(Request $request){
        $validate = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|email',
           // 'mobile' => 'required',
            'password' => ' required'
        ]);

        if($validate->fails()){
            return response()->json($validate->errors());
        }
        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            //'mobile'=>$request->mobile,
            'password'=>bcrypt($request->password)
        ]);

        $token = $user->createToken('authtoken')->accessToken;
        return response()->json([
            'data'=>$user,
            'access_token'=>$token,
            'token_type'=>'Bearer'
        ]);
    }

    public function sendOTP(Request $request){
        $mobile = $request->mobile;

        if(!preg_match('/^[0-9]{10}+$/',$mobile)){
            return response()->json([
            'message'=>'Invalid mobile number'],403);
        }

        $user = User::firstWhere('mobile',$mobile);
        if($user){
            $account_exists = true;
        }else{
            $account_exists = false;
        }
        $otp_number = random_int(1000,9999);

        $otp = new Code;
        $otp->code = $otp_number;
        $otp->mobile = $mobile;
        $otp->save();

        $otp->sendToMobile();

        return response()->json([
            'message' => 'ok',
            'data' => [
                'account' => $account_exists
            ]
            ]);
    }

    public function login(Request $request){
        $mobile = $request->mobile;
        $code = $request->otp;

        if(!preg_match('/^[0-9]{10}+$/',$mobile)){
            return response()->json([
                'message' => 'Invalid number'
            ],401);
        }

        if(!preg_match('/^[0-9]{10}+$/',$mobile)){
            return response()->json([
                'message' => 'Invalid OTP'
            ],401);
        }

        $otp = Code::where('mobile',$mobile)->where('code',$code)->where('active',1)->first();
        
        if(!$otp || !$otp->isvalidotptime()){
            return response()->json([
                'message'  => 'Inalid OTP'
            ],401);
        }

        $otp->active = 0;
        $otp->save();

        $user = User::firstWhere('mobile',$mobile);
        if(!$user){
            $user = new User;
            $user->mobile = $mobile;
            $user->password = bcrypt($request->password);
            $user->save();
        }

        Auth::login($user);
        $tokenResult = $user->createToken('access token');
        $token = $tokenResult->token;

        $token->expires_at = Carbon::now()->addWeeks(2);
        $token->save();

        return response()->json([
            'user' => $user,
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
            ]);
    }
}
