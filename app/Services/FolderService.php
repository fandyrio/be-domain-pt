<?php
    namespace App\Services;
    use App\Models\Ampuh_indikator;
    use App\Models\Ampuh_sub_indikator_lvl1;
    use App\Models\Ampuh_sub_indikator_lvl2;
    use App\Models\Ampuh_sub_indikator_lvl3;
    use App\Models\Ampuh_sub_indikator_lvl4;
    use App\Models\Ampuh_sub_indikator_lvl5;

    class FolderService{
        public function __construct(){
        }
        public function getDataFolderByParentID($parent_id, $level){
            $valid=true;
            if((int)$level === 0){
                $get_indikator=Ampuh_indikator::select('ampuh_indikator.indikator_name', 'ampuh_indikator.gd_id', 'ampuh_indikator.id')
                                    ->orderBy('ampuh_indikator.indikator_name', 'asc')
                                    ->get();
            }elseif((int)$level === 1){
                $get_indikator=Ampuh_sub_indikator_lvl1::join('ampuh_indikator as ai', function($join) use($parent_id){
                                                            $join->on('ampuh_sub_indikator_lvl1.indikator_id', '=', 'ai.id')
                                                            ->where('ai.gd_id', $parent_id);
                                                        })
                            ->select('ampuh_sub_indikator_lvl1.sub_indikator_name', 'Ampuh_sub_indikator_lvl1.id', 'Ampuh_sub_indikator_lvl1.gd_id', 'ai.indikator_name as parent_indikator_name')
                            ->get();
            }elseif((int)$level === 2){
                $get_indikator=Ampuh_sub_indikator_lvl2::join('ampuh_sub_indikator_lvl1 as lvl1', function($join) use($parent_id){
                                                            $join->on('ampuh_sub_indikator_lvl2.sub_indikator_lvl1_id', '=', 'lvl1.id')
                                                                ->where('lvl1.gd_id', $parent_id);
                                                        })
                                        ->select('ampuh_sub_indikator_lvl2.id', 'ampuh_sub_indikator_lvl2.gd_id', 'ampuh_sub_indikator_lvl2.sub_indikator_name', 'lvl1.sub_indikator_name as parent_indikator_name')
                                        ->get();
            }else{
                $valid=false;
                $total=0;
                $get_indikator=null;
            }

            if($valid){
                $total=$get_indikator->count();
            }
            return [
                'total'=>$total,
                'data'=>$get_indikator
            ];
        }

        //tujuan : Untuk mengambil untuk mengabil data indikator berdasarkan level. Tiap level memiliki table masing - masing
        //pemakaian : 
        //1.Soft Delete Pada folderSubfolderController , renameFolder (this function)
        public function getFolderByLevel($gd_id, $level){
            $get_data=null;
            if((int)$level === 0){
                $get_data=Ampuh_indikator::where('gd_id', $gd_id)->first();
            }elseif((int)$level === 1){
                $get_data=Ampuh_sub_indikator_lvl1::where('gd_id', $gd_id)->first();
            }elseif((int)$level === 2){
                $get_data=Ampuh_sub_indikator_lvl2::where('gd_id', $gd_id)->first();
            }elseif((int)$level === 3){
                $get_data=Ampuh_sub_indikator_lvl3::where('gd_id', $gd_id)->first();
            }else if((int)$level === 4){
                $get_data=Ampuh_sub_indikator_lvl4::where('gd_id', $gd_id)->first();
            }
            return $get_data;
            
        }
        public function softDeleteFolder($data){
            $data->rule_id=0;
            $data->detil_rule_id=0;
            if($data->update()){
                $msg="Success to delete data";
                $status=true;
            }else{
                $msg="Tidak dapat melakukan penghapusan";
                $status=false;
            }

            return [
                'status'=>$status,
                'msg'=>$msg,
            ];
        }

        public function renameFolder($data){
            $level=$data['level'];
            $gd_id=$data['gd_id'];
            $indikator_name=null;
            $msg="";
            $get_folder=$this->getFolderByLevel($gd_id, $level);
            $update=false;
            if((int)$level === 0){
                $indikator_name='indikator_name';
            }elseif((int)$level >= 1){
                $indikator_name="sub_indikator_name";
            }
            if(!is_null($get_folder)){
                $get_folder->$indikator_name=$data['new_name'];
                $update=$get_folder->update();
                if($update){
                    $msg="Berhasil mengubah data";
                }else{
                    $msg="Terjadi kesalahan sistem saat mengubah data";
                }
            }else{
                $msg="Data tidak ditemukan saat update data";
            }
            // return response()->json(['status'=>$update, 'msg'=>$msg]);
            return[
                'status'=>$update,
                'msg'=>$msg
            ];
        }
    }
?>