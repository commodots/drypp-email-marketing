<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmtpServer extends Model
{
    protected $fillable = [
        'name',
        'host',
        'daily_limit',
        'sent_today',
        'active'
    ];

    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'smtp_id');
    }
}
