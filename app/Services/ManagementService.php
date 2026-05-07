<?php
    namespace App\Services;
    use App\Models\Indikator_user;
    use Illuminate\Support\Facades\Crypt;
    use Illuminate\Contracts\Encryption\DecryptException;
    use App\Models\Master_file_indikator;
    use App\Models\Periode;
    use App\Models\Edoc_indikator;
    use Illuminate\Validation\ValidationException;
    use App\Services\GoogleDriveService;


    class ManagementService{
        protected $googleDriveService;
        protected $userServices;

        public function __construct(GoogleDriveService $google_drive_service, UserService $user_service){
            $this->googleDriveService=$google_drive_service;
            $this->userServices=$user_service;
        }

        public function getDataChecklistBagian($citizen_id, $id_bagian_enc, $year = null){
            $jumlah=0;
            $msg="";
            $status=false;
            $get_data=[];
            $data_tree=[];
            $isPjBagian=false;
            $isAdmin = false;
            $current_year=$year === null ? date('Y') : $year;
            try{
                $id_bagian=Crypt::decrypt($id_bagian_enc);
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
                                ->select('a.indikator_name as parent', 'b.sub_indikator_name as lvl1', 'd.sub_indikator_name as lvl2', 'f.sub_indikator_name as lvl3', 'h.sub_indikator_name as lvl4', 'j.sub_indikator_name as lvl5', 'indikator_user.id_indikator as id_parent', 'c.id as id_indikator_user_lvl1', 'c.id_indikator_lvl1 as id_lvl1', 'e.id as id_indikator_user_lvl2', 'e.id_indikator_lvl2 as id_lvl2', 'g.id as id_indikator_user_lvl3', 'g.id_indikator_lvl3 as id_lvl3', 'i.id as id_indikator_user_lvl4', 'i.id_indikator_lvl4 as id_lvl4', 'k.id as id_indikator_user_lvl5', 'k.id_indikator_lvl5 as id_lvl5', 'c.is_folder_bagian as folder_bagian_lvl1', 'e.is_folder_bagian as folder_bagian_lvl2', 'g.is_folder_bagian as folder_bagian_lvl3', 'i.is_folder_bagian as folder_bagian_lvl4', 'k.is_folder_bagian as folder_bagian_lvl5')
                                ->orderBy('a.indikator_name', 'asc')
                                ->orderBy('b.sub_indikator_name', 'asc')
                                ->orderBy('d.sub_indikator_name', 'asc')
                                ->orderBy('f.sub_indikator_name', 'asc')
                                ->orderBy('h.sub_indikator_name', 'asc')
                                ->orderBy('j.sub_indikator_name', 'asc')
                                ->get();
                $jumlah=$get_data->count();
                $data_tree=$this->treeData($get_data, $id_bagian);
                $isPjBagian=$this->userServices->isPjBagian($citizen_id, $id_bagian);
                $isAdmin=$this->userServices->isAdmin($citizen_id);
                $status=true;
            }catch(DecryptException $e){
                $msg="Invalid token";
            }

            return [
                'data_tree'=>$data_tree,
                'total'=>$jumlah,
                'msg'=>$msg,
                'status'=>$status,
                'isPj'=>$isPjBagian,
                'isAdmin'=>$isAdmin
            ];
        }
        public function treeData($data, $id_bagian){
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
                    $lvl1['is_folder_bagian']=(int)$list_data['folder_bagian_lvl1'];
                    //generate file master
                    if((int)$list_data['folder_bagian_lvl1'] === 1){
                        $get_file_master=$this->getFileMaster('A', $list_data['id_indikator_user_lvl1'], $id_bagian);
                        if((int)$get_file_master['jumlah'] > 0){
                            $lvl1['file_master']=$get_file_master['data'];
                        }
                    }

                    $lvl1['token']=Crypt::encrypt($list_data['id_lvl1']);
                    $idx_lvl2=$idx_lvl3=$idx_lvl4=$idx_lvl5=-1;
                }
                if($lvl2_before !== $list_data['id_lvl2'] && !is_null($list_data['id_lvl2'])){
                    $idx_lvl2+=1;
                    $lvl2=&$lvl1['lvl2'][$idx_lvl2];
                    $lvl2['nama']=$list_data['lvl2'];
                    $lvl2['id']=$list_data['id_lvl2'];
                    $lvl2['is_folder_bagian']=(int)$list_data['folder_bagian_lvl2'];
                    //generate file master
                    if((int)$list_data['folder_bagian_lvl2'] === 1){
                        $get_file_master=$this->getFileMaster('B', $list_data['id_indikator_user_lvl2'], $id_bagian);
                        if((int)$get_file_master['jumlah'] > 0){
                            $lvl2['file_master']=$get_file_master['data'];
                        }
                    }
                    $lvl2['token']=Crypt::encrypt($list_data['id_lvl2']);
                    $idx_lvl3=$idx_lvl4=$idx_lvl5=-1;
                    // $data_tree[$idx_parent]['lvl1'][$idx_lvl1]['lvl2'][$idx_lvl2]['lvl3']=null;
                }
    
                if($lvl3_before !== $list_data['id_lvl3'] && !is_null($list_data['id_lvl3'])){
                    $idx_lvl3+=1;
                    $lvl3=&$lvl2['lvl3'][$idx_lvl3];
                    $lvl3['nama']=$list_data['lvl3'];
                    $lvl3['id']=$list_data['id_lvl3'];
                    $lvl3['is_folder_bagian']=(int)$list_data['folder_bagian_lvl3'];
                    //generate file master
                    if((int)$list_data['folder_bagian_lvl3'] === 1){
                        $get_file_master=$this->getFileMaster('C', $list_data['id_indikator_user_lvl3'], $id_bagian);
                        if((int)$get_file_master['jumlah'] > 0){
                            $lvl3['file_master']=$get_file_master['data'];
                        }
                    }
                    $lvl3['token']=Crypt::encrypt($list_data['id_lvl3']);
                    $idx_lvl4=$idx_lvl5=-1;
                }
    
                if($lvl4_before !== $list_data['id_lvl4'] && !is_null($list_data['id_lvl4'])){
                    $idx_lvl4+=1;
                    $lvl4=&$lvl3['lvl4'][$idx_lvl4];
                    $lvl4['nama']=$list_data['lvl4'];
                    $lvl4['id']=$list_data['id_lvl4'];
                    $lvl4['is_folder_bagian']=(int)$list_data['folder_bagian_lvl4'];
                    //generate file master
                    if((int)$list_data['folder_bagian_lvl4'] === 1){
                        $get_file_master=$this->getFileMaster('D', $list_data['id_indikator_user_lvl4'], $id_bagian);
                        if((int)$get_file_master['jumlah'] > 0){
                            $lvl4['file_master']=$get_file_master['data'];
                        }
                    }
                    $lvl4['token']=Crypt::encrypt($list_data['id_lvl4']);
                    $idx_lvl5=-1;
                }
    
                if($lvl5_before !== $list_data['id_lvl5'] && !is_null($list_data['id_lvl5'])){
                    $idx_lvl5+=1;
                    $lvl5=&$lvl4['lvl5'][$idx_lvl5];
                    $lvl5['nama']=$list_data['lvl5'];
                    $lvl5['id']=$list_data['id_lvl5'];
                    $lvl5['is_folder_bagian']=(int)$list_data['folder_bagian_lvl5'];
                    //generate file master
                    if((int)$list_data['folder_bagian_lvl5'] === 1){
                        $get_file_master=$this->getFileMaster('E', $list_data['id_indikator_user_lvl5'], $id_bagian);
                        if((int)$get_file_master['jumlah'] > 0){
                            $lvl5['file_master']=$get_file_master['data'];
                        }
                    }
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

        public function getFileMaster($codeAlpha, $id_indikator_user, $id_bagian){
            $data=[];
            $file_code=$codeAlpha."-".$id_indikator_user;
            $get_master_file=Master_file_indikator::join('periode', 'periode.id', '=', 'master_file_indikator.periode')
                        ->select('master_file_indikator.file_name', 'master_file_indikator.id as id_master_file', 'periode.periode', 'periode.id as id_periode')
                        ->where('file_code', $file_code)
                        ->where('id_bagian', $id_bagian)->orderBy('no_urut', 'asc')->get();
            $jumlah=$get_master_file->count();
            if($jumlah > 0){
                $x=0;
                foreach($get_master_file as $list_file){
                    $edoc=$this->getEdocMaster(($list_file['id_master_file']));
                    $data[$x]['file_name']=$list_file['file_name'];
                    $data[$x]['id_file']=Crypt::encrypt($list_file['id_master_file']);
                    $data[$x]['edoc']=$edoc['edoc'];
                    $data[$x]['periode']=$list_file['periode'];
                    $data[$x]['periode_id']=$list_file['id_periode'];
                    $x++;
                }
            }
            return [
                'jumlah'=>$jumlah,
                'data'=>$data,
            ];
        }

        public function getEdocMaster($id_master){
            $data=[];
            $get_edoc=Edoc_indikator::where(function($where) use($id_master){
                                    $where->where('id_master', $id_master)
                                    ->whereRaw('max_fill_at <= date(now())');
                                })
                        ->orWhere(function($where) use($id_master){
                            $where->where('id_master', $id_master)
                            ->where('periode', 4);
                        })//kalau per tahun akan muncul terus
                        ->get();
            $jumlah=$get_edoc->count();
            if($jumlah > 0){
                $x=0;
                foreach($get_edoc as $list_edoc){
                    $data[$x]['edoc']=$list_edoc['edoc'];
                    $data[$x]['id']=Crypt::encrypt($list_edoc['id']);
                    $data[$x]['timeline']=$list_edoc['timeline'];
                    $x++;
                }
            }
            return [
                'edoc'=>$data,
                'jumlah'=>$jumlah,
            ];
        }

        public function updateEdocMaster($request){
            $update=false;
            try{
                $validate=$request->validate([
                    'master_file_id'=> ['required'],
                    'id_bagian_enc'=>['required'],
                    'tipe_indikator'=> ['required'],
                    'level' => ['required', 'digits:1'],
                    'nama_file' => ['required'],
                    'periode' => ['required', 'digits:1'],
                    'tahun' => ['required', 'digits:4'],
                ]);
                try{
                    $file_master_id=Crypt::decrypt($request->master_file_id);
                    $id_bagian=Crypt::decrypt($request->id_bagian_enc);
                    $get_data=Master_file_indikator::where('id', $file_master_id)
                                                    ->where('id_bagian', $id_bagian)
                                                    ->first();
                    $old_periode=(int)$get_data['periode'];
                    if(!is_null($get_data)){
                        $current_name=$get_data['file_name'];
                        if($current_name !== $request->name_file){
                            if($get_data['gd_id'] === ""){
                                // $msg="Google drive id belum di setting";
                                // $create_folder=$this->googleDriveService->createFolder($req)
                                $update_nama=false;
                            }else{
                                $update_nama=$this->googleDriveService->renameFolder($request->nama_file, $get_data['gd_id']);
                            }
                        }else{
                            $update_nama=true;
                        }
                        if($update_nama){
                            if($get_data['periode'] !== $request->periode){
                                //hapus edoc_indikator
                                $delete_data=Edoc_indikator::where('id_master', $file_master_id)->get();
                                $filled_file=0;
                                $deleted_file=0;
                                foreach($delete_data as $list_data){
                                    if(!is_null($list_data['edoc'])){
                                        $filled_file+=1;
                                        $delete_file=$this->googleDriveService->deleteFileDrive($list_data['edoc']);
                                        if($delete_file){
                                            $deleted_file+=1;
                                        }
                                    }
                                }

                                if($deleted_file === $filled_file){
                                    $delete_data=Edoc_indikator::where('id_master', $file_master_id)->delete();
                                }else{
                                    return [
                                        'status' => false,
                                        'msg' => 'File lama tidak dapat dihapus. Silahkan menghubungi tim IT'
                                    ];
                                }
                            }
                            $get_periode=Periode::where('id', $request->periode)->first();
                            if(!is_null($get_periode)){
                                $get_data->file_name=$request->nama_file;
                                $get_data->periode=$request->periode;
                                $update_data_file=$get_data->update();
                                if($update_data_file){
                                    if($old_periode === (int)$request->periode){
                                        $update=true;
                                        $msg="Berhasil mengubah nama file";
                                    }else{
                                        $timeline=$this->generateEdocTimeline($request->periode, $file_master_id, $request->tahun);
                                        $update=$timeline['status'];
                                        $msg=$timeline['msg'];
                                    }   
                                }
                            }else{
                                $msg="Data periode tidak valid";
                            }
                        }else{
                            $msg="Terjadi Kesalahan Sistem. Silahkan Hapus terlebih dahulu List File tersebut dan Simpan ulang";
                        }
                    }else{
                        $msg="Data tidak ditemukan";
                    }
                }catch(DecryptException $e){
                    $msg="Invald token";
                }
            }catch(ValidationException $e){
                $msg=$e->validator->errors()->first();
            }
            return [
                'status'=> $update,
                'msg' => $msg,
            ];
        }

        public function generateEdocTimeline($periode, $id_master, $tahun){
            $saved=false;
            $saved_data=0;
            $const_periode=['perbulan', 'per triwulan', 'per semester', 'per tahun'];
            $month=['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'Septmber', 'Oktober', 'November', 'Desember'];
            if((int)$periode === 1){
                for($x=0;$x<12;$x++){
                    $bulan=($x+1) < 10 ? '0'.($x+1) : ($x+1);
                    $edoc=new Edoc_indikator;
                    $edoc->id_master=$id_master;
                    $edoc->edoc=null;
                    $edoc->periode=$periode;
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
            }else if((int)$periode === 2){
                //per triwulan;
                for($x=0;$x<4;$x++){
                    $bulan=($x*3+3) < 10 ? '0'.($x*3+3) : ($x*3+3);
                    $edoc=new Edoc_indikator;
                    $edoc->id_master=$id_master;
                    $edoc->edoc=null;
                    $edoc->timeline=$month[$x*3]."-".$month[$x*3+3-1];
                    $edoc->max_fill_at=$tahun."-".$bulan."-01";
                    $edoc->periode=$periode;
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
            }else if((int)$periode === 3){
                for($x=0;$x<2;$x++){
                    $bulan=($x*6+5) < 10 ? '0'.($x*6+5) : ($x*6+5);
                    $edoc=new Edoc_indikator;
                    $edoc->id_master=$id_master;
                    $edoc->edoc=null;
                    $edoc->timeline=$month[$x*6]."-".$month[$x*6+5];
                    $edoc->max_fill_at=$tahun."-".$bulan."-01";
                    $edoc->periode=$periode;
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
            }else if((int)$periode === 4){
                $edoc=new Edoc_indikator;
                $edoc->id_master=$id_master;
                $edoc->edoc=null;
                $edoc->timeline="Januari - Desember";
                $edoc->max_fill_at=$tahun."-12-01";
                $edoc->periode=$periode;
                if($edoc->save()){
                    $saved=true;
                    $msg="All data saved";
                }else{
                    $msg="There is problem while saving data";
                }
            }else{
                $msg="Periode tidak dikenali";
            }
            return [
                'status' => $saved,
                'msg' => $msg,
            ];
        }

        public function deleteMaster($request){
            $deleted=false;
            try{
                $validate=$request->validate([
                    'id_bagian_enc'=>['required'],
                    'master_file_id'=>['required']
                ]);
                try{
                    $id_bagian=Crypt::decrypt($request->id_bagian_enc);
                    $id_file=Crypt::decrypt($request->master_file_id);
                    $get_data=Master_file_indikator::where('id', $id_file)
                                                ->where('id_bagian', $id_bagian)
                                                ->first();
                    if(!is_null($get_data)){
                        $get_edoc=Edoc_indikator::where('id_master', $id_file)->delete();
                        if($get_edoc){
                            if($get_data->delete()){
                                $msg="Berhasil menghapus data";
                                $deleted=true;
                            }else{
                                $msg="Terjadi kesalahan sistem saat menghapus data";
                            }
                        }else{
                            if($get_data->delete()){
                                $msg="Berhasil menghapus data. <b>Catatan: </b>: Indikator ini tidak memiliki edoc";
                                $deleted=true;
                            }else{
                                $msg="Terjadi kesalahan sistem saat menghapus data";
                            }
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
           return [
            'status'=>$deleted,
            'msg'=>$msg,
           ];
        }

        public function updateDataEdoc($request){
            $upload=false;
            try{
                $edoc_id=Crypt::decrypt($request->token);
                try{
                    $validate=$request->validate([
                        'token' => ['required'],
                        'file' => ['required', 'file', 'mimes:pdf']
                    ]);
                    $file=$request->file;
                    $type=$file->getMimeType();
                    $real_extention=$file->getClientOriginalExtension();
                    if($type === "application/pdf" && ($real_extention === "pdf" || $real_extention === "PDF")){
                        $get_data=Edoc_indikator::join('master_file_indikator as b', 'b.id', '=', 'edoc_indikator.id_master')
                                            ->where('edoc_indikator.id', $edoc_id)        
                                            ->select('b.file_code', 'edoc_indikator.timeline', 'b.gd_id', 'edoc_indikator.edoc')
                                            ->first();
                        if(!is_null(($get_data))){
                            $db=["Indikator_lvl1_user", "Indikator_lvl2_user", "Indikator_lvl3_user", "Indikator_lvl4_user", "Indikator_lvl5_user"];
                            $alpha_prefix=['A', 'B', 'C', 'D', 'E'];
                            $file_code=$get_data['file_code'];
                            $explode=explode("-", $file_code);
                            $alpha_code=$explode[0];
                            $db_id=$explode[1];
                            (int)$index_alpha=null;
                            for($x=0;$x<count($alpha_prefix);$x++){
                                if($alpha_prefix[$x] === $alpha_code){
                                    $index_alpha=$x;
                                    break;
                                }
                            }
                            if(!is_null($index_alpha)){
                                $get_indiaktor=app("App\\Models\\".$db[$x])::where("id", $db_id)->first();
                                $tahun=$get_indiaktor['tahun'];
                                $timeline=$get_data['timeline'];
    
                                if(!is_null($get_data['edoc'])){
                                    //remove old file
                                    $remove=$this->googleDriveService->deleteFileDrive($get_data['edoc']);
                                }else{
                                    $remove=true;
                                }
    
    
                                if($remove){
                                    $upload=$this->googleDriveService->uploadFile($tahun, $timeline, $file, $get_data['gd_id']);
                                    if($upload){
                                        $get_edoc=Edoc_indikator::where('id', $edoc_id)->first();
                                        if(!is_null($get_edoc)){
                                            $get_edoc->edoc=$upload;
                                            if($get_edoc->update()){
                                                $upload=true;
                                                $msg="Berhasil menyimpan data";
                                            }else{
                                                $msg="Data tidak dapat disimpan";
                                            }
                                        }else{
                                            $msg="Data edoc belum ada";
                                        }
                                    }else{
                                        $msg="Terjadi kesalahan sistem saat upload file ke Google Drive. Silahkan hubungi Tim Dev";
                                    }
                                }else{
                                    $msg="Dokumen lama tidak dapat dihapus. Silahkan menghuungi tim IT";
                                }
                            }else{
                                $msg="Kode dokument tidak valid";
                            }
                        }else{
                            $msg="Data tidak ditemukan";
                        }
                    }else{
                        $msg="Tipe File Harus PDF ".$type;
                    }
                }catch(ValidationException $e){
                    $msg=$e->validator->errors()->first();
                }
            }catch(DecryptException $e){
                $msg="Invalid token";
            }
            return [
                'status'=>$upload,
                'msg'=>$msg
            ];
        }

        public function deleteFileEdoc($id_edoc_enc){
            $status=false;
            try{
                $id_edoc=Crypt::decrypt($id_edoc_enc);
                $get_data=Edoc_indikator::where('id', $id_edoc)->first();
                if(!is_null($get_data)){
                    $delete_file=$this->googleDriveService->deleteFileDrive($get_data['edoc']);
                    if($delete_file){
                        $get_data->edoc=null;
                        if($get_data->update()){
                            $msg="Berhasil menghapus file";
                            $status=true;
                        }else{
                            $msg="Data already delete on drive, But on system still exists. Please report to IT";
                        }
                    }else{
                        $msg="There is problem while deleting you're file on Drive";
                    }
                }else{
                    $msg="Data tidak ditemukan";
                }
            }catch(DecryptException $e){
                $msg="Invalid token";
            }
            return [
                'status'=>$status,
                'msg'=>$msg,
            ];
        }

        public function generateChecklistByYear(){

        }
    }

?>