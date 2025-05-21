<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function user_data()
    {
        $clients = User::where('role', 'client')
            ->with([
                'clientType', // 👈 загрузка типа клиента
                'socialWorkers' => fn($q) => $q->select('users.id', 'name'),
            ])
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name ?? '',
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'status' => $user->status ?? 'Активный',
                    'type' => optional($user->clientType)->name, // <- имя типа
                    'client_type_id' => $user->client_type_id,   // <- сам ID
                    'social_worker_name' => $user->socialWorkers->pluck('name')->implode(', '),
                    'tab' => 'clients',
                ];
            });

        $socialWorkers = User::where('role', 'social_worker')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name ?? '',
                'phone' => $user->phone,
                'email' => $user->email,
                'status' => $user->status ?? 'Активный',
                'tab' => 'social_workers',
            ];
        });

        $admins = User::where('role', 'admin')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name ?? '',
                'phone' => $user->phone,
                'email' => $user->email,
                'status' => $user->status ?? 'Активный',
                'tab' => 'admins',
            ];
        });

        return response()->json([
            'users' => $clients->merge($socialWorkers)->merge($admins)
        ]);
    }
}
