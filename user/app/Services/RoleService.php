<?php

namespace App\Services;

use App\Models\Role;

class RoleService {

    public function list(array $params = []) {
        $query = Role::with('users');

        if (!empty($params['q'])) {
            $q = trim($params['q']);
            $query->where('name', 'like', "%{$q}%");
        }

        $perPage = isset($params['per_page']) ? (int) $params['per_page'] : 15;

        return $query->orderBy('id', 'asc')->paginate($perPage);
    }

    public function find($id) {
        return Role::with('users')->find($id);
    }

    public function create($data) {
        return Role::create($data);
    }

    public function update($id, $data) {
        $role = Role::find($id);
        if (!$role) {
            return null;
        }
        $role->update($data);
        return $role->load('users');
    }

    public function delete($id) {
        $role = Role::find($id);
        if (!$role) {
            return null;
        }
        return $role->delete();
    }
}

