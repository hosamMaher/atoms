<?php

use Illuminate\Support\Facades\Route;

// API Versioning v1: enhanced routes (pagination, search, filtering, soft deletes)
Route::prefix('v1')->group(function() {
    // Category
    Route::get('/categories', [App\Http\Controllers\Api\V1\CategoryController::class, 'index']);
    // Get subcategories by category ID (must be before /categories/{id})
    Route::get('/categories/{categoryId}/subcategories', [App\Http\Controllers\Api\V1\SubcategoryController::class, 'getByCategory']);
    Route::get('/categories/{id}', [App\Http\Controllers\Api\V1\CategoryController::class, 'show']);
    Route::post('/categories', [App\Http\Controllers\Api\V1\CategoryController::class, 'store']);
    Route::put('/categories/{id}', [App\Http\Controllers\Api\V1\CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [App\Http\Controllers\Api\V1\CategoryController::class, 'destroy']);
    Route::post('/categories/{id}/restore', [App\Http\Controllers\Api\V1\CategoryController::class, 'restore']);
    Route::delete('/categories/{id}/force', [App\Http\Controllers\Api\V1\CategoryController::class, 'forceDelete']);

    // Subcategory
    Route::get('/subcategories', [App\Http\Controllers\Api\V1\SubcategoryController::class, 'index']);
    Route::get('/subcategories/{id}', [App\Http\Controllers\Api\V1\SubcategoryController::class, 'show']);
    Route::post('/subcategories', [App\Http\Controllers\Api\V1\SubcategoryController::class, 'store']);
    Route::put('/subcategories/{id}', [App\Http\Controllers\Api\V1\SubcategoryController::class, 'update']);
    Route::delete('/subcategories/{id}', [App\Http\Controllers\Api\V1\SubcategoryController::class, 'destroy']);
    Route::post('/subcategories/{id}/restore', [App\Http\Controllers\Api\V1\SubcategoryController::class, 'restore']);
    Route::delete('/subcategories/{id}/force', [App\Http\Controllers\Api\V1\SubcategoryController::class, 'forceDelete']);
});

