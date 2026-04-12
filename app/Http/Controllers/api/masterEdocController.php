<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Master_file_indikator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Services\ManagementService;

class masterEdocController extends Controller
{
    private $management_service;

    public function __construct(ManagementService $management_service){
        $this->management_service=$management_service;
    }

    public function getEdocMaster(Request $request, $id_bagian_enc, $tahun){
        $status=false;
        $msg="";
        $jumlah=0;
        $tree=[];
        $isPj=false;
        
        $get_data_tree=$this->management_service->getDataChecklistBagian($request->user()->citizen_id, $id_bagian_enc, $tahun);
        if($get_data_tree['status']){
            $status=true;
            $jumlah=$get_data_tree['total'];
            $tree=$get_data_tree['data_tree'];
            $isPj=$get_data_tree['isPj'];

        }else{
            $msg=$get_data_tree['msg'];
        }
        return response()->json(['status'=>$status, 'total'=>$jumlah, 'tree'=>$tree, 'id_bagian'=>Crypt::decrypt($id_bagian_enc), 'msg'=>$msg, 'current_year'=>$tahun, 'isPj'=>$isPj]);
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
    
}
