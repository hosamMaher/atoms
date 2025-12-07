<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\RoleService;
use App\Traits\BaseResponse;
use App\Http\Resources\Api\V1\RoleResource;
use App\Http\Requests\Api\V1\StoreRoleRequest;
use Illuminate\Http\Request;

class RoleController extends Controller {
    use BaseResponse;

    protected $service;

    public function __construct(RoleService $service) {
        $this->service = $service;
    }

    /**
     * GET /v1/roles
     */
    public function index(Request $request) {
        $params = $request->only(['q', 'per_page']);
        $result = $this->service->list($params);

        return $this->success([
            'data' => RoleResource::collection($result->items()),
            'meta' => [
                'total' => $result->total(),
                'per_page' => $result->perPage(),
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage()
            ]
        ]);
    }

    /**
     * GET /v1/roles/{id}
     */
    public function show($id) {
        $role = $this->service->find($id);
        
        if (!$role) {
            return $this->error('Role not found', 404);
        }

        return $this->success(new RoleResource($role));
    }

    /** POST /v1/roles */
    public function store(StoreRoleRequest $request) {
        $role = $this->service->create($request->validated());
        return $this->success(new RoleResource($role), 'Role created successfully', 201);
    }

    /** PUT /v1/roles/{id} */
    public function update(Request $request, $id) {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:roles,name,' . $id,
            'slug' => 'sometimes|string|max:255|unique:roles,slug,' . $id,
            'description' => 'nullable|string'
        ]);

        $role = $this->service->update($id, $validated);
        
        if (!$role) {
            return $this->error('Role not found', 404);
        }

        return $this->success(new RoleResource($role), 'Role updated successfully');
    }

    /** DELETE /v1/roles/{id} */
    public function destroy($id) {
        $result = $this->service->delete($id);
        
        if ($result === null) {
            return $this->error('Role not found', 404);
        }

        return $this->success(null, 'Role deleted successfully');
    }
}

