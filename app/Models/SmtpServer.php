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
        'active',
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
        'warmup_last_sent_at',
        'health_score',
         'placement_score',
         'sent_last_24h', 'opens_last_24h',
        'clicks_last_24h', 'replies_last_24h', 'bounces_last_24h', 'fails_last_24h',
        'inbox_hits', 'spam_hits', 'hourly_limit', 'last_health_check_at'
    ];

    protected $casts = [
        'password' => 'encrypted',
        'imap_password' => 'encrypted',
        'secret_key' => 'encrypted',
        'last_health_check_at' => 'datetime',
        'is_blocked' => 'boolean',
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
