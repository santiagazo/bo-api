<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserGroupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// SMS code login (forgot password / magic login)
Route::post('/auth/send-code', [AuthController::class, 'sendCode']);
Route::post('/auth/verify-code', [AuthController::class, 'verifyCode']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
    
    // User profile
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    
    // Roles & Permissions
    Route::apiResource('roles', RoleController::class);
    Route::get('/permissions/grouped', [PermissionController::class, 'grouped']);
    Route::apiResource('permissions', PermissionController::class)->only(['index', 'show']);
    
    // Users (admin)
    Route::apiResource('users', UserController::class);
    Route::apiResource('user-groups', UserGroupController::class);
    
    // Organizations
    Route::apiResource('organizations', \App\Http\Controllers\OrganizationController::class);
    Route::post('/organizations/{id}/restore', [\App\Http\Controllers\OrganizationController::class, 'restore']);
    Route::delete('/organizations/{id}/force', [\App\Http\Controllers\OrganizationController::class, 'forceDelete']);
});

// Health check
Route::get('/health', fn() => response()->json(['status' => 'ok']));
