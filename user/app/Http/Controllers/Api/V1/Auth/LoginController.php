<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\JWTService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Traits\BaseResponse;

class LoginController extends Controller {
    use BaseResponse;

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $user = User::with('role')->where('email', $request->email)->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error('Invalid credentials', 401);
        }

        $jwt = new JWTService();
        $token = $jwt->createToken($user);

        return $this->success([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'role' => $user->role ? [
                    'id' => $user->role->id,
                    'name' => $user->role->name,
                    'slug' => $user->role->slug,
                ] : null
            ]
        ]);
    }
}

