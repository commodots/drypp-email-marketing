<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
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
        'password',
        'package_id',
        'emails_used',
        'leads_used',
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

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
    public function campaigns()
{
    return $this->hasMany(Campaign::class);
}

    public function emailsRemaining()
{
   if (!$this->package) return 0;
    
    return $this->package->email_quota - $this->emails_used;
}

   public function leadsRemaining()
{
    if (!$this->package) return 0;
    return (int) $this->package->lead_quota - (int) $this->leads_used;
}
}
