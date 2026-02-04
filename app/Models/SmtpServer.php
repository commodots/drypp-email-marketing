<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmtpServer extends Model
{
    protected $fillable = [
        'name',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'daily_limit',
        'sent_today',
        'active'
    ];

    protected $casts = [
        'password' => 'encrypted', 
    ];

    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'smtp_id');
    }
}
