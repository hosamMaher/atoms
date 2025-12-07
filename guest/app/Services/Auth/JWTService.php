<?php

namespace App\Services\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService {

    public function createToken($user) {
        $payload = [
            'sub' => $user->id,
            'email' => $user->email,
            'type' => 'guest',
            'iat' => time(),
            'exp' => time() + (config('authjwt.expires') * 60)
        ];

        return JWT::encode($payload, config('authjwt.secret'), 'HS256');
    }

    public function validateToken($token) {
        try {
            return JWT::decode($token, new Key(config('authjwt.secret'), 'HS256'));
        } catch (\Exception $e) {
            return false;
        }
    }
}

