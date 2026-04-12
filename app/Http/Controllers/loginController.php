<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;

class loginController extends Controller
{
    public function login(Request $request){
        $msg="-";
        try{
           
            $validate=$request->validate([
                'username'=>['required', 'min:10'],
                'password'=>['required', 'min:6']
            ]);
            $username=$request->username;
            $get_user=User::join('citizen', function($join) use($username){
                            $join->on('citizen.id', '=', 'users.citizen_id')
                            ->where('citizen.nip', $username)
                            ->where('citizen.status', true);
                        })->first();
            if(!is_null($get_user)){
                $hash=Hash::check($request->password, $get_user['password']);
                if($hash){
                    $user=User::where('citizen_id', $get_user['citizen_id'])->first();
                    $token=JWTAuth::fromUser($user);
                    $refreshToken=JWTAuth::claims(['type' => 'refresh'])->fromUser($user);//pitu ari
                    $same_site=config('session.same_site');
                    $isSecure=config('session.secure');
                    $sub=config('session.accepted_sub');
                    return response()->json(['message'=>'Login berhasil', 'token'=>$token, 'status'=>200])->withCookie(cookie('network', $refreshToken, 60*24*7, '/', $sub, $isSecure, true, false, $same_site));
                }else{
                    return response()->json(['message'=>'Password salah', 'status'=>401]);
                }
            }
        }catch(ValidationException $e){
            $msg=$e->validator->errors()->first();
        }
        return response()->json(['message'=>'Unauthorized '.$msg, 'status'=>401]);
    }


    public function refresh(Request $request){
        $refreshToken=$request->cookie('network');
        try{
            $payload=JWTAuth::setToken($refreshToken)->getPayLoad();
            if($payload->get('type') !== 'refresh'){
                return response()->json(['message'=>'Invalid token'], 401);
            }
            $user=JWTAuth::setToken($refreshToken)->toUser();
            $newAccessToken=JWTAuth::fromUser($user);

            return response()->json(['token'=>$newAccessToken], 200);
        }catch(\Exception){
            $same_site=config('session.same_site');
            $isSecure=config('session.secure');
            $sub=config('session.accepted_sub');
            return response()->json(['message'=>'Invalid or expired token'], 401)->withCookie(cookie('network', $sub, -1, '/', $sub, $isSecure, true, false, $same_site));
        }
    }

    public function logout(Request $request){
        // Auth::guard('web')->logout();
        // Cookie::queue(Cookie::forget('network'));
        // $request->session()->invalidate(); // ✅ Invalidate session
        // $request->session()->regenerateToken();  
        $same_site=config('session.same_site');
        $isSecure=config('session.secure');
        $sub=config('session.accepted_sub');
        return response()->json(['status'=>200, 'message'=>'Logged Out'])->withCookie(cookie('network', $sub, -1, '/', $sub, $isSecure, true, false, $same_site));
        // return response()->json(['message'=>'Login berhasil', 'token'=>$token, 'status'=>200])->withCookie(cookie('network', $refreshToken, 60*24*7, '/', null, false, true, false, 'Lax'));
    }
}
