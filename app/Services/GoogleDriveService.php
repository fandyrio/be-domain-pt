<?php
    namespace App\Services;

    use Google\Client;
    use Google\Service\Drive;
    use Google\Service\Drive\DriveFile;
    use Illuminate\Support\Facades\Storage;
    use App\Models\Ampuh_indikator;
    use App\Models\Ampuh_sub_indikator_lvl1;
    use App\Models\Ampuh_sub_indikator_lvl2;
    use App\Models\Ampuh_sub_indikator_lvl3;
    use App\Models\Ampuh_sub_indikator_lvl4;
    use App\Models\Ampuh_sub_indikator_lvl5;
    use Illuminate\Support\Facades\Crypt;
    use Illuminate\Contracts\Encryption\DecryptException;
    use Illuminate\Support\Facades\DB;
    
    class GoogleDriveService{
        protected $client;
        protected $driveService;
        protected $status;
        protected $googleDriveIdService;
        public function __construct(googleDriveIdService $google_drive_id_serivce)
        {
            $this->client = new Client();
            $this->client->setAuthConfig(storage_path('app/google/google_service.json'));
            $this->client->setScopes([Drive::DRIVE_FILE]);
            $this->driveService = new Drive($this->client);
            $this->googleDriveIdService=$google_drive_id_serivce;

            $this->status="error";
        }

        public function listFiles()
        {
            $query = "mimeType != 'application/vnd.google-apps.folder'";
            $files = $this->driveService->files->listFiles(['q' => $query]);

            return $files->getFiles();
        }

        public function uploadFile($tahun, $timeline, $file, $folder_id)
        {
            // $folderId = env('GOOGLE_DRIVE_FOLDER_ID');

            $fileMetadata = new DriveFile([
                'name' => $tahun."-".$timeline." || ".$file->getClientOriginalName(),
                'parents' => [$folder_id]
            ]);

            $content = file_get_contents($file->path());

                $uploadedFile = $this->driveService->files->create($fileMetadata, [
                    'data' => $content,
                    'mimeType' => $file->getMimeType(),
                    'uploadType' => 'multipart',
                ]);

                return $uploadedFile->id;
        }

        public function listFolderByParentId($parentId){
            // $parentId = '17gYnbEDq9-L0KF1eNnHiWdaVqgUV31tR'; dev
            // $parentId="1KlWVPKTSbDZVqMnzqf4YOlDdYfsvBEF1"; prod
            // $parentId="1PABOME0FeBcDsDDtYOX7UE-V3kpdveXO?hl=IDs";
            $this->client->setScopes([Drive::DRIVE_READONLY]);
            $query = "'{$parentId}' in parents and mimeType='application/vnd.google-apps.folder'";
            $optParams = [
                'q' => $query,
                'fields' => 'files(id, name)',
                'supportsAllDrives' => true,
                'includeItemsFromAllDrives' => true
            ];

            $results = $this->driveService->files->listFiles($optParams);
            $folders = [];

            foreach ($results->getFiles() as $folder) {
                // Recursively get subfolders
                // $subfolders = $this->listFolder($folder->getId());
                $folders[] = [
                    'id' => $folder->getId(),
                    'name' => $folder->getName(),
                    // 'subfolders' => $subfolders
                ];
            }

            return $folders;
        }


        public function getFolderProperti($gd_id){
            try{
                $this->client->setScopes([Drive::DRIVE_READONLY]);
                $folder=$this->driveService->files->get($gd_id, [
                    'fields' => 'id, name',
                ]);
                return[
                    'status'=>true,
                    'msg'=>'Found',
                    'id'=>$folder->getId(),
                    'name'=>$folder->getName(),
                ];
            }catch(\Exception $e){
                return ['status'=>false, 'msg'=>'Folder not Found. '.$e->getMessage()];
            }
        }

        public function renameDriveName($gd_id, $new_name){
            try{
                $this->client->setScopes([DRIVE::DRIVE]);
                $metaData=new DriveFile([
                    'name' => $new_name
                ]);
                $update=$this->driveService->files->update($gd_id, $metaData, [
                    'fields' => 'id, name'
                ]);

                return [
                    'id' =>$update->getId(),
                    'new_name' => $update->getName(),
                ];
            }catch(\Exception $e){
                return [
                    'error'=>$e->getMessage(),
                ];
            }
        }

        public function saveFolderSubFolderAsIndikatorSubIndikator($child_folders, $level, $parent_gd_id, $parent_id){
            $jumlah_folder=count($child_folders);
            $saved=0;
            $save=false;
            $new=0;
            $update=0;
            $msg="";
            $status=false;
            // var_dump($folders);
            for($x=0;$x<$jumlah_folder;$x++){
                $indikator_name=$child_folders[$x]['name'];
                $indikator_gd_id=$child_folders[$x]['id'];
                if((int)$level === 0 && $parent_gd_id === 'true'){
                    $check=Ampuh_indikator::where('gd_id', $indikator_gd_id)->first();
                    
                    $get_parent=$this->googleDriveIdService->getGdIdByGdId($parent_id);
                    if(is_null($get_parent)){
                        return response()->json(['status'=>false, 'msg'=>"Data Parent tidak ditemukan ".$parent_id]);
                    }
                    $parent_id_db=$get_parent['id'];
                    if(is_null($check)){
                        $save=$this->saveNewIndikator($indikator_gd_id, $indikator_name, $parent_id_db);
                        $new+=1;
                        // $indikator_id=$save;
                    }else{
                        $save=$this->updateIndikator($check, $indikator_name, $parent_id_db);
                        $update+=1;
                        // $indikator_id=$check['id'];
                    }
                }else if((int)$level === 1){
                    $check=Ampuh_indikator::where('gd_id', $parent_gd_id)->first();
                    if(!is_null($check)){
                        $check_sub_lvl1=Ampuh_sub_indikator_lvl1::where('gd_id', $indikator_gd_id)->first();
                        if(is_null($check_sub_lvl1)){
                            $save=$this->saveSubLvl1($indikator_gd_id, $indikator_name, $check['id']);
                            $new+=1;
                        }else{
                            $save=$this->updateSubLvl1($check_sub_lvl1, $indikator_name);
                            $update+=1;
                        }
                    }else{
                        $msg="Parent id tidak dikenali";
                        break;
                    }
                }else if((int)$level === 2){
                    $check=Ampuh_sub_indikator_lvl1::where('gd_id', $parent_gd_id)->first();
                    if(!is_null($check)){
                        $check_sub_lvl2=Ampuh_sub_indikator_lvl2::where('gd_id', $indikator_gd_id)->first();
                        if(is_null($check_sub_lvl2)){
                            $save=$this->saveSubLvl2($indikator_gd_id, $indikator_name, $check['id']);
                            $new+=1;
                        }else{
                            $save=$this->updateSubLvl2($check_sub_lvl2, $indikator_name, $check['id']);
                            $update+=1;
                        }
                    }else{
                        $msg="Parent id tidak dikenali";
                        break;
                    }
                }else if((int)$level === 3){
                    $check=Ampuh_sub_indikator_lvl2::where('gd_id', $parent_gd_id)->first();
                    if(!is_null($check)){
                        $check_sub_lvl3=Ampuh_sub_indikator_lvl3::where('gd_id', $indikator_gd_id)->first();
                        if(is_null($check_sub_lvl3)){
                            $save=$this->saveSubLvl3($indikator_gd_id, $indikator_name, $check['id']);
                        }else{
                            $save=$this->updateSubLvl3($check_sub_lvl3, $indikator_name, $check['id']);
                        }
                    }else{
                        $msg="Parent tidak dikenali";
                        break;
                    }
                }else if((int)$level === 4){
                    $check=Ampuh_sub_indikator_lvl3::where('gd_id', $parent_gd_id)->first();
                    if(!is_null($check)){
                        $check_sub_lvl4=Ampuh_sub_indikator_lvl4::where('gd_id', $indikator_gd_id)->first();
                        if(is_null($check_sub_lvl4)){
                            $save=$this->saveSubLvl4($indikator_gd_id, $indikator_name, $check['id']);
                        }else{
                            $save=$this->updateSubLvl4($check_sub_lvl4, $indikator_name, $check['id']);
                        }
                    }else{
                        $msg="Parent tidak dikenal";
                        break;
                    }
                }else if((int)$level === 5){
                    $check=Ampuh_sub_indikator_lvl4::where('gd_id', $parent_gd_id)->first();
                    if(!is_null($check)){
                        $check_sub_lvl5=Ampuh_sub_indikator_lvl5::where('gd_id', $indikator_gd_id)->first();
                        if(is_null($check_sub_lvl5)){
                            $save=$this->saveSubLvl5($indikator_gd_id, $indikator_name, $check['id']);
                        }else{
                            $save=$this->updateSubLvl5($check_sub_lvl5, $indikator_name, $check['id']);
                        }
                    }else{
                        $msg="Parent tidak dikenali";
                        break;
                    }
                }else{
                    $msg="Level tidak dikenali atau belum diregister ";
                }

                if($save){
                    $saved+=1;
                }
            }
            if($saved === $jumlah_folder){
                $msg="Berhasil menyimpan data ".$saved." Data";
                $status=true;
                if((int)$level === 1 && $jumlah_folder > 0){
                    $check->level_sub_indikator=1;
                    if(!$check->update()){
                        $msg.="\n Opps, table ampuh_indikator tidak bisa diupdate.\n";
                    }
                }elseif((int)$level === 2 && $jumlah_folder > 0){
                    $check->level_sub_indikator=1;
                    if(!$check->update()){
                        $msg.="\n Opps, table ampuh_sub_indikator_lvl1 tidak bisa diupdate.\n";
                    }
                }
            }else{
                $msg.="\nTerjadi kesalahan sistem saat menyimpan data. ".$saved.":".$jumlah_folder;
            }
            // $msg.="\n . Baru :".$new." Update : ".$update;
            return response()->json(['status'=>$status, 'msg'=>$msg]);
        }

        public function saveNewIndikator($gd_id, $indikator_name, $parent_id){
            $new_indikator=new Ampuh_indikator;
            $new_indikator->gd_id=$gd_id;
            $new_indikator->indikator_name=$indikator_name;
            $new_indikator->level_sub_indikator=0;
            $new_indikator->parent_id=$parent_id;
            $save=$new_indikator->save();
            return $save;
        }

        public function updateIndikator($check, $indikator_name, $parent_id){
            $check->indikator_name=$indikator_name;
            $check->parent_id=$parent_id;
            $save=$check->update();
            return $save;
        }

        public function saveSubLvl1($gd_id, $sub_indikator_name, $indikator_id){
            $new_sub_lvl1=new Ampuh_sub_indikator_lvl1;
            $new_sub_lvl1->indikator_id=$indikator_id;
            $new_sub_lvl1->gd_id=$gd_id;
            $new_sub_lvl1->sub_indikator_name=$sub_indikator_name;
            $new_sub_lvl1->level_sub_indikator=0;
            $save=$new_sub_lvl1->save();
            // return $new_sub_lvl1->id;
            return $save;
        }

        public function updateSubLvl1($check, $sub_indikator_name_lvl1){
            $check->sub_indikator_name=$sub_indikator_name_lvl1;
            $save=$check->update();
            return $save;
        }

        public function saveSubLvl2($gd_id, $sub_indikator_name, $sub_indikator_id_lvl1){
            $new_sub_lvl2=new Ampuh_sub_indikator_lvl2;
            $new_sub_lvl2->sub_indikator_lvl1_id=$sub_indikator_id_lvl1;
            $new_sub_lvl2->gd_id=$gd_id;
            $new_sub_lvl2->sub_indikator_name=$sub_indikator_name;
            $new_sub_lvl2->level_sub_indikator=0;
            $save=$new_sub_lvl2->save();
            // return $new_sub_lvl2->id;
            return $save;
        }

        public function updateSubLvl2($check, $sub_indikator_name_lvl2, $lvl1_id){
            $check->sub_indikator_name=$sub_indikator_name_lvl2;
            $check->sub_indikator_lvl1_id=$lvl1_id;
            $save=$check->update();
            return $save;
        }

        public function saveSubLvl3($gd_id, $sub_indikator_name, $parent_id){
            $new_sub_lvl3=new Ampuh_sub_indikator_lvl3;
            $new_sub_lvl3->parent_id=$parent_id;
            $new_sub_lvl3->gd_id=$gd_id;
            $new_sub_lvl3->sub_indikator_name=$sub_indikator_name;
            $new_sub_lvl3->level_sub_indikator=0;
            $save=$new_sub_lvl3->save();
            // return $new_sub_lvl3->id;
            return $save;
        }

        public function updateSubLvl3($check, $indikator_name, $parent_id){
            $check->sub_indikator_name=$indikator_name;
            $check->parent_id=$parent_id;
            $save=$check->update();
            return $save;
        }

        public function saveSubLvl4($gd_id, $sub_indikator_name, $parent_id){
            $new_sub_lvl4=new Ampuh_sub_indikator_lvl4;
            $new_sub_lvl4->parent_id=$parent_id;
            $new_sub_lvl4->gd_id=$gd_id;
            $new_sub_lvl4->sub_indikator_name=$sub_indikator_name;
            $new_sub_lvl4->level_sub_indiaktor=0;
            $save=$new_sub_lvl4->save();
            // return $new_sub_lvl4->id;
            return $save;
        }

        public function updateSubLvl4($check, $indikator_name, $parent_id){
            $check->sub_indikator_name=$indikator_name;
            $check->parent_id=$parent_id;
            $save=$check->update();
            return $save;
        }

        public function saveSubLvl5($gd_id, $sub_indikator_name, $parent_id){
            $new_sub_lvl5=new Ampuh_sub_indikator_lvl4;
            $new_sub_lvl5->parent_id=$parent_id;
            $new_sub_lvl5->gd_id=$gd_id;
            $new_sub_lvl5->sub_indikator_name=$sub_indikator_name;
            $new_sub_lvl5->level_sub_indiaktor=0;
            $save=$new_sub_lvl5->save();
            // return $new_sub_lvl4->id;
            return $save;
        }

        public function updateSubLvl5($check, $indikator_name, $parent_id){
            $check->sub_indikator_name=$indikator_name;
            $check->parent_id=$parent_id;
            $save=$check->update();
            return $save;
        }

        public function createFolder($folderName, $parent_id){
            $folderMetaData=new DriveFile([
                'name'=>$folderName,
                'mimeType'=> 'application/vnd.google-apps.folder',
            ]);

            if($parent_id){
                $folderMetaData->setParents([$parent_id]);
            }
            $folder=$this->driveService->files->create($folderMetaData, [
                'fields'=>'id',
            ]);
            return $folder->id;
        }

        public function renameFolder($new_name, $gd_id){
            $folder_data=new DriveFile([
                'name'=>$new_name,
                'mimeType'=>'application/vnd.google-apps.folder',
            ]);
            $update_name=$this->driveService->files->update($gd_id, $folder_data);
            return $update_name;
            
        }

        public function deleteFolder($folder_id){
            $folder=$this->driveService->files->get($folder_id, ['fields'=> 'mimeType']);
            if($folder->getMimeType() === "application/vnd.google-apps.folder"){
                $this->driveService->files->delete($folder_id);
                return true;
            }else{
                return response()->json(['status'=>false, 'msg'=>'Data yang anda berikan bukan folder']);
            }
        }

        public function deleteFileDrive($file_id){
            $deleted=$this->driveService->files->delete($file_id);
            if($deleted){
                return true;
            }
            return false;
        }

        public function saveFolderAsIndikator($level, $folderName, $gd_id, $parent_id, $is_folder_bagian){
            $save=false;
            $id=null;
            if($level < 6){
                try{
                    DB::beginTransaction();
                        if($level === 0){
                            $config_value=Crypt::decrypt($parent_id);
                            $get_data=$this->googleDriveIdService->getGdIdByGdId($config_value);
                            $parent_id=$get_data['id'];
                            $new_indikator=new Ampuh_indikator();
                            $new_indikator->parent_id=$parent_id;
                            $new_indikator->indikator_name=$folderName;
                        }else if($level === 1){
                            $get_parent=Ampuh_indikator::where('gd_id', Crypt::decrypt($parent_id))->first();
                            $new_indikator=new Ampuh_sub_indikator_lvl1;
                            $new_indikator->indikator_id=$get_parent['id'];
                            $new_indikator->sub_indikator_name=$folderName;
                        }else if($level === 2){
                            $get_parent=Ampuh_sub_indikator_lvl1::where('gd_id', Crypt::decrypt($parent_id))->first();
                            $new_indikator=new Ampuh_sub_indikator_lvl2;
                            $new_indikator->sub_indikator_lvl1_id=$get_parent['id'];
                            $new_indikator->sub_indikator_name=$folderName;
                        }else if($level === 3){
                            $get_parent=Ampuh_sub_indikator_lvl2::where('gd_id', Crypt::decrypt($parent_id))->first();
                            $new_indikator=new Ampuh_sub_indikator_lvl3;
                            $new_indikator->parent_id=$get_parent['id'];
                            $new_indikator->sub_indikator_name=$folderName;
                        }else if($level === 4){
                            $get_parent=Ampuh_sub_indikator_lvl3::where('gd_id', Crypt::decrypt($parent_id))->first();
                            $new_indikator=new Ampuh_sub_indikator_lvl4;
                            $new_indikator->parent_id=$get_parent['id'];
                            $new_indikator->sub_indikator_name=$folderName;
                        }else if($level === 5){
                            $get_parent=Ampuh_sub_indikator_lvl4::where('gd_id', Crypt::decrypt($parent_id))->first();
                            $new_indikator=new Ampuh_sub_indikator_lvl5;
                            $new_indikator->parent_id=$get_parent['id'];
                            $new_indikator->sub_indikator_name=$folderName;
                        }
                        $new_indikator->level_sub_indikator=0;
                        $new_indikator->gd_id=Crypt::decrypt($gd_id);
                        if($level > 0){
                            $new_indikator->is_folder_bagian=$is_folder_bagian;
                        }
                        $save=$new_indikator->save();
                        $id=$new_indikator->id;
                        if($level > 0){
                            $get_parent->level_sub_indikator=1;
                            $get_parent->update();
                        }
                    DB::commit();
                    $msg="Berhasil menyimpan data";
                    $save=true;
                }catch(\Exception $e){
                    DB::rollback();
                    $msg="Terjadi kesalahan saat menyimpan data folder ".$e->getMessage();
                }
            }else{
                $msg="Table untuk indikator level ".$level." belum tersedia. Silahkan hubungi tim dev untuk menindaklanjuti";
            }
            return response()->json(['status'=>$save, 'msg'=>$msg, 'id'=>$id]);
        }


        //penggunaan : softDelete
        public function getDataByLevel($level, $gd_id){
            if((int)$level === 0){
                return Ampuh_indikator::where('gd_id', $gd_id)->first();
            }else if((int)$level === 1){
                return Ampuh_sub_indikator_lvl1::where('gd_id', $gd_id)->first();
            }else if((int)$level === 2){
                return Ampuh_sub_indikator_lvl2::where('gd_id', $gd_id)->first();
            }else if((int)$level === 3){
                return Ampuh_sub_indikator_lvl3::where('gd_id', $gd_id)->first();
            }else if((int)$level === 4){
                return Ampuh_sub_indikator_lvl4::where('gd_id', $gd_id)->first();
            }else{
               return null;
            }
            //return response()->json(['status'=>$valid, 'msg'=>$msg, 'data'=>$get_data]);
        }
        
        public function deleteFolderAmpuh($data){
            if($data->delete()){
                return true;
            }
            return false;
        }

        
    }

?>