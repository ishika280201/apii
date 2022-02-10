<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends Controller
{
        public function forgotpassword(Request $request){
            $request->validate([
                'email' => 'required|email'
            ]);

            $status = Password::sendResetLink(
                $request->only('email')
            );

            if($status==Password::RESET_LINK_SENT){
                return[
                    'status'=>__($status)
                ];
            }

            throw ValidationException::withMessages([
                'email'=>[trans($status)],
            ]);
        }    

        public function resetpassword(Request $request){
            $request->validate([
                'token'=>'required',
                'email'=>'required|email',
                'password'=>'required'
            ]);

            $status = Password::reset(
                $request->only('email','password','token'),
                function($user) use($request){
                    $user->forceFill([
                        'password'=> Hash::make($request->password),
                        'remember_token'=>Str::random(60),
                    ])->save();

                    $user->tokens()->delete();
                    event(new PasswordReset($user));
                }
            );

            if($status==Password::PASSWORD_RESET){
                return response([
                    'message'=>'password reset succesfully'
                ]);
            }
            return response([
                'message'=>__($status)
            ],500);
        }
}
