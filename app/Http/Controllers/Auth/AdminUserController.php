<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;  
use App\Http\Controllers\Controller;
 

class AdminUserController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lastName' => 'required|string|max:100',
            'firstName' => 'required|string|max:100',
            'middleName' => 'nullable|string|max:100',
            'phone' => 'required|string|regex:/^\\+?[0-9]{10,15}$/|unique:users,phone',
            'email' => 'required|email|unique:users,email',
        ]);

        $fullName = trim("{$validated['lastName']} {$validated['firstName']} {$validated['middleName']}");

        $user = User::create([
            'name' => $fullName,
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make('Admin123!'),
            'role' => 'admin'
        ]);

        // Пример назначения роли (если используешь Spatie Roles & Permissions)
        // $user->assignRole('администратор');

        return redirect()->route('users')->with('success', 'Администратор успешно добавлен');
    }
}