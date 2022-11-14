<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Exception;

class SessionMiddleware
{
    public function handle($request, Closure $next)
    {
    	if(Session::get('email') && Session::get('token')){
			//add function filter nav menu
			try{
				
				$user = Http::get(env('API_URL').'/users/byrequest?email='.Session::get('email'), [
					'email' => Session::get('email'),
					'token' => Session::get('token'),
					'_token' => Session::get('token')
				]);
				
				if($user->failed()){
					return redirect()->route('logout');
				}
	
				$user_role = $user->json();
	
				//next code with request path
				$nav_menu = Http::get(env('API_URL').'/menu-nav/byrequest?link='.$request->path(), [
					'email' => Session::get('email'),
					'link' => $request->path(),
					'token' => Session::get('token'),
					'_token' => Session::get('token')
				]);
				//dd($request->path());
				
				if($nav_menu->failed()){
					return redirect()->route('logout');
				}
				

				//dd($user_role);
				$nav_menu_roles = $nav_menu->json();
				
				
				$allowed_nav_menu_role = json_decode($nav_menu_roles['data']['role_access'],true);
				foreach($allowed_nav_menu_role[$request->path()."_nav_menu"] as $role){
					if(strcmp($role,$user_role['data']['role_name']) == 0){
						return $next($request);
						break;
					}
				}
				return redirect()->route('logout');

			}catch(Exception $e){
				dd($e);
			}
			
    	}else{

    		return redirect()->route('logout');

    	}
    }
}