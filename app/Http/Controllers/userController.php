<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Citizen;
use App\Services\UserService;

class userController extends Controller
{
    protected $userService;
    public function __construct(UserService $user_service){
        $this->userService=$user_service;
    }
    public function userDetil(Request $request){
        $status=401;
        if($request->cookie('network')){
            $management=[];
            $user=$request->user();
            if(isset($user) && !is_null($user)){
                $citizen_id=$request->user()->citizen_id;//request user() diambil dari sanctum
                $get_user=User::join('citizen', function($join) use($citizen_id){
                                        $join->on('citizen.id', '=', 'users.citizen_id')
                                        ->where('users.citizen_id', $citizen_id);
                                    })
                                ->leftJoin('jabatan', 'jabatan.jabatan_cuti', '=', 'citizen.id_jabatan')
                                ->select('citizen.nama', 'citizen.foto', 'jabatan.jabatan')
                                ->first();
                $pj=$this->userService->isPj($request->user()->citizen_id);    
                return response()->json(['status'=>200, 'data'=>$get_user, 'access'=>$this->userService->isAdmin($request->user()->citizen_id), 'pj'=>$pj]);
            }
        }
        return response()->json(['status'=>$status, 'data'=>null, 'access'=>null, 'pj'=>null]);
        // return response()->json(['data'=>$request->user()]);
    }
}
