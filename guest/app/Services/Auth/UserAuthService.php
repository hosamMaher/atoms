<?php

namespace App\Services\Auth;

use App\Services\Integration\WSO2Service;
use Illuminate\Support\Facades\Log;

class UserAuthService {

    protected $wso2;

    public function __construct() {
        $this->wso2 = new WSO2Service();
    }

    /**
     * Validate JWT token and get user info from User Atom
     */
    public function validateToken($token) {
        try {
            $response = $this->wso2->request('user', 'post', 'auth/validate', [
                'token' => $token
            ], [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ]);

            if (isset($response['status']) && $response['status'] === true && isset($response['data']['user_id'])) {
                return $response['data']['user_id'];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('User auth validation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user details from User Atom
     */
    public function getUser($userId, $token = null) {
        try {
            $headers = [
                'Accept' => 'application/json'
            ];
            
            // Add Authorization header if token is provided
            if ($token) {
                $headers['Authorization'] = 'Bearer ' . $token;
            }
            
            $response = $this->wso2->request('user', 'get', 'users/' . $userId, [], $headers);
            
            if (isset($response['status']) && $response['status'] === true && isset($response['data'])) {
                return $response['data'];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get user: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if user has permission to approve/reject guest for specific category
     */
    public function hasCategoryPermission($userId, $categoryId, $subcategoryId = null, $token = null) {
        $user = $this->getUser($userId, $token);
        
        if (!$user) {
            return false;
        }
      
        // Admin can approve any category
        if (isset($user['role']['slug']) && $user['role']['slug'] === 'admin') {
            return true;
        }

        // Check category assignments
        $roleSlug = $user['role']['slug'] ?? null;
        
        if (isset($user['category_assignments']) && is_array($user['category_assignments'])) {
            foreach ($user['category_assignments'] as $assignment) {
                // Category Coordinator: can approve if assigned to the category
                if ($roleSlug === 'category_coordinator') {
                    if (isset($assignment['category_id']) && $assignment['category_id'] == $categoryId) {
                        return true;
                    }
                }
                
                // Sub-category Coordinator: can approve if assigned to the subcategory
                if ($roleSlug === 'subcategory_coordinator') {
                    if ($subcategoryId && isset($assignment['subcategory_id']) && $assignment['subcategory_id'] == $subcategoryId) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}

