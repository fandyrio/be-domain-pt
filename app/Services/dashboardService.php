<?php
    namespace App\Services;

use App\Models\Bagian;
use App\Models\Indikator_lvl2_user;
use App\Models\Master_file_indikator;
use Illuminate\Support\Facades\Crypt;

    class dashboardService{
        public function getDataDashboard($tahun){
            $get_bagian = Bagian::where("active", true)->get();
            $bagian = [];
            $data_perbagian = [];
            $edoc_kosong_bagian = [];
            $item_idle = [];
            foreach($get_bagian as $list){
                $bagian[] = [
                    'id'=>$list['id'],
                    'bagian'=>$list['bagian']
                ];
            }
            $jumlah_bagian = count($bagian);
            $jumlah_perbagian = Bagian::leftJoin("indikator_lvl2_user as b", 'b.id_bagian', '=', 'bagian.id')
                                    ->select("bagian.bagian")
                                    ->selectRaw("count(b.id) as jumlah")
                                    ->where("bagian.active", true)
                                    ->where('b.tahun', $tahun)
                                    ->groupBy("bagian.bagian")
                                    ->get();
            foreach($jumlah_perbagian as $list){
                $data_perbagian[] = [
                    'bagian'=>$list['bagian'],
                    'token'=>Crypt::encrypt($list['bagian']),
                    'jumlah'=>$list['jumlah']
                ];
            }
            if($jumlah_bagian > 0){
                //select file_name, timeline from master_file_indikator a left join edoc_indikator b on b.id_master=a.id where id_bagian = 33 and edoc is null;
                // $get_edoc_kosong = Master_file_indikator::leftJoin('edoc_indikator as b', 'b.id_master', '=', 'master_file_indikator.id')
                //                             ->whereRaw('edoc is null')
                //                             ->select("file_name, timeline")
                //                             ->orderBy('id_bagian', 'asc')
                //                             ->get();
                //select c.bagian, count(a.file_name) from master_file_indikator a left join edoc_indikator b on b.id_master=a.id join bagian c on c.id = a.id_bagian where edoc is null group by a.id_bagian;
                $get_edoc_kosong = Master_file_indikator::leftJoin('edoc_indikator as b', 'b.id_master', '=', 'master_file_indikator.id')
                                                ->join('bagian as c', 'c.id', '=', 'master_file_indikator.id_bagian')
                                                ->whereRaw('edoc is null')
                                                ->whereRaw('year(b.max_fill_at) = '.$tahun)
                                                ->whereRaw('b.max_fill_at <= date(now())')
                                                ->select('c.bagian', 'c.id')
                                                ->selectRaw("count(master_file_indikator.file_name) as jumlah")
                                                ->groupBy('c.bagian', 'c.id')
                                                ->get();
                foreach($get_edoc_kosong as $list){
                    $edoc_kosong_bagian[] = [
                        'bagian'=>$list['bagian'],
                        'token'=>Crypt::encrypt($list['id']),
                        'jumlah'=>$list['jumlah']
                    ];
                }

                //inidkator belum disetting
            // select f.bagian, count(c.sub_indikator_name)
            //    from indikator_lvl2_user a 
            //     join ampuh_sub_indikator_lvl2 c on c.id = a.id_indikator_lvl2
            //     JOIN ampuh_sub_indikator_lvl1 e on e.id = c.sub_indikator_lvl1_id
            //     left join master_file_indikator d on d.file_code=CONCAT('B-', a.id)
            //     join bagian f on f.id = a.id_bagian
            //     where d.id is null group by f.bagian;
                $get_edoc_idle = Indikator_lvl2_user::join('ampuh_sub_indikator_lvl2 as b', 'b.id', '=', 'indikator_lvl2_user.id_indikator_lvl2')
                                                    ->join('ampuh_sub_indikator_lvl1 as c', 'c.id', '=', 'b.sub_indikator_lvl1_id')
                                                    ->leftJoin('master_file_indikator as d', function($join){
                                                        $join->on('d.id_bagian', '=', 'indikator_lvl2_user.id_bagian')
                                                                ->whereRaw("d.file_code = CONCAT('B-', indikator_lvl2_user.id)");
                                                    })
                                                    ->join('bagian as e', 'e.id', '=', 'indikator_lvl2_user.id_bagian')
                                                    ->whereRaw('d.id is null')
                                                    ->where("indikator_lvl2_user.tahun", $tahun)
                                                    ->select("e.bagian", 'e.id')
                                                    ->selectRaw('count(c.sub_indikator_name) as jumlah')
                                                    ->groupBy('e.bagian', 'e.id')
                                                    ->get();
                foreach($get_edoc_idle as $list){
                    $item_idle[] = [
                        'bagian'=>$list['bagian'],
                        'token'=>Crypt::encrypt($list['id']),
                        'jumlah'=>$list['jumlah']
                    ];
                }
            }
            
            return ['jumlah_perbagian'=>$data_perbagian, 'edoc_kosong_perbagian'=>$edoc_kosong_bagian, 'item_idle'=>$item_idle];
        }

        public function getListEdocBelumDiisi($id_bagian, $tahun){
            //select file_name, timeline from master_file_indikator a left join edoc_indikator b on b.id_master=a.id where id_bagian = 33 and edoc is null and max_fill_at <= date(now());
            $get_data = Master_file_indikator::leftJoin("edoc_indikator as b", "b.id_master", "=", "master_file_indikator.id")
                                    ->where('id_bagian', $id_bagian)
                                    ->whereRaw('max_fill_at <= date(now())')
                                    ->whereRaw('year(max_fill_at) = '.$tahun)
                                    ->whereRaw('edoc is null')
                                    ->select("file_name as name", "timeline")
                                    ->get();
            $data = [];
            $jumlah = $get_data->count();
            foreach($get_data as $list){
                $data[] = [
                    'filename'=>$list['name'],
                    'timeline'=>$list['timeline']
                ];
            }
            
            return ['jumlah'=>$jumlah, 'data'=>$data];
        }

        public function getListEdocBelumPeriode($id_bagian, $tahun){
            $get_edoc_idle = Indikator_lvl2_user::join('ampuh_sub_indikator_lvl2 as b', 'b.id', '=', 'indikator_lvl2_user.id_indikator_lvl2')
                                                    ->join('ampuh_sub_indikator_lvl1 as c', 'c.id', '=', 'b.sub_indikator_lvl1_id')
                                                    ->leftJoin('master_file_indikator as d', function($join){
                                                        $join->on('d.id_bagian', '=', 'indikator_lvl2_user.id_bagian')
                                                                ->whereRaw("d.file_code = CONCAT('B-', indikator_lvl2_user.id)");
                                                    })
                                                    ->join('bagian as e', 'e.id', '=', 'indikator_lvl2_user.id_bagian')
                                                    ->whereRaw('d.id is null')
                                                    ->where("indikator_lvl2_user.tahun", $tahun)
                                                    ->where("indikator_lvl2_user.id_bagian", $id_bagian)
                                                    ->select("b.sub_indikator_name as name", "c.sub_indikator_name as name_root", 'tahun as timeline')
                                                    ->get();
            $data = [];
            $jumlah = $get_edoc_idle->count();
            foreach($get_edoc_idle as $list){
                $data[] = [
                    'filename'=>$list['name_root'],
                    'timeline'=>$list['timeline']
                ];
            }
            return ['jumlah'=>$jumlah, 'data'=>$data];
        }
    }


?>