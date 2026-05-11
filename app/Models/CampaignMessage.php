<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CampaignMessage extends Model
{
    protected $fillable = ['campaign_id', 'email', 'status', 'message_uuid'];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($message) {
            $message->message_uuid = (string) Str::uuid();
        });
    }
}
