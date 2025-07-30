<?php

use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\UserGroupController;
use App\Http\Controllers\Web\CalendarController;
use App\Http\Controllers\Web\MessageController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;



Route::get('/', [AuthController::class, 'showLoginForm'])->name('login.form');

Route::group([], function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'store'])->name('users.register');
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
    Route::post('/calendar/events/{eventId}/delete', [CalendarController::class, 'deleteCalendarEvent']);
    Route::get('/calendar/events/all', [CalendarController::class, 'getAllEvents']);
    Route::get('/calendar/events', [CalendarController::class, 'getUserEvents']);
    Route::delete('/calendar/event/{eventId}/team-members/{userId}', [CalendarController::class, 'removeTeamMember'])->name('calendar.team-members.remove');
    // Calendar Event Messages
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store'); 
    Route::get('/events/{eventId}/messages', [MessageController::class, 'fetchMessages'])->name('messages.fetch');
    Route::post('/message/react', [MessageController::class, 'react']);
    Route::post('/poll-vote', [MessageController::class, 'vote']);
    Route::post('/tasks/complete', [MessageController::class, 'complete']);
});



// Protected User Routes
Route::middleware('auth:web')->group(function () {
    Route::get('/profile', [UserController::class, 'showProfile'])->name('profile.show');
    Route::get('/edit_profile', [UserController::class, 'editProfile'])->name('edit-profile.show');
    Route::patch('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::delete('/profile', [UserController::class, 'deleteProfile'])->name('profile.delete');
});

Route::get('/users/filter/modal', [AdminController::class, 'filterModal'])->name('users.filter');

// Protected Admin Routes
Route::middleware(AdminMiddleware::class)->group(function () {

    Route::get('/create', [AdminController::class, 'showCreateUserForm'])->name('users.create');
    Route::post('/users', [AdminController::class, 'store'])->name('users.store');
    Route::get('/users', [AdminController::class, 'index'])->name('users.index');
    Route::get('/users/filter', [AdminController::class, 'filter'])->name('users.filter');
    Route::get('/users/{user}', [AdminController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}', [AdminController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'destroy'])->name('users.destroy');
    
    Route::get('/active', function () {
        $admin = auth()->guard('admin')->user();

        abort_unless(Gate::forUser($admin)->allows('access-monitoring'), 403);

        return view('Role.Admin.pages.monitor.websockets');
    });
});
