<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignMessage extends Model
{
    protected $fillable = ['campaign_id', 'email', 'status'];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}