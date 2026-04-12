<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bagian;
use App\Models\Pj_bagian;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class pjBagianController extends Controller
{
    public function getListPJ(){
        // $get_data=Pj_bagian::leftJoin('bagian', 'bagian.id', '=', 'pj_bagian.id_bagian')
        //             ->join('citizen', 'citizen.id', '=', 'pj_bagian.id_citizen')
        //             ->get();
        // $get_data=Bagian::leftJoin('pj_bagian', function($join){
        //                             $join->on('pj_bagian.id_bagian', '=', 'bagian.id')
        //                             ->where('pj_bagian.active', true);
        //                         })
        //                     ->leftJoin('citizen', 'citizen.id', '=', 'pj_bagian.id_citizen')
        //                     ->select('bagian.bagian', 'citizen.nama', 'pj_bagian.id', 'bagian.cuti_id', 'citizen.id as id_citizen', 'bagian.pj', 'bagian.id as id_bagian')
        //                     ->where('bagian.active', true)
        //                     ->get();

        $get_data=Bagian::leftJoin('pj_bagian as p', function($join){
                                    $join->on('bagian.id', '=', 'p.id_bagian')
                                    ->where('p.active', true);
                                })
                                ->leftJoin('citizen as c', 'c.id', '=', 'p.id_citizen')
                                ->select('bagian.bagian', 'bagian.id as id_bagian')
                                ->selectRaw("GROUP_CONCAT(c.nama order by c.nama SEPARATOR '||') as nama_citizen_pj")
                                ->selectRaw("GROUP_CONCAT(c.id order by c.nama SEPARATOR '||') as id_citizen")
                                ->selectRaw("GROUP_CONCAT(p.id order by c.nama SEPARATOR '||') as id_pj")
                                ->where('bagian.active', true)
                                ->groupBy('bagian.id', 'bagian.bagian')
                                ->get();
        $data=[];
        $x=0;
        foreach($get_data as $list_pj){
            $y=0;//untuk pj
            $data[$x]['bagian']=$list_pj['bagian'];
            $data[$x]['cuti_id_bagian']=Crypt::encrypt($list_pj['cuti_id']);
            $data[$x]['token_bagian']=Crypt::encrypt(($list_pj['id_bagian']));
            if(!is_null($list_pj['nama_citizen_pj'])){
                $nama_pj=explode('||', $list_pj['nama_citizen_pj']);
                $id_citizen_pj=explode('||',  $list_pj['id_citizen']);
                $id_pj=explode('||', $list_pj['id_pj']);
                $jumlah_pj=count($nama_pj);
                if($jumlah_pj > 0){
                    for($y=0;$y<$jumlah_pj;$y++){
                        $data[$x]['pj'][$y]['nama_pj']=$nama_pj[$y];
                        $data[$x]['pj'][$y]['token_pj']=Crypt::encrypt($id_pj[$y]);
                        $data[$x]['pj'][$y]['token_citizen']=Crypt::encrypt($id_citizen_pj[$y]);
                    }
                }
            }else{
                $data[$x]['pj']=null;
            }
            // $data[$x]['nama']=$list_pj['nama'];
            // $data[$x]['id_pj']=$list_pj['id'];
            // $data[$x]['id_citizen']=$list_pj['id_citizen'];
            $x++;
        }
        $jumlah=$get_data->count();
        return response()->json(['data'=>$data, 'jumlah'=>$jumlah]);
    }
    public function savePjBagian(Request $request){
        $msg="Terjadi kesalahan sistem";
        $save=false;
        try{
            $id_bagian=Crypt::decrypt($request->token);
            $get_data=Bagian::where('id', $id_bagian)->first();
            if(!is_null($get_data)){
                $pj_bagian=new Pj_bagian;
                $pj_bagian->id_citizen=$request->id_pj;
                $pj_bagian->id_bagian=$id_bagian;
                $pj_bagian->active=true;
                if($pj_bagian->save()){
                    $save=true;
                    $msg="Berhasil menyimpan data PJ";
                }

            }else{
                $msg="Data bagian tidak ditemukan";
            }
        }catch(DecryptException $e){
            $msg="Invalid token";
        }
        return response()->json(['status'=>$save, 'msg'=>$msg]);
    }

    public function deletePJ(Request $request){
        $delete=false;
        try{
            $id_pj=Crypt::decrypt($request->id_pj_enc);
            $id_citizen=Crypt::decrypt($request->id_citizen_enc);
            $id_bagian=Crypt::decrypt($request->id_bagian_enc);
            $get_data=Pj_bagian::where('id', $id_pj)
                        ->where('id_citizen', $id_citizen)
                        ->where('id_bagian', $id_bagian)
                        ->first();
            if(!is_null($get_data)){
                if($get_data->delete()){
                    $delete=true;
                    $msg="Berhasil menghapus data";
                }else{
                    $msg="Terjadi kesalahan sistem";
                }
            }else{
                $msg="Data tidak ditemukan ";
            }
        }catch(DecryptException $e){
            $msg="Invalid token";
        }
        return response()->json(['status'=>$delete, 'msg'=>$msg]);
    }
}
