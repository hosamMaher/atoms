<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\GuestService;
use App\Traits\BaseResponse;
use App\Http\Resources\Api\V1\GuestResource;
use App\Http\Requests\Api\V1\StoreGuestRequest;
use App\Http\Requests\Api\V1\UpdateGuestRequest;
use App\Http\Requests\Api\V1\RejectGuestRequest;
use App\Services\Auth\UserAuthService;
use Illuminate\Http\Request;

class GuestController extends Controller {
    use BaseResponse;

    protected $service;

    public function __construct(GuestService $service) {
        $this->service = $service;
    }

    /**
     * GET /v1/guests
     * Query params: q, status, category_id, subcategory_id, per_page
     * If user is admin: returns all guests
     * If user is not admin: returns only guests in user's assigned categories
     * Token is required
     */
    public function index(Request $request) {
        $params = $request->only(['q', 'status', 'category_id', 'subcategory_id', 'per_page']);
        
        // Token is required
        $authService = new UserAuthService();
        $token = $this->extractToken($request);
        
        if (!$token) {
            return $this->error('Authorization token required', 401);
        }
        
        $userId = $authService->validateToken($token);
        if (!$userId) {
            return $this->error('Invalid or expired token', 401);
        }
        
        $userData = $authService->getUser($userId, $token);
        if (!$userData) {
            return $this->error('Failed to get user information', 401);
        }
        
        $result = $this->service->list($params, $userId, $userData);

        return $this->success([
            'data' => GuestResource::collection($result->items()),
            'meta' => [
                'total' => $result->total(),
                'per_page' => $result->perPage(),
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage()
            ]
        ]);
    }

    /**
     * GET /v1/guests/{id}
     * Show single guest
     */
    public function show($id) {
        $guest = $this->service->find($id);
        
        if (!$guest) {
            return $this->error('Guest not found', 404);
        }

        return $this->success(new GuestResource($guest));
    }

    /** POST /v1/guests */
    public function store(StoreGuestRequest $request) {
        $guest = $this->service->create($request->validated());
        return $this->success(new GuestResource($guest), 'Guest created successfully', 201);
    }

    /** PUT /v1/guests/{id} */
    public function update(UpdateGuestRequest $request, $id) {
        $guest = $this->service->update($id, $request->validated());
        
        if (!$guest) {
            return $this->error('Guest not found', 404);
        }

        return $this->success(new GuestResource($guest), 'Guest updated successfully');
    }

    /** DELETE /v1/guests/{id} soft-delete */
    public function destroy($id) {
        $result = $this->service->delete($id);
        
        if ($result === null) {
            return $this->error('Guest not found', 404);
        }

        return $this->success(null, 'Guest deleted successfully');
    }

    /** POST /v1/guests/{id}/restore */
    public function restore($id) {
        $result = $this->service->restore($id);
        
        if ($result === null) {
            return $this->error('Guest not found', 404);
        }

        return $this->success(null, 'Guest restored successfully');
    }

    /** DELETE /v1/guests/{id}/force force delete */
    public function forceDelete($id) {
        $result = $this->service->forceDelete($id);
        
        if ($result === null) {
            return $this->error('Guest not found', 404);
        }

        return $this->success(null, 'Guest permanently deleted');
    }

    /** POST /v1/guests/{id}/approve */
    public function approve(Request $request, $id) {
        // Validate JWT token from User Atom
        $authService = new UserAuthService();
        $token = $this->extractToken($request);
        
        if (!$token) {
            return $this->error('Authorization token required', 401);
        }

        $userId = $authService->validateToken($token);
        if (!$userId) {
            return $this->error('Invalid or expired token', 401);
        }

        // Check permissions
        if (!$this->service->canApproveGuest($id, $userId, $token)) {
            dd($id, $userId, $token);
            return $this->error('You do not have permission to approve this guest', 403);
        }

        $guest = $this->service->approveGuest($id, $userId);
        
        if (!$guest) {
            return $this->error('Guest not found', 404);
        }

        return $this->success(new GuestResource($guest), 'Guest approved successfully');
    }

    /** POST /v1/guests/{id}/reject */
    public function reject(RejectGuestRequest $request, $id) {
        // Validate JWT token from User Atom
        $authService = new UserAuthService();
        $token = $this->extractToken($request);
        
        if (!$token) {
            return $this->error('Authorization token required', 401);
        }

        $userId = $authService->validateToken($token);
        if (!$userId) {
            return $this->error('Invalid or expired token', 401);
        }

        // Check permissions
        if (!$this->service->canApproveGuest($id, $userId, $token)) {
            return $this->error('You do not have permission to reject this guest', 403);
        }

        $guest = $this->service->rejectGuest($id, $userId, $request->validated()['reason']);
        
        if (!$guest) {
            return $this->error('Guest not found', 404);
        }

        return $this->success(new GuestResource($guest), 'Guest rejected successfully');
    }

    /**
     * Extract JWT token from request
     */
    private function extractToken(Request $request) {
        $header = $request->header('Authorization');
        if ($header && str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        return null;
    }
}

