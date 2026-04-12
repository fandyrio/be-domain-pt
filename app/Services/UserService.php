<?php
    namespace App\Services;

    use App\Models\Citizen;
    use App\Models\Pj_bagian;
    use App\Models\User;
    use Illuminate\Support\Facades\Crypt;
    use Illuminate\Contracts\Encryption\DecryptException;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Http\Request;

    class UserService{
        public function isAdmin($citizen_id){
            $getCitizen=Citizen::join('bagian', function($join){
                                    $join->on('bagian.id', '=', 'citizen.id_bagian')
                                    ->where('bagian.bagian_code', 'bag-024');
                                })
                        ->where('citizen.id', $citizen_id)
                        ->first();
            if(!is_null($getCitizen)){
                return true;
            }
            return false;
        }

        public function isPj($citizen_id){
            $get_citizen=Pj_bagian::join('citizen', function($join) use($citizen_id){
                                        $join->on('citizen.id', '=', 'pj_bagian.id_citizen')
                                        ->where('citizen.id', $citizen_id);
                                    })
                        ->get();
            $jumlah=$get_citizen->count();
            if($jumlah > 0){
                return true;
            }
            return false;
        }

        public function isPjBagian($citizen_id, $id_bagian){
            $get_pj=Pj_bagian::where('id_citizen', $citizen_id)
                            ->where('id_bagian', $id_bagian)
                            ->first();
            if(is_null($get_pj)){
                return false;
            }
            return true;
        }

        public function generateUser($citizen_id_enc, $nip_enc){
            $status=false;
            $msg="";
            try{
                $citizen_id=Crypt::decrypt($citizen_id_enc);
                $nip=Crypt::decrypt($nip_enc);
                $getDataCitizen=Citizen::where('id', $citizen_id)
                                    ->where('nip', $nip)
                                    ->first();
                if(!is_null($getDataCitizen)){
                    $new_user=New User;
                    $new_user->citizen_id=$citizen_id;
                    $new_user->password=Hash::make($nip);
                    $new_user->p_token='';
                    $new_user->password_changed=false;
                    $new_user->last_login=null;
                    if($new_user->save()){
                        $status=true;
                    }else{
                        $msg="Terjadi kesalahan sistem saat menyimpan data";
                    }
                }else{
                    $msg="Data tidak ditemukan";
                }
            }catch(DecryptException $e){
                $msg="Invalid token";
            }

            return [
                'status'=>$status,
                'msg'=>$msg
            ];
        }

        public function updateUser($citizen_id_enc, $nip_enc){
            $update=false;
            $msg="";
            try{
                $citizen_id=Crypt::decrypt($citizen_id_enc);
                $nip=Crypt::decrypt($nip_enc);
                $get_data=Citizen::join('users', 'users.citizen_id', '=', 'citizen.id')
                            ->where('users.id', $citizen_id)
                            ->select('users.id as user_id')
                            ->first();
                if(!is_null($get_data)){
                    $update=User::where('id', $get_data['user_id'])->update(['password'=>Hash::make($nip)]);
                    if(!$update){
                        $msg="Terjadi kesalahan sistem saat menyimpan data";
                    }
                }else{
                    $msg="Data tidak ditemukan";
                }
            }catch(DecryptException $e){
                $msg="Invalid token";
            }
            return [
                'status'=>$update,
                'msg'=>$msg
            ];
        }
    }

?>