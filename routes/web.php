<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facedes\Socialite;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Route::get('auth/google', 'googleController@redirectToGoogle')->name('auth-google');
// Route::get('auth/google/callback', 'googleController@googleCallBack')->name('auth-google-callback');
// Route::get('google-drive/list', 'googleDriveController@listFiles')->name('drive-files');
// Route::post('google-drive/upload', 'googleDriveController@uploadFile')->name('google-drive-upload');
// Route::get('auth/google/callback', function(){
//     $user=Socialite::driver('google')->stateless()->user();

//     session(['google_token'=>$user->token]);

//     return response()->json([$user]);
// });
Route::get('checklist', 'api\homeController@getData')->name('checklist');