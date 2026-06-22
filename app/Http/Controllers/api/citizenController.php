<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Models\Citizen;
use App\Models\Bagian;
use App\Models\Jabatan;
use App\Models\User;
use App\Services\bagianService;
use App\Services\citizenService;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class citizenController extends Controller
{
    protected $userService;
    protected $citizenService;
    protected $bagianService;

    public function __construct(UserService $user_service, citizenService $citizen_service, bagianService $bagian_service){
        $this->userService=$user_service;
        $this->citizenService=$citizen_service;
        $this->bagianService=$bagian_service;
    }


    public function syncCitizen(){
        $end_point_cuti=config('costum.api_cuti_prod');
        $path_citizen=$end_point_cuti."/sync-citizen";
        $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => $path_citizen,
            //CURLOPT_URL => 'http://backend-cuti.pn-bengkulu.go.id/api/get-hakim-dus',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            //CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST=> 0,
            CURLOPT_CUSTOMREQUEST => 'GET',
            //CURLOPT_POSTFIELDS => array("uid"=>$data['uid'], "token"=>$data["token"], "as"=>$data["as"]),
            CURLOPT_HTTPHEADER => array(
                'Accept:application/json',
                'Authorization:Bearer di1Z5eP9N6yNfPYhjU9Op8Dg0JlrJ81jelQYiDErfdBWOe0FcQa8l86E2dFA'
                ),
            ));
            $response = curl_exec($curl);
            $decode=json_decode($response);
            $info=curl_getinfo($curl);
            $err = curl_error($curl);  //if you need
            curl_close($curl);
            if(isset($decode->message)){
                return response()->json([ 'status'=>false, 'msg'=>'Error API Cuti '.$path_citizen." ".$decode->message ]);
            }else{
                $data=$decode->data;
                $jumlah=$decode->jumlah;
                $save_citizen=$this->saveCitizen($data, $jumlah);
                return response()->json($save_citizen);
            }
        // if($data_citizen->successful()){
        //     // var_dump($data_citizen->json());die();
        //     $data=$data_citizen->json();
        //     // $save_citizen=$this->saveCitizen($data);
        //     return $data_citizen->body();
        // }else{
        //     return response()->json([ 'status'=>false, 'msg'=>'Error API Cuti' ]);
        // }
    }

    public function saveCitizen($data, $jumlah){
        $data_citizen=$data;
        $saved=0;
        $status=false;
        $update_data=Citizen::query()->update(['synced' => false]);
        for($x=0;$x<$jumlah;$x++){
        // $check_data=Citizen::where('id')
            $check_data=Citizen::where('citizen_id_cuti', $data_citizen[$x]->id)->first();
            if(is_null($check_data)){
                $bagian=$this->bagianService->getBagianByCutiId($data_citizen[$x]->id_bagian);
                $citizen=new Citizen;
                $citizen->nama=$data_citizen[$x]->nama;
                $citizen->citizen_id_cuti=$data_citizen[$x]->id;
                $citizen->nip=$data_citizen[$x]->nip;
                $citizen->nik=$data_citizen[$x]->nik;
                $citizen->email=$data_citizen[$x]->email;
                $citizen->pangkat=$data_citizen[$x]->pangkat;
                $citizen->pendidikan=$data_citizen[$x]->pendidikan;
                $citizen->tempat_pendidikan=$data_citizen[$x]->pendidikan;
                $citizen->tgl_lulus=$data_citizen[$x]->tgl_lulus;
                $citizen->tempat_lahir=$data_citizen[$x]->tempat_lahir;
                $citizen->tanggal_lahir=$data_citizen[$x]->tanggal_lahir;
                $citizen->no_hp=$data_citizen[$x]->no_hp;
                $citizen->jenis_kelamin=$data_citizen[$x]->jenis_kelamin;
                $citizen->id_jabatan=$data_citizen[$x]->id_jabatan;
                $citizen->id_bagian=$bagian['id_bagian'];
                $citizen->satker=$data_citizen[$x]->satker;
                $citizen->foto=$data_citizen[$x]->foto;
                $citizen->masuk_kerja=$data_citizen[$x]->masuk_kerja;
                $citizen->status=$data_citizen[$x]->status;
                $citizen->synced=true;
                $save=$citizen->save();
                if($save){
                    $citizen_id=$citizen->id;
                    $save_user=$this->userService->generateUser(Crypt::encrypt($citizen_id), Crypt::encrypt($data_citizen[$x]->nip));
                    if($save_user['status']){
                        $saved+=1;
                    }else{
                        break;
                    }
                }
            }else{
                $citizen_id=$check_data['id'];
                $current_nip=$check_data['nip'];
                $data_citizen_nip=$data_citizen[$x]->nip;
                
                if($data_citizen[$x]->status){//kalau status aktif pada aplikasi simpeg
                    $bagian=$this->bagianService->getBagianByCutiId($data_citizen[$x]->id_bagian);
                    $check_data->nama=$data_citizen[$x]->nama;
                    $check_data->citizen_id_cuti=$data_citizen[$x]->id;
                    $check_data->nip=$data_citizen[$x]->nip;
                    $check_data->nik=$data_citizen[$x]->nik;
                    $check_data->email=$data_citizen[$x]->email;
                    $check_data->pangkat=$data_citizen[$x]->pangkat;
                    $check_data->pendidikan=$data_citizen[$x]->pendidikan;
                    $check_data->tempat_pendidikan=$data_citizen[$x]->pendidikan;
                    $check_data->tgl_lulus=$data_citizen[$x]->tgl_lulus;
                    $check_data->tempat_lahir=$data_citizen[$x]->tempat_lahir;
                    $check_data->tanggal_lahir=$data_citizen[$x]->tanggal_lahir;
                    $check_data->no_hp=$data_citizen[$x]->no_hp;
                    $check_data->jenis_kelamin=$data_citizen[$x]->jenis_kelamin;
                    // $check_data->id_jabatan=$data_citizen[$x]->id_jabatan;
                    // $check_data->id_bagian=$bagian['id_bagian'];
                    $check_data->satker=$data_citizen[$x]->satker;
                    $check_data->foto=$data_citizen[$x]->foto;
                    $check_data->masuk_kerja=$data_citizen[$x]->masuk_kerja;
                    $check_data->status=$data_citizen[$x]->status;
                    $check_data->synced=true;
                    $save=$check_data->update();
                    if($current_nip !== $data_citizen_nip){
                        $update_user=$this->userService->updateUser(Crypt::encrypt($check_data['id']), Crypt::encrypt($data_citizen[$x]->nip));
                        if(!$update_user['status']){
                            $save=false;
                            break;
                        }
                    }
                }else{//kalau sudah tidak aktif pada aplikasi simpeg
                    $save=true;
                }
                if($save){
                    $saved++;
                }
            }
        }
        if($saved === (int)$jumlah){
            $status=true;
            try{
                DB::beginTransaction();
                $delete_not_sync=User::whereIn('citizen_id', function($query){
                                    $query->select('id')
                                        ->from('citizen')
                                        ->where('synced', false);
                                })
                                ->delete();
                $delete_citizen=Citizen::where('synced', false)->delete();
                DB::commit();
            }catch(\Exception $e){
                DB::rollBack();
            }
            $msg="Berhasil menyimpan data ";
        }else{
            $msg="Beberapa data tidak disimpan / diubah ".$saved." : ".$jumlah;
        }
        return [
            'status'=>$status,
            'msg'=>$msg,
        ];
    }

    public function getDataCitizen(){
        $get_data=Citizen::leftJoin('jabatan', 'jabatan.jabatan_cuti', '=', 'citizen.id_jabatan')
                    ->leftJoin('bagian', 'bagian.id', '=', 'citizen.id_bagian')
                    ->select('citizen.nama', 'citizen.nip', 'jabatan.jabatan', 'bagian.bagian', 'citizen.id as id_citizen')
                    ->where('status', true)
                    ->orderBy('citizen.nama', 'asc')
                    ->get();
        $jumlah=$get_data->count();
        $data=[];
        $x=0;
        foreach($get_data as $citizen){
            $data[$x]['nama']=$citizen['nama'];
            $data[$x]['nip']=$citizen['nip'];
            $data[$x]['jabatan']=$citizen['jabatan'];
            $data[$x]['bagian']=$citizen['bagian'];
            $data[$x]['token']=Crypt::encrypt($citizen['id_citizen']);
            $x++;
        }
        return response()->json(['data'=>$data, 'jumlah'=>$jumlah]);
    }

    public function getAllCitizenForPj(Request $request){
        $data=[];
        $status=false;
        $msg="";
        try{
            $id_cuti_bagian=Crypt::decrypt($request->id_bagian);
            $id_bagian=Crypt::decrypt($request->id_bagian_enc);
            $get_citizen=Citizen::orderBy('nama', 'asc')
                        ->where('status', true)
                        ->get();
            if(isset($get_citizen)){
                $x=0;
                $status=true;
                foreach($get_citizen as $list_citizen){
                    $data[$x]['nama']=$list_citizen['nama'];
                    $data[$x]['nip']=$list_citizen['nip'];
                    $data[$x]['id']=$list_citizen['id'];
                    $x++;
                }
            }
        }catch(DecryptException $e){
            $msg="Invalid token";
        }
        return response()->json(['status'=>$status, 'data'=>$data, 'msg'=>$msg]);
    }

    public function getDataCitizenByBagian(Request $request){
        $data=[];
        $status=false;
        $msg="";
        try{
            $id_cuti_bagian=Crypt::decrypt($request->id_bagian);
            $id_bagian=Crypt::decrypt($request->id_bagian_enc);
            if((int)$id_cuti_bagian > 0){
                $get_data=Bagian::where('id', $id_bagian)->first();
                if(!is_null($get_data)){
                    $get_citizen=Citizen::where('id_bagian', $id_cuti_bagian)
                            ->orderBy('nama', 'asc')
                            ->get();
                }else{
                    $msg="Data bagian tidak ditemukan";
                }
            }else{
                $get_citizen=Citizen::orderBy('nama', 'asc')->get();
            }
            if(isset($get_citizen)){
                $x=0;
                $status=true;
                foreach($get_citizen as $list_citizen){
                    $data[$x]['nama']=$list_citizen['nama'];
                    $data[$x]['nip']=$list_citizen['nip'];
                    $data[$x]['id']=$list_citizen['id'];
                    $x++;
                }
            }
        }catch(DecryptException $e){
            $msg="Invalid token";
        }
        return response()->json(['status'=>$status, 'data'=>$data, 'msg'=>$msg]);
    }

    public function getCitizenDetilById($id_citizen_enc){
        try{
            $id_citizen=Crypt::decrypt($id_citizen_enc);
            $get_data=Citizen::leftJoin('bagian', 'bagian.id', '=', 'citizen.id_bagian')
                        ->select('citizen.nama', 'citizen.id_bagian', 'bagian.id as id_bagian', 'citizen.id_jabatan')
                        ->where('citizen.id', $id_citizen)->first();
            if(!is_null($get_data)){
                $data['nama']=$get_data['nama'];
                $data['id_jabatan']=$get_data['id_jabatan'];
                $data['id_bagian']=$get_data['id_bagian'];
                return response()->json(['data'=>$data, 'status'=>true]);
            }else{
                $msg="Data tidak ditemukan";
            }
        }catch(DecryptException $e){
            $msg="Invalid token";
        }
        return response()->json(['status'=>false, 'msg'=>$msg]);
    }

    public function updateBagianCitizen(Request $request){
        $status=false;
        try{
            $citizen_id=Crypt::decrypt($request->token);
            try{
                $validate=$request->validate([
                    'id_bagian'=>['required']
                ]);
                $id_bagian=$request->id_bagian;

                $update_citizen=$this->citizenService->updateBagianCitizen($citizen_id, $id_bagian);
                if($update_citizen['status']){
                    $status=$update_citizen['status'];
                    $msg="Berhasil mengubah data";
                }else{
                    $msg=$update_citizen['msg'];
                }
            }catch(ValidationException $e){
                $msg=$e->validator->errors()->first();
            }
        }catch(DecryptException $e){
            $msg="Invalid token";
        }
        return response()->json(['status'=>$status, 'msg'=>$msg]);
    }

    
}
