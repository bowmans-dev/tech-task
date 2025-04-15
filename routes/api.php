<?php

use App\Http\Controllers\Api\AdminController as ApiAdminController;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\UserController as ApiUserController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

// Public API Routes
Route::post('/login', [ApiAuthController::class, 'login'])->name('api.login');
Route::post('/logout', [ApiAuthController::class, 'logout'])->name('api.logout');
Route::post('password/email', [ApiAuthController::class, 'sendResetLink'])->name('api.password.email');
Route::post('password/reset', [ApiAuthController::class, 'resetPassword'])->name('api.password.update');

// Protected API Routes for Users
Route::middleware('auth:web')->group(function () {
    Route::get('/profile', [ApiUserController::class, 'showProfile'])->name('api.profile.show');
    Route::patch('/profile', [ApiUserController::class, 'updateProfile'])->name('api.profile.update');
    Route::delete('/profile', [ApiUserController::class, 'deleteProfile'])->name('api.profile.delete');
});

// Protected API Routes for Admins
Route::middleware(AdminMiddleware::class)->group(function () {

    Route::post('/users', [ApiAdminController::class, 'store'])->name('api.users.store');
    Route::get('/users', [ApiAdminController::class, 'index'])->name('api.users.index');
    Route::get('/users/filter', [ApiAdminController::class, 'filter'])->name('api.users.filter');
    Route::get('/users/{user}', [ApiAdminController::class, 'show'])->name('api.users.show');
    Route::patch('/users/{user}', [ApiAdminController::class, 'update'])->name('api.users.update');
    Route::delete('/users/{user}', [ApiAdminController::class, 'destroy'])->name('api.users.destroy');
});
