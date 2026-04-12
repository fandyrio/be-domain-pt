<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Api_token;
use Illuminate\Support\Facades\Hash;
class apiTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token=hash('sha256', $request->bearerToken());
        $get_data=Api_token::where('env', 'local')
                ->where('token', $token)    
                ->first();
        if(!is_null($get_data)){
            return $next($request);
        }
        return response()->json(['status'=>401, 'msg'=>'Unauthorized '.$token]);
    }
}
