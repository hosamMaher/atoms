<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use App\Traits\BaseResponse;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Http\Requests\Api\V1\StoreCategoryRequest;
use App\Http\Requests\Api\V1\UpdateCategoryRequest;
use Illuminate\Http\Request;

class CategoryController extends Controller {
    use BaseResponse;

    protected $service;

    public function __construct(CategoryService $service) {
        $this->service = $service;
    }

    /**
     * GET /v1/categories
     * Query params: q, is_active, per_page
     */
    public function index(Request $request) {
        $params = $request->only(['q','is_active','per_page']);
        $result = $this->service->list($params);

        // return paginated resource
        return $this->success([
            'data' => CategoryResource::collection($result->items()),
            'meta' => [
                'total' => $result->total(),
                'per_page' => $result->perPage(),
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage()
            ]
        ]);
    }

    /**
     * GET /v1/categories/{id}
     * Show single category
     */
    public function show($id) {
        $cat = $this->service->find($id);
        
        if (!$cat) {
            return $this->error('Category not found', 404);
        }

        return $this->success(new CategoryResource($cat->load('subcategories')));
    }

    /** POST /v1/categories */
    public function store(StoreCategoryRequest $request) {
        $cat = $this->service->create($request->validated());
        return $this->success(new CategoryResource($cat), 'Category created successfully', 201);
    }

    /** PUT /v1/categories/{id} */
    public function update(UpdateCategoryRequest $request, $id) {
        $cat = $this->service->update($id, $request->validated());
        
        if (!$cat) {
            return $this->error('Category not found', 404);
        }

        return $this->success(new CategoryResource($cat), 'Category updated successfully');
    }

    /** DELETE /v1/categories/{id} soft-delete */
    public function destroy($id) {
        $result = $this->service->delete($id);
        
        if ($result === null) {
            return $this->error('Category not found', 404);
        }

        return $this->success(null, 'Category deleted successfully');
    }

    /** POST /v1/categories/{id}/restore */
    public function restore($id) {
        $result = $this->service->restore($id);
        
        if ($result === null) {
            return $this->error('Category not found', 404);
        }

        return $this->success(null, 'Category restored successfully');
    }

    /** DELETE /v1/categories/{id}/force force delete */
    public function forceDelete($id) {
        $result = $this->service->forceDelete($id);
        
        if ($result === null) {
            return $this->error('Category not found', 404);
        }

        return $this->success(null, 'Category permanently deleted');
    }
}

