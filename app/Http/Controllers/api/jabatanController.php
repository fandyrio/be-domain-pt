<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Jabatan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class jabatanController extends Controller
{
    public function syncJabatan(){
        $api_path=config('costum.api_cuti');
        $path=$api_path."/sync-jabatan";
        $response=HTTP::withToken('Bearer di1Z5eP9N6yNfPYhjU9Op8Dg0JlrJ81jelQYiDErfdBWOe0FcQa8l86E2dFA')->get($path);
        $data=$response->json();
        $save_jabatan=$this->saveJabatan($data);
        
        return $save_jabatan;        
    }

    public function saveJabatan($data){
        $jumlah=$data['jumlah'];
        $jabatan=$data['data'];
        $saved=0;
        $status=false;
        if((int)$jumlah > 0){
            for($x=0;$x<$jumlah;$x++){
                $jabatan_code="jab-0".$jabatan[$x]['id'];
                $check=Jabatan::where('jabatan_code', $jabatan_code)->first();
                if(is_null($check)){
                    $new_jabatan=new Jabatan;
                    $new_jabatan->jabatan=$jabatan[$x]['jabatan'];
                    $new_jabatan->jabatan_code=$jabatan_code;
                    $new_jabatan->jabatan_cuti=$jabatan[$x]['id'];
                    $new_jabatan->active=true;
                    $save=$new_jabatan->save();
                    if($save){
                        $saved++;
                    }
                }else{
                    $check->jabatan=$jabatan[$x]['jabatan'];
                    $save=$check->update();
                    if($save){
                        $saved++;
                    }
                }
            }
        }
        if($saved === $jumlah){
            $status=true;
            $msg="Berhasil menyimpan data";
        }else{
            $msg="Terjadi kesalahan saat menyimpan data";
        }

        return [
            'status'=>true,
            'msg'=>$msg
        ];
    }

    public function getAllJabatan(){
        $data_jabatan=Jabatan::all();
        $x=0;
        $data=[];
        foreach($data_jabatan as $jabatan){
            $data[$x]['id']=$jabatan['id'];
            $data[$x]['jabatan']=$jabatan['jabatan'];
            $data[$x]['jabatan_code']=Crypt::encrypt($jabatan['jabatan_code']);
            $data[$x]['active']=(int)$jabatan['active'];
            $x++;
        }

        return response()->json(['data'=>$data, 'jumlah'=>$data_jabatan->count()]);
    }

    public function updateStatusJabatan(Request $request){
        $update=false;
        try{
            $jabatan_code=Crypt::decrypt($request->jabatan_code_enc);
            $active=(boolean)$request->status;
            $get_data=Jabatan::where('jabatan_code', $jabatan_code)->first();
            if(is_null($get_data)){
                $msg="Data tidak ditemukan";
            }else{
                $get_data->active=$active;
                $update=$get_data->update();
                if($update){
                    $msg="Berhasil mengubah status";
                }else{
                    $msg="Terjadi kesalahan sistem";
                }
            }
        }catch(DecryptException $e){
            $msg="Invalid token";
        }
        return response()->json(['status'=>$update, 'msg'=>$msg]);
    }
}
