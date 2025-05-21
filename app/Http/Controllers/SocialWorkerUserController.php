<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SocialWorkerUserController extends Controller
{
    public function destroy($id)
    {
        $user = User::where('role', 'social_worker')->findOrFail($id);
        $user->delete();

        return response()->json(['success' => true]);
    }
}
