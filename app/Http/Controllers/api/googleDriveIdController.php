<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Services\googleDriveIdService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;

class googleDriveIdController extends Controller
{
    protected $googleDriveIdService;
    public function __construct(googleDriveIdService $google_drive_id_service)
    {
        $this->googleDriveIdService=$google_drive_id_service;
    }

    public function getListGDID(){
        $data=[];
        $get_data=$this->googleDriveIdService->getListGDIDConfig();
        $total=$get_data->count();
        if($total > 0){
            foreach($get_data as $list_data){
                $data[]=[
                    "config_name"=>$list_data['config_initial'],
                    "config_value"=>$list_data['config_value'],
                    "config_token"=>Crypt::encrypt($list_data['id'])
                ];
            }
        }
        return response()->json(['status'=>true, 'data'=>$data, 'jumlah'=>$total]);
    }

    public function getGdIdById($gd_id){
        $status=false;
        $data=null;
        try{
            $gd_id_dec=Crypt::decrypt($gd_id);
            $get_data=$this->googleDriveIdService->getGdIdById($gd_id_dec);
            if(!is_null($get_data)){
                $data=[
                    "config_name"=>$get_data['config_initial'],
                    "config_value"=>$get_data['config_value'],
                    "config_token"=>Crypt::encrypt($get_data['id'])
                ];
                $status=true;
                $msg="Data Found";
            }else{
                $msg="Data sedang diproses";
            }
        }catch(DecryptException $e){
            $msg="Invalid data";
        }

        return response()->json(['status'=>$status, 'msg'=>$msg, 'data'=>$data]);
    }

    public function updateGoogleDriveId(Request $request){
        $status=false;
        try{
            $request->validate([
                'nama_gd'=>['required', 'string'],
                'gd_id'=>['required', 'string'],
                'token'=>['required', 'string']
            ]);

            try{
                $id=Crypt::decrypt($request->token);
                $update=$this->googleDriveIdService->updateGdId($id, $request->nama_gd, $request->gd_id);
                $status=$update['status'];
                $msg=$update['msg'];     
            }catch(DecryptException $e){
                $msg="Invalid token";
            }
        }catch(ValidationException $e){
            $msg=$e->validator->errors()->first();
        }
        return response()->json(['status'=>$status, 'msg'=>$msg]);
    }

    public function saveGdId(Request $request){
        $status=false;
        try{
            $request->validate([
                'nama_gd'=>['required', 'string'],
                'gd_id'=>['required', 'string']
            ]);
            $save=$this->googleDriveIdService->saveNewGdId($request->nama_gd, $request->gd_id);
            $status=$save['status'];
            $msg=$save['msg'];
        }catch(ValidationException $e){
            $msg=$e->validator->errors()->first();
        }

        return response()->json(['status'=>$status, 'msg'=>$msg]);
    }

    public function deactivateGdId(Request $request){
        $status=false;
        try{
            $gd_id=Crypt::decrypt($request->gd_id);
            $deactivate=$this->googleDriveIdService->deactivateGdId($gd_id);
            $status=$deactivate['status'];
            $msg=$deactivate['msg'];
        }catch(DecryptException $e){
            $msg="Data sedang diproses";
        }

        return response()->json(['status'=>$status, 'msg'=>$msg]);
    }
}
