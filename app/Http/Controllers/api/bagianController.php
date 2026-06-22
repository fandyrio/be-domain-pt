<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bagian;
use App\Models\Pj_bagian;
use App\Models\Citizen;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Models\Indikator_user;
use App\Services\UserService;
use Illuminate\Validation\ValidationException;

class bagianController extends Controller
{
    protected $userService;

    public function __construct(UserService $user_service){
        $this->userService=$user_service;
    }

    public function syncBagian(){
        $end_point_cuti=config('costum.api_cuti_prod');
        $path=$end_point_cuti."/get-all-bagian-nf";
        $response=Http::withToken('Bearer di1Z5eP9N6yNfPYhjU9Op8Dg0JlrJ81jelQYiDErfdBWOe0FcQa8l86E2dFA')->get($path);
        $data=$response->json();
        $save_data=$this->saveBagian($data);
        return $save_data;
    }

    public function saveBagianManual(Request $request){
        $save=false;
        try{
            $validate=$request->validate([
                'new_bagian_name' => ['required'],
            ]);

            $get_data=Bagian::whereRaw("bagian_code like '%bag-manual%' ")->get();
            $jumlah=$get_data->count();
            $index=$jumlah+1;
            $new_bagian=new Bagian;
            $new_bagian->bagian_code='bag-manual-'.$index;
            $new_bagian->bagian=$request->new_bagian_name;
            $new_bagian->cuti_id=0;
            $new_bagian->alias=$request->new_bagian_name;
            $new_bagian->active=true;
            if($new_bagian->save()){
                $save=true;
                $msg="Berhasil menyimpan data bagian";
            }else{
                $msg="Database Error!.";
            }

        }catch(ValidationException $e){
            $msg=$e->validator->errors()->first();
        }
        return response()->json(['status'=>$save, 'msg'=>$msg]);
    }

    public function saveBagian($data){
        $status=false;
        $saved=0;
        if((int)$data['total'] > 0){
            $data_bagian=$data['data'];
            
            for($x=0;$x<$data['total'];$x++){
                $bagian_code='bag-0'.$data_bagian[$x]['id'];
                $check=$this->getBagianById($bagian_code);
                if(is_null($check)){
                    $alias=str_replace(
                        array("Kepaniteraan ", "Bagian "),
                        array("", ""), 
                        $data_bagian[$x]['bagian']);
                    $bagian=new Bagian;
                    $bagian->bagian_code=$bagian_code;
                    $bagian->cuti_id=$data_bagian[$x]['id'];
                    $bagian->bagian=$data_bagian[$x]['bagian'];
                    $bagian->active=true;
                    $bagian->alias=$alias;
                    $save=$bagian->save();
           
                }else{
                    $check->bagian=$data_bagian[$x]['bagian'];
                    $save=$check->update();
                }
                if($save){
                    $saved++;
                }
            }
        }
        if($saved === (int)$data['total']){
            $status=true;
            $msg="Berhasil menyimpan data bagian ".$saved." : ".$data['total'];
        }else{
            $msg="Sistem tidak menyimpan data ".$saved." : ".$data['total'];
        }
        return [
            'status'=>$status,
            'msg'=>$msg
        ];
    }

    public function getBagianById($bagian_code){
        $get_data=Bagian::where('bagian_code', $bagian_code)->first();
        return $get_data;
    }

    public function getBagianByAlias(Request $request){
        $alias_enc=$request->bagian_alias_enc;
        $status=false;
        $data=[];
        $msg="";
        try{
            $alias=Crypt::decrypt($alias_enc);
            $get_bagian=Bagian::where('alias', $alias)->first();
            if(!is_null(($get_bagian))){
                $data['bagian']=$get_bagian['bagian'];
                $data['token']=Crypt::encrypt($get_bagian['id']);
                $status=true;
            }else{
                $msg="Data tidak ditemukan";
            }
        }catch(DecryptException $e){
            $msg="Invalid token";
        }
        return response()->json(['status'=>$status, 'msg'=>$msg, 'data'=>$data]);
    }

    public function getAllBagian(){
        $get_data=Bagian::all();
        $data=[];
        $x=0;
        foreach($get_data as $data_bagian){
            $data[$x]['bagian']=$data_bagian['bagian'];
            $data[$x]['alias']=$data_bagian['alias'];
            $data[$x]['active']=(int)$data_bagian['active'];
            $data[$x]['id']=Crypt::encrypt($data_bagian['id']);
            $data[$x]['bagian_code']=Crypt::encrypt($data_bagian['bagian_code']);
            $data[$x]['uid']=$data_bagian['id'];
            $x++;
        }
        return response()->json(['data'=>$data, 'jumlah'=>$get_data->count()]);
    }

    public function updateBagian(Request $request){
        $update=false;
        try{
            $bagian_code=Crypt::decrypt($request->bagian_code_enc);
            $bagian=$this->getBagianById($bagian_code);
            if(is_null($bagian)){
                $msg="Data bagian tidak ditemukan";
            }else{
                $status=(boolean)$request->status;
                $bagian->active=$status;
                $update=$bagian->update();
                if($update){
                    $msg="Berhasil memperbaharui data";
                }else{
                    $msg="Terjadi kesalahan sistem";
                }
            }
        }catch(DecryptException $e){
            $msg="Invalid token";
        }
        return response()->json(['status'=>$update, 'msg'=>$msg]);
    }

    public function updateBagianManual(Request $request){
        $update=false;
        try{
            $validate=$request->validate([
                'bagian_code_enc' => ['required'],
                'new_bagian_name' => ['required']
            ]);
            try{
                $bagian_code=Crypt::decrypt($request->bagian_code_enc);
                $bagian=$this->getBagianById($bagian_code);
                if(is_null($bagian)){
                    $msg="Data bagian tidak ditemukan";
                }else{
                    $bagian->alias=$request->new_bagian_name;
                    $update=$bagian->update();
                    if($update){
                        $msg="Berhasil memperbaharui data";
                    }else{
                        $msg="Terjadi kesalahan sistem";
                    }
                }
            }catch(DecryptException $e){
                $msg="Invalid token";
            }
        }catch(ValidationException $e){
            $msg=$e->validator->errors()->first();
        }

        return response()->json(['status'=>$update, 'msg'=>$msg]);
    }

    public function getActiveManagement(Request $request){
        if($this->userService->isAdmin($request->user()->citizen_id)){
            $get_bagian=Bagian::where('active', true)->get();
        }else{
            $id_bagian_arr=[];
            $citizen_id=$request->user()->citizen_id;
            $get_bagian=Pj_bagian::join('citizen', function($join) use($citizen_id){
                                        $join->on('citizen.id', '=', 'pj_bagian.id_citizen')
                                        ->where('citizen.id', $citizen_id);
                                    })
                                ->join('bagian', 'bagian.id', '=', 'pj_bagian.id_bagian')
                            ->select("bagian.bagian", 'bagian.id as id_bagian', 'bagian.alias')
                        ->get();
        }
        $x=0;
        foreach($get_bagian as $list_pj){
            $alias=is_null($list_pj['alias']) ? $list_pj['bagian'] : $list_pj['alias'];
            $id_bagian_arr[$x]['target']=Crypt::encrypt($alias);
            $id_bagian_arr[$x]['id']=Crypt::encrypt($list_pj['id_bagian']);
            $id_bagian_arr[$x]['bagian']=$alias;
            $id_bagian_arr[$x]['nama_bagian']=$list_pj['bagian'];
            $x++;
        }
        return response()->json(['data'=>$id_bagian_arr, 'jumlah'=>$get_bagian->count()]);
    }

    public function getActiveBagian(Request $request){
        if($this->userService->isAdmin($request->user()->citizen_id)){
            $get_bagian=Bagian::where('active', true)->get();
        }else{
            $id_bagian_arr=[];
            $citizen=Citizen::leftJoin('bagian', 'bagian.id', '=', 'citizen.id_bagian')
                        ->select('bagian.id as id_bagian')
                        ->where('citizen.id', $request->user()->citizen_id)->first();
            $id_bagian=$citizen['id_bagian'];//id bagian cuti
            $get_pj=Pj_bagian::where('id_citizen', $request->user()->citizen_id)->get();
            foreach($get_pj as $pj){
                array_push($id_bagian_arr, $pj['id_bagian']);
            }
            array_push($id_bagian_arr, $id_bagian);
            $get_bagian=Bagian::whereIn('id', $id_bagian_arr)->get();
        }
        $jumlah=$get_bagian->count();
        $x=0;
        $data=[];
        foreach($get_bagian as $bagian){
            $alias=is_null($bagian['alias']) ? $bagian['bagian'] : $bagian['alias'];
            $data[$x]['id']=Crypt::encrypt($bagian['id']);
            $data[$x]['bagian']=$alias;
            $data[$x]['target']=Crypt::encrypt($alias);
            $data[$x]['nama_bagian']=$bagian['bagian'];
            $x++;
        }
        return response()->json(['data'=>$data, 'jumlah'=>$jumlah]);
    }

    public function getToken($string){
        $current_year=date('Y');
        $id_bagian="";
        $nama_bagian="";
        $get_data=Bagian::where('alias', $string)->first();
        if(is_null($get_data)){
            return response()->json(['msg'=>'Text tidak ditemukan'], 400);
        }else{
            $id_bagian=$get_data['id'];
            $nama_bagian=$get_data['bagian'];
        }
        return response()->json(['status'=> 200, 'token'=>Crypt::encrypt($string), 'current_year'=>$current_year, 'token_bagian'=>Crypt::encrypt($id_bagian), 'id_bagian'=>$id_bagian, 'nama_bagian'=>$nama_bagian]);
    }

    public function deleteBagian(Request $request){
        $delete=false;
        try{
            $id_bagian=Crypt::decrypt($request->bagian_id_enc);
            $get_data=Bagian::where('id', $id_bagian)->first();
            if(!is_null($get_data)){
                $get_indikator=Indikator_user::where('id_bagian', $id_bagian)->get();
                $jumlah=$get_indikator->count();
                if($jumlah === 0){
                    if($get_data->delete()){
                        $delete=true;
                        $msg="Berhasil menghapus data";
                    }else{
                        $msg="Terjadi kesalahan database";
                    }
                }else{
                    $msg="Data bagian tidak dapat dihapus. Indikator user telah diisi";
                }
            }else{
                $msg="Data bagian tidak dapat dihapus";
            }
        }catch(DecryptException $e){
            $msg="Invalid token";
        }
        return response()->json(['status'=>$delete, 'msg'=>$msg]);
    }
}
