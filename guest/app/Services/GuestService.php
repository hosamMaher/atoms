<?php

namespace App\Services;

use App\Models\Guest;
use App\Services\Integration\WSO2Service;
use Illuminate\Support\Facades\Hash;

class GuestService {

    /**
     * List guests with pagination, filtering and search.
     * Accepts $params: ['q' => string, 'status' => string, 'category_id' => int, 'subcategory_id' => int, 'per_page' => int]
     * If user is admin: returns all guests
     * If user is not admin: filters by user's category assignments
     */
    public function list(array $params = [], $userId = null, $userData = null) {
        $query = Guest::query();
  
        // Filter by user permissions (userId and userData are required)
        if ($userId && $userData) {
            $roleSlug = $userData['role']['slug'] ?? null;
         
            // Admin can see all guests
            if ($roleSlug !== 'admin') {
                $assignments = $userData['category_assignments'] ?? [];
                
                if (!empty($assignments)) {
                    // Get category and subcategory IDs from assignments based on role
                    $categoryIds = [];
                    $subcategoryIds = [];
                    
                    foreach ($assignments as $assignment) {
                        // Category Coordinator: sees all guests in assigned categories
                        if ($roleSlug === 'category_coordinator' && isset($assignment['category_id'])) {
                            $categoryIds[] = $assignment['category_id'];
                        }
                        
                        // Subcategory Coordinator: sees only guests in assigned subcategories
                        if ($roleSlug === 'subcategory_coordinator' && isset($assignment['subcategory_id']) && $assignment['subcategory_id']) {
                            $subcategoryIds[] = $assignment['subcategory_id'];
                        }
                    }
                    
                    // Filter guests based on role
                    $query->where(function($q) use ($categoryIds, $subcategoryIds, $roleSlug) {
                        if ($roleSlug === 'category_coordinator' && !empty($categoryIds)) {
                            // Category Coordinator: guests in assigned categories
                            $q->whereIn('category_id', $categoryIds);
                        } elseif ($roleSlug === 'subcategory_coordinator' && !empty($subcategoryIds)) {
                            // Subcategory Coordinator: guests in assigned subcategories
                            $q->whereIn('subcategory_id', $subcategoryIds);
                        } else {
                            // No matching assignments, return empty
                            $q->whereRaw('1 = 0');
                        }
                    });
                } else {
                    // User has no assignments, return empty result
                    $query->whereRaw('1 = 0'); // Always false condition
                }
            }
        }

        if (!empty($params['q'])) {
            $q = trim($params['q']);
            $query->where(function($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('mobile', 'like', "%{$q}%");
            });
        }

        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (isset($params['category_id'])) {
            $query->where('category_id', $params['category_id']);
        }

        if (isset($params['subcategory_id'])) {
            $query->where('subcategory_id', $params['subcategory_id']);
        }

        $perPage = isset($params['per_page']) ? (int) $params['per_page'] : 15;

        $result = $query->orderBy('id', 'desc')->paginate($perPage);
        
        // Enrich each guest with category and subcategory data
        $result->getCollection()->transform(function ($guest) {
            return $this->enrichGuestWithRelations($guest);
        });

        return $result;
    }

    public function find($id) {
        $guest = Guest::find($id);
        return $this->enrichGuestWithRelations($guest);
    }

    public function findWithTrashed($id) {
        return Guest::withTrashed()->find($id);
    }

    public function create($data) {
        // Check if status is provided, if not, determine based on category auto_approve
        if (!isset($data['status']) && isset($data['category_id'])) {
            $category = $this->getCategoryDetails($data['category_id']);
            
            // If category has auto_approve enabled, set status to approved
            // Support both boolean true and integer 1
            if ($category && isset($category['auto_approve']) && 
                ($category['auto_approve'] === true || $category['auto_approve'] === 1 || $category['auto_approve'] === '1')) {
                $data['status'] = 'approved';
            } else {
                $data['status'] = 'pending';
            }
        } elseif (!isset($data['status'])) {
            // Default to pending if no category_id provided
            $data['status'] = 'pending';
        }

        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $guest = Guest::create($data);
        return $this->enrichGuestWithRelations($guest);
    }

    public function update($id, $data) {
        $guest = Guest::find($id);
        if (!$guest) {
            return null;
        }
        
        // Prevent direct modification of status and approval fields
        // These should only be modified through approve/reject endpoints
        unset($data['status']);
        unset($data['approved_by']);
        unset($data['approved_at']);
        unset($data['rejected_by']);
        unset($data['rejected_at']);
        unset($data['reject_reason']);
        
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        $guest->update($data);
     
        return $this->enrichGuestWithRelations($guest);
    }

    public function delete($id) {
        $guest = Guest::find($id);
        if (!$guest) {
            return null;
        }
        return $guest->delete();
    }

    public function restore($id) {
        $guest = Guest::withTrashed()->find($id);
        if (!$guest) {
            return null;
        }
        return $guest->restore();
    }

    public function forceDelete($id) {
        $guest = Guest::withTrashed()->find($id);
        if (!$guest) {
            return null;
        }
        return $guest->forceDelete();
    }

    /** Approve guest */
    public function approveGuest($id, $userId) {
        $guest = Guest::find($id);
        if (!$guest) {
            return null;
        }
        $guest->status = 'approved';
        $guest->approved_by = $userId;
        $guest->approved_at = now();
        $guest->rejected_by = null;
        $guest->rejected_at = null;
        $guest->reject_reason = null;
        $guest->save();
        return $this->enrichGuestWithRelations($guest);
    }

    /** Reject guest */
    public function rejectGuest($id, $userId, $reason) {
        $guest = Guest::find($id);
        if (!$guest) {
            return null;
        }   
   
        $guest->status = 'rejected';
        $guest->rejected_by = $userId;
        $guest->rejected_at = now();
        $guest->reject_reason = $reason;
        $guest->approved_by = null;
        $guest->approved_at = null;
        $guest->save();
        return $this->enrichGuestWithRelations($guest);
    }

    /**
     * Check if user can approve/reject this guest
     */
    public function canApproveGuest($guestId, $userId, $token = null) {
        $guest = Guest::find($guestId);
    
        if (!$guest) {
            return false;
        }

        $authService = new \App\Services\Auth\UserAuthService();
        return $authService->hasCategoryPermission($userId, $guest->category_id, $guest->subcategory_id, $token);
    }

    /**
     * Get category details via WSO2
     */
    public function getCategoryDetails($categoryId) {
        if (!$categoryId) {
            return null;
        }
        
        try {
            $wso2 = new WSO2Service();
            $response = $wso2->request('category', 'get', 'categories/' . $categoryId);
            
            // Extract data from response if it's wrapped
            // Category API returns: {"success": true, "data": {...}}
            if (isset($response['data']) && is_array($response['data'])) {
                return $response['data'];
            }
            
            // If response is already the data itself
            return $response;
        } catch (\Exception $e) {
            \Log::error('Failed to fetch category: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get subcategory details via WSO2
     */
    public function getSubcategoryDetails($subcategoryId) {
        if (!$subcategoryId) {
            return null;
        }
        
        try {
            $wso2 = new WSO2Service();
            $response = $wso2->request('category', 'get', 'subcategories/' . $subcategoryId);
            
            // Extract data from response if it's wrapped
            if (isset($response['data'])) {
                return $response['data'];
            }
            
            return $response;
        } catch (\Exception $e) {
            \Log::error('Failed to fetch subcategory: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Enrich guest with category and subcategory data
     */
    public function enrichGuestWithRelations($guest) {
        if (!$guest) {
            return $guest;
        }

        // Add category data
        if ($guest->category_id) {
            $guest->category_data = $this->getCategoryDetails($guest->category_id);
        }

        // Add subcategory data
        if ($guest->subcategory_id) {
            $guest->subcategory_data = $this->getSubcategoryDetails($guest->subcategory_id);
        }

        return $guest;
    }
}

