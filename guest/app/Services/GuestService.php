<?php

namespace App\Services;

use App\Models\Guest;

class GuestService {

    /**
     * List guests with pagination, filtering and search.
     * Accepts $params: ['q' => string, 'status' => string, 'category_id' => int, 'subcategory_id' => int, 'per_page' => int]
     */
    public function list(array $params = []) {
        $query = Guest::query();

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

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function find($id) {
        return Guest::find($id);
    }

    public function findWithTrashed($id) {
        return Guest::withTrashed()->find($id);
    }

    public function create($data) {
        return Guest::create($data);
    }

    public function update($id, $data) {
        $guest = Guest::find($id);
        if (!$guest) {
            return null;
        }
        $guest->update($data);
        return $guest;
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
        return $guest;
    }

    /** Reject guest */
    public function rejectGuest($id, $userId, $reason = null) {
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
        return $guest;
    }
}

