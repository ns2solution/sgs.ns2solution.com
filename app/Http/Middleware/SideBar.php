<?php

namespace App\Http\Middleware;

use Closure;
use Exception;

use Illuminate\Support\Facades\Session;

class SideBar
{
    public function handle($request, Closure $next)
    {
        try{
            $ROLE = Session::get('user')->role;
            $URL  = $request->path();

            $SETTING = _settingSidebar();

            if(array_key_exists($URL, $SETTING)){

                if(!in_array($ROLE, $SETTING[$URL])){
                    
                    echo "Anda tidak memiliki hak akses ke halaman ini.";
                    exit();

                }

            }else{

                echo "[SB Middleware] Error: Url " . $URL . " belum di set.";
                exit();

            }

            return $next($request);
                //throw $th;
        }catch (Exception $e) {
            return redirect(url('/'));
        }
    }
}
