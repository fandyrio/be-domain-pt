<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Edoc_indikator;
use App\Models\Bagian;
use App\Models\Master_file_indikator;
use App\Models\Msg_sent;
use App\Services\bagianService;
use Illuminate\Http\Request;
use App\Services\MonitoringService;
use App\Services\WaService; 
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class monitoringController extends Controller
{
    protected $monitoringService;
    protected $bagianService;
    protected $waService;


    public function __construct(MonitoringService $monitoring_service, bagianService $bagian_service, WaService $wa_service){
        $this->monitoringService=$monitoring_service;
        $this->bagianService=$bagian_service;
        $this->waService=$wa_service;
    }

    public function dataTunggakan($year, $month){
        $tunggakan_bagian=[];$status=false;
        $saved=0;
        $get_data=$this->monitoringService->getMonitoringData($year);
        $data=$get_data['data'];
        $total=$get_data['total'];
        if($total > 0){
            $get_pj=$this->bagianService->getPJBagian();
            $total_pj=$get_pj['total'];
            $data_pj=$get_pj['data'];
            if($total_pj > 0){
                $generateReport=$this->monitoringService->generateReportTunggakan($total_pj, $total, $data_pj, $data);
                $data_report=$generateReport['data'];
                $jlh_data=count($data_report);
                if($jlh_data > 0){
                    $must_save=0;
                    for($x=0;$x<$jlh_data;$x++){
                        $msg="Tunggakan File SIPENA yang belum diupload: \n";
                        if(isset($data_report[$x]['tunggakan']) && count($data_report[$x]['tunggakan']) > 0){
                            $must_save+=1;
                            for($y=0;$y< count($data_report[$x]['tunggakan']);$y++){
                                $msg.="\r";
                                $msg.=$data_report[$x]['tunggakan'][$y]."\n";
                            }
                            $save_msg=$this->monitoringService->saveMsg($msg, $data_report[$x]['no_hp'], $data_report[$x]['nama'], $month, $year);
                            if($save_msg){
                                $saved+=1;
                            }
                            // echo $data_report[$x]['nama']."<br />".$data_report[$x]['no_hp'];
                            // $send_wa=$this->waService->sendMsg($msg, $data_report[$x]['no_hp'], $data_report[$x]['nama']);
                        }
                    }
                    if($saved === $must_save){
                        $status=true;
                        $msg="success";
                    }else{
                        $msg="Pesan tidak seluruh nya terinput ".$saved.":".$jlh_data;
                    }
                    return [
                        'status'=>$status,
                        'msg'=>$msg,
                    ];
                }
                // return $data_report;
            }else{
                $msg="PJ Belum disetting";
            }
        }else{
            $msg="Tidak ada tunggakan";
        }

        return [
            'status'=>$status,
            'msg'=>$msg
        ];
        
    }

    public function getMsgSent($selected_month, $year){
        $status=true;
        $msg="";
        // $this_year=date('Y');
        $data=array();
        $jumlah=0;
        $this_month=date('n');
        $get_data=Msg_sent::where('month', $selected_month)
                        ->where('year', $year)    
                        ->get();
        $jumlah=$get_data->count();
        if($jumlah === 0 && (int)$selected_month ===(int) $this_month){
            $status=false;
            $generate_data=$this->dataTunggakan($year, $selected_month);
            $status=$generate_data['status'];
            if($status === true){
                $msg="Genereting data ... ".$status;
            }else{
                $msg=$generate_data['msg'];
            }
        }

        if($status){
            $get_data=$this->monitoringService->getMsgSent($selected_month, $year);
            $data=$get_data['data'];
            $jumlah=$get_data['jumlah'];
        }

        return response()->json(['status'=>$status, 'msg'=>$msg, 'data'=>$data, 'jumlah'=>$jumlah, 'selected_month'=>$selected_month.":".$this_month, 'year'=>$year]);
    }
    
    public function sendMsgTunggakan(Request $request){
        $status=false;
        try{
            $token_msg=Crypt::decrypt($request->token_msg);
            $send_msg=$this->monitoringService->sendMsgTunggakan($token_msg);
            if($send_msg['status'] === true){
                $status=$send_msg['status'];
            }
            $msg=$send_msg['msg'];
        }catch(DecryptException $e){
            $msg="Invalid token";
        }

        return response()->json(['status'=>$status, 'msg'=>$msg]);
    }
}
