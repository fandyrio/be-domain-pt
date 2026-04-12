<?php

namespace App\Http\Middleware;

use App\Services\UserService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class isAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    protected $userService;
    public function __construct(UserService $user_service)
    {
        // throw new \Exception('Not implemented');
        $this->userService=$user_service;
    }
    public function handle(Request $request, Closure $next): Response
    {
        if(!$this->userService->isAdmin($request->user()->citizen_id)){
            abort(response()->json(
                
                [
                    'api_status'=>401,
                    'message' => 'Authorization Denied'
                ]
            ));
        }
        return $next($request);
    }
}
