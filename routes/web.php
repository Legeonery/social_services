<?php
use App\Http\Controllers\Auth\AdminUserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('users', function () {
    return Inertia::render('Users');
})->middleware(['auth', 'verified'])->name('users');

Route::get('services', function () {
    return Inertia::render('Services');
})->middleware(['auth', 'verified'])->name('services');

Route::get('schedule', function () {
    return Inertia::render('Schedule');
})->middleware(['auth', 'verified'])->name('schedule');

Route::get('schedule/{date}', function ($date) {
    return Inertia::render('Schedule/Day', [
        'date' => $date,
    ]);
})->middleware(['auth', 'verified'])->name('schedule.day');

Route::post('/users/admins', [AdminUserController::class, 'store'])->name('users.admins.store');

Route::get('/users_data', [UserController::class, 'user_data'])->name('users_data');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
