<?php

namespace App\Http\Middleware;

use Closure;
use Validator;
use Exception;

use App\User;

class TokenMiddleware
{
    public function handle($request, Closure $next)
    {
        $rules = [
            'token' => 'required|max:255|regex:/^\S*$/u',
            'email' => 'required|string|email|max:100|regex:/^\S*$/u'
        ];

        try{

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Invalid token.',
                    'error'   => ucfirst($validator->errors()->first())
                ], 401);
            }

            $token = User::select('email', 'token')
                        ->where(['email' => $request->email, 'token' => $request->token])
                        ->first();

            if(!$token){
                return response()->json([
                    'message' => 'Invalid token.'
                ], 401);
            }

            return $next($request);

        }catch(Exception $e){

            return response()->json([
                'message' => 'Terdapat kesalahan pada sistem internal.',
                'error'   => '[Middleware] ' . $e->getMessage()
            ], 500);

        }
    }
}
