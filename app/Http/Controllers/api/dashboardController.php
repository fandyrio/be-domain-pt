<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Services\dashboardService;
use Illuminate\Http\Request;

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
}
