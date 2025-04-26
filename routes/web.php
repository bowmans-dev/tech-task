<?php

use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\UserGroupController;
use App\Http\Controllers\Web\CalendarController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;



Route::get('/', [AuthController::class, 'showLoginForm'])->name('login.form');

Route::group([], function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Password reset routes
Route::prefix('password')->group(function () {
    Route::post('/email', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset', [AuthController::class, 'resetPassword'])->name('password.update');
});

// User Groups
Route::group([], function () {
    Route::post('/groups/create-group', [UserGroupController::class, 'createGroup'])->name('groups.store'); 
    Route::delete('/groups/{id}', [UserGroupController::class, 'deleteGroup'])->name('groups.destroy');
    Route::post('/user-groups/add-user', [UserGroupController::class, 'addUserToGroup'])->name('user_groups.store');
    Route::post('/user-groups/remove-user', [UserGroupController::class, 'removeUserFromGroup'])->name('user_groups.remove');
});

// Calendar Routes
Route::group([], function () {
    Route::get('/calendar', [CalendarController::class, 'showCalendar'])->name('calendar.show');
    Route::post('/calendar/events/save', [CalendarController::class, 'saveCalendarEvent']);
    Route::get('/calendar/events/all', [CalendarController::class, 'getAllEvents']);
});

// Protected User Routes
Route::middleware('auth:web')->group(function () {
    Route::get('/profile', [UserController::class, 'showProfile'])->name('profile.show');
    Route::patch('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::delete('/profile', [UserController::class, 'deleteProfile'])->name('profile.delete');
});

// Protected Admin Routes
Route::middleware(AdminMiddleware::class)->group(function () {

    Route::get('/create', [AdminController::class, 'showCreateUserForm'])->name('users.create');
    Route::post('/users', [AdminController::class, 'store'])->name('users.store');
    Route::get('/users', [AdminController::class, 'index'])->name('users.index');
    Route::get('/users/filter', [AdminController::class, 'filter'])->name('users.filter');
    Route::get('/users/{user}', [AdminController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}', [AdminController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroy'])->name('users.destroy');
});
