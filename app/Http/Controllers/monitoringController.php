<?php

namespace App\Http\Controllers;

use App\Models\Bagian;
use App\Models\Edoc_indikator;
use App\Models\Master_file_indikator;
use Illuminate\Http\Request;

class monitoringController extends Controller
{
    public function getMonitoring($year){
        $msg="";
        $data=[];
        $get_data=Edoc_indikator::join('master_file_indikator as mfi', 'mfi.id', '=', 'edoc_indikator.id_master')
                            ->join('bagian', 'bagian.id', '=', 'mfi.id_bagian')
                            ->whereRaw('edoc_indikator.edoc is null')
                            ->whereRaw('date(max_fill_at) <= date(now()')
                            ->where('year(max_fill_at)', $year)
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
                    $data[$x]['filename']=$list_data['filename'];
                }else{
                    $y++;
                    $data[$x]['file'][$y]['month']=$list_data['month_name'];
                }

                $index++;
                $id_master_before=$list_data['id_master'];
            }
        }
    }
}
