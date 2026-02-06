<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'name',
    ];


    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'meta' => 'array'
    ];

    public function groups()
    {
        return $this->belongsToMany(ContactGroup::class, 'contact_group_items', 'contact_id', 'contact_group_id');
    }
}
