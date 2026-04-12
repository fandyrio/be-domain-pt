<?php
    namespace App\Services;

    use App\Models\Citizen;

    class citizenService {
        public function updateBagianCitizen($id_citizen, $id_bagian){
            $status=false;
            $msg="";
            $get_citizen=Citizen::where('id', $id_citizen)->first();
            if(!is_null($get_citizen)){
                $get_citizen->id_bagian=$id_bagian;
                if($get_citizen->update()){
                    $status=true;
                }else{
                    $msg="Terjadi error database";
                }
            }else{
                $msg="Data tidak ditemukan";
            }
            return [
                'status'=>$status,
                'msg' => $msg
            ];
        }
    }
?>