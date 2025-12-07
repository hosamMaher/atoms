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
            Log::warning("Permission check failed: User not found", ['user_id' => $userId]);
            return false;
        }
      
        $roleSlug = $user['role']['slug'] ?? null;
        
        // Admin can approve any category
        if ($roleSlug === 'admin') {
            Log::info("Permission granted: Admin user", ['user_id' => $userId, 'category_id' => $categoryId]);
            return true;
        }

        // Check category assignments
        $assignments = $user['category_assignments'] ?? [];
        
        if (empty($assignments)) {
            Log::warning("Permission denied: No category assignments", [
                'user_id' => $userId,
                'role' => $roleSlug,
                'category_id' => $categoryId,
                'subcategory_id' => $subcategoryId
            ]);
            return false;
        }
           
        foreach ($assignments as $assignment) {
            // Category Coordinator: can approve if assigned to the category
            if ($roleSlug === 'category_coordinator') {
                if (isset($assignment['category_id']) && $assignment['category_id'] == $categoryId) {
                    Log::info("Permission granted: Category Coordinator matched", [
                        'user_id' => $userId,
                        'category_id' => $categoryId,
                        'assignment_category_id' => $assignment['category_id']
                    ]);
                    return true;
                }
            }
            
            // Sub-category Coordinator: can approve if assigned to the subcategory
            if ($roleSlug === 'subcategory_coordinator') {
                if ($subcategoryId && isset($assignment['subcategory_id']) && $assignment['subcategory_id'] == $subcategoryId) {
                    Log::info("Permission granted: Subcategory Coordinator matched", [
                        'user_id' => $userId,
                        'subcategory_id' => $subcategoryId,
                        'assignment_subcategory_id' => $assignment['subcategory_id']
                    ]);
                    return true;
                }
            }
        }

        Log::warning("Permission denied: No matching assignment", [
            'user_id' => $userId,
            'role' => $roleSlug,
            'category_id' => $categoryId,
            'subcategory_id' => $subcategoryId,
            'assignments' => $assignments
        ]);

        return false;
    }
}

