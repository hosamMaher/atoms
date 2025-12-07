<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\JWTService;
use Illuminate\Http\Request;
use App\Traits\BaseResponse;

class ValidateController extends Controller {
    use BaseResponse;

    public function validateToken(Request $request) {
        $request->validate([
            'token' => 'required'
        ]);

        $jwt = new JWTService();
        $data = $jwt->validateToken($request->token);

        if (!$data) {
            return $this->error('Invalid or expired token', 401);
        }

        return $this->success([ 'user_id' => $data->sub ]);
    }
}

