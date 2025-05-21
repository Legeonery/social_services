<?php
use App\Http\Controllers\Auth\AdminUserController;
use App\Http\Controllers\ClientUserController;
use App\Http\Controllers\SocialWorkerUserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserController;
use App\Models\ClientType;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');
    Route::get('users', fn() => Inertia::render('Users'))->name('users');
    Route::get('services', fn() => Inertia::render('Services'))->name('services');
    Route::get('schedule', fn() => Inertia::render('Schedule'))->name('schedule');
    Route::get('schedule/{date}', fn($date) => Inertia::render('Schedule/Day', ['date' => $date]))->name('schedule.day');
});

Route::post('/users/admins', [AdminUserController::class, 'store'])->name('users.admins.store');
Route::put('/users/admins/{id}', [AdminUserController::class, 'update'])->name('users.admins.update');
Route::delete('/users/admins/{id}', [AdminUserController::class, 'destroy']);
Route::delete('/users/social_workers/{id}', [SocialWorkerUserController::class, 'destroy']);
Route::post('/users/clients', [ClientUserController::class, 'store']);
Route::put('/users/clients/{id}', [ClientUserController::class, 'update']);
Route::delete('/users/clients/{id}', [ClientUserController::class, 'destroy']);

Route::get('/client-types', function () {
    return ClientType::select('id', 'name')->get();
});

Route::get('/users_data', [UserController::class, 'user_data']);

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
