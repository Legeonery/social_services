<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Inertia\Inertia;

class UserController extends Controller
{
    public function user_data()
    {
        $today = Carbon::today()->toDateString();

        $clients = User::where('role', 'client')
            ->with(['clientType', 'socialWorkers' => function ($query) {
                $query->withPivot(['temporary', 'from', 'to']);
            }])
            ->get()
            ->map(function ($user) use ($today) {
                // Найдём текущего активного соцработника
                $activeWorker = $user->socialWorkers
                    ->filter(function ($worker) use ($today) {
                        $pivot = $worker->pivot;
                        if (!$pivot->temporary) {
                            return true; // основной
                        }
                        return $pivot->from <= $today && $pivot->to >= $today; // временный, но активный
                    })
                    ->sortByDesc(fn($w) => $w->pivot->temporary) // временный > основной (чтобы временный перебивал основного)
                    ->first();

                return [
                    'id' => $user->id,
                    'name' => $user->name ?? '',
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'status' => $user->status ?? 'Активный',
                    'type' => optional($user->clientType)->name,
                    'client_type_id' => $user->client_type_id,
                    'social_worker_name' => $activeWorker?->name,
                    'social_worker_type' => $activeWorker?->pivot->temporary ? 'временный' : 'основной',
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
            'users' => $clients->merge($socialWorkers)->merge($admins),
        ]);
    }
    public function unassignedClients(Request $request)
    {
        $workerId = $request->query('worker_id');
        $today = Carbon::today()->toDateString();

        $clients = User::where('role', 'client')
            ->where('status', 'active')
            ->where(function ($query) use ($workerId, $today) {
                $query
                    ->whereDoesntHave('socialWorkers')
                    ->orWhereHas('socialWorkers', fn($q) => $q->where('users.id', $workerId))
                    ->orWhereHas('socialWorkers.absences', function ($q) use ($today) {
                        $q->whereDate('from', '<=', $today)
                            ->whereDate('to', '>=', $today);
                    });
            })
            ->whereDoesntHave('socialWorkers', function ($q) use ($workerId, $today) {
                $q->where('users.id', '!=', $workerId)
                    ->where('client_social_worker.temporary', true)
                    ->whereDate('client_social_worker.from', '<=', $today)
                    ->whereDate('client_social_worker.to', '>=', $today);
            })
            ->select('id', 'name')
            ->addSelect([
                'hasPrimaryWorker' => \DB::table('client_social_worker')
                    ->selectRaw('count(*)')
                    ->whereColumn('client_social_worker.client_id', 'users.id')
                    ->where('client_social_worker.temporary', false)
            ])
            ->get()
            ->map(function ($client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'hasPrimaryWorker' => $client->hasPrimaryWorker > 0,
                ];
            });

        return response()->json(['clients' => $clients]);
    }
}
