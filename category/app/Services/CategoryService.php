<?php

namespace App\Services;

use App\Models\Category;

class CategoryService {

    /**
     * List categories with pagination, filtering and search.
     * Accepts $params: ['q' => string, 'is_active' => 0|1, 'per_page' => int]
     */
    public function list(array $params = []) {
        $query = Category::with('subcategories');

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
        return Category::create($data);
    }

    public function find($id) {
        return Category::find($id);
    }

    public function findWithTrashed($id) {
        return Category::withTrashed()->find($id);
    }

    public function update($id, $data) {
        $cat = Category::find($id);
        if (!$cat) {
            return null;
        }
        $cat->update($data);
        return $cat;
    }

    public function delete($id) {
        $cat = Category::find($id);
        if (!$cat) {
            return null;
        }
        return $cat->delete();
    }

    public function restore($id) {
        $cat = Category::withTrashed()->find($id);
        if (!$cat) {
            return null;
        }
        return $cat->restore();
    }

    public function forceDelete($id) {
        $cat = Category::withTrashed()->find($id);
        if (!$cat) {
            return null;
        }
        return $cat->forceDelete();
    }
}

