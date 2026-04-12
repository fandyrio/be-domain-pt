<?php

    namespace App\Services;

use App\Models\Web_config;

    class googleDriveIdService{
        public function getListGDIDConfig(){
            return Web_config::where("config_name", "main_gd_id")
                    ->where("active", true)        
                    ->get();
        }

        public function getGdIdById($gd_id){
            return Web_config::where("id", $gd_id)
                            ->where("config_name", "main_gd_id")
                            ->first();
        }

        public function getGdIdByGdId($gd_id){
            return Web_config::where("config_value", $gd_id)->first();
        }

        public function updateGdId($id, $nama_gd, $gd_id){
            $status=false;
            $get_data=$this->getGdIdById($id);
            if(!is_null($get_data)){
                //tidak boleh ada id yang sama lebih dari 1, jadi kalau ditemukan google drive id nya sama, maka tidak diupdate
                $check_gd_id=$this->getGdIdByGdId($gd_id);
                if(is_null($check_gd_id)){
                    $get_data->config_value=$gd_id;
                }
                $get_data->config_initial=$nama_gd;
                if($get_data->update()){
                    $status=true;
                    $msg="Berhasil mengubah data";
                }else{
                    $msg="Terjadi kesalahan sistem saat mengubah data";
                }
            }else{
                $msg="Tidak dapat mengubah data";
            }
            return ['status'=>$status, 'msg'=>$msg];
        }

        public function saveNewGdId($nama_gd, $gd_id){
            $status=false;
            $check_gd_id=$this->getGdIdByGdId($gd_id);
            if(is_null($check_gd_id)){
                $new_gd=new Web_config;
                $new_gd->config_name="main_gd_id";
                $new_gd->config_initial=$nama_gd;
                $new_gd->config_value=$gd_id;
                $new_gd->active=true;
                if($new_gd->save()){
                    $status=true;
                    $msg="Berhasil menyimpan Google Drive ID Baru";
                }else{
                    $msg="Terjadi kesalahan sistem";
                }
            }else{
                $msg="Google drive ID telah didaftarkan. ";
            }

            return ['status'=>$status, 'msg'=>$msg];
        }

        public function deactivateGdId($gd_id){
            $status=false;
            $get_gd_id=$this->getGdIdById($gd_id);
            if(!is_null($get_gd_id)){
                $get_gd_id->active=false;
                if($get_gd_id->update()){
                    $status=true;
                    $msg="Berhasil menghapus data";
                }else{
                    $msg="Terjadi kesalahan sistem saat menghapus data";
                }
            }else{
                $msg="Data tidak dapat diproses";
            }

            return ['status'=>$status, 'msg'=>$msg];
        }
    }

?>