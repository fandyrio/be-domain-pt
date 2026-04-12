<?php

namespace App\Http\Controllers;
use Google\Client;
use Google\Service\Drive;

use Illuminate\Http\Request;

class googleController extends Controller
{
    public function redirectToGoogle(){
        $client=new Client();
        $client->setAuthConfig(storage_path('app/google/client_secret.json'));
        $client->setRedirectUri(route('auth-google-callback'));
        $client->setScopes([Drive::DRIVE]);
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return redirect($client->createAuthUrl());
    }

    public function googleCallBack(Request $request)
    {
        $client = new Client();
        $client->setAuthConfig(storage_path('app/google/client_secret.json'));
        $client->setRedirectUri(route('auth-google-callback'));
        $client->setScopes([Drive::DRIVE]);
        $client->setAccessType('offline');

        $token = $client->fetchAccessTokenWithAuthCode($request->code);

        if (isset($token['error'])) {
            return response()->json(['error' => 'Authentication failed'], 401);
        }

        session(['google_token' => $token]);

        return redirect()->route('drive-files');
    }
}
