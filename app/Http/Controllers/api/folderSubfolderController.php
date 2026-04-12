<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ampuh_indikator;
use App\Models\Ampuh_sub_indikator_lvl1;
use App\Models\Ampuh_sub_indikator_lvl2;
use App\Models\Web_config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Services\FolderService;
use App\Services\googleDriveIdService;
use App\Services\GoogleDriveService;

class folderSubfolderController extends Controller
{
    protected $status;
    protected $folderService;
    protected $googleDriveService;
    protected $googleDriveIdService;

    public function __construct(FolderService $folderService, GoogleDriveService $google_drive_service, googleDriveIdService $google_drive_id){
        $this->folderService=$folderService;
        $this->status=false;
        $this->googleDriveService=$google_drive_service;
        $this->googleDriveIdService=$google_drive_id;
    }
    // public function getMainFolderAmpuh($level, $parent_id){
    //     $get_data=$this->folderService->getDataFolder($level, $parent_id);
    //     return response()->json(['jumlah'=>$total, 'data'=>$get_indikator, 'level'=>$level]);
    // }

    public function getAllFolderSubfolder(Request $request){
        $status=false;
        try{
            $gd_id_main_folder=Crypt::decrypt($request->gd_id);
            $get_gd_id=$this->googleDriveIdService->getGdIdById($gd_id_main_folder);
            try{
                if(is_null($get_gd_id)){
                    throw new \Exception("Data Konfigurasi sedang diproses");
                }
                $get_parent_properti=$this->googleDriveService->getFolderProperti($get_gd_id['config_value']);
                if($get_parent_properti['status'] === false){
                    throw new \Exception($get_parent_properti['msg']);
                }
                $get_parent_properti['id']=Crypt::encrypt($get_parent_properti['id']);//google drive ID
                $get_parent_properti['index']=Crypt::encrypt('0');
                $get_folder=Ampuh_indikator::leftJoin('ampuh_sub_indikator_lvl1 as lvl1', function($join){
                                                $join->on('lvl1.indikator_id', '=', 'ampuh_indikator.id')
                                                ->where(function($q){
                                                    $q->where('lvl1.rule_id', '>', 0)
                                                    ->orWhereRaw('lvl1.rule_id is null');
                                                });
                                            })
                            ->leftJoin('ampuh_sub_indikator_lvl2 as lvl2', function($join){
                                                $join->on('lvl2.sub_indikator_lvl1_id', '=', 'lvl1.id')
                                                ->where(function($r){
                                                    $r->where('lvl2.rule_id', '>', 0)
                                                    ->orWhereRaw('lvl2.rule_id is null');
                                                });
                                            })
                            ->leftJoin('ampuh_sub_indikator_lvl3 as lvl3', function($join){
                                            $join->on('lvl3.parent_id', '=', 'lvl2.id')
                                            ->where(function($r){
                                                $r->where('lvl3.rule_id', '>', 0)
                                                ->orWhereRaw('lvl3.rule_id is null');
                                            });
                                        })
                            ->leftJoin('ampuh_sub_indikator_lvl4 as lvl4', function($join){
                                            $join->on('lvl4.parent_id', '=', 'lvl3.id')
                                            ->where(function($r){
                                                $r->where('lvl4.rule_id', '>', 0)
                                                ->orWhereRaw('lvl4.rule_id is null');
                                            });
                                        })
                            ->leftJoin('ampuh_sub_indikator_lvl5 as lvl5', function($join){
                                        $join->on('lvl5.parent_id', '=', 'lvl4.id')
                                            ->where(function($r){
                                                $r->where('lvl5.rule_id', '>', 0)
                                                ->orWhereRaw('lvl5.rule_id is null');
                                            });
                                        })
                            ->select('ampuh_indikator.id', 
                                            'ampuh_indikator.gd_id', 
                                            'ampuh_indikator.indikator_name', 
                                            'lvl1.id as id_lvl1', 
                                            'lvl1.gd_id as gd_id_lvl1', 
                                            'lvl1.sub_indikator_name as lvl1_name', 
                                            'lvl1.indikator_id', 
                                            'lvl1.is_folder_bagian as is_folder_bagian_lvl1',

                                            'lvl2.id as id_lvl2', 
                                            'lvl2.gd_id as gd_id_lvl2', 
                                            'lvl2.sub_indikator_name as lvl2_name', 
                                            'lvl2.sub_indikator_lvl1_id as lvl1_indikator_id',
                                            'lvl2.is_folder_bagian as is_folder_bagian_lvl2',


                                            'lvl3.id as id_lvl3',
                                            'lvl3.gd_id as gd_id_lvl3',
                                            'lvl3.sub_indikator_name as lvl3_name',
                                            'lvl3.parent_id as parent_id_lvl3',
                                            'lvl3.is_folder_bagian as is_folder_bagian_lvl3',
                                            
                                            'lvl4.id as id_lvl4',
                                            'lvl4.gd_id as gd_id_lvl4',
                                            'lvl4.sub_indikator_name as lvl4_name',
                                            'lvl4.parent_id as parent_id_lvl4', 
                                            'lvl4.is_folder_bagian as is_folder_bagian_lvl4',

                                            'lvl5.id as id_lvl5',
                                            'lvl5.gd_id as gd_id_lvl5',
                                            'lvl5.sub_indikator_name as lvl5_name',
                                            'lvl5.parent_id as parent_id_lvl5',
                                            'lvl5.is_folder_bagian as is_folder_bagian_lvl5',
                                            )
                            ->where(function($w){
                                $w->where('ampuh_indikator.rule_id', '>', 0)
                                ->orWhereRaw('ampuh_indikator.rule_id is null');
                            })
                            ->where("ampuh_indikator.parent_id", $gd_id_main_folder)
                            ->orderBy('ampuh_indikator.indikator_name', 'asc')
                            ->orderBy('lvl1.sub_indikator_name', 'asc')
                            ->orderBy('lvl2.sub_indikator_name', 'asc')
                            ->orderBy('lvl3.sub_indikator_name', 'asc')
                            ->orderBy('lvl4.sub_indikator_name', 'asc')
                            ->orderBy('lvl5.sub_indikator_name', 'asc')
                            ->get();
            
                // $gd_id_main_folder="1KlWVPKTSbDZVqMnzqf4YOlDdYfsvBEF1";
                $total=$get_folder->count();
                if((int)$total === 0){
                    return response()->json(['status'=>false, 'msg'=>"Tidak ada data. Silahkan Tarik Sub Folder", 'data'=>null, 'jumlah'=>0, 'data_folder'=>$get_parent_properti, 'main_folder'=>Crypt::encrypt($gd_id_main_folder)]);
                }
                $data_tree=$this->treeFolder($get_folder);
                return response()->json(['status'=>true, 'msg'=>'', 'data_folder'=>$get_parent_properti, 'data'=>$data_tree['indikator'], 'jumlah'=>count($data_tree['indikator']), 'level0'=>Crypt::encrypt('0'), 'level1'=>Crypt::encrypt('1'), 'level2'=>Crypt::encrypt('2'), 'level3'=>Crypt::encrypt('3'), 'main_folder'=>Crypt::encrypt($gd_id_main_folder), 'level4'=>Crypt::encrypt('4'), 'level5'=>Crypt::encrypt('5')]);
            }catch(\Exception $e){
                $msg=$e->getMessage();
            }
        }catch(DecryptException $e){
            return response()->json(['status'=>false, 'msg'=>"Invalid Token"]);
        }
        // return response()->json(['data'=>$get_folder]);
        return response()->json(['status'=>false, 'msg'=>$msg]);
    }

    public function treeFolder($folder){
        $index=0;
        $x=$y=$z=$z1=$z2=$z3=0;
        $p1=$p2=$p3=0;
        $data=[];
        foreach($folder as $list_data){
            $indikator=true;
            $sub_indikator_lvl1=true;
            $sub_indikator_lvl2=true;
            $sub_indikator_lvl3=true;
            $sub_indikator_lvl4=true;
            $sub_indikator_lvl5=true;
            if($index > 0){
                $indikator_id_before=$data['indikator'][$x]['id'];
                if($indikator_id_before === $list_data['id']){
                    $indikator=false;
                    $sub_indikator_lvl1_before=$data['indikator'][$x]['lvl1'][$y]['id_lvl1'];
                    if($sub_indikator_lvl1_before === $list_data['id_lvl1']){
                        $sub_indikator_lvl1=false;
                        $sub_indikator_lvl2_before=$data['indikator'][$x]['lvl1'][$y]['lvl2'][$z]['id_lvl2'];
                        if($sub_indikator_lvl2_before === $list_data['id_lvl2']){
                            $sub_indikator_lvl2=false;
                            $sub_indikator_lvl3_before=$data['indikator'][$x]['lvl1'][$y]['lvl2'][$z]['lvl3'][$z1]['id_lvl3'];
                            if($sub_indikator_lvl3_before === $list_data['id_lvl3']){
                                $sub_indikator_lvl3=false;
                                $sub_indikator_lvl4_before=$data['indikator'][$x]['lvl1'][$y]['lvl2'][$z]['lvl3'][$z1]['lvl4'][$z2]['id_lvl4'];
                                if($sub_indikator_lvl4_before === $list_data['id_lvl4']){
                                    $sub_indikator_lvl4=false;
                                    $sub_indikator_lvl5_before=$data['indikator'][$x]['lvl1'][$y]['lvl2'][$z]['lvl3'][$z1]['lvl4'][$z2]['lvl5'][$z3]['id_lvl5'];
                                    if($sub_indikator_lvl5_before === $list_data['id_lvl5']){
                                        $sub_indikator_lvl5=false;
                                    }else{
                                        $z3++;
                                    }
                                }else{
                                    $z2++;$z3=0;
                                }
                            }else{
                                $z1++;$z2;$z3=0;
                            }
                        }else{
                            $z++;$z1=0;$z2;$z3=0;
                            // var_dump($list_data['id_lvl2'].':'.$sub_indikator_lvl2_before);
                        }
                    }else{
                        $y++;$z=0;$z1=0;$z2;$z3=0;
                    }
                }else{
                    $x++;$y=0;$z=0;$z1=0;$z2;$z3=0;
                }
            }
            if($indikator){
                $data['indikator'][$x]['nama']=$list_data['indikator_name'];
                $data['indikator'][$x]['id']=$list_data['id'];
                $data['indikator'][$x]['gd_id']=Crypt::encrypt($list_data['gd_id']);
                $data['indikator'][$x]['lvl1']=[];
            }
            if($list_data['id_lvl1'] !== null && $sub_indikator_lvl1 === true){
                $data_level1=&$data['indikator'][$x]['lvl1'][$y];
                $data_level1['id_lvl1']=$list_data['id_lvl1'];
                $data_level1['nama']=$list_data['lvl1_name'];
                $data_level1['gd_id']=Crypt::encrypt($list_data['gd_id_lvl1']);
                $data_level1['is_folder_bagian']=$list_data['is_folder_bagian_lvl1'];
                $data_level1['lvl2']=[];
            }

            if($list_data['id_lvl2'] !==  null && $sub_indikator_lvl2 === true){
                $data_level2=&$data['indikator'][$x]['lvl1'][$y]['lvl2'];
                $data_level2[$z]['id_lvl2']=$list_data['id_lvl2'];
                $data_level2[$z]['nama']=$list_data['lvl2_name'];
                $data_level2[$z]['gd_id']=Crypt::encrypt($list_data['gd_id_lvl2']);
                $data_level2[$z]['is_folder_bagian']=$list_data['is_folder_bagian_lvl2'];
                $data_level2[$z]['lvl3']=null;
            }

            if($list_data['id_lvl3'] !== null && $sub_indikator_lvl3 === true){
                $data_level3=&$data['indikator'][$x]['lvl1'][$y]['lvl2'][$z]['lvl3'];
                $data_level3[$z1]['id_lvl3']=$list_data['id_lvl3'];
                $data_level3[$z1]['nama']=$list_data['lvl3_name'];
                $data_level3[$z1]['gd_id']=Crypt::encrypt($list_data['gd_id_lvl3']);
                $data_level3[$z1]['is_folder_bagian']=$list_data['is_folder_bagian_lvl3'];
                $data_level3[$z1]['lvl4']=null;
            }

            if($list_data['id_lvl4'] !== null && $sub_indikator_lvl4 === true){
                $data_level4=&$data['indikator'][$x]['lvl1'][$y]['lvl2'][$z]['lvl3'][$z1]['lvl4'];
                $data_level4[$z2]['id_lvl4']=$list_data['id_lvl4'];
                $data_level4[$z2]['nama']=$list_data['lvl4_name'];
                $data_level4[$z2]['gd_id']=Crypt::encrypt($list_data['gd_id_lvl4']);
                $data_level4[$z2]['is_folder_bagian']=$list_data['is_folder_bagian_lvl4'];
                $data_level4[$z2]['lvl5']=null;
            }

            if($list_data['id_lvl5'] !== null && $sub_indikator_lvl5 === true){
                $data_level5=&$data['indikator'][$x]['lvl1'][$y]['lvl2'][$z]['lvl3'][$z1]['lvl4'][$z2]['lvl5'];
                $data_level5[$z3]['id_lvl5']=$list_data['id_lvl5'];
                $data_level5[$z3]['nama']=$list_data['lvl5_name'];
                $data_level5[$z3]['gd_id']=Crypt::encrypt($list_data['gd_id_lvl5']);
                $data_level5[$z3]['is_folder_bagian']=$list_data['is_folder_bagian_lvl5'];
                $data_level5[$z3]['lvl6']=null;
            }


            $index++;
        }
        foreach ($data['indikator'] as &$indikator) {
            if (isset($indikator['lvl1']) && is_array($indikator['lvl1'])) {
                foreach ($indikator['lvl1'] as &$lvl1) {
                    if (isset($lvl1['lvl2']) && is_array($lvl1['lvl2'])) {
                        $lvl1['lvl2'] = array_values($lvl1['lvl2']);
                        foreach ($lvl1['lvl2'] as &$lvl2) {
                            if (isset($lvl2['lvl3']) && is_array($lvl2['lvl3'])) {
                                $lvl2['lvl3'] = array_values($lvl2['lvl3']);
                                foreach($lvl2['lvl3'] as &$lvl3){
                                    if(isset($lvl3['lvl4']) && is_array($lvl3['lvl4'])){
                                        $lvl3['lvl4']=array_values($lvl3['lvl4']);
                                        foreach($lvl3['lvl4'] as &$lvl4){
                                            if(isset($lvl4['lvl5']) && is_array($lvl4['lvl5'])){
                                                $lvl4['lvl5']=array_values($lvl4['lvl5']);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
        
                $indikator['lvl1'] = array_values($indikator['lvl1']);
            }
        }
        return $data;
    }

    //diakses dari halaman home
    public function getMainGdId(){
        $gd_id="";
        $status=400;
        $get_data=Web_config::where('config_name', 'main_gd_id')
                            ->where('active', true)
                            ->first();
        if(!is_null($get_data)){
            $gd_id=$get_data['config_value'];
            $status=200;
        }
        return response()->json(['status'=>$status, 'gd_id'=>Crypt::encrypt($gd_id), 'level'=>Crypt::encrypt('0')]);
    }
    public function softDeleteFolder(Request $request){
        $delete=false;
        try{
            $gd_id=Crypt::decrypt($request->gd_id);
            $level=Crypt::decrypt($request->level);
            $get_data=$this->folderService->getFolderByLevel($gd_id, $level);
            if(is_null($get_data)){
                $msg="Data tidak ditemukan : ".$gd_id."_".$level;
            }else{
                $delete_folder=$this->folderService->softDeleteFolder($get_data);
                $msg=$delete_folder['msg'];
                $delete=$delete_folder['status'];
            }
        }catch(DecryptException $e){
            $msg="Data tidak valid";
        }
        return response()->json(['status'=>$delete, 'msg'=>$msg]);
    }
}
