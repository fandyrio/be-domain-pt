<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Master_file_indikator;
use App\Services\bagianService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Services\ManagementService;
use Illuminate\Validation\ValidationException;

class masterEdocController extends Controller
{
    private $management_service;
    private $bagianService;

    public function __construct(ManagementService $management_service, bagianService $bagian_service){
        $this->management_service=$management_service;
        $this->bagianService=$bagian_service;
    }

    public function getEdocMaster(Request $request, $id_bagian_enc, $tahun){
        $status=false;
        $msg="";
        $jumlah=0;
        $tree=[];
        $isPj=false;
        $perbaikan_aktif = false;
        
        $get_data_tree=$this->management_service->getDataChecklistBagian($request->user()->citizen_id, $id_bagian_enc, $tahun);
        if($get_data_tree['status']){
            $status=true;
            $jumlah=$get_data_tree['total'];
            $tree=$get_data_tree['data_tree'];
            $isPj=$get_data_tree['isPj'];
            $isAdmin = $get_data_tree['isAdmin'];
            $perbaikan_aktif = $get_data_tree['perbaikan_aktif'];
            $periode_perbaikan = $get_data_tree['periode_perbaikan'];
            if($isAdmin){
                $isPj = true;
            }
        }else{
            $msg=$get_data_tree['msg'];
        }
        return response()->json(['status'=>$status, 'total'=>$jumlah, 'tree'=>$tree, 'id_bagian'=>Crypt::decrypt($id_bagian_enc), 'msg'=>$msg, 'current_year'=>$tahun, 'isPj'=>$isPj, 'perbaikan_aktif'=>$perbaikan_aktif, 'periode_perbaikan'=>$periode_perbaikan]);
    }

    public function updateEdocMaster(Request $request){
        $update_master=$this->management_service->updateEdocMaster($request);
        return response()->json(['status'=>$update_master['status'], 'msg'=>$update_master['msg']]);        
    }
    public function deleteEdocMaster(Request $request){
        $delete_master=$this->management_service->deleteMaster($request);
        return response()->json(['status'=>$delete_master['status'], 'msg'=>$delete_master['msg']]);
    }
    public function updateFileEdoc(Request $request){
        $update_edoc=$this->management_service->updateDataEdoc($request);
        return response()->json(['status'=>$update_edoc['status'], 'msg'=>$update_edoc['msg']]);
    }

    public function deleteFileEdoc(Request $request){
        $id_edoc=$request->id_file_enc;
        $delete_file=$this->management_service->deleteFileEdoc($id_edoc);
        return response()->json(['status'=>$delete_file['status'], 'msg'=>$delete_file['msg']]);
    }

    public function getEffectedBagian(Request $request){
        $list_bagian = null;
        $status = false;
        $id = null;
        $bagian = null;
        $msg = "Data Bagian terkait tidak ditemukan untuk checklist ini";
        try{
            $request->validate([
                'parent'=>['required', 'string'],
                'tahun'=>['required', 'digits: 4'],
                'level'=>['required', 'digits: 1']
            ]);
            try{
                $parent_id = Crypt::decrypt($request->parent);
                $level = $request->level;
                $tahun = $request->tahun;
                // $tahun = null;
                $list_bagian = $this->management_service->getEffectedBagian($parent_id, $level, $tahun);
                $jumlah = $list_bagian['jumlah'];
                if($jumlah > 0){
                    $bagian = $list_bagian['data'];
                    $status = true;
                    $msg = "Data Found";
                }
            }catch(DecryptException $e){
                $msg = "Invalid token";
            }
        }catch(ValidationException $e ){
            $msg = $e->validator->errors()->first();
        }
        return response()->json(['status'=>$status, 'msg'=>$msg, 'bagian'=>$bagian, 'token'=>$id]);
    }

    public function toArray($str_arr){
        $arr = [];
        $replace = str_replace(array("[", "]"), "", $str_arr);
        $explode = explode(",", $replace);
        for($x=0;$x<count($explode);$x++){
            array_push($arr, $explode[$x]);
        }
        return $arr;
    }

    public function getPerbaikanDetil(Request $request){
        $status = false;
        $data = [];
        try{
            $request->validate([
                'id_perbaikan_enc'=>['required', 'string']
            ]);
            $explode = explode("-", $request->id_perbaikan_enc);
            if(count($explode) !== 2){
                return response()->json(['status'=>false, 'msg'=>'Data tidak valid '.count($explode)]);
            }
            
            try{
                $perbaikan_id = Crypt::decrypt($explode[0]);
                $indikator_lvl_id = Crypt::decrypt($explode[1]);
                $get_data = $this->management_service->getPerbaikanChecklistDetil($perbaikan_id, $indikator_lvl_id);
                if(!is_null($get_data)){
                    $id_bagian_arr = $this->toArray($get_data->id_bagian);
                    $get_bagian = $this->bagianService->getBagianByIdBundle($id_bagian_arr); 
                    // if(count($get_bagian) === 0){
                    //     $msg='Data tidak lengkap';
                    // }else{
                        $data = [
                            "catatan"=>$get_data->catatan,
                            "periode_perbaikan"=>$get_data->periode_perbaikan_id,
                            "effected_bagian"=>$get_bagian,
                        ];
                        $status = true;
                        $msg = "Data Found";
                    // }
                }else{
                    $msg = "Data tidak ditemukan";
                }
                
            }catch(DecryptException $e){
                $msg='Invalid token';
            }

        }catch(ValidationException $e){
            $msg = $e->validator->errors()->first();
        }

        return response()->json(['status'=>$status, 'msg'=>$msg, 'data'=>$data]);
    }
    
}
