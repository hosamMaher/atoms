<?php

use Illuminate\Support\Facades\Route;

// Guest Atom API v1
Route::prefix('v1')->group(function() {
    Route::get('/guests', [App\Http\Controllers\Api\V1\GuestController::class, 'index']);
    Route::get('/guests/{id}', [App\Http\Controllers\Api\V1\GuestController::class, 'show']);
    Route::post('/guests', [App\Http\Controllers\Api\V1\GuestController::class, 'store']);
    Route::put('/guests/{id}', [App\Http\Controllers\Api\V1\GuestController::class, 'update']);
    Route::delete('/guests/{id}', [App\Http\Controllers\Api\V1\GuestController::class, 'destroy']);
    Route::post('/guests/{id}/restore', [App\Http\Controllers\Api\V1\GuestController::class, 'restore']);
    Route::delete('/guests/{id}/force', [App\Http\Controllers\Api\V1\GuestController::class, 'forceDelete']);

    // Approval Workflow
    Route::post('/guests/{id}/approve', [App\Http\Controllers\Api\V1\GuestController::class, 'approve']);
    Route::post('/guests/{id}/reject', [App\Http\Controllers\Api\V1\GuestController::class, 'reject']);
});

