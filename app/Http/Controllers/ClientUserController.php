<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ClientType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientUserController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lastName' => 'required|string|max:100',
            'firstName' => 'required|string|max:100',
            'middleName' => 'nullable|string|max:100',
            'phone' => 'required|string|regex:/^\+?[0-9]{10,15}$/|unique:users,phone',
            'email' => 'required|email|unique:users,email',
            'socialWorker' => 'nullable|string|max:100',
            'client_type_id' => 'required|exists:client_types,id',
            'status' => 'sometimes|string|in:Активный,Неактивный',
        ]);

        $clientType = ClientType::findOrFail($validated['client_type_id']);
        $status = $validated['status'] ?? 'Активный';
        $dbStatus = $status === 'Активный' ? 'active' : 'inactive';

        $fullName = trim("{$validated['lastName']} {$validated['firstName']} {$validated['middleName']}");

        $user = User::create([
            'name' => $fullName,
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make('Client123!'),
            'role' => 'client',
            'status' => $dbStatus,
            'client_type_id' => $clientType->id,
        ]);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'status' => $status,
                'type' => $clientType->name,
                'client_type_id' => $clientType->id,
                'tab' => 'clients',
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::where('role', 'client')->findOrFail($id);

        $validated = $request->validate([
            'lastName' => 'required|string|max:100',
            'firstName' => 'required|string|max:100',
            'middleName' => 'nullable|string|max:100',
            'phone' => 'required|string|regex:/^\+?[0-9]{10,15}$/|unique:users,phone,' . $id,
            'email' => 'required|email|unique:users,email,' . $id,
            'socialWorker' => 'nullable|string|max:100',
            'client_type_id' => 'required|exists:client_types,id',
            'status' => 'sometimes|string|in:Активный,Неактивный',
        ]);

        $clientType = ClientType::findOrFail($validated['client_type_id']);
        $status = $validated['status'] ?? 'Активный';
        $dbStatus = $status === 'Активный' ? 'active' : 'inactive';
        $fullName = trim("{$validated['lastName']} {$validated['firstName']} {$validated['middleName']}");

        $user->update([
            'name' => $fullName,
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'status' => $dbStatus,
            'client_type_id' => $clientType->id,
        ]);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'status' => $status,
                'type' => $clientType->name,
                'client_type_id' => $clientType->id,
                'tab' => 'clients',
            ]
        ]);
    }
    public function destroy($id)
    {
        $user = User::where('role', 'client')->findOrFail($id);
        $user->delete();

        return response()->json(['success' => true]);
    }
}
