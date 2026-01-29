<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Subscription;

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
        'role', 
        'two_factor_enabled',
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
        if (!$this->subscription || $this->subscription->status !== 'active') return 0;
        return $this->subscription->package->email_limit - $this->subscription->emails_used;
    }

    public function leadsRemaining()
    {
        if (!$this->subscription || $this->subscription->status !== 'active') return 0;
        return $this->subscription->package->lead_limit - $this->subscription->leads_used;
    }
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }
    public function hasQuota($amount = 1) {
        return $this->emailsRemaining() >= $amount;
    }
}
