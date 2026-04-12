<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:api')->get('/users', function (Request $request) {
    return $request->user();
});
Route::get('test-folder', 'googleDriveController@createFolderTesting');
Route::get('google-drive/list', 'googleDriveController@listFiles')->name('drive-files')->middleware('auth:api');
Route::post('google-drive/upload', 'googleDriveController@uploadFile')->name('google-drive-upload')->middleware('auth:api');
//Mengambil folder berdasarkan folder id dan level
Route::get('google-drive/list-folder/{folder_id}/{level}', 'googleDriveController@  ')->name('list-folder')->middleware('auth:api');
Route::post('google-drive/create-new-folder', 'googleDriveController@createNewFolder')->name('create-folder')->middleware('auth:api');
Route::post('google-drive/update-folder', 'googleDriveController@updateFolderByGdId')->name('update-folder')->middleware(['auth:api', 'isAdmin']);
Route::get("get-list-gdid", 'api\googleDriveIdController@getListGDID')->name("get-list-gdid")->middleware(['auth:api', 'isAdmin']);
Route::get("get-gd-id/{id}", 'api\googleDriveIdController@getGdIdById')->name('get-gd-id')->middleware(['auth:api', 'isAdmin']);
Route::post("update-gd-id", "api\googleDriveIdController@updateGoogleDriveId")->name("update-gd-id")->middleware(['auth:api', 'isAdmin']);
Route::post('save-gd-id', "api\googleDriveIdController@saveGdId")->name("save-gd-id")->middleware(['auth:api', 'isAdmin']);
Route::post("remove-gd-id", "api\googleDriveIdController@deactivateGdId")->name('remove-gd-id')->middleware(['auth:api', 'isAdmin']);
//tidak dipakai lagi, sudah disatukan dengan endpoint list-folder
// Route::post('google-drive/update-folder-properties', 'googleDriveController@updateFolderByGdId')->name('update-folder-properties')->middleware('auth:api');
//================================================================================================================================================================


Route::post('login', 'loginController@login')->name('login-api');
Route::post('logout', 'loginController@logout')->name('logout-api')->middleware('auth:api');
Route::post('refresh', 'loginController@refresh')->name('refresh');// untuk sementara tidak pakai middleware
Route::get('user', 'userController@userDetil')->name('user-detil')->middleware('auth:api');
Route::get('enc/{str}', 'googleDriveController@encrypt');
Route::get('dec/{str}', 'googleDriveController@decrypt');
Route::get('get-main-folder-ampuh/{level}/{parent_id}', 'api\folderSubFolderController@getMainFolderAmpuh')->name('get-main-folder-ampuh')->middleware('auth:api');//ga d pake lg
Route::get('get-main-gdid', 'api\folderSubfolderController@getMainGdId')->name('get-main-gd-id')->middleware('auth:api');
Route::post('get-mapping-folder-ampuh', 'api\folderSubfolderController@getAllFolderSubfolder')->name('get-mapping-folder-ampuh')->middleware('auth:api');
//tidak dipakai, rencana untuk delete langsung gd
Route::post('google-drive/delete-folder', 'googleDriveController@deleteFolder')->name('delete-folder');
//=================================================================================================================
Route::post('delete-folder', 'api\folderSubfolderController@softDeleteFolder')->name('delete-folder-by-gdid-level')->middleware('auth:api');


//BAGIAN
Route::post('sync-bagian', 'api\bagianController@syncBagian')->name('sync-bagian')->middleware('auth:api');
Route::get('get-all-bagian', 'api\bagianController@getAllBagian')->name('get-all-bagian')->middleware('auth:api');
Route::post('update-bagian', 'api\bagianController@updateBagian')->name('update-bagian')->middleware('auth:api');
Route::get('get-bagian', 'api\bagianController@getActiveBagian')->name('get-bagian')->middleware('auth:api');
Route::get('get-management',  'api\bagianController@getActiveManagement')->name('get-management')->middleware('auth:api');
Route::post('get-bagian-alias', 'api\bagianController@getBagianByAlias')->name('get-bagian-alias')->middleware('auth:api');
Route::post('delete-bagian', 'api\bagianController@deleteBagian')->name('delete-bagian')->middleware('auth:api');
Route::post('update-bagian-manual', 'api\bagianController@updateBagianManual')->name('update-bagian-manual')->middleware('auth:api');
Route::post('save-bagian-manual', 'api\bagianController@saveBagianManual')->name('save-bagian-manual')->middleware('auth:api');


//Jabatan
Route::post('sync-jabatan', 'api\jabatanController@syncJabatan')->name('sync-jabatan')->middleware('auth:api');
Route::get('get-jabatan', 'api\jabatanController@getAllJabatan')->name('get-jabatan')->middleware('auth:api');
Route::post('update-status-jabatan', 'api\jabatanController@updateStatusJabatan')->name('update-status-jabatan')->middleware('auth:api');

//citizen
Route::get('sync-citizen', 'api\citizenController@syncCitizen')->name('sync-citizen')->middleware('auth:api');
Route::get('data-citizen', 'api\citizenController@getDataCitizen')->name('data-citizen')->middleware('auth:api');
Route::post('list-citizen-by-bagianid', 'api\citizenController@getAllCitizenForPj')->name('list-citizen-by-bagian')->middleware('auth:api');//api ini diganti jadi seluruh citizen
Route::get('detil-citizen/{id}', 'api\citizenController@getCitizenDetilById')->name('detil-citizen')->middleware('auth:api');
Route::post("update-citizen-bagian", 'api\citizenController@updateBagianCitizen')->name('update-citizen-bagian')->middleware('auth:api');


//encrypt-decrpyt
Route::get('get-token/{string}', 'api\bagianController@getToken')->name('get-token')->middleware('auth:api');

//checklist-bagian
Route::post('save-checklist-bagian', 'api\checklistBagianController@saveChecklist')->name('save-checklist-bagian')->middleware('auth:api');
Route::get('get-checklist-bagian/{id_bagian}/{year?}', 'api\checklistBagianController@getDataChecklistBagian')->name('checklist-bagian')->middleware('auth:api');
Route::post('delete-indikator-bagian', 'api\checklistBagianController@deleteIndikatorBagian')->name('delete-indikator-bagian')->middleware('auth:api');
Route::post('save-checklist-data-bagian', 'api\checklistBagianController@saveChecklistBagian')->name('saev-checklist-data-bagian')->middleware('auth:api');
Route::post('generate-checklist-bagian', 'api\checklistBagianController@generateChecklistBagian')->name('generate-checklist-bagian')->middleware('auth:api');
Route::post("remove-checklist-bagian", 'api\checklistBagianController@removeChecklistBagian')->name('remove-checklist-bagian')->middleware(['auth:api', 'isAdmin']);

//pj bagian
Route::get('list-pj', 'api\pjBagianController@getListPJ')->name('list-pj')->middleware('auth:api');
Route::post('save-pj-bagian', 'api\pjBagianController@savePjBagian')->name('save-pj-bagian')->middleware('auth:api');
Route::post('delete-pj-bagian', 'api\pjBagianController@deletePj')->name('delete-pj-bagian')->middleware('auth:api');

//Management folder bagian
Route::get('get-management-bagian/{id_bagian_enc}/{year}', 'api\masterEdocController@getEdocMaster')->name('get-management-bagian')->middleware('auth:api');
Route::post('update-edoc-master', 'api\masterEdocController@updateEdocMaster')->name('update-edoc-master')->middleware('auth:api');
Route::post('delete-edoc-master', 'api\masterEdocController@deleteEdocMaster')->name('delete-edoc-master')->middleware('auth:api');
Route::post('update-file-edoc', 'api\masterEdocController@updateFileEdoc')->name('update-file-edoc')->middleware('auth:api');
Route::post('delete-file-edoc', 'api\masterEdocController@deleteFileEdoc')->name('delete-file-edoc')->middleware('auth:api');


//home
Route::get('get-checklist-home/{year}', 'api\homeController@getDataChecklist')->name('get-checklist-home');


//monitoring
Route::get('list-tunggakan/{year}', 'api\monitoringController@dataTunggakan');
Route::get('get-msg-sent/{month}/{year}', 'api\monitoringController@getMsgSent')->middleware('auth:api');

//send-msg
Route::post('send-msg-tunggakan', 'api\monitoringController@sendMsgTunggakan')->middleware('auth:api');