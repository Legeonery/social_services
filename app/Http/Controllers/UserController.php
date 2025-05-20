<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function user_data()
    {
        $clients = User::role('client')
            ->with(['socialWorkers' => function ($q) {
                $q->select('users.id', 'name');
            }])
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'first_name' => $user->first_name ?? '',
                    'last_name' => $user->last_name ?? '',
                    'middle_name' => $user->middle_name ?? '',
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'status' => $user->status ?? 'Активный',
                    'type' => $user->pivot->type ?? '', // если нужно
                    'social_worker_name' => $user->socialWorkers->pluck('name')->implode(', '),
                    'tab' => 'clients',
                ];
            });
        var_dump($clients);
        $socialWorkers = User::role('social_worker')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'first_name' => $user->first_name ?? '',
                'last_name' => $user->last_name ?? '',
                'middle_name' => $user->middle_name ?? '',
                'phone' => $user->phone,
                'email' => $user->email,
                'status' => $user->status ?? 'Активный',
                'tab' => 'social_workers',
            ];
        });

        $admins = User::role('admin')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'first_name' => $user->first_name ?? '',
                'last_name' => $user->last_name ?? '',
                'middle_name' => $user->middle_name ?? '',
                'phone' => $user->phone,
                'email' => $user->email,
                'status' => $user->status ?? 'Активный',
                'tab' => 'admins',
            ];
        });

        return Inertia::render('Users', [
            'users' => $clients->merge($socialWorkers)->merge($admins),
        ]);
    }
}
