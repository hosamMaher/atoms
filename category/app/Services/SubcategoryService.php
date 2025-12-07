<?php

namespace App\Services;

use App\Models\Subcategory;

class SubcategoryService {
    /**
     * List subcategories with pagination, filtering and search.
     * Accepts $params: ['q' => string, 'is_active' => 0|1, 'category_id' => int, 'per_page' => int]
     * Only returns subcategories that have a valid category
     */
    public function list(array $params = []) {
        $query = Subcategory::with('category')
            ->whereNotNull('category_id')
            ->whereHas('category');

        if (!empty($params['q'])) {
            $q = trim($params['q']);
            $query->where('name', 'like', "%{$q}%");
        }

        if (isset($params['is_active'])) {
            $query->where('is_active', (bool) $params['is_active']);
        }

        if (isset($params['category_id'])) {
            $query->where('category_id', (int) $params['category_id']);
        }

        $perPage = isset($params['per_page']) ? (int) $params['per_page'] : 15;

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    /**
     * Get subcategories by category ID
     * Only returns subcategories that have a valid category
     */
    public function listByCategory($categoryId, array $params = []) {
        $query = Subcategory::with('category')
            ->where('category_id', $categoryId)
            ->whereHas('category');

        if (!empty($params['q'])) {
            $q = trim($params['q']);
            $query->where('name', 'like', "%{$q}%");
        }

        if (isset($params['is_active'])) {
            $query->where('is_active', (bool) $params['is_active']);
        }

        $perPage = isset($params['per_page']) ? (int) $params['per_page'] : 15;

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function create($data) {
        $subcategory = Subcategory::create($data);
        return $subcategory->load('category');
    }

    public function find($id) {
        return Subcategory::with('category')
            ->whereNotNull('category_id')
            ->whereHas('category')
            ->find($id);
    }

    public function findWithTrashed($id) {
        return Subcategory::withTrashed()->find($id);
    }

    public function update($id, $data) {
        $sub = Subcategory::find($id);
        if (!$sub) {
            return null;
        }
        $sub->update($data);
        return $sub->load('category');
    }
    public function delete($id) {
        $sub = Subcategory::find($id);
        if (!$sub) {
            return null;
        }
        return $sub->delete();
    }
    public function restore($id) {
        $sub = Subcategory::withTrashed()->find($id);
        if (!$sub) {
            return null;
        }
        return $sub->restore();
    }
    public function forceDelete($id) {
        $sub = Subcategory::withTrashed()->find($id);
        if (!$sub) {
            return null;
        }
        return $sub->forceDelete();
    }
}

