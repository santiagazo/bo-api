<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\OrganizationController;
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

    Route::middleware('tenant.organization')->group(function () {
        // Tenant context
        Route::get('/organizations/current', [OrganizationController::class, 'current']);

        // Roles & Permissions
        Route::apiResource('roles', RoleController::class);
        Route::get('/permissions/grouped', [PermissionController::class, 'grouped']);
        Route::apiResource('permissions', PermissionController::class)->only(['index', 'show']);

        // Users (admin)
        Route::apiResource('users', UserController::class);
        Route::apiResource('user-groups', UserGroupController::class);

        // Organizations
        Route::apiResource('organizations', OrganizationController::class);
        Route::post('/organizations/{id}/restore', [OrganizationController::class, 'restore']);
        Route::delete('/organizations/{id}/force', [OrganizationController::class, 'forceDelete']);
    });
});

// Health check
Route::get('/health', fn () => response()->json(['status' => 'ok']));
