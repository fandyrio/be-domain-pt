<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Services\dashboardService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class dashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(dashboardService $dashboard_service)
    {
        // throw new \Exception('Not implemented');
        $this->dashboardService = $dashboard_service;
    }

    public function getDataChecklistTahunan($tahun){
        $status = false;
        $msg = "Data tidak ditemukan";
        $get_data = $this->dashboardService->getDataDashboard($tahun);
        $jumlah_perbagian = count($get_data['jumlah_perbagian']);
        if($jumlah_perbagian > 0){
            $status = true;
            $msg = "Data Found";
        }

        return response()->json(['status'=>$status, 'msg'=>$msg, 'data'=>$get_data]);
    }

    public function getListEdocBelumDiisi($tahun, $id_bagian){
        $status = true;
        $msg = "";
        $data = [];
        try{
            $id_bagian_int = Crypt::decrypt($id_bagian);
            $get_data = $this->dashboardService->getListEdocBelumDiisi($id_bagian_int, $tahun);
            $jumlah = $get_data['jumlah'];
            $data = $get_data['data'];
            if($jumlah === 0){
                $status = false;
                $msg = "Data tidak ditemukan";
            }
        }catch(DecryptException $e){
            $msg = "Invalid token";
        }
        return response()->json(['status'=>$status, 'msg'=>$msg, 'data'=>$data]);
    }

    public function getListEdocBelumPeriode($tahun, $id_bagian){
        $status = true;
        $msg = "";
        $data = [];
        try{
            $id_bagian_int = Crypt::decrypt($id_bagian);
            $get_data = $this->dashboardService->getListEdocBelumPeriode($id_bagian_int, $tahun);
            $jumlah = $get_data['jumlah'];
            $data = $get_data['data'];
            if($jumlah === 0){
                $status = false;
                $msg = "Data tidak ditemukan";
            }
        }catch(DecryptException $e){
            $msg = "Invalid token";
        }
        return response()->json(['status'=>$status, 'msg'=>$msg, 'data'=>$data]);
    }
}
