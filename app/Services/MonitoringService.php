<?php
    namespace App\Services;
    use App\Models\Edoc_indikator;
    use App\Models\Msg_sent;
    use App\Models\Bagian;
    use App\Models\Master_file_indikator;
    use Illuminate\Support\Facades\Crypt;
    use App\Services\WaService;


    class MonitoringService{
        protected $waService;
        public function __construct(WaService $wa_service){
            $this->waService=$wa_service;
        }

        public function getMonitoringData($year){
            
            $msg="";
            $data=[];
            $get_data=Edoc_indikator::join('master_file_indikator as mfi', 'mfi.id', '=', 'edoc_indikator.id_master')
                                ->join('bagian', 'bagian.id', '=', 'mfi.id_bagian')
                                ->whereRaw('edoc_indikator.edoc is null')
                                ->whereRaw('date(max_fill_at) <= date(now())')
                                ->whereRaw('year(max_fill_at) = '.$year)
                                ->select('mfi.file_name', 'mfi.id as id_master', 'bagian.bagian', 'bagian.id as id_bagian')
                                ->selectRaw('monthname(max_fill_at) as month_name')
                                ->get();
            if($get_data->count() > 0){
                $x=0;$y=0;
                $id_master_before=null;
                $index=0;
                foreach($get_data as $list_data){
                    if($id_master_before !== $list_data['id_master']){
                        if($index > 0){
                            $x++;
                        }
                        $y=0;
                        $data[$x]['id_master']=$list_data['id_master'];
                        $data[$x]['id_bagian']=$list_data['id_bagian'];
                        $data[$x]['filename']=$list_data['file_name'];
                        $data[$x]['file'][$y]['month']=$list_data['month_name'];
                    }else{
                        $y++;
                        $data[$x]['file'][$y]['month']=$list_data['month_name'];
                    }
    
                    $index++;
                    $id_master_before=$list_data['id_master'];
                }
            }
    
            return [
                'data'=>$data,
                'total'=>$x,
            ];
        }

        public function generateReportTunggakan($total_pj, $total_tunggakan, $data_pj, $data_tunggakan){
            for($a=0;$a<$total_pj;$a++){
                $index[$a]=0;
            }
            
            for($x=0;$x<$total_tunggakan;$x++){
                for($y=0;$y<$total_pj;$y++){
                    if((int)$data_pj[$y]['id_bagian'] === (int)$data_tunggakan[$x]['id_bagian']){
                        $data_pj[$y]['tunggakan'][$index[$y]]=$data_tunggakan[$x]['filename'];
                        $bulan="";
                        if(count($data_tunggakan[$x]['file']) > 0){
                            for($b=0;$b<count($data_tunggakan[$x]['file']);$b++){
                                $bulan.=$data_tunggakan[$x]['file'][$b]['month'].", ";
                            }
                        }
                        $data_pj[$y]['tunggakan'][$index[$y]]=$data_tunggakan[$x]['filename']." Periode ".$bulan;
                        $index[$y]++;
                    }
                }
            }
            return [
                'data'=>$data_pj
            ];
        }

        public function saveMsg($msg, $reciver, $nama, $month, $year){
            $msg_sent=new Msg_sent;
            $msg_sent->msg=$msg;
            $msg_sent->status=false;
            $msg_sent->nama_penerima=$nama;
            $msg_sent->no_penerima=$reciver;
            $msg_sent->year=date('Y');
            $msg_sent->month=$month;
            $msg_sent->year=$year;
            return $msg_sent->save();
        }

        public function getMsgSent($month, $year){
            $data=array();
            $get_data=Msg_sent::where('month', $month)
                            ->where('year', $year)
                            ->get();
            $jumlah=$get_data->count();
            if($jumlah > 0){
                $x=0;
                foreach($get_data as $list_data){
                    $data[$x]['msg']=str_replace("\r", "<br />", $list_data['msg']);
                    $data[$x]['status']=(int)$list_data['status'];
                    $data[$x]['nama_penerima']=$list_data['nama_penerima'];
                    $data[$x]['no_penerima']=$list_data['no_penerima'];
                    $data[$x]['token_msg']=Crypt::encrypt($list_data['id']);
                    $data[$x]['sent_at']=date('d M Y H:i', strtotime($list_data['sent_at']));
                    $x++;
                }
            }

            return [
                'data'=>$data,
                'jumlah'=>$jumlah
            ];
        }

        public function sendMsgTunggakan($msg_id){
            $status=false;
            $get_data=Msg_sent::where('id', $msg_id)
                        ->where('status', false)
                        ->first();
            if(!is_null($get_data)){
                $no_penerima=$get_data['no_penerima'];
                $msg=$get_data['msg'];
                $nama_penerima=$get_data['nama_penerima'];
                $send_msg=$this->waService->sendMsg($msg, $no_penerima, $nama_penerima);
                if($send_msg === "success"){
                    $update_status=$this->updateStatusKirimMsg($msg_id);
                    if($update_status){
                        $status=true;
                        $msg="Berhasil mengirimkan Pesan";
                    }else{
                        $msg="Terjadi kesalahan sistem saat mengubah status pesan";
                    }
                }else{
                    $msg="Terjadi kesalahan sistem saat mengirimkan pesan";
                }
            }else{
                $msg="Data tidak ditemukan";
            }

            return [
                'status'=>$status,
                'msg'=>$msg
            ];
        }

        public function updateStatusKirimMsg($id_msg){
            $get_data=Msg_sent::where('id', $id_msg)
                            ->where('status', false)
                            ->first();
            $get_data->status=true;
            $get_data->sent_at=date('Y-m-d H:i:s');
            return $get_data->update();
        }
    }

?>