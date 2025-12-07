<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SubcategoryService;
use App\Traits\BaseResponse;
use App\Http\Resources\Api\V1\SubcategoryResource;
use App\Http\Requests\Api\V1\StoreSubcategoryRequest;
use App\Http\Requests\Api\V1\UpdateSubcategoryRequest;
use Illuminate\Http\Request;

class SubcategoryController extends Controller {
    use BaseResponse;

    protected $service;

    public function __construct(SubcategoryService $service) {
        $this->service = $service;
    }

    /**
     * GET /v1/subcategories
     * Query params: q, is_active, category_id, per_page
     */
    public function index(Request $request) {
        $params = $request->only(['q', 'is_active', 'category_id', 'per_page']);
        $result = $this->service->list($params);

        return $this->success([
            'data' => SubcategoryResource::collection($result->items()),
            'meta' => [
                'total' => $result->total(),
                'per_page' => $result->perPage(),
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage()
            ]
        ]);
    }

    /**
     * GET /v1/categories/{categoryId}/subcategories
     * Get subcategories by category ID
     * Query params: q, is_active, per_page
     */
    public function getByCategory(Request $request, $categoryId) {
        $params = $request->only(['q', 'is_active', 'per_page']);
        $result = $this->service->listByCategory($categoryId, $params);

        return $this->success([
            'data' => SubcategoryResource::collection($result->items()),
            'meta' => [
                'total' => $result->total(),
                'per_page' => $result->perPage(),
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage()
            ]
        ]);
    }

    /**
     * GET /v1/subcategories/{id}
     * Show single subcategory
     */
    public function show($id) {
        $sub = $this->service->find($id);
        
        if (!$sub) {
            return $this->error('Subcategory not found', 404);
        }

        return $this->success(new SubcategoryResource($sub));
    }

    public function store(StoreSubcategoryRequest $request) {
        $sub = $this->service->create($request->validated());
        return $this->success(new SubcategoryResource($sub), 'Subcategory created successfully', 201);
    }

    public function update(UpdateSubcategoryRequest $request, $id) {
        $sub = $this->service->update($id, $request->validated());
        
        if (!$sub) {
            return $this->error('Subcategory not found', 404);
        }

        return $this->success(new SubcategoryResource($sub), 'Subcategory updated successfully');
    }

    public function destroy($id) {
        $result = $this->service->delete($id);
        
        if ($result === null) {
            return $this->error('Subcategory not found', 404);
        }

        return $this->success(null, 'Subcategory deleted successfully');
    }

    public function restore($id) {
        $result = $this->service->restore($id);
        
        if ($result === null) {
            return $this->error('Subcategory not found', 404);
        }

        return $this->success(null, 'Subcategory restored successfully');
    }

    public function forceDelete($id) {
        $result = $this->service->forceDelete($id);
        
        if ($result === null) {
            return $this->error('Subcategory not found', 404);
        }

        return $this->success(null, 'Subcategory permanently deleted');
    }
}

