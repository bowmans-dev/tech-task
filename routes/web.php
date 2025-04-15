<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/create', function () {
    return view('user.create');
});

// Default / route for GET and POST
Route::match(['get', 'post'], '/', [AuthController::class, 'login'])->name('login');

// Logout route
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected User Routes
Route::middleware('auth:web')->group(function () {
    Route::get('/profile', [UserController::class, 'showProfile'])->name('profile.show');
    Route::patch('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::delete('/profile', [UserController::class, 'deleteProfile'])->name('profile.delete');
});

// Protected Admin Routes
Route::middleware(AdminMiddleware::class)->group(function () {

    Route::post('/users', [AdminController::class, 'store'])->name('users.store');
    Route::get('/users', [AdminController::class, 'index'])->name('users.index');
    Route::get('/users/filter', [AdminController::class, 'filter'])->name('users.filter');
    Route::get('/users/{user}', [AdminController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}', [AdminController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroy'])->name('users.destroy');

    Route::post('password/email', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('password/reset/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [AuthController::class, 'resetPassword'])->name('password.update');
});
