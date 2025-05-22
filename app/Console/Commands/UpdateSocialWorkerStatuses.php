<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class UpdateSocialWorkerStatuses extends Command
{
    protected $signature = 'social-workers:update-statuses';
    protected $description = 'Сбросить статус соцработников, если закончился отпуск или больничный';

    public function handle()
    {
        $today = Carbon::today()->toDateString();

        $workers = User::where('role', 'social_worker')
            ->whereIn('status', ['on_vacation', 'sick_leave'])
            ->whereDoesntHave('absences', function ($query) use ($today) {
                $query->whereDate('to', '>=', $today);
            })
            ->get();

        foreach ($workers as $worker) {
            $worker->update(['status' => 'active']);
            $this->info("Соцработник {$worker->name} теперь активен.");
        }
    }
}