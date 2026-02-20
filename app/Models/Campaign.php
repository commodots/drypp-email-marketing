<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\SmtpServer;
use App\Models\CampaignEmail;
use App\Models\CampaignMessage;


class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'status',
        'smtp_id',
        'total_emails',
        'sent',
        'opens',
        'clicks',
        'format',
        'recipient_type',
        'group_id',
        'excluded_contact_ids',
    ];

    protected $casts = [
    'excluded_contact_ids' => 'array', 
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function smtp()
    {
        return $this->belongsTo(SmtpServer::class, 'smtp_id');
    }

    public function emailContent()
    {
        return $this->hasOne(CampaignEmail::class);
    }
    public function messages()
    {
        return $this->hasMany(CampaignMessage::class);
    }

    public function getProgressAttribute()
    {
        if ($this->total_emails == 0) return 0;
        return ($this->sent / $this->total_emails) * 100;
    }
}
