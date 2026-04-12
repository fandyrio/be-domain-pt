<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Models\Bagian;
use App\Models\Ampuh_indikator;
use App\Models\Edoc_indikator;
use App\Models\Indikator_user;
use App\Models\Indikator_lvl1_user;
use App\Models\Indikator_lvl2_user;
use App\Models\Indikator_lvl3_user;
use App\Models\Indikator_lvl4_user;
use App\Models\Indikator_lvl5_user;
use App\Services\GoogleDriveService;
use App\Models\Master_file_indikator;
use App\Services\bagianService;
use App\Services\ChecklistBagianService;
use Illuminate\Validation\ValidationException;

class checklistBagianController extends Controller
{
    
    protected $googleDriveService;
    protected $checklistBagianService;
    protected $bagianService;

    public function __construct(GoogleDriveService $googleDriveService, ChecklistBagianService $checklist_bagian_service, bagianService $bagian_service){
        $this->googleDriveService=$googleDriveService;
        $this->checklistBagianService=$checklist_bagian_service;
        $this->bagianService=$bagian_service;
    }

    public function saveChecklist(Request $request){
        $status=false;
        $msg="";
        $save_data=0;
        $exists=0;
        $jumlah_parent=0;
        $jumlah_lvl2=0;
        $jumlah_lvl3=0;
        $jumlah_lvl4=0;
        $jumlah_lvl5=0;
        try{
            $token=Crypt::decrypt($request->token);
            if($token === $request->nama_bagian){
                $get_bagian=Bagian::where('alias', $token)->first();
                if(!is_null($get_bagian)){
                    $parent=$request->parent;
                    $jumlah_parent=count($parent);
                    if($jumlah_parent > 0){
                        
                        $gd_id_parent=[];
                        for($x=0;$x<$jumlah_parent;$x++){
                            $gd_id_parent[$x]=Crypt::decrypt($parent[$x]);
                        }
                        $get_data_parent=Ampuh_indikator::whereIn('gd_id', $gd_id_parent)->get();
                        $jumlah_parent_query=$get_data_parent->count();
                        if($jumlah_parent_query === $jumlah_parent){
                            foreach($get_data_parent as $list_parent){
                                $get_data=Indikator_user::where('id_bagian', $get_bagian['id'])
                                            ->where('id_indikator', $list_parent['id'])
                                            ->where('tahun', $request->tahun)
                                            ->first();
                                if(is_null($get_data)){
                                    $new_parent=new Indikator_user;
                                    $new_parent->id_indikator=$list_parent['id'];
                                    $new_parent->id_bagian=$get_bagian['id'];
                                    $new_parent->has_child=false;
                                    $new_parent->periode=0;
                                    $new_parent->tahun=$request->tahun;
                                    if($new_parent->save()){
                                        $save_data+=1;
                                    }
                                }else{
                                    $exists+=1;
                                }
                            }
                        }else{
                            $msg="There is some data not valid. Please reload page and rerun the process";
                        }
                    }else{
                        $msg="There is no data to process";
                    }
                }else{
                    $msg="Data not found";
                }
            }else{
                $msg="Data missmatch";
            }

            $data=['lvl1', 'lvl2', 'lvl3', 'lvl4', 'lvl5'];
            $db=["Indikator_lvl1_user", "Indikator_lvl2_user", "Indikator_lvl3_user", "Indikator_lvl4_user", "Indikator_lvl5_user"];
            $indikator_id=['id_indikator_lvl1', 'id_indikator_lvl2', 'id_indikator_lvl3', 'id_indikator_lvl4', 'id_indikator_lvl5'];
            $db_master=["Ampuh_sub_indikator_lvl1", "Ampuh_sub_indikator_lvl2", "Ampuh_sub_indikator_lvl3", "Ampuh_sub_indikator_lvl4", "Ampuh_sub_indikator_lvl5"];
            $total=$save_data+$exists;
            if($total > 0 && $total === $jumlah_parent){
                for($x=0;$x<count($data);$x++){
                    $saved=0;
                    $jumlah=count($request->input($data[$x]));
                    if($jumlah > 0){
                        $gd_id_lvl=[];
                        for($y=0;$y<$jumlah;$y++){
                            $gd_id_lvl[$y]=Crypt::decrypt($request->input($data[$x])[$y]);
                        }
                        $get_master=app("App\\Models\\".$db_master[$x])::whereIn('gd_id', $gd_id_lvl)->get();
                        if($get_master->count() === $jumlah){
                            foreach($get_master as $list_master){
                                $fullClass="App\\Models\\$db[$x]";
                                $prop_name=$indikator_id[$x];
                                $get_child=app($fullClass)::where($prop_name, $list_master['id'])
                                            ->where('id_bagian', $get_bagian['id'])
                                            ->where('tahun', $request->tahun)
                                            ->first();
                                if(is_null($get_child)){
                                    $indikator=new $fullClass();
                                    $indikator->$prop_name=$list_master['id'];
                                    $indikator->id_bagian=$get_bagian['id'];
                                    $indikator->has_child=0;
                                    $indikator->periode=0;
                                    $indikator->tahun=$request->tahun;
                                    if($indikator->save()){
                                        $saved+=1;
                                        $save_data+=1;
                                    }
                                }else{
                                    $saved+=1;
                                    $save_data+=1;
                                }
                            }
                            if($saved !== $get_master->count()){
                                $msg="Operation not completed. Please call tim Dev ";
                                break;
                            }
                        }else{
                            $msg="Invalid Child Properti ".$data[$x];
                            break;
                        }
                    }else{
                        break;
                    }
                }
            }

            $input_data=count($request->lvl1)+count($request->lvl2)+count($request->lvl3)+count($request->lvl4)+count($request->lvl5)+count($request->parent);
            if($input_data === ($save_data+$exists) && $save_data > 0){
                $msg="";
                $status=true;
                $this->checkFolderBagian($get_bagian['id'], $get_bagian['bagian'], $request->tahun);
                $msg.="Data complete to save";
            }

        }catch(DecryptException $e){
            $msg="Invalid token";
        }
        return response()->json(['status'=>$status, 'msg'=>$msg]);
    }

    public function checkFolderBagian($id_bagian, $nama_folder, $tahun){
        $get_checklist_bagian=$this->getDataChecklistBagian(Crypt::encrypt($id_bagian), $tahun);
        $fetch_data_checklist=$get_checklist_bagian->getData();
        $checklist_bagian=$fetch_data_checklist->tree;
        $jumlah_data=count($checklist_bagian);
        for($x=0;$x<$jumlah_data;$x++){
            $parent=$checklist_bagian[$x];
            if(isset($parent->lvl1)){//check lvl1
                $lvl1=$parent->lvl1;
                $jumlah_lvl1=count($lvl1);
                for($y=0;$y<$jumlah_lvl1;$y++){
                    if(isset($lvl1[$y]->lvl2)){//check lvl2
                        $lvl2=$lvl1[$y]->lvl2;
                        $jumlah_lvl2=count($lvl2);
                        for($z=0;$z<$jumlah_lvl2;$z++){
                            if(isset($lvl2[$z]->lvl3)){
                                $lvl3=$lvl2[$z]->lvl3;
                                $jumlah_lvl3=count($lvl3);
                                for($a=0;$a<$jumlah_lvl3;$a++){
                                    if(isset($lvl3[$a]->lvl4)){
                                        $lvl4=$lvl3[$a]->lvl4;
                                        $jumlah_lvl4=count($lvl4);
                                        for($b=0;$b<$jumlah_lvl4;$b++){
                                            if(isset($lvl4[$b]->lvl5)){

                                            }else{
                                                if((int)$lvl4[$b]->is_folder_bagian === 0){
                                                    $level=5;
                                                    $this->generateFolder($lvl4[$a]->id, $level, $nama_folder, $id_bagian, $tahun);
                                                }
                                            }
                                        }
                                    }else{
                                        if((int)$lvl3[$a]->is_folder_bagian === 0){
                                            $level=4;
                                            $this->generateFolder($lvl3[$a]->id, $level, $nama_folder, $id_bagian, $tahun);
                                        }
                                    }
                                }
                            }else{
                                if((int)$lvl2[$z]->is_folder_bagian === 0){
                                    $level=3;
                                    $this->generateFolder($lvl2[$z]->id, $level, $nama_folder, $id_bagian, $tahun);
                                }
                            }
                        }
                    }else{
                        if((int)$lvl1[$y]->is_folder_bagian === 0){
                            $level=2;
                            $this->generateFolder($lvl1[$y]->id, $level, $nama_folder, $id_bagian, $tahun);
                        }
                    }
                }
            }else{
                $level=1;
                $this->generateFolder($parent->id, $level, $nama_folder, $id_bagian, $tahun);
            }
        }
    }

    public function generateFolder($parent_id, $level, $nama_folder, $id_bagian, $tahun){
        $db_master=["Ampuh_indikator", "Ampuh_sub_indikator_lvl1", "Ampuh_sub_indikator_lvl2", "Ampuh_sub_indikator_lvl3", "Ampuh_sub_indikator_lvl4", "Ampuh_sub_indikator_lvl5"];

        //get parent
        $get_data=app("App\\Models\\".$db_master[$level-1])::where('id', $parent_id)->first();
        $gd_id=$get_data['gd_id'];

        $check_exists=$this->checkFolderByName($nama_folder, $level, $parent_id);
        if($check_exists['status'] === "exists"){
            $id_indikator=$check_exists['id_indikator'];
            $status=true;
        }else{
            $createFolder=$this->googleDriveService->createFolder($nama_folder, $gd_id);
            $save_master=$this->googleDriveService->saveFolderAsIndikator($level, $nama_folder, Crypt::encrypt($createFolder), Crypt::encrypt($gd_id), true);
            $result_save_master=$save_master->getData();
            $id_indikator=$result_save_master->id;
            $status=$result_save_master->status;
        }
        if($status){
            $save_indikator=$this->saveIndikatorPerLevel(($level-1), $id_indikator, $id_bagian, $tahun);                      
        }
    }

    public function checkFolderByName($folder_name, $level, $parent_id){
        $db_master=["Ampuh_indikator", "Ampuh_sub_indikator_lvl1", "Ampuh_sub_indikator_lvl2", "Ampuh_sub_indikator_lvl3", "Ampuh_sub_indikator_lvl4", "Ampuh_sub_indikator_lvl5"];
        $parent=["", "indikator_id", "sub_indikator_lvl1_id", "parent_id", "parent_id", "parent_id"];
        $get_data=app("App\\Models\\".$db_master[$level])::where($parent[$level], $parent_id)
                        ->where('sub_indikator_name', $folder_name)
                        ->where(function($w){
                                $w->where('rule_id', '>', 0)
                                    ->orWhereRaw('rule_id is null');
                            })
                        ->first();
        if(is_null($get_data)){
            return [
                'status'=>'not_exists',
            ];
        }else{
            return [
                'status'=>'exists',
                'id_indikator'=>$get_data['id'],
            ];
        }
    }

    public function saveIndikatorPerLevel($level, $id_indikator, $id_bagian, $tahun){
        $save=false;
        $db=["Indikator_lvl1_user", "Indikator_lvl2_user", "Indikator_lvl3_user", "Indikator_lvl4_user", "Indikator_lvl5_user"];
        $indikator_id=['id_indikator_lvl1', 'id_indikator_lvl2', 'id_indikator_lvl3', 'id_indikator_lvl4', 'id_indikator_lvl5'];
        $fullClass="App\\Models\\$db[$level]";
        $prop_name=$indikator_id[$level];
        $indikator=new $fullClass();
        $indikator->$prop_name=$id_indikator;
        $indikator->id_bagian=$id_bagian;
        $indikator->has_child=false;
        $indikator->periode=0;
        $indikator->tahun=$tahun;
        $indikator->is_folder_bagian=true;
        if($indikator->save()){
            $msg="Berhasil menyimpan data";
            $save=true;
        }
        return [
            'status'=>$save,
            'msg'=>$msg
        ];
    }

    public function getAllChecklistBagian($year){
        
    }

    public function getDataChecklistBagian($id_bagian_enc, $year = null){
        $jumlah=0;
        $msg="";
        $get_data=[];
        $current_year=$year === null ? date('Y') : $year;
        try{
            $id_bagian=Crypt::decrypt($id_bagian_enc);
            $get_bagian=$this->bagianService->getBagianById($id_bagian);
            if(is_null($get_bagian['id_bagian_enc'])){
                return response()->json(['msg'=>'Data tidak ditemukan'], 404);
            }
            $get_data=Indikator_user::join('ampuh_indikator as a', function($join) use($id_bagian){
                                            $join->on('a.id', '=', 'indikator_user.id_indikator')
                                            ->where('indikator_user.id_bagian', $id_bagian)
                                            ->where(function($w){
                                                $w->where('a.rule_id', '>', 0)
                                                ->orWhereRaw('a.rule_id is null');
                                            });
                                        })
                                    ->leftJoin('ampuh_sub_indikator_lvl1 as b', function($join) use($id_bagian){
                                                    $join->on('b.indikator_id', '=', 'indikator_user.id_indikator')
                                                    ->where(function($w){
                                                        $w->where('b.rule_id', '>', 0)
                                                        ->orWhereRaw('b.rule_id is null');
                                                    })
                                                    ->whereIn('b.id', function($query) use($id_bagian){
                                                        $query->select('id_indikator_lvl1')
                                                                ->from('indikator_lvl1_user')
                                                                ->where('id_bagian', $id_bagian);
                                                    });
                                                })
                                    ->leftJoin('indikator_lvl1_user as c', function($join) use($id_bagian, $current_year){
                                        $join->on('c.id_indikator_lvl1', '=', 'b.id')
                                        ->where('c.id_bagian', $id_bagian)
                                        ->where('c.tahun', $current_year);
                                    })
                                    ->leftJoin('ampuh_sub_indikator_lvl2 as d', function($join) use($id_bagian){
                                                $join->on('d.sub_indikator_lvl1_id', '=', 'c.id_indikator_lvl1')
                                                ->where(function($w){
                                                    $w->where('d.rule_id', '>', 0)
                                                    ->orWhereNull('d.rule_id');
                                                })
                                                ->whereIn('d.id', function($query) use($id_bagian){
                                                    $query->select('id_indikator_lvl2')
                                                        ->from('indikator_lvl2_user')
                                                        ->where('id_bagian', $id_bagian);
                                                });
                                            })
                                    ->Leftjoin('indikator_lvl2_user as e', function($join) use($id_bagian, $current_year){
                                        $join->on('e.id_indikator_lvl2', '=', 'd.id')
                                        ->where('e.id_bagian', $id_bagian)
                                        ->where('e.tahun', $current_year);
                                    })
                                    ->leftJoin('ampuh_sub_indikator_lvl3 as f', function($join) use($id_bagian){
                                                    $join->on('f.parent_id', '=', 'e.id_indikator_lvl2')
                                                    ->where(function($w){
                                                        $w->where('f.rule_id', '>', 0)
                                                        ->orWhereNull('f.rule_id');
                                                    })
                                                    ->whereIn("f.id", function($query) use($id_bagian){
                                                        $query->select('id_indikator_lvl3')
                                                            ->from('indikator_lvl3_user')
                                                            ->where('id_bagian', $id_bagian);
                                                    });
                                                })
                                    ->leftJoin('indikator_lvl3_user as g', function($join) use($id_bagian, $current_year){
                                        $join->on('g.id_indikator_lvl3', '=', 'f.id')
                                        ->where('g.id_bagian', $id_bagian)
                                        ->where('g.tahun', $current_year);
                                    })
                                    ->leftJoin('ampuh_sub_indikator_lvl4 as h', function($join) use($id_bagian){
                                                    $join->on('h.parent_id', '=', 'g.id_indikator_lvl3')
                                                    ->where(function($w){
                                                        $w->where('h.rule_id', '>', 0)
                                                        ->orWhereRaw('h.rule_id is null');
                                                    })
                                                    ->whereIn("h.id", function($query) use($id_bagian){
                                                        $query->select('id_indikator_lvl4')
                                                            ->from('indikator_lvl4_user')
                                                            ->where('id_bagian', $id_bagian);
                                                    });
                                                })
                                    ->leftJoin('indikator_lvl4_user as i', function($join) use($id_bagian, $current_year){
                                        $join->on('i.id_indikator_lvl4','=', 'h.id')
                                        ->where('i.id_bagian', $id_bagian)
                                        ->where('i.tahun', $current_year);;
                                    })
                                    ->leftJoin('ampuh_sub_indikator_lvl5 as j', function($join) use($id_bagian){
                                                    $join->on('j.parent_id', '=', 'i.id_indikator_lvl4')
                                                    ->where(function($w){
                                                        $w->where('j.rule_id', '>', 0)
                                                        ->orWhereRaw('j.rule_id is null');
                                                    })
                                                    ->whereIn("j.id", function($query) use($id_bagian){
                                                        $query->select('id_indikator_lvl5')
                                                            ->from('indikator_lvl5_user')
                                                            ->where('id_bagian', $id_bagian);
                                                    });
                                                })
                                    ->leftJoin('indikator_lvl5_user as k', function($join) use($id_bagian, $current_year){
                                        $join->on('k.id_indikator_lvl5', '=', 'j.id')
                                        ->where('k.id_bagian', $id_bagian)
                                        ->where('k.tahun', $current_year);
                                    })
                            ->where('indikator_user.tahun', $current_year)
                            ->select('a.indikator_name as parent', 'b.sub_indikator_name as lvl1', 'd.sub_indikator_name as lvl2', 'f.sub_indikator_name as lvl3', 'h.sub_indikator_name as lvl4', 'j.sub_indikator_name as lvl5', 'indikator_user.id_indikator as id_parent', 'c.id_indikator_lvl1 as id_lvl1', 'e.id_indikator_lvl2 as id_lvl2', 'g.id_indikator_lvl3 as id_lvl3', 'i.id_indikator_lvl4 as id_lvl4', 'k.id_indikator_lvl5 as id_lvl5', 'c.is_folder_bagian as folder_bagian_lvl1', 'e.is_folder_bagian as folder_bagian_lvl2', 'g.is_folder_bagian as folder_bagian_lvl3', 'i.is_folder_bagian as folder_bagian_lvl4', 'k.is_folder_bagian as folder_bagian_lvl5')
                            ->orderBy('a.indikator_name', 'asc')
                            ->orderBy('b.sub_indikator_name', 'asc')
                            ->orderBy('d.sub_indikator_name', 'asc')
                            ->orderBy('f.sub_indikator_name', 'asc')
                            ->orderBy('h.sub_indikator_name', 'asc')
                            ->orderBy('j.sub_indikator_name', 'asc')
                            ->get();
            $jumlah=$get_data->count();
            $data_tree=$this->treeData($get_data);

            $get_year=Indikator_user::select('tahun')
                        ->where('id_bagian', $id_bagian)
                        ->distinct()->get();
            $jumlah_tahun=$get_year->count();
            $data_tahun=null;
            if($jumlah_tahun > 0){
                $x=0;
                foreach($get_year as $year){
                    $data_tahun[$x]['tahun']=$year['tahun'];
                    $x++;
                }
            }


        }catch(DecryptException $e){
            $msg="Invalid token";
        }
        return response()->json(['total'=>$jumlah, 'tree'=>$data_tree, 'data'=>$get_data, 'msg'=>$msg, 'current_year'=>$current_year, 'efective_year'=>$data_tahun, 'bagian'=>['token'=>$get_bagian['id_bagian_enc'], 'bagian'=>$get_bagian['bagian']]]);
    }
    public function treeData($data){
        $jumlah_data=count($data);
        $data_tree=[];
        $parent_before=$lvl1_before=$lvl2_before=$lvl3_before=$lvl4_before=$lvl5_before="";
        $idx_parent=$idx_lvl1=$idx_lvl2=$idx_lvl3=$idx_lvl4=$idx_lvl5=-1;
        foreach($data as $list_data){
            if($parent_before !== $list_data['id_parent']){
                $idx_parent+=1;
                $data_tree[$idx_parent]['parent']=$list_data['parent'];
                $data_tree[$idx_parent]['id']=$list_data['id_parent'];
                $parent=&$data_tree[$idx_parent];
                $idx_lvl1=$idx_lvl2=$idx_lvl3=$idx_lvl4=$idx_lvl5=-1;
                // $data_tree[$idx_parent]['lvl1'][$idx_lvl1]['lvl2'][$idx_lvl2]=null;
            }
            if($lvl1_before !== $list_data['id_lvl1'] && !is_null($list_data['id_lvl1'])){
                $idx_lvl1+=1;
                $lvl1=&$parent['lvl1'][$idx_lvl1];
                $lvl1['nama']=$list_data['lvl1'];
                $lvl1['id']=$list_data['id_lvl1'];
                $lvl1['is_folder_bagian']=$list_data['folder_bagian_lvl1'];
                $lvl1['token']=Crypt::encrypt($list_data['id_lvl1']);
                $idx_lvl2=$idx_lvl3=$idx_lvl4=$idx_lvl5=-1;
            }
            if($lvl2_before !== $list_data['id_lvl2'] && !is_null($list_data['id_lvl2'])){
                $idx_lvl2+=1;
                $lvl2=&$lvl1['lvl2'][$idx_lvl2];
                $lvl2['nama']=$list_data['lvl2'];
                $lvl2['id']=$list_data['id_lvl2'];
                $lvl2['is_folder_bagian']=$list_data['folder_bagian_lvl2'];
                $lvl2['token']=Crypt::encrypt($list_data['id_lvl2']);
                $idx_lvl3=$idx_lvl4=$idx_lvl5=-1;
                // $data_tree[$idx_parent]['lvl1'][$idx_lvl1]['lvl2'][$idx_lvl2]['lvl3']=null;
            }

            if($lvl3_before !== $list_data['id_lvl3'] && !is_null($list_data['id_lvl3'])){
                $idx_lvl3+=1;
                $lvl3=&$lvl2['lvl3'][$idx_lvl3];
                $lvl3['nama']=$list_data['lvl3'];
                $lvl3['id']=$list_data['id_lvl3'];
                $lvl3['is_folder_bagian']=$list_data['folder_bagian_lvl3'];
                $lvl3['token']=Crypt::encrypt($list_data['id_lvl3']);
                $idx_lvl4=$idx_lvl5=-1;
            }

            if($lvl4_before !== $list_data['id_lvl4'] && !is_null($list_data['id_lvl4'])){
                $idx_lvl4+=1;
                $lvl4=&$lvl3['lvl4'][$idx_lvl4];
                $lvl4['nama']=$list_data['lvl4'];
                $lvl4['id']=$list_data['id_lvl4'];
                $lvl4['is_folder_bagian']=$list_data['folder_bagian_lvl4'];
                $lvl4['token']=Crypt::encrypt($list_data['id_lvl4']);
                $idx_lvl5=-1;
            }

            if($lvl5_before !== $list_data['id_lvl5'] && !is_null($list_data['id_lvl5'])){
                $idx_lvl5+=1;
                $lvl5=&$lvl4['lvl5'][$idx_lvl5];
                $lvl5['nama']=$list_data['lvl5'];
                $lvl5['id']=$list_data['id_lvl5'];
                $lvl5['is_folder_bagian']=$list_data['folder_bagian_lvl5'];
                $lvl5['token']=Crypt::encrypt($list_data['id_lvl5']);
            }
            $parent_before=$list_data['id_parent'];
            $lvl1_before=$list_data['id_lvl1'];
            $lvl2_before=$list_data['id_lvl2'];
            $lvl3_before=$list_data['id_lvl3'];
            $lvl4_before=$list_data['id_lvl4'];
            $lvl5_before=$list_data['id_lvl5'];
        }
        return $data_tree;
    }
    public function deleteIndikatorBagian(Request $request){
        $data_tree=null;
        $msg="";
        try{
            $id_bagian=Crypt::decrypt($request->id_bagian);
            $get_data=Bagian::where('id', $id_bagian)->first();
            $year=$request->year;
            if(is_null($get_data)){
                return response()->json(['status'=>false, 'msg'=>'Data bagian tidak ditemukan']);
            }
            $get_data=$this->getDataChecklistBagian($request->id_bagian);
            $id_indikator=$request->id_indikator;
            $data_bagian=$get_data->getData();

            if($data_bagian->total === 0){
                return response()->json(['status'=>false, 'msg'=>'There is no data found']);
            }


            $data_tree=$data_bagian->tree;
            $jumlah_data_tree=count($data_tree);
            $level=$request->level;
            $data_dihapus=[];
            $lvl1=$lvl2=$lvl3=$lvl4=$lvl5=[];
            for($x=0;$x<$jumlah_data_tree;$x++){
                if($level === "parent"){
                    $parent=$data_tree;
                    $fetch_parent=$this->fetchIndikatorParent($parent, $id_indikator, $x);
                    if($fetch_parent !== false){
                        $fetch_lvl1=$this->fetchIndikatorLvl1($fetch_parent['arr_lvl1'], $fetch_parent['jumlah_lvl1']);
                        $fetch_lvl2=$this->fetchIndikatorLvl2($fetch_lvl1['arr_lvl2']);
                        $fetch_lvl3=$this->fetchIndikatorLvl3($fetch_lvl2['arr_lvl3']);
                        $fetch_lvl4=$this->fetchIndikatorLvl4($fetch_lvl3['arr_lvl4']);
                        $fetch_lvl5=$this->fetchIndikatorLvl5($fetch_lvl4['arr_lvl5']);
                        break;
                    }
                }else if($level === "lvl1"){
                    $parent=null;
                    if(isset($data_tree[$x]->lvl1)){
                        $parent=$data_tree[$x]->lvl1;
                        $fetch_lvl1=$this->fetchIndikatorParentLvl1($parent, $id_indikator);
                        if($fetch_lvl1 !== false){
                            $fetch_lvl2=$this->fetchIndikatorLvl2($fetch_lvl1['arr_lvl2']);
                            $fetch_lvl3=$this->fetchIndikatorLvl3($fetch_lvl2['arr_lvl3']);
                            $fetch_lvl4=$this->fetchIndikatorLvl4($fetch_lvl3['arr_lvl4']);
                            $fetch_lvl5=$this->fetchIndikatorLvl5($fetch_lvl4['arr_lvl5']);
                            break;
                        }
                    }
                }else if($level === "lvl2"){
                    $parent=null;
                    if(isset($data_tree[$x]->lvl1)){
                        $jumlah_lvl1=count($data_tree[$x]->lvl1);
                        for($y=0;$y<$jumlah_lvl1;$y++){
                            if(isset($data_tree[$x]->lvl1[$y]->lvl2)){
                                $parent=$data_tree[$x]->lvl1[$y]->lvl2;
                                $fetch_lvl2=$this->fetchIndikatorParentLvl2($parent, $id_indikator);
                                if($fetch_lvl2 !== false){
                                    $fetch_lvl3=$this->fetchIndikatorLvl3($fetch_lvl2['arr_lvl3']);
                                    $fetch_lvl4=$this->fetchIndikatorLvl4($fetch_lvl3['arr_lvl4']);
                                    $fetch_lvl5=$this->fetchIndikatorLvl5($fetch_lvl4['arr_lvl5']);
                                    break;
                                }
                            }
                        }
                    }
                }else if($level === "lvl3"){
                    $parent=null;
                    if(isset($data_tree[$x]->lvl1)){
                        $jumlah_lvl1=count($data_tree[$x]->lvl1);
                        for($y=0;$y<$jumlah_lvl1;$y++){
                            if(isset($data_tree[$x]->lvl1[$y]->lvl2)){
                                $jumlah_lvl2=count($data_tree[$x]->lvl1[$y]->lvl2);
                                for($z=0;$z<$jumlah_lvl2;$z++){
                                    if(isset($data_tree[$x]->lvl1[$y]->lvl2[$z]->lvl3)){
                                        $parent=$data_tree[$x]->lvl1[$y]->lvl2[$z]->lvl3;
                                        $fetch_lvl3=$this->fetchIndikatorParentLvl3($parent, $id_indikator);
                                        if($fetch_lvl3 !== false){
                                            $fetch_lvl4=$this->fetchIndikatorLvl4($fetch_lvl3['arr_lvl4']);
                                            $fetch_lvl5=$this->fetchIndikatorLvl5($fetch_lvl4['arr_lvl5']);
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }else if($level === "lvl4"){
                    $parent=null;
                    if(isset($data_tree[$x]->lvl1)){
                        $jumlah_lvl1=count($data_tree[$x]->lvl1);
                        for($y=0;$y<$jumlah_lvl1;$y++){
                            if(isset($data_tree[$x]->lvl1[$y]->lvl2)){
                                $jumlah_lvl2=count($data_tree[$x]->lvl1[$y]->lvl2);
                                for($z=0;$z<$jumlah_lvl2;$z++){
                                    if(isset($data_tree[$x]->lvl1[$y]->lvl2[$z]->lvl3)){
                                        $jumlah_lvl3=count($data_tree[$x]->lvl1[$y]->lvl2[$z]->lvl3);
                                        for($a=0;$a<$jumlah_lvl3;$a++){
                                            if(isset($data_tree[$x]->lvl1[$y]->lvl2[$z]->lvl3[$a]->lvl4)){
                                                $parent=$data_tree[$x]->lvl1[$y]->lvl2[$z]->lvl3[$a]->lvl4;
                                                $fetch_lvl4=$this->fetchIndikatorParentLvl4($parent, $id_indikator);
                                                if($fetch_lvl4 !== false){
                                                    $fetch_lvl5=$this->fetchIndikatorLvl5($fetch_lvl4['arr_lvl5']);
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }else if($level === "lvl5"){
                    $lvl5=[];
                    array_push($lvl5, $id_indikator);
                    $fetch_lvl5['lvl5']=$lvl5;
                }
            }
            $data_dihapus['parent']=isset($fetch_parent['parent']) ? $fetch_parent['parent'] : [];
            $data_dihapus['lvl1']=isset($fetch_lvl1['lvl1']) ? $fetch_lvl1['lvl1'] : [];
            $data_dihapus['lvl2']=isset($fetch_lvl2['lvl2']) ? $fetch_lvl2['lvl2'] : [];
            $data_dihapus['lvl3']=isset($fetch_lvl3['lvl3']) ? $fetch_lvl3['lvl3'] : []; 
            $data_dihapus['lvl4']=isset($fetch_lvl4['lvl4']) ? $fetch_lvl4['lvl4'] : [];
            $data_dihapus['lvl5']=isset($fetch_lvl5['lvl5']) ? $fetch_lvl5['lvl5'] : [];
            $delete_data=$this->deleteIndikatorBagianById($data_dihapus['lvl1'], $data_dihapus['lvl2'], $data_dihapus['lvl3'], $data_dihapus['lvl4'], $data_dihapus['lvl5'], $data_dihapus['parent'], $id_bagian, $year);
            if($delete_data){
                $msg="Berhasil menghapus data ";
            }else{
                $msg="Terjadi kesalahan sistem saat menghapus data";
            }
        }catch(DecryptException $e){
            $msg="Invalid token";
        }
        return response()->json(['status'=>$delete_data, 'msg'=>$msg]);
    }
    public function deleteIndikatorBagianById($lvl1, $lvl2, $lvl3, $lvl4, $lvl5, $parent, $id_bagian, $year){
        $deleted=0;
        if(count($lvl1) > 0){
            for($x=0;$x<count($lvl1);$x++){
                $delete_lvl1=Indikator_lvl1_user::whereIn('id_indikator_lvl1', $lvl1)
                                        ->where('id_bagian', $id_bagian)
                                        ->where('tahun', $year)
                                        ->delete();
                $deleted+=$delete_lvl1;
            }
        }
        if(count($lvl2) > 0){
            for($x=0;$x<count($lvl2);$x++){
                $delete_lvl2=Indikator_lvl2_user::whereIn('id_indikator_lvl2', $lvl2)
                                        ->where('id_bagian', $id_bagian)
                                        ->where('tahun', $year)
                                        ->delete();
                $deleted+=$delete_lvl2;
            }
        }
        if(count($lvl3) > 0){
            for($x=0;$x<count($lvl3);$x++){
                $delete_lvl3=Indikator_lvl3_user::whereIn('id_indikator_lvl3', $lvl3)
                                        ->where('id_bagian', $id_bagian)
                                        ->where('tahun', $year)
                                        ->delete();
                $deleted+=$delete_lvl3;
            }
        }
        if(count($lvl4) > 0){
            for($x=0;$x<count($lvl4);$x++){
                $delete_lvl4=Indikator_lvl4_user::whereIn('id_indikator_lvl4', $lvl4)
                                        ->where('id_bagian', $id_bagian)
                                        ->where('tahun', $year)
                                        ->delete();
                $deleted+=$delete_lvl4;
            }
        }
        if(count($lvl5) > 0){
            for($x=0;$x<count($lvl5);$x++){
                $delete_lvl5=Indikator_lvl5_user::whereIn('id_indikator_lvl5', $lvl5)
                                        ->where('id_bagian', $id_bagian)
                                        ->where('tahun', $year)
                                        ->delete();
                $deleted+=$delete_lvl5;
            }
        }
        if(count($parent) > 0){
            for($x=0;$x<count($parent);$x++){
                $delete_parent=Indikator_user::whereIn('id_indikator', $parent)
                                        ->where('id_bagian', $id_bagian)
                                        ->where('tahun', $year)
                                        ->delete();
                $deleted+=$delete_parent;
            }
        }
        $must_delete=count($lvl1)+count($lvl2)+count($lvl3)+count($lvl4)+count($lvl5)+count($parent);
        if($must_delete === $deleted){
            return true;
        }
        return false;
    }
    public function fetchIndikatorParent($data_tree, $id_indikator, $x, $parent = []){
        $arr_lvl1=[];
        $jumlah_lvl1=0;
        if((int)$data_tree[$x]->id === (int)$id_indikator){
            array_push($parent, $data_tree[$x]->id);
            if(isset($data_tree[$x]->lvl1)){
                $jumlah_lvl1=count($data_tree[$x]->lvl1);
                for($a=0;$a<$jumlah_lvl1;$a++){
                    $arr_lvl1[$a]=$data_tree[$x]->lvl1[$a];
                }
            }
            $jumlah_lvl1=count($arr_lvl1);
            return [
                'parent'=>$parent,
                'arr_lvl1'=>$arr_lvl1,
                'jumlah_lvl1'=>$jumlah_lvl1
            ];
        }
        return false;
    }

    public function fetchIndikatorParentLvl1($arr_lvl1, $id_indikator, $lvl1 = []){
        $jumlah_lvl1=count($arr_lvl1);
        for($x=0;$x<$jumlah_lvl1;$x++){
            if($arr_lvl1[$x]->id === $id_indikator){
                array_push($lvl1, $arr_lvl1[$x]->id);
                $arr_lvl2=isset($arr_lvl1[$x]->lvl2) ? $arr_lvl1[$x]->lvl2 : [];
                return [
                    'lvl1'=>$lvl1,
                    'arr_lvl2'=>$arr_lvl2,
                ];
            }
        }
        return false;
    }

    public function fetchIndikatorParentLvl2($arr_lvl2, $id_indikator, $lvl2 = []){
        $jumlah_lvl2=count($arr_lvl2);
        for($x=0;$x<$jumlah_lvl2;$x++){
            if($arr_lvl2[$x]->id === $id_indikator){
                array_push($lvl2, $arr_lvl2[$x]->id);
                $arr_lvl3=isset($arr_lvl2[$x]->lvl3) ? $arr_lvl2[$x]->lvl3 : [];
                return [
                    'lvl2'=>$lvl2,
                    'arr_lvl3'=>$arr_lvl3,
                ];
            }
        }
        return false;
    }

    public function fetchIndikatorParentLvl3($arr_lvl3, $id_indikator, $lvl3 = []){
        $jumlah_lvl3=count($arr_lvl3);
        for($x=0;$x<$jumlah_lvl3;$x++){
            if($arr_lvl3[$x]->id === $id_indikator){
                array_push($lvl3, $arr_lvl3[$x]->id);
                $arr_lvl4=isset($arr_lvl3[$x]->lvl4) ? $arr_lvl3[$x]->lvl4 : [];
                return [
                    'lvl3'=>$lvl3,
                    'arr_lvl4'=>$arr_lvl4,
                ];
            }
        }
        return false;
    }

    public function fetchIndikatorParentLvl4($arr_lvl4, $id_indikator, $lvl4 = []){
        $jumlah_lvl4=count($arr_lvl4);
        for($x=0;$x<$jumlah_lvl4;$x++){
            if($arr_lvl4[$x]->id === $id_indikator){
                array_push($lvl4, $arr_lvl4[$x]->id);
                $arr_lvl5=isset($arr_lvl4[$x]->lvl5) ? $arr_lvl4[$x]->lvl5 : [];
                return [
                    'lvl4'=>$lvl4,
                    'arr_lvl5'=>$arr_lvl5,
                ];
            }
        }
        return false;
    }


    public function fetchIndikatorLvl1($arr_lvl1, $jumlah_lvl1, $lvl1 = []){
        $arr_lvl2=[];
        for($y=0;$y<$jumlah_lvl1;$y++){
            array_push($lvl1, $arr_lvl1[$y]->id);
            if(isset($arr_lvl1[$y]->lvl2)){
                $jumlah_lvl2=count($arr_lvl1[$y]->lvl2);
                for($a=0;$a<$jumlah_lvl2;$a++){
                    $arr_lvl2[count($arr_lvl2)]=$arr_lvl1[$y]->lvl2[$a];
                }
            }
        }
        return [
            'lvl1'=>$lvl1,
            'arr_lvl2'=>$arr_lvl2
        ];
    }
    public function fetchIndikatorLvl2($arr_lvl2, $lvl2 = []){
        $jumlah_lvl2=count($arr_lvl2);
        $arr_lvl3=[];
        for($z=0;$z<$jumlah_lvl2;$z++){
            array_push($lvl2, $arr_lvl2[$z]->id);
            if(isset($arr_lvl2[$z]->lvl3)){
                $jumlah_lvl3=count($arr_lvl2[$z]->lvl3);
                for($a=0;$a<$jumlah_lvl3;$a++){
                    $arr_lvl3[count($arr_lvl3)]=$arr_lvl2[$z]->lvl3[$a];
                }
            }
        }
        return [
            'lvl2'=>$lvl2,
            'arr_lvl3'=>$arr_lvl3,
        ];
    }
    public function fetchIndikatorLvl3($arr_lvl3, $lvl3 = []){
        $jumlah_lvl3=count($arr_lvl3);
        $arr_lvl4=[];
        for($a=0;$a<$jumlah_lvl3;$a++){
            array_push($lvl3, $arr_lvl3[$a]->id);
            if(isset($arr_lvl3[$a]->lvl4)){
                $jumlah_lvl4=count($arr_lvl3[$a]->lvl4);
                for($b=0;$b<$jumlah_lvl4;$b++){
                    $arr_lvl4[count($arr_lvl4)]=$arr_lvl3[$a]->lvl4[$b];
                }
            }
        }
        return [
            'lvl3'=>$lvl3,
            'arr_lvl4'=>$arr_lvl4
        ];
    }
    public function fetchIndikatorLvl4($arr_lvl4, $lvl4 = []){
        $jumlah_lvl4=count($arr_lvl4);
        $arr_lvl5=[];
        for($a=0;$a<$jumlah_lvl4;$a++){
            array_push($lvl4, $arr_lvl4[$a]->id);
            if(isset($arr_lvl4[$a]->lvl5)){
                $jumlah_lvl5=count($arr_lvl4[$a]->lvl5);
                for($b=0;$b<$jumlah_lvl5;$b++){
                    $arr_lvl5[count($arr_lvl5)]=$arr_lvl4[$a]->lvl5[$b];
                }
            }
        }
        return [
            'lvl4'=>$lvl4,
            'arr_lvl5'=>$arr_lvl5
        ];
    }
    public function fetchIndikatorLvl5($arr_lvl5, $lvl5 = []){
        $jumlah_lvl5=count($arr_lvl5);
        $arr_lvl6=[];
        for($a=0;$a<$jumlah_lvl5;$a++){
            array_push($lvl5, $arr_lvl5[$a]->id);
            // $arr_lvl5=isset($arr_lvl4[$a]->lvl4) ? $arr_lvl4[$a]->lvl5 : [];
        }
        return [
            'lvl5'=>$lvl5,
            'arr_lvl6'=>$arr_lvl6
        ];
    }

    public function saveChecklistBagian(Request $request){
        $status=false;

        try{
            $validate=$request->validate([
                'parent_id'=> ['required'],
                'id_bagian_enc'=>['required'],
                'tipe_indikator'=> ['required'],
                'level' => ['required', 'digits:1'],
                'nama_file' => ['required'],
                'periode' => ['required', 'digits:1'],
                'tahun' => ['required', 'digits:4'],
            ]);
            try{
                $parent_id=Crypt::decrypt($request->parent_id);
                $id_bagian=Crypt::decrypt($request->id_bagian_enc);
                $tipe=$request->tipe_indikator;
                $level=$request->level;
                $file_name=$request->nama_file;
                $periode=$request->periode;
                $tahun=$request->tahun;
    
                $db=["indikator_lvl1_user", "indikator_lvl2_user", "indikator_lvl3_user", "indikator_lvl4_user", "indikator_lvl5_user"];
                $indikator_id=['id_indikator_lvl1', 'id_indikator_lvl2', 'id_indikator_lvl3', 'id_indikator_lvl4', 'id_indikator_lvl5'];
                $db_master=["Ampuh_sub_indikator_lvl1", "Ampuh_sub_indikator_lvl2", "Ampuh_sub_indikator_lvl3", "Ampuh_sub_indikator_lvl4", "Ampuh_sub_indikator_lvl5"];
                $get_data=app("App\\Models\\".$db_master[$level-1])::join($db[$level-1].' as b', function($join) use($id_bagian, $indikator_id, $level, $db_master, $tahun){
                                                                        $join->on("b.".$indikator_id[$level-1], '=', strtolower($db_master[$level-1]).".id")
                                                                        ->where('id_bagian', $id_bagian)
                                                                        ->where('tahun', $tahun);
                                                                    })
                                                                ->where(strtolower($db_master[$level-1]).".id", $parent_id)
                                                                ->select('b.id as id_indikator_user', 'b.tahun', strtolower($db_master[$level-1]).".gd_id")
                                                                ->first();
                if(!is_null($get_data)){
                    $alpha_code=['A', 'B', 'C', 'D', 'E', 'F', 'G'];
                    if($tipe === "file"){
                        $createFolder=$this->googleDriveService->createFolder($file_name, $get_data['gd_id']);
                        if($createFolder){
                            $file_code=$alpha_code[$level-1]."-".$get_data['id_indikator_user'];
                            $get_exists_file=Master_file_indikator::where('file_code', $file_code)->get();
                            $jumlah=$get_exists_file->count();
                            $new_file=new Master_file_indikator;
                            $new_file->id_bagian=$id_bagian;
                            $new_file->file_code=$file_code;
                            $new_file->no_urut=$jumlah+1;
                            $new_file->file_name=$file_name;
                            $new_file->periode=$periode;
                            $new_file->gd_id=$createFolder;
                            $save_file=$new_file->save();
                            if($save_file){
                                $id_master=$new_file->id;
                                $generate_edoc=$this->generateEdocTimeline($periode, $id_master, $get_data['tahun']);
                                if($generate_edoc['status']){
                                    $status=true;
                                    $msg="Berhasil menyimpan data ";
                                }else{
                                    $msg=$generate_edoc['msg'];
                                }
                            }else{
                                $msg="Terjadi kesalahan saat menyimpan data master";
                            }
                        }else{
                            $msg="Folder not created. Please contact timdevel";
                        }
                    }else if($tipe === "folder"){
        
                    }else{
                        $msg="Invalid data";
                    }
                }else{
                    $msg="Data tidak ditemukan";
                }
            }catch(DecryptException $e){
                $msg="Invalid token";
            }
        }catch(ValidationException $e){
            $msg=$e->validator->errors()->first();
        }
        return response()->json(['status'=>$status, 'msg'=>$msg]);
    }

    public function generateEdocTimeline($periode, $id_master, $tahun){
        $saved=false;
        $saved_data=0;
        $periode=$periode-1;
        $const_periode=['perbulan', 'per triwulan', 'per semester', 'per tahun'];
        $month=['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'Septmber', 'Oktober', 'November', 'Desember'];
        if($periode === 0){
            for($x=0;$x<12;$x++){
                $bulan=($x+1) < 10 ? '0'.($x+1) : ($x+1);
                $edoc=new Edoc_indikator;
                $edoc->id_master=$id_master;
                $edoc->edoc=null;
                $edoc->periode=$periode+1;
                $edoc->timeline=$month[$x];
                $edoc->max_fill_at= $tahun."-". $bulan .'-01';
                if($edoc->save()){
                    $saved_data+=1;
                }
            }

            if($saved_data === 12){
                $saved=true;
                $msg="All data saved";
            }else{
                $msg="There is some data not save ".$saved_data;
            }
        }else if((int)$periode === 1){
            //per triwulan;
            for($x=0;$x<4;$x++){
                $bulan=($x*3+3) < 10 ? '0'.($x*3+3) : ($x*3+3);
                $edoc=new Edoc_indikator;
                $edoc->id_master=$id_master;
                $edoc->edoc=null;
                $edoc->timeline=$month[$x*3]."-".$month[$x*3+3-1];
                $edoc->max_fill_at=$tahun."-".$bulan."-01";
                $edoc->periode=$periode+1;
                if($edoc->save()){
                    $saved_data+=1;
                }
            }
            if($saved_data === 4){
                $saved = true;
                $msg="All data saved";
            }else{
                $msg="There is some data (triwulan) not save ".$saved_data;
            }
        }else if((int)$periode === 2){
            for($x=0;$x<2;$x++){
                $bulan=($x*6+5) < 10 ? '0'.($x*6+5) : ($x*6+5);
                $edoc=new Edoc_indikator;
                $edoc->id_master=$id_master;
                $edoc->edoc=null;
                $edoc->timeline=$month[$x*6]."-".$month[$x*6+5];
                $edoc->max_fill_at=$tahun."-".$bulan."-01";
                $edoc->periode=$periode+1;
                if($edoc->save()){
                    $saved_data+=1;
                }
            }
            if($saved_data === 2){
                $saved = true;
                $msg="All data saved";
            }else{
                $msg="There is some data (triwulan) not save ".$saved_data;
            }
        }else if((int)$periode === 3){
            $edoc=new Edoc_indikator;
            $edoc->id_master=$id_master;
            $edoc->edoc=null;
            $edoc->timeline='Januari - Desember';
            $edoc->max_fill_at=$tahun."-01-01";
            $edoc->periode=$periode+1;
            if($edoc->save()){
                $saved=true;
                $msg="All data saved";
            }else{
                $msg="There is problem while save data";
            }
        }else{
            $msg="Periode belum ditentukan";
        }

        return [
            'status' => $saved,
            'msg' =>$msg
        ];
    }

    public function generateChecklistBagian(Request $request){
        $status=false;
        try{
            $validate=$request->validate([
                'tahun'=> ['required', 'digits:4', 'integer'],
                'token'=>['required', 'string'],
                'current_year'=>['required', 'digits:4', 'integer']
            ]);
            try{
                $id_bagian=Crypt::decrypt($request->token);
                $this_year=date('Y');
                if((int)$this_year === (int)$request->current_year){
                    $generate_bagian=$this->checklistBagianService->generateChecklistBagian($request->tahun, $id_bagian, $this_year);
                    if($generate_bagian['status']){
                        $status=true;
                    }
                    $msg=$generate_bagian['msg'];
                }else{
                    $msg="Data tahun generate harus tahun berjalan ".$request->current_year;
                }
            }catch(DecryptException $e){
                $msg="Data tidak valid";
            }
        }catch(ValidationException $e){
            $msg=$e->validator->errors()->first();
        }
        return response()->json(['status'=>$status, 'msg'=>$msg]);
    }

    public function removeChecklistBagian(Request $request){
        $status=false;
        try{
            $request->validate([
                'year'=>['required', 'digits:4', 'integer'],
                'bagian_text'=>['required', 'string'],
                'id_bagian'=>['required', 'string']
            ]);
            try{
                $id_bagian=Crypt::decrypt($request->id_bagian);
                $year=(int)$request->year;
                $this_year=date("Y");
                if($year === (int)$this_year){
                    $remove=$this->checklistBagianService->removeChecklistByYear($request->year, $request->bagian_text, $id_bagian);
                    $status=$remove['status'];
                    $msg=$remove['msg'];   
                }else{
                    $msg="Tidak bisa menghapus checklist ini    ";
                }
            }catch(DecryptException $e){
                $msg="Data tidak valid";
            }
        }catch(ValidationException $e){
            $msg=$e->validator->errors()->first();
        }

        return response()->json(['status'=>$status, 'msg'=>$msg]);
    }
}
