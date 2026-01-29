<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'package_id',
        'emails_used',
        'leads_used',
        'expires_at',
        'status'
    ];
    protected $casts =
     [
        'expires_at' => 'datetime'
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function remainingEmails()
    {
        return $this->package->email_limit - $this->emails_used;
    }
}
