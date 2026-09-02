<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DivisionController as AdminDivisionController;
use App\Http\Controllers\Admin\ExpenseController as AdminExpenseController;
use App\Http\Controllers\Admin\SubmissionController as AdminSubmissionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\SubmissionController as UserSubmissionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Root Landing Route
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

// Shared Authenticated Routes
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
});

// User (Employee) Routes - Protected by Auth & User Role
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    // User Submissions CRUD
    Route::get('/submissions', [UserSubmissionController::class, 'index'])->name('user.submissions.index');
    Route::get('/submissions/create', [UserSubmissionController::class, 'create'])->name('user.submissions.create');
    Route::post('/submissions', [UserSubmissionController::class, 'store'])->name('user.submissions.store');
    Route::get('/submissions/{submission}', [UserSubmissionController::class, 'show'])->name('user.submissions.show');
    Route::get('/submissions/{submission}/edit', [UserSubmissionController::class, 'edit'])->name('user.submissions.edit');
    Route::put('/submissions/{submission}', [UserSubmissionController::class, 'update'])->name('user.submissions.update');
    Route::delete('/submissions/{submission}', [UserSubmissionController::class, 'destroy'])->name('user.submissions.destroy');
});

// Admin Routes - Protected by Auth & Admin Role
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Financial & Expense Management
    Route::get('/expenses', [AdminExpenseController::class, 'index'])->name('expenses.index');

    // Admin Submissions Management & Replies
    Route::get('/submissions', [AdminSubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/{submission}', [AdminSubmissionController::class, 'show'])->name('submissions.show');
    Route::post('/submissions/{submission}/reply', [AdminSubmissionController::class, 'updateStatus'])->name('submissions.reply');
    Route::delete('/submissions/{submission}', [AdminSubmissionController::class, 'destroy'])->name('submissions.destroy');

    // User Management CRUD
    Route::resource('users', AdminUserController::class);

    // Division Management CRUD
    Route::resource('divisions', AdminDivisionController::class)->except(['create', 'show', 'edit']);

    // Category Management CRUD
    Route::resource('categories', AdminCategoryController::class)->except(['create', 'show', 'edit']);
});

require __DIR__.'/auth.php';
