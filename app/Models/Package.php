<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'type',
        'email_limit',
        'lead_limit',
        'price_ngn',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}