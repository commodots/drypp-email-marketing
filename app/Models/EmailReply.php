<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailReply extends Model
{
    protected $fillable = ['campaign_message_id', 'from_email', 'body'];

    public function campaignMessage()
    {
        return $this->belongsTo(CampaignMessage::class);
    }
}
