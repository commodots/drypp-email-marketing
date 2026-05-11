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
        'active',
        'type',
        'api_key',
        'region',
        'access_key',
        'secret_key',
        'imap_host',
        'imap_port',
        'imap_username',
        'imap_password',
        'is_active',
        'is_rotating',
        'hourly_limit',
        'sent_this_hour',
        'last_sent_at',
        'failure_count',
        'is_blocked',
        'priority',
        'is_transactional',
        'warmup_enabled',
        'warmup_day',
        'warmup_daily_limit',
        'warmup_sent_today',
        'warmup_last_sent_at'
    ];

    protected $casts = [
        'password' => 'encrypted',
        'imap_password' => 'encrypted',
        'secret_key' => 'encrypted',
    ];

    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'smtp_id');
    }

    public function campaignsWithRotation()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_smtp');
    }
}
