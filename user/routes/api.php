<?php

use Illuminate\Support\Facades\Route;

// User Atom API v1
Route::prefix('v1')->group(function() {
    // AUTH ROUTES (Public)
    Route::post('/auth/login', [App\Http\Controllers\Api\V1\Auth\LoginController::class, 'login']);
    Route::post('/auth/validate', [App\Http\Controllers\Api\V1\Auth\ValidateController::class, 'validateToken']);

    // User routes (Protected)
    Route::middleware('auth.jwt')->group(function() {
        Route::get('/users', [App\Http\Controllers\Api\V1\UserController::class, 'index']);
        Route::get('/users/{id}', [App\Http\Controllers\Api\V1\UserController::class, 'show']);
        Route::post('/users', [App\Http\Controllers\Api\V1\UserController::class, 'store']);
        Route::put('/users/{id}', [App\Http\Controllers\Api\V1\UserController::class, 'update']);
        Route::delete('/users/{id}', [App\Http\Controllers\Api\V1\UserController::class, 'destroy']);
        Route::post('/users/{id}/restore', [App\Http\Controllers\Api\V1\UserController::class, 'restore']);
        Route::delete('/users/{id}/force', [App\Http\Controllers\Api\V1\UserController::class, 'forceDelete']);
        
        // Category Assignments
        Route::post('/users/{id}/assign-category', [App\Http\Controllers\Api\V1\UserController::class, 'assignCategory']);
        Route::delete('/users/{id}/assignments/{assignmentId}', [App\Http\Controllers\Api\V1\UserController::class, 'removeCategoryAssignment']);

        // Role routes
        Route::get('/roles', [App\Http\Controllers\Api\V1\RoleController::class, 'index']);
        Route::get('/roles/{id}', [App\Http\Controllers\Api\V1\RoleController::class, 'show']);
        Route::post('/roles', [App\Http\Controllers\Api\V1\RoleController::class, 'store']);
        Route::put('/roles/{id}', [App\Http\Controllers\Api\V1\RoleController::class, 'update']);
        Route::delete('/roles/{id}', [App\Http\Controllers\Api\V1\RoleController::class, 'destroy']);
    });
});

