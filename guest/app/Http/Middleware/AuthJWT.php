<?php

namespace App\Http\Middleware;

use App\Services\Auth\JWTService;
use Closure;
use Illuminate\Http\Request;

class AuthJWT {
    public function handle(Request $request, Closure $next) {

        $header = $request->header('Authorization');
        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $token = substr($header, 7);
        $jwt = new JWTService();
        $data = $jwt->validateToken($token);

        if (!$data) {
            return response()->json(['status' => false, 'message' => 'Invalid token'], 401);
        }

        $request->merge(['auth_user_id' => $data->sub]);

        return $next($request);
    }
}

