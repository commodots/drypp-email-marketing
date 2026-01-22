<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'status',
        'email_subject',
        'email_body',
        'emails_count',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}