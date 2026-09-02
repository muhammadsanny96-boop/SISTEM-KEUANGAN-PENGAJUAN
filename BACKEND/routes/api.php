<?php

use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\DivisionController as AdminDivisionController;
use App\Http\Controllers\Api\Admin\ExpenseController as AdminExpenseController;
use App\Http\Controllers\Api\Admin\SubmissionController as AdminSubmissionController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommonController;
use App\Http\Controllers\Api\User\DashboardController as UserDashboardController;
use App\Http\Controllers\Api\User\SubmissionController as UserSubmissionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Authentication & Common Options
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::get('/options', [CommonController::class, 'getOptions'])->name('api.options');

// Authenticated Routes (Sanctum Protected)
Route::middleware('auth:sanctum')->group(function () {
    // Current User Profile & Logout
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    // Employee / User Endpoints
    Route::middleware('role:user')->prefix('user')->name('api.user.')->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
        Route::post('/submissions/{submission}/reply', [UserSubmissionController::class, 'reply'])->name('submissions.reply');
        Route::apiResource('submissions', UserSubmissionController::class);
    });

    // Administrator Endpoints
    Route::middleware('role:admin')->prefix('admin')->name('api.admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/expenses', [AdminExpenseController::class, 'index'])->name('expenses.index');

        // Submissions & Status Update / Review
        Route::get('/submissions', [AdminSubmissionController::class, 'index'])->name('submissions.index');
        Route::get('/submissions/{submission}', [AdminSubmissionController::class, 'show'])->name('submissions.show');
        Route::post('/submissions/{submission}/reply', [AdminSubmissionController::class, 'updateStatus'])->name('submissions.reply');
        Route::delete('/submissions/{submission}', [AdminSubmissionController::class, 'destroy'])->name('submissions.destroy');

        // Master Data Management
        Route::apiResource('categories', AdminCategoryController::class);
        Route::apiResource('divisions', AdminDivisionController::class);
        Route::apiResource('users', AdminUserController::class);
    });
});
