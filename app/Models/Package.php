<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'type',
        'email_quota',
        'lead_quota',
        'price',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}