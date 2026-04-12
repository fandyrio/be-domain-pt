<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Client;
use Google\Service\Drive;
use App\Services\GoogleDriveService;
use App\Services\FolderService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class googleDriveController extends Controller
{
    //
    protected $googleDriveService;
    protected $folderService;
    
    public function __construct(GoogleDriveService $googleDriveService, FolderService $folderService){
        $this->googleDriveService=$googleDriveService;
        $this->folderService=$folderService;
    }

    public function listFiles()
    {
        // $client = new Client();
        // $client->setAuthConfig(storage_path('app/google/client_secret.json'));

        // $token = session('google_token');

        // if (!$token) {
        //     return response()->json(['error' => 'User is not authenticated'], 401);
        // }

        // $client->setAccessToken($token);

        // if ($client->isAccessTokenExpired()) {
        //     $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        //     session(['google_token' => $client->getAccessToken()]);
        // }

        // $driveService = new Drive($client);
        // $files = $driveService->files->listFiles();

        // return response()->json($files);
        $files = $this->googleDriveService->listFiles();
        return response()->json($files);
    }

    public function uploadFile(Request $request){
        $request->validate([
            'file'=>'required|file'
        ]);

        $fileId=$this->googleDriveService->uploadFile($request->file('file'));
        return response()->json(['file_id' => $fileId]);
    }
    
    public function listFolder($parent_id, $level){
        try{
            $parent_id=Crypt::decrypt($parent_id);
            $level=Crypt::decrypt($level);
            $child_folder=$this->googleDriveService->listFolderByParentId($parent_id);
            if((int)$level === 0){
                $parent_gd_id='true';
            }else{
                $parent_gd_id=$parent_id;
            }
            $save_list=$this->googleDriveService->saveFolderSubFolderAsIndikatorSubIndikator($child_folder, $level, $parent_gd_id, $parent_id);
            return $save_list;
            // return $parent_id;
        }catch(DecryptException $e){
            $msg="Invalid token";
            return response()->json(['status'=>false, 'msg'=>$msg]);
        }

    }

    public function updateFolderByGdId(Request $request){
        $status=false;
        try{
            $gd_id=Crypt::decrypt($request->target_parent);
            $level=Crypt::decrypt($request->target_level);
            $level=(int)$level;
            $rename_service=$this->googleDriveService->renameDriveName($gd_id, $request->nama_folder);
            if(!isset($rename_service['error'])){
                $data['new_name']=$rename_service['new_name'];
                $data['gd_id']=$gd_id;
                $data['level']=$level;
                $rename_folder=$this->folderService->renameFolder($data);
                $status=$rename_folder['status'];
                $msg=$rename_folder['msg'];
            }else{
                $msg="Error : ".$rename_service['error'];
            }
        }catch(DecryptException $e){
            $msg="Invalid token";
        }
        return response()->json(['status'=>$status, 'msg'=>$msg]);
    }


    public function createFolderTesting(){
        $this->googleDriveService->createFolder('test', '1Ka8fK0E0ltdHInBeiTlVcVn50dHoZrWP');
    }

    public function createNewFolder(Request $request){
        $created=false;
        $msg="";
        try{
            $level=(int)Crypt::decrypt($request->target_level);
            $folderName=$request->nama_folder;
            $parent_id=Crypt::decrypt($request->target_parent);
            try{
                $request->validate([
                    'nama_folder'=>['required'],
                ]);
                $folder_id=$this->googleDriveService->createFolder($folderName, $parent_id);
                $save=$this->googleDriveService->saveFolderAsIndikator($level, $folderName, Crypt::encrypt($folder_id), Crypt::encrypt($parent_id), false);
                $status_save=$save->getData();
                if($status_save->status){
                    $created=true;
                    $msg="Berhasil membuat folder dan menyimpan data folder";
                }else{
                    $msg="Terjadi kesalahan sistem saat menyimpan data folder 1 ".$status_save->msg;
                }
            }catch(ValidationException $e){
                $msg=$e->validator->errors()->first();
            }
        }catch(DecryptException $e){
            $msg="There is problem while creating folder. Please call tim dev";
        }
        return response()->json(['status'=>$created, 'msg'=>$msg]);
    }

    public function encrypt($id){
        return Crypt::encrypt($id);
    }

    public function decrypt($string){
        return Crypt::decrypt($string);
    }

    //tidak dipakai lagi
    public function deleteFolders(Request $request){
        $deleted=false;
        $valid=true;
        $msg="";
        try{
            $level=Crypt::decrypt($request->level);
            $gd_id=Crypt::decrypt($request->gd_id);
            $get_data=$this->googleDriveService->getDataByLevel($level, $gd_id);
            if(!is_null($get_data)){
                $delete_gd=$this->googleDriveService->deleteFolder($gd_id);
                if($delete_gd){
                    $deleted=$this->googleDriveService->deleteFolderAmpuh($get_data);
                    if($deleted){
                        $msg="Berhasil menghapus data";
                    }else{
                        $msg="Terjadi kesalahan saat menghapus data";
                    }
                }else{
                    $fetch_del_gd=$delete_gd->getData();
                    $msg=$fetch_del_gd->msg;
                }
            }else{
                $msg="Data tidak ditemukan";
            }

        }catch(DecryptException $e){
            $msg="Invalid token ".$e->getMessage();
        }
        return response()->json(['status'=>$deleted, 'msg'=>$msg]);
    }
}
