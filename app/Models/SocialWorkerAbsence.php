<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialWorkerAbsence extends Model
{
    protected $fillable = ['user_id', 'type', 'from', 'to'];

    public function socialWorker()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
