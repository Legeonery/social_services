<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;


class AdminUserController extends Controller
{
public function store(Request $request)
{
$validated = $request->validate([
'lastName' => 'required|string|max:255',
'firstName' => 'required|string|max:255',
'middleName' => 'nullable|string|max:255',
'phone' => 'required|string|unique:users,phone|regex:/^\+?[0-9]{10,15}$/',
'email' => 'required|email|unique:users,email',
]);

$user = User::create([
'name' => trim("{$validated['lastName']} {$validated['firstName']} {$validated['middleName']}"),
'phone' => $validated['phone'],
'email' => $validated['email'],
'password' => Hash::make(Str::random(10)),
]);

$user->assignRole('admin');

// Присвоение роли (если используется Spatie Roles, например)
// $user->assignRole('admin');

return response()->json([
'message' => 'Администратор успешно добавлен',
'user' => $user,
]);
}
}