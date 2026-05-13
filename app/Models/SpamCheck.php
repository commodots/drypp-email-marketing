<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpamCheck extends Model
{
    protected $fillable = ['smtp_server_id', 'seed_inbox_id', 'placement', 'checked_at', 'message_uuid'];
}
