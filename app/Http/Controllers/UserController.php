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
                $primaryWorker = $user->socialWorkers
                    ->first(fn($worker) => !$worker->pivot->temporary);

                $temporaryWorker = $user->socialWorkers
                    ->first(
                        fn($worker) =>
                        $worker->pivot->temporary &&
                            $worker->pivot->from <= $today &&
                            $worker->pivot->to >= $today
                    );

                return [
                    'id' => $user->id,
                    'name' => $user->name ?? '',
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'status' => $user->status ?? 'Активный',
                    'type' => optional($user->clientType)->name,
                    'client_type_id' => $user->client_type_id,

                    // Раздельно указываем основного и временного
                    'primary_social_worker' => $primaryWorker?->name,
                    'temporary_social_worker' => $temporaryWorker?->name,
                    'temporary_period' => $temporaryWorker ? [
                        'from' => $temporaryWorker->pivot->from,
                        'to' => $temporaryWorker->pivot->to,
                    ] : null,

                    'tab' => 'clients',
                ];
            });

        $socialWorkers = User::where('role', 'social_worker')
            ->with(['clients' => function ($query) {
                $query->select('users.id', 'users.name')
                    ->withPivot(['temporary', 'from', 'to']);
            }])
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name ?? '',
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'status' => $user->status ?? 'Активный',
                    'tab' => 'social_workers',
                    'socialWorkerClients' => $user->clients->map(function ($client) {
                        $label = $client->name;
                        if ($client->pivot->temporary) {
                            $label .= ' (временно: ' . $client->pivot->from . ' — ' . $client->pivot->to . ')';
                        }
                        return $label;
                    }),
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
