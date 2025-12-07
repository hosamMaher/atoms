<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService {

    /**
     * List users with pagination, filtering and search.
     * Accepts $params: ['q' => string, 'role_id' => int, 'is_active' => 0|1, 'per_page' => int]
     */
    public function list(array $params = []) {
        $query = User::with('role', 'categoryAssignments');

        if (!empty($params['q'])) {
            $q = trim($params['q']);
            $query->where(function($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if (isset($params['role_id'])) {
            $query->where('role_id', $params['role_id']);
        }

        if (isset($params['is_active'])) {
            $query->where('is_active', (bool) $params['is_active']);
        }

        $perPage = isset($params['per_page']) ? (int) $params['per_page'] : 15;

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function find($id) {
        return User::with('role', 'categoryAssignments')->find($id);
    }

    public function findWithTrashed($id) {
        return User::withTrashed()->with('role', 'categoryAssignments')->find($id);
    }

    public function create($data) {
        // Password will be hashed automatically by User model's setPasswordAttribute
        return User::create($data);
    }

    public function update($id, $data) {
        $user = User::find($id);
        if (!$user) {
            return null;
        }
        
        // If password is empty, remove it (don't update)
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }
        
        // Password will be hashed automatically by User model's setPasswordAttribute if provided
        $user->update($data);
        return $user->load('role', 'categoryAssignments');
    }

    public function delete($id) {
        $user = User::find($id);
        if (!$user) {
            return null;
        }
        return $user->delete();
    }

    public function restore($id) {
        $user = User::withTrashed()->find($id);
        if (!$user) {
            return null;
        }
        return $user->restore();
    }

    public function forceDelete($id) {
        $user = User::withTrashed()->find($id);
        if (!$user) {
            return null;
        }
        return $user->forceDelete();
    }

    /**
     * Assign user to category/subcategory
     */
    public function assignCategory($userId, $categoryId, $subcategoryId = null) {
        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        // Check if assignment already exists
        $existing = $user->categoryAssignments()
            ->where('category_id', $categoryId)
            ->where('subcategory_id', $subcategoryId)
            ->first();

        if ($existing) {
            // Return existing assignment instead of throwing error (idempotent)
            return $existing;
        }

        return $user->categoryAssignments()->create([
            'category_id' => $categoryId,
            'subcategory_id' => $subcategoryId
        ]);
    }

    /**
     * Remove category assignment
     */
    public function removeCategoryAssignment($userId, $assignmentId) {
        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        return $user->categoryAssignments()->where('id', $assignmentId)->delete();
    }
}

