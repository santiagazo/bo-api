<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CompanyBrandProfileController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
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
    Route::get('/user', [AuthController::class, 'user'])->middleware('company.context:optional');
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

    // User profile
    Route::put('/user/profile', [UserController::class, 'updateProfile']);

    // Platform / global admin routes
    Route::get('/companies', [CompanyController::class, 'index'])->middleware('company.context:optional');
    Route::post('/companies', [CompanyController::class, 'store'])->middleware('company.context:optional');
    Route::get('/companies/current', [CompanyController::class, 'current'])->middleware('company.context');
    Route::get('/companies/{company}', [CompanyController::class, 'show'])->middleware('company.context:optional');
    Route::get('/companies/{company}/brand', [CompanyBrandProfileController::class, 'show'])->middleware('company.context:optional');
    Route::put('/companies/{company}/brand', [CompanyBrandProfileController::class, 'update'])->middleware('company.context:optional');
    Route::put('/companies/{company}', [CompanyController::class, 'update'])->middleware('company.context:optional');
    Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->middleware('company.context:optional');
    Route::post('/companies/{id}/restore', [CompanyController::class, 'restore'])->middleware('company.context:optional');
    Route::delete('/companies/{id}/force', [CompanyController::class, 'forceDelete'])->middleware('company.context:optional');

    Route::middleware('company.context')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'show'])->middleware('location.context:optional');

        // Roles & Permissions
        Route::apiResource('roles', RoleController::class);
        Route::get('/permissions/grouped', [PermissionController::class, 'grouped']);
        Route::apiResource('permissions', PermissionController::class)->only(['index', 'show']);

        // Users (admin)
        Route::apiResource('users', UserController::class);

        // External organizations
        Route::apiResource('organizations', OrganizationController::class)->except(['restore', 'forceDelete']);
        Route::apiResource('customers', CustomerController::class)->except(['restore', 'forceDelete']);
        Route::apiResource('jobs', JobController::class)->except(['restore', 'forceDelete'])->middleware('location.context:optional');
        Route::post('/locations/reorder', [LocationController::class, 'reorder']);
        Route::apiResource('locations', LocationController::class)->except(['restore', 'forceDelete']);
    });
});

// Health check
Route::get('/health', fn () => response()->json(['status' => 'ok']));
