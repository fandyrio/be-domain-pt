<?php
    namespace App\Services;
    use App\Models\Bagian;
    use App\Models\Pj_bagian;
use Illuminate\Support\Facades\Crypt;
    use Illuminate\Contracts\Encryption\DecryptException;

    class bagianService{
        public function getBagianByCutiId($id_bagian_cuti){
            $get_bagian=Bagian::where('cuti_id', $id_bagian_cuti)->first();
            if(!is_null($get_bagian)){
                return [
                    'id_bagian'=>$get_bagian['id'],
                    'bagian'=>$get_bagian['bagian'],
                ];
            }else{
                return [
                    'id_bagian'=>null,
                    'bagian'=>'',
                ];
            }
        }

        public function getBagianById($id_bagian){
            $get_bagian=Bagian::where('id', $id_bagian)->first();
            if(!is_null($get_bagian)){
                return [
                    'id_bagian_enc'=>Crypt::encrypt($get_bagian['id']),
                    'bagian'=>$get_bagian['bagian'],
                ];
            }
            return [
                'id_bagian_enc'=>null,
                'bagian'=>null,
            ];
            
        }

        public function getPJBagian(){
            $data=[];
            $x=0;
            $get_data=Pj_bagian::join('citizen', 'citizen.id', '=', 'pj_bagian.id_citizen')
                        ->join('bagian', 'bagian.id', '=', 'pj_bagian.id_bagian')
                        ->select('citizen.nama', 'citizen.no_hp', 'pj_bagian.id_bagian', 'bagian.bagian')
                        ->where('pj_bagian.active', true)->get();
            $total=$get_data->count();
            if($total > 0){
                foreach($get_data as $list_data){
                    $data[$x]['id_bagian']=$list_data['id_bagian'];
                    $data[$x]['nama']=$list_data['nama'];
                    $data[$x]['no_hp']=$list_data['no_hp'];
                    $data[$x]['bagian']=$list_data['bagian'];
                    $x++;
                }
            }

            return [
                'data'=>$data,
                'total'=>$total
            ];
        }

    }

?>