<?php
    namespace App\Services;

use App\Models\Bagian;
use App\Models\Indikator_user;
    use App\Models\Indikator_lvl1_user;
    use App\Models\Indikator_lvl2_user;
    use App\Models\Indikator_lvl3_user;
    use App\Models\Indikator_lvl4_user;
    use App\Models\Indikator_lvl5_user;
    use Illuminate\Support\Facades\DB;

    class ChecklistBagianService{
        public function generateChecklistBagian($tahun, $id_bagian, $year_generate){
            //1. generate indikator
            ///1.1 Level 0 (Indikator user)
            $save=false;
            try{
                $get_indikator_user=Indikator_user::where('id_bagian', $id_bagian)
                                        ->where('tahun', $year_generate)
                                        ->get();
                $jlh_indikator_user=$get_indikator_user->count();
                if($jlh_indikator_user === 0){
                    DB::beginTransaction();
                    $get_indikator_user_before=Indikator_user::where('id_bagian', $id_bagian)
                                        ->where('tahun', $tahun)
                                        ->get();
                    foreach($get_indikator_user_before as $list_indikator_user){
                        $indikator_user=new Indikator_user;
                        $indikator_user->id_indikator=$list_indikator_user['id_indikator'];
                        $indikator_user->id_bagian=$id_bagian;
                        $indikator_user->has_child=0;
                        $indikator_user->periode=$list_indikator_user['periode'];
                        $indikator_user->tahun=$year_generate;
                        $indikator_user->is_folder_bagian=$list_indikator_user['is_folder_bagian'];
                        $indikator_user->save();
                    }
                    $get_sub_indikator_lvl1=Indikator_lvl1_user::where('id_bagian', $id_bagian)
                                                ->where('tahun', $year_generate)
                                                ->get();
                    $jlh_sub_indiaktor_lvl1=$get_sub_indikator_lvl1->count();
                    if($jlh_sub_indiaktor_lvl1 === 0){
                        $get_sub_indikator_lvl1_before=Indikator_lvl1_user::where('id_bagian', $id_bagian)
                                                ->where('tahun', $tahun)
                                                ->get();
                        foreach($get_sub_indikator_lvl1_before as $list_sub_indiaktor_lvl1){
                            $sub_indikator_lvl1=new Indikator_lvl1_user;
                            $sub_indikator_lvl1->id_indikator_lvl1=$list_sub_indiaktor_lvl1['id_indikator_lvl1'];
                            $sub_indikator_lvl1->id_bagian=$id_bagian;
                            $sub_indikator_lvl1->has_child=$list_sub_indiaktor_lvl1['has_child'];
                            $sub_indikator_lvl1->periode=$list_sub_indiaktor_lvl1['periode'];
                            $sub_indikator_lvl1->tahun=$year_generate;
                            $sub_indikator_lvl1->is_folder_bagian=$list_sub_indiaktor_lvl1['is_folder_bagian'];
                            $sub_indikator_lvl1->save();
                        }
                        $get_sub_indikator_lvl2=Indikator_lvl2_user::where('id_bagian', $id_bagian)
                                                ->where('tahun', $year_generate)
                                                ->get();
                        $jlh_sub_indiaktor_lvl2=$get_sub_indikator_lvl2->count();
                        if($jlh_sub_indiaktor_lvl2 === 0){
                            $get_sub_indikator_lvl2_before=Indikator_lvl2_user::where('id_bagian', $id_bagian)
                                                ->where('tahun', $tahun)
                                                ->get();
                            foreach($get_sub_indikator_lvl2_before as $list_sub_indiaktor_lvl2){
                                $sub_indikator_lvl2=new Indikator_lvl2_user;
                                $sub_indikator_lvl2->id_indikator_lvl2=$list_sub_indiaktor_lvl2['id_indikator_lvl2'];
                                $sub_indikator_lvl2->id_bagian=$id_bagian;
                                $sub_indikator_lvl2->has_child=$list_sub_indiaktor_lvl2['has_child'];
                                $sub_indikator_lvl2->periode=$list_sub_indiaktor_lvl2['periode'];
                                $sub_indikator_lvl2->tahun=$year_generate;
                                $sub_indikator_lvl2->is_folder_bagian=$list_sub_indiaktor_lvl2['is_folder_bagian'];
                                $sub_indikator_lvl2->save();
                            }
                            $get_sub_indikator_lvl3=Indikator_lvl3_user::where('id_bagian', $id_bagian)
                                                ->where('tahun', $year_generate)
                                                ->get();
                            $jlh_sub_indiaktor_lvl3=$get_sub_indikator_lvl3->count();
                            if($jlh_sub_indiaktor_lvl3 === 0){
                                $get_sub_indikator_lvl3_before=Indikator_lvl3_user::where('id_bagian', $id_bagian)
                                                ->where('tahun', $tahun)
                                                ->get();
                                foreach($get_sub_indikator_lvl3_before as $list_sub_indiaktor_lvl3){
                                    $sub_indikator_lvl3=new Indikator_lvl3_user;
                                    $sub_indikator_lvl3->id_indikator_lvl3=$list_sub_indiaktor_lvl3['id_indikator_lvl3'];
                                    $sub_indikator_lvl3->id_bagian=$id_bagian;
                                    $sub_indikator_lvl3->has_child=$list_sub_indiaktor_lvl3['has_child'];
                                    $sub_indikator_lvl3->periode=$list_sub_indiaktor_lvl3['periode'];
                                    $sub_indikator_lvl3->tahun=$year_generate;
                                    $sub_indikator_lvl3->is_folder_bagian=$list_sub_indiaktor_lvl3['is_folder_bagian'];
                                    $sub_indikator_lvl3->save();
                                }
                                $get_sub_indikator_lvl4=Indikator_lvl4_user::where('id_bagian', $id_bagian)
                                                    ->where('tahun', $year_generate)
                                                    ->get();
                                $jlh_sub_indiaktor_lvl4=$get_sub_indikator_lvl4->count();
                                if($jlh_sub_indiaktor_lvl4 === 0){
                                    $get_sub_indikator_lvl4_before=Indikator_lvl4_user::where('id_bagian', $id_bagian)
                                                    ->where('tahun', $tahun)
                                                    ->get();
                                    foreach($get_sub_indikator_lvl4_before as $list_sub_indiaktor_lvl4){
                                        $sub_indikator_lvl4=new Indikator_lvl4_user;
                                        $sub_indikator_lvl4->id_indikator_lvl4=$list_sub_indiaktor_lvl4['id_indikator_lvl4'];
                                        $sub_indikator_lvl4->id_bagian=$id_bagian;
                                        $sub_indikator_lvl4->has_child=$list_sub_indiaktor_lvl4['has_child'];
                                        $sub_indikator_lvl4->periode=$list_sub_indiaktor_lvl4['periode'];
                                        $sub_indikator_lvl4->tahun=$year_generate;
                                        $sub_indikator_lvl4->is_folder_bagian=$list_sub_indiaktor_lvl4['is_folder_bagian'];
                                        $sub_indikator_lvl4->save();
                                    }
                                    $get_sub_indikator_lvl5=Indikator_lvl5_user::where('id_bagian', $id_bagian)
                                                        ->where('tahun', $year_generate)
                                                        ->get();
                                    $jlh_sub_indiaktor_lvl5=$get_sub_indikator_lvl5->count();
                                    if($jlh_sub_indiaktor_lvl5 === 0){
                                        $get_sub_indikator_lvl5_before=Indikator_lvl5_user::where('id_bagian', $id_bagian)
                                                        ->where('tahun', $tahun)
                                                        ->get();
                                        foreach($get_sub_indikator_lvl5_before as $list_sub_indiaktor_lvl5){
                                            $sub_indikator_lvl5=new Indikator_lvl5_user;
                                            $sub_indikator_lvl5->id_indikator_lvl5=$list_sub_indiaktor_lvl5['id_indikator_lvl5'];
                                            $sub_indikator_lvl5->id_bagian=$id_bagian;
                                            $sub_indikator_lvl5->has_child=$list_sub_indiaktor_lvl5['has_child'];
                                            $sub_indikator_lvl5->periode=$list_sub_indiaktor_lvl5['periode'];
                                            $sub_indikator_lvl5->tahun=$year_generate;
                                            $sub_indikator_lvl5->is_folder_bagian=$list_sub_indiaktor_lvl5['is_folder_bagian'];
                                            $sub_indikator_lvl5->save();
                                        }
                                    }
                                }
                            }
                        }
                    }
                    DB::commit();
                    $save=true;
                    $msg="Berhasil Generate Checklist";
                }else{
                    $msg="Data sudah ada. Silahkan lanjutkan";
                }
            }catch(\Exception $e){
                DB::rollBack();
                $msg=$e->getMessage();
            }

            return [
                'status'=>$save,
                'msg'=>$msg
            ];
        }

        public function removeChecklistByYear($year, $bagian_text, $id_bagian){
            $status=false;
            $msg="";
            try{
                DB::beginTransaction();
                    $get_data=Bagian::where("alias", $bagian_text)
                                ->where('id', $id_bagian)
                                ->first();
                    if(!is_null($get_data)){
                        DB::table("indikator_user")->where("tahun", $year)
                            ->where('id_bagian', $id_bagian)
                            ->delete();
                        DB::table("indikator_lvl1_user")->where("tahun", $year)->where('id_bagian', $id_bagian)->delete();
                        DB::table("indikator_lvl2_user")->where("tahun", $year)->where('id_bagian', $id_bagian)->delete();
                        DB::table("indikator_lvl3_user")->where("tahun", $year)->where('id_bagian', $id_bagian)->delete();
                        DB::table("indikator_lvl4_user")->where("tahun", $year)->where('id_bagian', $id_bagian)->delete();
                        DB::table("indikator_lvl5_user")->where("tahun", $year)->where('id_bagian', $id_bagian)->delete();
                        DB::table("edoc_indikator")->whereRaw("year(max_fill_at) = ".$year)->delete();
                    }else{
                        throw new \Exception("Data Bagian tidak ditemukan");
                    }
                DB::commit();
                $status=true;
                $msg="Berhasil menghapus seluruh checklist";
            }catch(\Exception $e){
                DB::rollBack();
                $msg=$e->getMessage();
            }

            return ['status'=>$status, 'msg'=>$msg];
        }
    }
?>