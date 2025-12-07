<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Traits\BaseResponse;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Http\Requests\Api\V1\UpdateUserRequest;
use Illuminate\Http\Request;

class UserController extends Controller {
    use BaseResponse;

    protected $service;

    public function __construct(UserService $service) {
        $this->service = $service;
    }

    /**
     * GET /v1/users
     * Query params: q, role_id, is_active, per_page
     */
    public function index(Request $request) {
        $params = $request->only(['q', 'role_id', 'is_active', 'per_page']);
        $result = $this->service->list($params);

        return $this->success([
            'data' => UserResource::collection($result->items()),
            'meta' => [
                'total' => $result->total(),
                'per_page' => $result->perPage(),
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage()
            ]
        ]);
    }

    /**
     * GET /v1/users/{id}
     */
    public function show($id) {
        $user = $this->service->find($id);
        
        if (!$user) {
            return $this->error('User not found', 404);
        }

        return $this->success(new UserResource($user));
    }

    /** POST /v1/users */
    public function store(StoreUserRequest $request) {
        $user = $this->service->create($request->validated());
        return $this->success(new UserResource($user->load('role', 'categoryAssignments')), 'User created successfully', 201);
    }

    /** PUT /v1/users/{id} */
    public function update(UpdateUserRequest $request, $id) {
        $user = $this->service->update($id, $request->validated());
        
        if (!$user) {
            return $this->error('User not found', 404);
        }

        return $this->success(new UserResource($user), 'User updated successfully');
    }

    /** DELETE /v1/users/{id} */
    public function destroy($id) {
        $result = $this->service->delete($id);
        
        if ($result === null) {
            return $this->error('User not found', 404);
        }

        return $this->success(null, 'User deleted successfully');
    }

    /** POST /v1/users/{id}/restore */
    public function restore($id) {
        $result = $this->service->restore($id);
        
        if ($result === null) {
            return $this->error('User not found', 404);
        }

        return $this->success(null, 'User restored successfully');
    }

    /** DELETE /v1/users/{id}/force */
    public function forceDelete($id) {
        $result = $this->service->forceDelete($id);
        
        if ($result === null) {
            return $this->error('User not found', 404);
        }

        return $this->success(null, 'User permanently deleted');
    }

    /** POST /v1/users/{id}/assign-category */
    public function assignCategory(Request $request, $id) {
        $request->validate([
            'category_id' => 'required|integer',
            'subcategory_id' => 'nullable|integer'
        ]);

        $assignment = $this->service->assignCategory($id, $request->category_id, $request->subcategory_id);
        
        if (!$assignment) {
            return $this->error('User not found', 404);
        }

        return $this->success(new \App\Http\Resources\Api\V1\UserCategoryAssignmentResource($assignment), 'Category assigned successfully');
    }

    /** DELETE /v1/users/{id}/assignments/{assignmentId} */
    public function removeCategoryAssignment($id, $assignmentId) {
        $result = $this->service->removeCategoryAssignment($id, $assignmentId);
        
        if ($result === null) {
            return $this->error('Assignment not found', 404);
        }

        return $this->success(null, 'Assignment removed successfully');
    }
}

