<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\SocialWorkerAbsence;

class SocialWorkerUserController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lastName' => 'required|string|max:100',
            'firstName' => 'required|string|max:100',
            'middleName' => 'nullable|string|max:100',
            'phone' => 'required|string|regex:/^\+?[0-9]{10,15}$/|unique:users,phone',
            'email' => 'required|email|unique:users,email',
            'status' => 'sometimes|string|in:Активный,В отпуске,На больничном,Уволенный',
            'unavailabilityPeriod.from' => 'nullable|date',
            'unavailabilityPeriod.to' => 'nullable|date|after_or_equal:unavailabilityPeriod.from',
        ]);

        $fullName = trim("{$validated['lastName']} {$validated['firstName']} {$validated['middleName']}");

        $user = User::create([
            'name' => $fullName,
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make('Social123!'),
            'role' => 'social_worker',
            'status' => 'active',
        ]);

        $today = now()->toDateString();
        $hasOverlap = SocialWorkerAbsence::where('user_id', $user->id)
            ->whereDate('from', '<=', $today)
            ->whereDate('to', '>=', $today)
            ->exists();

        if (
            !$hasOverlap &&
            !empty($validated['unavailabilityPeriod']['from']) &&
            !empty($validated['unavailabilityPeriod']['to'])
        ) {
            $absenceType = $request->input('status') === 'На больничном' ? 'sick_leave' : 'vacation';

            SocialWorkerAbsence::create([
                'user_id' => $user->id,
                'type' => $absenceType,
                'from' => $validated['unavailabilityPeriod']['from'],
                'to' => $validated['unavailabilityPeriod']['to'],
            ]);
        }

        $currentAbsence = $user->absences()
            ->whereDate('from', '<=', $today)
            ->whereDate('to', '>=', $today)
            ->latest('from')
            ->first();

        $status = 'Активный';
        if ($currentAbsence) {
            $status = $currentAbsence->type === 'vacation' ? 'В отпуске' : 'На больничном';
        }

        $user->update([
            'status' => $this->mapStatus($status),
        ]);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'status' => $status,
                'tab' => 'social_workers',
            ]
        ]);
    }


    public function update(Request $request, $id)
    {
        $user = User::where('role', 'social_worker')->findOrFail($id);

        $validated = $request->validate([
            'lastName' => 'required|string|max:100',
            'firstName' => 'required|string|max:100',
            'middleName' => 'nullable|string|max:100',
            'phone' => 'required|string|regex:/^\+?[0-9]{10,15}$/|unique:users,phone,' . $id,
            'email' => 'required|email|unique:users,email,' . $id,
            'unavailabilityPeriod.from' => 'nullable|date',
            'unavailabilityPeriod.to' => 'nullable|date|after_or_equal:unavailabilityPeriod.from',
        ]);

        $fullName = trim("{$validated['lastName']} {$validated['firstName']} {$validated['middleName']}");

        $today = now()->toDateString();
        $hasActiveAbsence = $user->absences()
            ->whereDate('from', '<=', $today)
            ->whereDate('to', '>=', $today)
            ->exists();

        if (
            !$hasActiveAbsence &&
            !empty($validated['unavailabilityPeriod']['from']) &&
            !empty($validated['unavailabilityPeriod']['to'])
        ) {
            $absenceType = $request->input('status') === 'На больничном' ? 'sick_leave' : 'vacation';

            SocialWorkerAbsence::create([
                'user_id' => $user->id,
                'type' => $absenceType,
                'from' => $validated['unavailabilityPeriod']['from'],
                'to' => $validated['unavailabilityPeriod']['to'],
            ]);
        }

        $currentAbsence = $user->absences()
            ->whereDate('from', '<=', $today)
            ->whereDate('to', '>=', $today)
            ->latest('from')
            ->first();

        $status = 'Активный';
        if ($currentAbsence) {
            $status = $currentAbsence->type === 'vacation' ? 'В отпуске' : 'На больничном';
        }

        $dbStatus = $this->mapStatus($status);

        $user->update([
            'name' => $fullName,
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'status' => $dbStatus,
        ]);

        // 💡 Синхронизация прикреплённых клиентов
        if ($request->has('attachedClients')) {
            $existingClientIds = $user->clients()->pluck('users.id')->toArray();
            $newClientIds = [];

            foreach ($request->attachedClients as $attached) {
                if (!isset($attached['client_id'])) continue;

                $clientId = $attached['client_id'];
                $isTemporary = $attached['temporary'] ?? false;
                $periodFrom = $isTemporary ? ($attached['period']['from'] ?? null) : null;
                $periodTo = $isTemporary ? ($attached['period']['to'] ?? null) : null;

                $existingRelations = \DB::table('client_social_worker')
                    ->where('client_id', $clientId)
                    ->get();

                $hasPrimaryWorker = $existingRelations->contains(function ($r) {
                    return !$r->temporary;
                });

                if (!$isTemporary && $hasPrimaryWorker) {
                    continue; // ❌ Нельзя назначить основного, если уже есть основной
                }

                if ($isTemporary) {
                    if (!$periodFrom || !$periodTo) continue;

                    $primaryWorker = $existingRelations->first(fn($r) => !$r->temporary);
                    if (!$primaryWorker) continue;

                    $primaryWorkerId = $primaryWorker->social_worker_id ?? $primaryWorker->user_id;

                    $primaryWorkerAbsences = SocialWorkerAbsence::where('user_id', $primaryWorkerId)
                        ->whereDate('from', '<=', $periodFrom)
                        ->whereDate('to', '>=', $periodTo)
                        ->exists();

                    if (!$primaryWorkerAbsences) {
                        continue; // ❌ Нельзя временно прикрепить вне периода отсутствия основного
                    }
                }

                $pivotData = [
                    'temporary' => $isTemporary,
                    'from' => $periodFrom,
                    'to' => $periodTo,
                ];

                if (in_array($clientId, $existingClientIds)) {
                    $user->clients()->updateExistingPivot($clientId, $pivotData);
                } else {
                    $user->clients()->attach($clientId, $pivotData);
                }

                $newClientIds[] = $clientId;
            }

            if ($request->has('removedClients')) {
                $user->clients()->detach($request->removedClients);
            }

            $previousTemporaries = $user->clients()
                ->wherePivot('temporary', true)
                ->pluck('users.id')
                ->diff($newClientIds);

            if ($previousTemporaries->isNotEmpty()) {
                $user->clients()->detach($previousTemporaries);
            }
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'status' => $status,
                'tab' => 'social_workers',
            ]
        ]);
    }

    public function getClients($id)
    {
        $socialWorker = User::where('role', 'social_worker')->findOrFail($id);

        $clients = $socialWorker->clients()->get()->map(function ($client) use ($id) {
            $primaryRelation = $client->socialWorkers()
                ->wherePivot('temporary', false)
                ->where('users.id', '!=', $id)
                ->first();

            $absencePeriod = null;

            if ($primaryRelation) {
                // Берём ближайший или активный период отсутствия
                $absence = $primaryRelation->absences()
                    ->whereDate('to', '>=', now()) // не обязательно активный, а любой актуальный или будущий
                    ->orderBy('from')
                    ->first();

                if ($absence) {
                    $absencePeriod = [
                        'from' => $absence->from->toDateString(),
                        'to' => $absence->to->toDateString(),
                    ];
                }
            }

            return [
                'id' => $client->id,
                'fullName' => $client->name,
                'temporary' => $client->pivot->temporary ?? false,
                'period' => [
                    'from' => $client->pivot->from ?? null,
                    'to' => $client->pivot->to ?? null,
                ],
                'hasPrimaryWorker' => !!$primaryRelation,
                'absencePeriod' => $absencePeriod,
            ];
        });

        return response()->json(['clients' => $clients]);
    }
    public function destroy($id)
    {
        $user = User::where('role', 'social_worker')->findOrFail($id);
        $user->delete();

        return response()->json(['success' => true]);
    }

    private function mapStatus(string $status): string
    {
        return match ($status) {
            'Активный' => 'active',
            'Уволенный' => 'inactive',
            'В отпуске' => 'on_vacation',
            'На больничном' => 'sick_leave',
            default => 'active',
        };
    }
    public function getAbsences($id)
    {
        $user = User::findOrFail($id);
        $absences = $user->absences()->orderByDesc('from')->get();

        return response()->json(['absences' => $absences]);
    }

    public function deleteAbsence($id)
    {
        $absence = SocialWorkerAbsence::findOrFail($id);
        $absence->delete();

        return response()->json(['success' => true]);
    }

    public function updateAbsence(Request $request, $id)
    {
        $absence = SocialWorkerAbsence::findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|in:sick_leave,vacation',
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $absence->update($validated);

        return response()->json(['success' => true, 'absence' => $absence]);
    }
}
