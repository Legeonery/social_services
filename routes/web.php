<?php

use App\Http\Controllers\Auth\AdminUserController;
use App\Http\Controllers\ClientUserController;
use App\Http\Controllers\SocialWorkerUserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UserController;
use App\Models\ClientType;
use App\Http\Controllers\ServiceController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        return Inertia::render('Dashboard');
    })->name('dashboard');
    Route::get('users', function () {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        return Inertia::render('Users');
    })->name('users');
    Route::get('/services', function () {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        return Inertia::render('Services');
    })->name('services');
    Route::get('schedule', function () {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        return Inertia::render('Schedule');
    })->name('schedule');
    Route::get('schedule/{date}', function ($date) {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        return Inertia::render('Schedule/Day', ['date' => $date]);
    })->name('schedule.day');

    Route::get('/client-services', function () {
        if (auth()->user()->role !== 'client') {
            abort(403);
        }
        return Inertia::render('ClientServices');
    })->name('client.services');

    Route::get('/provide-services', function () {
        if (auth()->user()->role !== 'social_worker') {
            abort(403);
        }
        return Inertia::render('ProvideServices');
    })->name('socialworker.services');
});

Route::post('/users/admins', [AdminUserController::class, 'store'])->name('users.admins.store');
Route::put('/users/admins/{id}', [AdminUserController::class, 'update'])->name('users.admins.update');
Route::delete('/users/admins/{id}', [AdminUserController::class, 'destroy']);

Route::post('/users/social-workers', [SocialWorkerUserController::class, 'store'])->name('users.social_workers.store');
Route::put('/users/social-workers/{id}', [SocialWorkerUserController::class, 'update'])->name('users.social_workers.update');
Route::delete('/users/social_workers/{id}', [SocialWorkerUserController::class, 'destroy']);
Route::get('/users/social-workers/{id}/clients', [SocialWorkerUserController::class, 'getClients']);

Route::post('/users/clients', [ClientUserController::class, 'store']);
Route::put('/users/clients/{id}', [ClientUserController::class, 'update']);
Route::delete('/users/clients/{id}', [ClientUserController::class, 'destroy']);

Route::get('/users/unassigned-clients', [UserController::class, 'unassignedClients']);

Route::get('/users/social-workers/{id}/absences', [SocialWorkerUserController::class, 'getAbsences']);
Route::delete('/absences/{id}', [SocialWorkerUserController::class, 'deleteAbsence']);
Route::put('/absences/{id}', [SocialWorkerUserController::class, 'updateAbsence']);

Route::get('/services-api', [ServiceController::class, 'index']);
Route::post('/services-api', [ServiceController::class, 'store']);
Route::put('/services-api/{service}', [ServiceController::class, 'update']);
Route::delete('/services-api/{service}', [ServiceController::class, 'destroy']);

Route::get('/client-types', function () {
    return ClientType::select('id', 'name')->get();
});

Route::get('/users_data', [UserController::class, 'user_data']);

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
