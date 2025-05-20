<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'status',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    // Клиент -> соцработник (один соцработник)
    public function socialWorkers()
    {
        return $this->belongsToMany(User::class, 'client_social_worker', 'client_id', 'social_worker_id')
            ->withPivot('type')
            ->withTimestamps();
    }

    // Соцработник -> клиенты
    public function clients()
    {
        return $this->belongsToMany(User::class, 'client_social_worker', 'social_worker_id', 'client_id')
            ->withPivot('type')
            ->withTimestamps();
    }
}
