<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ampuh_indikator;
use App\Models\Ampuh_sub_indikator_lvl1;
use App\Models\Ampuh_sub_indikator_lvl2;
use App\Models\Ampuh_sub_indikator_lvl3;
use App\Models\Ampuh_sub_indikator_lvl4;
use App\Models\Ampuh_sub_indikator_lvl5;
use App\Models\Edoc_indikator;
use App\Models\Indikator_user;
use App\Models\Indikator_lvl1_user;
use App\Models\Indikator_lvl2_user;
use App\Models\Indikator_lvl3_user;
use App\Models\Indikator_lvl4_user;
use App\Models\Indikator_lvl5_user;
use App\Models\Master_file_indikator;
use App\Models\Perbaikan_checklist;
use App\Models\Periode_perbaikan;
use Illuminate\Support\Facades\DB;


class homeController extends Controller
{
    public function getDataChecklist($year){
        $id_perbaikan = null;
        $explode = explode("-", $year);
        if(count($explode) === 2){
            $year = $explode[0];
            // $year = 2021;
            $id_perbaikan = $explode[1];
        }

        if(is_null($id_perbaikan)){
            $get_data=Ampuh_indikator::join('indikator_user as iu', 'iu.id_indikator', '=', 'ampuh_indikator.id')
                            ->leftJoin('ampuh_sub_indikator_lvl1 as lvl1', function($join){
                                            $join->on('lvl1.indikator_id', '=', 'ampuh_indikator.id')
                                                ->where(function($w){
                                                    $w->whereRaw('lvl1.rule_id is null')
                                                    ->orWhere('lvl1.rule_id', 1);
                                                });
                                        })
                            ->leftJoin('ampuh_sub_indikator_lvl2 as lvl2', function($join){
                                            $join->on('lvl2.sub_indikator_lvl1_id', '=', 'lvl1.id')
                                                ->where(function($w){
                                                    $w->whereRaw('lvl2.rule_id is null')
                                                    ->orWhere('lvl2.rule_id', 1);
                                                });
                                        })
                            ->leftJoin('ampuh_sub_indikator_lvl3 as lvl3', function($join){
                                            $join->on('lvl3.parent_id', '=', 'lvl2.id')
                                                ->where(function($w){
                                                    $w->whereRaw('lvl3.rule_id is null')
                                                        ->orWhere('lvl3.rule_id', 1);
                                                });
                                        })
                            ->leftJoin('ampuh_sub_indikator_lvl4 as lvl4', function($join){
                                            $join->on('lvl4.parent_id', '=', 'lvl3.id')
                                                ->where(function($w){
                                                    $w->whereRaw('lvl4.rule_id is null')
                                                    ->orWhere('lvl4.rule_id', 1);
                                                });
                                        })
                            ->leftJoin('indikator_lvl4_user as lvl4_user', 'lvl4_user.id_indikator_lvl4', '=', 'lvl4.id')
                            ->select('ampuh_indikator.id as id_parent', 'ampuh_indikator.indikator_name', 'lvl1.id as id_lvl1', 'lvl1.sub_indikator_name as lvl1', 'lvl1.is_folder_bagian as bagian_lvl1', 'lvl2.id as id_lvl2', 'lvl2.sub_indikator_name as lvl2', 'lvl2.is_folder_bagian as bagian_lvl2', 'lvl3.id as id_lvl3', 'lvl3.sub_indikator_name as lvl3', 'lvl3.is_folder_bagian as bagian_lvl3', 'lvl4.id as id_lvl4', 'lvl4.sub_indikator_name as lvl4', 'lvl4.is_folder_bagian as bagian_lvl4', 
                                DB::raw('(CASE
                                        WHEN lvl2.is_folder_bagian = true then (SELECT id from bagian where bagian = lvl2)
                                        WHEN lvl3.is_folder_bagian = true then (SELECT id from bagian where bagian = lvl3)
                                        WHEN lvl4.is_folder_bagian = true then (SELECT id from bagian where bagian = lvl4)
                                        ELSE null END
                                ) as id_bagian'),
                                DB::raw('(CASE
                                    WHEN lvl2.is_folder_bagian = true then (SELECT id from indikator_lvl2_user where id_indikator_lvl2 = id_lvl2 and tahun="'.$year.'")
                                    WHEN lvl3.is_folder_bagian = true then (SELECT id from indikator_lvl3_user where id_indikator_lvl3 = id_lvl3 and tahun="'.$year.'")
                                    WHEN lvl4.is_folder_bagian = true then (SELECT id from indikator_lvl4_user where id_indikator_lvl4 = id_lvl4 and tahun="'.$year.'")
                                    ELSE null END
                        ) as id_indikator')
                            )
                            ->whereRaw("ampuh_indikator.indikator_name not like '%00.%'")
                            ->where('iu.tahun', $year)
                            // ->orderBy('ampuh_indikator.indikator_name', 'asc')
                            ->orderByRaw("CAST(SUBSTRING_INDEX(ampuh_indikator.indikator_name, '.', 1) as unsigned) ASC")
                            ->orderBy('lvl1', 'asc')
                            ->orderBy('lvl2', 'asc')
                            ->orderBy('lvl3', 'asc')
                            ->get();
        }else{
            $get_data=Ampuh_indikator::join('indikator_user as iu', 'iu.id_indikator', '=', 'ampuh_indikator.id')
                            ->leftJoin('ampuh_sub_indikator_lvl1 as lvl1', function($join){
                                            $join->on('lvl1.indikator_id', '=', 'ampuh_indikator.id')
                                                ->where(function($w){
                                                    $w->whereRaw('lvl1.rule_id is null')
                                                    ->orWhere('lvl1.rule_id', 1);
                                                });
                                        })
                            ->leftJoin('ampuh_sub_indikator_lvl2 as lvl2', function($join){
                                            $join->on('lvl2.sub_indikator_lvl1_id', '=', 'lvl1.id')
                                                ->where(function($w){
                                                    $w->whereRaw('lvl2.rule_id is null')
                                                    ->orWhere('lvl2.rule_id', 1);
                                                });
                                        })
                            ->leftJoin('ampuh_sub_indikator_lvl3 as lvl3', function($join){
                                            $join->on('lvl3.parent_id', '=', 'lvl2.id')
                                                ->where(function($w){
                                                    $w->whereRaw('lvl3.rule_id is null')
                                                        ->orWhere('lvl3.rule_id', 1);
                                                });
                                        })
                            ->leftJoin('ampuh_sub_indikator_lvl4 as lvl4', function($join){
                                            $join->on('lvl4.parent_id', '=', 'lvl3.id')
                                                ->where(function($w){
                                                    $w->whereRaw('lvl4.rule_id is null')
                                                    ->orWhere('lvl4.rule_id', 1);
                                                });
                                        })
                            ->leftJoin('indikator_lvl4_user as lvl4_user', 'lvl4_user.id_indikator_lvl4', '=', 'lvl4.id')
                            ->join("perbaikan_checklist as pc", "pc.indikator_lvl_id", "=", "lvl1.id")
                            ->join("periode_perbaikan as pp", function($join) use($id_perbaikan){
                                $join->on("pp.id", "=", "pc.periode_perbaikan_id")
                                        ->where("pp.id", $id_perbaikan);
                            })
                            ->select('ampuh_indikator.id as id_parent', 'ampuh_indikator.indikator_name', 'lvl1.id as id_lvl1', 'lvl1.sub_indikator_name as lvl1', 'lvl1.is_folder_bagian as bagian_lvl1', 'lvl2.id as id_lvl2', 'lvl2.sub_indikator_name as lvl2', 'lvl2.is_folder_bagian as bagian_lvl2', 'lvl3.id as id_lvl3', 'lvl3.sub_indikator_name as lvl3', 'lvl3.is_folder_bagian as bagian_lvl3', 'lvl4.id as id_lvl4', 'lvl4.sub_indikator_name as lvl4', 'lvl4.is_folder_bagian as bagian_lvl4', 
                                DB::raw('(CASE
                                        WHEN lvl2.is_folder_bagian = true then (SELECT id from bagian where bagian = lvl2)
                                        WHEN lvl3.is_folder_bagian = true then (SELECT id from bagian where bagian = lvl3)
                                        WHEN lvl4.is_folder_bagian = true then (SELECT id from bagian where bagian = lvl4)
                                        ELSE null END
                                ) as id_bagian'),
                                DB::raw('(CASE
                                    WHEN lvl2.is_folder_bagian = true then (SELECT id from indikator_lvl2_user where id_indikator_lvl2 = id_lvl2 and tahun="'.$year.'")
                                    WHEN lvl3.is_folder_bagian = true then (SELECT id from indikator_lvl3_user where id_indikator_lvl3 = id_lvl3 and tahun="'.$year.'")
                                    WHEN lvl4.is_folder_bagian = true then (SELECT id from indikator_lvl4_user where id_indikator_lvl4 = id_lvl4 and tahun="'.$year.'")
                                    ELSE null END
                        ) as id_indikator')
                            )
                            ->whereRaw("ampuh_indikator.indikator_name not like '%00.%'")
                            ->where('iu.tahun', $year)
                            // ->orderBy('ampuh_indikator.indikator_name', 'asc')
                            ->orderByRaw("CAST(SUBSTRING_INDEX(ampuh_indikator.indikator_name, '.', 1) as unsigned) ASC")
                            ->orderBy('lvl1', 'asc')
                            ->orderBy('lvl2', 'asc')
                            ->orderBy('lvl3', 'asc')
                            ->get();
        }

        $data=[];
        $id_parent_before=null;
        $id_lvl1_before=null;
        $id_lvl2_before=null;
        $id_lvl3_before=null;
        $id_lvl4_before=null;
        $id_lvl5_before=null;
        $x=0;
        $index=0;
        $blm_upload_perbaikan = 0;
        $sudah_upload_perbaikan = 0;
        foreach($get_data as $list){
            $change_y=true;
            $change_z=true;
            $change_z1=true;
            if((int)$id_parent_before !== (int)$list['id_parent'] || $id_parent_before === null){
                if($index > 0){
                    $x++;
                    $change_y=false;
                    $change_z=false;
                    $change_z1=false;
                }
                $y=0;$z=0;$z1=0;
                $data[$x]['parent_id']=$list['id_parent'];
                $data[$x]['parent_name']=$list['indikator_name'];
            }

            if($id_lvl1_before !== (int)$list['id_lvl1']){
                if($index > 0 && $change_y === true ){
                    $y++;
                    $change_z=false;
                }
                $z=0;$z1=0;
                $get_catatan = $this->getCatatanPerbaikan($year, $list['id_lvl1'], $id_perbaikan);
                
                $data[$x]['lvl1'][]=[
                    'id_lvl1'=>$list['id_lvl1'],
                    'nama_lvl1'=>$list['lvl1'],
                    'perbaikan'=>count($get_catatan) > 0 ? true : false,
                    'catatan_perbaikan'=>$get_catatan,
                ];
                
            }

            if($id_lvl2_before !== (int)$list['id_lvl2']){
                if($index > 0 && $change_z === true){
                    $z++;
                }
                $edoc=null;
                $filename=null;
                $id_indikator_lvl2=$list['id_indikator'];
                if((int)$list['bagian_lvl2'] === 1 && !is_null($id_indikator_lvl2)){
                    if(!is_null($id_perbaikan)){
                        $jumlah_catatan = count($get_catatan);
                        $selected_id_perbaikan = null;
                        for($jc=0;$jc<$jumlah_catatan;$jc++){
                            if((int)$get_catatan[$jc]['id_perbaikan'] === (int)$id_perbaikan){
                                $selected_id_perbaikan=$jc;
                            }
                        }
                        if(in_array($list['id_bagian'], $get_catatan[$selected_id_perbaikan]['id_bagian'])){
                            $get_edoc=$this->generateEdocFile($id_indikator_lvl2, 2, $id_perbaikan);
                            $blm_upload_perbaikan+=$get_edoc['blm_upload_perbaikan'];
                            $sudah_upload_perbaikan+=$get_edoc['sudah_upload_perbaikan'];
                            $data[$x]['lvl1'][$y]['lvl2'][]=[
                                'id_lvl2'=>$list['id_lvl2'],
                                'nama_lvl2'=>$list['lvl2'],
                                'judul'=>true,
                                'edoc'=>$get_edoc['edoc'],
                                'id_bagian'=>$get_catatan,
                                'this_id_bagian'=>$list['id_bagian']
                            ];
                         }
                    }else{
                        $get_edoc=$this->generateEdocFile($id_indikator_lvl2, 2);
                        $data[$x]['lvl1'][$y]['lvl2'][]=[
                            'id_lvl2'=>$list['id_lvl2'],
                            'nama_lvl2'=>$list['lvl2'],
                            'judul'=>true,
                            'edoc'=>$get_edoc['edoc'],
                            'id_bagian'=>$get_catatan,
                            'this_id_bagian'=>$list['id_bagian']
                        ];
                    }
                }else{
                    $data[$x]['lvl1'][$y]['lvl2'][]=[
                        'id_lvl2'=>$list['id_lvl2'],
                        'nama_lvl2'=>$list['lvl2'],
                        'judul'=>null,
                    ];
                }
            }

            if($id_lvl3_before !== (int)$list['id_lvl3'] && !is_null($list['id_lvl3'])){
                if($index > 0 && $change_z1 === true){
                    $z1++;
                }
                $id_indikator_lvl3=$list['id_indikator'];
                if((int)$list['bagian_lvl3'] === 1 && !is_null($id_indikator_lvl3)){
                    $get_edoc=$this->generateEdocFile($id_indikator_lvl3, 3);
                    $data[$x]['lvl1'][$y]['lvl2'][$z]['lvl3'][]=[
                        'id_lvl3'=>$list['id_lvl3'],
                        'nama_lvl3'=>$list['lvl3'],
                        'judul'=>$get_edoc['filename'],
                        'edoc'=>$get_edoc['edoc'],
                    ];
                }else{
                    $data[$x]['lvl1'][$y]['lvl2'][$z]['lvl3'][]=[
                        'id_lvl3'=>$list['id_lvl3'],
                        'nama_lvl3'=>$list['lvl3'],
                    ];
                }
            }
            

            $id_parent_before=(int)$list['id_parent'];
            $id_lvl1_before=(int)$list['id_lvl1'];
            $id_lvl2_before=(int)$list['id_lvl2'];
            $id_lvl3_before=(int)$list['id_lvl3'];
            $index++;
        }
        //return response()->json(['data'=>$get_data]);
        return [
            'data'=>$data,
            'blm_upload_perbaikan'=>$blm_upload_perbaikan,
            'sudah_upload_perbaikan'=>$sudah_upload_perbaikan,
            'is_perbaikan'=>is_null($id_perbaikan) ? false : true
        ];
    }

    public function getData(){
        // $data=[];
        // $data=$this->getDataChecklist();
        // return view('list', ['data'=>$data['data'], 'no'=>1]);
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

     public function getCatatanPerbaikan($current_year, $indikator_lvl, $id_perbaikan){
        $data = [];
        if(!is_null($id_perbaikan)){
            $get_data = Perbaikan_checklist::join('periode_perbaikan as pp', 'pp.id', '=', 'perbaikan_checklist.periode_perbaikan_id')
                                ->select("perbaikan_checklist.catatan", "pp.periode", 'perbaikan_checklist.id_bagian', 'pp.id as id_perbaikan')
                                ->where("pp.tahun", $current_year)
                                ->where("perbaikan_checklist.indikator_lvl_id", $indikator_lvl)
                                ->where('pp.id', $id_perbaikan)
                                ->get();
        }else{
            $get_data = Perbaikan_checklist::join('periode_perbaikan as pp', 'pp.id', '=', 'perbaikan_checklist.periode_perbaikan_id')
                                ->select("perbaikan_checklist.catatan", "pp.periode", 'perbaikan_checklist.id_bagian', 'pp.id as id_perbaikan')
                                ->where("pp.tahun", $current_year)
                                ->where("perbaikan_checklist.indikator_lvl_id", $indikator_lvl)
                                ->get();
        }
        

        foreach($get_data as $list){
            $data[] = [
                'id_perbaikan'=>$list['id_perbaikan'],
                'periode'=>$list['periode'],
                'catatan'=>$list['catatan'],
                'id_bagian'=>$this->toArray($list['id_bagian'])
            ];
        }

        return $data;
    }

    public function generateEdocFile($id_indikator, $level, $id_perbaikan = null){
        $blm_upload_perbaikan = 0;
        $sudah_upload_perbaikan = 0;
        $edoc=null;
        $is_perbaikan = false;
        $filename=null;
        $string_lvl=['A', 'B', 'C', 'D', 'E', 'F'];
        $perlu_dok_perbaikan = false;
        if(is_null($id_perbaikan)){
            $get_data_indikator=Master_file_indikator::where('file_code', $string_lvl[$level-1].'-'.$id_indikator)->get();
        }else{
            $perlu_dok_perbaikan = true;
            $get_data_indikator=Master_file_indikator::where(function($w) use($string_lvl, $level, $id_indikator){
                                                            $w->where('file_code', $string_lvl[$level-1].'-'.$id_indikator)
                                                                ->whereNull('periode_perbaikan_id');
                                                        })
                                            ->orWhere(function($w) use($string_lvl, $id_perbaikan, $id_indikator, $level){
                                                 $w->where('file_code', $string_lvl[$level-1].'-'.$id_indikator)
                                                                ->where('periode_perbaikan_id', $id_perbaikan);
                                            })->get();
        }
        $jumlah=$get_data_indikator->count();
        if($jumlah > 0){
            $x=0;
            $sudah_ada_perbaikan = false;
            foreach($get_data_indikator as $file_indikator){
                $filename=$file_indikator['file_name'];
                $is_perbaikan = $file_indikator['is_perbaikan'];
                $edoc[$x]['filename']=$filename;
                $edoc[$x]['link_gd_id'] = $file_indikator['gd_id'];
                $edoc[$x]['is_perbaikan'] = $is_perbaikan;
                if($perlu_dok_perbaikan && $is_perbaikan){
                    $sudah_ada_perbaikan = true;
                }
                $get_edoc=Edoc_indikator::where('id_master', $file_indikator['id'])
                            ->where('max_fill_at', '<', date(now()))
                            ->get();
                $jumlah_edoc=$get_edoc->count();
                if($jumlah_edoc > 0){
                    $edoc_index=0;
                    foreach($get_edoc as $list_edoc){
                        if($is_perbaikan && $perlu_dok_perbaikan && is_null($list_edoc['edoc'])){
                            $blm_upload_perbaikan+=1;
                        }else if($is_perbaikan && $perlu_dok_perbaikan && !is_null($list_edoc['edoc'])){
                            $sudah_upload_perbaikan+=1;
                        }
                        $edoc[$x]['file'][$edoc_index]['edoc']=$list_edoc['edoc'];
                        $edoc[$x]['file'][$edoc_index]['timeline']=$list_edoc['timeline'];
                        $edoc[$x]['file'][$edoc_index]['max_fill_at']=$list_edoc['max_fill_at'];
                        $edoc_index++;
                    }
                }
                $x++;
            }
            
            if($sudah_ada_perbaikan === false && $perlu_dok_perbaikan){
                $get_periode_perbaikan = Periode_perbaikan::where("id", $id_perbaikan)->first();
                $edoc[$x]['filename'] = "Perbaikan ".$get_periode_perbaikan->periode;
                $edoc[$x]['is_perbaikan'] = true;
                $blm_upload_perbaikan += 1;
            }
        }

        return [
            'edoc' => $edoc,
            'filename' => $filename,
            'blm_upload_perbaikan'=>$blm_upload_perbaikan,
            'sudah_upload_perbaikan'=>$sudah_upload_perbaikan
        ];
    }
}
